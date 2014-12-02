<?php
require 'password_compat/password.php';

/**
 *
 * @author kenta.fried@gmail.com
 */
class MitgliedController extends Zend_Controller_Action {

	/**
	 * Cost parameter for BCRYPT that allows to change the CPU cost of the algorithm. The cost can
	 * range from 4 to 31. It is recommended to use the highest cost possible, while keeping
	 * response time reasonable (target 0.1 and 0.5 seconds for a hashing).
	 */
	const ALGORITHMIC_COST = 15;

	public function init() {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('hinzufuegen', 'json')->initContext();
		$ajaxContext->addActionContext('aendern', 'json')->initContext();
		$ajaxContext->addActionContext('loeschen', 'json')->initContext();
	}

	public function hinzufuegenAction() {
		$this->isAjaxPost();

		$formData = $this->getRequest()->getPost();
		$newMemberForm = new Application_Form_NewMember('Login', 'notExists');
		$id = null;

		if ($newMemberForm->isValid($formData)) {
			// save new member
			$mitglied = new Application_Model_Mitglied();
			$mitglied->readNewMemberForm($newMemberForm);
			$mitglied->setStatus('aktiv');
			$mitglied->setPasswortHash(
				password_hash($newMemberForm->getValue('passwort'), PASSWORD_BCRYPT,
					array(
						"cost" => MitgliedController::ALGORITHMIC_COST,
					)));
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$id = $mitgliedMapper->save($mitglied);

			// session
			$session = new Zend_Session_Namespace('pixelfornature');
			$session->loadMenu = true;

			// create signup interaction
			$interactionMapper = new Application_Model_DbTable_Interaktion();
			$interactionMapper->createSignup($id, $session->project['id']);

			// login new member
			Zend_Loader::loadFile("AuthenticationService.php");
			$authService = new AuthenticationService();
			$authService->loginUser($formData['email'], $formData['passwort']);
		}
		$this->_helper->json(array(
			"id" => $id,
			"error" => ($newMemberForm->getMessages() ? $newMemberForm->getMessages() : ""),
		));
	}

	public function aendernAction() {
		$this->isAjaxPost();

		$formData = $this->getRequest()->getPost();
		$session = new Zend_Session_Namespace('pixelfornature');
		$auth = Zend_Auth::getInstance();
		$mitgliedMapper = new Application_Model_DbTable_Mitglied();
		$success = false;
		$isValid = false;
		$id = null;
		$errors = array();

		if ($auth->hasIdentity()) {
			// logged in users need to have a session and a valid form
			if (!isset($session->user['id'])) {
				$errors = array_merge($errors, array(
					"passwort" => (object) array(// show in the password error label
						"noSession" => "Session user not found")));
			} else {
				// validate form
				$id = $session->user['id'];
				$updateMemberForm = new Application_Form_NewMember('Update', 'notExists', array('field' => 'id', 'value' => $id));
				$isValid = $updateMemberForm->isValidPartial($formData);
				$errors = array_merge($errors, $updateMemberForm->getMessages());
			}
		} else {
			// not logged in users need to have a hash and can only change the password
			$formData = $this->ensureExists($formData, array('email', 'passwort', 'passwortWiederholt', 'verifizierungHash'));

			// validate form
			$updateMemberForm = new Application_Form_NewMember('Reset', 'exists');
			$isValid = $updateMemberForm->isValidPartial($formData);
			$errors = array_merge($errors, $updateMemberForm->getMessages());

			// match email and hash
			$user = $mitgliedMapper->findByCondition(array(
				'email' => $formData['email'],
				'verifizierung_hash' => $formData['verifizierungHash']));
			if (!$user) {
				$isValid = false;
				$errors = array_merge($errors, array(
					"passwort" => (object) array(// show in the password error label
						"incorrect_hash" => "Deine Anfragedaten sind nicht mehr gültig. Bitte klick erneut auf 'Passwort vergessen' und lass dir eine neue E-Mail schicken.")));
			} else {
				$id = $user['id'];
				$formData = array(
					"passwort" => $formData["passwort"],
					"verifizierung_hash" => "");
			}
		}

		if ($isValid) {
			// save changes to user
			$formData['id'] = $id;
			$formData['datum_geaendert'] = date('Y-m-d H:i:s');
			if (isset($formData['passwort'])) {
				$formData['passwort_hash'] = password_hash($formData['passwort'], PASSWORD_BCRYPT,
					array(
						"cost" => MitgliedController::ALGORITHMIC_COST,
					));
				unset($formData['passwort']);
				unset($formData['passwortWiederholt']);
			}
			$mitgliedMapper->save($formData);
			unset($formData['passwort_hash']);

			if ($auth->hasIdentity()) {
				// update session user
				$session->user = array_merge($session->user, $formData);
			}
			$success = true;
		}

		$this->_helper->json(array(
			"id" => $id,
			"success" => $success,
			"error" => ($errors ? ((object) $errors) : ""),
		));
	}

	public function passwortZuruecksetzenAction() {
		$email = $this->getRequest()->getParam('email');
		$hash = $this->getRequest()->getParam('hash');
		$errors = array();
		$resetPasswordForm = new Application_Form_NewMember('ResetPassword');
		$resetPasswordForm->getElement('email')->setValue($email)->setAttrib("disabled", "disabled");
		$this->view->assign("resetPasswordForm", $resetPasswordForm);
		$this->view->assign("hash", $hash);

		if ($email && $hash) {
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$member = $mitgliedMapper->findByCondition(array(
				'email' => $email,
				'verifizierung_hash' => $hash));
			if (!$member) {
				$errors['invalidData'] = 'Ungültige Daten';
			}
		} else {
			$errors['missingData'] = 'Fehlende Daten';
		}
		$this->view->assign("errors", $errors);
	}

	public function loeschenAction() {
		$this->isAjaxPost();

		$errors = array();
		$success = false;

		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$errors['not_logged_in'] = "No user is logged in";
		}

		$session = new Zend_Session_Namespace('pixelfornature');
		if (!isset($session->user['id'])) {
			$errors['no_session_user'] = "No session user found";
		}
		$id = $session->user['id'];

		if (!$errors) {
			// logout member
			Zend_Loader::loadFile("AuthenticationService.php");
			$authService = new AuthenticationService();
			$authService->logoutUser();

			// delete member
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$mitgliedMapper->delete(array(
				'id = ?' => $id));
			$success = true;
		}

		$this->_helper->json(array(
			"id" => $id,
			"success" => $success,
			"error" => ($errors ? $errors : ""),
		));
	}

	// not used
	private function isHuman() {
		$captcha = $this->getRequest()
		                ->getPost('captcha');
		$captchaId = $captcha['id'];
		$captchaInput = $captcha['input'];
		$captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
		$captchaIterator = $captchaSession->getIterator();
		$captchaWord = $captchaIterator['word'];
		return ($captchaInput == $captchaWord);
	}

	private function isAjaxPost() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			throw new Exception("Not an xml request", 1);
		}
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

	private function ensureExists($form, $fields) {
		foreach ($fields as $field) {
			if (!isset($form[$field])) {
				$form[$field] = "";
			}
		}
		return $form;
	}

}

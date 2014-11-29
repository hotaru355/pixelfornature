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

		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			throw new Exception("Not logged in", 1);
		}

		$session = new Zend_Session_Namespace('pixelfornature');
		if (!isset($session->user['id'])) {
			throw new Exception("Session user not found", 1);
		}
		$id = $session->user['id'];

		$updateMemberForm = new Application_Form_NewMember('Update', 'notExists', array('field' => 'id', 'value' => $id));
		$formData = $this->getRequest()->getPost();
		$success = false;

		if ($updateMemberForm->isValidPartial($formData)) {

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
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$mitgliedMapper->save($formData);

			// update session user
			unset($formData['passwort_hash']);
			$user = array_merge($session->user, $formData);
			// $user = $mitgliedMapper->findById($id);
			$session->user = $user;

			$success = true;
		}
		$this->_helper->json(array(
			"id" => $id,
			"success" => $success,
			"error" => ($updateMemberForm->getMessages() ? $updateMemberForm->getMessages() : ""),
		));
	}

	public function passwortZuruecksetzenAction() {
		$email = $this->getRequest()->getParam('email');
		$hash = $this->getRequest()->getParam('hash');
		$errors = array();

		echo $hash;
		if ($email && $hash) {
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$member = $mitgliedMapper->findByCondition(array('email' => $email));

			if (!$member) {
				$errors['invalidEmail'] = 'E-Mail nicht gefunden';
			} else {
				$resetPasswordForm = new Application_Form_NewMember('ResetPassword');
				$resetPasswordForm->getElement('email')->setValue($member['email']);
				// var_dump($resetPasswordForm);
				$this->view->assign('resetPasswordForm', $resetPasswordForm);
				$this->view->assign('email', $member['email']);
			}
		} else {
			$errors['invalidLink'] = 'fehlt info';
		}
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
			$mitgliedMapper->delete($id);
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

}

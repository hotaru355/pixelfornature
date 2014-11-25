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

		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

	public function indexAction() {
		$mitglied = new Application_Model_Mitglied();
		$mitglied->setEmail('kenta.fried@gmail.com')
		         ->setVorname("firstname")
		         ->setNachname("lastname")
		         ->setVerifizierungHash('qwerasdf');

		$this->sendVerificationEmail($mitglied);
	}

	public function hinzufuegenAction() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			return;
		}

		$newMemberForm = new Application_Form_NewMember();
		$formData = $this->getRequest()->getPost();
		$id = null;

		if ($newMemberForm->isValid($formData)) {
			$mitglied = new Application_Model_Mitglied();
			$mitglied->readNewMemberForm($newMemberForm);
			$mitglied->setStatus('aktiv');
			$mitglied->setPasswortHash(
				password_hash($newMemberForm->getValue('passwort'), PASSWORD_BCRYPT,
					array(
						"cost" => MitgliedController::ALGORITHMIC_COST
					)));

			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$id = $mitgliedMapper->save($mitglied);

			$session = new Zend_Session_Namespace('pixelfornature');
			$session->loadMenu = true;

			$interactionMapper = new Application_Model_DbTable_Interaktion();
			$interactionMapper->createSignup($id, $session->project['id']);
		}
		$this->_helper->json(array(
			"id" => $id,
			"error" => (empty($newMemberForm->getMessages()) ? "" : $newMemberForm->getMessages()),
		));
	}

	public function aendernAction() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			return;
		}

		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			throw new Exception("Not logged in", 1);
		}

		$session = new Zend_Session_Namespace('pixelfornature');
		if (!isset($session->user['id'])) {
			throw new Exception("Session user not found", 1);
		}
		$id = $session->user['id'];

		$newMemberForm = new Application_Form_NewMember('Update', array('field' => 'id', 'value' => $id));
		$formData = $this->getRequest()->getPost();
		$success = false;

		if ($newMemberForm->isValidPartial($formData)) {

			// save changes to user
			$formData['id'] = $id;
			$formData['datum_geaendert'] = date('Y-m-d H:i:s');
			if (isset($formData['passwort'])) {
				$formData['passwort_hash'] = password_hash($formData['passwort'], PASSWORD_BCRYPT,
					array(
						"cost" => MitgliedController::ALGORITHMIC_COST
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
			"error" => (empty($newMemberForm->getMessages()) ? "" : $newMemberForm->getMessages()),
		));
	}

	public function entfernenAction() {
		// action body
	}

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

	private function sendVerificationEmail($mitglied) {
		// Hash zur Validierung der Anmeldung = Hash(UserEmail + Zufallszahl)
		//$mitglied->setVerifizierungHash(sha1($mitglied->getEmail() . rand(1, 1000000)));
		if (!$mitglied->getEmail() || !$mitglied->getVerifizierungHash()) {
			throw new Exception('Cannot send email. No email address or verification hash provided.');
		}

		$emailConfig = $this->getInvokeArg('bootstrap')
		                    ->getOption('m2spende')['verification_email'];

		$fromAddress = $emailConfig['fromAddress'];
		$fromName = $emailConfig['fromName'];
		$subject = 'Willkommen bei Naturefund!';
		$verificationUrl = sprintf('%s?email=%s&hash=%s', $emailConfig['url'], $mitglied->getEmail(),

			$mitglied->getVerifizierungHash());

		$emailBodyHtml = new Zend_View();
		$emailBodyHtml->setScriptPath(APPLICATION_PATH . '/views/emails/');
		$emailBodyHtml->assign('vorname', $mitglied->getVorname());
		$emailBodyHtml->assign('url', $verificationUrl);

		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyHtml($emailBodyHtml->render('verification_de.phtml'));
		$mail->setFrom($fromEmail, $fromName);
		$mail->addTo($mitglied->getEmail(), $mitglied->getVorname() . ' ' . $mitglied->getName());
		$mail->setSubject($subject);
		$mail->send();
	}
}

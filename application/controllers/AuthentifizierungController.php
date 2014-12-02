<?php

class AuthentifizierungController extends Zend_Controller_Action {

	public function init() {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('login', 'json')->initContext();
		$ajaxContext->addActionContext('logout', 'json')->initContext();
		$ajaxContext->addActionContext('passwortZuruecksetzen', 'json')->initContext();
	}

	public function loginAction() {
		$this->isAjaxPost();

		$loginForm = new Application_Form_NewMember('Login');
		$formData = $this->getRequest()->getPost();
		$success = false;

		if ($loginForm->isValidPartial($formData)) {
			$values = $loginForm->getValues();

			Zend_Loader::loadFile("AuthenticationService.php");
			$authService = new AuthenticationService();
			$success = $authService->loginUser($values['email'], $values['passwort']);
		}

		$this->_helper->json(array(
			"success" => $success,
			"error" => ($loginForm->getMessages() ? $loginForm->getMessages() : ""),
		));
	}

	public function logoutAction() {
		$this->isAjaxPost();

		Zend_Loader::loadFile("AuthenticationService.php");
		$authService = new AuthenticationService();
		$success = $authService->logoutUser();

		$this->_helper->json(array(
			"success" => true,
		));
	}

	public function requestResetAction() {
		$this->isAjaxPost();

		$resetPasswordForm = new Application_Form_NewMember('ResetPassword', 'exists');
		$formData = $this->getRequest()->getPost();
		$success = false;

		if (!isset($formData['email'])) {
			$formData['email'] = "";// gard against malicious attempts
		}
		if ($resetPasswordForm->isValidPartial($formData)) {
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$member = $mitgliedMapper->findByCondition(array('email' => $formData['email']));

			// Hash zur Validierung der Anmeldung = Hash(UserEmail + Zufallszahl)
			$member['verifizierung_hash'] = sha1($member['email'] . rand(1, 1000000));

			$mitgliedMapper->save(array(
				'id' => $member['id'],
				'verifizierung_hash' => $member['verifizierung_hash']));

			$this->sendVerificationEmail($member);
			$success = true;
		}

		$this->_helper->json(array(
			"success" => $success,
			"error" => ($resetPasswordForm->getMessages() ? $resetPasswordForm->getMessages() : ""),
		));
	}

	private function sendVerificationEmail($member) {
		if (!$member['email'] || !$member['verifizierung_hash']) {
			throw new Exception('No email address or verification hash provided.');
		}

		$emailConfig = $this->getInvokeArg('bootstrap')->getOption('pixelfornature')['verification_email'];

		$subject = 'Pixel for nature: Passwort zurücksetzen';
		$verificationUrl = sprintf('%s?email=%s&hash=%s', $emailConfig['url'], $member['email'], $member['verifizierung_hash']);

		$emailBodyHtml = new Zend_View();
		$emailBodyHtml->setScriptPath(APPLICATION_PATH . '/views/emails/');
		$emailBodyHtml->assign('vorname', $member['vorname']);
		$emailBodyHtml->assign('url', $verificationUrl);

		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyHtml($emailBodyHtml->render('verification_de.phtml'));
		$mail->setFrom($emailConfig['fromAddress'], $emailConfig['fromName']);
		$mail->addTo($member['email'], $member['vorname'] . ' ' . $member['nachname']);
		$mail->setSubject($subject);

		$mail->send();
	}

	private function isAjaxPost() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			throw new Exception("Not an xml request", 1);
		}
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

}

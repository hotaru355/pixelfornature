<?php

class AuthentifizierungController extends Zend_Controller_Action {

	public function init() {
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();

		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('login', 'json')->initContext();
		$ajaxContext->addActionContext('logout', 'json')->initContext();
	}

	public function loginAction() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			return;
		}

		$loginForm = new Application_Form_Login();
		$formData = $this->getRequest()->getPost();
		$success = false;

		if ($loginForm->isValid($formData)) {
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
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			return;
		}

		Zend_Loader::loadFile("AuthenticationService.php");
		$authService = new AuthenticationService();
		$success = $authService->logoutUser();

		$this->_helper->json(array(
			"success" => true
		));
	}

	public function bestaetigenAction() {
		$email = $this->getRequest()
		              ->getParam('email');
		$hash = $this->getRequest()
		             ->getParam('hash');

		if ($email && $hash) {
			$mitgliedMapper = new Application_Model_DbTable_Mitglied();
			$mitglieder = $mitgliedMapper->suchen(array('email' => $email, 'verifizierung_hash' => $hash));

			if (count($mitglieder) != 1) {
				$this->view->assign('erfolgreich', false);
			} else {
				$mitglied = reset($mitglieder);
				$mitglied->setStatus('aktiv');
				$mitgliedMapper->speichern($mitglied);
				$this->view->assign('erfolgreich', true);
			}
		} else {
			$this->_helper->redirector('index', 'index');
		}
	}
}

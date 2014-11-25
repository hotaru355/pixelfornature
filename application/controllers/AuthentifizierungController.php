<?php
require_once 'password_compat/Auth.php';

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
			$user = $this->_authenticate($values['email'], $values['passwort']);
			if ($user != null) {
				$session = new Zend_Session_Namespace('pixelfornature');
				$interaktionMapper = new Application_Model_DbTable_Interaktion();
				$session->user = (array) $user;
				$session->user['timeline'] = $interaktionMapper->getTimeline($user->id);
				$session->user['pixelsTotal'] = $interaktionMapper->getPixelsTotalByMember($user->id);
				$session->loadMenu = true;
				$success = true;
			}
		}

		$this->_helper->json(array(
			"success" => $success,
			"error" => (empty($loginForm->getMessages()) ? "" : $loginForm->getMessages()),
		));

	}

	public function logoutAction() {
		if (!$this->_request->isXmlHttpRequest() || !$this->getRequest()->isPost()) {
			return;
		}
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();

		Zend_Auth::getInstance()->clearIdentity();
		$session = new Zend_Session_Namespace('pixelfornature');
		unset($session->user);

		$this->_helper->json(array(
			"success" => true
		));
	}

	protected function _authenticate($email, $password) {
		$adapter = $this->_getAuthAdapter();
		$adapter->setIdentity($email);
		$adapter->setCredential($password);

		$auth = Zend_Auth::getInstance();
		$user = null;
		if ($auth->authenticate($adapter)->isValid()) {
			$user = $adapter->getResultRowObject(array(
				'id',
				'vorname',
				'nachname',
				'strasse',
				'plz',
				'ort',
				'email',
				'telefon'));
		}
		return $user;
	}

	protected function _getAuthAdapter() {
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$authAdapter = new Compat_Auth_Adapter_DbTable($dbAdapter);

		$authAdapter->setTableName('mitglieder')
		            ->setIdentityColumn('email')
		            ->setCredentialColumn('passwort_hash')
		            ->setStatusColumn('status')
		            ->setStatusPassValue('aktiv');

		return $authAdapter;
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

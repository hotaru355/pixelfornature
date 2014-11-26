<?php
require_once 'password_compat/Auth.php';

class AuthenticationService {

	public function loginUser($email, $password) {
		$adapter = $this->_getAuthAdapter();
		$adapter->setIdentity($email);
		$adapter->setCredential($password);

		$auth = Zend_Auth::getInstance();
		$isValid = false;
		if ($auth->authenticate($adapter)->isValid()) {
			$session = new Zend_Session_Namespace('pixelfornature');
			$interaktionMapper = new Application_Model_DbTable_Interaktion();

			$user = $adapter->getResultRowObject(array(
				'id',
				'vorname',
				'nachname',
				'strasse',
				'plz',
				'ort',
				'email',
				'telefon'));
			$session->user = (array) $user;
			$session->user['timeline'] = $interaktionMapper->getTimeline($user->id);
			$session->user['pixelsTotal'] = $interaktionMapper->getPixelsTotalByMember($user->id);
			$session->loadMenu = true;
			$isValid = true;
		}
		return $isValid;
	}

	public function logoutUser() {
		Zend_Auth::getInstance()->clearIdentity();
		$session = new Zend_Session_Namespace('pixelfornature');
		unset($session->user);
		return true;
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
}
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

			// retrieve user
			$user = $adapter->getResultRowObject(array(
				'id',
				'vorname',
				'nachname',
				'strasse',
				'plz',
				'ort',
				'email',
				'telefon'));

			// save user data to session
			$session->user = (array) $user;
			$session->user['timeline'] = $interaktionMapper->getTimeline($user->id);
			$session->user['pixelsTotal'] = $interaktionMapper->getPixelsTotalByMember($user->id);
			$session->loadMenu = true;

			// save donation interaction, if it happened when user was not logged in
			if ($session->donatedPixels) {
				$interactionMapper = new Application_Model_DbTable_Interaktion();
				$interactionMapper->createDonation($session->user['id'], $session->project['id'], $session->donatedPixels);
				$session->user['pixelsTotal'] = $interaktionMapper->getPixelsTotalByMember($session->user['id']);
				$session->donatedPixels = null;
			}
			$isValid = true;
		}
		return $isValid;
	}

	public function logoutUser() {
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::namespaceUnset('pixelfornature');
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
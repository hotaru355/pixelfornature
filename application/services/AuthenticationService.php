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
				'facebook_id',
				'vorname',
				'nachname',
				'strasse',
				'plz',
				'ort',
				'email',
				'telefon'));

			if ($session->pendingData) {
				// save facebook id to user
				$mitgliedMapper = new Application_Model_DbTable_Mitglied();
				$mitgliedMapper->save(array(
					"id" => $user->id,
					"facebook_id" => $session->pendingData->fbId,
				));

				// save pending donations created when user was not logged in
				foreach ($session->pendingData->donations as $donation) {
					$donation['mitglied_id'] = $user->id;
					$interaktionMapper->createDonation($donation);
				}
				$session->pendingData = null;
			}

			// save user data to session
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
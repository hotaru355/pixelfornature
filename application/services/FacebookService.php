<?php
class FacebookService {
	private $log;
	private $fbSession;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');

		Facebook\FacebookSession::setDefaultApplication(
			"1410418365855348", // AppId
			"c39cebabff11320e15ee20870cdc60ad"// AppSecret
		);
		$this->fbSession = (new Facebook\FacebookJavaScriptLoginHelper())->getSession();
	}

	public function uploadPhoto($imagePath) {
		$fbImageLink = null;

		if ($this->fbSession) {

			$fbPicture = (new Facebook\FacebookRequest(
				$this->fbSession, 'POST', '/me/photos',
				array(
					'source' => '@' . $imagePath,
					'message' => 'Ich spende Pixel für Natur! http://pixelfornature.org',
				)
			))->execute()->getGraphObject();

			$fbImageLink = "https://www.facebook.com/me?preview_cover=" . $fbPicture->getProperty('id');
		}

		return $fbImageLink;
	}

	public function getUser() {
		$fbUser = null;
		if ($this->fbSession) {
			$fbUser = (new Facebook\FacebookRequest(
				$this->fbSession, 'GET', '/me'
			))    ->execute()->getGraphObject();
		}
		return $fbUser;
	}
}

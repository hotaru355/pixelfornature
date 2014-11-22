<?php
class FacebookService {
	private $facebook;
	private $log;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');
	}

	public function uploadPhoto($imagePath) {
		Facebook\FacebookSession::setDefaultApplication(
			"1410418365855348", // AppId
			"c39cebabff11320e15ee20870cdc60ad"// AppSecret
		);
		$fbImageLink = null;
		$fbSession = (new Facebook\FacebookJavaScriptLoginHelper())->getSession();

		if ($fbSession) {
			$response = (new Facebook\FacebookRequest(
				$fbSession, 'POST', '/me/photos',
				array(
					'source' => '@' . 'image/png',
					'message' => 'Ich spende Pixel für Natur!',
				)
			))->execute()->getGraphObject();

			$fbImageLink = "https://www.facebook.com/me?preview_cover=" . $response->getProperty('id');
		}

		return $fbImageLink;
	}

}

// $facebook->setFileUploadSupport(true);
// $accounts = $facebook->api('/me/accounts');
// for ($i = 0; $accounts['data'][$i]; $i++) {
// 	$page_access_token = $accounts['data'][$i]['access_token'];
// 	$page_id = $accounts['data'][$i]['id'];

// 	$facebook->setAccessToken($page_access_token);
// 	$args = array('name' => 'awesome album name', 'message' => 'awesome album message');
// 	try {
// 		$album_id = $facebook->api("/$page_id/albums", 'post', $args);
// 	} catch (Exception $e) {
// 		echo $e->getMessage();
// 	}

// 	$args = array('image' => '@' . realpath('/var/www/facebook.png'));
// 	try {
// 		$uploaded_photo_details = $facebook->api("/{$album_id['id']}/photos", 'post', $args);
// 	} catch (Exception $e) {
// 		echo $e->getMessage();
// 	}

// 	if (isset($uploaded_photo_details['id'])) {
// 		$args = array('cover' => $uploaded_photo_details['id'], 'offset_y' => 0);
// 		try {
// 			$cover_details = $facebook->api("/{$page_id}", 'post', $args);
// 		} catch (Exception $e) {
// 			echo $e->getMessage();
// 		}
// 	}
// }

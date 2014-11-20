<?php
class FacebookService {
	private $facebook;
	private $log;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');
		// Zend_Loader::loadFile("facebook.php");

		// $this->facebook = new Facebook(
		//         array(
		//                 "appId" => "1410418365855348",
		//                 "secret" => "c39cebabff11320e15ee20870cdc60ad",
		//                 "cookie" => true,
		//                 "fileUpload" => true
		//         ));
	}

	public function uploadPhoto($accessData, $imagePath) {
		Facebook\FacebookSession::setDefaultApplication("1410418365855348", "c39cebabff11320e15ee20870cdc60ad");

		$helper = new Facebook\FacebookJavaScriptLoginHelper();
		try {
			$session = $helper->getSession();
		} catch (Facebook\FacebookRequestException $ex) {
			// When Facebook returns an error
			$this->log->error($ex);
		} catch (\Exception $ex) {
			// When validation fails or other local issues
			$this->log->error($ex);
		}
		if ($session) {
			// Logged in
		}

		$request = new Facebook\FacebookRequest($session, 'GET', '/me');
		$response = $request->execute();
		$graphObject = $response->getGraphObject();

		$this->log->debug($response);
		$this->log->debug($graphObject);

		return $fbImageLink;
	}

	public function uploadPhoto2($accessData, $imagePath) {
		$this->facebook->setAccessToken($accessData["accessToken"]);

		try {
			$albums = $this->facebook->api("/me/albums");
			$album_id = "";

			foreach ($albums["data"] as $item) {
				if ($item["type"] == "cover") {
					$album_id = $item["id"];
					break;
				}
			}
			// $this->_log->info("albumID: " . $album_id);

			$args = array(
				'message' => 'Hochgeladen von Naturefund.de',
				'source' => '@' . $imagePath,
			);

			// $data = $facebook->api("/" . $album_id . "/photos", 'post', $args);
			$data = $this->facebook->api("/me/photos", 'post', $args);
			$pictue = $this->facebook->api('/' . $data['id']);
			$fbImageLink = "https://www.facebook.com/profile.php?preview_cover=" . $data['id'];
		} catch (FacebookApiException $e) {
			$result = $e->getResult();
			throw new Exception($result["error"]["message"], $result["error"]["code"], $e);
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

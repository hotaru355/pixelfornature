<?php
class FacebookService
{
    private $facebook;

    function __construct()
    {
        Zend_Loader::loadFile("facebook.php");
        
        $this->facebook = new Facebook(
                array(
                        "appId" => "1410418365855348",
                        "secret" => "c39cebabff11320e15ee20870cdc60ad",
                        "cookie" => true,
                        "fileUpload" => true
                ));
    }

    public function uploadPhoto($accessData, $imagePath)
    {
        $this->facebook->setAccessToken($accessData ["accessToken"]);
        
        try {
            $albums = $this->facebook->api("/me/albums");
            $album_id = "";
            
            foreach ($albums ["data"] as $item) {
                if ($item ["type"] == "cover") {
                    $album_id = $item ["id"];
                    break;
                }
            }
            // $this->_log->info("albumID: " . $album_id);
            
            $args = array(
                    'message' => 'Hochgeladen von Naturefund.de',
                    'source' => '@' . $imagePath
            );
            
            // $data = $facebook->api("/" . $album_id . "/photos", 'post', $args);
            $data = $this->facebook->api("/me/photos", 'post', $args);
            $pictue = $this->facebook->api('/' . $data ['id']);
            $fbImageLink = "https://www.facebook.com/profile.php?preview_cover=" . $data ['id'];
        } catch (FacebookApiException $e) {
            $result = $e->getResult();
            throw new Exception($result["error"]["message"], $result["error"]["code"], $e);
        }
        
        return $fbImageLink;
    }
}
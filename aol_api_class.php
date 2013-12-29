<?php

/**
 * AOL API Class beta
 * By Amr Mahrous
 * http://AmrMahrous.com
 * amr@amrmahrous.com
 * +201110042952
 * Egypt
 * 
 */
class AOL_API_Class {

    private $aolAuthHost = "https://api.screenname.aol.com/";
    private $aolAuthorizeUrl = "https://api.screenname.aol.com/auth/authorize";
    private $aolAccessTokenUrl = "https://api.screenname.aol.com/auth/access_token";
    private $aolGetUserDataUrl = "https://api.screenname.aol.com/auth/getUserData";
    private $aolGetUsersubscribe = 'https://reader.aol.com/reader/api/0/subscription/list';
    private $aolfeedsubscribe = 'https://reader.aol.com/reader/api/0/subscription/quickadd';
    private $clientId;
    private $clientSecret;
    private $callbackUrl;

    function __construct($data = false) {
        if (!$data) {
            die('Please provide required data');
        }
        $this->clientId = $data['client_id'];
        $this->clientSecret = $data['client_secret'];
        $this->callbackUrl = $data['callback_url'];

        if ($this->route_controller()) {
            header('Content-Type: application/json');
            echo $this->route_controller();
        } else {
            $this->get_token_from_code();
            $this->auth();
        }
    }

    public function route_controller() {
        $info = NULL;
        if (isset($_GET['act'])) {
            switch ($_GET['act']) {
                case 'user_info':
                    $info = $this->get_user_info();
                    break;
                case 'reader_subscribe_get':
                    $info = $this->reader_subscribe_get();
                    break;
                case 'reader_subscribe_add':
                    $info = $this->reader_subscribe_add();
                    break;
                default:
                    break;
            }
        }
        return $info;
    }

    private function auth() {
        if (!isset($_SESSION['refreshToken'])) {
            $authorizationReq = $this->aolAuthorizeUrl . "?client_id=" . $this->clientId . "&response_type=code&redirect_uri=" . $this->callbackUrl;
            header("Location: $authorizationReq");
        }
    }

    private function get_token_from_code() {

        if (isset($_GET['code'])) {

            $accessPost = "grant_type=authorization_code&redirect_uri=" . urlencode($this->callbackUrl) . "&client_id=" . urlencode($this->clientId) . "&client_secret=" . urlencode($this->clientSecret) . "&code=" . $_GET["code"];
            $accessCurl = curl_init($this->aolAccessTokenUrl);
            curl_setopt($accessCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($accessCurl, CURLOPT_FAILONERROR, false);
            curl_setopt($accessCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($accessCurl, CURLOPT_POST, 1);
            curl_setopt($accessCurl, CURLOPT_POSTFIELDS, $accessPost);
            $error = null;
            $accessResponse = curl_exec($accessCurl);
            if (!$accessResponse) {
                $error = curl_error($accessCurl);
            }
            curl_close($accessCurl);
            $decodedAccessResponse = json_decode($accessResponse, TRUE);
            if (!isset($decodedAccessResponse["error"])) {
                $_SESSION['access_token'] = $decodedAccessResponse["access_token"];
                $_SESSION['refreshToken'] = $decodedAccessResponse["refresh_token"];
                $redirect = $this->callbackUrl . "?act=user_info";
                header("Location: $redirect");
                return true;
            } elseif (isset($decodedAccessResponse["error"])) {
                echo "error: " . $decodedAccessResponse["error"] . "<br />
        error_description: " . $decodedAccessResponse["error_description"];
            } else {
                echo 'Unkown Error!';
            }
        }
    }

    public function get_token_from_refresh_token() {
        if (isset($_SESSION['refreshToken'])) {

            $accessPost = "grant_type=refresh_token&refresh_token=" . $_SESSION['refreshToken'] . "&redirect_uri=" . urlencode($this->callbackUrl) . "&client_id=" . urlencode($this->clientId) . "&client_secret=" . urlencode($this->clientSecret);
            $accessCurl = curl_init($this->aolAccessTokenUrl);
            curl_setopt($accessCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($accessCurl, CURLOPT_FAILONERROR, false);
            curl_setopt($accessCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($accessCurl, CURLOPT_POST, 1);
            curl_setopt($accessCurl, CURLOPT_POSTFIELDS, $accessPost);
            $error = null;
            $accessResponse = curl_exec($accessCurl);
            if (!$accessResponse) {
                $error = curl_error($accessCurl);
            }
            curl_close($accessCurl);
            $decodedAccessResponse = json_decode($accessResponse, TRUE);
            if (!isset($decodedAccessResponse["error"])) {
                $_SESSION['access_token'] = $decodedAccessResponse["access_token"];
            }
            return $decodedAccessResponse;
        }
    }

    public function get_user_info() {
        $token_info = $this->get_token_from_refresh_token();
        $access_token = $token_info['access_token'];
        $authorizationHeader = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: BEARER " . $access_token
        );
        $apiCurl = curl_init($this->aolGetUserDataUrl . "?f=json");
        curl_setopt($apiCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($apiCurl, CURLOPT_FAILONERROR, false);
        curl_setopt($apiCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($apiCurl, CURLOPT_HTTPHEADER, $authorizationHeader);

        $error = null;
        $apiResponse = curl_exec($apiCurl);
        if (!$apiResponse) {
            $error = curl_error($apiCurl);
        }
        curl_close($apiCurl);
        return $apiResponse;
    }

    public function reader_subscribe_get() {
        $token_info = $this->get_token_from_refresh_token();
        $access_token = $token_info['access_token'];
        $apiCurl1 = curl_init($this->aolGetUsersubscribe . "?f=json&access_token=" . $access_token);
        curl_setopt($apiCurl1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($apiCurl1, CURLOPT_FAILONERROR, false);
        curl_setopt($apiCurl1, CURLOPT_SSL_VERIFYPEER, false);

        $error1 = null;
        $apiResponse1 = curl_exec($apiCurl1);
        if (!$apiResponse1) {
            $error1 = curl_error($apiCurl1);
        }
        curl_close($apiCurl1);
        return $apiResponse1;
    }

    public function reader_subscribe_add() {
        if (isset($_GET['rss_url']) AND !empty($_GET['rss_url'])) {

            $token_info = $this->get_token_from_refresh_token();
            $access_token = $token_info['access_token'];

            $accessPost = "quickadd=" . $_GET['rss_url'] . "&access_token=" . $access_token;
            $accessCurl = curl_init($this->aolfeedsubscribe);
            curl_setopt($accessCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($accessCurl, CURLOPT_FAILONERROR, false);
            curl_setopt($accessCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($accessCurl, CURLOPT_POST, 1);
            curl_setopt($accessCurl, CURLOPT_POSTFIELDS, $accessPost);


            $error1 = null;
            $apiResponse = curl_exec($accessCurl);
            if (!$apiResponse) {
                $error1 = curl_error($accessCurl);
            }
            curl_close($apiCurl);
            return $apiResponse;
        } else {
            return "Missing GET fields";
        }
    }

}

?>

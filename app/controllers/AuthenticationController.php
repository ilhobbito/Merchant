<?php
namespace App\Controllers;

require '../vendor/autoload.php';

use Dotenv\Dotenv;

use Google_Client;

class AuthenticationController
{
    private $client;


    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();
    }
    public function buildClient(){

        $appId = $_ENV['FACEBOOK_APP_ID'];
        $appSecret = $_ENV['FACEBOOK_APP_SECRET'];
        $fb = new \Facebook\Facebook([
            'app_id' => $appId,
            'app_secret' =>  $appSecret,
            'default_graph_version' => 'v22.0',
        ]);
        return $fb;
    }
    
    public function index() {
        require_once '../app/views/authentication/login.php';
    }

    public function terms(){
        require_once '../app/views/authentication/terms.html';
    }

    public function googleLogin()
    { 
        $this->client = new Google_Client();
        $this->client->setPrompt('consent');
        $this->client->setAuthConfig('../client_secret.json');
        $this->client->setRedirectUri('https://127.0.0.1/Merchant/public/authentication/callback');
        $this->client->setScopes(['https://www.googleapis.com/auth/content',  //Google Merchant API
                                  'https://www.googleapis.com/auth/adwords']); // Google Ads API
        $this->client->setAccessType('offline');
    
        $auth_url = $this->client->createAuthUrl();
        echo "Login Called! " . $auth_url . "<br>"; 
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();
    }

    public function facebookLogin()
    {
        $fb = $this->buildClient();

        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email', 'catalog_management', 'business_management', 'ads_management', 'pages_show_list', 'whatsapp_business_management', 'pages_read_engagement', 'pages_manage_ads'];

        // The callback route for Facebook
        $callbackUrl = 'https://127.0.0.1/Merchant/public/authentication/facebookCallback';

        $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

        // Redirect to Facebook’s login page
        header('Location: ' . filter_var($loginUrl, FILTER_SANITIZE_URL));
        exit();
    }

    public function callback()
    {
        // Recreate the client here
        $client = new \Google_Client();
        $client->setAuthConfig('../client_secret.json');
        $client->setRedirectUri('https://127.0.0.1/Merchant/public/authentication/callback');
        $client->setScopes([
            'https://www.googleapis.com/auth/content',
            'https://www.googleapis.com/auth/adwords'
        ]);
        // Set the access type to offline to get a refresh token
    $client->setAccessType('offline');
        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $_SESSION['access_token'] = $token;
            $_SESSION['refresh_token'] = $token['refresh_token'];
            file_put_contents('token.json', json_encode($token));
            $configPath = __DIR__ . '/../../google_ads_php.ini'; 
            $configData = parse_ini_file($configPath, true);

            $configData['OAUTH2']['refreshToken'] = $token['refresh_token'];
            $this->writeIniFile($configData, $configPath);

            // Redirect to dashboard after successful authentication
            header('Location: /Merchant/public/dashboard');
            exit();

        } else {
            echo "Authorization failed!";
        }
    }
    // Function to write the INI file
    // This function takes an associative array and a file path as input, and writes the array to the specified INI file.
    private function writeIniFile(array $assoc, string $path): bool
{
    $content = '';
    foreach ($assoc as $section => $values) {
        $content .= "[$section]\n";
        foreach ($values as $key => $val) {
            $escapedVal = is_numeric($val) ? $val : '' . addslashes($val) . '';
            $content .= "$key = $escapedVal\n";
        }
        $content .= "\n";
    }
    return file_put_contents($path, $content) !== false;
}


    public function facebookCallback()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        };
        // Initialize the Facebook SDK
        $fb = $this->buildClient();
       
        // Get the helper
        $helper = $fb->getRedirectLoginHelper();
        
        try {
            // Get the access token from Facebook
            $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        // Check if we have a valid access token
        if (!isset($accessToken)) {
            echo 'Facebook authorization failed!';
            exit;
        }
        
        // Store the token in session or a file
        $_SESSION['fb_access_token'] = (string) $accessToken;
        file_put_contents('fb_token.json', json_encode(['access_token' => (string) $accessToken]));
        
        // Redirect to dashboard
        header('Location: /Merchant/public/fbdashboard');
        exit();
    }


}

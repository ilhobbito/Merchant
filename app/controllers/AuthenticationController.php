<?php
namespace App\Controllers;

require '../vendor/autoload.php';


use Google_Client;

class AuthenticationController
{
    private $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig('../client_secret.json');
        $this->client->setRedirectUri('http://127.0.0.1/authentication/callback');
        $this->client->addScope('https://www.googleapis.com/auth/content');
        $this->client->setAccessType('offline');
    }
    
    public function index() {
        require_once '../app/views/authentication/login.php';
    }
    public function login()
    { 
        $auth_url = $this->client->createAuthUrl();
        echo "Login Called! " . $auth_url . "<br>"; 
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit();
    }

    public function callback()
    {

        session_start();
        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            $_SESSION['access_token'] = $token;
            file_put_contents('token.json', json_encode($token));

            // Redirect to dashboard after successful authentication
            header('Location: /dashboard');
            exit();
        } else {
            echo "Authorization failed!";
        }
    }


}

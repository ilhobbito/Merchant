<?php
namespace App\Models;

use Google_Client;
use Google_Service_ShoppingContent;

class GoogleAuth
{
    private $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig('client_secret.json');

        if (file_exists('token.json')) {
            $token = json_decode(file_get_contents('token.json'), true);
            $this->client->setAccessToken($token);

            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents('token.json', json_encode($this->client->getAccessToken()));
            }
        }
    }

    public function getClient()
    {
        return $this->client;
    }
}
<?php
namespace App\Models;

use App\Controllers\FbdashboardController;

class User{

    public function getBusinessId($fbClient){

        try {
            $response = $fbClient->get('/me/businesses', $_SESSION['fb_access_token']);
            $businesses = $response->getDecodedBody();

            if (isset($businesses['data']) && count($businesses['data']) > 0) {
                // Selects the first business TODO: Make a selectable list or find a way to auto select the correct business
                $businessId = $businesses['data'][0]['id'];
        
                $_SESSION['business_id'] = $businessId;
        
                return $businessId;
                
            } else {
                echo "No businesses found for this user.";
            }
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
    }

    public function getAdsId($fbClient){

        try {
            $response = $fbClient->get('/me/adaccounts?fields=name,account_id', $_SESSION['fb_access_token']);
            $adAccountsData = $response->getDecodedBody();

            if (isset($adAccountsData['data']) && !empty($adAccountsData['data'])) {
                // Selects the first ads account TODO: Make a selectable list or find a way to auto select the correct ads account
                return $adAccountsData['data'][0]['account_id'];

            } else {
                echo "No ad accounts found for this user.";

            }
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
    }

    public function getAllCatalogs($fbClient){
        try {
            // Gets all catalogs
            $response = $fbClient->get("/{$_SESSION['business_id']}/owned_product_catalogs", $_SESSION['fb_access_token']);

            // GraphEdge is meant for lists of objects
            $catalogs = $response->getGraphEdge();
            return $catalogs;

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        
    }

    public function getAllProducts($catalogId, $fbClient){

        //$catalogs = $this->getAllCatalogs($fbClient);
        $response = $fbClient->get(
            "/{$catalogId}/products?fields=id,name,price,retailer_id", $_SESSION['fb_access_token']);
        $productsData = $response->getDecodedBody();
        return $productsData;
    
    }

    public function getProductSetsForCatalog($catalog_id, $accessToken) {

        $url = "https://graph.facebook.com/v22.0/{$catalog_id}/product_sets?fields=id,name,filter&access_token={$accessToken}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        if (isset($result['error'])) {
            echo "Error: " . $result['error']['message'];
            return [];
        }
        
        return $result['data'] ?? [];
    }

    public function getProductSetById($productSetId, $accessToken){
        $url = "https://graph.facebook.com/v22.0/{$productSetId}?fields=id,name,products{id,name,retailer_id,price}&access_token={$accessToken}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \RuntimeException("Facebook API error: " . $data['error']['message']);
        }

        // return a single product‐set as an associative array
        return $data;
    }

    public function getCatalogById($catalogId, $accessToken){
        $url = "https://graph.facebook.com/v22.0/{$catalogId}?fields=id,name&access_token={$accessToken}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \RuntimeException("Facebook API error: " . $data['error']['message']);
        }

        // return a single product‐set as an associative array
        return $data;
    }
    

}
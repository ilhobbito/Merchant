<?php
namespace App\Models;

use App\Controllers\FbdashboardController;

class User{

    public function getBusinessId($fb){

        try {
            $response = $fb->get('/me/businesses', $_SESSION['fb_access_token']);
            $businesses = $response->getDecodedBody();
            // $businesses should have a structure like:
            // [ 'data' => [ [ 'id' => '...', 'name' => '...', ... ], ... ] ]
            
            if (isset($businesses['data']) && count($businesses['data']) > 0) {
                // If the user has multiple businesses, you might want to list them.
                // For now, let's assume you pick the first one:
                $businessId = $businesses['data'][0]['id'];
        
                // Store it for later use
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

    public function getAdsId($fb){
        
        try {
            $response = $fb->get('/me/adaccounts?fields=name,account_id', $_SESSION['fb_access_token']);
            $adAccountsData = $response->getDecodedBody();

            if (isset($adAccountsData['data']) && !empty($adAccountsData['data'])) {

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

    public function getAllCatalogs($fb){
        try {
            
            // Gets all catalogs
            $response = $fb->get("/{$_SESSION['business_id']}/owned_product_catalogs", $_SESSION['fb_access_token']);

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

    public function getAllProducts($fb){

        $catalogs = $this->getAllCatalogs($fb);
        echo "<a href='/Merchant/public/fbdashboard'>Return</a><br><br>";
        foreach($catalogs as $catalog){
            try {
                $response = $fb->get(
                    "/{$catalog['id']}/products?fields=id,name,price,retailer_id", $_SESSION['fb_access_token']);
                $productsData = $response->getDecodedBody();
                echo "<h3>Products in " . $catalog['id'] . ": </h3>";    
                if (isset($productsData['data']) && !empty($productsData['data'])) {
                    foreach ($productsData['data'] as $product) {
                        
                        echo "Product ID: " . $product['id'] . "<br>";
                        echo "Name: " . $product['name'] . "<br>";
                        echo "Price: " . $product['price'] . "<br>";
                        echo "Retailer ID: " . $product['retailer_id'] . "<br><br>";
                    }
                    echo "___________________________________________________________<br><br>";
                } else {
                    echo "No products found in this catalog.<br>";
                }
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }
        }
    
    }

}
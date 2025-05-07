<?php
namespace App\Models;

use App\Controllers\FbdashboardController;

class User {

    
    // Retrieves the first Facebook Business ID associated with the authenticated user.
    // This is used to access catalogs, pixels, and ad-related resources tied to a business account.
    public function getBusinessId($fbClient) {
        try {
            // Make a Graph API call to fetch businesses associated with the current user
            $response = $fbClient->get('/me/businesses', $_SESSION['fb_access_token']);
            $businesses = $response->getDecodedBody();

            // Check if any businesses were returned
            if (isset($businesses['data']) && count($businesses['data']) > 0) {
                // Selects the first business in the list
                // Add logic to handle more than one business and be selectable
                $businessId = $businesses['data'][0]['id'];

                // Store in session for reuse across the app
                $_SESSION['business_id'] = $businessId;

                return $businessId;
            } else {
                echo "No businesses found for this user.";
            }

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Handles errors returned from Facebook's Graph API
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Handles local SDK errors (e.g., token issues, HTTP failures)
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        return null;
    }

    // Retrieves the first Facebook Ad Account ID associated with the authenticated user.
    // This is required for creating and managing campaigns, ad sets, creatives, and ads.
    public function getAdsId($fbClient) {
        try {
            // Make a Graph API request to fetch the user's ad accounts
            $response = $fbClient->get('/me/adaccounts?fields=name,account_id', $_SESSION['fb_access_token']);
            $adAccountsData = $response->getDecodedBody(); 
            
            // Check if ad accounts are returned
            if (isset($adAccountsData['data']) && !empty($adAccountsData['data'])) {
                // Selects the first ad account
                // TODO: Enhance by letting the user select from multiple accounts if available
                return $adAccountsData['data'][0]['account_id'];
            } else {
                echo "No ad accounts found for this user.";
            }

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Handles Graph API errors (e.g., permissions, invalid tokens)
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Handles local SDK issues (e.g., HTTP failures, misconfigurations)
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        return null;
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

    // Retrieves all products from a specific Facebook catalog.
    public function getAllProducts($catalogId, $fbClient) {
        // Send a GET request to retrieve product fields for the given catalog
        $response = $fbClient->get(
            "/{$catalogId}/products?fields=id,name,price,retailer_id",
            $_SESSION['fb_access_token']
        );

        // Decode the response to a PHP array
        $productsData = $response->getDecodedBody();

        // Return raw product data (Facebook returns it under the 'data' key)
        return $productsData;
    }


    // Retrieves all product sets belonging to a specific Facebook catalog.
    // Each product set can be used for filtering products in ads (e.g., on availability, brand).
    public function getProductSetsForCatalog($catalog_id, $accessToken) {

        // Build the Graph API URL with required fields
        $url = "https://graph.facebook.com/v22.0/{$catalog_id}/product_sets?fields=id,name,filter&access_token={$accessToken}";

        // Initialize and execute a simple cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the JSON response into an associative array
        $result = json_decode($response, true);

        // Handle potential API error response
        if (isset($result['error'])) {
            echo "Error: " . $result['error']['message'];
            return [];
        }

        // Return the list of product sets, or empty array if 'data' is missing
        return $result['data'] ?? [];
    }


    
    // Retrieves a single product set by ID, including its basic product information.
    // Throws an exception if the Facebook API returns an error.
    public function getProductSetById($productSetId, $accessToken) {
        // Build the Graph API URL to fetch the product set with embedded product info
        $url = "https://graph.facebook.com/v22.0/{$productSetId}?fields=id,name,products{id,name,retailer_id,price,image_link}&access_token={$accessToken}";

        // Initialize and execute cURL request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Throw a runtime exception if the response contains an error
        if (isset($data['error'])) {
            throw new \RuntimeException("Facebook API error: " . $data['error']['message']);
        }

        // Return the full product set data (including embedded product array)
        return $data;
    }


    // Retrieves all products belonging to a specific product set.
    // This is commonly used when previewing or applying actions to a filtered group of products.
    public function getProductSetProducts($productSetId, $accessToken) {
        // Build the Graph API URL to fetch products in the given product set
        $url = "https://graph.facebook.com/v22.0/{$productSetId}/products?fields=id,name,retailer_id,price,images&access_token={$accessToken}";

        // Execute the API request using cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response
        $data = json_decode($response, true);

        // If Facebook returned an error, throw an exception
        if (isset($data['error'])) {
            throw new \RuntimeException("Facebook API error: " . $data['error']['message']);
        }

        // Return the list of products (found under the 'data' key)
        return $data['data'];
    }

    

    // Retrieves basic information (ID and name) for a specific Facebook product catalog.
    public function getCatalogById($catalogId, $accessToken) {
        // Build the API URL to fetch catalog details
        $url = "https://graph.facebook.com/v22.0/{$catalogId}?fields=id,name&access_token={$accessToken}";

        // Perform the HTTP request via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the response to an associative array
        $data = json_decode($response, true);

        // Throw an exception if Facebook returned an error
        if (isset($data['error'])) {
            throw new \RuntimeException("Facebook API error: " . $data['error']['message']);
        }

        // Return the catalog details (e.g., ['id' => ..., 'name' => ...])
        return $data;
    }
}
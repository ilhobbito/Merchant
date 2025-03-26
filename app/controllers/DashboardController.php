<?php
namespace App\Controllers;

require '../vendor/autoload.php';

use Google_Client;
use Google_Service_ShoppingContent;
use Google_Service_ShoppingContent_Product;
use Google_Service_ShoppingContent_Price;

use Dotenv\Dotenv;

class DashboardController
{
    private $client;

    public function __construct(){

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();
        
        // Check if Google token is set
        if (isset($_SESSION['google_access_token'])) {
            // Initialize Google client
            $this->client = new Google_Client();
            $this->client->setApplicationName('Google-Merchant-API-Test');
            $this->client->setAccessToken($_SESSION['google_access_token']);

            if ($this->client->isAccessTokenExpired()) {
                echo "Google access token expired. Please re-authenticate.";
                exit();
            }
        }
        // Or else if Facebook token is set
        elseif (isset($_SESSION['fb_access_token'])) {
            // Possibly initialize Facebook SDK or store the token for FB API calls
            // e.g., $this->fb = new \Facebook\Facebook([...]);
            // $this->fb->setDefaultAccessToken($_SESSION['fb_access_token']);
        }
        else {
            // Neither Google nor Facebook token is set
            echo "No valid access token found. Please authenticate first.";
            exit();
        }

    }

    public function index(){
        require_once '../app/views/dashboard/index.php';
    }

    public function createTestProduct(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        require_once '../app/views/dashboard/create-test-product.php';
        } else {
        
        $client = new Google_Client();
        $client->setApplicationName('Google-Merchant-API-Test');
        $client->setAccessToken($_SESSION['google_access_token']);

        $service = new Google_Service_ShoppingContent($client);
        $merchantId = $_ENV['MERCHANT_ID']; // Replace with merchandId
        // $_SESSION['last_created_product_id'] = $insertedProduct->getId(); // Store product ID in session

        $product = new Google_Service_ShoppingContent_Product();
        // Required fields for the product
        $product->setOfferId("dummy_003");
        $product->setTitle("Dummy Test Product");
        $product->setDescription("This is a dummy product for testing purposes.");
        $product->setLink("http://example.com/dummy-product");
        $product->setImageLink("http://example.com/images/dummy-product.jpg");
        $product->setContentLanguage("en");
        $product->setTargetCountry("US");
        $product->setChannel("online");

        // Availability and condition
        $product->setAvailability("in stock");
        $product->setCondition("new");

        // Set price
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue("9.99");
        $price->setCurrency("USD");
        $product->setPrice($price);

        // Insert the product into your Merchant Center account
        try {
            $insertedProduct = $service->products->insert($merchantId, $product);
            $_SESSION['last_created_product_id'] = $insertedProduct->getId(); // Store product ID
            echo "Product added successfully!<br>";
            echo "Product ID: " . $insertedProduct->getId();
            echo "Offer ID:  " . $insertedProduct->getOfferId();
            echo "<br><br>It might take some time for the product to show up in the feed, please wait a moment if you don't see it and refresh";
            echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
        } catch (\Exception $e) {
            echo "An error occurred while adding the product: " . $e->getMessage();
            echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
        }
    }
    }

    public function editTestProduct() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once '../app/views/dashboard/edit-product.php';
        } else {
            $client = new Google_Client();
            $client->setApplicationName('Google-Merchant-API-Test');
            $client->setAccessToken($_SESSION['google_access_token']);
    
            $service = new Google_Service_ShoppingContent($client);
            $merchantId = $_ENV['MERCHANT_ID'];
    
            $productId = $_SESSION['last_created_product_id'] ?? $_POST['productId'];
            $updatedTitle = $_POST['title'] ?? ''; // Fallback till tom sträng om ej satt
            $updatedDescription = $_POST['description'] ?? '';
            $updatedPrice = $_POST['price'] ?? null; // Null om ej satt
            $updatedCurrency = $_POST['currency'] ?? null;
    
            try {
                // Validera att pris och valuta är satta och giltiga
                if (empty($updatedPrice) || empty($updatedCurrency)) {
                    throw new \Exception("Price and currency are required fields.");
                }
    
                // Skapa ett nytt produkt-objekt
                $product = new Google_Service_ShoppingContent_Product();
                $product->setTitle($updatedTitle);
                $product->setDescription($updatedDescription);
    
                // Sätt priset
                $price = new Google_Service_ShoppingContent_Price();
                $price->setValue($updatedPrice); // T.ex. "9.99"
                $price->setCurrency($updatedCurrency); // T.ex. "USD"
                $product->setPrice($price);
    
                // Uppdatera produkten i Merchant Center
                $updatedProduct = $service->products->update($merchantId, $productId, $product);
    
                $_SESSION['last_updated_product_id'] = $updatedProduct->getId();
                echo "Product updated successfully!<br>";
                echo "Product ID: " . $updatedProduct->getId();
                echo "<br><br>Changes might take some time to reflect. Please refresh the feed.";
                echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
            } catch (\Exception $e) {
                echo "An error occurred while updating the product: " . $e->getMessage();
                echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
            }
        }
    }

    public function listProducts(){


        $service = new Google_Service_ShoppingContent($this->client);
        $merchantId = $_ENV['MERCHANT_ID']; // Replace with merchandId

        try {
            // List products for the specified Merchant Center account
            $productsResponse = $service->products->listProducts($merchantId);
            $products = $productsResponse->getResources();
            require_once '../app/views/dashboard/list-products.php';
            
            // if (!empty($products)) {
            //     foreach ($products as $product) {
            //         echo "Product ID: " . $product->getId() . "<br>";
            //     }
            // } else {
            //     echo "No products found.";
            // }
            // echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
            echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
        }
    }
   
    public function logout(){
        unset($_SESSION['google_access_token']);
        unset($_SESSION['fb_access_token']);
        session_destroy();
        header('Location: /Merchant/public/');
    }
}
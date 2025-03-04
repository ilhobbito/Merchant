<?php
namespace App\Controllers;

require '../vendor/autoload.php';


use Google_Client;
use Google_Service_ShoppingContent;
use Google_Service_ShoppingContent_Product;
use Google_Service_ShoppingContent_Price;

class DashboardController
{
    private $client;

    public function __construct(){

        if (!isset($_SESSION['access_token'])) {
            echo "Access token not found. Please authenticate first.";
            exit();
        }

        $this->client = new Google_Client();
        $this->client->setApplicationName('Google-Merchant-API-Test');
        $this->client->setAccessToken($_SESSION['access_token']);

        if ($this->client->isAccessTokenExpired()) {
            echo "Access token expired. Please re-authenticate.";
            exit();
        }        
    }


    public function index(){
        require_once '../app/views/dashboard/index.php';
    }

    public function addTestProduct(){

        $client = new Google_Client();
        $client->setApplicationName('Google-Merchant-API-Test');
        $client->setAccessToken($_SESSION['access_token']);

        $service = new Google_Service_ShoppingContent($client);
        $merchantId = '';

        // Create a dummy product object.
        $product = new Google_Service_ShoppingContent_Product();

        // Required fields for the product.
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

        // Set price. Price must be provided as a Price object.
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue("9.99");
        $price->setCurrency("USD");
        $product->setPrice($price);

        // Optionally add additional fields as required by your Merchant Center account.
        try {
            // Insert the product into your Merchant Center account.
            $insertedProduct = $service->products->insert($merchantId, $product);
            echo "Product added successfully!<br>";
            echo "Product ID: " . $insertedProduct->getId();
            echo "<br><br>It might take some time for the product to show up in the feed, please wait a moment if you don't see it and refresh";
            echo "<a href='/dashboard'><br>Return</a>";
        } catch (\Exception $e) {
            echo "An error occurred while adding the product: " . $e->getMessage();
            echo "<a href='/dashboard'><br>Return</a>";
        }
    }


    public function listProducts(){

        $service = new Google_Service_ShoppingContent($this->client);
        $merchantId = '';
        try {
            // List products for the specified Merchant Center account
            $productsResponse = $service->products->listProducts($merchantId);
            $products = $productsResponse->getResources();
            
            if (!empty($products)) {
                foreach ($products as $product) {
                    echo "Product ID: " . $product->getId() . "<br>";
                    // You can display additional product details as needed
                }
            } else {
                echo "No products found.";
            }
            echo "<a href='/dashboard'><br>Return</a>";
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
            echo "<a href='/dashboard'><br>Return</a>";
        }
    }
   

    public function logout(){
        session_destroy();
        header('Location: /authentication');
    }
}
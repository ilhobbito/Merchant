<?php
namespace App\Controllers;

session_start();
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

    public function createTestProduct() {
        require_once '../app/views/dashboard/create-test-product.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product = new Google_Service_ShoppingContent_Product();
            $product->setOfferId($_POST['offerId']);
            $price = new Google_Service_ShoppingContent_Price();
    
            if (!isset($_POST['price']) || !isset($_POST['currency'])) {
                die("Error: Price and currency are required.");
            }
            if (!is_numeric($_POST['price']) || $_POST['price'] <= 0) {
                die("Error: Price must be a positive numeric value.");
            }
    
            $price->setValue(floatval($_POST['price']));
            $price->setCurrency($_POST['currency']);
            $product->setPrice($price);
    
            $product->setTitle($_POST['title']);
            $product->setDescription($_POST['description']);
            $product->setLink($_POST['link']);
            $product->setImageLink($_POST['imageLink']);
            $product->setAvailability($_POST['availability']);
            $product->setCondition($_POST['condition']);
            $product->setContentLanguage("en");
            $product->setTargetCountry("US");
            $product->setChannel("online");
            $product->setGtin("1234567890123");
            $product->setBrand("TestBrand");
    
            try {
                $service = new Google_Service_ShoppingContent($this->client);
                $merchantId = $_ENV['MERCHANT_ID'];
    
                $insertedProduct = $service->products->insert($merchantId, $product);
    
                if (!isset($_SESSION['products']) || !is_array($_SESSION['products'])) {
                    $_SESSION['products'] = [];
                }
    
                // Använd data från $product och $insertedProduct istället för att hämta med get
                $_SESSION['products'][] = [
                    'id' => $insertedProduct->getId(),
                    'offerId' => $insertedProduct->getOfferId(),
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'link' => $product->getLink(),
                    'imageLink' => $product->getImageLink(),
                    'availability' => $product->getAvailability(),
                    'condition' => $product->getCondition(),
                    'price' => [
                        'value' => $price->getValue(),
                        'currency' => $price->getCurrency()
                    ]
                ];
    
                echo "Product added successfully!";
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }


    public function editTestProduct() {
        require_once '../app/views/dashboard/edit-product.php';
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
    
        $client = new Google_Client();
        $client->setApplicationName('Google-Merchant-API-Test');
        $client->setAccessToken($_SESSION['google_access_token']);
    
        if ($client->isAccessTokenExpired()) {
            die("Error: Access token expired.");
        }
    
        $service = new Google_Service_ShoppingContent($client);
        $merchantId = $_ENV['MERCHANT_ID'];
    
        $productId = $_POST['productId'] ?? null;
        if (!$productId) {
            die("Error: Product ID is required.");
        }
    
        try {
            // Hämta befintlig produkt från Google Merchant Center
            $existingProduct = $service->products->get($merchantId, $productId);
    
            // Skapa ett nytt produkt-objekt
            $product = new Google_Service_ShoppingContent_Product();
            $product->setTitle($_POST['title'] ?? $existingProduct->getTitle());
            $product->setDescription($_POST['description'] ?? $existingProduct->getDescription());
            $product->setLink($existingProduct->getLink() ?? 'https://www.example.com/');
            $product->setImageLink($existingProduct->getImageLink() ?? 'https://www.example.com/');
            $product->setAvailability($existingProduct->getAvailability() ?? 'in stock');
            $product->setCondition($existingProduct->getCondition() ?? 'new');
            $product->setGtin($existingProduct->getGtin() ?? '1234567890123');
            $product->setBrand($existingProduct->getBrand() ?? 'TestBrand');
    
            $price = new Google_Service_ShoppingContent_Price();
            $price->setValue(floatval($_POST['price'] ?? $existingProduct->getPrice()->getValue()));
            $price->setCurrency($_POST['currency'] ?? $existingProduct->getPrice()->getCurrency());
            $product->setPrice($price);
    
            // Uppdatera produkten i Merchant Center
            $updatedProduct = $service->products->update($merchantId, $productId, $product);
    
            echo "Product updated successfully!<br>";
            echo "Product ID: " . $updatedProduct->getId();
            echo "<br><br>Changes might take some time to reflect. Please refresh the feed.";
        } catch (\Exception $e) {
            echo "An error occurred while updating the product: " . $e->getMessage();
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
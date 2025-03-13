<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

// To avoid undefined key array warning from displaying. 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 1);



class FbdashboardController{
    private $accessToken;
    private $data = [
        'app_id' => '1500469780921483',
        'app_secret' => '47a7df4f77564bf52e559f6c50c093e1',
        'fb_access_token' => '',
        'business_id' => '1684232715804793'
    ];
    public function __construct()
    {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Now you can safely assign the session token
        if (isset($_SESSION['fb_access_token'])) {
            $this->data['fb_access_token'] = $_SESSION['fb_access_token'];
        }
    }

    public function index(){
        require_once __DIR__ . '/../views/fbdashboard/index.php';
    }
    public function buildClient(){

        $fb = new \Facebook\Facebook([
            'app_id' => '1500469780921483',
            'app_secret' => '47a7df4f77564bf52e559f6c50c093e1',
            'default_graph_version' => 'v22.0',
        ]);
        return $fb;
    }

    // A very simple API test that just returns a greeting to the authenticated user via the accessToken
    public function apiTest(){
        
        $fb = $this->buildClient();
        //$accessToken = $_SESSION['fb_access_token'];
        try {
            $response = $fb->get('/me?fields=id,name,email', $this->data['fb_access_token']);
            $user = $response->getGraphUser();
            echo '<h4>Hello, ' . $user->getName() . '</h4>';
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
        
    }

    public function createTestCatalog()
    {   

        $fb = $this->buildClient();

        try {
            // Create a new catalog TODO: Make the user input a name instead of having it hardcoded as Test Catalog
            $response = $fb->post("/{$this->data['business_id']}/owned_product_catalogs", [
                'name' => 'Test Catalog #3'
            ], $this->data['fb_access_token']);

            //GraphNode is meant for a single object
            $catalog = $response->getGraphNode();
            echo "Catalog created with ID: " . $catalog['id'];
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";
        
    }

    public function listAllCatalogs(){

        $fb = $this->buildClient();
        try {
            
            // Gets all catalogs
            $response = $fb->get("/{$this->data['business_id']}/owned_product_catalogs", $this->data['fb_access_token']);

            // GraphEdge is meant for lists of objects
            $catalogs = $response->getGraphEdge();
            $x = 1;
            foreach( $catalogs as $catalog){
                echo "Catalog #" . $x . ", Name: " . $catalog['name'] . ", Id: " , $catalog['id'] . "<br>";
                $x++;
            }

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";
    }

    public function createTestProduct(){     
        $fb = $this->buildClient();

        try {
            $response = $fb->post(
                //TODO: Make the catalog id a variable
                "/1803639413748271/products",
                [
                    // Retailer ID needs to be unique or an error about duplicate id's will show.
                    'retailer_id' => 'test-product-1', 
                    'name'        => 'Test product',
                    'description' => 'This is a test product',
                    'image_url'   => 'https://example.com/images/product.jpg',
                    'url'         => 'https://example.com/product/1',
                    'price'       => '199',
                    'currency'    => 'SEK',
                    'availability'=> 'in stock',
                ],
                $this->data['fb_access_token']
            );

            $result = $response->getDecodedBody();
            echo "New product added! ID: " . $result['id'];

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
    }
}


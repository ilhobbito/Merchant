<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

// To avoid undefined key array warning from displaying. 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 1);

use Dotenv\Dotenv;
use App\Models\Campaign;
use App\Models\User;

class FbdashboardController{

    private $data = [];
    private $accessToken;

    public function __construct()
    { 
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();

        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['fb_access_token'])) {
            $this->data['fb_access_token'] = $_SESSION['fb_access_token'];
        }
        $this->data['app_id'] = $_ENV['FACEBOOK_APP_ID'];
        $this->data['app_secret'] = $_ENV['FACEBOOK_APP_SECRET'];
        $fb = $this->buildClient();
        $user = new User();
        $this->data['business_id'] =  $user->getBusinessId($fb);
        $this->data['ads_id'] = "act_" . $user->getAdsId($fb);
    }

    public function index(){
        require_once __DIR__ . '/../views/fbdashboard/index.php';
    }

    public function buildClient() {
        return new \Facebook\Facebook([
            'app_id'                => $this->data['app_id'],
            'app_secret'            => $this->data['app_secret'],
            'default_graph_version' => 'v22.0',
        ]);
    }

    // A very simple API test that just returns a greeting to the authenticated user via the accessToken
    public function apiTest(){
        
        $fb = $this->buildClient();
        $message;

        try {
            $response = $fb->get('/me?fields=id,name,email', $this->data['fb_access_token']);
            $user = $response->getGraphUser();
            $message = 'Hello, ' . $user->getName();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }  
        require_once __DIR__ . '/../views/fbdashboard/api-test.php';     
    }
    
    // Makes an API request to Post a catalog to the users ads account
    public function createCatalog()
    {   
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/fbdashboard/create-catalog.php';
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fb = $this->buildClient();
            
            try {
                $response = $fb->post("/{$this->data['business_id']}/owned_product_catalogs", [
                    'name' => $_POST['catalog_name'] 
                ], $this->data['fb_access_token']);

                //GraphNode is meant for a single object
                $catalog = $response->getGraphNode();
                echo "Catalog " . $_POST['catalog_name'] . "    created with ID: " . $catalog['id'];
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";
        }
    }

    // Makes an API request to retrieve all the catalogs connected the users business account
    public function listCatalogs(){
        $fb = $this->buildClient();
        $user = new User();
        $catalogs = $user->getAllCatalogs($fb);
        require_once __DIR__ . '/../views/fbdashboard/list-catalogs.php';
    }

    // Makes an API request to Post a product to the users business account
    public function createProduct()
    {     
        $fb = $this->buildClient();
        $user = new User();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {   
            $catalogs = $user->getAllCatalogs($fb);
            require_once __DIR__ . '/../views/fbdashboard/create-product.php';
        }
        
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                
                $response = $fb->post(
                    // Select_catalog is the ID of the <select> options in the view page
                    "/{$_POST['select_catalog']}/products",
                    [
                        // Retailer ID needs to be unique or an error about duplicate id's will show.
                        'retailer_id' => $_POST['product_id'], 
                        'name'        => $_POST['product_name'],
                        'description' => $_POST['description'],
                        'image_url'   => $_POST['image_url'],
                        'url'         => $_POST['product_url'],
                        'price'       => $_POST['price'],
                        'currency'    => $_POST['currency'],
                        'availability'=> $_POST['availability'],
                    ],
                    $this->data['fb_access_token']
                );

                $result = $response->getDecodedBody();
                echo "New product " . $_POST['product_name'] . " with ID: " . $result['id'] . "<br> was successfully added to catalog " . $_POST['select_catalog'];
                echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";

            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }
        }
    }

    // Retrieves all the products connected the users business account
    public function listAllProducts(){
        $fb = $this->buildClient();
        $user = new User();
        $catalogs = $user->getAllProducts($fb);
        require_once __DIR__ . '/../views/fbdashboard/list-products.php';

    }

    // Makes an API request to Post a campaign to the users Ads Account
    public function createCampaign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        require_once __DIR__ . '/../views/fbdashboard/create-campaign.php';
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/campaigns";

            if(!isset($_POST['campaign_name'])){
                echo "A campaign name must be set!";
                return;
            }
        
            // Create a new cURL resource
            $ch = curl_init($url);

            // Set the POST fields
            $postFields = [
                'name'         => $_POST['campaign_name'],
                'objective'    => $_POST['objective'] ?? 'OUTCOME_TRAFFIC',    
                'status'       => $_POST['status'] ?? 'PAUSED',
                'special_ad_categories' => json_encode([]),
                'access_token' => $this->data['fb_access_token']
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

            // Return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request
            $response = curl_exec($ch);

            // Check for cURL errors
            if ($error = curl_error($ch)) {
                echo "cURL Error: " . $error;
            } else {
                echo "Campaign \"" .$_POST['campaign_name'] . "\" with ID '" . $response . "' successfully created<br>";
                echo "Status: "  . $_POST['status'] . "     Objective: " . $_POST['objective'] . "<br>";
                echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
            }
            // Close cURL resource
            curl_close($ch);
        }
    }

    // Makes an API request to Post an Ad Set to the users Ads Account
    public function createAdSet()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $cm = new Campaign();
            $campaigns = $cm->getCampaigns($this->data['ads_id'], $this->data['fb_access_token']);
            if (isset($campaigns['error'])) {
                echo "Error fetching campaigns: " . $campaigns['error']['message'];
                return;
            }
            require_once __DIR__ . '/../views/fbdashboard/create-adset.php';    
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adsets";
            $ch = curl_init($url);

            if(!isset($_POST['adset_name'])){
                echo "A name for the Adset must be set!";
                return;
            }

            $postFields = [
                'name'            => $_POST['adset_name'],
                'campaign_id'     => $_POST['campaign_id'], 
                'daily_budget'    => $_POST['daily_budget'] ?? 1500, // Daily budget is in the smallest currency unit. Set cost like this, "1000" = 10.00
                'billing_event'   => $_POST['billing_event'] ?? 'IMPRESSIONS',
                'bid_strategy'    => $_POST['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
                'optimization_goal' => $_POST['optimization_goal'] ?? 'LINK_CLICKS',
                'targeting'       => json_encode([
                    'geo_locations' => [
                        'countries' => ['SE']  
                    ]
                ]),
                'dsa_beneficiary'     => $_POST['dsa_beneficiary'], // The Facebook Page or entity benefiting
                'dsa_payor'           => $_POST['dsa_payor'],  // The Facebook Page or entity paying
                'status'          => $_POST['status'] ?? 'PAUSED',
                'access_token'    => $this->data['fb_access_token']
            ];

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            if ($error = curl_error($ch)) {
                echo "cURL Error: " . $error;
            } else {
                echo "Ad Set " . $_POST['adset_name'] . " with Id: " . $response . " succesfully created!<br>";
                echo "Daily limit set to: " . $_POST['daily_budget'] . " with Billing events set to " . $_POST['billing_event'] . "!<br>";
                echo "Bid strategy: " . $_POST['bid_strategy'] . "      Optimization Goal: " . $_POST['optimization_goal'] . ".!<br>";
                echo "Target Country: SE, Sweden [by default]<br>";
                echo "DSA Beneficary: " . $_POST['dsa_beneficiary'] . "         DSA Payor: " . $_POST['dsa_payor'] . "<br>";
                echo "Ad set Status: " . $_POST['status'] . "<br>";
                echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
            }

        curl_close($ch);
        }
        
    }

    // Makes an API request to Post an Ad Creative to the users Ads account
    public function createAdCreative()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/fbdashboard/create-adcreative.php';
        }
        else{
            $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adcreatives";
            $ch = curl_init($url);
    
            $objectStorySpec = [
                'page_id'   => $_POST['page_id'] ?? '', 
                'link_data' => [
                    'link'    => $_POST['link'] ??'https://www.example.com/',
                    'message' => $_POST['message'] ??'Check out our amazing offer!'
                ]
            ];
    
            $postFields = [
                'name'               => $_POST['creative_name'] ?? 'MyAdCreative',
                'object_story_spec'  => json_encode($objectStorySpec),
                'access_token'       => $this->data['fb_access_token']
            ];
    
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
    
            if ($error = curl_error($ch)) {
                echo "cURL Error: " . $error;
            } else {
                echo "Ad Creative " . $_POST['creative_name'] . " with ID: " . $response . " was successfully created!<br>";
                echo "Link: " . $_POST['link'] . "      " . $_POST['page_id'] . "<br>Ad Message: \"" . $_POST['message'] . "\".";  
            }
    
            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
            curl_close($ch);
        }
       
    }

    // Makes an API request to Post an actual Advertisement to the users Ads Account
    public function createAdvertisement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $cm = new Campaign();
            $adSets = $cm->getAdSets($this->data['ads_id'], $this->data['fb_access_token']);
            $adCreatives = $cm->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);
            require_once __DIR__ . '/../views/fbdashboard/create-advertisement.php';
        }
        else{
            $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/ads";
            $ch = curl_init($url);
    
            if(isset($_POST['adset_id']) && isset($_POST['adcreative_id'])){
                $postFields = [
                    'name'     => $_POST['ad_name'],
                    'adset_id' => $_POST['adset_id'],
                    // The 'creative' field expects a JSON object containing 'creative_id'
                    'creative' => json_encode([
                        'creative_id' => $_POST['adcreative_id'] ?? $creativeId
                    ]),
                    // Keep it PAUSED to avoid going live immediately and having to pay
                    'status'   => $_POST['status'] ?? 'PAUSED',
                    'access_token' => $this->data['fb_access_token']
                ];
            }
            else{
                echo "Ad Set and Ad Creative requires Id's";
                return;
            }
           
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            $response = curl_exec($ch);
            if ($error = curl_error($ch)) {
                echo "cURL Error: " . $error;
            } else {
                echo "Ad " . $_POST['ad_name'] . " with Id: " . $response . " was successfully created!<br>";
                echo "This ad belongs to Ad Set with Id: " . $_POST['adset_id'] . " and Ad Creative with Id: " . $_POST['adcreative_id'] . ".<br>";
                echo "Status: " . $_POST['status'];
                echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
            }
            curl_close($ch);
        }
    }

    // Makes an API request to retrieve some basic info about the ads account
    public function checkAdAccount()
    {
        $fb = $this->buildClient();
        $message;
        try {
            $response = $fb->get(
                "/{$this->data['ads_id']}?fields=name,currency",
                $this->data['fb_access_token']
            );
            $adAccount = $response->getDecodedBody();
            $message = json_encode($adAccount);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        require_once __DIR__ . '/../views/fbdashboard/check-ad-account.php'; 
        
    }

    // Makes an API request to retrieve a pixel connected to the users account if there is any
    public function getPixel()
    {
        $fb = $this->buildClient();
        $message;
        try {
            $response = $fb->get(
                "/{$this->data['ads_id']}/adspixels?fields=id,name",
                $this->data['fb_access_token']
            );
            $pixels = $response->getDecodedBody();
            $message = json_encode($pixels);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }
        require_once  __DIR__ . '/../views/fbdashboard/get-pixel.php'; 
    }
  
}
 
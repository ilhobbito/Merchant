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
  
        //$this->data['catalog_id'] = '';
        //$this->data['campaign_id'] = '';
        //$this->data['fb_page_access_token'] = '';
       
    }

    public function index(){
        require_once __DIR__ . '/../views/fbdashboard/index.php';
    }

    public function routing(){
        $cm = new Campaign();
        $user = new User();
        $fb = $this->buildClient();

        if (isset($_GET['value'])) {
            $value = $_GET['value']; 
            
            if($value == 1){
                require_once __DIR__ . '/../views/fbdashboard/create-campaign.php';
            }
            elseif($value == 2){
                $this->createAdSet();
                require_once __DIR__ . '/../views/fbdashboard/create-adset.php';
            }
            elseif($value == 3){
                require_once __DIR__ . '/../views/fbdashboard/create-adcreative.php';
            }
            elseif($value == 4){
                
                $adSets = $cm->getAdSets($this->data['ads_id'], $this->data['fb_access_token']);
                $adCreatives = $cm->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);
                require_once __DIR__ . '/../views/fbdashboard/create-advertisement.php';
            }
            elseif($value == 5){
                $catalogs = $user->getAllCatalogs($fb);
                require_once __DIR__ . '/../views/fbdashboard/create-product.php';
            }
            elseif($value == 6){
                require_once __DIR__ . '/../views/fbdashboard/create-catalog.php';
            }
            elseif($value == 7){
                $catalogs = $user->getAllCatalogs($fb);
                require_once __DIR__ . '/../views/fbdashboard/list-catalogs.php';
            }
            elseif($value == 8){
                $catalogs = $user->getAllProducts($fb);
                require_once __DIR__ . '/../views/fbdashboard/list-products.php';
            }
        }
    }

  
    public function buildClient() {
        return new \Facebook\Facebook([
            'app_id'                => $this->data['app_id'],
            'app_secret'            => $this->data['app_secret'],
            'default_graph_version' => 'v22.0',
        ]);
    }



    public function checkAdAccount()
    {
        $fb = $this->buildClient();
        try {
            $response = $fb->get(
                "/{$this->data['ads_id']}?fields=name,currency",
                $this->data['fb_access_token']
            );
            $adAccount = $response->getDecodedBody();
            print_r($adAccount);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
        echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
    }
  

    public function getPixel()
    {
        $fb = $this->buildClient();
        try {
            $response = $fb->get(
                "/{$this->data['ads_id']}/adspixels?fields=id,name",
                $this->data['fb_access_token']
            );
            $pixels = $response->getDecodedBody();
            print_r($pixels);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
        echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
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

    public function createCatalog()
    {   

        $fb = $this->buildClient();

        try {
            // Create a new catalog TODO: Make the user input a name instead of having it hardcoded as Test Catalog
            $response = $fb->post("/{$this->data['business_id']}/owned_product_catalogs", [
                'name' => $_POST['catalog_name'] 
            ], $this->data['fb_access_token']);

            //GraphNode is meant for a single object
            $catalog = $response->getGraphNode();
            echo "Catalog " . $_POST['catalog_name'] . "    created with ID: " . $catalog['id'];
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

    public function listCatalogs(){
        $fb = $this->buildClient();
        $user = new User();
        $catalogs = $user->getAllCatalogs($fb);
    }

    public function listAllProducts(){
        $fb = $this->buildClient();
        $user = new User();
        $catalogs = $user->getAllProducts($fb);
    }



    public function createProduct()
    {     
        $fb = $this->buildClient();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $response = $fb->post(
                    //TODO: Make the catalog id a variable
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
        else{
            echo "Something has gone wrong...";
        }
    }

    public function createCampaign()
    {
        // Replace <YOUR_AD_ACCOUNT_ID> and <YOUR_ADS_MANAGEMENT_TOKEN> with real values
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
       
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adsets";
            $ch = curl_init($url);

            if(!isset($_POST['adset_name'])){
                echo "A name for the Adset must be set!";
                return;
            }

            $postFields = [
                'name'            => $_POST['adset_name'],
                'campaign_id'     => $_POST['campaign_id'], //'120219705537980468',
                // Daily budget is in the smallest currency unit. For USD, "1000" = $10.00
                'daily_budget'    => $_POST['daily_budget'] ?? 1000,
                'billing_event'   => $_POST['billing_event'] ?? 'IMPRESSIONS',
                'bid_strategy'    => $_POST['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
   
                'optimization_goal' => $_POST['optimization_goal'] ?? 'LINK_CLICKS',
                'targeting'       => json_encode([
                    'geo_locations' => [
                        'countries' => ['SE']  // or your target country codes
                    ]
                ]),
                'dsa_beneficiary'     => $_POST['dsa_beneficiary'],//'551570511380996',  // The Page or entity benefiting
                'dsa_payor'           => $_POST['dsa_payor'], //'551570511380996',  // The Page or entity paying
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

    public function createAdCreative()
    {
        $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adcreatives";
        $ch = curl_init($url);

        // Replace <PAGE_ID> with a page you manage
        $objectStorySpec = [
            'page_id'   => $_POST['page_id'] ?? '', // 551570511380996
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
            echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
        }

        curl_close($ch);
    }

    public function createAdvertisement()
    {
        $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/ads";
        $ch = curl_init($url);

        // Replace with the IDs from your previous steps
        $adSetId   = '120219705756250468';  // from "Ad Set creation response"
        $creativeId = '508791218715591';    // from "Ad Creative creation response"

        if(isset($_POST['adset_id']) && isset($_POST['adcreative_id'])){
            $postFields = [
                'name'     => $_POST['ad_name'] ?? 'MyAd',
                'adset_id' => $_POST['adset_id'] ?? $adSetId,
                // The 'creative' field expects a JSON object containing 'creative_id'
                'creative' => json_encode([
                    'creative_id' => $_POST['adcreative_id'] ?? $creativeId
                ]),
                // Keep it PAUSED to avoid going live immediately
                'status'   => $_POST['status'] ?? 'PAUSED',
                'access_token' => $this->data['fb_access_token']
            ];
        }
        else{
            echo "Ad set and Ad Creative requires Id's";
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
 

    // public function checkCatalog()
    // {
    //     $fb = $this->buildClient();
    //     try {
    //         $response = $fb->get(
    //             "/{$this->data['business_id']}/owned_product_catalogs?fields=id,name",
    //             $this->data['fb_access_token']
    //         );
    //         $catalogs = $response->getDecodedBody();
    //         print_r($catalogs);
    //     } catch (\Facebook\Exceptions\FacebookResponseException $e) {
    //         echo 'Graph returned an error: ' . $e->getMessage();
    //     } catch (\Facebook\Exceptions\FacebookSDKException $e) {
    //         echo 'Facebook SDK returned an error: ' . $e->getMessage();
    //     }
    // }   

 // public function createTestCampaign()
    // {
    //     $fb = $this->buildClient();
    //     try {
    //         // Create Campaign
    //         $campaignResponse = $fb->post(
    //             "/{$this->data['ads_id']}/campaigns",
    //             [
    //                 'name' => 'My Test Campaign',
    //                 'objective' => 'OUTCOME_AWARENESS',
    //                 'status' => 'PAUSED',
    //                 'special_ad_categories' => ['NONE']
    //             ],
    //             $this->data['fb_access_token']
    //         );
    //         $campaignBody = $campaignResponse->getDecodedBody();
    //         $campaignId = $campaignBody['id'];
    //         echo "Created Campaign with ID: $campaignId\n";
    //         $this->data['campaign_id'] = $campaignId;

    //     } catch (\Facebook\Exceptions\FacebookResponseException $e) {
    //         echo 'Graph returned an error: ' . $e->getMessage();
    //     } catch (\Facebook\Exceptions\FacebookSDKException $e) {
    //         echo 'Facebook SDK returned an error: ' . $e->getMessage();
    //     }
    // }

 // public function createTestAdset(){

    //     $fb = $this->buildClient();
        
    //     // Create Adset
    //     try{
    //         $adSetResponse = $fb->post(
    //             "/{$this->data['ads_id']}/adsets",
    //             [
    //                 'name' => 'My Test Ad Set',
    //                 'billing_event' => 'IMPRESSIONS',
    //                 'daily_budget' => '10000', // Sets budget to 100 of the selected currency
    //                 'campaign_id' => $this->data['campaign_id'],
    //                 'special_ad_categories' => [], 
    //                 'bid_strategy' => 'LOWEST_COST_WITHOUT_CAP',
    //                 'dsa_beneficiary'   => '551570511380996',
    //                 'dsa_payor'         => '551570511380996',
    //                 'optimization_goal' => 'REACH',
    //                 'targeting' => [
    //                     'geo_locations' => [
    //                         'countries' => ['SE']
    //                     ],
    //                     'age_min' => 18, 
    //                     'age_max' => 65
    //                 ],
    //                 'status' => 'PAUSED',
    //             ],
    //             $this->data['fb_access_token']
    //         );
    //         $adSetBody = $adSetResponse->getDecodedBody();
    //         $adSetId = $adSetBody['id'];
    //         echo "Created Ad Set with ID: $adSetId<br>";
    //         echo "Token i kod: " . $this->data['fb_page_access_token'] . "<br>";
    //         echo "App ID i kod: " . $this->data['app_id'] . "<br>";
    //         try {
    //             if (!file_exists('D:/xampp/htdocs/Merchant/app/images/dummy.png')) {
    //                 echo "File not found!";
    //                 exit;
    //             }
    //             $imageResponse = $fb->post(
    //                 "/{$this->data['ads_id']}/adimages",
    //                 [
    //                     'url' => 'https://cdn-icons-png.flaticon.com/512/3273/3273898.png'
    //                 ],
    //                 $this->data['fb_access_token'] // Must have ads_management scope
    //             );
    //             $imageBody = $imageResponse->getDecodedBody();
    //             $imageHash = $imageBody['images'][0]['hash'];
    //             echo "Image Hash: $imageHash\n";
    //         } catch (Facebook\Exceptions\FacebookResponseException $e) {
    //             echo 'Graph returned an error: ' . $e->getMessage();
    //             exit;
    //         }
    //         //  Create Ad Creative
    //         $creativeResponse = $fb->post(
    //             "/{$this->data['ads_id']}/adcreatives",
    //             [
    //                 'name' => 'My Test Creative',
    //                 'page_id' => '551570511380996',
    //                 'object_story_spec' => [
    //                     'page_id' => '551570511380996',
    //                     'link_data' => [
    //                         'message' => 'Check out our products!',
    //                         'link' => 'https://www.example.com/',
    //                         'name' => 'Test Product Ad',
    //                         'description' => 'Discover our amazing products!',
    //                         'image_hash' => $imageHash, // Ersätt med en giltig bild-URL
    //                         'call_to_action' => [
    //                             'type' => 'SHOP_NOW'
    //                         ]
    //                     ]
    //                 ]
    //             ],
    //             $this->data['fb_access_token']
    //         );
    //         $creativeBody = $creativeResponse->getDecodedBody();
    //         $creativeId = $creativeBody['id'];
    //         echo "Created Ad Creative with ID: $creativeId\n";

    //         // Create ad
    //         $adResponse = $fb->post(
    //             "/{$this->data['ads_id']}/ads",
    //             [
    //                 'name' => 'My Test Ad',
    //                 'adset_id' => $adSetId,
    //                 'creative' => ['creative_id' => $creativeId],
    //                 'status' => 'PAUSED',
    //             ],
    //             $this->data['fb_access_token']
    //         );
    //         $adBody = $adResponse->getDecodedBody();
    //         $adId = $adBody['id'];
    //         echo "Created Ad with ID: $adId\n";
    //     } 
    //     catch (\Facebook\Exceptions\FacebookResponseException $e) {
    //         // Print the full error details to help pinpoint the issue
    //         $errorDetails = $e->getResponse()->getDecodedBody();
    //         echo "Graph returned an error: " . $e->getMessage() . "\n";
    //         echo "Error details: " . print_r($errorDetails, true);
    //     } 
    //     catch (\Facebook\Exceptions\FacebookSDKException $e) {
    //         echo "Facebook SDK returned an error: " . $e->getMessage();
    //     }
    // }
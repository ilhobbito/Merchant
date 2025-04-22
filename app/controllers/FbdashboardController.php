<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/config.php';

// To avoid undefined key array warning from displaying. 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 1);

use Dotenv\Dotenv;
use App\Models\Campaign;
use App\Models\User;
use FacebookAds\Object\ProductSet;
use FacebookAds\Api;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\Fields\ProductSetFields;

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
        $fbClient = $this->buildClient();
        $user = new User();
        $this->data['business_id'] =  $user->getBusinessId($fbClient);
        $this->data['ads_id'] = "act_" . $user->getAdsId($fbClient);
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
        
        $fbClient = $this->buildClient();
        $message;

        try {
            $response = $fbClient->get('/me?fields=id,name,email', $this->data['fb_access_token']);
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
            $fbClient = $this->buildClient();
            
            try {
                $response = $fbClient->post("/{$this->data['business_id']}/owned_product_catalogs", [
                    'name' => $_POST['catalog_name'],
                ], $this->data['fb_access_token']);

                //GraphNode is meant for a single object
                $catalog = $response->getGraphNode();

                $detailsResponse = $fbClient->get("/{$catalog['id']}?fields=id,name", $this->data['fb_access_token']);
                $lastCreatedCatalog = $detailsResponse->getGraphNode()->AsArray();
                $_SESSION['last_created_catalog'] = $lastCreatedCatalog;

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
        $fbClient = $this->buildClient();
        $user = new User();
        $catalogs = $user->getAllCatalogs($fbClient);
        require_once __DIR__ . '/../views/fbdashboard/list-catalogs.php';
    }

    // Makes an API request to Post a product to the users business account
    public function createProduct()
    {     
        $fbClient = $this->buildClient();
        $user = new User();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {   
            $catalogs = $user->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-product.php';
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
    
                $response = $fbClient->post(
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
                        'sale_price'  => $_POST['price'],
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

    public function createProductsCsv()
    {
       
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $userManager = new User();
            $fbClient = $this->buildClient();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-products-csv.php';
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_csv'])) {
            $catalogId = $_POST['catalog_id'] ?? null;

            if (!$catalogId) {
                die("Missing catalog ID");
            }

            $apiUrl = "https://graph.facebook.com/v19.0/{$catalogId}/products";
            $csvFile = $_FILES['product_csv']['tmp_name'];
        
            if (($handle = fopen($csvFile, "r")) !== false) {
                $header = fgetcsv($handle); // first row (column headers)
        
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row); // map headers to row values
        
                    // Split price and currency
                    $priceParts = explode(' ', $data['price']); // ["999.99", "SEK"]
                    $correctPrice = floatval($priceParts[0]);
                    $correctPrice = intval(round($correctPrice * 100));

                    $postData = [
                    'price'  => $correctPrice,     // "999.99"
                    'currency'      => $priceParts[1],     // "SEK"
                    'retailer_id'   => $data['retailer_id'],
                    'name'          => $data['name'],
                    'description'   => $data['description'],
                    'availability'  => $data['availability'],
                    'condition'     => $data['condition'],
                    'image_url'    => $data['image_url'],
                    'link'          => $data['link'],
                    'brand'         => $data['brand'],
                    'access_token'  => $this->data['fb_access_token'],
                    ];
                    // Optional sale_price
                    if (!empty($data['sale_price'])) {
                        $postData['sale_price'] = $data['sale_price'];
                    }
        
                    // Send to Facebook API
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode === 200) {
                        echo "Uploaded: " . $data['retailer_id'] . "<br>";
                        echo "Displayed price: " . number_format($correctPrice / 100, 2) . " " . $priceParts[1]. "<br><br>";
                    } else {
                        echo "Failed: " . $data['retailer_id'] . "<br>";
                        echo "<pre>" . htmlspecialchars($response) . "</pre>";
                    }
                    
                }
        
                fclose($handle);
                echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
            } else {
                echo "Could not open uploaded file.";
                echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
            }
        } else {
            echo "No file uploaded.";
            echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
       
    }

    // Retrieves all the products connected the users business account
    public function listAllProducts(){
        $fbClient = $this->buildClient();
        $userManager = new User();
        $catalogs = $userManager->getAllCatalogs($fbClient);
        require_once __DIR__ . '/../views/fbdashboard/list-products.php';

    }

    // public function updateProductSet()
    // {
    //     $product_set_id = ''; // Replace
    //     $access_token = $this->data['fb_access_token'];
    
    //     $url = "https://graph.facebook.com/v19.0/{$product_set_id}";
    //     $data = [
    //         'access_token' => $access_token,
    //         'filter' => json_encode([
    //             'availability' => [
    //                 'is_any' => ['in stock']
    //             ]
    //         ])
    //     ];
    
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    
    //     echo "Update response: " . $response . "\n";
    // }

    public function createProductSet()
    {
        $fbClient = $this->buildClient();
        $user = new User();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {   
            $catalogs = $user->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-product-set.php';
        }
        else{
            $url = "https://graph.facebook.com/v19.0/{$_POST['catalog']}/product_sets";

            if($_POST['filter'] == "no_filter"){
                $data = [
                    'access_token' => $this->data['fb_access_token'],
                    'name' => $_POST['name'] . time(),
                ];    
            }
            else{
                $data = [
                    'access_token' => $this->data['fb_access_token'],
                    'name' => $_POST['name'] . time(),
                    
                    'filter' => json_encode([
                        //Selects what to filter the catalog on, i.e. Availabilty or Id's
                        "{$_POST['filter']}" => [
                            // Default Is_Any means that everything that has any properties for the selected filter will be picked
                            "{$_POST['filter_type']}" => [$_POST['filter_object']] // Object is the string that it will filter on, like a retailer_id or if something is "in stock"
                        ]
                    ])
                ];
            }
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        try{
            
            if ($error) {
                throw new \Exception("cURL Error: " . $error);
            }
            $decoded = json_decode($response, true);

            if (isset($decoded['error'])) {
            $this->throwFacebookApiException($decoded['error']);
            }

            echo "Productset \"" . $_POST['name'] . "\" was successfully created!<br>" . $response . "<br>
            Filter by " . $_POST['filter'] . " with filter type " . $_POST['filter_type'] . " and filter object " . $_POST['filter_object'];

        }catch(\Exception $e){
            echo "Error creating a Product Set: <br>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
       
        echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
        
    }

    public function createSale() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $fbClient = $this->buildClient();
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-sale.php';
        } 
        else {

    
            // Step 1: Get all product IDs in the selected product set
            $url = "https://graph.facebook.com/v17.0/{$_POST['product_set']}/products?access_token={$this->data['fb_access_token']}&fields=id";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
    
            $products = json_decode($response, true);
            if (!isset($products['data'])) {
                die("Failed to retrieve products: " . print_r($products, true));
            }
    
            $discountAmount = floatval($_POST['discount_amount']); // e.g. 20 for 20%
    
            // Step 2: Loop through products and update each one
            foreach ($products['data'] as $product) {
                $productItemId = $product['id'];
            
                // Step 1: Get original price and currency
                $getProductUrl = "https://graph.facebook.com/v17.0/{$productItemId}?access_token={$this->data['fb_access_token']}&fields=price,currency";
                $ch = curl_init($getProductUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $productDetails = json_decode(curl_exec($ch), true);
                curl_close($ch);
            
                if (!isset($productDetails['price'], $productDetails['currency'])) {
                    echo "⚠️ Skipping product {$productItemId} — missing price or currency.<br>";
                    continue;
                }
            
                $currency = $productDetails['currency']; // "SEK"
                
                // Remove non-numeric (currency) characters
                $rawPrice = str_replace(',', '', $productDetails['price']); // "SEK10000.00"
                preg_match('/([\d\.]+)/', $rawPrice, $matches);
                $basePrice = isset($matches[1]) ? floatval($matches[1]) : 0.0;
                
                // Calculate the discount
                $discountAmount = $_POST['discount_amount'];
                $salePrice = round($basePrice * (1 - $discountAmount / 100), 2);
                
                // Format: "0.99 SEK"
                $minorUnitPrice = (int) round($salePrice * 100);
            
                // DEBUG: Output what will be sent
                echo "Updating product {$productItemId} with sale_price = {$formattedSale}<br>";
            
                // Step 2: Send update
                $updateUrl = "https://graph.facebook.com/v17.0/{$productItemId}?access_token={$this->data['fb_access_token']}";
                $data = [
                    'sale_price' => $minorUnitPrice,
                    'currency' => $currency
                ];
            
                echo "<pre>POST DATA:\n" . print_r($data, true) . "</pre>";
            
                $ch = curl_init($updateUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $updateResponse = curl_exec($ch);
                curl_close($ch);
            
                $result = json_decode($updateResponse, true);
                echo "<pre>API Response:\n" . print_r($result, true) . "</pre>";
            
                // Step 3: Verify result by fetching product again
                $verifyUrl = "https://graph.facebook.com/v17.0/{$productItemId}?access_token={$this->data['fb_access_token']}&fields=sale_price";
                $verifyCh = curl_init($verifyUrl);
                curl_setopt($verifyCh, CURLOPT_RETURNTRANSFER, true);
                $verifyResponse = curl_exec($verifyCh);
                curl_close($verifyCh);
            
                $verified = json_decode($verifyResponse, true);
                echo "<strong>Confirmed sale_price: " . ($verified['sale_price'] ?? 'Not Set') . "</strong><br><br>";
            }
    
            echo "<br><a href='/Merchant/public/fbdashboard'>Return to Dashboard</a>";
        }
    }

    // Makes an API request to Post a campaign to the users Ads Account
    public function createCampaign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        require_once __DIR__ . '/../views/fbdashboard/create-campaign.php';
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/campaigns";

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
                // Change these to be more dynamic
                'buying_type' => 'AUCTION',  
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
            $error = curl_error($ch);
            curl_close($ch);

            // Check for cURL errors
            try{
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);

                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }
                echo "Campaign \"" .$_POST['campaign_name'] . "\" with ID '" . $response . "' successfully created<br>";
                echo "Status: "  . $_POST['status'] . "     Objective: " . $_POST['objective'] . "<br>";
               
            }
            catch(\Exception $e){
                echo "Error creating Campaign: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
            echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }

    // Makes an API request to Post an Ad Set to the users Ads Account
    public function createAdSet()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $campaignManager = new Campaign();
            $fbClient = $this->buildClient();
            $userManager = new User();

            $campaigns = $campaignManager->getCampaigns($this->data['ads_id'], $this->data['fb_access_token']);
            $catalogs = $userManager->getAllCatalogs($fbClient);

            if (isset($campaigns['error'])) {
                echo "Error fetching campaigns: " . $campaigns['error']['message'];
                return;
            }
            require_once __DIR__ . '/../views/fbdashboard/create-adset.php';    
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adsets";
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
                'bid_amount' => $_POST['bid_amount'] ?? 50,   
                'targeting'       => json_encode([
                    'geo_locations' => [
                        'countries' => ['SE']  
                    ],
                    'publisher_platforms' => ['facebook'],
                    'facebook_positions' => ['feed']
                ]),
                'dsa_beneficiary'     => $_POST['dsa_beneficiary'], // The Facebook Page or entity benefiting
                'dsa_payor'           => $_POST['dsa_payor'],  // The Facebook Page or entity paying
                'status'          => $_POST['status'] ?? 'PAUSED',
                'access_token'    => $this->data['fb_access_token'],
                
                // Advertisement Schedule
                'start_time'        => (new \DateTime('+1 day'))->format(\DateTime::ISO8601),
                'end_time'          => (new \DateTime('+7 days'))->format(\DateTime::ISO8601),

                'promoted_object[pixel_id]'         => $_ENV['PIXEL_ID'], // TODO: Make Pixel more dynamic in case more than one pixel exists
                //'promoted_object[product_set_id]'   => $_POST['product_set'],
                'promoted_object[custom_event_type]'=> 'PURCHASE',

            ];
            var_dump($postFields);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            try {
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);

                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }

                echo "Ad Set <strong>{$_POST['adset_name']}</strong> created successfully!<br>";
                echo "Ad Set ID: " . $decoded['id'] . "<br>";
                echo "Daily budget: {$_POST['daily_budget']}<br>"; // TODO: Make it so that it adds decimal to not confuse user i.e. 15.00 rather than 1500
                echo "Target Country: SE<br>";

            } catch (\Exception $e) {
                echo "Error creating Ad Set: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
            echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
        }
        
    }

    // Makes an API request to Post an Ad Creative to the users Ads account
    public function createAdCreative()
    {
        $fbClient = $this->buildClient();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-adcreative.php';
        }
        else{

            var_dump($_POST);
            $productSetId = $_POST['product_set'];
            $accessToken = $this->data['fb_access_token'];

            $productResponse = $fbClient->get(
                "/$productSetId/products?fields=name,retailer_id,price,sale_price,url&limit=1",
                $accessToken
            );

            $productData = $productResponse->getDecodedBody()['data'][0];

            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adcreatives";
            $ch = curl_init($url);
            $objectStorySpec = [
                'page_id' => $_POST['page_id'],
                'template_data' => [
                    'link' => 'https://www.example.com',
                    'message' => $_POST['message'],
                    'name' => $productData['name'],
                    'description' => 'Now only ' . $productData['sale_price'] . ' (was ' . $productData['price'] . ')',
                    'call_to_action' => [
                        'type' => $_POST['call_to_action'],
                        'value' => [
                            'link' => 'https://www.example.com' // optional fallback
                        ]
                    ],
                ]
                        
            ];
            $postFields = [
                'name'               => $_POST['creative_name'] ?? 'MyAdCreative',
                'object_story_spec'  => json_encode($objectStorySpec),
                'access_token'       => $this->data['fb_access_token'],
                'product_set_id'     => $_POST['product_set'],
                'catalog_id'         => $_POST['catalog_id'],
               // 'dynamic_ad_voice'   => 'DYNAMIC'
                
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
    
            try {
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);

                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }

                echo "Ad Creative " . $_POST['creative_name'] . " with ID: " . $response . " was successfully created!<br>";
                echo "Call to action type: " . $_POST['call_to_action'] . "<br>";
                echo "Link: " . $_POST['link'] . "      " . $_POST['page_id'] . "<br>Ad Message: \"" . $_POST['message'] . "\".";  
            }
            catch(\Exception $e){
                echo "Error creating Ad Creative: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
            
            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
            
        }
       
    }

    // Makes an API request to Post an actual Advertisement to the users Ads Account
    public function createAdvertisement()
    {
        $campaignManager = new Campaign();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           
            $adSets = $campaignManager->getAdSets($this->data['ads_id'], $this->data['fb_access_token']);
            $adCreatives = $campaignManager->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);
            require_once __DIR__ . '/../views/fbdashboard/create-advertisement.php';
        }
        else{
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/ads";
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
                    'access_token' => $this->data['fb_access_token'],
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
            $error = curl_error($ch);
            curl_close($ch);
           
            try {
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);

                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }

                echo "Ad " . $_POST['ad_name'] . " with Id: " . $response . " was successfully created!<br>";
                echo "This ad belongs to Ad Set with Id: " . $_POST['adset_id'] . " and Ad Creative with Id: " . $_POST['adcreative_id'] . ".<br>";
                echo "Status: " . $_POST['status'];
            }
            catch(\Exception $e){
                echo "Error creating Advertisement: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
            
            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
            
            
        }
    }

    // Makes an API request to retrieve some basic info about the ads account
    public function checkAdAccount()
    {
        $fbClient = $this->buildClient();
        $message;
        try {
            $response = $fbClient->get(
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
        $fbClient = $this->buildClient();
        $message;
        try {
            $response = $fbClient->get(
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

    //TODO: Implement this perhaps?
    public function mockData()
    {
        $adId = '120219870898810468'; // Can also be Ad Set ID or Campaign ID
        $adSetId = '120219870613290468';
        $since = '2024-03-01';  // Start date
        $until = '2024-03-24';  // End date

        $mockData = [
            'data' => [
                [
                    'impressions' => '1243',
                    'clicks' => '58',
                    'spend' => '12.67',
                    'ad_name' => 'Mock Ad A',
                    'date_start' => '2024-03-01',
                    'date_stop' => '2024-03-21'
                ]
            ]
        ];

        // Meta Graph API URL
        $url = "https://graph.facebook.com/v19.0/{$adId}/insights?fields=impressions,clicks,spend&time_range[since]={$since}&time_range[until]={$until}&access_token={$this->data['fb_access_token']}";

        // Init cURL
        $ch = curl_init();

        // Set options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode response
        $data = json_decode($response, true);
        if (empty($data['data'])) {
            $data = $mockData;
            echo "No real data found from " . $adId . "— using mock data.</br>";
        } else {
            echo "Real data fetched from ad " . $adId . ".</br>";
        }
        // Output results
        if (!empty($data['data'])) {
            $result = $data['data'][0];
            echo "Impressions: " . $result['impressions'] . "</br>";
            echo "Clicks: " . $result['clicks'] . "</br>";
            echo "Spend: SEK" . $result['spend'] . "</br>";
        } else {
            echo "No performance data available for this ad.</br>";
        }
        echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
    }

    public function imageHash()
    {
        $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adimages";
        $ch = curl_init($url);

        $postFields = [
            'filename' => new \CURLFile(__DIR__ . '/../images/dummy.png', 'image/png', 'dummy.png'),
            'access_token' => $this->data['fb_access_token']
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the response to get the image hash
        $data = json_decode($response, true);
        $imageHash = $data['images']['dummy.png']['hash'] ?? null;

        return $imageHash;
    }

    // This is used in createAdCreative to dynamically load relevant productsets for the selected catalog rather than
    // all productsets like the one in the User model
    public function getProductSetsByAJAX()
    {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        if (!isset($_GET['catalog_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing catalog ID']);
            return;
        }
        if (empty($_GET['catalog_id'])) {
            echo json_encode(['error' => 'No catalog ID provided']);
            exit;
        }

        $catalogId = $_GET['catalog_id'];
        $accessToken = $_SESSION['fb_access_token'] ?? null;

        if (!$accessToken) {
            http_response_code(403);
            echo json_encode(['error' => 'Access token missing']);
            return;
        }

        $userManager = new \App\Models\User();
        $productSets = $userManager->getProductSetsForCatalog($catalogId, $accessToken);

        if (empty($productSets)) {
            echo json_encode(['error' => 'No product sets found', 'catalog_id' => $catalogId]);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($productSets);
    }

    function throwFacebookApiException(array $error)
    {
        throw new \Exception(
            "Facebook API Error: " . $error['message'] . "\n" .
            "Type: " . $error['type'] . "\n" .
            "Code: " . $error['code'] . "\n" .
            "Subcode: " . ($error['error_subcode'] ?? 'N/A') . "\n" .
            "Blame Field: " . json_encode($error['error_data']['blame_field_specs'] ?? []) . "\n" .
            "Trace ID: " . ($error['fbtrace_id'] ?? 'N/A') . "\n" .
            ($error['error_user_title'] ?? '') . ': ' . ($error['error_user_msg'] ?? '')
        );
    }

    public function getProductsByAJAX()
    {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        if (!isset($_GET['product_set'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing product set ID']);
            return;
        }

        $productSetId = $_GET['product_set'];
        $accessToken = $_SESSION['fb_access_token'] ?? null;

        if (!$accessToken) {
            http_response_code(403);
            echo json_encode(['error' => 'Access token missing']);
            return;
        }

        $fbClient = $this->buildClient();

        try {
            $response = $fbClient->get("/{$productSetId}/products?fields=id,name,retailer_id,price,sale_price", $accessToken);
            $data = $response->getDecodedBody();
            echo json_encode($data['data'] ?? []);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Failed to fetch products', 'details' => $e->getMessage()]);
        }
    }

    public function deleteAdCreative(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $campaignManager = new Campaign();
            $adCreatives = $campaignManager->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);
            require_once __DIR__ . '/../views/fbdashboard/delete-ad-creative.php'; 
        }
        else{
            $selectedIds = $_POST['selected_creatives'] ?? [];
            $ads_id = $this->data['ads_id'];
            $accessToken = $this->data['fb_access_token'];
        
            if (empty($selectedIds)) {
                echo "No creatives selected.";
                return;
            }
            echo "<h3>Deleted Creatives: </h3>";
            foreach ($selectedIds as $id) {
                $deleteUrl = "https://graph.facebook.com/v22.0/{$id}?access_token={$accessToken}";
                $ch = curl_init($deleteUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
        
                $result = json_decode($response, true);
                if (isset($result['success']) && $result['success']) {
                    echo "Deleted Creative ID: {$id}<br>";
                } else {
                    echo "Failed to delete Creative ID: {$id}<br>";
                }
            }
            echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }
}

       
  

 
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
        // Load environment variables from the .env file located two levels up
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();
    
        // Start the session if it hasn't already been started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        // If an access token exists in the session, store it in the data array
        if (isset($_SESSION['fb_access_token'])) {
            $this->data['fb_access_token'] = $_SESSION['fb_access_token'];
        }
    
        // Load Facebook app credentials from the environment
        $this->data['app_id'] = $_ENV['FACEBOOK_APP_ID'];
        $this->data['app_secret'] = $_ENV['FACEBOOK_APP_SECRET'];
    
        // Build the Facebook client
        $fbClient = $this->buildClient();
    
        // Initialize the User model to retrieve required Facebook business information.
        // First, we fetch the business ID associated with the authenticated user.
        // Then, we fetch the user's primary ads account ID and prefix it with 'act_' (as Facebook ad accounts use this format).
        $user = new User();
        $this->data['business_id'] = $user->getBusinessId($fbClient);
        $this->data['ads_id'] = "act_" . $user->getAdsId($fbClient); // Facebook ad accounts are prefixed with 'act_'
    }

    public function index(){
        require_once __DIR__ . '/../views/fbdashboard/index.php';
    }

    // Builds and returns a Facebook SDK client using app credentials from $this->data.
    // This client is used for making authenticated Graph API requests.
    public function buildClient() {
        return new \Facebook\Facebook([
            'app_id'                => $this->data['app_id'],
            'app_secret'            => $this->data['app_secret'],
            'default_graph_version' => 'v22.0',
        ]);
    }
    
    // A simple API test that fetches the authenticated user's name via the Facebook Graph API
    public function apiTest() {
        $fbClient = $this->buildClient();
        $message;

        try {
            // Request basic user info using the stored access token
            $response = $fbClient->get('/me?fields=id,name,email', $this->data['fb_access_token']);
            $user = $response->getGraphUser();

            // Display a greeting using the user's name
            $message = 'Hello, ' . $user->getName();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        // Load the API test view
        require_once __DIR__ . '/../views/fbdashboard/api-test.php';
    }
    
    // Handles catalog creation via Facebook's Graph API
    public function createCatalog()
    {
        // If not a POST request, show the catalog creation form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/fbdashboard/create-catalog.php';
        } 
        else {
            $fbClient = $this->buildClient();

            try {
                // Send POST request to create a new product catalog under the user's business ID
                $response = $fbClient->post("/{$this->data['business_id']}/owned_product_catalogs", [
                    'name' => $_POST['catalog_name'],
                ], $this->data['fb_access_token']);

                // Retrieve details of the newly created catalog (id, name)
                $catalog = $response->getGraphNode();
                $detailsResponse = $fbClient->get("/{$catalog['id']}?fields=id,name", $this->data['fb_access_token']);
                $lastCreatedCatalog = $detailsResponse->getGraphNode()->asArray();

                // Save last created catalog to session for future use in UI (e.g., dropdowns)
                $_SESSION['last_created_catalog'] = $lastCreatedCatalog;

                echo "Catalog '{$_POST['catalog_name']}' created with ID: {$catalog['id']}";
            } 
            catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } 
            catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";
        }
    }

   // Retrieves and displays all product catalogs linked to the user's business
    public function listCatalogs() {
        $fbClient = $this->buildClient();
        $user = new User();

        // Fetch all owned product catalogs using the Facebook API
        $catalogs = $user->getAllCatalogs($fbClient);

        // Load the view to display the catalog list
        require_once __DIR__ . '/../views/fbdashboard/list-catalogs.php';
    }

    // Handles product creation by posting to the selected catalog via Facebook's Graph API
    public function createProduct()
    {     
        $fbClient = $this->buildClient();
        $user = new User();

        // If not a POST request, show product creation form with list of catalogs
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $catalogs = $user->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-product.php';
        } 
        else {
            try {
                // Send POST request to add a new product to the selected catalog
                $response = $fbClient->post(
                    "/{$_POST['select_catalog']}/products",
                    [
                        'retailer_id'  => $_POST['product_id'], // Must be unique
                        'name'         => $_POST['product_name'],
                        'description'  => $_POST['description'],
                        'image_url'    => $_POST['image_url'],
                        'url'          => $_POST['product_url'],
                        'price'        => $_POST['price'],
                        'sale_price'   => $_POST['price'],
                        'currency'     => $_POST['currency'],
                        'availability' => $_POST['availability'],
                    ],
                    $this->data['fb_access_token']
                );

                // Decode response and confirm success to the user
                $result = $response->getDecodedBody();
                echo "New product '{$_POST['product_name']}' (ID: {$result['id']}) was successfully added to catalog {$_POST['select_catalog']}";
                echo "<a href='/Merchant/public/fbdashboard'><br>Return</a>";

            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }
        }
    }

    // Handles CSV-based product import into a Facebook catalog via the Graph API
    public function createProductsCsv()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $userManager = new User();
            $fbClient = $this->buildClient();

            // Fetch all catalogs to populate the dropdown in the view
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-products-csv.php';
        } 
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_csv'])) {
            
            // Ensure catalog ID is provided
            $catalogId = $_POST['catalog_id'] ?? null;
            if (!$catalogId) {
                die("Missing catalog ID");
            }

            // Define the Graph API endpoint for product uploads
            $apiUrl = "https://graph.facebook.com/v19.0/{$catalogId}/products";
            $csvFile = $_FILES['product_csv']['tmp_name'];

            // Open the uploaded CSV file and parse its contents
            if (($handle = fopen($csvFile, "r")) !== false) {
                $header = fgetcsv($handle); // Read the column headers

                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row); // Map column names to values

                    // Extract and format price to fit Facebooks requirements
                    $priceParts = explode(' ', $data['price']); // Example: ["999.99", "SEK"]
                    $correctPrice = intval(round(floatval($priceParts[0]) * 100));

                    // Prepare the POST data for Facebook
                    $postData = [
                        'price'         => $correctPrice,
                        'currency'      => $priceParts[1],
                        'retailer_id'   => $data['retailer_id'], // Must be unique
                        'name'          => $data['name'],
                        'description'   => $data['description'],
                        'availability'  => $data['availability'],
                        'condition'     => $data['condition'],
                        'image_url'     => $data['image_url'],
                        'link'          => $data['link'],
                        'brand'         => $data['brand'],
                        'access_token'  => $this->data['fb_access_token'],
                    ];

                    if (!empty($data['sale_price'])) {
                        $postData['sale_price'] = $data['sale_price'];
                    }

                    // Facebook's SDK doesn't support CSV uploads directly,
                    // so we send each product manually via raw HTTP POST (cURL)
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Display success or error message for each product
                    if ($httpCode === 200) {
                        echo "Uploaded: {$data['retailer_id']}<br>";
                        echo "Displayed price: " . number_format($correctPrice / 100, 2) . " {$priceParts[1]}<br><br>";
                    } else {
                        echo "Failed: {$data['retailer_id']}<br>";
                        echo "<pre>" . htmlspecialchars($response) . "</pre>";
                    }
                }

                fclose($handle);
                echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
            } 
            else {
                echo "Could not open uploaded file.";
                echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
            }
        } 
        else {
            echo "No file uploaded.";
            echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }

    // Retrieves all catalogs connected to the user's business account for product listing
    public function listAllProducts(){

        // Build Facebook client and get all catalogs linked to the user's business
        // The view will loop through each catalog and retrieve its associated products
        $fbClient = $this->buildClient();
        $userManager = new User();
        $catalogs = $userManager->getAllCatalogs($fbClient);
        require_once __DIR__ . '/../views/fbdashboard/list-products.php';

    }

    // Creates a Facebook Product Set for use in dynamic ads.
    // A Product Set defines a filter to target specific products within a catalog.
    public function createProductSet()
    {
        // On GET request: build Facebook client and fetch all catalogs for form display
        $fbClient = $this->buildClient();
        $user = new User();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {   
            $catalogs = $user->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-product-set.php';
        }
        else{
            // Tells the url to target the product_sets end point in the selected catalog.
            $url = "https://graph.facebook.com/v19.0/{$_POST['catalog']}/product_sets";

            // If no filter is selected, target all products in the catalog
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
                        // Define filter based on user input (e.g., availability, retailer_id)
                        // "is_any" selects all matching values of the specified filter property
                        "{$_POST['filter']}" => [
                            // Default Is_Any means that everything that has any properties for the selected filter will be picked
                            "{$_POST['filter_type']}" => [$_POST['filter_object']] // Object is the string that it will filter on, like a retailer_id or if something is "in stock"
                        ]
                    ])
                ];
            }
            
        // Send POST request to Graph API using cURL to create the product set
        // Handle API response and display success or error message
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

            echo "Product Set \"{$_POST['name']}\" was successfully created!<br>";
            echo "Filter by: {$_POST['filter']} → {$_POST['filter_type']} = {$_POST['filter_object']}<br>";

        }catch(\Exception $e){
            echo "Error creating a Product Set: <br>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
       
        echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
        
    }

    // Applies a sale price to all products in a selected product set
    // Retrieves products via the Facebook Graph API, calculates discounted prices, and updates each product
    public function createSale() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Simply build a Facebook Client and make a new instance of the User Model then get all the catalogs using the getAllCatalogs function in User.
            $fbClient = $this->buildClient();
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/fbdashboard/create-sale.php';
        } 
        else {

    
            // Step 1: Fetch all product IDs from the selected product set via the Graph API
            $url = "https://graph.facebook.com/v17.0/{$_POST['product_set']}/products?access_token={$this->data['fb_access_token']}&fields=id";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
    
            // Decode JSON response and validate that product data exists
            $products = json_decode($response, true);
            if (!isset($products['data'])) {
                die("Failed to retrieve products: " . print_r($products, true));
            }
    
            // Get the discount percentage entered by the user
            $discountAmount = floatval($_POST['discount_amount']); // e.g. 20 for 20%
    
            // Step 2: Loop through products and update each one
            foreach ($products['data'] as $product) {
                $productItemId = $product['id'];
            
                // Fetch current price and currency of the product via Graph API
                $getProductUrl = "https://graph.facebook.com/v17.0/{$productItemId}?access_token={$this->data['fb_access_token']}&fields=price,currency";
                $ch = curl_init($getProductUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $productDetails = json_decode(curl_exec($ch), true);
                curl_close($ch);
            
                // Checks if price and currency is set or return an error message.
                if (!isset($productDetails['price'], $productDetails['currency'])) {
                    echo "Skipping product {$productItemId} — missing price or currency.<br>";
                    continue;
                }
            
                // Use the product's existing currency (assumed to follow standard ISO-4217 format, e.g., SEK, USD)
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
            
                // Show calculated sale price and prepared POST data
                echo "Updating product {$productItemId} with sale_price = {$salePrice}<br>";
            
                // Send update request to Graph API to apply the sale price
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
            
                // Confirm that the sale price was applied by fetching the product again
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

    // Creates a Facebook Ads campaign via the Graph API
    public function createCampaign()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/fbdashboard/create-campaign.php';
            return;
        }

        // Ensure a campaign name was submitted
        if (!isset($_POST['campaign_name'])) {
            echo "A campaign name must be set!";
            return;
        }

        // Set Graph API endpoint for campaign creation
        $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/campaigns";

        // Prepare POST fields for the campaign request
        $postFields = [
            'name'                  => $_POST['campaign_name'],
            'objective'             => $_POST['objective'] ?? 'OUTCOME_TRAFFIC',
            'buying_type'           => 'AUCTION',
            'status'                => $_POST['status'] ?? 'PAUSED',
            'special_ad_categories' => json_encode([]), // Empty by default
            'access_token'          => $this->data['fb_access_token']
        ];

        // Send POST request via cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // Handle cURL or API errors
        try {
            if ($error) {
                throw new \Exception("cURL Error: " . $error);
            }

            $decoded = json_decode($response, true);

            if (isset($decoded['error'])) {
                $this->throwFacebookApiException($decoded['error']);
            }

            echo "Campaign \"{$_POST['campaign_name']}\" successfully created.<br>";
            echo "Status: {$_POST['status']} | Objective: {$_POST['objective']}<br>";
            echo "Raw response: {$response}<br>";

        } catch (\Exception $e) {
            echo "Error creating Campaign:<br>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }

        echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
    }

    // Creates an Ad Set in the user's Ads Account via the Facebook Graph API
    // Uses form inputs for campaign selection, budget, targeting, and schedule
    public function createAdSet()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Initialize required models and fetch data for the form
            $campaignManager = new Campaign();
            $fbClient = $this->buildClient();
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            
            // Fetch campaigns via raw cURL; check for API errors in the returned structure
            $campaigns = $campaignManager->getCampaigns($this->data['ads_id'], $this->data['fb_access_token']);
            if (isset($campaigns['error'])) {
                echo "Error fetching campaigns: " . $campaigns['error']['message'];
                return;
            }
        
            // Load the form view
            require_once __DIR__ . '/../views/fbdashboard/create-adset.php';    
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Set Graph API endpoint for creating ad sets
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adsets";
            $ch = curl_init($url);
        
            // Ensure a name is provided
            if (!isset($_POST['adset_name'])) {
                echo "A name for the Ad Set must be set!";
                return;
            }
        
            // Prepare POST fields for ad set creation
            $postFields = [
                'name'                => $_POST['adset_name'],
                'campaign_id'         => $_POST['campaign_id'],
                'daily_budget'        => $_POST['daily_budget'] ?? 1500, // Value in minor units (e.g., 1500 = 15.00)
                'billing_event'       => $_POST['billing_event'] ?? 'IMPRESSIONS',
                'bid_strategy'        => $_POST['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
                'optimization_goal'   => $_POST['optimization_goal'] ?? 'LINK_CLICKS',
                'bid_amount'          => $_POST['bid_amount'] ?? 50,
        
                // Define geographic targeting and ad placement
                'targeting' => json_encode([
                    'geo_locations' => ['countries' => ['SE']],
                    'publisher_platforms' => ['facebook'],
                    'facebook_positions' => ['feed']
                ]),
        
                // Dynamic Sponsored Ads metadata (Payor = who pays, Beneficiary = who benefits)
                'dsa_beneficiary' => $_POST['dsa_beneficiary'],
                'dsa_payor'       => $_POST['dsa_payor'],
                'status'          => $_POST['status'] ?? 'PAUSED',
                'access_token'    => $this->data['fb_access_token'],
        
                // Ad scheduling (1 day from now to 7 days from now)
                'start_time' => (new \DateTime('+1 day'))->format(\DateTime::ISO8601),
                'end_time'   => (new \DateTime('+7 days'))->format(\DateTime::ISO8601),
        
                // Promoted object (pixel-based optimization)
                'promoted_object[pixel_id]'          => $_ENV['PIXEL_ID'], 
                'promoted_object[custom_event_type]' => 'PURCHASE',
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
        
                // Show success message and key campaign details
                echo "Ad Set <strong>{$_POST['adset_name']}</strong> created successfully!<br>";
                echo "Ad Set ID: " . $decoded['id'] . "<br>";
                echo "Daily budget: {$_POST['daily_budget']}<br>";
                echo "Target Country: SE<br>";
        
            } catch (\Exception $e) {
                echo "Error creating Ad Set: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
        
            echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
        }  
    }

    // Creates a new Ad Creative using a selected product set and basic template content
    public function createAdCreative()
    {
        $fbClient = $this->buildClient();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Fetch all catalogs to populate form options
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);

            // Load the form view for ad creative creation
            require_once __DIR__ . '/../views/fbdashboard/create-adcreative.php';
        } 
        else {
            // Fetch one product from the selected product set to use in the creative
            $productResponse = $fbClient->get(
                "/{$_POST['product_set']}/products?fields=name,retailer_id,price,sale_price,url&limit=1",
                $this->data['fb_access_token']
            );

            // Decode the first product's data for use in the template
            $productData = $productResponse->getDecodedBody()['data'][0];

            // Prepare API endpoint for Ad Creative creation
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adcreatives";
            $ch = curl_init($url);

            // Construct the object_story_spec required for dynamic ads
            $objectStorySpec = [
                'page_id' => $_POST['page_id'],
                'template_data' => [
                    'link' => 'https://www.example.com', // Static link placeholder
                    'message' => $_POST['message'],
                    'name' => $productData['name'],
                    'description' => 'Now only ' . $productData['sale_price'] . ' (was ' . $productData['price'] . ')',
                    'call_to_action' => [
                        'type' => $_POST['call_to_action'],
                        'value' => [
                            'link' => 'https://www.example.com' // CTA destination
                        ]
                    ],
                ]
            ];

            // Build the POST payload
            $postFields = [
                'name'              => $_POST['creative_name'] ?? 'MyAdCreative',
                'object_story_spec' => json_encode($objectStorySpec),
                'access_token'      => $this->data['fb_access_token'],
                'product_set_id'    => $_POST['product_set'],
                'catalog_id'        => $_POST['catalog_id'],
            ];

            // Execute the cURL request to create the ad creative
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

                // Output success message and key data
                echo "Ad Creative \"" . $_POST['creative_name'] . "\" was successfully created!<br>";
                echo "Call to action: " . $_POST['call_to_action'] . "<br>";
                echo "Link: " . $_POST['link'] . "<br>";
                echo "Page ID: " . $_POST['page_id'] . "<br>";
                echo "Ad Message: \"" . $_POST['message'] . "\".";  
            } 
            catch (\Exception $e) {
                echo "Error creating Ad Creative: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }

            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }

    // Creates a Facebook Advertisement by linking an Ad Set and an Ad Creative
    public function createAdvertisement()
    {
        // Initialize Campaign model to fetch required ad set and creative data
        $campaignManager = new Campaign();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Fetch available Ad Sets for this ad account (via raw Graph API using cURL)
            $adSets = $campaignManager->getAdSets($this->data['ads_id'], $this->data['fb_access_token']);

            // Fetch available Ad Creatives (filtered by effective_status = ACTIVE)
            $adCreatives = $campaignManager->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);

            // Load the form to create a new advertisement
            require_once __DIR__ . '/../views/fbdashboard/create-advertisement.php';
        } 
        else {
            // Prepare API endpoint for ad creation
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/ads";
            $ch = curl_init($url);

            // Validate that both Ad Set and Ad Creative IDs were provided
            if (isset($_POST['adset_id']) && isset($_POST['adcreative_id'])) {
                $postFields = [
                    'name'        => $_POST['ad_name'],
                    'adset_id'    => $_POST['adset_id'],

                    // The 'creative' field expects a JSON object containing 'creative_id'
                    'creative'    => json_encode([
                        'creative_id' => $_POST['adcreative_id']
                    ]),

                    // Default to PAUSED to avoid publishing live immediately
                    'status'      => $_POST['status'] ?? 'PAUSED',
                    'access_token'=> $this->data['fb_access_token'],
                ];
            } else {
                echo "Ad Set and Ad Creative require IDs.";
                return;
            }

            // Send the POST request to create the advertisement
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            // Handle errors and show success message
            try {
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);

                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }

                echo "Ad \"" . $_POST['ad_name'] . "\" was successfully created!<br>";
                echo "Ad Set ID: " . $_POST['adset_id'] . "<br>";
                echo "Creative ID: " . $_POST['adcreative_id'] . "<br>";
                echo "Status: " . ($_POST['status'] ?? 'PAUSED');
            } 
            catch (\Exception $e) {
                echo "Error creating Advertisement: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }

            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }

    // Retrieves basic information about the current Ads Account (name and currency)
    public function checkAdAccount()
    {
        $fbClient = $this->buildClient();
        $message = null;

        try {
            // Request basic account details from the Graph API
            $response = $fbClient->get(
                "/{$this->data['ads_id']}?fields=name,currency",
                $this->data['fb_access_token']
            );

            // Convert the response into a readable JSON string
            $adAccount = $response->getDecodedBody();
            $message = json_encode($adAccount);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Handle API-level errors (e.g., permission issues, invalid ID)
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Handle SDK-related issues (e.g., auth problems, local validation)
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        // Load the view to display the account info or error
        require_once __DIR__ . '/../views/fbdashboard/check-ad-account.php'; 
    }

    // Retrieves the Facebook Pixel(s) associated with the user's Ads Account, if any exist
    public function getPixel()
    {
        $fbClient = $this->buildClient();
        $message = null;

        try {
            // Request all ad pixels linked to the ad account (returns id and name)
            $response = $fbClient->get(
                "/{$this->data['ads_id']}/adspixels?fields=id,name",
                $this->data['fb_access_token']
            );

            // Decode and prepare the response for display
            $pixels = $response->getDecodedBody();
            $message = json_encode($pixels);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Handle API-level errors (e.g., invalid access token, permissions)
            $message = 'Graph returned an error: ' . $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Handle local SDK errors (e.g., validation or request failures)
            $message = 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        // Load the view to display pixel data or error message
        require_once  __DIR__ . '/../views/fbdashboard/get-pixel.php'; 
    }

    // Handles AJAX request to retrieve product sets for a selected catalog only
    // Used in createAdCreative() to avoid loading all product sets globally
    public function getProductSetsByAJAX()
    {
        // Clear any existing output to ensure a clean JSON response
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        // Validate incoming catalog_id parameter
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

        // Ensure an access token is available
        if (!$accessToken) {
            http_response_code(403);
            echo json_encode(['error' => 'Access token missing']);
            return;
        }

        // Fetch product sets for the given catalog
        $userManager = new \App\Models\User();
        $productSets = $userManager->getProductSetsForCatalog($catalogId, $accessToken);

        // Return error if none found
        if (empty($productSets)) {
            echo json_encode([
                'error' => 'No product sets found',
                'catalog_id' => $catalogId
            ]);
            return;
        }

        // Return product sets as JSON
        echo json_encode($productSets);
    }

    // Throws a detailed exception when a Facebook Graph API error occurs
    // Includes message, error type, code, subcode, trace ID, and user-facing messages (if available)
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

    // Handles an AJAX request to retrieve all products from a given product set
    // Used to dynamically load products without refreshing the page
    public function getProductsByAJAX()
    {
        // Ensure no prior output interferes with the JSON response
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json');

        // Validate required query parameter
        if (!isset($_GET['product_set'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing product set ID']);
            return;
        }

        $productSetId = $_GET['product_set'];
        $accessToken = $_SESSION['fb_access_token'] ?? null;

        // Ensure access token is available
        if (!$accessToken) {
            http_response_code(403);
            echo json_encode(['error' => 'Access token missing']);
            return;
        }

        $fbClient = $this->buildClient();

        try {
            // Fetch products belonging to the given product set
            $response = $fbClient->get(
                "/{$productSetId}/products?fields=id,name,retailer_id,price,sale_price",
                $accessToken
            );

            // Extract and return the product data
            $data = $response->getDecodedBody();
            echo json_encode($data['data'] ?? []);
            
        } catch (Exception $e) {
            // Return a JSON-formatted error response
            echo json_encode([
                'error' => 'Failed to fetch products',
                'details' => $e->getMessage()
            ]);
        }
    }

    // Permanently deletes selected Ad Creatives from Facebook
    public function deleteAdCreative()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Fetch all existing ad creatives to display in the selection form
            $campaignManager = new Campaign();
            $adCreatives = $campaignManager->getAdCreatives($this->data['ads_id'], $this->data['fb_access_token']);

            // Load the view to let the user select creatives to delete
            require_once __DIR__ . '/../views/fbdashboard/delete-ad-creative.php';
        } 
        else {
            // Get selected ad creative IDs from the form submission
            $selectedIds = $_POST['selected_creatives'] ?? [];
            $ads_id = $this->data['ads_id'];
            $accessToken = $this->data['fb_access_token'];

            // If no creatives are selected, exit early with a message
            if (empty($selectedIds)) {
                echo "No creatives selected.";
                return;
            }

            echo "<h3>Deleted Creatives:</h3>";

            // Loop through each selected creative and send a DELETE request
            foreach ($selectedIds as $id) {
                $deleteUrl = "https://graph.facebook.com/v22.0/{$id}?access_token={$accessToken}";
                $ch = curl_init($deleteUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response, true);

                // Check if deletion was successful
                if (isset($result['success']) && $result['success']) {
                    echo "Deleted Creative ID: {$id}<br>";
                } else {
                    echo "Failed to delete Creative ID: {$id}<br>";
                }
            }

            // Provide a return link to the dashboard after deletion
            echo "<br><br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }
}
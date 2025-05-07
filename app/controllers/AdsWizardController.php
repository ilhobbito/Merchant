<?php
namespace App\Controllers;

// Load required dependencies and config
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/config.php';

// Suppress specific types of error messages for a cleaner output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 1);

// Import classes and models
use Dotenv\Dotenv;
use App\Models\Campaign;
use App\Models\User;
use FacebookAds\Object\ProductSet;
use FacebookAds\Api;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\Fields\ProductSetFields;

// Controller for managing the streamlined "Ads Wizard" flow.
// This class aims to guide the user step-by-step through campaign creation.

class AdsWizardController {

    // Holds shared data like access tokens and business/ad account IDs
    private $data = [];

    // Optional: direct access token shortcut (not currently used)
    private $accessToken;


    // Constructor initializes environment variables, session, and core Facebook Ads data.
    public function __construct()
    { 
        // Load .env variables (e.g., app ID and secret)
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();

        // Start session if it's not already active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store the Facebook access token if it's already available
        if (isset($_SESSION['fb_access_token'])) {
            $this->data['fb_access_token'] = $_SESSION['fb_access_token'];
        }

        // Store the app credentials from the .env file
        $this->data['app_id']     = $_ENV['FACEBOOK_APP_ID'];
        $this->data['app_secret'] = $_ENV['FACEBOOK_APP_SECRET'];

        // Initialize Facebook SDK client and retrieve core business data
        $fbClient = $this->buildClient();
        $user = new User();

        // Retrieve the Business Manager ID and Ad Account ID, prefixed with "act_"
        $this->data['business_id'] = $user->getBusinessId($fbClient);
        $this->data['ads_id']      = "act_" . $user->getAdsId($fbClient);
    }

    
    // Loads the main Facebook dashboard index view (fallback or starting point).
    public function index() {
        require_once __DIR__ . '/../views/fbdashboard/index.php';
    }

    // Builds and returns a Facebook client instance
    // Used to interact with the Graph API using stored app credentials
    public function buildClient() {
        return new \Facebook\Facebook([
            'app_id'                => $this->data['app_id'],
            'app_secret'            => $this->data['app_secret'],
            'default_graph_version' => 'v22.0',
        ]);
    }

    // The first step in the Ads Wizard flow: creates a Facebook campaign
    // Stores campaign data in $_SESSION for use in the next steps of the wizard
    public function createCampaignWizard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/ads-wizard/step-one-campaign.php';
        }
        else{

            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/campaigns";

            $name = trim($_POST['campaign_name'] ?? '');
            if ($name === '') {
            $_SESSION['flash_campaign_error'] = 'Please enter a campaign name.';
            require_once __DIR__ . '/../views/ads-wizard/step-one-campaign.php';
            return;
            }

            // Create a new cURL resource
            $ch = curl_init($url);

            // Set the POST fields, this is the data inside a campaign
            $postFields = [
                'name'         => $_POST['campaign_name'],
                'objective'    => $_POST['objective'],
                'buying_type' => 'AUCTION',  
                'status'       => $_POST['status'],
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
           
            // Steps in this function:
            // 1. Show the campaign creation form (GET)
            // 2. On POST, validate input and create a campaign via Graph API
            // 3. On success, store campaign data in session and redirect to step 2
            // 4. On error, reload with an error message
            try{
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);
               
                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }

                // Saves a campaign into a sessiopn then ads the newly created campaign id into the same session variable
                $_SESSION['wizard-campaign'] = $postFields;
                $_SESSION['wizard-campaign']['id'] = $decoded['id'] ?? null;

                // This is the success response that will be shown in the next view.
                $_SESSION['flash_campaign'] = [
                    'title'   => "Campaign \"" .$_POST['campaign_name'] . "\" was successfully created!",
                    'body'    => "ID: {$decoded['id']}<br>"
                                . "Status: {$_POST['status']}<br>"
                                . "Objective: {$_POST['objective']}",
                    ];
    
                    header('Location: createAdSetWizard');
                    exit;                          
            }
            catch(\Exception $e){

                // On error the page will reload with an error message. 
                $_SESSION['flash_campaign_error'] = $e->getMessage();
                $fbClient    = $this->buildClient();
                $userManager = new User();
                $campaign    = $_SESSION['wizard-campaign']['id'];
                $catalogs    = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
        }

    }

    // Step 2 of the campaign wizard: creates an Ad Set and stores it in session for the next step
    public function createAdSetWizard()
    {
        $fbClient = $this->buildClient();
        $userManager = new User();

        // Retrieve the campaign ID created in step 1
        $campaign = $_SESSION['wizard-campaign']['id'];
        $catalogs = $userManager->getAllCatalogs($fbClient);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';    
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // If the objective is SALES, ensure a catalog and product set are selected
            // (Sales campaigns require product-based targeting)
            if($_SESSION['wizard-campaign']['objective'] === 'OUTCOME_SALES'){

                if($_POST['catalog_id'] !== '' && $_POST['product_set'] !== 'none'){
                    $_SESSION['wizard-catalog-id'] = $_POST['catalog_id'];
                    $_SESSION['wizard-productset'] = $_POST['product_set'];
                }
                else{
                    $_SESSION['flash_adset_error'] = 'A product set must be selected!';
                    require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                    return;
                }
            }
          
            // Validate all required form fields before creating the Ad Set
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adsets";
            $ch = curl_init($url);

            $name = trim($_POST['adset_name'] ?? '');
            if ($name === '') {
                $_SESSION['flash_adset_error'] = 'A name for the Ad set must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
            if(!isset($_POST['billing_event'])){
                $_SESSION['flash_adset_error'] = 'A billing event must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
            if(!isset($_POST['bid_strategy'])){
                $_SESSION['flash_adset_error'] = 'A bid strategy must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
            if(!isset($_POST['optimization_goal'])){
                $_SESSION['flash_adset_error'] = 'An optimization goal must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
            if(!isset($_POST['daily_budget']) || $_POST['daily_budget'] <= 0){
                $_POST['daily_budget'] = 1500;

            }

            // Prepare data payload for the new Ad Set (to be submitted to Facebook Graph API)
            $postFields = [
                'name'            => $_POST['adset_name'],
                'campaign_id'     => $campaign, 
                'daily_budget'    => $_POST['daily_budget'], // Daily budget is in the smallest currency unit. Set cost like this, "1000" = 10.00
                'billing_event'   => $_POST['billing_event'],
                'bid_strategy'    => $_POST['bid_strategy'],
                'optimization_goal' => $_POST['optimization_goal'],   
                'targeting'       => json_encode([
                    'geo_locations' => [
                        'countries' => ['SE']  
                    ],
                    'publisher_platforms' => ['facebook'],
                    'facebook_positions' => ['feed']
                ]),
                'status'          => $_POST['status'] ?? 'PAUSED',
                'access_token'    => $this->data['fb_access_token'],
                
                // Advertisement Schedule TODO: Make it so the user can set their own start and end times.
                'start_time'        => (new \DateTime('+1 day'))->format(\DateTime::ISO8601),
                'end_time'          => (new \DateTime('+7 days'))->format(\DateTime::ISO8601),

                'promoted_object[pixel_id]'         => $_ENV['PIXEL_ID'], 
                'promoted_object[custom_event_type]'=> 'PURCHASE', 
            ];

            // Conditionally include bid_amount only if a capped strategy is selected
            if ($_POST['bid_strategy'] !== 'LOWEST_COST_WITHOUT_CAP' && isset($_POST['bid_amount']) && is_numeric($_POST['bid_amount']) && $_POST['bid_amount'] > 0) {
                $postFields['bid_amount'] = (int) $_POST['bid_amount'];
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

               // Store ad set data and ID in session for future use in the wizard
                $_SESSION['wizard-adset'] = $postFields;
                $_SESSION['wizard-adset']['id'] = $decoded['id'] ?? null;
                
                // Formats the budget price to show two decimals
                $raw = (int) ($_POST['daily_budget'] ?? 0);
                $display = number_format($raw / 100, 2, '.', ',');

                // Store success message for step 3's view (flash-based feedback)
                $_SESSION['flash_adset'] = [
                'title'   => "Ad Set “{$_POST['adset_name']}” was successfully created!",
                'body'    => "ID: {$decoded['id']}<br>"
                            . "Daily budget: {$display} SEK<br>"
                            . "Target Country: SE",
                ];

                header('Location: createAdCreativeWizard');
                exit;

            } catch (\Exception $e) {
                // On error the page will reload with an error message. 
                $_SESSION['flash_adset_error'] = $e->getMessage();
                $fbClient    = $this->buildClient();
                $userManager = new User();
                $adset    = $_SESSION['wizard-adset']['id'];
                $catalogs    = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
                return;
            }
            echo "<a href='Merchant/public/adsWizard/createAdCreativeWizard'>Next step</a>";
        }
        
    }

    // Step 3 of the campaign wizard: creates an Ad Creative and stores it in session
    public function createAdCreativeWizard()
    {
        $fbClient = $this->buildClient();

        // If GET request, show the creative creation form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);

            require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
        } 
        else {
            $productSetId = $_SESSION['wizard-productset'];
            $accessToken = $this->data['fb_access_token'];

            // Validate required input fields and return with flash message if invalid
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adcreatives";
            $ch = curl_init($url);

            $name = trim($_POST['creative_name'] ?? '');
            if ($name === '') {
                $_SESSION['flash_creative_error'] = 'A name for the Ad creative must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
            if ($_POST['link'] == '') {
                $_SESSION['flash_creative_error'] = 'A link cannot be empty!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
            if ($_POST['page_id'] == '') {
                $_SESSION['flash_creative_error'] = 'A proper Page ID must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }

            /**
             * Build the object_story_spec dynamically based on the campaign objective
             * - For TRAFFIC campaigns: use uploaded image with link_data
             * - For SALES campaigns: use dynamic template_data with product info
             */
            if ($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_TRAFFIC') {
                $imageHash = $this->imageHash($_FILES['ad_image']);

                $objectStorySpec = [
                    'page_id' => $_POST['page_id'],
                    'link_data' => [
                        'link' => $_POST['link'] ?? 'https://www.example.com',
                        'message' => $_POST['message'],
                        'image_hash' => $imageHash,
                        'call_to_action' => [
                            'type' => $_POST['call_to_action'],
                            'value' => [
                                'link' => $_POST['link'] ?? 'https://www.example.com'
                            ]
                        ]
                    ]
                ];

                $postFields = [
                    'name' => $_POST['creative_name'] ?? 'MyAdCreative',
                    'object_story_spec' => json_encode($objectStorySpec),
                    'access_token' => $accessToken
                ];
            } 
            else if ($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_SALES') {
                // Fetch sample product data to confirm structure
                $productResponse = $fbClient->get(
                    "/$productSetId/products?fields=name,retailer_id,price,sale_price,url&limit=1",
                    $accessToken
                );

                /**
                 * For sales-based ads, use template_data with dynamic product fields
                 * Facebook will substitute placeholders like {{product.name}} at runtime
                 */
                $objectStorySpec = [
                    'page_id' => $_POST['page_id'],
                    'template_data' => [
                        'link' => $_POST['link'] ?? 'https://www.example.com',
                        'message' => $_POST['message'],
                        'name' => '{{product.name}}',
                        'description' => 'Buy now: {{product.name}} only for {{product.price}}!',
                        'call_to_action' => [
                            'type' => $_POST['call_to_action'],
                            'value' => [
                                'link' => 'https://www.example.com' // fallback link
                            ]
                        ],
                    ]
                ];

                $postFields = [
                    'name'               => $_POST['creative_name'] ?? 'MyAdCreative',
                    'object_story_spec'  => json_encode($objectStorySpec),
                    'access_token'       => $accessToken,
                    'product_set_id'     => $_SESSION['wizard-productset'],
                    'catalog_id'         => $_SESSION['wizard-catalog-id'],
                ];
            }

            // Execute POST request to create ad creative
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

                /**
                 * Save creative info to session for next step (ad creation)
                 * Includes ID and optional image hash
                 */
                $_SESSION['wizard-creative'] = array_merge(
                    $_SESSION['wizard-creative'] ?? [],
                    $postFields,
                    [
                        'id' => $decoded['id'] ?? null,
                        'image_hash' => $imageHash ?? null
                    ]
                );

                // Prepare and flash success message to be displayed in step 4
                $_SESSION['flash_adset'] = [
                    'title' => "Ad Creative \"{$_POST['creative_name']}\" was successfully created!",
                    'body'  => "ID: {$decoded['id']}<br>"
                            . "Call to Action: {$_POST['call_to_action']}<br>"
                            . "Objective: {$_POST['objective']}<br>"
                            . "Link: {$_POST['link']}  | Page ID: {$_POST['page_id']}<br>"
                            . "Ad Message: \"{$_POST['message']}\"",
                ];

                // Proceed to step 4
                header('Location: createAdvertisementWizard');
                exit;

            } catch (\Exception $e) {
                // On failure, show form again with error message
                $_SESSION['flash_creative_error'] = $e->getMessage();
                $userManager = new User();
                $catalogs = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
        }
    }

    // Step 4 of the campaign wizard: finalizes the ad by combining the ad set and ad creative
    public function createAdvertisementWizard()
    {
        $userManager = new User();

        // Retrieve previously created ad set and ad creative from session
        $adSet = $_SESSION['wizard-adset'];
        $adCreative = $_SESSION['wizard-creative'];

        // For sales-based campaigns, also fetch the associated product set
        if ($_SESSION['wizard-campaign']['objective'] == "OUTCOME_SALES") {
            $productSet = $userManager->getProductSetById($_SESSION['wizard-creative']['product_set_id'], $_SESSION['fb_access_token']);
        }

        // If not submitted yet, load the ad creation view
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
        } 
        else {
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/ads";
            $ch = curl_init($url);

            // Validate ad name
            $name = trim($_POST['ad_name'] ?? '');
            if ($name === '') {
                $_SESSION['flash_ad_error'] = 'A name for the Advertisement must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
                return;
            }

            // Ensure required IDs are present before building the ad
            if (isset($_SESSION['wizard-adset']['id']) && isset($_SESSION['wizard-creative']['id'])) {
                $postFields = [
                    'name'       => $_POST['ad_name'],
                    'adset_id'   => $_SESSION['wizard-adset']['id'],
                    'creative'   => json_encode([
                        'creative_id' => $_SESSION['wizard-creative']['id']
                    ]),
                    'status'     => $_POST['status'] ?? 'PAUSED', // Keep it paused by default
                    'access_token' => $this->data['fb_access_token'],
                ];
            } 
            else {
                echo "Ad Set and Ad Creative require valid IDs.";
                return;
            }

            // Execute request to create ad
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

                // Success: redirect to results page
                $_SESSION['flash_ad'] = [
                    'title' => "Ad \"{$_POST['ad_name']}\" successfully created!",
                    'body'  => "Ad ID: {$decoded['id']}<br>"
                            . "Ad Set ID: {$_SESSION['wizard-adset']['id']}<br>"
                            . "Ad Creative ID: {$_SESSION['wizard-creative']['id']}<br>"
                            . "Status: {$_POST['status']}",
                ];

                header('Location: resultWizard');
                exit;
            } 
            catch (\Exception $e) {
                // On error, reload view with message
                $_SESSION['flash_ad_error'] = $e->getMessage();
                $fbClient = $this->buildClient();
                $catalogs = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
                return;
            }

            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
        }
    }

    // Final step of the Ads Wizard: compiles and displays a full summary of the created campaign, ad set, and ad creative.
    // Gives the user an overview of their selections, including targeting details, visual previews, and platform-specific data.
    function resultWizard()
    {
        $userManager = new User();
        $fbClient = $this->buildClient();

        // Retrieve campaign, ad set, and creative from session
        $campaign = $_SESSION['wizard-campaign'];
        $adset = $_SESSION['wizard-adset'];
        $creative = $_SESSION['wizard-creative'];

        // If the campaign objective is SALES, also fetch the product set and catalog details
        if ($campaign['objective'] === "OUTCOME_SALES") {
            $productSet = $userManager->getProductSetById($creative['product_set_id'], $_SESSION['fb_access_token']);
            $catalog = $userManager->getCatalogById($_SESSION['wizard-catalog-id'], $_SESSION['fb_access_token']);

            // Retrieve product images to preview from the product set
            $productImages = $userManager->getProductSetProducts(
                $creative['product_set_id'],
                $_SESSION['fb_access_token']
            );
        }

        // Decode targeting if stored as a JSON string
        if (isset($adset['targeting']) && is_string($adset['targeting'])) {
            $adset['targeting'] = json_decode($adset['targeting'], true);
        }

        // Decode object story spec for rendering creative details
        if (isset($creative['object_story_spec']) && is_string($creative['object_story_spec'])) {
            $creative['object_story_spec'] = json_decode($creative['object_story_spec'], true);
        }

        // If the campaign used a direct image upload (i.e., not dynamic from catalog), store image URL
        if (isset($_SESSION['wizard-creative']['image_hash'])) {
            $imageUrl = $_SESSION['wizard-creative']['image_url'] ?? null;
        }

        // Generate a human-readable preview of the dynamic product description
        if ($campaign['objective'] === "OUTCOME_SALES") {
            $productResponse = $fbClient->get(
                "/{$creative['product_set_id']}/products?fields=name,price,sale_price,url&limit=1",
                $_SESSION['fb_access_token']
            );

            $productData = $productResponse->getDecodedBody()['data'][0];
            $descriptionTemplate = $creative['object_story_spec']['template_data']['description'];

            $previewDescription = str_replace(
                ['{{product.name}}', '{{product.price}}'],
                [$productData['name'], $productData['price']],
                $descriptionTemplate
            );
        }

        // Load final result/summary screen
        require_once __DIR__ . '/../views/ads-wizard/end-screen.php';
    }

    // Generalized error handling with built in error messages
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

    // Handles the validation and upload of a user-provided image file,
    // then returns a Facebook-compatible image hash for use in ad creatives.
    public function imageHash($uploadedFile)
    {
        // Allowed MIME types for Facebook ad images
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        // Validate file type
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            throw new \Exception("Unsupported image format. Please upload a JPG, PNG, or GIF.");
        }

        // Validate file upload status
        if (!isset($uploadedFile) || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Image upload failed or no image provided.");
        }

        // Extract necessary info from uploaded file
        $tmpPath = $uploadedFile['tmp_name'];
        $originalName = $uploadedFile['name'];
        $mimeType = mime_content_type($tmpPath);

        // Endpoint to upload image to Facebook
        $url = "https://graph.facebook.com/v17.0/{$this->data['ads_id']}/adimages";
        $ch = curl_init($url);

        // Prepare POST payload with the image file and access token
        $postFields = [
            'filename' => new \CURLFile($tmpPath, $mimeType, $originalName),
            'access_token' => $this->data['fb_access_token']
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode Facebook response
        $data = json_decode($response, true);

        // Extract hash and URL from response
        $imageInfo = $data['images'][$originalName] ?? null;

        if (!$imageInfo || !isset($imageInfo['hash'])) {
            throw new \Exception("Failed to get image hash from Facebook API response.");
        }

        // Store both the hash and image URL in session for later use
        $_SESSION['wizard-creative']['image_hash'] = $imageInfo['hash'];
        $_SESSION['wizard-creative']['image_url']  = $imageInfo['url'];

        // Return hash to be embedded in object_story_spec
        return $imageInfo['hash'];
    }
}
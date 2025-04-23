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

class AdsWizardController{

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

            // Set the POST fields
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
           
            // Check for cURL errors
            try{
                if ($error) {
                    throw new \Exception("cURL Error: " . $error);
                }

                $decoded = json_decode($response, true);
               
                if (isset($decoded['error'])) {
                    $this->throwFacebookApiException($decoded['error']);
                }
                $_SESSION['wizard-campaign'] = $postFields;
                $_SESSION['wizard-campaign']['id'] = $decoded['id'] ?? null;

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

    // Makes an API request to Post an Ad Set to the users Ads Account
    public function createAdSetWizard()
    {
        $fbClient = $this->buildClient();
        $userManager = new User();
        $campaign = $_SESSION['wizard-campaign']['id'];
        $catalogs = $userManager->getAllCatalogs($fbClient);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';    
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $campaign = $_SESSION['wizard-campaign']['id'];

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

                'promoted_object[pixel_id]'         => $_ENV['PIXEL_ID'], // TODO: Make Pixel more dynamic in case more than one pixel exists
                'promoted_object[custom_event_type]'=> 'PURCHASE',

            ];
            if ($_POST['bid_strategy'] !== 'LOWEST_COST_WITHOUT_CAP' && isset($_POST['bid_amount']) && is_numeric($_POST['bid_amount']) && $_POST['bid_amount'] > 0) {
                $postFields['bid_amount'] = (int) $_POST['bid_amount'];
            }

            // if (isset($_POST['dsa_beneficiary']) && isset($_POST['dsa_payor'])){
            //     $postFields['dsa_beneficiary'] = $_POST['dsa_beneficiary'];
            //     $postFields['dsa_payor'] = $_POST['dsa_payor'];   
            // }
            // else{
            //     $_SESSION['flash_adset_error'] = 'DSA beneficiary/payor must be set!';
            //     require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';
            //     return;
            // }

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

                $_SESSION['wizard-adset'] = $postFields;
                $_SESSION['wizard-adset']['id'] = $decoded['id'] ?? null;
                
                // Formats the budget price to show two decimals
                $raw = (int) ($_POST['daily_budget'] ?? 0);
                $display = number_format($raw / 100, 2, '.', ',');


                $_SESSION['flash_adset'] = [
                'title'   => "Ad Set “{$_POST['adset_name']}” was successfully created!",
                'body'    => "ID: {$decoded['id']}<br>"
                            . "Daily budget: {$display} SEK<br>"
                            . "Target Country: SE",
                ];

                header('Location: createAdCreativeWizard');
                exit;

            } catch (\Exception $e) {

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

    public function createAdCreativeWizard()
    {
        $fbClient = $this->buildClient();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            
            $userManager = new User();
            $catalogs = $userManager->getAllCatalogs($fbClient);
            require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
        }
        else{

            $productSetId = $_SESSION['wizard-productset'];
            $accessToken = $this->data['fb_access_token'];

            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adcreatives";
            $ch = curl_init($url);
            

            $name = trim($_POST['creative_name'] ?? '');
            if ($name === '') {
                $_SESSION['flash_creative_error'] = 'A name for the Ad creative must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
            if ($_POST['link'] == '') {
                $_SESSION['flash_creative_error'] = 'A link can not be empty!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
            if ($_POST['page_id'] == '') {
                $_SESSION['flash_creative_error'] = 'A proper page Id must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;
            }
            // Changes the data sent to the API depending on the objective type
            if($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_TRAFFIC'){
                $imageHash = $this->imageHash();
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
                    'access_token' => $this->data['fb_access_token']
                ];
            }
            else if($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_SALES'){

                $productResponse = $fbClient->get(
                    "/$productSetId/products?fields=name,retailer_id,price,sale_price,url&limit=1",
                    $accessToken
                );

                $productData = $productResponse->getDecodedBody()['data'][0];

                if(empty($_POST['description'])){
                    $_POST['description'] = 'Now only ' . $productData['sale_price'] . ' (was ' . $productData['price'] . ')';
                }

                $objectStorySpec = [
                    'page_id' => $_POST['page_id'],
                    'template_data' => [
                        'link' => $_POST['link'] ?? 'https://www.example.com',
                        'message' => $_POST['message'],
                        'name' => $productData['name'],
                        'description' => $_POST['description'],
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
                    'product_set_id'     => $_SESSION['wizard-productset'],
                    'catalog_id'         => $_SESSION['wizard-catalog-id'],
                    
                ];
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

                $_SESSION['wizard-creative'] = $postFields;
                $_SESSION['wizard-creative']['id'] = $decoded['id'] ?? null;
                $_SESSION['flash_adset'] = [
                    'title'   => "Ad Creative \"" .$_POST['creative_name'] . "\" was successfully created!",
                    'body'    => "ID: {$decoded['id']}<br>"
                                . "Call to Action: {$_POST['call_to_action']}<br>"
                                . "Objective: {$_POST['objective']}<br>"
                                . "Link: {$_POST['link']}  | Page Id: {$_POST['page_id']}<br>"
                                . "Ad Message: \"{$_POST['message']}\"",
                    ];
    
                    header('Location: createAdvertisementWizard');
                    exit;                     
               
            }
            catch(\Exception $e){
                $_SESSION['flash_creative_error'] = $e->getMessage();
                $fbClient    = $this->buildClient();
                $userManager = new User();
                $creative    = $_SESSION['wizard-creative']['id'];
                $catalogs    = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-three-adcreative.php';
                return;

            }           
            
        }
    }

    public function createAdvertisementWizard()
    {
        $userManager = new User();
        $adSet = $_SESSION['wizard-adset'];
        $adCreative = $_SESSION['wizard-creative'];
        if($_SESSION['wizard-campaign']['objective'] == "OUTCOME_SALES"){
            $productSet = $userManager->getProductSetById($_SESSION['wizard-creative']['product_set_id'], $_SESSION['fb_access_token']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
        }
        else{
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/ads";
            $ch = curl_init($url);


            $name = trim($_POST['ad_name'] ?? '');
            if ($name === '') {
                $_SESSION['flash_ad_error'] = 'A name for the Advertisement must be set!';
                require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
                return;
            }
            if(isset($_SESSION['wizard-adset']['id']) && isset($_SESSION['wizard-creative']['id'])){
                $postFields = [
                    'name'     => $_POST['ad_name'],
                    'adset_id' => $_SESSION['wizard-adset']['id'],
                    // The 'creative' field expects a JSON object containing 'creative_id'
                    'creative' => json_encode([
                        'creative_id' => $_SESSION['wizard-creative']['id']
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
                $_SESSION['flash_ad_error'] = $e->getMessage();
                $fbClient    = $this->buildClient();
                $userManager = new User();
                $ad    = $_SESSION['wizard-advertisement']['id'];
                $catalogs    = $userManager->getAllCatalogs($fbClient);
                require_once __DIR__ . '/../views/ads-wizard/step-four-advertisement.php';
                return;       
            }
            
            echo "<br><a href='/Merchant/public/fbdashboard'>Return</a>";
            
            
        }
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
}
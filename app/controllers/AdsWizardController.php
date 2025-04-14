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

            if(!isset($_POST['campaign_name'])){
                echo "A campaign name must be set!";
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
                echo "Campaign \"" .$_POST['campaign_name'] . "\" with ID '" . $response . "' successfully created<br>";
                echo "Status: "  . $_POST['status'] . "     Objective: " . $_POST['objective'] . "<br>";
                echo "<a href='/Merchant/public/adsWizard/createAdSetWizard'>Next step</a>";
                
               
            }
            catch(\Exception $e){
                echo "Error creating Campaign: <br>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
                echo "<a href='/Merchant/public/fbdashboard'>Return</a>";
            }
        }

    }

    // Makes an API request to Post an Ad Set to the users Ads Account
    public function createAdSetWizard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $fbClient = $this->buildClient();
            $userManager = new User();
            $campaign = $_SESSION['wizard-campaign']['id'];
            $catalogs = $userManager->getAllCatalogs($fbClient);

            require_once __DIR__ . '/../views/ads-wizard/step-two-adset.php';    
        }

        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $campaign = $_SESSION['wizard-campaign']['id'];
            
            $url = "https://graph.facebook.com/v22.0/{$this->data['ads_id']}/adsets";
            $ch = curl_init($url);

            if(!isset($_POST['adset_name'])){
                echo "A name for the Adset must be set!";
                return;
            }

            $postFields = [
                'name'            => $_POST['adset_name'],
                'campaign_id'     => $campaign, 
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


}
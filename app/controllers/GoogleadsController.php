<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
use Google_Client;
use Google\Ads\GoogleAds\V19\Resources\Customer;
use Google\Ads\GoogleAds\V19\Services\CustomerClientOperation;
use Google\Ads\GoogleAds\V19\Services\CreateCustomerClientRequest;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsException;
use Google\Ads\GoogleAds\V19\Services\ListAccessibleCustomersRequest;


class GoogleAdsController{
    private $client;
    public function __construct(){
        // Not sure if necessary, will try to remove to check at a later point
        $this->client = new Google_Client();
        $this->client->setAuthConfig('../client_secret.json');
    }

    public function index(){
        require_once '../app/views/googleads/index.php';
    } 

    public function listCampaign(){

        
        $managerCustomerId = '9816924442'; // Replace with manager id that has developer token
        $storedToken = json_decode(file_get_contents('token.json'), true);

        // Check if refresh_token is present
        if (isset($storedToken['refresh_token'])){
            $refresh_token = $storedToken['refresh_token'];
        
            // Refresh the access token
            $token_data = $this->client->fetchAccessTokenWithRefreshToken($refresh_token);
        
            // Use the new access token
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];
            } else {
                echo "Error: No access token found after refresh!";
                return;
            }
        } 
        else{
            echo "Error: No refresh token found in token.json!";
            return;
        } 

        // Parses the google_ads_php.ini file to access it's data
        $storedIni = parse_ini_file('../google_ads_php.ini', true); 
        if (isset($storedIni['GOOGLE_ADS']['developerToken'])) {
            $developer_token = $storedIni['GOOGLE_ADS']['developerToken'];
        } else {
            echo "No developertoken could be retrieved!";
            return;
        }
        $customer_id = "5533436415"; // Replace with client id that has been made through the api
        // Uses api to search for the specific user
        $url = "https://googleads.googleapis.com/v19/customers/{$customer_id}/googleAds:searchStream";

        $headers = [
            "Authorization: Bearer " . $access_token,
            "developer-token: " . $developer_token,
            "Content-Type: application/json",
            "login-customer-id: " . $managerCustomerId
        ];

        // Use GAQL to check for campaigns
        $payload = json_encode([
        "query" => "SELECT campaign.id, campaign.name FROM campaign LIMIT 10"
        ]);

        // Builds an API call with the data previously provided, in this case it is a Post with the payload GAQL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        require_once '../app/views/googleads/list-campaign.php';
        
    }

    function createTestClient(): void
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){

        require_once '../app/views/googleads/create-test-client.php';
        } else {
        

        $managerCustomerId = '9816924442'; // Replace with manager id that has developer token
        $configPath = __DIR__ . '/../../google_ads_php.ini'; 
        
        // Build OAuth2 credentials from the OAUTH2 section
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile($configPath) 
            ->build();
        
        // Build the main client from the GOOGLE_ADS section, then attach the OAuth2 creds
        $googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile($configPath) 
            ->withOAuth2Credential($oAuth2Credential)
            ->withLoginCustomerId($managerCustomerId) 
            ->build();

        $customerServiceClient = $googleAdsClient->getCustomerServiceClient();

        // Define the new customer (client) account.
        $customerClient = new Customer([
            // Will have to change name to not create duplicates TODO: Make it a input variable
            'descriptive_name' => 'Test Client Account',
            'currency_code' => 'USD',
            'time_zone' => 'America/New_York'
        ]);

        // Build the CreateCustomerClientRequest using camelCase keys.
        $request = new CreateCustomerClientRequest([
            'customer_id' => $managerCustomerId,
            'customer_client' => $customerClient,
        ]);
    
        // Create the test client account.
        $response = $customerServiceClient->createCustomerClient($request);

        printf("Test client account created with resource name: %s\n", $response->getResourceName());
        echo "<a href='/Merchant/public/googleads'><br>Return</a>";
    }
}

    public function listAccountsWithLibrary()
    {
        // Had a ton of trouble to read OAuth2 Credentials from the google_ads_php.ini file so 
        // had to build them individually and then merge them to be able to read it correctly.
        $configPath = __DIR__ . '/../../google_ads_php.ini'; 
    
        // Build OAuth2 credentials from the OAUTH2 section
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile($configPath) 
            ->build();

        // Build the main client from the GOOGLE_ADS section, then attach the OAuth2 creds
        $googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile($configPath) 
            ->withOAuth2Credential($oAuth2Credential) 
            ->build();

        // This gets a list with all accounts in the account list of the manager ID provided
        $customerServiceClient = $googleAdsClient->getCustomerServiceClient();
        $request = new ListAccessibleCustomersRequest();
        $response = $customerServiceClient->listAccessibleCustomers($request);

        require_once '../app/views/googleads/list-accounts-with-library.php';

    }

    public function setTestBudget(){

        // Makes token.json's data retrievable
        $storedToken = json_decode(file_get_contents('token.json'), true);

        // Check if refresh_token is present
        if (isset($storedToken['refresh_token'])){
            $refresh_token = $storedToken['refresh_token'];
        
            // Refresh the access token
            $token_data = $this->client->fetchAccessTokenWithRefreshToken($refresh_token);
        
            // Use the new access token
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];
            } else {
                echo "Error: No access token found after refresh!";
                return;
            }
        } 
        else{
            echo "Error: No refresh token found in token.json!";
            return;
        } 

        // makes google_ads_php.ini's data retrievable to search for the developerToken
        $storedIni = parse_ini_file('../google_ads_php.ini', true); 

        if (isset($storedIni['GOOGLE_ADS']['developerToken'])) {
            $developer_token = $storedIni['GOOGLE_ADS']['developerToken'];
        } else {
            echo "No developertoken could be retrieved!";
            return;
        }
    
        $managerCustomerId = '9816924442'; // Replace with manager id that has developer token
        $customer_id = '5533436415'; // Replace with client id that has been made through the api
        
        // uses searchStream to check for the specific user
        $url = "https://googleads.googleapis.com/v19/customers/{$customer_id}/googleAds:searchStream";

        $headers = [
            "Authorization: Bearer " . $access_token,
            "developer-token: " . $developer_token,
            "Content-Type: application/json",
            "login-customer-id: " . $managerCustomerId, // Is the comma needed? Probably not. TODO: Test to remove it
        ];
        
        // GAQL query: get basic customer info
        $payload = json_encode([
          "query" => "SELECT customer.id, customer.descriptive_name, customer.status FROM customer LIMIT 1"
        ]);
        
        // Builds an API Call with the previously provided data to get info about a specific account
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Checks if it gets a valid response and if it does writes it out in typical json format
        if ($http_code == 200) {
            $responseDecoded = json_decode($response, true);
            echo "<pre>" . json_encode($responseDecoded, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "GAQL Request Failed. HTTP Code: $http_code\nResponse: $response";
        }
        
        // Used to create a budget
        $url = "https://googleads.googleapis.com/v19/customers/{$customer_id}/campaignBudgets:mutate";

        $budget_data = json_encode([
            "operations" => [
                [
                    "create" => [
                        // Name will conflict if not updated. TODO: Make this into an input variable.
                        "name" => "Test Campaign Budget",
                        "amountMicros" => "5000000",  // $5 budget since google handles this in micros
                        "deliveryMethod" => "STANDARD"
                    ]
                ]
            ]
        ]);
        // Creates an API Call to post a new budget
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $budget_data);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
    
        // ✅ Improved error handling
        if ($http_code == 200) {
            echo "✅ Budget Created Successfully: " . $response;
        } else {
            echo "❌ Budget Creation Failed! HTTP Code: " . $http_code . "<br>";
            if (!empty($curl_error)) {
                echo "cURL Error: " . $curl_error . "<br>";
            } else {
                echo "API Response: " . $response . "<br>";
            }
        }
        require_once '../app/views/googleads/set-test-budget.php';
    }
    
    
}
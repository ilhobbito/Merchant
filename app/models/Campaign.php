<?php
namespace App\Models;

class Campaign{
    
    // Retrieves all campaigns for a given Facebook Ad Account.
    // The ad account ID (must be prefixed with "act_" in the controller)
    public function getCampaigns($ads_id, $fb_access_token) {
        // Fields to retrieve for each campaign
        $fields = 'id,name,status';

        // Construct the Facebook Graph API URL
        $url = "https://graph.facebook.com/v17.0/{$ads_id}/campaigns?fields={$fields}&access_token={$fb_access_token}";

        // Execute cURL request to Facebook API
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the JSON response into a PHP array
        $campaigns = json_decode($response, true);

        // Facebook wraps all campaign data under a 'data' key
        if (isset($campaigns['data'])) {
            return $campaigns;
        }
        // Return error message if present
        elseif (isset($campaigns['error'])) {
            return ['error' => $campaigns['error']['message']];
        }
        // Fallback for unknown response structure
        else {
            return ['error' => 'Could not load campaigns - unexpected response'];
        }
    }

    // Retrieves all ad sets for a given Facebook Ad Account.
    // This includes key configuration fields such as budget, bidding, and optimization strategy.
    public function getAdSets($ads_id, $fb_access_token) {
        // Define the fields to fetch for each ad set
        $url = "https://graph.facebook.com/v22.0/{$ads_id}/adsets?access_token={$fb_access_token}"
            . "&fields=id,name,daily_budget,billing_event,bid_strategy,optimization_goal,promoted_object";

        // Execute the cURL request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the response into an associative array
        $adSets = json_decode($response, true);

        // Return the ad set data if successful
        if (isset($adSets['data'])) {
            return $adSets;
        }
        // Return the error if Facebook API responded with one
        elseif (isset($adSets['error'])) {
            return ['error' => $adSets['error']['message']];
        }
        // Fallback for unexpected responses
        else {
            return ['error' => 'Could not load ad sets - unexpected response'];
        }
    }

    // Retrieves all active ad creatives for a given Facebook Ad Account.
    // Ad creatives define the visual and textual content used in actual advertisements.
    public function getAdCreatives($ads_id, $fb_access_token) {
        // Build the Graph API URL to fetch creatives with status ACTIVE
        $url = "https://graph.facebook.com/v17.0/{$ads_id}/adcreatives"
            . "?access_token={$fb_access_token}"
            . "&fields=id,name,object_story_spec"
            . "&effective_status=['ACTIVE']";

        // Perform cURL request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response into associative array
        $adCreatives = json_decode($response, true);

        // Return creatives if present
        if (isset($adCreatives['data'])) {
            return $adCreatives;
        }
        // Return any API error as a formatted response
        elseif (isset($adCreatives['error'])) {
            return ['error' => $adCreatives['error']['message']];
        }
        // Handle unexpected or malformed responses
        else {
            return ['error' => 'Could not load ad creatives - unexpected response'];
        }
    }
}
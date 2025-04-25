<?php
namespace App\Models;

class Campaign{
    
    public function getCampaigns($ads_id, $fb_access_token){

        $fields = 'id,name,status';

        $url = "https://graph.facebook.com/v17.0/{$ads_id}/campaigns?fields={$fields}&access_token={$fb_access_token}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $campaigns = json_decode($response, true);

        // Facebook returns a structure wiht 'data' as the main variable after decoding the json, so all the other fields have to go through the 'data' variable first to be used
        
        if (isset($campaigns['data'])) {
            return $campaigns;
        } elseif (isset($campaigns['error'])) {
            return ['error' => $campaigns['error']['message']];
        } else {
            return ['error' => 'Could not load campaigns - unexpected response'];
        }
        
    }

    public function getAdSets($ads_id, $fb_access_token){

        $url = "https://graph.facebook.com/v22.0/{$ads_id}/adsets?access_token={$fb_access_token}&fields=id,name,daily_budget,billing_event,bid_strategy,optimization_goal,promoted_object";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $adSets = json_decode($response, true);
        
        if (isset($adSets['data'])) {
            return $adSets;
        } elseif (isset($adSets['error'])) {
            return ['error' => $adSets['error']['message']];
        } else {
            return ['error' => 'Could not load ad sets - unexpected response'];
        }
    }

    public function getAdCreatives($ads_id, $fb_access_token){

        $url = "https://graph.facebook.com/v17.0/{$ads_id}/adcreatives?access_token={$fb_access_token}&fields=id,name,object_story_spec&effective_status=['ACTIVE']";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $adCreatives = json_decode($response, true);

        if (isset($adCreatives['data'])) {
            return $adCreatives;
        } elseif (isset($adCreatives['error'])) {
            return ['error' => $adCreatives['error']['message']];
        } else {
            return ['error' => 'Could not load ad creatives- unexpected response'];
        }
    }

    public function deleteAdCreatives($ads_id, $fb_access_token) {
        
        $adCreatives = $this->getAdCreatives($ads_id, $fb_access_token);

        // STEP 2: Loop through each creative
        foreach ($adCreatives['data'] as $creative) {
            $id = $creative['id'];
            $name = isset($creative['name']) ? $creative['name'] : 'No Name';
    
            // STEP 3: Filter logic – customize this!
            if (stripos($name, 'TEST') !== false || empty($name)) {
                echo "Deleting creative: {$name} (ID: {$id})...\n";
    
                // STEP 4: Make DELETE call
                $deleteUrl = "https://graph.facebook.com/v22.0/{$id}?access_token={$fb_access_token}";
                $delCh = curl_init($deleteUrl);
                curl_setopt($delCh, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($delCh, CURLOPT_RETURNTRANSFER, true);
                $deleteResponse = curl_exec($delCh);
                $error = curl_error($delCh);
                curl_close($delCh);
    
                if ($error) {
                    echo "Error deleting {$id}: {$error}\n";
                } else {
                    $result = json_decode($deleteResponse, true);
                    if (isset($result['success']) && $result['success']) {
                        echo "Successfully deleted {$id}!\n";
                    } else {
                        echo "Failed to delete {$id}. Response: {$deleteResponse}\n";
                    }
                }
            } else {
                echo "Keeping creative: {$name} (ID: {$id})\n";
            }
        }
    }
}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ad Set</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <div style="display: flex; gap: 20px;">
        <!-- Form container -->
        <div style="flex: 1;">
            <form method="POST" action="createAdset">
                <label for="adset_name">Adset Name: </label>
                <input type="text" name="adset_name" id="adset_name">
                <br><br>

                <h3>Select a campaign to create the adset for</h3>
                <label for="campaign_id">Campaign Id: </label>
                <select name="campaign_id" id="campaign_id">
                    <?php foreach($campaigns['data'] as $campaign){ ?>
                            <option value="<?php echo $campaign['id']?>"><?php echo "Name: " . $campaign['name'] . " Id: " . $campaign['id']?></option>
                    <?php }?>
                </select><br><br>

                <h3>Select a daily budget, 1000 = 10.00 sek</h3>
                <label for="daily_budget">Daily Budget: </label>
                <input type="number" name="daily_budget" id="daily_budget" min="1500">
                <br><br>

                <label for="billing_event">Billing Event: </label>
                <select name="billing_event" id="billing_event">
                    <option value="IMPRESSIONS">Impressions</option>
                </select>
                <br><br>

                <label for="bid_strategy">Bid Strategy: </label>
                <select name="bid_strategy" id="bid_strategy">
                    <option value="LOWEST_COST_WITHOUT_CAP">Lowest cost without cap</option>
                </select>
                <br><br>

                <label for="optimization_goal">Optimization Goal: </label>
                <select name="optimization_goal" id="optimization_goal">
                    <option value="LINK_CLICKS">Link Clicks</option>
                </select>
                <br><br>
                
                <label for="dsa_beneficiary">DSA Beneficiary: </label>
                <input type="text" name="dsa_beneficiary" id="dsa_beneficiary">
                <br><br>

                <label for="dsa_payor">DSA Payor:</label>
                <input type="text" name="dsa_payor" id="dsa_payor">    
                <br><br>

                <label for="status">Status: </label>
                <select name="status" id="status">
                    <option value="PAUSED" selected>Paused</option>
                    <option value="ACTIVE" disabled>Active</option>
                </select>
                <br><br>
                <button type="submit" name="create_adset">Create Adset</button>
            </form>
        </div>
    </div>
</body>
</html>
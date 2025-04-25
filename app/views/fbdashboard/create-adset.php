<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ad Set</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <h2>Create Adset</h2>
    <div style="display: flex; gap: 20px;">
        <!-- Form container -->
        <div style="flex: 1;">
            <form method="POST" action="createAdset">
                <label for="adset_name">Adset Name:</label>
                <input type="text" name="adset_name" id="adset_name">
                <br><br>

                <h3>Select a campaign to create the adset for</h3>
                <label for="campaign_id">Campaign Id: </label>
                <select name="campaign_id" id="campaign_id">
                    <?php foreach($campaigns['data'] as $campaign){ ?>
                            <option value="<?php echo $campaign['id']?>"><?php echo "Name: " . $campaign['name'] . " Id: " . $campaign['id']?></option>
                    <?php }?>
                </select><br><br>

                <!-- TODO: Add a number of product sets for each catalog to display on the side -->
                 <h4>Select a catalog and product set</h4>

                <label for="catalog_id">Catalog: </label>
                <select name="catalog_id" id="catalog_id">
                <?php if (isset($_SESSION['last_created_catalog'])): ?>
                    <option value="" disabled>== Last Created Catalog ==</option>
                    <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                        <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                    </option>
                    <option value="" disabled>==========================</option>
                <?php endif; 
 
                    foreach($catalogs as $catalog){
                        if(isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']){
                            // Skip to avoid duplicate
                            continue;
                        } else {
                            echo "<option value=\"{$catalog['id']}\">" . htmlspecialchars($catalog['name']) . ", Id: {$catalog['id']}</option>";
                        }
                    }
                ?>
                </select>
                <br><br>

                <label for="product_set">Product Set: </label>
                <select name="product_set" id="product_set">
                    <option value="">-- Select a Product Set --</option>
                </select>
                <br><br>

                <h3>Select a daily budget, 1000 = 10.00 sek</h3>
                <label for="daily_budget">Daily Budget: </label>
                <input type="number" name="daily_budget" id="daily_budget" min="1500">
                <br><br>

                <label for="billing_event">Billing Event: </label>
                <select name="billing_event" id="billing_event">
                    <option value="IMPRESSIONS">Impressions</option>
                </select>
                <br><br>
                <!-- TODO: Add AJAX To make a field appear for budget cap whe nselecting LOWEST_COST_WITH_BID_CAP -->
                <label for="bid_strategy">Bid Strategy: </label>
                <select name="bid_strategy" id="bid_strategy">
                    <option value="LOWEST_COST_WITHOUT_CAP">Lowest cost without cap</option>
                    <option value="LOWEST_COST_WITH_BID_CAP">Lowest cost with cap</option>
                    
                </select>
                <br><br>

                <label for="optimization_goal">Optimization Goal: </label>
                <select name="optimization_goal" id="optimization_goal">
                    <option value="LINK_CLICKS">Link Clicks</option>
                    <option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>
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

<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>
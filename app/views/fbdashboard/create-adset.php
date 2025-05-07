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
    <!-- 
        Form for creating a new Facebook Ad Set. 
        On submit, this POSTs to createAdSet() in fbDashboardController.
    -->
    <div style="flex: 1;">
        <form method="POST" action="createAdset">

            <!-- Basic ad set naming -->
            <label for="adset_name">Adset Name:</label>
            <input type="text" name="adset_name" id="adset_name">
            <br><br>

            <!-- Select from available campaigns (fetched in controller) -->
            <h3>Select a campaign to create the adset for</h3>
            <label for="campaign_id">Campaign Id: </label>
            <select name="campaign_id" id="campaign_id">
                <?php foreach($campaigns['data'] as $campaign): ?>
                    <option value="<?= $campaign['id'] ?>">
                        <?= "Name: " . htmlspecialchars($campaign['name']) . " Id: " . $campaign['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <!-- Catalog and product set selection -->
            <h4>Select a catalog and product set</h4>

            <!-- Catalog dropdown (preselect last created if set) -->
            <label for="catalog_id">Catalog: </label>
            <select name="catalog_id" id="catalog_id">
                <?php if (isset($_SESSION['last_created_catalog'])): ?>
                    <option value="" disabled>== Last Created Catalog ==</option>
                    <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                        <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                    </option>
                    <option value="" disabled>==========================</option>
                <?php endif; ?>

                <?php foreach ($catalogs as $catalog): ?>
                    <?php if (isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']) continue; ?>
                    <option value="<?= $catalog['id'] ?>">
                        <?= htmlspecialchars($catalog['name']) ?>, Id: <?= $catalog['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Product sets are loaded dynamically via JS when a catalog is selected -->
            <label for="product_set">Product Set: </label>
            <select name="product_set" id="product_set">
                <option value="">-- Select a Product Set --</option>
            </select>
            <br><br>

            <!-- Budget configuration -->
            <h3>Select a daily budget, 1000 = 10.00 SEK</h3>
            <label for="daily_budget">Daily Budget: </label>
            <input type="number" name="daily_budget" id="daily_budget" min="1500">
            <br><br>

            <!-- Billing method (fixed to impressions) -->
            <label for="billing_event">Billing Event: </label>
            <select name="billing_event" id="billing_event">
                <option value="IMPRESSIONS">Impressions</option>
            </select>
            <br><br>

            <!-- Bidding strategy -->
            <label for="bid_strategy">Bid Strategy: </label>
            <select name="bid_strategy" id="bid_strategy">
                <option value="LOWEST_COST_WITHOUT_CAP">Lowest cost without cap</option>
                <option value="LOWEST_COST_WITH_BID_CAP">Lowest cost with cap</option>
            </select>
            <br><br>

            <!-- Optimization goal -->
            <label for="optimization_goal">Optimization Goal: </label>
            <select name="optimization_goal" id="optimization_goal">
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>
            </select>
            <br><br>

            <!-- DSA (Dynamic Sponsored Ads) roles -->
            <label for="dsa_beneficiary">DSA Beneficiary: </label>
            <input type="text" name="dsa_beneficiary" id="dsa_beneficiary">
            <br><br>

            <label for="dsa_payor">DSA Payor:</label>
            <input type="text" name="dsa_payor" id="dsa_payor">
            <br><br>

            <!-- Ad set status (active disabled by default for safety) -->
            <label for="status">Status: </label>
            <select name="status" id="status">
                <option value="PAUSED" selected>Paused</option>
                <option value="ACTIVE" disabled>Active</option>
            </select>
            <br><br>

            <!-- Submit button -->
            <button type="submit" name="create_adset">Create Adset</button>
        </form>
    </div>
</div>

<!-- JavaScript to dynamically load product sets based on selected catalog -->
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>

</body>
</html>

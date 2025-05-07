<!-- 
    Step 2 of the Ads Wizard: Create an Ad Set.
    This step lets the user configure delivery rules, targeting, budget, bidding, and optimization settings.
    If the campaign objective is SALES, a catalog and product set must be selected.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Two Adset</title>
    <link rel="stylesheet" href="/Merchant/public/assets/css/boxes.css">
</head>
<body>

<!-- Success flash message after campaign creation -->
<?php if (!empty($_SESSION['flash_campaign'])): ?>
    <div class="alert alert-success">
        <h4><?= htmlspecialchars($_SESSION['flash_campaign']['title']) ?></h4>
        <p><?= $_SESSION['flash_campaign']['body'] ?></p>
    </div>
    <?php unset($_SESSION['flash_campaign']); ?>
<?php endif; ?>

<!-- Error message if adset creation failed -->
<?php if (!empty($_SESSION['flash_adset_error'])): ?>
    <div class="alert alert-danger">
        <h4><?= nl2br(htmlspecialchars($_SESSION['flash_adset_error'])) ?></h4>
    </div>
    <?php unset($_SESSION['flash_adset_error']); ?>
<?php endif; ?>

<a href='/Merchant/public/fbdashboard'>Return to dashboard</a><br><br>

<h2>Step 2: Ad Set</h2>
<h4>The ad set defines your targeting, schedule, and budget for the campaign.</h4>

<div style="display: flex; gap: 20px;">
    <div style="flex: 1;">
        <!-- Ad Set Creation Form -->
        <form method="POST" action="createAdsetWizard" id="adset-form">

            <!-- Ad Set Name -->
            <label for="adset_name">Adset Name: </label><br>
            <input type="text" name="adset_name" id="adset_name"
                value="<?= isset($_POST['adset_name']) ? htmlspecialchars($_POST['adset_name']) : ''; ?>">
            <br><br>

            <!-- Catalog & Product Set Section -->
            <h2>Select a catalog and product set</h2>
            <?php if ($_SESSION['wizard-campaign']['objective'] === 'OUTCOME_TRAFFIC'): ?>
                <!-- Catalogs disabled for traffic objective -->
                <p class="description-box">Catalog selection is disabled for Traffic-based campaigns.</p>
                <div style="display:flex;"><div>
                <label for="catalog_id">Catalog: </label>
                <select name="catalog_id" id="catalog_id" disabled>
                    <option value='' selected>None</option>
                </select>
            <?php else: ?>
                <h4>Optionally choose a product set to promote specific products. Leave as "None" to skip.</h4>
                <div style="display:flex; gap:30px;">
                    <div>
                        <label for="catalog_id">Catalog: </label>
                        <select name="catalog_id" id="catalog_id">
                            <?php if (isset($_SESSION['last_created_catalog'])): ?>
                                <option value="" disabled>== Last Created Catalog ==</option>
                                <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                                    <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                                </option>
                                <option value="" disabled>==========================</option>
                            <?php endif; ?>

                            <option value="">None</option>
                            <?php foreach($catalogs as $catalog): ?>
                                <?php if (isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']) continue; ?>
                                <option value="<?= $catalog['id'] ?>" <?= (isset($_POST['catalog_id']) && $_POST['catalog_id'] == $catalog['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($catalog['name']) ?>, Id: <?= $catalog['id'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            <?php endif; ?>

            <!-- Product Set Dropdown and Button -->
            <div id="product-select-box">
                <h4>Choose an existing product set or create a new one.</h4>
                <div style="display: flex; gap: 20px;">
                    <div>
                        <label for="product_set">Product Set: </label>
                        <select name="product_set" id="product_set" data-selected="<?= htmlspecialchars($_POST['product_set'] ?? '') ?>">
                            <option value="">Please select a catalog first</option>
                        </select>
                        <br><br>
                    </div>
                    <div>
                        <button disabled>Create Product Set</button>
                    </div>
                </div>
            </div>

            <!-- Live Product Preview -->
            <div id="product-list-container">
                <ul id="product_list" style="margin: 0; padding-left: 20px;"></ul>
            </div>
            </div>
<hr>

            <!-- Daily Budget -->
            <h3>Select a daily budget</h3>
            <h4>Minimum is 1500 (15.00 in your ad account's currency)</h4>
            <label for="daily_budget">Daily Budget: </label>
            <input type="number" name="daily_budget" id="daily_budget" min="1500"
                value="<?= htmlspecialchars($_POST['daily_budget'] ?? '') ?>">
            <br><br>

<hr>

            <!-- Billing Event -->
            <h3>Select Billing Event</h3>
            <h4>Defines how Facebook charges your ad: per impression, click, etc.</h4>
            <label for="billing_event">Billing Event: </label>
            <select name="billing_event" id="billing_event" data-selected="<?= htmlspecialchars($_POST['billing_event'] ?? '') ?>"></select>
            <br><br>
            <p id="billing-event-description" class="description-box" style="display: block;"></p>

<hr>

            <!-- Bid Strategy -->
            <h4>Choose how Facebook optimizes your budget for best results</h4>
            <label for="bid_strategy">Bid Strategy: </label>
            <select name="bid_strategy" id="bid_strategy" data-selected="<?= htmlspecialchars($_POST['bid_strategy'] ?? '') ?>">
                <option value="LOWEST_COST_WITHOUT_CAP">Lowest cost without cap</option>
                <option value="LOWEST_COST_WITH_BID_CAP">Lowest cost with cap</option>
                <option value="COST_CAP">Cost cap</option>
            </select>
            <br><br>

            <!-- Cost Cap Warning and Field -->
            <div style="display:flex;gap:20px;">
                <div>
                    <p id="bid-strategy-description" class="description-box" style="display: block;"></p>
                </div>
                <div id="bid_amount_box">
                    <h4>Set cost cap</h4>
                    <h3 id="bid_amount_warning" style="color:red;">Warning: Your cost cap exceeds your daily budget!</h3>
                    <label for="bid_amount">Cost Cap: </label>
                    <input id="bid_amount" name="bid_amount" type="number" min="1"
                        value="<?= htmlspecialchars($_POST['bid_amount'] ?? '') ?>">
                </div>
            </div>

<hr>

            <!-- Optimization Goal -->
            <label for="optimization_goal">Optimization Goal: </label>
            <select name="optimization_goal" id="optimization_goal" data-selected="<?= htmlspecialchars($_POST['optimization_goal'] ?? '') ?>"></select>
            <br><br>
            <p id="optimization-goal-description" class="description-box" style="display: block;"></p>

<hr>

            <!-- DSA Fields -->
            <div id="display_dsa">
                <span><strong>Dynamic Shopping Ads (DSA)</strong> require additional IDs. These fields specify who pays and who owns the products.</span><br><br>
                <label for="dsa_beneficiary">DSA Beneficiary: </label>
                <input type="text" name="dsa_beneficiary" id="dsa_beneficiary"
                    value="<?= htmlspecialchars($_POST['dsa_beneficiary'] ?? '') ?>"><br><br>

                <label for="dsa_payor">DSA Payor:</label>
                <input type="text" name="dsa_payor" id="dsa_payor"
                    value="<?= htmlspecialchars($_POST['dsa_payor'] ?? '') ?>"><br><br>
            </div>

            <!-- Status -->
            <label for="status">Status: </label>
            <select name="status" id="status">
                <option value="PAUSED">Paused</option>
                <option value="ACTIVE">Active</option>
            </select>
            <br><br>

            <!-- Submit Button -->
            <button type="submit" name="create_adset">Create Adset</button>
        </form>
    </div>
</div>

<!-- Pass campaign objective to JS -->
<?php $objective = $_SESSION['wizard-campaign']['objective'] ?? ''; ?>
<script>
    window.adWizardObjective = <?= json_encode($objective) ?>;
</script>

<!-- JS handlers for dynamic UI -->
<script src="/Merchant/public/assets/js/adset-handler.js"></script>
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>

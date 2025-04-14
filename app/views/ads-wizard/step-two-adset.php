<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Two Adset</title>
    <style>
        .description-box {
        margin-top: 10px;
        padding: 10px;
        border: 1px solid #ccc;
        display: none;
        width: 300px;
        }

        #product-list-container {
            max-height: 120px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
        }
  </style>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return to dashboard</a><br><br>
    <h2>Step 2:  Ad Set</h2>
    <h4>The ad set for your campaign is where the rules are set, like who to target and how much you're willing to spend.</h4>
    <h4>Start by adding a name!</h4>
    <div style="display: flex; gap: 20px;">
        <!-- Form container -->
        <div style="flex: 1;">
            <form method="POST" action="createAdset">
                <label for="adset_name">Adset Name: </label><br>
                <input type="text" name="adset_name" id="adset_name">
                <br><br>

                <!-- TODO: Add a number of product sets for each catalog to display on the side -->
                <h2>Select a catalog and product set</h2>               
                <?php if ($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_TRAFFIC'){
                        echo '<p style="display: block;"class="description-box">Since you selected <strong>\'Traffic\'</strong> as the campaign goal, the catalog selector will be disabled.</p>';
                        ?> 
                        <div style="display:flex;"> 
                        <div>   
                        <?php
                        echo '<label for="catalog_id">Catalog: </label>';
                        echo '<select name="catalog_id" id="catalog_id">';
                        echo "<option value='' disabled selected>None</option>";
                    }
                    else{   
                        echo "<h4>Here you can select a product set if you want your campaign to market specific products. <br>Leave as None if you don't plan to market any products in your catalogs.</h4>";
                        ?> 
                        <div style="display:flex; gap:30px; "> 
                        <div>
                        <?php
                        echo '<label for="catalog_id">Catalog: </label>';
                        echo '<select name="catalog_id" id="catalog_id">';
                        if (isset($_SESSION['last_created_catalog'])): ?>
                            <option value="" disabled>== Last Created Catalog ==</option>
                            <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                                <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                            </option>
                            <option value="" disabled>==========================</option>
                        <?php endif; 
                        echo "<option value=''>None</option>";
                            foreach($catalogs as $catalog){
                                if(isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']){
                                    // Skip to avoid duplicate
                                    continue;
                                } else {
                                    echo "<option value=\"{$catalog['id']}\">" . htmlspecialchars($catalog['name']) . ", Id: {$catalog['id']}</option>";
                                }
                        }
                        echo "</select>";
                    }?>
                </select>
                <br><br>


                <div id="product-select-box">
                    <h4>Don't find a product set that you want to use? Create one with the side button.</h4>
                    <div style="display: flex; gap: 20px;">
                        <div>            
                            <label for="product_set">Product Set: </label>
                            <select name="product_set" id="product_set">
                                <option value="">-- Select a Product Set --</option>
                            </select>
                            <br><br>
                        </div>
                        <div>
                            <button>Create Product Set</button>
                        </div>
                    </div>
                </div>
                </div>
                    <div id="product-list-container">
                        <ul id="product_list" style="margin: 0; padding-left: 20px;"></ul>
                    </div>
                </div>
<hr>
                <h3>Select a daily budget</h3>
                <h4>The amount of money you're willing to spend on a daily basis to keep your ads running.
                    This is counted in minimals wich means that 1000 will be 10.00 in the currency that is set when you make your ads account. The minimal amount you can set is 1500.
                </h4>
                <label for="daily_budget">Daily Budget: </label>
                <input type="number" name="daily_budget" id="daily_budget" min="1500">
                <br><br>

<hr>
                <h3>Select Billing Event</h3>
                <h4>A billing event determines when you're charged for your ad. 
                    It defines what user action triggers payment based on your campaign objective.</h4>
                <label for="billing_event">Billing Event: </label>
                <select name="billing_event" id="billing_event">
                </select>
                <br><br>
                
                <p id="billing-event-description" class="description-box" style="display: block;"></p>
<hr>
                <h4>A bid strategy is Facebooks way to automatically disperse your money for your ads.</h4>
                <label for="bid_strategy">Bid Strategy: </label>
                        <select name="bid_strategy" id="bid_strategy">
                            <option value="LOWEST_COST_WITHOUT_CAP">Lowest cost without cap</option>
                            <option value="LOWEST_COST_WITH_BID_CAP">Lowest cost with cap</option>
                            <option value="COST_CAP">Cost cap</option>
                            <option value="BID_CAP">Bid cap</option>
                        </select>
                        <br><br>
                <div style="display:flex;gap:20px;">
                    <div>
                        <p id="bid-strategy-description" class="description-box" style="display: block;"></p>
                    </div>

                    <div id="cost_cap_box">
                        <h4>Set cost cap</h4>
                        <h3 id="cost_cap_warning" style="color:red;">Warning! Your cost cap is exceeding your daily budget, may affect your ad performance!</h3>
                        <label for="cost_cap">Cost Cap: </label>
                        <input id="cost_cap" name="cost_cap" type="number" min="1">
                    </div>
                </div>
                
                
<hr>
                <label for="optimization_goal">Optimization Goal: </label>
                <select name="optimization_goal" id="optimization_goal">
                </select>
                <br><br>
                
                <p id="optimization-goal-description" class="description-box" style="display: block;"></p>
<hr>
                <div id="display_dsa">
                    <span><strong>Dynamic Shopping Ads</strong> requires ID's from either a Facebook Page, Facebook Business account or Instagram Business account. Payor is who is paying for 
                    the ads while beneficiary is who owns the products in a catalog.</span><br><br>
                
                    <label for="dsa_beneficiary">DSA Beneficiary: </label>
                    <input type="text" name="dsa_beneficiary" id="dsa_beneficiary">
                    <br><br>

                    <label for="dsa_payor">DSA Payor:</label>
                    <input type="text" name="dsa_payor" id="dsa_payor">    
                    <br><br>
                </div>
                

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

<?php $objective = $_SESSION['wizard-campaign']['objective'] ?? ''; ?>
<script>

    const currentDailyBudget = document.getElementById('daily_budget');
    let calculatedCostCap = 1500;
    const currentCostCap = document.getElementById('cost_cap');
    const costCapWarning = document.getElementById('cost_cap_warning');


    const productSelectBox = document.getElementById('product-select-box');

    const objective = "<?= htmlspecialchars($objective, ENT_QUOTES) ?>";
    const billingEventList = document.getElementById('billing_event');
    const selectedBillingEvent = document.getElementById('billing_event');
    const billingDescriptionBox = document.getElementById('billing-event-description');
    console.log("Objective type: " + objective); 

    const selectedStrategy = document.getElementById('bid_strategy');
    const selectedStrategyDescription = document.getElementById('bid-strategy-description');
    
    const selectedOptimizationGoal = document.getElementById('optimization_goal');
    const optimizationGoalList = document.getElementById('optimization_goal');
    const optimizationGoalDescription = document.getElementById('optimization-goal-description');

    const costCapBox = document.getElementById('cost_cap_box');
    const displayDSA = document.getElementById('display_dsa');

    switch (objective) {
        case "OUTCOME_TRAFFIC":
            billingEventList.innerHTML = `
                <option value='IMPRESSIONS'>Impressions</option>
                <option value='LINK_CLICKS'>Link Clicks</option>
                <option value='LANDING_PAGE_VIEWS'>Landing Page Views</option>
            `;
            break;
        case "OUTCOME_SALES":
            billingEventList.innerHTML = `
                <option value='IMPRESSIONS'>Impressions</option>
                <option value='OFFSITE_CONVERSIONS'>Offsite Conversions</option>
                <option value='LANDING_PAGE_VIEWS'>Landing Page Views</option>
            `;
    }

    function showBillingDescription(value){
        if (!value){
            console.warning("Incorrect value for billing events!");
            return;
        }

        switch(value){
            case 'IMPRESSIONS':
                billingDescriptionBox.innerHTML = `<strong>Impressions</strong> means that your campaign will be charged for every 1000th time your ad is shown on someones screen.`;
                optimizationGoalList.innerHTML = `
                <option value="IMPRESSIONS">Impressions</option>
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="LANDING_PAGE_VIEWS">Landing Page Views</option>
                <option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
                break;
            case 'LINK_CLICKS':
                billingDescriptionBox.innerHTML = `<strong>Link clicks</strong> means that your campaign will be charged for each time someone clicks your link.`;
                optimizationGoalList.innerHTML = `<option value="LINK_CLICKS">Link Clicks</option>`;
                break;
            case 'LANDING_PAGE_VIEWS':
                billingDescriptionBox.innerHTML = `<strong>Landing page views</strong> means that your campaign will be charged each time someone clicks your link and lands on your page.`;
                optimizationGoalList.innerHTML = `<option value="LANDING_PAGE_VIEWS">Landing Page Views</option>`;
                break;
            case 'OFFSITE_CONVERSIONS':
                billingDescriptionBox.innerHTML = `<strong>Offsite conversions</strong> means that your campaign will be charged each time someone makes a conversion. Like a purchase, a registration or a subscribtion.`;
                optimizationGoalList.innerHTML = `<option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
                break;

        }
    }

    function showOptimizationDescription(value){
        switch(value){
            case 'IMPRESSIONS':
                optimizationGoalDescription.innerHTML = `<strong>Impressions</strong> means that Facebook will try to show your ads to people who are more likely to see your ads.`;
                break;
            case 'LINK_CLICKS':
                optimizationGoalDescription.innerHTML = `<strong>Link clicks</strong> means that Facebook will try to show your ads to people who are more likely to click links.`;
                break;
            case 'LANDING_PAGE_VIEWS':
                optimizationGoalDescription.innerHTML = `<strong>Landing page views</strong> means that Facebook will try to show your ads to people who are more likely to click links and let the page load.`;
                break;
            case 'OFFSITE_CONVERSIONS':
                optimizationGoalDescription.innerHTML = `<strong>Offsite conversions</strong> means that Facebook will try to show your ads to people who are more likely to complete transactions like purchases, registrations or subscriptions.`;
                break;
            
            
        }
    }
    function showProductSets(value){
        
        if (!value){
            productSelectBox.style.display = 'none';
            return;
        }
        else{
            productSelectBox.style.display = 'block';
        }
    }

  

    
    selectedBillingEvent.addEventListener('change', function (){
        showBillingDescription(this.value);
        selectedOptimizationGoal.dispatchEvent(new Event('change'));
        toggleDSAVisibility(this.value);
        
    });

    function toggleDSAVisibility(value){
        if(value == 'OFFSITE_CONVERSIONS'){
            displayDSA.style.display = 'block';
        }
        else{
            displayDSA.style.display = 'none';
        }
    }
    selectedOptimizationGoal.addEventListener('change', function (){
        showOptimizationDescription(this.value);
    });

    selectedStrategy.addEventListener('change', function () {
        console.log("Strategy changed to:", this.value);
        switch(this.value){
            case "LOWEST_COST_WITHOUT_CAP":
                selectedStrategyDescription.innerHTML = `
                <strong>Lowest cost without cap</strong> means that Facebook
                will try to make each billing event cost as little as possible but
                there is no limit for how much it will cost as long as it doesn't exceed your daily budget.`;
                costCapBox.style.display = 'none';
                currentCostCap.value = "";
                break;
            case "LOWEST_COST_WITH_BID_CAP":
                selectedStrategyDescription.innerHTML = `
                <strong>Lowest cost with bid cap</strong> means that Facebok
                will try to make each billing event cost as little as possible but 
                it won't cost more than your bid cap.`;
                costCapBox.style.display = 'block';
                break;
            case "COST_CAP":
                selectedStrategyDescription.innerHTML = `
                <strong>Cost cap</strong> means that Facebook will try to keep the 
                cost per billing event as close to your set cap. However it could go
                over or under your budget by a bit.`;
                costCapBox.style.display = 'block';
                break;
            case "BID_CAP":
                selectedStrategyDescription.innerHTML = `
                <strong>Bid cap</strong> means that Facebook will never exceed
                the cap you set under any circumstances.`;
                costCapBox.style.display = 'block';
                break;
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        const selectedCatalog = document.getElementById('catalog_id');

        selectedStrategy.dispatchEvent(new Event('change'));
        selectedBillingEvent.dispatchEvent(new Event('change'));
        selectedOptimizationGoal.dispatchEvent(new Event('change'));

        if (selectedCatalog) {
        showProductSets(selectedCatalog.value);

        selectedCatalog.addEventListener('change', function () {
        showProductSets(this.value);
    });
    }
    });
    
    function checkCostCap(dailyCap, costCap){
        dailyCap = parseFloat(dailyCap);
        costCap = parseFloat(costCap);
        if(dailyCap < costCap){
            costCapWarning.style.display = 'block';
        }
        else{
            costCapWarning.style.display = 'none';
        }
    }
    currentDailyBudget.addEventListener('change', function (){
        calculatedCostCap = this.value;
        checkCostCap(calculatedCostCap, currentCostCap.value);
    });
    currentCostCap.addEventListener('change', function (){
        checkCostCap(calculatedCostCap, this.value);
    }) 
</script>
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>
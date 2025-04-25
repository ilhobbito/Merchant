console.log("adset-handler.js loaded!");
const currentDailyBudget = document.getElementById('daily_budget');
    let calculatedCostCap = 1500;
    const currentCostCap = document.getElementById('bid_amount');
    const costCapWarning = document.getElementById('bid_amount_warning');


    const productSelectBox = document.getElementById('product-select-box');

    const objective = window.adWizardObjective;
    const billingEventList = document.getElementById('billing_event');
    const selectedBillingEvent = document.getElementById('billing_event');
    const billingDescriptionBox = document.getElementById('billing-event-description');
    console.log("Objective type: " + objective); 

    const selectedStrategy = document.getElementById('bid_strategy');
    const selectedStrategyDescription = document.getElementById('bid-strategy-description');
    
    const selectedOptimizationGoal = document.getElementById('optimization_goal');
    const optimizationGoalList = document.getElementById('optimization_goal');
    const optimizationGoalDescription = document.getElementById('optimization-goal-description');

    const costCapBox = document.getElementById('bid_amount_box');
    const displayDSA = document.getElementById('display_dsa');

    switch (objective) {
        case "OUTCOME_TRAFFIC":
            billingEventList.innerHTML = `
                <option value='IMPRESSIONS'>Impressions</option>
                <option value='LINK_CLICKS' disabled>Link Clicks</option>
            `;
            break;
        case "OUTCOME_SALES":
            billingEventList.innerHTML = `
                <option value='IMPRESSIONS'>Impressions</option>
                <option value='LINK_CLICKS' disabled>Link Clicks</option>`;
    }

    function showBillingDescription(value){
        if (!value){
            console.warn("Incorrect value for billing events!");
            return;
        }

        switch(value){
            case 'IMPRESSIONS':
                billingDescriptionBox.innerHTML = `<strong>Impressions</strong> means that your campaign will be charged for every 1000th time your ad is shown on someones screen.`;
                optimizationGoalList.innerHTML = `
                <option value="IMPRESSIONS">Impressions</option>
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
                break;
            case 'LINK_CLICKS':
                billingDescriptionBox.innerHTML = `<strong>Link clicks</strong> means that your campaign will be charged for each time someone clicks your link.`;
                optimizationGoalList.innerHTML = `
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="LANDING_PAGE_VIEWS">Landing Page Views</option>
                `;
                break;
            case 'LANDING_PAGE_VIEWS':
                billingDescriptionBox.innerHTML = `<strong>Landing page views</strong> means that your campaign will be charged each time someone clicks your link and lands on your page.`;
                optimizationGoalList.innerHTML = `<option value="LANDING_PAGE_VIEWS">Landing Page Views</option>`;
                break;
            case 'OFFSITE_CONVERSIONS':
                billingDescriptionBox.innerHTML = `<strong>Offsite Conversions</strong> means that your campaign will be charged each time someone makes a transaction, like subscribing or filling out a form.`;
                optimizationGoalList.innerHTML = `<option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
                break;

        }
    }

    function showOptimizationDescription(value){
        switch(value){
            case 'IMPRESSIONS':
                optimizationGoalDescription.innerHTML = `<strong>Impressions</strong> means that Facebook will try to show your ads to people who are more likely to watch your ads.`;
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
        if(selectedStrategy.value !== "LOWEST_COST_WITHOUT_CAP"){
            updateMinBudget(this.value, selectedStrategy.value, selectedOptimizationGoal.value)
        } 
    });

    function toggleDSAVisibility(value){
        if(value == 'OFFSITE_CONVERSIONS'){
            displayDSA.style.display = 'block';
        }
        else{
            displayDSA.style.display = 'none';
        }
    }

    function updateMinBudget(billing, strategy, optimization){
        const combo = `${billing}_${strategy}_${optimization}`;
        console.log(combo);
        switch(combo){
            case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_IMPRESSIONS':
                currentCostCap.min = 15;
                break;
            case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_LINK_CLICKS':
                currentCostCap.min = 1;
                break;
            case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_LANDING_PAGE_VIEWS':
                currentCostCap.min = 1;
                break;
            case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_OFFSITE_CONVERSIONS':
                currentCostCap.min = 1;
                break;
        }    
    }
    selectedOptimizationGoal.addEventListener('change', function (){
        showOptimizationDescription(this.value);
        if(selectedStrategy.value !== "LOWEST_COST_WITHOUT_CAP"){
            updateMinBudget(selectedBillingEvent.value, selectedStrategy.value, this.value)
        }
        
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

        if(this.value !== "LOWEST_COST_WITHOUT_CAP"){
            updateMinBudget(selectedBillingEvent.value, this.value, selectedOptimizationGoal.value)
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
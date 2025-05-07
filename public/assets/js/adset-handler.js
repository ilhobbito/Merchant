// adset-handler.js — Controls Ad Set form logic for step two of Ads Wizard
console.log("adset-handler.js loaded!");

// Reference to daily budget input and cost cap fields
const currentDailyBudget = document.getElementById('daily_budget');
let calculatedCostCap = 1500;
const currentCostCap = document.getElementById('bid_amount');
const costCapWarning = document.getElementById('bid_amount_warning');

// Catalog/product set container
const productSelectBox = document.getElementById('product-select-box');

// Retrieve the campaign objective (e.g., OUTCOME_SALES)
const objective = window.adWizardObjective;

// Select field references
const billingEventList = document.getElementById('billing_event');
const selectedBillingEvent = document.getElementById('billing_event');
const billingDescriptionBox = document.getElementById('billing-event-description');

const selectedStrategy = document.getElementById('bid_strategy');
const selectedStrategyDescription = document.getElementById('bid-strategy-description');

const selectedOptimizationGoal = document.getElementById('optimization_goal');
const optimizationGoalList = document.getElementById('optimization_goal');
const optimizationGoalDescription = document.getElementById('optimization-goal-description');

const costCapBox = document.getElementById('bid_amount_box');
const displayDSA = document.getElementById('display_dsa');

console.log("Objective type: " + objective);

// Initialize billing event options based on campaign objective
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
        break;
}

// Update billing event descriptions and load matching optimization goals
function showBillingDescription(value) {
    if (!value) {
        console.warn("Incorrect value for billing events!");
        return;
    }

    switch (value) {
        case 'IMPRESSIONS':
            billingDescriptionBox.innerHTML = `<strong>Impressions:</strong> Charged per 1000 views.`;
            optimizationGoalList.innerHTML = `
                <option value="IMPRESSIONS">Impressions</option>
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
            break;
        case 'LINK_CLICKS':
            billingDescriptionBox.innerHTML = `<strong>Link Clicks:</strong> Charged when someone clicks your ad link.`;
            optimizationGoalList.innerHTML = `
                <option value="LINK_CLICKS">Link Clicks</option>
                <option value="LANDING_PAGE_VIEWS">Landing Page Views</option>`;
            break;
        case 'LANDING_PAGE_VIEWS':
            billingDescriptionBox.innerHTML = `<strong>Landing Page Views:</strong> Charged when a user loads your linked page.`;
            optimizationGoalList.innerHTML = `<option value="LANDING_PAGE_VIEWS">Landing Page Views</option>`;
            break;
        case 'OFFSITE_CONVERSIONS':
            billingDescriptionBox.innerHTML = `<strong>Offsite Conversions:</strong> Charged when a conversion action (e.g. purchase) happens.`;
            optimizationGoalList.innerHTML = `<option value="OFFSITE_CONVERSIONS">Offsite Conversions</option>`;
            break;
    }
}

// Show explanation of optimization goal behavior
function showOptimizationDescription(value) {
    switch (value) {
        case 'IMPRESSIONS':
            optimizationGoalDescription.innerHTML = `<strong>Impressions:</strong> Targets users who are likely to view your ad.`;
            break;
        case 'LINK_CLICKS':
            optimizationGoalDescription.innerHTML = `<strong>Link Clicks:</strong> Targets users likely to click your link.`;
            break;
        case 'LANDING_PAGE_VIEWS':
            optimizationGoalDescription.innerHTML = `<strong>Landing Page Views:</strong> Targets users who click and wait for page to load.`;
            break;
        case 'OFFSITE_CONVERSIONS':
            optimizationGoalDescription.innerHTML = `<strong>Offsite Conversions:</strong> Targets users likely to complete a conversion.`;
            break;
    }
}

// Toggle visibility of product set box
function showProductSets(value) {
    productSelectBox.style.display = value ? 'block' : 'none';
}

// Toggle DSA (Dynamic Shopping Ad) fields if applicable
function toggleDSAVisibility(value) {
    displayDSA.style.display = (value === 'OFFSITE_CONVERSIONS') ? 'block' : 'none';
}

// Dynamically update minimum cost cap based on combo selection
function updateMinBudget(billing, strategy, optimization) {
    const combo = `${billing}_${strategy}_${optimization}`;
    console.log(combo);

    switch (combo) {
        case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_IMPRESSIONS':
        case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_LINK_CLICKS':
        case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_LANDING_PAGE_VIEWS':
        case 'IMPRESSIONS_LOWEST_COST_WITH_BID_CAP_OFFSITE_CONVERSIONS':
            currentCostCap.min = 1;
            break;
    }
}

// Event handlers for dynamic field updates
selectedBillingEvent.addEventListener('change', function () {
    showBillingDescription(this.value);
    selectedOptimizationGoal.dispatchEvent(new Event('change'));
    toggleDSAVisibility(this.value);

    if (selectedStrategy.value !== "LOWEST_COST_WITHOUT_CAP") {
        updateMinBudget(this.value, selectedStrategy.value, selectedOptimizationGoal.value);
    }
});

selectedOptimizationGoal.addEventListener('change', function () {
    showOptimizationDescription(this.value);

    if (selectedStrategy.value !== "LOWEST_COST_WITHOUT_CAP") {
        updateMinBudget(selectedBillingEvent.value, selectedStrategy.value, this.value);
    }
});

// Change bid strategy dynamically and show/hide cost cap input
selectedStrategy.addEventListener('change', function () {
    console.log("Strategy changed to:", this.value);

    switch (this.value) {
        case "LOWEST_COST_WITHOUT_CAP":
            selectedStrategyDescription.innerHTML = `
                <strong>Lowest Cost Without Cap:</strong> Facebook optimizes for cheapest results without spending limits.`;
            costCapBox.style.display = 'none';
            break;
        case "LOWEST_COST_WITH_BID_CAP":
            selectedStrategyDescription.innerHTML = `
                <strong>Lowest Cost With Bid Cap:</strong> Sets a max cost limit per event.`;
            costCapBox.style.display = 'block';
            break;
        case "COST_CAP":
            selectedStrategyDescription.innerHTML = `
                <strong>Cost Cap:</strong> Facebook aims to keep average cost per event close to your cap.`;
            costCapBox.style.display = 'block';
            break;
        case "BID_CAP":
            selectedStrategyDescription.innerHTML = `
                <strong>Bid Cap:</strong> Facebook never bids higher than your cap, under any condition.`;
            costCapBox.style.display = 'block';
            break;
    }

    if (this.value !== "LOWEST_COST_WITHOUT_CAP") {
        updateMinBudget(selectedBillingEvent.value, this.value, selectedOptimizationGoal.value);
    }
});

// On initial page load, dispatch change events to show current selection values
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

// Warn if cost cap exceeds daily budget
function checkCostCap(dailyCap, costCap) {
    dailyCap = parseFloat(dailyCap);
    costCap = parseFloat(costCap);

    costCapWarning.style.display = (dailyCap < costCap) ? 'block' : 'none';
}

currentDailyBudget.addEventListener('change', function () {
    calculatedCostCap = this.value;
    checkCostCap(calculatedCostCap, currentCostCap.value);
});

currentCostCap.addEventListener('change', function () {
    checkCostCap(calculatedCostCap, this.value);
});

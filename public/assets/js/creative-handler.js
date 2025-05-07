// creative-handler.js — Manages dynamic behavior for Ad Creative form step
console.log("creative-handler.js loaded!");

const objective = window.adWizardObjective; // Read campaign objective passed from PHP
const ctaDescriptionBox = document.getElementById("cta-description-box");
const ctaList = document.getElementById("call_to_action");

console.log("Objective:", objective);

// Populate CTA options based on selected campaign objective
switch (objective) {
    case "OUTCOME_TRAFFIC":
        ctaList.innerHTML = `
            <option value="LEARN_MORE">Learn More</option>
            <option value="CONTACT_US">Contact Us</option>`;
        break;
    case "OUTCOME_SALES":
        ctaList.innerHTML = `
            <option value="LEARN_MORE">Learn More</option>
            <option value="SHOP_NOW">Shop Now</option>
            <option value="BUY_NOW">Buy Now</option>`;
        break;
}

// Update CTA description when selection changes
ctaList.addEventListener('change', function () {
    setCTADescription(this.value);
});

// Trigger initial description update on page load
document.addEventListener('DOMContentLoaded', function () {
    ctaList.dispatchEvent(new Event('change'));
});

// Helper: Provide a dynamic explanation for the selected call-to-action
function setCTADescription(value) {
    switch (value) {
        case "LEARN_MORE":
            ctaDescriptionBox.innerHTML = `
                <strong>Learn More</strong> sends the user to a page with more information.
                Ideal for educational content, blog posts, or case studies.`;
            break;
        case "SHOP_NOW":
            ctaDescriptionBox.innerHTML = `
                <strong>Shop Now</strong> directs the user to your store or product page.
                Great for promoting product discovery and encouraging immediate purchases.`;
            break;
        case "BUY_NOW":
            ctaDescriptionBox.innerHTML = `
                <strong>Buy Now</strong> leads directly to a checkout or product detail page.
                Best used for limited-time offers or impulse-driven conversions.`;
            break;
    }
}

console.log("creative-handler.js loaded!")

const objective = window.adWizardObjective;
const ctaDescriptionBox = document.getElementById("cta-description-box");
const ctaList = document.getElementById("call_to_action");
console.log(objective);

switch(objective){
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

ctaList.addEventListener('change', function(){
    setCTADescription(this.value);
})

document.addEventListener('DOMContentLoaded', function () {
    ctaList.dispatchEvent(new Event('change'));
});

function setCTADescription(value){
    switch(value){
        case "LEARN_MORE":
            ctaDescriptionBox.innerHTML = `
            <strong>Learn More</strong> means that the user will be sent to an informational page. This is good for when you want to interest a user first with methods like
            blog posts or case studies.`;
            break;
        case "SHOP_NOW":
            ctaDescriptionBox.innerHTML = `
            <strong>Shop Now</strong> means that the user will be directed straight to your product or page. This is good for when
            you want to encourage the user to make immediate online purchases`;
            break;
        case "BUY_NOW":
            ctaDescriptionBox.innerHTML = `
            <strong>Buy Now</strong> means that the user will be taken to a checkout or product page with the intent to complete a purchase right away. This is good for
            time sensitive offers or when your product has a strong impulse appeal`;
            break;
    }
}
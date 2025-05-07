// catalog-handler.js — Handles catalog and product set selection logic for Ad Set creation
console.log("catalog-handler.js loaded!");

// Fetch product sets when a catalog is selected
function getProductSetsByCatalog() {
    const category = document.getElementById('catalog_id');
    const productSetSelect = document.getElementById('product_set');
    if (!category || !productSetSelect) return;

    category.addEventListener('change', function () {
        const catalogId = this.value;

        // Skip fetch if catalog ID is invalid
        if (!catalogId || catalogId.trim() === '' || catalogId === 'none') {
            console.warn("No valid catalog ID, skipping fetch.");
            productSetSelect.innerHTML = '<option value="">No catalog selected</option>';
            return;
        }

        console.log("📦 Catalog changed:", catalogId);
        productSetSelect.innerHTML = '<option value="">Searching...</option>';

        // Fetch product sets using AJAX
        fetch('/Merchant/public/fbdashboard/getProductSetsByAJAX?catalog_id=' + encodeURIComponent(catalogId))
            .then(res => res.json())
            .then(data => {
                productSetSelect.innerHTML = '';

                if (data.error) {
                    productSetSelect.innerHTML = `<option disabled selected>${data.error}</option>`;
                    return;
                }

                if (!Array.isArray(data) || data.length === 0) {
                    productSetSelect.innerHTML = `<option disabled selected>No product sets available</option>`;
                    return;
                }

                // Populate dropdown with product sets
                data.forEach((ps, index) => {
                    const option = document.createElement('option');
                    option.value = ps.id;
                    option.textContent = `${ps.name} (ID: ${ps.id})`;
                    if (index === 0) option.selected = true;
                    productSetSelect.appendChild(option);
                });

                getProductsByProductSet(); // trigger product load
                productSetSelect.dispatchEvent(new Event('change'));
            })
            .catch(err => {
                productSetSelect.innerHTML = `<option disabled>Error loading sets</option>`;
                console.error("Fetch error:", err);
            });
    });
}

// Fetch products when a product set is selected
function getProductsByProductSet() {
    const productSet = document.getElementById('product_set');
    if (!productSet) return;

    productSet.addEventListener('change', function () {
        console.log("🛒 Product set changed");
        const productSetId = this.value;
        if (!productSetId) return;

        const productList = document.getElementById('product_list');
        if (!productList) {
            console.warn("No #product_list element found — skipping render.");
            return;
        }

        productList.innerHTML = '<li>Loading products...</li>';

        // Fetch products via AJAX
        fetch(`/Merchant/public/fbdashboard/getProductsByAJAX?product_set=${productSetId}`)
            .then(res => res.json())
            .then(data => {
                productList.innerHTML = '';

                if (data.error) {
                    productList.innerHTML = `<li>Error: ${data.error}</li>`;
                    return;
                }

                if (data.length === 0) {
                    productList.innerHTML = `<li>No products found</li>`;
                    return;
                }

                // Render each product with name, price, and sales price
                data.forEach(product => {
                    const li = document.createElement('li');
                    li.setAttribute('data-base', product.price.replace(/[^\d.,]/g, '').replace(/,/g, ''));
                    li.innerHTML = `
                        ${product.name} (ID: ${product.id})<br>
                        Base Price: ${product.price} | Sales Price: <span class="sale-price">${product.sale_price ?? '-'}</span><br><br>`;
                    productList.appendChild(li);
                });

                // Trigger discount recalculation if discount input exists
                document.getElementById('discount_amount')?.dispatchEvent(new Event('input'));
            })
            .catch(err => {
                productList.innerHTML = `<li>Request failed.</li>`;
                console.error(err);
            });
    });
}

// Handle discount type selection (flat vs percentile)
function selectSaleType(button) {
    if (button.value === "flat_discount") {
        console.log("Flat discount selected");
    } else {
        console.log("Percentile discount selected");
    }
    document.getElementById('sale_type_hidden').value = button.value;
}

// Apply discount value to all products in the list
function setupDiscountUpdater() {
    const discountInput = document.getElementById('discount_amount');
    if (!discountInput) return;

    discountInput.addEventListener('input', function () {
        const discount = parseFloat(this.value);
        console.log("Discount updated:", discount);

        if (isNaN(discount) || discount <= 0 || discount > 100) return;

        const products = document.querySelectorAll('#product_list li');

        products.forEach(item => {
            const base = parseFloat(item.getAttribute('data-base'));
            if (isNaN(base)) return;

            const sale = (base * (1 - discount / 100)).toFixed(2);

            const salePriceSpan = item.querySelector('.sale-price');
            if (salePriceSpan) {
                salePriceSpan.textContent = sale;
            }
        });
    });
}

// Run setup on page load
document.addEventListener('DOMContentLoaded', function () {
    getProductSetsByCatalog();
    setupDiscountUpdater();

    // Auto-trigger catalog load if already selected
    setTimeout(() => {
        const category = document.getElementById('catalog_id');
        if (category && category.value && category.value !== 'none') {
            category.dispatchEvent(new Event('change'));
        }
    }, 100);
});

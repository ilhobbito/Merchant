console.log("catalog-handler.js loaded!");
function getProductSetsByCatalog() {
    const category = document.getElementById('catalog_id');
    const productSetSelect = document.getElementById('product_set');
    if (!category || !productSetSelect) return;

    category.addEventListener('change', function () {
        const catalogId = this.value;

        if (!catalogId || catalogId.trim() === '' || catalogId === 'none') {
            console.warn("No valid catalog ID, skipping fetch.");
            productSetSelect.innerHTML = '<option value="">No catalog selected</option>';
            return;
        }

        console.log("Catalog changed: " + catalogId);

        productSetSelect.innerHTML = '<option value="">Searching...</option>';
        console.log("📦 Fetching product sets for catalog ID:", catalogId);
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

                data.forEach((ps, index) => {
                    const option = document.createElement('option');
                    option.value = ps.id;
                    option.textContent = `${ps.name} (ID: ${ps.id})`;
                    if (index === 0) option.selected = true;
                    productSetSelect.appendChild(option);
                });

                getProductsByProductSet(); // optional
                productSetSelect.dispatchEvent(new Event('change'));
            })
            .catch(err => {
                productSetSelect.innerHTML = `<option disabled>Error loading sets</option>`;
                console.error("Fetch error:", err);
            });
    });
}

function getProductsByProductSet() {
    const productSet = document.getElementById('product_set');
    if (!productSet) return;

    productSet.addEventListener('change', function () {
        console.log("Product set changed");
        const productSetId = this.value;
        if (!productSetId) return;

        // Clear current product list if any
        const productList = document.getElementById('product_list');
        if (!productList) {
            console.warn("No #product_list element found — skipping render.");
            return;
        }
        if (productList) productList.innerHTML = '<li>Loading products...</li>';

        fetch(`/Merchant/public/fbdashboard/getProductsByAJAX?product_set=${productSetId}`)
            .then(res => res.json())
            .then(data => {
                if (productList) productList.innerHTML = '';

                if (data.error) {
                    productList.innerHTML = `<li>Error: ${data.error}</li>`;
                    return;
                }

                if (data.length === 0) {
                    productList.innerHTML = `<li>No products found</li>`;
                    return;
                }

                data.forEach(product => {
                    const li = document.createElement('li');
                    li.setAttribute('data-base', product.price.replace(/[^\d.,]/g, '').replace(/,/g, ''));
                    // Wrap the sales price in a span with class "sale-price"
                    li.innerHTML = `
                    ${product.name} (ID: ${product.id})<br>
                    Base Price: ${product.price} | Sales Price: <span class="sale-price">${product.sale_price ?? '-'}</span><br><br>`;
                    productList.appendChild(li);
                });

                document.getElementById('discount_amount')?.dispatchEvent(new Event('input'));
            })
            .catch(err => {
                if (productList) productList.innerHTML = `<li>Request failed.</li>`;
                console.error(err);
            });
    });
}

function selectSaleType(button) {
    if(button.value == "flat_discount"){
        console.log("Flat discount selected");
    }
    else{
        console.log("Percentile discount selected");
    }
    document.getElementById('sale_type_hidden').value = button.value;
}

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

            // Query the span with class "sale-price" inside the li and update it.
            const salePriceSpan = item.querySelector('.sale-price');
            if (salePriceSpan) {
                salePriceSpan.textContent = sale;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    getProductSetsByCatalog();
    setupDiscountUpdater();
    setTimeout(() => {
        const category = document.getElementById('catalog_id');
        if (category && category.value && category.value !== 'none') {
            category.dispatchEvent(new Event('change'));
        }
    }, 100);
});
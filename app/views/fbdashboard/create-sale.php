<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sale</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<div style="display: flex;">
    <!-- Left panel: Sale form -->
    <div style="width: 40vw;">
        <h2>Create Sale</h2>

        <!-- 
            Form POSTs to createSale() in fbDashboardController.
            Applies a sale price (currently only percent discount) to all products in a selected product set.
        -->
        <form method="POST" action="createSale">

            <!-- Catalog selector: pre-selects last created if available -->
            <label for="catalog_id">Catalog: </label>
            <select name="catalog_id" id="catalog_id">
                <?php if (isset($_SESSION['last_created_catalog'])): ?>
                    <option value="" disabled>== Last Created Catalog ==</option>
                    <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                        <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                    </option>
                    <option value="" disabled>==========================</option>
                <?php endif; ?>

                <?php foreach($catalogs as $catalog): ?>
                    <?php
                    // Avoid duplicate listing of the last created catalog
                    if (isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']) continue;
                    ?>
                    <option value="<?= $catalog['id'] ?>">
                        <?= htmlspecialchars($catalog['name']) ?>, Id: <?= $catalog['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Product Set selector (populated dynamically via JS based on selected catalog) -->
            <label for="product_set">Product Set: </label>
            <select name="product_set" id="product_set">
                <option value="">-- Select a Product Set --</option>
            </select>
            <br><br>

            <!-- Sale type selection (currently only % discount) -->
            <label for="sale_type">Sale Type: </label>
            <div style="display: flex; gap: 10px;">
                <!-- Placeholder for potential future types like "flat_discount" -->
                <button type="button" name="sale_type" id="sale_type" value="percent_discount" onclick="selectSaleType(this)">
                    Percentile Discount
                </button>
                <!-- Hidden input stores selected sale type for form submission -->
                <input type="hidden" name="sale_type" id="sale_type_hidden">
            </div>

            <!-- Discount input in percentage -->
            <label for="discount_amount">Discount Amount: </label>
            <span>
                <input name="discount_amount" id="discount_amount" type="number" min="1" max="100"> %
            </span>
            <br><br>

            <button type="submit">Create Sale</button>
        </form>
    </div>

    <!-- Right panel: product preview based on selected product set -->
    <div>
        <h2 style="margin-left: 25px;">Products</h2>
        <ul id="product_list">
            <!-- Populated dynamically via JS when a product set is selected -->
        </ul>
        <br><br>
    </div>
</div>

<!-- Handles product set loading and potentially product preview -->
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ad Creative</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<h2>Create Ad Creative</h2>

<!-- 
    Form for creating a new Facebook Ad Creative. 
    On submit, this POSTs to createAdCreative() in fbDashboardController.
-->
<form method="POST" action="createAdCreative">

    <!-- Creative name input -->
    <label for="creative_name">Creative Name: </label>
    <input type="text" name="creative_name" id="creative_name">
    <br><br>

    <!-- Catalog selector, pre-selects last created catalog if available -->
    <label for="catalog_id">Catalog: </label>
    <select name="catalog_id" id="catalog_id">
        <?php 
        if (isset($_SESSION['last_created_catalog'])) {
            echo "<option value=" . $_SESSION['last_created_catalog']['id'] . "> == Last Created Catalog == " . $_SESSION['last_created_catalog']['name'] . ", Id: " . $_SESSION['last_created_catalog']['id'] . "</option>";
            echo "<option value='' disabled>==========================</option>";
        }

        // Populate all catalogs, excluding the already pre-selected one
        foreach ($catalogs as $catalog) {
            if (isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']) {
                // Skip to avoid duplicate entry
                continue;
            }
            echo "<option value=" . $catalog['id'] . ">" . $catalog['name'] . ", Id: " . $catalog['id'] . "</option>";
        }
        ?>
    </select>

    <h3>Select Product Set to use</h3>

    <!-- Product Set selector, populated via JS based on selected catalog -->
    <label for="product_set">Product Set: </label>
    <select name="product_set" id="product_set">
        <option value="">-- Select a Product Set --</option>
    </select>
    <br><br>

    <!-- Page ID input -->
    <label for="page_id">Page Id: </label>
    <input type="text" name="page_id" id="page_id">
    <br><br>

    <!-- Ad message content -->
    <label for="message">Ad Message: </label>
    <br>
    <textarea cols="40" rows="10" name="message" id="message"></textarea>
    <br><br>

    <!-- Call to action (currently only "Shop Now") -->
    <label for="call_to_action">Call to action type: </label>
    <select name="call_to_action" id="call_to_action">
        <option value="SHOP_NOW">Shop Now</option>
    </select>
    <br><br>

    <!-- Submit button -->
    <button type="submit" name="create_creative">Create Ad Creative</button>
</form>

<!-- 
    This JS file listens to changes in the catalog dropdown
    and dynamically loads product sets into the product_set <select>
-->
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>

</body>
</html>

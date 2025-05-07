<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a>
<h2>Create Product</h2>

<!-- 
    This form POSTs to createProduct() in fbDashboardController.
    It creates a new product inside a selected Facebook Catalog.
-->
<form method="POST" action="createProduct">

    <!-- Catalog selector (user chooses which catalog to insert the product into) -->
    <label for="select_catalog">Select Catalog: </label><br>
    <!-- Dynamically generated dropdown of available catalogs -->
    <select name="select_catalog" id="select_catalog">
        <?php foreach ($catalogs as $catalog): ?>
            <option value="<?= $catalog['id'] ?>">
                <?= "Name: " . htmlspecialchars($catalog['name']) . " Id: " . $catalog['id'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <!-- Product fields mapped to Facebook's product catalog requirements -->

    <!-- Must be unique; serves as the retailer ID -->
    <label for="product_id">Product Id: </label>
    <input type="text" name="product_id" id="product_id">
    <br><br>

    <label for="product_name">Product Name: </label>
    <input type="text" name="product_name" id="product_name">
    <br><br>

    <!-- Optional description field -->
    <label for="description">Product Description: </label><br>
    <textarea name="description" id="description" cols="40" rows="5"></textarea>
    <br><br>

    <!-- Required image URL field for Facebook catalog -->
    <label for="image_url">Product Image URL: </label>
    <input type="text" name="image_url" id="image_url">
    <br><br>

    <label for="product_url">Product Page URL: </label>
    <input type="text" name="product_url" id="product_url">
    <br><br>

    <!-- Price input in numeric form (e.g., 999.99 as 99999 in minor units) -->
    <label for="price">Product Price: </label>
    <input type="number" min="1" name="price" id="price">
    <br><br>

    <!-- Currency selector (defaults to SEK for now) -->
    <label for="currency">Currency: </label>
    <select name="currency" id="currency">
        <option value="SEK">SEK (kr)</option>
    </select>
    <br><br>

    <!-- Stock availability: required for ads to display properly -->
    <label for="availability">Product Availability: </label>
    <select name="availability" id="availability">
        <option value="in stock">In Stock</option>
        <option value="out of stock">Out of Stock</option>
    </select>
    <br><br>

    <!-- Submit button -->
    <button type="submit" name="create-product">Create Product</button>
    <br><br>

</form>

</body>
</html>

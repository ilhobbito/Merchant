<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Products CSV</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<!-- 
    This form POSTs to createProductsCsv() in fbDashboardController.
    It allows the user to upload a CSV file to bulk-import products into a selected catalog.
-->
<form action="createProductsCsv" method="POST" enctype="multipart/form-data">

    <!-- Catalog selector -->
    <!-- Preselects the last created catalog (if present in session) to help the user continue working efficiently -->
    <label for="catalog_id">Select Catalog</label><br>
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
            // Skip if it's already shown as last created
            if (isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']) continue; 
            ?>
            <option value="<?= $catalog['id'] ?>">
                <?= htmlspecialchars($catalog['name']) ?>, Id: <?= $catalog['id'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <!-- File input for CSV file upload -->
    <!-- The CSV should contain properly formatted product fields: retailer_id, name, description, price, currency, etc. -->
    <input type="file" name="product_csv" accept=".csv" required>
    <br><br>

    <!-- Submit button to trigger the CSV upload and bulk product creation -->
    <button type="submit">Upload Products</button>

</form>

<!-- Script used for dynamic catalog/product set handling (if needed) -->
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>

</body>
</html>

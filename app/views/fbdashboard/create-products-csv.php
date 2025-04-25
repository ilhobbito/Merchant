<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Products CSV</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <form action="createProductsCsv" method="POST" enctype="multipart/form-data">
        
        <label for="catalog_id">Select Catalog</label><br>
        <select name="catalog_id" id="catalog_id">
            <?php if (isset($_SESSION['last_created_catalog'])): ?>
                    <option value="" disabled>== Last Created Catalog ==</option>
                    <option value="<?= $_SESSION['last_created_catalog']['id'] ?>" selected>
                        <?= htmlspecialchars($_SESSION['last_created_catalog']['name']) ?>, Id: <?= $_SESSION['last_created_catalog']['id'] ?>
                    </option>
                    <option value="" disabled>==========================</option>
                <?php endif; 
              foreach($catalogs as $catalog){
                if(isset($_SESSION['last_created_catalog']) && $catalog['id'] === $_SESSION['last_created_catalog']['id']){
                    // Skip to avoid duplicate
                    continue;
                } else {
                    echo "<option value=\"{$catalog['id']}\">" . htmlspecialchars($catalog['name']) . ", Id: {$catalog['id']}</option>";
                }
            }?>
            
        </select><br><br>
        <input type="file" name="product_csv" accept=".csv" required>
        <br><br>

        <button type="submit">Upload Products</button>
    </form>

    <script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sale</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <div style="display: flex; ">
        <div style="width: 40vw;">
            <h2>Create Sale</h2>
            <form method="POST" action="createSale">
                <label for="catalog_id">Catalog: </label>
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

                <label for="product_set">Product Set: </label>
                <select name="product_set" id="product_set">
                        <option value="">-- Select a Product Set --</option>
                </select><br><br>

                <label for="sale_type">Sale Type: </label>
                <div style="display: flex; gap:10px;">
                    <!--<button type="button" name="sale_type" id="sale_type" value="flat_discount" onclick="selectSaleType(this)">Flat Discount</button> -->
                    <button type="button" name="sale_type" id="sale_type" value="percent_discount" onclick="selectSaleType(this)">Percentile Discount</button>
                    <input type="hidden" name="sale_type" id="sale_type_hidden">
                </div>

                <label for="discount_amount">Discount Amount: </label>
                <span><input name="discount_amount" id="discount_amount" type="number" min="1" max="100"> %</span>
                <br><br>

                <button type="submit">Create Sale</button>
            </form>
        </div>
        <div>
            <h2 style=" margin-left: 25px;">Products</h2>
            <ul id="product_list">

            </ul>
            <br><br>
        </div>
    </div>
    
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>
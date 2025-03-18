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



    <form method="POST" action="createProduct">
        <label for="select_catalog">Select Catalog: </label><br>
        <select name="select_catalog" id="select_catalog">
            <?php foreach($catalogs as $catalog){ ?>
                    <option value="<?php echo $catalog['id']?>"><?php echo "Name: " . $catalog['name'] . " Id: " . $catalog['id']?></option>
               <?php }?>
        </select><br><br>

        <label for="product_id">Product Id: </label>
        <input type="text" name="product_id" id="product_id">
        <br><br>

        <label for="product_name">Product Name: </label>
        <input type="text" name="product_name" id="product_name">
        <br><br>
        
        <label for="description">Product Description: </label><br>
        <textarea name="description" id="description" cols="40" rows="5"></textarea>
        <br><br>
        
        <label for="image_url">Product Image Url: </label>
        <input type="text" name="image_url" id="image_url">
        <br><br>

        <label for="product_url">Product  Url: </label>
        <input type="text" name="product_url" id="product_url">
        <br><br>

        <label for="price">Product Price: </label>
        <input type="number" min="1" name="price" id="price">
        <br><br>

        <label for="currency">Product Price: </label>
        <select name="currency" id="Currency">
                <option value="SEK">SEK (kr)</option>
        </select>
        <br><br>

        <label for="availability">Product availability: </label>
        <select name="availability" id="availability">
                <option value="in stock">In Stock</option>
                <option value="out of stock">Out of Stock</option>
        </select>
        <br><br>

        <button type="submit" name="create-product">Create Product</button>
        <br><br>

    </form>
</body>
</html>
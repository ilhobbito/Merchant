<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Products</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <h2>All products</h2>
    <!-- For each catalog, fetch and display associated products with basic information -->
    <?php foreach($catalogs as $catalog){
        echo "<h3>Products in " . $catalog['name'] . "</h3>";
        try {
            $productsData = $userManager->getAllProducts($catalog['id'], $fbClient);
            if (isset($productsData['data']) && !empty($productsData['data'])) {
                foreach ($productsData['data'] as $product) {
                    
                    echo "Product ID: " . $product['id'] . "<br>";
                    echo "Name: " . $product['name'] . "<br>";
                    echo "Price: " . $product['price'] . "<br>";
                    echo "Retailer ID: " . $product['retailer_id'] . "<br><br>";
                }
                echo "___________________________________________________________<br><br>";
            } else {
                echo "No products found in this catalog.<br>";
            }
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
    }?>
</body>
</html>
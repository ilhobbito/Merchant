
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List products</title>
</head>
<body>
<?php
if (!empty($products)) {
                foreach ($products as $product) {

                    echo "Title: " . $product->getTitle() . "<br>";
                    echo "Product ID: " . $product->getId() . "<br>";
                }
            } else {
                echo "No products found.";
            }
            echo "<a href='/Merchant/public/dashboard'><br>Return</a>";
    ?>
</body>
</html>
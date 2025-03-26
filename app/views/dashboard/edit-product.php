<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Test Product</title>
</head>
<body>
    <h2>Edit Test Product</h2>
    <form action="/Merchant/public/dashboard/editTestProduct" method="POST">
        <label for="productId">Select Product:</label><br>
        <select name="productId" id="productId" required>
            <?php
            session_start();
            // Fetch the list of test products (hårdkodat för nu, kan ersättas med dynamisk data)
            $products = [
                ['id' => 'online:en:US:dummy_003', 'title' => 'Dummy Test Product']
            ];
            foreach ($products as $product) {
                echo '<option value="' . htmlspecialchars($product['id']) . '">' . htmlspecialchars($product['title']) . '</option>';
            }
            ?>
        </select><br><br>
        <label for="title">Product Title:</label><br>
        <input type="text" name="title" id="title" value="Test dummy"><br><br>
        <label for="price">Product Price:</label><br>
        <input type="text" name="price" id="price" value="19.90" required><br><br>
        <label for="currency">Currency:</label><br>
        <input type="text" name="currency" id="currency" value="USD" required><br><br>
        <button type="submit">Submit</button>
    </form>

    <?php
    // Om du vill visa feedback eller felsöka efter POST, kan du lägga till detta
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h3>Formulärdata skickat:</h3>";
        echo "<pre>";
        var_dump($_POST);
        echo "</pre>";
        // Session-variabeln kan visas för felsökning
        if (isset($_SESSION['last_created_product_id'])) {
            echo "Senast sparade productId: " . htmlspecialchars($_SESSION['last_created_product_id']) . "<br>";
        }
    }
    ?>

    <a href='/Merchant/public/dashboard'><br>Return to Dashboard</a>
</body>
</html>
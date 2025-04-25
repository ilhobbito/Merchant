<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
session_start();

// Kontrollera om användaren är autentiserad
if (!isset($_SESSION['google_access_token'])) {
    die("Error: User is not authenticated.");
}

// Konfigurera Google Client
$client = new Google_Client();
$client->setApplicationName('Google-Merchant-API-Test');
$client->setAccessToken($_SESSION['google_access_token']);

// Kontrollera om access token är giltig, uppdatera om nödvändigt
if ($client->isAccessTokenExpired()) {
    // Hantera token-uppdatering om du använder refresh tokens
    die("Error: Access token expired.");
}

$service = new Google_Service_ShoppingContent($client);
$merchantId = $_ENV['MERCHANT_ID'];

// Hämta alla produkter från Google Merchant Center
try {
    $products = [];
    $parameters = ['maxResults' => 250]; // Max 250 produkter per sida
    do {
        $result = $service->products->listProducts($merchantId, $parameters);
        foreach ($result->getResources() as $product) {
            $products[] = [
                'id' => $product->getId(),
                'offerId' => $product->getOfferId(),
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'link' => $product->getLink(),
                'imageLink' => $product->getImageLink(),
                'availability' => $product->getAvailability(),
                'condition' => $product->getCondition(),
                'price' => [
                    'value' => $product->getPrice()->getValue(),
                    'currency' => $product->getPrice()->getCurrency()
                ]
            ];
        }
        $parameters['pageToken'] = $result->getNextPageToken();
    } while ($parameters['pageToken']);
} catch (\Exception $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

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
        <select name="productId" id="productId" required onchange="updateFormFields(this)">
            <?php
            if (empty($products)) {
                echo '<option value="">No products available</option>';
            } else {
                foreach ($products as $product) {
                    $selected = (isset($_POST['productId']) && $_POST['productId'] === $product['id']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($product['id']) . '" ' . $selected . '>' . htmlspecialchars($product['title']) . '</option>';
                }
            }
            ?>
        </select><br><br>

        <label for="title">Product Title:</label><br>
        <input type="text" name="title" id="title" value="" required><br><br>

        <label for="description">Product Description:</label><br>
        <textarea name="description" id="description"></textarea><br><br>

        <label for="price">Product Price:</label><br>
        <input type="number" step="0.01" name="price" id="price" value="" required><br><br>

        <label for="currency">Currency:</label><br>
        <input type="text" name="currency" id="currency" value="" required><br><br>

        <button type="submit">Submit</button>
    </form>
    <a href="/Merchant/public/dashboard"><br>Return to Dashboard</a>

    <script>
        // Gör produkterna tillgängliga i JavaScript
        const products = <?php echo json_encode($products); ?>;

        function updateFormFields(select) {
            const selectedId = select.value;
            let product = {};
            // Hitta den valda produkten i arrayen
            products.forEach(p => {
                if (p.id === selectedId) {
                    product = p;
                }
            });

            // Fyll i formulärfälten med produktens data
            document.getElementById('title').value = product.title || '';
            document.getElementById('description').value = product.description || '';
            document.getElementById('price').value = product.price ? product.price.value : '';
            document.getElementById('currency').value = product.price ? product.price.currency : '';
        }

        window.onload = function() {
            const select = document.getElementById('productId');
            if (select.value) {
                updateFormFields(select);
            }
        };
    </script>
</body>
</html>
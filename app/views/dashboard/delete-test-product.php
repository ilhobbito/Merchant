<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

// Check if user is authenticated
if (!isset($_SESSION['access_token'])) {
    die("Error: User is not authenticated.");
}

// Configure Google Client
$client = new Google_Client();
$client->setApplicationName('Google-Merchant-API-Test');
$client->setAccessToken($_SESSION['access_token']);

// Check if access token is valid, refresh if necessary
if ($client->isAccessTokenExpired()) {
    // Handle token refresh if you are using refresh tokens
    die("Error: Access token expired.");
}

$service = new Google_Service_ShoppingContent($client);
$merchantId = $_ENV['MERCHANT_ID'];

// Get all products from Google Merchant Center
try {
    $products = [];
    $parameters = ['maxResults' => 250]; // Max 250 products per page
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
    <title>Delete Test Product</title>
</head>
<body>
<form action="/Merchant/public/dashboard/deleteTestProduct" method="POST">
        <label for="productId">Välj produkt att ta bort:</label><br>
        <select name="productId" id="productId" required>
            <?php
            if (empty($products)) {
                echo '<option value="">Inga produkter tillgängliga</option>';
            } else {
                foreach ($products as $product) {
                    $selected = (isset($_POST['productId']) && $_POST['productId'] === $product['id']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($product['id']) . '" ' . $selected . '>' . htmlspecialchars($product['title']) . '</option>';
                }
            }
            ?>
        </select><br><br>

        <button type="submit">Delete</button>
    </form>
    <a href="/Merchant/public/dashboard"><br>Return to Dashboard</a>

    <script>
        // Make products available in JavaScript
        const products = <?php echo json_encode($products); ?>;
// Function to find products to display in the dropdown by id
function displayProductInfo(select) {
    const selectedId = select.value;
    const product = products.find(p => p.id === selectedId) || {};
    const productInfoDiv = document.getElementById('productInfo');
    
    productInfoDiv.innerHTML = product.id ? `
        <strong>Vald produkt:</strong><br>
        Titel: ${product.title || 'Ingen titel'}<br>
        Pris: ${product.price ? product.price.value + ' ' + product.price.currency : 'Inget pris'}
    ` : '';
}
// Event listener for the select element
window.onload = () => {
    const select = document.getElementById('productId');
    if (select.value) displayProductInfo(select);
};
    </script>
</body>
</html>
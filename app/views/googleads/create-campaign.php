<?php
require_once '../app/controllers/GoogleAdsController.php';
use App\Controllers\GoogleAdsController;

try {
    $controller = new GoogleAdsController();
} catch (Exception $e) {
    echo "<p class='error'>Error loading controller: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Google Ads Campaign</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
        form { margin-top: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], input[type="number"], input[type="submit"] { padding: 8px; margin-bottom: 10px; }
        a { display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Create Google Ads Campaign</h1>

    <h2>Create a New Campaign</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="create">
        <label for="campaign_name">Campaign Name:</label>
        <input type="text" id="campaign_name" name="campaign_name" required>
        <label for="budget_usd">Daily Budget (USD):</label>
        <input type="number" id="budget_usd" name="budget_usd" min="1" step="0.01" required>
        <input type="submit" value="Create Campaign">
    </form>

    <a href="/Merchant/public/googleads">Return</a>
</body>
</html>
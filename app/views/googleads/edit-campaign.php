<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$controllerPath = __DIR__ . '/../../../app/controllers/GoogleAdsController.php';
if (!file_exists($controllerPath)) {
    echo "Error: GoogleAdsController.php not found at $controllerPath";
    exit;
}

try {
    require_once $controllerPath;
    $controller = new App\Controllers\GoogleAdsController();
} catch (Exception $e) {
    echo "Error loading controller: " . htmlspecialchars($e->getMessage());
    exit;
}

$campaigns = [];
try {
    $campaignData = $controller->listCampaign();
    if (isset($campaignData['error'])) {
        echo "<p class='error'>Error fetching campaigns: " . htmlspecialchars($campaignData['error']) . "</p>";
    } elseif (is_array($campaignData)) {
        $campaigns = $campaignData;
    }
} catch (Exception $e) {
    echo "<p class='error'>Error fetching campaigns: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Hämta kampanj-ID från URL om det finns
$campaignId = $_GET['campaign_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit or Delete Google Ads Campaign</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .error { color: red; }
        .success { color: green; }
        form { margin-top: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], input[type="submit"] { padding: 8px; margin-bottom: 10px; width: 300px; }
        a { display: inline-block; margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Edit or Delete Google Ads Campaign</h1>

        <p>Enter the Campaign ID to edit or delete below.</p>


    <h2>Edit an Existing Campaign</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="edit">
        <label for="campaign_id">Campaign ID:</label>
        <input type="text" id="campaign_id" name="campaign_id" value="<?php echo htmlspecialchars($campaignId); ?>" required>
        <label for="campaign_name">New Campaign Name:</label>
        <input type="text" id="campaign_name" name="campaign_name" value="" required>
        <input type="submit" value="Edit Campaign">
    </form>

    <h2>Delete a Campaign</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="delete">
        <label for="campaign_id_delete">Campaign ID:</label>
        <input type="text" id="campaign_id_delete" name="campaign_id" value="<?php echo htmlspecialchars($campaignId); ?>" required>
        <input type="submit" value="Delete Campaign">
    </form>

    <a href="/Merchant/public/googleads">Return</a>
</body>
</html>
<?php
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

// Load the listCampaign function to get the campaigns at the top of the page to have them available for editing
$campaigns = [];
try {
    $campaignData = $controller->listCampaign(true);
    if (isset($campaignData['error'])) {
        echo "<p class='error'>Error fetching campaigns: " . htmlspecialchars($campaignData['error']) . "</p>";
    } elseif (is_array($campaignData)) {
        $campaigns = $campaignData;
    }
} catch (Exception $e) {
    echo "<p class='error'>Error fetching campaigns: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$campaignId = $_GET['campaign_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Google Ads Campaign</title>
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
    <h1>Edit Google Ads Campaign</h1>

        <p>Enter the Campaign ID to edit.</p>

    <h2>Edit an Existing Campaign</h2>
    <form method="post" action="editCampaign">
        <input type="hidden" name="action" value="edit">
        <label for="campaign_id">Campaign ID:</label>
        <input type="text" id="campaign_id" name="campaign_id" value="<?php echo htmlspecialchars($campaignId); ?>" required>
        <label for="campaign_name">New Campaign Name:</label>
        <input type="text" id="campaign_name" name="campaign_name" value="" required>
        <input type="submit" value="Edit Campaign">
    </form>
</body>
</html>
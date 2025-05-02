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

$campaignId = $_POST['campaign_id'] ?? $_GET['campaign_id'] ?? '';
if (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($campaignId)) {
    try {
        $result = $controller->deleteCampaign($campaignId);
        if (isset($result['success'])) {
            echo "<p class='success'>Campaign deleted successfully!</p>";
            $campaignData = $controller->listCampaign();
            if (is_array($campaignData)) {
                $campaigns = $campaignData;
            }
        } else {
            echo "<p class='error'>Error deleting campaign: " . htmlspecialchars($result['error']) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error deleting campaign: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Campaign</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
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
    <h2>Delete a Campaign</h2>
    <form method="post" action="deleteCampaign">
        <input type="hidden" name="action" value="delete">
        <label for="campaign_id_delete">Campaign ID:</label>
        <input type="text" id="campaign_id_delete" name="campaign_id" value="<?php echo htmlspecialchars($campaignId); ?>" required>
        <input type="submit" value="Delete Campaign">
    </form>

    <a href="/Merchant/public/googleads">Return</a>
</body>
</html>
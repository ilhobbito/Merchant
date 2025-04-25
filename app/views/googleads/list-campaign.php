<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Google Ads Campaigns</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>List of Campaigns</h1>
    <?php
    if ($http_code == 200 && !empty($response)) {
        $responseData = json_decode($response, true);
        if (!empty($responseData)) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
            foreach ($responseData as $batch) {
                foreach ($batch['results'] as $result) {
                    $campaign = $result['campaign'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($campaign['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($campaign['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($campaign['status']) . "</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
        } else {
            echo "<p>No campaigns found.</p>";
        }
    } else {
        echo "<p>Error: Could not retrieve campaigns. HTTP Code: " . htmlspecialchars($http_code) . "</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    ?>
    <a href="/Merchant/public/googleads">Return</a>
</body>
</html>
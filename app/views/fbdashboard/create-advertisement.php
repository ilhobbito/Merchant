<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Advertisement</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<div style="display: flex; gap: 20px;">
    <div style="flex: 1;">

        <h3>Create Advertisement:</h3>

        <!-- 
            This form POSTs to createAdvertisement() in fbDashboardController.
            Requires an Ad Set (target group) and Ad Creative (visual content) to launch.
        -->
        <form method="POST" action="createAdvertisement">

            <!-- Text input for the internal ad name -->
            <label for="ad_name">Ad Name: </label>
            <input type="text" name="ad_name" id="ad_name">
            <br><br>

            <!-- Select from available Ad Sets (fetched via getAdSets) -->
            <!-- Each option shows its ID, name, and linked product set (if any) -->
            <label for="adset_id">Ad Set Id: </label>
            <select name="adset_id" id="adset_id">
                <?php foreach ($adSets['data'] as $adSet): 
                    $promotedObject = $adSet['promoted_object'] ?? [];
                    $productSetId = $promotedObject['product_set_id'] ?? 'None'; 
                ?>
                    <option value="<?= $adSet['id']; ?>">
                        <?= "Name: {$adSet['name']} | Id: {$adSet['id']} | ProductSet: {$productSetId}" ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Select from existing Ad Creatives (fetched via getAdCreatives) -->
            <label for="adcreative_id">Ad Creative Id: </label>
            <select name="adcreative_id" id="adcreative_id">
                <?php foreach($adCreatives['data'] as $adCreative): ?>   
                    <option value="<?= $adCreative['id'] ?>">
                        <?= "Name: " . htmlspecialchars($adCreative['name']) . " Id: " . $adCreative['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Status select (only PAUSED is allowed at creation time for safety) -->
            <label for="status">Status: </label>
            <select name="status" id="status">
                <option value="PAUSED" selected>Paused</option>
                <option value="ACTIVE" disabled>Active</option>
            </select>
            <br><br>

            <!-- Submit button to create the ad -->
            <button type="submit" name="create_ad">Create Ad</button>
        </form>

    </div>
</div>

</body>
</html>

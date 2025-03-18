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
            <h3>Create Advertisement: </h3>
            <form method="POST" action="createAdvertisement" >
                <label for="ad_name">Ad Name: </label>
                <input type="text" name="ad_name" id="ad_name">
                <br><br>

                <label for="adset_id">Ad Set Id: </label>
                <input type="text" name="adset_id" id="adset_id">
                <br><br>

                <label for="adcreative_id">Ad Creative Id: </label>
                <input type="text" name="adcreative_id" id="adcreative_id">
                <br><br>

                <label for="status">Status: </label>
                <select name="status" id="status">
                    <option value="PAUSED" selected>Paused</option>
                    <option value="ACTIVE" disabled>Active</option>
                </select>
                <br><br>
                <button type="submit" name="create_ad">Create Ad</button>
                </form>

            </form>
        </div>
        <div style="flex: 1;">
            <h3>Your Ad Sets: </h3>
            <?php if (isset($adSets['data'])): ?>
            <?php 
                $x = 1; 
                foreach ($adSets['data'] as $adSet) {
                    echo "#" . $x . ": Id: " . $adSet['id'] 
                        . " Ad Set Name: " . $adSet['name'] . "<br>";
                    $x++;
                }
            ?>
            <?php else: ?>
                <p>No ad sets or error occurred.</p>
            <?php endif; ?>
        </div>
        <div style="flex: 1;">
        <h3>Your Ad Creatives: </h3>
            <?php if (isset($adCreatives['data'])): ?>
            <?php 
                $x = 1; 
                foreach ($adCreatives['data'] as $adCreative) {
                    echo "#" . $x . ": Id: " . $adCreative['id'] 
                        . " Ad Set Name: " . $adCreative['name'] . "<br>";
                    $x++;
                }
            ?>
            <?php else: ?>
                <p>No ad creatives or error occurred.</p>
            <?php endif; ?>
        </div>

    </div>
    
</body>
</html>
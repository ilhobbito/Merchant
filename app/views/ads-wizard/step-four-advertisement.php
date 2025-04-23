<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Four Advertisement</title>

    <link rel="stylesheet" href="/Merchant/public/assets/css/boxes.css">
</head>
<body>

<?php if (!empty($_SESSION['flash_adset'])): ?>
    <div class="alert alert-success">
        <h4><?= htmlspecialchars($_SESSION['flash_adset']['title']) ?></h4>
        <p><?= $_SESSION['flash_adset']['body'] // already escaped above ?></p>
    </div>
    <?php unset($_SESSION['flash_adset']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_ad_error'])): ?>
    <div class="alert alert-danger">
        <h4><?= nl2br(htmlspecialchars($_SESSION['flash_ad_error'])) ?></h4>
    </div>
    <?php unset($_SESSION['flash_ad_error']); ?>
<?php endif; ?>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
            
            <h3>Create Advertisement: </h3>
            <form method="POST" action="createAdvertisementWizard" >
                <label for="ad_name">Ad Name: </label>
                <input type="text" name="ad_name" id="ad_name">
                <br><br>

                <h4>Ad Set:</h4>
                <?php echo "<strong>Name:</strong> {$adSet['name']} | <strong>Id:</strong> {$adSet['id']}";
                
                echo "<h4>Product Set: </h4>";
                if(isset($productSet)){
                    echo "<strong>Name:</strong> {$productSet['name']} | <strong>Id:</strong> {$productSet['id']}";
                }
                else{
                    echo "none";
                } ?>
                <br><br>

                <h4>Ad Creative:</h4>
                <?php echo "<strong>Name:</strong> " . $adCreative['name'] . " <strong>Id:</strong> " . $adCreative['id']?>
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
    </div>
    
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Four Advertisement</title>
</head>
<body>
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
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
                <select name="adset_id" id="adset_id">
                    <?php foreach($adSets['data'] as $adSet){ ?>
                            <option value="<?php echo  $adSet['id']?>"><?php echo "Name: " . $adSet['name'] . " Id: " . $adSet['id']?></option>
                    <?php }?>
                </select><br><br>

                <label for="adcreative_id">Ad Set Id: </label>
                <select name="adcreative_id" id="adcreative_id">
                    <?php foreach($adCreatives['data'] as $adCreative){ ?>
                            <option value="<?php echo  $adCreative['id']?>"><?php echo "Name: " . $adCreative['name'] . " Id: " . $adCreative['id']?></option>
                    <?php }?>
                </select><br><br>

     

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
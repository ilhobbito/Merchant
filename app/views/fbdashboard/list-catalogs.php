<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Catalogs</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <h2>Your Catalogs</h2>
    <?php foreach($catalogs as $catalog){ ?>
        <option value="<?php echo $catalog['id']?>"><?php echo "Name: " . $catalog['name'] . " Id: " . $catalog['id']?></option>
    <?php }?>

</body>
</html>
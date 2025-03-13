<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
</head>
<body>
    <?php if(isset($_SESSION['google_access_token'])){?>
        <h2>Welcome to the Google Merchant Dashboard</h2>
        <a href="dashboard/addTestProduct">Add product</a><br>
        <a href="dashboard/listProducts">List Products</a><br>
        <a href="/Merchant/public/index.php?url=googleads/index">Go to Google Ads</a><br>
    <?php } 
    else if(isset($_SESSION['fb_access_token'])){?>
        <h2>Welcome to the Facebook Business Dashboard</h2>
    <?php } ?>

    
    <a href="dashboard/logout">Logout</a>
</body>
</html>
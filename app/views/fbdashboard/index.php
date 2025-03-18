<?php
if (!isset($_SESSION['fb_access_token'])) {
    header('Location: /');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Dashboard</title>
</head>
<body>
    <h2>Welcome to Facebook Dashboard!</h2>
    <a href="fbdashboard/apiTest">Test API call</a><br><br>
    <h4>Catalogs and products</h4>
    <a href="fbdashboard/routing?value=6">Create Catalog</a><br>
    <a href="fbdashboard/routing?value=7">List All Catalogs</a><br>
    <a href="fbdashboard/routing?value=5">Create Product</a><br>
    <a href="fbdashboard/routing?value=8">List All Products</a><br>
    <h4>________________________________________________________</h4>
    <h4>Campaigns and Advertisements</h4>
    <a href="fbdashboard/routing?value=1">Create Campaign *Step 1</a><br>
    <a href="fbdashboard/routing?value=2">Create Ad Set *Step 2</a><br>
    <a href="fbdashboard/routing?value=3">Create Ad Creative *Step 3</a><br>
    <a href="fbdashboard/routing?value=4">Create Advertisement *Step 4</a><br>
    <a href="fbdashboard/checkAdAccount">Check Ad Account</a><br>
    <a href="fbdashboard/getPixel">Get Pixel</a><br>
</body>
</html>
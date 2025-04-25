<?php
require_once 'D:\xampp\htdocs\Merchant\public\config.php';
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
    <a href="<?php echo BASE_URL; ?>/fbdashboard/apiTest">Test API call</a><br><br>
    <h4>Catalogs and products</h4>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createCatalog">Create Catalog</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/listCatalogs">List All Catalogs</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createProduct">Create Product</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createProductsCsv">Create Product CSV</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/listAllProducts">List All Products</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createProductSet">Create a Product Set</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createSale">Create Sale</a>
    <h4>________________________________________________________</h4>
    <h4>Campaigns and Advertisements</h4>
    <a href="<?php echo BASE_URL; ?>/./adsWizard/createcampaignWizard">Create Campaign Wizard</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createCampaign">Create Campaign *Step 1</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createAdSet">Create Ad Set *Step 2</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createAdCreative">Create Ad Creative *Step 3</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/createAdvertisement">Create Advertisement *Step 4</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/checkAdAccount">Check Ad Account</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/getPixel">Get Pixel</a><br>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/deleteAdCreative">Delete Ad Creative</a><br>
    <h4>________________________________________________________</h4>
    <h4>Ad Performances (Mock)</h4>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/mockData">Get mocked data</a>
    <a href="<?php echo BASE_URL; ?>/fbdashboard/mockData">Check Ads</a>
</body>
</html>
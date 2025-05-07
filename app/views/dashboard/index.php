
<?php require_once __DIR__ . '/../../../public/config.php'; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome to the Google Merchant Dashboard</h2>
    <a href="<?php echo BASE_URL; ?>/dashboard/createTestProduct">Add product</a><br>
    <a href="<?php echo BASE_URL; ?>/dashboard/deleteTestProduct">Delete product</a><br>
    <a href="<?php echo BASE_URL; ?>/dashboard/editTestProduct">Edit product</a><br>
    <a href="<?php echo BASE_URL; ?>/dashboard/listProducts">List Products</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/index">Go to Google Ads</a><br>
    <a href="<?php echo BASE_URL; ?>/dashboard/logout">Logout</a>
</body>
</html>
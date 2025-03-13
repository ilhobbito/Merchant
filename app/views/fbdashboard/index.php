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
    <a href="fbdashboard/apiTest">Test API call</a><br>
    <a href="fbdashboard/createTestCatalog">Create Test Catalog</a><br>
    <a href="fbdashboard/listAllCatalogs">List all catalogs</a><br>
    <a href="fbdashboard/createTestProduct">Create Test Product</a>
</body>
</html>
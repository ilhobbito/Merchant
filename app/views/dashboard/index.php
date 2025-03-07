<?php
if (!isset($_SESSION['access_token'])) {
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome to the Google Merchant Dashboard</h2>
    <a href="dashboard/addTestProduct">Add product</a><br>
    <a href="dashboard/listProducts">List Products</a><br>
    <a href="googleads/index">Go to Google Ads</a><br>
    <a href="dashboard/logout">Logout</a>
</body>
</html>
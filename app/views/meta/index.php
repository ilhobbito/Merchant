<?php
if (!file_exists('meta_token.json')) {
    header('Location: /meta-authenticate'); // Om inget token finns, redirect till autentisering
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Meta Dashboard</title>
</head>
<body>
    <h2>Welcome to Meta Dashboard</h2>
    <a href="/meta/createCatalog">Create Test Catalog</a><br>
    <a href="/meta/addProduct">Add Product to Catalog</a><br>
    <a href="/meta/createCampaign">Create Test Campaign</a><br>
    <a href="/meta/listCatalogs">List Catalogs</a><br>
</body>
</html>
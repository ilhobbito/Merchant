<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Catalog</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a>
    <h2>Create Catalog</h2>

    <form method="POST" action="createCatalog">
        <label for="catalog_name">Catalog Name: </label>
        <input type="text" name="catalog_name" id="catalog_name"><br><br>

        <button type="submit" name="create_catalog">Create Catalog</button>

    </form>
</body>
</html>
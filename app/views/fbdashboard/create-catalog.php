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

<!-- 
    This form POSTs to createCatalog() in fbDashboardController.
    Creates a new Facebook product catalog using the name provided below.
-->
<form method="POST" action="createCatalog">

    <!-- Name of the new catalog to be created -->
    <label for="catalog_name">Catalog Name: </label>
    <input type="text" name="catalog_name" id="catalog_name"><br><br>

    <!-- Submit the form to create the catalog -->
    <button type="submit" name="create_catalog">Create Catalog</button>

</form>

</body>
</html>

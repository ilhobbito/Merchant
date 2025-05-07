<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product Set</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<h2>Create Product Set</h2>

<!-- 
    This form submits to createProductSet() in fbDashboardController.
    It allows creating a filtered (or unfiltered) product set within a selected catalog.
-->
<form action="createProductSet" method="POST">

    <!-- Catalog dropdown populated with all available catalogs -->
    <label for="catalog">Select Catalog</label><br>
    <select name="catalog" id="catalog">
        <?php foreach($catalogs as $catalog): ?>
            <option value="<?= $catalog['id'] ?>">
                Name: <?= htmlspecialchars($catalog['name']) ?> Id: <?= $catalog['id'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <!-- Name of the product set -->
    <label for="name">Product Set Name</label>
    <input type="text" name="name" id="name">
    <br><br>

    <!-- Filter field: defines the field to filter on -->
    <label for="filter">Filter Options</label>
    <select name="filter" id="filter">
        <option value="no_filter">No Filters</option>
        <option value="retailer_id">Retailer ID</option>
        <option value="availability">Availability</option>
        <option value="name">Name</option>
    </select>
    <br><br>

    <!-- Filter type: Graph API uses things like is_any / is_all -->
    <label for="filter_type">Filter Type</label>
    <select name="filter_type" id="filter_type">
        <option value="is_any">Is Any</option>
    </select>
    <br><br>

    <!-- Value to filter on (e.g., specific retailer ID or stock status) -->
    <label for="filter_object">Filter Object</label>
    
    <!-- Helpful usage notes to avoid misconfiguration -->
    <p>Currently: if no filter is selected, all items in the selected catalog will be included.</p>
    <p>If 'Retailer ID' is selected, enter a valid retailer ID (string match).</p>
    <p>If 'Availability' is selected, type <code>in stock</code> (case-sensitive).</p>

    <input type="text" name="filter_object" id="filter_

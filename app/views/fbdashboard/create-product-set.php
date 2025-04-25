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
    <form action="createProductSet" method="POST">
        <label for="catalog">Select Catalog</label><br>
        <select name="catalog" id="catalog">
            <?php foreach($catalogs as $catalog){?>
                <option value="<?php echo $catalog['id']?>">Name: <?php echo $catalog['name'] . " Id: " . $catalog['id'] ?></option>
            <?php } ?>
        </select>

        <br><br>

        <label for="name">Product Set Name</label>
        <input type="text" name="name" id="name"><br><br>

        <label for="filter">Filter Options</label>
        <select name="filter" id="filter">
            <option value="no_filter">No Filters</option>
            <option value="retailer_id">Retailer ID</option>
            <option value="availability">Availability</option>
            <option value="name">Name</option>
        </select><br><br>

        <!-- TODO: Might have to remove this if is_any is the only option available -->
        <label for="filter_type">Filter Type</label>
        <select name="filter_type" id="filter_type">
            <option value="is_any">Is Any</option>
        </select><br><br>

        <label for="filter_object">Filter object</label>
        <p>Currently: if no filter is selected it will make a productset of all the items in the selected catalog.</p>
        <p>If Retailer ID is selected you must get the retailer Id of the object in the text box.</p>
        <p>If Availability is selected you must type in 'in stock' in the text box.</p>
        <input type="text" name="filter_object" id="filter_object"><br><br>

        <button type="submit">Create Product Set</button>

    </form>

</body>
</html>
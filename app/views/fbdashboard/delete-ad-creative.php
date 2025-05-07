<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Ad Creative</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<!-- 
    Form for deleting one or more Facebook Ad Creatives.
    POSTs to deleteAdCreative() in fbDashboardController.
    Permanently deletes ad creatives from the Facebook Ads account and is the only way to do so.
-->
<form method="POST" action="deleteAdCreative">

    <!-- List all ad creatives as checkboxes for multi-selection -->
    <?php foreach ($adCreatives['data'] as $creative): ?>
        <?php
            $id = $creative['id'];
            $name = $creative['name'] ?? 'No Name';
        ?>
        <div>
            <!-- Each checkbox represents a creative that can be selected for deletion -->
            <input type="checkbox" name="selected_creatives[]" value="<?= $id ?>">
            <label><?= htmlspecialchars($name) ?> (ID: <?= $id ?>)</label>
        </div>
    <?php endforeach; ?>

    <br>

    <!-- Submit to delete selected creatives -->
    <button type="submit">Delete Selected</button>
</form>

</body>
</html>

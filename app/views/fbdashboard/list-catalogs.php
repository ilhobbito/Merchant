<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Catalogs</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>
<h2>Your Catalogs</h2>

<!-- 
    Loop through each catalog and display basic info.
    Output is presented as an unordered list for clarity.
-->
<ul>
    <?php foreach ($catalogs as $catalog): ?>
        <li>
            <strong><?= htmlspecialchars($catalog['name']) ?></strong> — ID: <?= $catalog['id'] ?>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>

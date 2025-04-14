<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Ad Creative</title>
</head>
<body>
<a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <form method="POST" action="deleteAdCreative">
        <?php foreach ($adCreatives['data'] as $creative): ?>
            <?php
                $id = $creative['id'];
                $name = $creative['name'] ?? 'No Name';
            ?>
            <div>
                <input type="checkbox" name="selected_creatives[]" value="<?php echo $id; ?>">
                <label><?php echo htmlspecialchars($name); ?> (ID: <?php echo $id; ?>)</label>
            </div>
        <?php endforeach; ?><br>
        <button type="submit">Delete Selected</button>
    </form>
</body>
</html>
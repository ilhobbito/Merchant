<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    if (!empty($response)) {
        echo "GAQL API Response: " . $response;
    } else {
        echo "No campaigns could be found, which is to be expected since the function for adding them has not yet been added.";
    }
    ?>
    <a href='/Merchant/public/googleads'><br>Return</a>
</body>
</html>
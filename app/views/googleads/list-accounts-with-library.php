<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List accounts</title>
</head>
<body>
    <?php
     foreach ($response->getResourceNames() as $resourceName) {
        echo $resourceName . "<br>";
    }
    ?>
    <a href='/Merchant/public/googleads'><br>Return</a>
</body>
</html>
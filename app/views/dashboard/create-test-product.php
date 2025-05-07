<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add test product</title>
</head>
<body>
     <!-- How to create a test product: -->
<form action="/Merchant/public/dashboard/createTestProduct" method="post">
    <label for="offerId">Offer id</label><br>
    <input type="text" id="offerId" name="offerId" required><br>

    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" required><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" required></textarea><br>

    <label for="link">Product Link:</label><br>
    <input type="url" id="link" name="link" value="https://example.com" required><br>

    <label for="imageLink">Image Link:</label><br>
    <input type="url" id="imageLink" name="imageLink"  value="https://example.com" required><br>

    <label for="price">Price: </label><br>
    <input type="number" id="price" name="price" step="0.01" required><br>

    <label for="currency"></label><br>
    <select id="currency" name="currency" required>
        <option value="USD" selected>USD</option><br><br>
        <option value="EUR">EUR</option>
        <option value="GBP">GBP</option>
    <label for="availability">Availability:</label><br>
    <input type="text" id="availability" name="availability" value="In stock" required><br>
    </select><br>
    <button type="submit">Create Test Product</button>
</form>
    <a href='/Merchant/public/dashboard'><br>Return</a></body>
</html>
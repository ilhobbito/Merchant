<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ad Creative</title>
</head>
<body>
    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <h2>Create Ad Creative</h2>
    <form method="POST" action="createAdCreative">
        <label for="creative_name">Creative Name: </label>
        <input type="text" name="creative_name" id="creative_name">
        <br><br>

        <label for="page_id">Page Id: </label>
        <input type="text" name="page_id" id="page_id">
        <br><br>

        <label for="link">Link: </label>
        <input type="text" name="link" id="link">
        <br><br>

        <label for="message">Ad Message: </label>
        <br>
        <textarea cols="40" row="10" name="message" id="message"></textarea>
        <br><br>
        <button type="submit" name="create_creative">Create Ad Creative</button>
    </form>

</body>
</html>
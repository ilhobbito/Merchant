<?php
if (!isset($_SESSION['access_token'])) {
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Google-Ads</title>
</head>
<body>
    <h2>Welcome to Google Ads</h2>
    <a href="googleads/createTestClient">Create Test Client</a><br>
    <a href="googleads/listCampaign">List campaigns</a><br>
    <a href="googleads/setTestBudget">Set Test Budget</a><br>
    <a href="googleads/listAccountsWithLibrary">List accounts</a>
</body>
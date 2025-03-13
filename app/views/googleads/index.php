<?php
if (!isset($_SESSION['google_access_token'])) {
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
    <a href="createTestClient">Create Test Client</a><br>
    <a href="apiTest">Test Google Ads API</a><br>
    <a href="setTestBudget">Set Test Budget</a><br>
    <a href="listAccountsWithLibrary">List Accounts</a>
</body>
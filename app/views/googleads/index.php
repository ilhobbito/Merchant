<?php
require_once 'D:\xampp\htdocs\Merchant\public\config.php';

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
    <a href="<?php echo BASE_URL; ?>/googleads/createTestClient">Create Test Client</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/listCampaign">List campaigns</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/setTestBudget">Set Test Budget</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/listAccountsWithLibrary">List accounts</a>
</body>
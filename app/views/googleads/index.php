<?php
require_once __DIR__ . '/../../../public/config.php';

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
    <a href="<?php echo BASE_URL; ?>/googleads/createTestClient">Create Test Client</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/createCampaign">Create Campaign</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/editCampaign">Edit Campaign</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/deleteCampaign">Delete Campaign</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/listCampaign/false">List Campaigns</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/setTestBudget">Set Test Budget</a><br>
    <a href="<?php echo BASE_URL; ?>/googleads/listAccountsWithLibrary">List accounts</a><br>
    <a href="<?php echo BASE_URL; ?>/dashboard/index">Return to Dashboard</a><br>
</body>
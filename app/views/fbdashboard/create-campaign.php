<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign</title>
</head>
<body>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<h2>Create Campaign</h2>

<!-- 
    This form sends a POST request to createCampaign() in fbDashboardController.
    A campaign is the top-level container for ads and must have a name, objective, and status.
-->
<form method="POST" action="createCampaign">

    <!-- Campaign name input -->
    <label for="campaign_name">Campaign Name: </label>
    <input type="text" name="campaign_name" id="campaign_name">
    <br><br>

    <!-- Objective defines what this campaign is optimized for -->
    <!-- Only Traffic and Sales are currently supported -->
    <label for="objective">Objective: </label>
    <select name="objective" id="objective">
        <option value="OUTCOME_TRAFFIC">Traffic</option>
        <option value="OUTCOME_SALES">Sales</option>

        <!--

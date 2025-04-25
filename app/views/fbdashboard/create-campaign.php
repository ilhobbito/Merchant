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
    <form method="POST" action="createCampaign">
        <label for="campaign_name">Campaign Name: </label>
        <input type="text" name="campaign_name" id="campaign_name">

        <br><br>

    <label for="objective">Objective: </label>
    <select name="objective" id="objective">

        <option value="OUTCOME_TRAFFIC">Traffic</option>
        <option value="OUTCOME_SALES">Sales</option>
        <option value="OUTCOME_LEADS" disabled>Leads -- Disabled --</option>
        <option value="OUTCOME_AWARENESS" disabled>Awareness -- Disabled --</option>
        <option value="OUTCOME_ENGAGEMENT" disabled>Engagement -- Disabled --</option> 
        <option value="OUTCOME_APP_PROMOTION" disabled>App Promotion -- Disabled --</option>
    </select>
    <br><br>

    <label for="status">Status: </label>
    <select name="status" id="status">
        <option value="PAUSED" selected>Paused</option>
        <option value="ACTIVE" disabled>Active</option>
    </select>
    <br><br>
    <button type="submit" name="create_campaign">Create Campaign</button>
    </form>

  
</body>
</html>
<!-- 
    Step 1 of the Ads Wizard: Create a new Facebook Campaign.
    This form collects basic campaign settings (name, objective, status),
    which are sent to createCampaignWizard() for processing and saving.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step One Campaign</title>
    <link rel="stylesheet" href="/Merchant/public/assets/css/boxes.css">
</head>
<body>

    <!-- Display campaign creation errors (if any) -->
    <?php if (!empty($_SESSION['flash_campaign_error'])): ?>
        <div class="alert alert-danger">
            <h4><?= nl2br(htmlspecialchars($_SESSION['flash_campaign_error'])) ?></h4>
        </div>
        <?php unset($_SESSION['flash_campaign_error']); ?>
    <?php endif; ?>

    <a href='/Merchant/public/fbdashboard'>Return to dashboard</a><br><br>

    <h2>Welcome to the Ads Wizard</h2>
    <h3>Here you can make a campaign and ads step by step in order.</h3>
    <h3>Start by creating a campaign</h3>

    <!-- Campaign creation form -->
    <form method="POST" action="createCampaignWizard">
        <label for="campaign_name">Campaign Name: </label>
        <input type="text" name="campaign_name" id="campaign_name"
        value="<?php echo isset($_POST['campaign_name']) ? htmlspecialchars($_POST['campaign_name']) : ''; ?>">
        <br><br>

        <label for="objective">Objective: </label>
        <select name="objective" id="objective">
            <option value="OUTCOME_TRAFFIC" <?php echo (isset($_POST['objective']) && $_POST['objective'] === 'OUTCOME_TRAFFIC') ? 'selected' : ''; ?>>Traffic</option>
            <option value="OUTCOME_SALES" <?php echo (isset($_POST['objective']) && $_POST['objective'] === 'OUTCOME_SALES') ? 'selected' : ''; ?>>Sales</option>
            <option value="OUTCOME_LEADS" disabled>Leads -- Disabled --</option>
            <option value="OUTCOME_AWARENESS" disabled>Awareness -- Disabled --</option>
            <option value="OUTCOME_ENGAGEMENT" disabled>Engagement -- Disabled --</option> 
            <option value="OUTCOME_APP_PROMOTION" disabled>App Promotion -- Disabled --</option>
        </select>
        <br><br>

        <!-- Dynamic description based on selected objective -->
        <div class="description-box" id="objective-description"></div>

        <br><br>
        <label for="status">Status: </label>
        <select name="status" id="status">
            <option value="PAUSED"  <?php echo (isset($_POST['status']) && $_POST['status'] === 'PAUSED') ? 'selected' : ''; ?>>Paused</option>
            <option value="ACTIVE" <?php echo (isset($_POST['status']) && $_POST['status'] === 'ACTIVE') ? 'selected' : ''; ?>>Active</option>
        </select>
        <br><br>

        <!-- Dynamic description based on selected status -->
        <div class="description-box" id="status-description"></div><br><br>

        <button type="submit" name="create_campaign">Create Campaign</button>
    </form>

<!-- 
    JS logic: Show relevant descriptions dynamically when the user selects 
    an objective or status.
-->
<script>
    const descriptionsOutcome = {
        OUTCOME_TRAFFIC: "<strong>Traffic:</strong> Send people to your website or landing page to increase visits.",
        OUTCOME_SALES: "<strong>Sales:</strong> Promote products to drive purchases through your store or catalog.",
    };

    const descriptionStatus = {
        PAUSED: "<strong>Paused:</strong> Campaign won't run until activated.",
        ACTIVE: "<strong>Active:</strong> Campaign will start immediately and incur costs.",
    };

    const objectiveSelect = document.getElementById('objective');
    const descriptionBoxOutcome = document.getElementById('objective-description');
    const statusSelect = document.getElementById('status');
    const descriptionBoxStatus = document.getElementById('status-description');

    function updateDescription(value) {
        if (descriptionsOutcome[value]) {
            descriptionBoxOutcome.innerHTML = descriptionsOutcome[value];
            descriptionBoxOutcome.style.display = 'block';
        } else {
            descriptionBoxOutcome.style.display = 'none';
        }
    }

    function updateStatus(value) {
        if (descriptionStatus[value]) {
            descriptionBoxStatus.innerHTML = descriptionStatus[value];
            descriptionBoxStatus.style.display = 'block';
        } else {
            descriptionBoxStatus.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateDescription(objectiveSelect.value);
        updateStatus(statusSelect.value);
    });

    objectiveSelect.addEventListener('change', function () {
        updateDescription(this.value);
    });

    statusSelect.addEventListener('change', function () {
        updateStatus(this.value);
    });
</script>
</body>
</html>

<!-- 
    Step 3 of the Ads Wizard: Create an Ad Creative.
    This step defines the visual and textual content of the ad.
    If the campaign is for SALES, a dynamic product set will be used.
    If it's for TRAFFIC, a static image must be uploaded manually.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Three Ad Creative</title>
    <link rel="stylesheet" href="/Merchant/public/assets/css/boxes.css">
</head>
<body>

<!-- Success flash message after Ad Set creation -->
<?php if (!empty($_SESSION['flash_adset'])): ?>
    <div class="alert alert-success">
        <h4><?= htmlspecialchars($_SESSION['flash_adset']['title']) ?></h4>
        <p><?= $_SESSION['flash_adset']['body'] ?></p>
    </div>
    <?php unset($_SESSION['flash_adset']); ?>
<?php endif; ?>

<!-- Error flash message if ad creative creation fails -->
<?php if (!empty($_SESSION['flash_creative_error'])): ?>
    <div class="alert alert-danger">
        <h4><?= nl2br(htmlspecialchars($_SESSION['flash_creative_error'])) ?></h4>
    </div>
    <?php unset($_SESSION['flash_creative_error']); ?>
<?php endif; ?>

<a href='/Merchant/public/fbdashboard'>Return</a><br><br>

<h2>Step Three: Ad Creative</h2>
<h4>This step defines what your ad will show — image, headline, message, and call to action.</h4>

<!-- Show message if campaign uses SALES objective (dynamic ads) -->
<?php if ($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_SALES'): ?>
    <h4>
        You've selected "Sales" as your campaign objective.<br>
        Product images, names, and prices will be pulled dynamically from the selected product set.
    </h4>
<?php endif; ?>

<!-- Ad Creative creation form -->
<form method="POST" action="createAdCreativeWizard" enctype="multipart/form-data">

    <!-- Creative name -->
    <label for="creative_name">Creative Name: </label>
    <input type="text" name="creative_name" id="creative_name">
    <br><br>

    <!-- Link to redirect ad traffic -->
    <label for="link">Link: </label>
    <input type="text" name="link" id="link">
    <div class="description-box" style="display: block;">
        <p><strong>Link:</strong> Destination URL users will be sent to when they click the ad.</p>
    </div>
    <br><br>

    <!-- Upload image only for TRAFFIC objective -->
    <?php if ($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_TRAFFIC'): ?>
        <label for="ad_image">Upload Image:</label>
        <input type="file" name="ad_image" accept="image/*">
        <br><br>
        <div class="description-box" style="display: block;">
            <p><strong>Image:</strong> Upload a JPG, PNG, or GIF. This image will be shown in the ad.</p>
        </div>
        <br><br>
    <?php endif; ?>

    <!-- Facebook Page ID to associate with the ad -->
    <label for="page_id">Page Id: </label>
    <input type="text" name="page_id" id="page_id">
    <div class="description-box" style="display: block;">
        <p><strong>Page ID:</strong> This is the Facebook page or business the ad is promoting.</p>
    </div>
    <br><br>

    <!-- Ad message to show to users -->
    <label for="message">Ad Message: </label><br>
    <textarea cols="40" rows="10" name="message" id="message"></textarea>
    <div class="description-box" style="display: block;">
        <p><strong>Message:</strong> A custom message that appears in the ad to attract viewers.</p>
    </div>
    <br><br>

    <!-- Call to action selection -->
    <div class="description-box" style="display: block;">
        <p><strong>Call to Action:</strong> Tells users what to do when they see your ad (e.g., Shop Now).</p>
    </div>
    <label for="call_to_action">Call to Action Type: </label>
    <select name="call_to_action" id="call_to_action">
        <option value="SHOP_NOW">Shop Now</option>
    </select>
    <br><br>
    <p class="description-box" style="display: block;" id="cta-description-box"></p>

    <!-- Submit -->
    <button type="submit" name="create_creative">Create Ad Creative</button>
</form>

<!-- Pass campaign objective to JS -->
<?php $objective = $_SESSION['wizard-campaign']['objective'] ?? ''; ?>
<script>
    window.adWizardObjective = <?= json_encode($objective) ?>;
</script>

<!-- Load JS to handle dynamic UI behaviors -->
<script src="/Merchant/public/assets/js/creative-handler.js"></script>
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>

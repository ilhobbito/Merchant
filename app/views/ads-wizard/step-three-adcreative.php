<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step Three Ad Creative</title>

    <link rel="stylesheet" href="/Merchant/public/assets/css/boxes.css">
</head>
<body>
    
    <?php if (!empty($_SESSION['flash_adset'])): ?>
        <div class="alert alert-success">
            <h4><?= htmlspecialchars($_SESSION['flash_adset']['title']) ?></h4>
            <p><?= $_SESSION['flash_adset']['body'] // already escaped above ?></p>
        </div>
        <?php unset($_SESSION['flash_adset']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_creative_error'])): ?>
    <div class="alert alert-danger">
        <h4><?= nl2br(htmlspecialchars($_SESSION['flash_creative_error'])) ?></h4>
    </div>
    <?php unset($_SESSION['flash_creative_error']); ?>
    <?php endif; ?>

    <a href='/Merchant/public/fbdashboard'>Return</a><br><br>
    <h2>Step Three: Ad Creative</h2>
    <h4>Ad creative is where you put up the content like images and description texts.</h4>
    <?php if($_SESSION['wizard-campaign']['objective'] == 'OUTCOME_SALES'){
        echo "<h4>Since you've picked 'Outcome Sales' as your campaign goal you have selected a productset in the previous step. In this case the images and information will be loaded dynamically from the product set</h4>";
    } ?>

    <form method="POST" action="createAdCreativeWizard">
        <label for="creative_name">Creative Name: </label>
        <input type="text" name="creative_name" id="creative_name">
        <br><br>

        <label for="link">Link: </label>
        <input type="text" name="link" id="link">
        <br>
        <div class="description-box" style="display: block;">
            <p><strong>Link</strong> is the url to your website, shop or page.</p>
        </div><br><br>

        <label for="page_id">Page Id: </label>
        <input type="text" name="page_id" id="page_id">
        <br>

        <div class="description-box" style="display: block;">
            <p><strong>Page Id</strong> is the id of the Facebook page or Facebook business you're intending the ad to promote</p>
        </div><br><br>

        <label for="message">Ad Message: </label>
        <br>
        <textarea cols="40" row="10" name="message" id="message"></textarea>
        <br>
        <div class="description-box" style="display: block;">
            <p><strong>Message</strong> is a message you want the viewers of the ad to see.</p>
        </div><br><br>
        
        <div class="description-box" style="display: block;">
            <p><strong>Call to action</strong> is a way to tell facebook what you want the users to do with your ad.</p>
        </div>

        <label for="call_to_action">Call to action type: </label>
        <select name="call_to_action" id="call_to_action">
            <option value="SHOP_NOW">Shop Now</option>
        </select>
        <br><br>

        <p class="description-box" style="display: block;" id="cta-description-box"></p>

       
        
        <button type="submit" name="create_creative">Create Ad Creative</button>
    </form>

<?php $objective = $_SESSION['wizard-campaign']['objective'] ?? ''; ?>
<script>
  window.adWizardObjective = <?= json_encode($_SESSION['wizard-campaign']['objective'] ?? '') ?>;
</script>
<script src="/Merchant/public/assets/js/creative-handler.js"></script>
<script src="/Merchant/public/assets/js/catalog-handler.js"></script>
</body>
</html>
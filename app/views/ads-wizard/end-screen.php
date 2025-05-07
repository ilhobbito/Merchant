<!-- 
    Final Step: Campaign Summary Screen
    This view shows a comprehensive overview of the campaign, ad set, ad creative, and associated product data.
    It visually confirms that the Facebook Ads campaign has been created successfully.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Summary</title>
</head>
<body>

    <h2>Congratulations, your campaign is complete!</h2>

    <!-- Campaign & Ad Set Summary -->
    <div style="display: flex;">
        <!-- Campaign Info -->
        <div style="border: 2px solid grey; width: 50%; margin: 5px; padding-left: 25px;">
            <h3>Campaign Data:</h3>
            <p><strong>Name:</strong> <?= $campaign['name'] ?></p>
            <p><strong>Id:</strong> <?= $campaign['id'] ?></p>
            <p><strong>Objective:</strong> <?= $campaign['objective'] ?></p>
            <p><strong>Starts:</strong> <?= $adset['start_time'] ?></p>
            <p><strong>Ends:</strong> <?= $adset['end_time'] ?></p>
        </div>

        <!-- Ad Set Info -->
        <div style="border: 2px solid grey; width: 50%; margin: 5px; padding-left: 25px;">
            <h3>Ad Set Data:</h3>
            <p><strong>Name:</strong> <?= $adset['name'] ?></p>
            <p><strong>Id:</strong> <?= $adset['id'] ?></p>
            <p><strong>Billing Event:</strong> <?= $adset['billing_event'] ?></p>
            <p><strong>Bid Strategy:</strong> <?= $adset['bid_strategy'] ?></p>

            <!-- Budget & Bid -->
            <?php
                $budget = number_format($adset['daily_budget'] / 100, 2, '.', ',');
                echo "<p><strong>Daily Budget:</strong> {$budget} SEK</p>";

                if (isset($adset['bid_amount'])) {
                    $bid = number_format($adset['bid_amount'] / 100, 2, '.', ',');
                    echo "<p><strong>Bid Amount:</strong> {$bid} SEK</p>";
                }

                echo "<p><strong>Optimization Goal:</strong> {$adset['optimization_goal']}</p>";

                echo "<p><strong>Target Countries:</strong></p>";
                foreach ($adset['targeting']['geo_locations']['countries'] as $country) {
                    echo "<p>• {$country}</p>";
                }

                echo "<p><strong>Target Platforms:</strong></p>";
                foreach ($adset['targeting']['publisher_platforms'] as $platform) {
                    echo "<p>• {$platform}</p>";
                }
            ?>
        </div>
    </div>

    <!-- Creative & Product Preview -->
    <div style="display: flex; gap: 20px; max-width: 97%;">
        <!-- Ad Creative Summary -->
        <div style="border: 2px solid grey; max-width: 50%; margin: 5px; padding-left: 25px;">
            <div style="display: flex; gap:20vw; justify-content:center;">
                <h3>Creative Data:</h3>
                <h4>Product Images:</h4>
            </div>

            <div style="display: flex;">
                <!-- Creative Info -->
                <div style="padding-right: 20px; width: 25%;">
                    <?php if ($campaign['objective'] === "OUTCOME_SALES"): ?>
                        <p><strong>Name:</strong> <?= $creative['name'] ?></p>
                        <p><strong>Link:</strong> <?= $creative['object_story_spec']['template_data']['link'] ?></p>
                        <p><strong>Page Id:</strong> <?= $creative['object_story_spec']['page_id'] ?></p>
                        <?php if (!empty($creative['object_story_spec']['template_data']['message'])): ?>
                            <p><strong>Message:</strong> <?= $creative['object_story_spec']['template_data']['message'] ?></p>
                        <?php endif; ?>
                        <?php if (!empty($creative['object_story_spec']['template_data']['description'])): ?>
                            <p><strong>Example Description:</strong> <?= htmlspecialchars($previewDescription) ?></p>
                        <?php endif; ?>
                        <p><strong>Call to Action:</strong> <?= $creative['object_story_spec']['template_data']['call_to_action']['type'] ?></p>
                    <?php else: ?>
                        <p><strong>Name:</strong> <?= $creative['name'] ?></p>
                        <p><strong>Link:</strong> <?= $creative['object_story_spec']['link_data']['link'] ?></p>
                        <p><strong>Page Id:</strong> <?= $creative['object_story_spec']['page_id'] ?></p>
                        <p><strong>Call to Action:</strong> <?= $creative['object_story_spec']['link_data']['call_to_action']['type'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Image Preview -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; max-width:50%;">
                    <?php if ($campaign['objective'] === 'OUTCOME_SALES' && !empty($productImages)): ?>
                        <!-- Scrollable image list for dynamic products -->
                        <div style="display: flex; overflow-x: auto; gap: 10px; margin-left: 10vw; padding: 10px; border: 1px solid #ccc; max-width: 100%;">
                            <?php foreach ($productImages as $product): ?>
                                <?php
                                    $imageData = $product['images'][0] ?? null;
                                    if (is_string($imageData)) $imageData = json_decode($imageData, true);
                                    $imageUrl = $imageData['url'] ?? null;
                                ?>
                                <?php if ($imageUrl): ?>
                                    <div style="min-width: 150px; flex: 0 0 auto; text-align: center;">
                                        <img 
                                            src="<?= htmlspecialchars($imageUrl) ?>" 
                                            alt="Product Image" 
                                            style="max-height: 150px; width: auto; object-fit: contain; border: 1px solid #aaa; background: #f8f8f8; padding: 5px;">
                                        <p style="font-size: 0.9em; margin-top: 5px;"><?= htmlspecialchars($product['name']) ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($imageUrl)): ?>
                        <!-- Static uploaded image (traffic-based ads) -->
                        <h4>Uploaded Image:</h4>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Uploaded ad image" style="max-width: 100%; border: 1px solid #ccc; margin-top: 10px;">
                    <?php else: ?>
                        <p>Image preview not available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Set Detail (if applicable) -->
        <?php if (isset($productSet) && isset($catalog)): ?>
            <div style="border: 2px solid grey; width: 50%; margin: 5px; padding-left: 25px; max-height: 55vh; overflow-y: auto;">
                <h3>Product Set:</h3>
                <p><strong>Catalog:</strong> <?= $catalog['name'] ?></p>
                <p><strong>Catalog Id:</strong> <?= $catalog['id'] ?></p>
                <hr>
                <p><strong>Product Set:</strong> <?= $productSet['name'] ?></p>
                <p><strong>Set Id:</strong> <?= $productSet['id'] ?></p>
                <hr>
                <h4>Product Details:</h4>
                <?php foreach ($productSet['products']['data'] as $product): ?>
                    <p><strong>Name:</strong> <?= $product['name'] ?></p>
                    <p><strong>Retailer Id:</strong> <?= $product['retailer_id'] ?></p>
                    <p><strong>Price:</strong> <?= $product['price'] ?></p>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Return Button -->
    <a href="/Merchant/public/fbdashboard" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        margin: 10px;
    ">Return to Dashboard</a>

</body>
</html>

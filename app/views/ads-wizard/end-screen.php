<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Congratulations, your campaign is finished!</h2>
    <div style="display: flex;">
        <div style="border: solid 2px grey; width: 50%; margin: 5px; padding-left:25px;">
            <h3>Campaign Data:</h3>
            <?php 
                echo "<h4>Name:</h4> <p>{$campaign['name']}</p>";
                echo "<h4>Id:</h4> <p>{$campaign['id']}</p>";
                echo "<h4>Objective:</h4> <p>{$campaign['objective']}";
                echo "<h4>Starts:</h4> <p>{$adset['start_time']}";
                echo "<h4>Ends:</h4> <p>{$adset['end_time']}";
            ?>
        </div>
        <div style="border: solid 2px grey; width: 50%; margin: 5px; padding-left:25px;">
            <h3>Ad set Data:</h3>
            <?php
                echo "<h4>Name:</h4> <p>{$adset['name']}</p>";
                echo "<h4>Id:</h4> <p>{$adset['id']}</p>";
                echo "<h4>Billing Event:</h4> <p>{$adset['billing_event']}</p>";
                echo "<h4>Bid Strategy:</h4> <p>{$adset['bid_strategy']}</p>";
                $raw = (int) ($adset['daily_budget']);
                $display = number_format($raw / 100, 2, '.', ',');
                echo "<h4>Daily Budget:</h4> <p>{$display} SEK</p>";
                if(isset($adset['bid_amount'])){
                    $raw = (int) ($adset['bid_amount']);
                    $display = number_format($raw / 100, 2, '.', ',');
                    echo "<h4>Bid amount</h4> <p>{$display} SEK</p>";
                }
                echo "<h4>Optimization Goal:</h4> <p>{$adset['optimization_goal']}";
                echo "<h4>Target Countries:</h4>"; 
                
                foreach($adset['targeting']['geo_locations']['countries'] as $country){
                    echo "<p> {$country} </p>";
                }

                echo "<h4>Target Platforms:</h4>";
                foreach($adset['targeting']['publisher_platforms'] as $platform){
                    echo "<p> {$platform} </p>";
                }
            ?>
        </div>

    </div>
    <div style="display: flex;">
        <div style="border: solid 2px grey; width: 50%; margin: 5px; padding-left:25px;">
            <h3>Creative Data:</h3>
            <?php
                if($campaign['objective'] == "OUTCOME_SALES"){
                    echo "<h4>Name:</h4> <p>{$creative['name']}";
                    echo "<h4>Link:</h4> <p>{$creative['object_story_spec']['template_data']['link']}";
                    echo "<h4>Page Id:</h4> <p>{$creative['object_story_spec']['page_id']}";

                
                    if(!empty($creative['object_story_spec']['template_data']['message'])){
                        echo "<h4>Message:</h4> <p>{$creative['object_story_spec']['template_data']['message']}";
                    }
                    if(!empty($creative['object_story_spec']['template_data']['description'])){
                        echo "<h4>Description:</h4> <p>{$creative['object_story_spec']['template_data']['description']}";
                    }
                    echo "<h4>Call to Action: </h4> <p>{$creative['object_story_spec']['template_data']['call_to_action']['type']}";
                }
                else if($campaign['objective'] == "OUTCOME_TRAFFIC"){
                    echo "<h4>Name:</h4> <p>{$creative['name']}";
                    echo "<h4>Link:</h4> <p>{$creative['object_story_spec']['link_data']['link']}";
                    echo "<h4>Page Id:</h4> <p>{$creative['object_story_spec']['page_id']}";
                
                    echo "<h4>Call to Action: </h4> <p>{$creative['object_story_spec']['link_data']['call_to_action']['type']}";
                }
                
                
            ?>
        </div>
        <?php if(isset($productSet) && isset($catalog)){ ?>
        <div style="border: solid 2px grey; width: 50%; margin: 5px; padding-left:25px;">       
            <h3>Product Set:</h3>
            <?php
                echo "<h4>Catalog:</h4> <p>{$catalog['name']}</p>";
                echo "<h4>Id: </h4> <p>{$catalog['id']}</p><hr>";
                echo "<h4>Product Set:</h4> <p>{$productSet['name']}</p>";
                echo "<h4>Id:</h4> <p>{$productSet['id']}</p><hr>";
                echo "<h4>Product details:</h4>";
                foreach($productSet['products']['data'] as $product){
                    echo "<p><strong>Name: {$product['name']}</strong></p></br>";
                    echo "<p><strong>Retailer Id: {$product['retailer_id']}</strong></p></br>";
                    echo "<p><strong>Price: {$product['price']}</strong></p></br>";
                }
            ?>
        </div>
        <?php } ?>
    </div>
    <a href="/Merchant/public/fbdashboard" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        margin: 5px;
    ">Return to Dashboard</a>
</body>
</html>
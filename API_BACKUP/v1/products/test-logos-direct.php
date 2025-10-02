<?php
// Direct test of logo functionality
header("Content-Type: text/plain");

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die("Database connection failed\n");
}

echo "=== LOGO TEST FOR PRODUCT 91754 ===\n\n";

// Step 1: Check if product has logo IDs
$product_id = 91754;
$sql = "SELECT ID, item_title, item_logo_ids FROM Items WHERE ID = $product_id LIMIT 1";
$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo "Product Found: " . $row['item_title'] . "\n";
    echo "item_logo_ids value: '" . $row['item_logo_ids'] . "'\n\n";
    
    if (empty($row['item_logo_ids'])) {
        echo "❌ No logo IDs assigned to this product\n";
    } else {
        echo "✅ Logo IDs found: " . $row['item_logo_ids'] . "\n\n";
        
        // Step 2: Try to fetch those logos
        $logo_ids = $row['item_logo_ids'];
        echo "Searching for logos with IDs: $logo_ids\n\n";
        
        $logo_sql = "SELECT ID, Name, image_name, CID FROM ClientLogos WHERE ID IN ($logo_ids)";
        $logo_result = $mysqli->query($logo_sql);
        
        if (!$logo_result) {
            echo "❌ Logo query failed: " . $mysqli->error . "\n";
        } else {
            echo "Logos found: " . $logo_result->num_rows . "\n\n";
            
            while ($logo = $logo_result->fetch_assoc()) {
                echo "Logo ID: " . $logo['ID'] . "\n";
                echo "Logo Name: " . $logo['Name'] . "\n";
                echo "Image: " . $logo['image_name'] . "\n";
                echo "CID: " . $logo['CID'] . "\n";
                echo "---\n";
            }
        }
    }
} else {
    echo "❌ Product not found\n";
}

echo "\n=== CHECKING OTHER PRODUCTS ===\n\n";

// Check a few products to see which have logos
$check_sql = "SELECT ID, item_title, item_logo_ids 
              FROM Items 
              WHERE CID = 244 
              AND item_logo_ids != '' 
              AND item_logo_ids IS NOT NULL 
              LIMIT 5";

$check_result = $mysqli->query($check_sql);

if ($check_result && $check_result->num_rows > 0) {
    echo "Products with logos assigned:\n";
    while ($row = $check_result->fetch_assoc()) {
        echo "- Product " . $row['ID'] . ": " . $row['item_title'] . " (Logo IDs: " . $row['item_logo_ids'] . ")\n";
    }
} else {
    echo "No products found with logos assigned\n";
}

$mysqli->close();
?>
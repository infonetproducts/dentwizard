<?php
// Test script to check what's in the database
header('Content-Type: text/plain');

// Database connection
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die("Database connection failed");
}

// First, let's see what columns the Items table has
echo "=== ITEMS TABLE STRUCTURE ===\n";
$result = $mysqli->query("DESCRIBE Items");
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
}

echo "\n=== SAMPLE ITEMS WITH FORMIDS ===\n";
// Check if any items have FormID values
$sql = "SELECT ID, ItemNumber, Name, FormID FROM Items WHERE FormID IS NOT NULL AND FormID != '' LIMIT 10";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: {$row['ID']}, ItemNumber: {$row['ItemNumber']}, FormID: {$row['FormID']}, Name: {$row['Name']}\n";
    }
} else {
    echo "No items found with FormID values\n";
}

echo "\n=== SEARCHING FOR YOUR SPECIFIC FORMIDS ===\n";
// Try to find some of the FormIDs from your Excel
$test_ids = ['M980', '8000', 'K580', '18500', '18000'];
foreach($test_ids as $id) {
    $sql = "SELECT ID, ItemNumber, Name, Description FROM Items 
            WHERE ItemNumber = '$id' 
               OR ID = '$id' 
               OR FormID = '$id'
            LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Found $id: ItemNumber={$row['ItemNumber']}, Name={$row['Name']}\n";
    } else {
        echo "$id: Not found in any column\n";
    }
}

echo "\n=== SAMPLE OF ACTUAL ITEM DATA ===\n";
// Show some actual items from the database
$sql = "SELECT ID, ItemNumber, Name FROM Items WHERE Name != '' ORDER BY ID DESC LIMIT 10";
$result = $mysqli->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "ID: {$row['ID']}, ItemNumber: {$row['ItemNumber']}, Name: {$row['Name']}\n";
    }
}

$mysqli->close();
?>
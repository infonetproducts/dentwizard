<?php
// Ultra-simple Orders table check
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');

echo "Starting Orders table check...\n\n";

// Database connection
$conn = mysqli_connect("localhost", "rwaf", "Py*uhb\$L\$##", "rwaf");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected to database\n\n";

// List all tables
echo "=== All Tables ===\n";
$tables_result = mysqli_query($conn, "SHOW TABLES");
while ($table = mysqli_fetch_array($tables_result)) {
    echo $table[0] . "\n";
}

echo "\n=== Checking Orders Table ===\n";

// Check if Orders exists
$check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM Orders");
if ($check) {
    $row = mysqli_fetch_assoc($check);
    echo "Orders table exists with " . $row['cnt'] . " records\n\n";
} else {
    echo "Error accessing Orders table: " . mysqli_error($conn) . "\n\n";
}

// Get first few column names
echo "=== Orders Columns (first 20) ===\n";
$cols = mysqli_query($conn, "SHOW COLUMNS FROM Orders");
$count = 0;
while ($col = mysqli_fetch_assoc($cols)) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
    $count++;
    if ($count >= 20) break;
}

echo "\n=== OrderItems Check ===\n";
$check2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM OrderItems");
if ($check2) {
    $row2 = mysqli_fetch_assoc($check2);
    echo "OrderItems table has " . $row2['cnt'] . " records\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

// Check key OrderItems columns
echo "\n=== OrderItems Attribute Columns ===\n";
$cols2 = mysqli_query($conn, "SHOW COLUMNS FROM OrderItems LIKE '%size%'");
while ($col = mysqli_fetch_assoc($cols2)) {
    echo "SIZE: " . $col['Field'] . "\n";
}

$cols3 = mysqli_query($conn, "SHOW COLUMNS FROM OrderItems LIKE '%color%'");
while ($col = mysqli_fetch_assoc($cols3)) {
    echo "COLOR: " . $col['Field'] . "\n";
}

$cols4 = mysqli_query($conn, "SHOW COLUMNS FROM OrderItems LIKE '%artwork%'");
while ($col = mysqli_fetch_assoc($cols4)) {
    echo "ARTWORK: " . $col['Field'] . "\n";
}

$cols5 = mysqli_query($conn, "SHOW COLUMNS FROM OrderItems LIKE '%logo%'");
while ($col = mysqli_fetch_assoc($cols5)) {
    echo "LOGO: " . $col['Field'] . "\n";
}

echo "\nDone.";
mysqli_close($conn);
?>
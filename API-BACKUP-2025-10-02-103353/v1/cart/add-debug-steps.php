<?php
// Debug version - logs each step to see where it fails
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: Output something immediately
echo "Step 1 OK\n";

// Step 2: Set headers
header("Content-Type: text/plain");
echo "Step 2 OK\n";

// Step 3: Start session
session_start();
echo "Step 3 OK\n";

// Step 4: Read input
$input = file_get_contents('php://input');
echo "Step 4 OK - Input: " . $input . "\n";

// Step 5: Decode JSON
$data = json_decode($input, true);
echo "Step 5 OK - Decoded: " . print_r($data, true) . "\n";

// Step 6: Database
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
echo "Step 6 OK - DB Connected\n";

// Step 7: Query
$result = $mysqli->query("SELECT ID FROM Items WHERE ID = 91754");
echo "Step 7 OK - Query done\n";

// If we get here, everything works
echo "ALL STEPS COMPLETED SUCCESSFULLY";
?>
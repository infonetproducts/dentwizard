<?php
// Manual session setter for Jamie - visit this page directly to force login
session_start();

// Force set Jamie's session
$_SESSION['user_id'] = 20296;
$_SESSION['userEmail'] = 'jkrugger@infonetproducts.com';
$_SESSION['userName'] = 'Jamie Krugger';
$_SESSION['userType'] = 'standard';

echo "<h1>Session Set for Jamie Krugger</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "User Email: " . $_SESSION['userEmail'] . "\n";
echo "User Name: " . $_SESSION['userName'] . "\n";
echo "</pre>";

echo "<h2>Now test the session:</h2>";
echo "<p>Visit: <a href='test-db-session.php'>test-db-session.php</a> to verify session is set</p>";
echo "<p>Or go to: <a href='http://localhost:3000'>React App</a> and you should be logged in</p>";

// Also output JSON for API testing
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'session_set' => true,
        'session_id' => session_id(),
        'user_data' => $_SESSION
    ]);
}
?>

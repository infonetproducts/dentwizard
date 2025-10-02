<?php
// Bridge between PHP session and React localStorage
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: text/html");

// Check if session is set
if (!isset($_SESSION['user_id'])) {
    die("No session found. Please run force-login-jamie.php first.");
}

// Get user data from session
$userData = [
    'id' => $_SESSION['user_id'],
    'email' => $_SESSION['userEmail'],
    'name' => $_SESSION['userName'],
    'userType' => $_SESSION['userType'] ?? 'standard'
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setting React Authentication</title>
</head>
<body>
    <h1>Setting React App Authentication</h1>
    <p>Session found for: <?php echo $_SESSION['userName']; ?></p>
    
    <script>
        // Set localStorage values that React expects
        const userData = <?php echo json_encode($userData); ?>;
        
        // Store in localStorage (this is what React checks)
        localStorage.setItem('authToken', '<?php echo session_id(); ?>');
        localStorage.setItem('authMethod', 'standard');
        localStorage.setItem('user', JSON.stringify(userData));
        localStorage.setItem('userId', userData.id);
        localStorage.setItem('userEmail', userData.email);
        localStorage.setItem('userName', userData.name);
        
        console.log('Authentication set in localStorage:', userData);
        
        document.write('<h2>✅ React authentication has been set!</h2>');
        document.write('<p>User: ' + userData.name + '</p>');
        document.write('<p>Email: ' + userData.email + '</p>');
        document.write('<p>ID: ' + userData.id + '</p>');
        document.write('<br>');
        document.write('<h3>Now you can:</h3>');
        document.write('<p><a href="http://localhost:3000">Go to React App - You should be logged in!</a></p>');
    </script>
    
    <noscript>
        <p>JavaScript is required to set React authentication.</p>
    </noscript>
</body>
</html>

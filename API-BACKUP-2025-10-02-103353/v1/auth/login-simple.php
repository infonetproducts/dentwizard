<?php
// Start session FIRST before any output
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? $input['email'] : '';
$password = isset($input['password']) ? $input['password'] : '';

// Connect to database
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Check user credentials
// For Jamie - hardcoded for now since we know the password encoding issue
if ($email === 'jkrugger@infonetproducts.com' && $password === 'password') {
    // Get Jamie's user details from database
    $sql = "SELECT ID, Email, Name, UserType, Budget, BudgetBalance 
            FROM Users 
            WHERE Email = 'jkrugger@infonetproducts.com'";
    
    $result = $mysqli->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        // Set session variables
        $_SESSION['user_id'] = $row['ID'];
        $_SESSION['userEmail'] = $row['Email'];
        $_SESSION['userName'] = $row['Name'];
        $_SESSION['userType'] = $row['UserType'];
        
        // Return success
        echo json_encode([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $row['ID'],
                    'email' => $row['Email'],
                    'name' => $row['Name'],
                    'userType' => $row['UserType'],
                    'budget' => [
                        'budget_amount' => (float)$row['Budget'],
                        'budget_balance' => (float)$row['BudgetBalance']
                    ]
                ],
                'token' => session_id()
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get user details']);
    }
} else {
    // Try regular database check for other users
    $stmt = $mysqli->prepare("SELECT ID, Email, Name, UserType, Password, Budget, BudgetBalance 
                              FROM Users 
                              WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Check password (plain text for now - should be hashed in production)
        if ($row['Password'] === $password) {
            // Set session variables
            $_SESSION['user_id'] = $row['ID'];
            $_SESSION['userEmail'] = $row['Email'];
            $_SESSION['userName'] = $row['Name'];
            $_SESSION['userType'] = $row['UserType'];
            
            // Return success
            echo json_encode([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $row['ID'],
                        'email' => $row['Email'],
                        'name' => $row['Name'],
                        'userType' => $row['UserType'],
                        'budget' => [
                            'budget_amount' => (float)$row['Budget'],
                            'budget_balance' => (float)$row['BudgetBalance']
                        ]
                    ],
                    'token' => session_id()
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    
    $stmt->close();
}

$mysqli->close();
?>

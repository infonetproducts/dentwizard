<?php
// api/v1/auth/validate.php
// Validate SSO token and exchange for JWT

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../config/jwt.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['sso_token']) || !isset($input['client_domain'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'SSO token and client domain are required'
    ]);
    exit;
}

$sso_token = $input['sso_token'];
$client_domain = $input['client_domain'];

// TODO: Validate SSO token with your SSO provider (Auth0, Okta, etc.)
// This is a placeholder - implement actual SSO validation
$sso_valid = true; // This should be result of actual SSO validation
$sso_user_email = 'user@example.com'; // Get from SSO provider
$sso_user_name = 'John Doe'; // Get from SSO provider

if (!$sso_valid) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid SSO token'
    ]);
    exit;
}
// Get client information based on domain
$pdo = getPDOConnection();
$stmt = $pdo->prepare("SELECT ID, Name FROM Clients WHERE domain = ? OR subdomain = ?");
$stmt->execute([$client_domain, $client_domain]);
$client = $stmt->fetch();

if (!$client) {
    // Fallback - try to get default client
    $stmt = $pdo->prepare("SELECT ID, Name FROM Clients LIMIT 1");
    $stmt->execute();
    $client = $stmt->fetch();
}

$client_id = $client['ID'] ?? 0;

// Check if user exists or create new user
$stmt = $pdo->prepare("SELECT ID, Name, Email, BillCode, is_view_only, BudgetBalance 
                       FROM Users 
                       WHERE Email = ? AND CID = ?");
$stmt->execute([$sso_user_email, $client_id]);
$user = $stmt->fetch();

if (!$user) {
    // Create new user
    $stmt = $pdo->prepare("INSERT INTO Users (CID, Email, Name, Login, Password, created_date) 
                          VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $client_id,
        $sso_user_email,
        $sso_user_name,
        $sso_user_email,
        password_hash(uniqid(), PASSWORD_DEFAULT) // Random password since using SSO
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Fetch the created user
    $stmt = $pdo->prepare("SELECT ID, Name, Email, BillCode, is_view_only, BudgetBalance 
                           FROM Users WHERE ID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

// Generate JWT token
$jwt_token = JWTManager::generateToken([
    'id' => $user['ID'],
    'email' => $user['Email'],
    'client_id' => $client_id,
    'roles' => $user['is_view_only'] ? ['viewer'] : ['user']
]);

// Return success response
echo json_encode([
    'success' => true,
    'data' => [
        'jwt_token' => $jwt_token,
        'user' => [
            'id' => $user['ID'],
            'email' => $user['Email'],
            'name' => $user['Name'],
            'cid' => $client_id,
            'is_view_only' => (bool)$user['is_view_only'],
            'budget_balance' => (float)$user['BudgetBalance'],
            'bill_code' => $user['BillCode']
        ],
        'client' => [
            'id' => $client['ID'],
            'name' => $client['Name']
        ],
        'expires_in' => 3600
    ]
]);
?>
<?php
/**
 * EXAMPLE: How to add security to your endpoints
 * This shows the before and after of securing an endpoint
 */

// ============================================
// BEFORE: Unsecured endpoint
// ============================================

/*
<?php
// Old version without security
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

// Get products
$pdo = getPDOConnection();
$stmt = $pdo->query("SELECT * FROM Items LIMIT 20");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $products
]);
?>
*/

// ============================================
// AFTER: Secured endpoint
// ============================================

// New version with flexible security
header('Content-Type: application/json');

// Step 1: Include security config (replaces manual CORS headers)
require_once '../../config/security.php';

// Step 2: Validate the request (handles auth based on environment)
$user = validateRequest();
// In development with BYPASS_AUTH=true, this always passes
// In staging, this requires API key
// In production, this requires SSO token

// Step 3: Your normal endpoint logic
require_once '../../config/database.php';

// Optional: Use user info if authenticated
if ($user && isset($user['client_id'])) {
    // User is authenticated, can use their client_id
    $client_id = $user['client_id'];
} else {
    // Not authenticated or in bypass mode
    $client_id = $_GET['client_id'] ?? 1;
}

// Get products
$pdo = getPDOConnection();
$stmt = $pdo->prepare("
    SELECT * FROM Items 
    WHERE client_id = :client_id 
    LIMIT 20
");
$stmt->execute(['client_id' => $client_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return response
echo json_encode([
    'success' => true,
    'data' => $products,
    'security_mode' => $_SERVER['HTTP_X_SECURITY_MODE'] ?? 'unknown',
    'user' => $user ? ['id' => $user['user_id'] ?? null] : null
]);

// ============================================
// NOTES:
// ============================================

/*
What validateRequest() does based on environment:

1. DEVELOPMENT (BYPASS_AUTH=true):
   - Always returns true
   - No authentication needed
   - Perfect for local testing

2. STAGING (with API_KEY set):
   - Checks for X-API-Key header
   - Returns true if valid
   - Returns 401 error if invalid/missing

3. PRODUCTION (with SSO):
   - Validates Bearer token
   - Returns user data if valid
   - Returns 401 error if invalid

The security level is controlled by your .env file:
- Use .env.development for local testing
- Use .env.staging for Render
- Use .env.production for live site

You don't need to change your code when moving between environments!
*/
<?php
/**
 * User Budget API Endpoint
 * Deploy to: /lg/API/v1/user/budget.php
 */

session_start();
include_once("../../include/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['AID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['AID'];
$client_id = $_SESSION['CID'];

$sql = "SELECT BudgetBalance, BudgetAllocated, FirstName, LastName, is_view_only 
        FROM Users WHERE ID = '$user_id' AND CID = '$client_id'";
$result = mysql_query($sql);

$response = ['success' => true, 'has_budget' => false, 'balance' => 0];

if ($user = mysql_fetch_assoc($result)) {
    if ($user['BudgetBalance'] !== null && $user['BudgetBalance'] != '') {
        $response['has_budget'] = true;
        $response['balance'] = floatval($user['BudgetBalance']);
        $response['allocated'] = floatval($user['BudgetAllocated']);
    }
    $response['is_view_only'] = ($user['is_view_only'] == 1);
    $response['user_name'] = $user['FirstName'] . ' ' . $user['LastName'];
}

echo json_encode($response);
?>
<?php
/**
 * Tax Calculation API Endpoint
 * Path: /lg/API/v1/tax/calculate.php
 * 
 * This endpoint calculates tax using TaxJar API based on shipping address
 */

session_start();
include_once("../../include/db.php");
include_once("../../taxjar/common_function_taxjar.php");
include_once("../../shop_common_function.php");

// Set JSON header
header('Content-Type: application/json');

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['to_state']) || !isset($input['to_zip']) || !isset($input['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$to_state = $input['to_state'];
$to_zip = $input['to_zip'];
$to_city = isset($input['to_city']) ? $input['to_city'] : '';
$amount = floatval($input['amount']);

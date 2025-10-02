<?php
// ULTRA SIMPLE TEST - Just returns success
header("Content-Type: application/json");
echo json_encode(array('status' => 'success', 'data' => array()));
?>
<?php

// Your TaxJar API Key
$apiKey = "08d4bb639bc54bd9a8e7c43f80ce4f94";

//https://rwaf.co/lg/taxjar/transaction_api.php
// API Endpoint
$url = "https://api.taxjar.com/v2/transactions/orders";

$data = [
    "transaction_id"   => "ORDER-1001",
    "transaction_date" => date("Y-m-d"),
    "to_country"       => "US",
    "to_state"         => "CA",
    "to_city"          => "Los Angeles",
    "to_zip"           => "90002",
    "amount"           => 175,    // subtotal (160) + shipping (15), NO tax
    "shipping"         => 15,
    "sales_tax"        => 12.75,  // collected tax
    "line_items"       => [
        [
            "id"                => "1",
            "quantity"          => 2,
            "product_identifier"=> "SKU-123",
            "description"       => "Blue T-Shirt",
            "unit_price"        => 50,
            "discount"          => 0,
            "sales_tax"         => 8.50
        ],
        [
            "id"                => "2",
            "quantity"          => 1,
            "product_identifier"=> "SKU-456",
            "description"       => "Red Shoes",
            "unit_price"        => 60,
            "discount"          => 0,
            "sales_tax"         => 4.25
        ]
    ]
];

$jsonData = json_encode($data);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

$response = curl_exec($ch);

if (curl_errno($ch)) 
{
   	 echo "cURL Error: " . curl_error($ch);
	
} else {

    echo "Response: " . $response;
	
}

curl_close($ch);
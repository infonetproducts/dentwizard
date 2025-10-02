<?php
include("common_function_taxjar.php");

if(LIVE_Enable)
{
	$api_key = API_KEY_TAXJAR_LIVE ; // Live enabled
}else{
	$api_key = API_KEY_TAXJAR_Sandbox ; // Sanbox enabled

}

$url = "https://api.taxjar.com/v2/taxes";

$order_data = [
    "from_country" => "US",
    "from_zip" => "10001", // Seller is in New York (or any other state)
    "from_state" => "NY",
    "to_country" => "US",
    "to_zip" => "19102", // Buyer is in Philadelphia, PA
    "to_state" => "PA",
    "amount" => 50.00, // Clothing item price
    "shipping" => 5.00, // Shipping charge
    "nexus_addresses" => [],
    "line_items" => [
        [
            "id" => "1",
            "quantity" => 1,
            "product_tax_code" => "20010", // Clothing tax code
            "unit_price" => 50.00,
            "discount" => 0.00
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code == 200) {
    $data = json_decode($response, true);
   echo "<pre>";
    print_r($data);
} else {
    echo "Error: Unable to calculate tax. HTTP Code: " . $http_code;
    echo "\nResponse: " . $response;
}

curl_close($ch);

?>
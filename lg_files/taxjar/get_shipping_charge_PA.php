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
    "from_zip" => "92093",
    "from_state" => "CA",
    "to_country" => "US",
    "to_zip" => "90002",
    "to_state" => "CA",
    "amount" => 100.00,
    "shipping" => 10.00,
    "nexus_addresses" => [],
    "line_items" => [
        [
            "id" => "1",
            "quantity" => 1,
            "product_tax_code" => "",
            "unit_price" => 100.00,
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
	print_r("<pre>");
    print_r($data);
} else {
    echo "Error: Unable to calculate tax. HTTP Code: " . $http_code;
}

curl_close($ch);

?>



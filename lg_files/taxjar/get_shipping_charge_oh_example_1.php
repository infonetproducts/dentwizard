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
    "from_zip" => "45202", // Seller's ZIP (Cincinnati, OH)
    "from_state" => "OH",
    "to_country" => "US",
    "to_zip" => "43215", // Buyer's ZIP (Columbus, OH)
    "to_state" => "OH",
    "amount" => 150.00, // Product price
    "shipping" => 10.00, // Shipping charge
    "nexus_addresses" => [
        [
            "id" => "Leader-Graphics-OH",
            "country" => "US",
            "zip" => "45202", // Seller's Ohio office
            "state" => "OH"
        ]
    ],
    "line_items" => [
        [
            "id" => "1",
            "quantity" => 1,
            "product_tax_code" => "20010", // General tangible goods
            "unit_price" => 150.00,
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



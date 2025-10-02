<?php
include("common_function_taxjar.php");

if(LIVE_Enable)
{
	$api_key = API_KEY_TAXJAR_LIVE ; // Live enabled
}else{
	$api_key = API_KEY_TAXJAR_Sandbox ; // Sanbox enabled

}



$zip_code = '90002'; // Replace with the ZIP code you want to look up

$url = "https://api.taxjar.com/v2/rates/$zip_code";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code == 200) {
    $data = json_decode($response, true);
    print_r("<pre>");
    print_r($data);
} else {
    echo "Error: Unable to fetch tax rates. HTTP Code: " . $http_code;
}

curl_close($ch);

?>

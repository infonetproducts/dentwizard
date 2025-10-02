<?php
define("LIVE_Enable",1);  // 1 mean live and zero mean sandbox
define("API_KEY_TAXJAR_LIVE","08d4bb639bc54bd9a8e7c43f80ce4f94");
define("API_KEY_TAXJAR_Sandbox","b51622641e05c36cfbb361fff05e863e");


function create_order_transaction_api($sale_tax_response,$order_id,$amount_to_collect)
{
	if(LIVE_Enable)
	{
		$api_key = API_KEY_TAXJAR_LIVE ; // Live enabled
	}else{
		$api_key = API_KEY_TAXJAR_Sandbox ; // Sanbox enabled
	
	}
	
	
	//pr($sale_tax_response);die;
	
	
	 
	
	 $CID = $_SESSION['CID'];
	 
	 	$tarjar_line_items = array();
	$item_index = 0 ;
	$item_index_id = 1 ;
	
	$order_total_price_tax_jar = 0 ;
	
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
	foreach($qty_arr as $set_key=>$qty_sess)
	{
	
		$item_detail = get_item_detail_by_item_id($item_id_sess);
		
		
		$item_detail = get_item_detail_by_item_id($item_id_sess);
		$tax_category = $item_detail['tax_category'];
		$tax_category_arr = array(2,4);  // Apparel 2 , 4 other  need calculate 
		if($item_detail['tax_category']!="" and $item_detail['product_tax_code']!="")
		{
			if(in_array($tax_category , $tax_category_arr) )
			{
				// then we need to calculate 
			}else{
				// then we not need to calculate 
				continue;
			}
		}else{
			// then we not need to calculate 
				continue;
		}
		
		
			$item_id = $item_detail['ID'] ;
		//echo $_SESSION['Order'][$item_id]['size'];
		if(isset($_SESSION['size_item'][$item_id][$set_key]))
		{
			$size = $_SESSION['size_item'][$item_id][$set_key] ;
			
			if($size!="")
			{
				 $is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					//$price = get_item_sale_price_by_size_new($size,$item_id);
					
					$price = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
				}else{
					$price = get_item_price_by_size_new($size,$item_id);
				}
				
				$item_detail['Price']  = $price ;
				
				
				if($item_detail['item_price_type']=="multi_quantity_price")
				{
					$range_price_detail = get_range_item_price_based_on_qty_and_size($item_id,$CID,$qty_sess,$size);
					if(!empty($range_price_detail))
					{
						$item_detail['Price'] = $range_price_detail['price'];
					}
					
					if(empty($range_price_detail))
					{
						// if not set based on size then we need to get from main setting.
						$range_price_detail = get_range_item_price_based_on_qty($item_id,$CID,$qty_sess);
						if(!empty($range_price_detail))
						{
							$item_detail['Price'] = $range_price_detail['price'];
						}
					}
				}
				  
			}	
			
		}else{
		
			$is_apply_sale_price = check_sale_date($item_id);
			 
			if($is_apply_sale_price==1)
			{
				/*$price = get_item_sale_price_by_size_new($size,$item_id);	
				$item_detail['Price'] = $price ; */
				
				$price = calculate_percentage_item_price($item_id);
				$item_detail['Price'] = $price ; 
			}
			
				// check if item in range price
		if($item_detail['item_price_type']=="multi_quantity_price")
		{
			$range_price_detail = get_range_item_price_based_on_qty($item_id,$CID,$qty_sess);
			if(!empty($range_price_detail))
			{
				$item_detail['Price'] = $range_price_detail['price'];
			}
		}
		
		}
		
		$price = $item_detail['Price'];		
		
		
		/* [
            "id"                => "1",
            "quantity"          => 2,
            "product_identifier"=> "SKU-123",
            "description"       => "Blue T-Shirt",
            "unit_price"        => 50,
            "discount"          => 0,
            "sales_tax"         => 8.50
        ],*/
		
		$item_sale_tax = $sale_tax_response['tax']['breakdown']['line_items'][$item_index]['tax_collectable'] ;
		
		$tarjar_line_items[] = array(
				"id" => $item_index_id,
				"quantity" => $qty_sess,
				"product_identifier" => $item_detail['FormID'],  
				"description"       => $item_detail['item_title'],  
				"unit_price" => $price,
				"discount" => 0.00,
				"sales_tax"         => $item_sale_tax
		);
		
		 
		$order_total_price_tax_jar += $qty_sess * $price;
		
	
				
				}
				
		$item_index++;
		$item_index_id++;
		}
		
		
	  
	  
	$url = "https://api.taxjar.com/v2/transactions/orders";
	
//	$total_amount_tax_jar = $_SESSION['total_price'] ;
	
	$data = [
				"transaction_id"   => $order_id,
				"transaction_date" => date("Y-m-d"),
				"to_country"       => "US",
				"to_state"         => $_SESSION['delivery_form']['shipping_state'],
				//"to_city"          => $_SESSION['delivery_form']['shipping_state'],,
				"to_zip"           => $_SESSION['delivery_form']['shipping_zip'],
				"amount"           => $order_total_price_tax_jar,    // subtotal (160) + shipping (15), NO tax
				"shipping"         => 0,
				"sales_tax"        => $amount_to_collect,  // collected tax
				"line_items"       => $tarjar_line_items
			];

	// pr($data); 
	 
	 //die;
	 
	 if(!empty($tarjar_line_items))
	{
	  
	$jsonData = json_encode($data);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Authorization: Bearer $api_key",
		"Content-Type: application/json"
	]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	
	$response = curl_exec($ch);
	
	if (curl_errno($ch)) 
	{
		// echo "cURL Error: " . curl_error($ch);
		
	} else {
	
		$response_arr = json_decode($response,true);
		
		/*print_r("<pre>");
		print_r($response_arr);
		
	die;*/
	
		//echo "Response: " . $response;
		
	}
	
	curl_close($ch);
	
	}
	
}


function calculate_sale_tax()
{
	if(LIVE_Enable)
	{
		$api_key = API_KEY_TAXJAR_LIVE ; // Live enabled
	}else{
		$api_key = API_KEY_TAXJAR_Sandbox ; // Sanbox enabled
	
	}
	
	//print_r("<pre>");
//print_r($_SESSION);


	
	 $CID = $_SESSION['CID'];

/*	
	Array
(
    [delivery_method] => 7_to_10_days
    [shipping_address_1] => 5141 Merilee Drive
    [shipping_city] => Ohio
    [shipping_zip] => 43001
    [shipping_state] => OH
    [delivery_form] => Continue
)*/
	
	$url = "https://api.taxjar.com/v2/taxes";
	
	$tarjar_line_items = array();
	
	$order_total_price_tax_jar =  0 ;
	
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
	foreach($qty_arr as $set_key=>$qty_sess)
	{
	
		$item_detail = get_item_detail_by_item_id($item_id_sess);
		$tax_category = $item_detail['tax_category'];
		$tax_category_arr = array(2,4);  // Apparel 2 , 4 other  need calculate 
		if($item_detail['tax_category']!="" and $item_detail['product_tax_code']!="")
		{
			if(in_array($tax_category , $tax_category_arr) )
			{
				// then we need to calculate 
			}else{
				// then we not need to calculate 
				continue;
			}
		}else{
			// then we not need to calculate 
				continue;
		}
		
			$item_id = $item_detail['ID'] ;
		//echo $_SESSION['Order'][$item_id]['size'];
		if(isset($_SESSION['size_item'][$item_id][$set_key]))
		{
			$size = $_SESSION['size_item'][$item_id][$set_key] ;
			
			if($size!="")
			{
				 $is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					//$price = get_item_sale_price_by_size_new($size,$item_id);
					
					$price = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
				}else{
					$price = get_item_price_by_size_new($size,$item_id);
				}
				
				$item_detail['Price']  = $price ;
				
				
				if($item_detail['item_price_type']=="multi_quantity_price")
				{
					$range_price_detail = get_range_item_price_based_on_qty_and_size($item_id,$CID,$qty_sess,$size);
					if(!empty($range_price_detail))
					{
						$item_detail['Price'] = $range_price_detail['price'];
					}
					
					if(empty($range_price_detail))
					{
						// if not set based on size then we need to get from main setting.
						$range_price_detail = get_range_item_price_based_on_qty($item_id,$CID,$qty_sess);
						if(!empty($range_price_detail))
						{
							$item_detail['Price'] = $range_price_detail['price'];
						}
					}
				}
				  
			}	
			
		}else{
		
			$is_apply_sale_price = check_sale_date($item_id);
			 
			if($is_apply_sale_price==1)
			{
				/*$price = get_item_sale_price_by_size_new($size,$item_id);	
				$item_detail['Price'] = $price ; */
				
				$price = calculate_percentage_item_price($item_id);
				$item_detail['Price'] = $price ; 
			}
			
				// check if item in range price
		if($item_detail['item_price_type']=="multi_quantity_price")
		{
			$range_price_detail = get_range_item_price_based_on_qty($item_id,$CID,$qty_sess);
			if(!empty($range_price_detail))
			{
				$item_detail['Price'] = $range_price_detail['price'];
			}
		}
		
		}
		
		$price = $item_detail['Price'];		
		
		
		
		$tarjar_line_items[] = array(
				"id" => $item_id_sess,
				"quantity" => $qty_sess,
				//"product_tax_code" => "20010", // General tangible goods
				"product_tax_code" => $item_detail['product_tax_code'],
				"unit_price" => $price,
				"discount" => 0.00
		);
		
		 
		$order_total_price_tax_jar += $qty_sess * $price;
		
	
				
				}
				
		}		
	
	$order_data = [
		"from_country" => "US",
		"from_zip" => "45202", // Seller's ZIP (Cincinnati, OH)
		"from_state" => "OH",
		"to_country" => "US",
		"to_zip" => $_SESSION['delivery_form']['shipping_zip'], // Buyer's ZIP (Columbus, OH)
		"to_state" => $_SESSION['delivery_form']['shipping_state'],
		//"amount" => $_SESSION['total_price'], // Product price
		"amount" => $order_total_price_tax_jar, // Product price
		"shipping" => 0.00, // Shipping charge
		"nexus_addresses" => [
			[
				"id" => "Leader-Graphics-OH",
				"country" => "US",
				"zip" => "45202", // Seller's Ohio office
				"state" => "OH"
			]
		],
		"line_items" => $tarjar_line_items
	];
	
		//print_r("<pre>");
		//print_r($order_data);
				
				
	
	
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
		//print_r("<pre>");
		//print_r($data);
		return $data;
	} else {
		return $data = array("error"=>1 , "message"=>  "Error: Unable to calculate tax. HTTP Code: " . $http_code);
	}
	
	curl_close($ch);


}
?>
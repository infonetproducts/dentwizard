<?php
session_start();
include_once("include/db.php");
include_once("shop_common_function.php");

$action = '';
$item_id = '';
$set_key = '';

if(isset( $_POST['action'] ))
{
	$action = $_POST['action'] ;
}

if(isset( $_POST['item_id'] ))
{
	$item_id = $_POST['item_id'] ;
}

if(isset( $_POST['set_key'] ))
{
	$set_key = $_POST['set_key'] ;
}


if($action=="billing_details")
{

}


 
if($action=="update_cart_by_item_id")
{
	
	
	
	sleep(1);
	
	$item_id = $_POST['item_id'] ;
	$cid = $_POST['cid'] ;
	$item_qty = $_POST['qty'] ;
	$canvas_download_url = '';
	
	if(isset( $_POST['canvas_download_url']) and  $_POST['canvas_download_url']!="")
	{
		$canvas_download_url = $_POST['canvas_download_url'] ;
		
		if($canvas_download_url!="")
		{
			$_SESSION['canvas_download_url_new'][$item_id][] = $canvas_download_url ;
				  
		}
		
			
	}
	
	if(isset( $_POST['is_gift_card_item']) and  $_POST['is_gift_card_item']!="")
	{
	
		
			
			$is_gift_card_item = $_POST['is_gift_card_item'] ;
			
		
			if($is_gift_card_item!="")
			{
				$_SESSION['gift_card_is_gift_card_item'][$item_id][] = $is_gift_card_item ;
					  
			}
		
		
		
		
		
		$gift_price = $_POST['gift_price'] ;
		if($gift_price!="")
		{
			$_SESSION['gift_card_gift_price'][$item_id][] = $gift_price ;
				  
		}
		
		$item_price_type = $_POST['item_price_type'] ;
		if($item_price_type!="")
		{
			$_SESSION['gift_card_item_price_type'][$item_id][] = $item_price_type ;
				  
		}
		
		$custom_gift_amount = $_POST['custom_gift_amount'] ;
		if($custom_gift_amount!="")
		{
			$_SESSION['gift_card_custom_gift_amount'][$item_id][] = $custom_gift_amount ;
				  
		}
		
		$custom_gift_email = $_POST['custom_gift_email'] ;
		if($custom_gift_email!="")
		{
			$_SESSION['gift_card_custom_gift_email'][$item_id][] = $custom_gift_email ;
			
			//	$item_qty = $_POST['qty'] ;
			$custom_gift_email_arr = explode(",",$custom_gift_email);
			$custom_gift_email_count = count($custom_gift_email_arr);
			$_POST['qty'] = $custom_gift_email_count;
			$item_qty = $_POST['qty'] ;
				  
		}
		
		$custom_gift_from = $_POST['custom_gift_from'] ;
		if($custom_gift_from!="")
		{
			$_SESSION['gift_card_custom_gift_from'][$item_id][] = $custom_gift_from ;
				  
		}
		
		$custom_gift_message = $_POST['custom_gift_message'] ;
		if($custom_gift_message!="")
		{
			$_SESSION['gift_card_custom_gift_message'][$item_id][] = $custom_gift_message ;
				  
		}
		
		 
		
		
		$custom_gift_delivery_date = $_POST['custom_gift_delivery_date'] ;
		if($custom_gift_delivery_date!="")
		{
			$_SESSION['sess_custom_gift_delivery_date'][$item_id][] = $custom_gift_delivery_date ;
				  
		}
		
			
	}
	
	
	
	if($item_qty==0)
	{	
		unset($_SESSION['Order'][$item_id]);
		
		
		
	}else{
	
		$_SESSION['Order'][$item_id][] = $item_qty  ;
		
		// start code for get what key added to item like : 0 , 1, 2, 3 etc this array : $_SESSION['Order'][$item_id][]
		$total_item_count_for_key = count($_SESSION['Order'][$item_id]);
		$key_item_find = '';
		if($total_item_count_for_key==1)
		{
			$key_item_find = 0;
		}else if($total_item_count_for_key>1)
		{
			$key_item_find = $total_item_count_for_key - 1 ;
		}
		
		/*echo $key_item_find;
		print_r("<pre>");
		print_r($_SESSION['Order'][$item_id]);
		die;*/
		
		// end code for get what key added to item like : 0 , 1, 2, 3 etc this array : $_SESSION['Order'][$item_id][]
		
		
		if(isset( $_POST['size']) and  $_POST['size']!="")
		{
			$size = $_POST['size'] ;
		
		 
			if($size!="")
			{
				 $_SESSION['size_item'][$item_id][] = $size ;
				  
			}
		
		}
		
		if(isset( $_POST['cappyhour_logo']) and  $_POST['cappyhour_logo']!="")
		{
			$cappyhour_logo = $_POST['cappyhour_logo'] ;
		 
			if($cappyhour_logo!="")
			{
				 $_SESSION['sess_cappyhour_logo'][$item_id][] = $cappyhour_logo ;
				  
			}
		
		}
		
		if(isset( $_POST['cappyhour_tonal']) and  $_POST['cappyhour_tonal']!="")
		{
			$cappyhour_tonal = $_POST['cappyhour_tonal'] ;
		 
			if($cappyhour_tonal!="")
			{
				 $_SESSION['sess_cappyhour_tonal'][$item_id][] = $cappyhour_tonal ;
				  
			}
		
		}
		
		 
		
		if(isset( $_POST['waist_size']) and  $_POST['waist_size']!="")
		{
			$waist_size = $_POST['waist_size'] ;
		 
			if($waist_size!="")
			{
				 $_SESSION['waist_size'][$item_id][] = $waist_size ;
				  
			}
		
		}
		
		if(isset( $_POST['length_inches']) and  $_POST['length_inches']!="")
		{
			$length_inches = $_POST['length_inches'] ;
		 
			if($length_inches!="")
			{
				 $_SESSION['length_inches'][$item_id][] = $length_inches ;
				  
			}
		
		}
		
		
		
		if(isset( $_POST['color']) and  $_POST['color']!="")
		{
			$color = $_POST['color'] ;
		
		 
			if($color!="")
			{
				 $_SESSION['color_item'][$item_id][] = $color ;
				  
			}
		
		}
		
		if(isset( $_POST['item_img']) and  $_POST['item_img']!="")
		{
			$item_img = $_POST['item_img'] ;
		
		 
			if($item_img!="")
			{
				 $_SESSION['item_img_tmp'][$item_id][] = $item_img ;
				  
			}
		
		}
		
		
		
		
		if(isset( $_POST['custom_name']) and  $_POST['custom_name']!="")
		{
			$custom_name = $_POST['custom_name'] ;
		 
			if($custom_name!="")
			{
				// $_SESSION['custom_name_tmp'][$item_id][] = $custom_name ;
				 $_SESSION['custom_name_tmp'][$item_id][$key_item_find] = $custom_name ;
				  
			}
		
		}
		
		if(isset( $_POST['custom_name_price']) and  $_POST['custom_name_price']!="")
		{
			$custom_name_price = $_POST['custom_name_price'] ;
		 
			if($custom_name_price!="")
			{
				// $_SESSION['custom_name_price_tmp'][$item_id][] = $custom_name_price ;
				 $_SESSION['custom_name_price_tmp'][$item_id][$key_item_find] = $custom_name_price ;
				  
			}
		
		}
		
		if(isset( $_POST['custom_number']) and  $_POST['custom_number']!="")
		{
			$custom_number = $_POST['custom_number'] ;
		 
			if($custom_number!="")
			{
				 //$_SESSION['custom_number_tmp'][$item_id][] = $custom_number ;
				 $_SESSION['custom_number_tmp'][$item_id][$key_item_find] = $custom_number ;
				  
			}
		
		}
		
		if(isset( $_POST['custom_number_price']) and  $_POST['custom_number_price']!="")
		{
			$custom_number_price = $_POST['custom_number_price'] ;
		 
			if($custom_number_price!="")
			{
				// $_SESSION['custom_number_price_tmp'][$item_id][] = $custom_number_price ;
				
				 $_SESSION['custom_number_price_tmp'][$item_id][$key_item_find] = $custom_number_price ;
				  
			}
		
		}
		
		
		if(isset( $_POST['special_comment']) and  $_POST['special_comment']!="")
		{
			$special_comment = $_POST['special_comment'] ;
		 
			if($special_comment!="")
			{
				 $_SESSION['special_comment_tmp'][$item_id][] = $special_comment ;
				  
			}
		
		}
		
		
		
		if(isset( $_POST['artwork_logo']) and  $_POST['artwork_logo']!="")
		{
			$artwork_logo = $_POST['artwork_logo'] ;
		
		 
			if($artwork_logo!="")
			{
				 $_SESSION['artwork_logo_item'][$item_id][] = $artwork_logo ;
				  
			}
		
		}
		
 		
	}


}





 

if($action=="update_cart_by_item_id_with_size")
{
	sleep(1);
	
	$item_id = $_POST['item_id'] ;
	$cid = $_POST['cid'] ;
	$item_qty = $_POST['qty'] ;
	
	if($item_qty==0)
	{	
		//unset($_SESSION['Order'][$item_id]);
		
	}else{
	
		//$_SESSION['Order'][$item_id] = $item_qty  ;
		
		
		if(isset( $_POST['size']) and  $_POST['size']!="")
		{
			$size = $_POST['size'] ;
		
		
	 		 
		 
			if($size!="")
			{
				
				// $price = get_item_price_by_size_new($size,$item_id);
				
				$is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					$price = get_item_sale_price_by_size_new($size,$item_id);
				}else{
					$price = get_item_price_by_size_new($size,$item_id);
				}
				
				
				
				
				 echo trim($price);
				  
			}
		
		 }else{
			
			 $is_apply_sale_price = check_sale_date($item_id);
			 
			if($is_apply_sale_price==1)
			{
				$price = get_item_sale_price_by_size_new($size,$item_id);	
				 echo trim($price);
			}
			
			
		}
		
	}


}



if($action=="update_cart_old_bk")
{
	
	sleep(1);
	
	unset($_POST['action']);
	
	//pr_n($_POST);
	 
	
	foreach($_POST as $item_id=>$item_qty)
	{
		//pr_n($_POST);
		
		if($item_qty>0)
		{	
			$_SESSION['Order'][$item_id] =  $item_qty ;
			
		}else{
		 
			$_SESSION['Order'][$item_id] =  '' ;
			unset($_SESSION['Order'][$item_id]);
		
		}
		 
	
	}
	
	$action="total_price_calculate" ;

}


if($action=="update_cart")
{
	
	sleep(1);
	
	unset($_POST['action']);
	
	//pr_n($_POST);
	 
	
	foreach($_POST as $item_id=>$item_qty_arr)
	{
		//pr_n($_POST);
		
		foreach($item_qty_arr as $set_key => $item_qty)
		{
		
			if($item_qty>0)
			{	
				$_SESSION['Order'][$item_id][$set_key] =  $item_qty ;
				
			}else{
			 
				$_SESSION['Order'][$item_id][$set_key] =  '' ;
				unset($_SESSION['Order'][$item_id][$set_key]);
				
				unset($_SESSION['size_item'][$item_id][$set_key]);
				unset($_SESSION['color_item'][$item_id][$set_key]);
				unset($_SESSION['artwork_logo_item'][$item_id][$set_key]);
				unset($_SESSION['item_img_tmp'][$item_id][$set_key]);
				
				if(empty($_SESSION['Order'][$item_id]))
				{
					unset($_SESSION['Order'][$item_id]);
					unset($_SESSION['size_item'][$item_id]);
					unset($_SESSION['color_item'][$item_id]);
					unset($_SESSION['artwork_logo_item'][$item_id]);
					unset($_SESSION['item_img_tmp'][$item_id]);
				}
			
			}
		
		}
		 
	
	}
	
	$action="total_price_calculate" ;

}



if($action=="remove_to_cart")
{
	$_SESSION['Order'][$item_id][$set_key] = '';
	unset($_SESSION['Order'][$item_id][$set_key]);
	unset($_SESSION['size_item'][$item_id][$set_key]);
	unset($_SESSION['color_item'][$item_id][$set_key]);
	unset($_SESSION['artwork_logo_item'][$item_id][$set_key]);
	unset($_SESSION['item_img_tmp'][$item_id][$set_key]);
	
}

if($action=="add_to_cart")
{
	sleep(1);
	
	//$_SESSION['Order'][$form_id] = '';
	if(isset($_SESSION['Order'][$item_id]))
	{
		$_SESSION['Order'][$item_id] = $_SESSION['Order'][$item_id] + 1 ;
	
	}else{
		$_SESSION['Order'][$item_id] =  1 ;
	}
	
	
	$action="total_price_calculate" ;
}

//pr_n($_SESSION['Order']);

$total_price = 0 ;
if($action=="total_price_calculate")
{


	if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
	{
		foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
		{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
			foreach($qty_arr as $set_key=>$qty_sess)
			{

			
			
			
			$item_details = get_item_detail_by_item_id($item_id_sess);
			
			
			if($item_details["item_price_type"]=="multi_quantity_price")
			{ 
				$item_id =  $item_details['ID'] ;
				if(isset($_SESSION['size_item'][$item_id][$set_key]))
				{
					$size = $_SESSION['size_item'][$item_id][$set_key] ;
					
					if($size!="")
					{
						 
						// $item_details["Price"] = get_item_price_by_size_new($size,$item_id_sess);
						
						
							$is_apply_sale_price = check_sale_date($item_id_sess);

							if($is_apply_sale_price==1)
							{
								//$item_details["Price"] = get_item_sale_price_by_size_new($size,$item_id_sess);
								
								$item_details["Price"] = get_item_sale_price_by_size_new_with_percentage($size,$item_id_sess);
								
								
							}else{
								$item_details["Price"] = get_item_price_by_size_new($size,$item_id_sess);
							}
									
						
						
						  
					}	
					
				 }else{
			
					 $is_apply_sale_price = check_sale_date($item_id_sess);
					 
					if($is_apply_sale_price==1)
					{
						$item_details['Price']  = get_item_sale_price_by_size_new($size,$item_id_sess);	
						 
					}
					
					
				}
				
				
				
			
			}else{
			
					 
					 $is_apply_sale_price = check_sale_date($item_id_sess);
					 
					if($is_apply_sale_price==1)
					{
						//$item_details['Price']  = get_item_sale_price_by_size_new($size,$item_id_sess);	
						
						$item_details['Price']  = get_item_sale_price_by_size_new_with_percentage($size,$item_id_sess);	
						
						 
					}
					
					
				 
					
					
			}
			
			
			
			//pr_n($item_details);
			
			 $total_price += $item_details['Price'] * $qty_sess ;
			 
			 
			  if(isset($_SESSION['custom_number_tmp'][$item_id_sess][$set_key]))
			 {
			 	 $total_price += $_SESSION['custom_number_price_tmp'][$item_id_sess][$set_key] ;
			 }
			 
			  if(isset($_SESSION['custom_name_tmp'][$item_id_sess][$set_key]))
			 {
			 	 $total_price += $_SESSION['custom_name_price_tmp'][$item_id_sess][$set_key] ;
			 }
			 
			 
		}	
		
		}
	}
	

 

if(isset($_SESSION['delivery_form']['delivery_method']) and $total_price>0)
{
	$delivery_method = $_SESSION['delivery_form']['delivery_method'];
	$delivery_charge = '';
	
	if($delivery_method=="3_to_5_days")
	{
		$delivery_charge = 0 ;
	}
	
	if($delivery_method=="1_to_2_days")
	{
		$delivery_charge = 0 ;
	}
	
	if($delivery_method=="7_to_10_days")
	{
		if($_SESSION['CID']==56 or $_SESSION['CID']==59 or  $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==78 or $_SESSION['CID']==72 or $_SESSION['CID']==89)
		{
			$delivery_charge = 0 ;
		}else{
			$delivery_charge = 10 ;
		}
		
	 

		
		
		
		
	}
	
	
	
	
	$total_price = $total_price + $delivery_charge ;
		
}


if(isset($_SESSION['billing_form']['shipping_state']) and $_SESSION['billing_form']['shipping_state']=='PA')
{
	$shipping_state = $_SESSION['billing_form']['shipping_state'] ;
	$shipping_zip = $_SESSION['billing_form']['shipping_zip'] ;
	
	 	$shipping_zip = mysql_escape_string($shipping_zip);
		$shipping_state = mysql_escape_string($shipping_state);
	
	$sql_get_exits = "select * from pa_zipcodes where  zip_code = '$shipping_zip'
		 and state_name = '$shipping_state' ";
		 
	$rs_exits = mysql_query( $sql_get_exits );	
	
	if(mysql_num_rows($rs_exits)>0 and $total_price>0)
	{
		// $total_price = $total_price + 5.94 ; // 5.94 , it is salex tax on PA state zip code.
		
		 $total_price = $total_price + 0  ; // 5.94 , it is salex tax on PA state zip code.
		
		$is_PA_state = 1 ;
	}	
	
	
}


$total_price = number_format($total_price,2);

// before discount
$_SESSION['total_price_before_discount'] = $total_price ;
// after discount 
 $total_price = calculate_discount_client_setting($total_price,$_SESSION['CID']); 

$_SESSION['total_price_after_discount'] = $total_price ;

$_SESSION['total_price'] = $total_price  ;
	
echo trim($total_price);
die;

}

if($action=="client_discount_total_price_calculate")
{
	$is_need_apply_client_discount = check_sale_date_client_setting($_SESSION['CID']);
	
	//pr_n($_SESSION);
	
	if($is_need_apply_client_discount==1)
	{
		$total_price_before_discount = $_SESSION['total_price_before_discount'] ;
		 $total_price_after_discount = $_SESSION['total_price_after_discount'] ;
		if($total_price_after_discount>0)
		{
			echo "<strong class='product-price-discount'>$<strong id='ajax_p'>$total_price_before_discount</strong><i>$<strong id='ajax_sale_price'>$total_price_after_discount</strong></i></strong>";
			die;	
		}else{
			die("not_discount");
		}
		
		
	}else{
	
		die("not_discount");
		
	}
	
}



$cart_total_price = 0 ;
if($action=="cart_total_calculate")
{
	if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
	{
		foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
		{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
		foreach($qty_arr as $set_key=>$qty_sess)
		{
			$item_details = get_item_detail_by_item_id($item_id_sess);
			
			
			
			
			if($item_details["item_price_type"]=="multi_quantity_price")
			{
				$item_id =  $item_details['ID'] ;
				
				if(isset($_SESSION['size_item'][$item_id][$set_key]))
				{
					$size = $_SESSION['size_item'][$item_id][$set_key] ;
					
					if($size!="")
					{
						 
						//$item_details["Price"] = get_item_price_by_size_new($size,$item_id_sess);
						
						
						
							$is_apply_sale_price = check_sale_date($item_id_sess);

							if($is_apply_sale_price==1)
							{
								$item_details["Price"] = get_item_sale_price_by_size_new($size,$item_id_sess);
							}else{
								$item_details["Price"] = get_item_price_by_size_new($size,$item_id_sess);
							}
								
						
						
						  
					}
					
				 }else{
			
					 $is_apply_sale_price = check_sale_date($item_id_sess);
					 
					if($is_apply_sale_price==1)
					{
						$item_details['Price']  = get_item_sale_price_by_size_new($size,$item_id_sess);	
						 
					}
					
					
				}
				
				
				
			
			 }else{
			
			 $is_apply_sale_price = check_sale_date($item_id_sess);
			 
			if($is_apply_sale_price==1)
			{
				$price = get_item_sale_price_by_size_new($size,$item_id_sess);	
				$item_details['Price'] = $price ; 
			}
			
			
		}
		
			
			
			
			
			//pr_n($item_details);
			
			 $cart_total_price += $item_details['Price'] * $qty_sess ;
			 
			 
		}
			 
			 
			
		}
	}
	

$cart_total_price = number_format($cart_total_price,2);

echo trim($cart_total_price);
die;

}



if($action=="price_by_size")
{
	$size = $_POST['size'] ;
	$item_id = $_POST['item_id'] ;
	
	//pr_n($_POST);
	
	$price = get_item_price_by_size_new($size,$item_id);
	//$price = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
	echo $price ;

}

if($action=="sale_price_by_size")
{
	$size = $_POST['size'] ;
	$item_id = $_POST['item_id'] ;
	
	//pr_n($_POST);
	
	//$price = get_item_sale_price_by_size_new($size,$item_id);
	
	$price = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
	echo $price ;

}


include_once("ajax_cart_new.php");
?>
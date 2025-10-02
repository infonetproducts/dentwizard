<?php



function get_range_item_price_based_on_qty_and_size($item_id,$CID,$qty,$size)
{
	$qty = mysql_escape_string($qty);
	if (!is_numeric($qty)) exit;
	
	$item_id = mysql_escape_string($item_id);
	if (!is_numeric($item_id)) exit;
	
	
	 $sql_get_range_price = " select * from items_range_price_options 
								where 
									cid = $CID 
									and item_id = '$item_id' 
									and size = '$size' 
									and  to_qty >= $qty	
									order by from_qty asc
									limit 1
								";
	
	$rs_range_price = mysql_query($sql_get_range_price)   ;
	
	$total_row = mysql_num_rows($rs_range_price);
	
	if($total_row==0)
	{
	
		$sql_get_range_price = "
			SELECT price
			FROM items_range_price_options
			WHERE 
			cid = $CID 
			and item_id = '$item_id'
			and size = '$size'  
			and to_qty <= to_qty
			ORDER BY to_qty DESC
			LIMIT 1
		";
		
		$rs_range_price = mysql_query($sql_get_range_price)   ;
		$total_row = mysql_num_rows($rs_range_price);
	
	}
	
	
	
	
	$range_price_detail = array();
	
	if(mysql_num_rows($rs_range_price)>0)
	{
		$range_price_detail = mysql_fetch_assoc($rs_range_price);
	}
	
	if(!empty($range_price_detail))
	{
		$range_price_detail['price'] = number_format($range_price_detail['price'],2);
	}
	
	//pr_n($range_price_detail);
	
	return $range_price_detail;	
	
}

function get_range_item_price_based_on_qty($item_id,$CID,$qty)
{
	$item_id = mysql_escape_string($item_id);
	if (!is_numeric($item_id)) exit;
	
	$qty = mysql_escape_string($qty);
	if (!is_numeric($qty)) exit;
	
	
	 $sql_get_range_price = " select * from items_range_price 
								where 
									cid = $CID 
									and item_id = '$item_id' 
									and  to_qty >= $qty	
									order by from_qty asc
									limit 1
								";
	
	$rs_range_price = mysql_query($sql_get_range_price)   ;
	$total_row = mysql_num_rows($rs_range_price);
	
	if($total_row==0)
	{
	
		$sql_get_range_price = "
			SELECT price
			FROM items_range_price
			WHERE 
			cid = $CID 
			and item_id = '$item_id' 
			and to_qty <= to_qty
			ORDER BY to_qty DESC
			LIMIT 1
		";
		
		$rs_range_price = mysql_query($sql_get_range_price)   ;
		$total_row = mysql_num_rows($rs_range_price);
	
	}
	
	
	$range_price_detail = array();
	
	if(mysql_num_rows($rs_range_price)>0)
	{
		$range_price_detail = mysql_fetch_assoc($rs_range_price);
	}
	
	if(!empty($range_price_detail))
	{
		$range_price_detail['price'] = number_format($range_price_detail['price'],2);
	}
	
	//pr_n($range_price_detail);
	
	return $range_price_detail;	
	
}

function get_all_category_for_custom()
{
	 // 569 , 788 , 1181 , 781
	// $array_cat = array ( 569 , 788 , 1181 , 781 );
	// below is custom category  
	$array_cat[] = 569 ;
	$array_cat[] = 788 ;
	$array_cat[] = 1181 ;
	$array_cat[] = 781 ;
	 
	 $sql_get_sub_cats = "
	 	
		SELECT * FROM `Category` WHERE  ParentID IN ( 569 , 788 , 1181 , 781 )
	 ";
	 
	 $rs_sub_cat = mysql_query($sql_get_sub_cats);
	 if(mysql_num_rows($rs_sub_cat)>0)
	 {
	 	while($sub_cat_detail = mysql_fetch_assoc($rs_sub_cat))
		{
			$array_cat[] = $sub_cat_detail["ID"];
		}
	 }
	 
	 //pr_n($array_cat);
	 
	 return $array_cat;
	 
}


function get_client_setting($cid)
{
	 $sql_get_client_setting = " 
		SELECT * FROM Clients
									WHERE ID =  '$cid'
		  ";
		$rs_client_setting = mysql_query($sql_get_client_setting) ;
	    $client_detail = mysql_fetch_assoc($rs_client_setting) ;
		return $client_detail;
}

function check_sale_date_client_setting($cid)
{
	
	$cid = mysql_escape_string($cid);
	 
	 $sql_get_client_setting = " 
	 	
		SELECT is_enable_sale , percentage_off , sale_start_date , sale_end_date 
									FROM Clients
									WHERE ID =  '$cid'
									 
		  ";
		  
		
		$rs_client_info = mysql_query($sql_get_client_setting) ;
	 
	    $client_detail = mysql_fetch_assoc($rs_client_info) ;
		
		$percentage_off = $client_detail['percentage_off'] ;
		$sale_start_date = $client_detail['sale_start_date'] ;
		$sale_end_date = $client_detail['sale_end_date'] ;
		$today_date = date("Y-m-d");
		
		if($sale_start_date=="0000-00-00" and $sale_end_date=="0000-00-00")
		{
			if($sale_price>0)
			{
				return 1 ;  // need to apply percentage discount
			}else{
				return 2 ; // not need to apply percentage discount
			}
		}
		
		
		if($sale_start_date!="0000-00-00" and $sale_end_date!="0000-00-00")
		{
			// check date is expire or not
			
			if($today_date==$sale_start_date)
			{
				// then percentage discount will apply
				return 1 ; 
				
			}else if($today_date==$sale_end_date)
			{
				// then percentage discount will apply
				return 1 ; 
			}else if( $sale_end_date > $today_date )
			{
				if($today_date > $sale_start_date)
				{
					// then percentage discount will apply
					return 1 ; 
				}else{
					return 2 ; // not need to apply sale tax
				}
			}
			
		}else{
					return 2 ; // not need to apply percentage discount
		}
		
	
	
}

function calculate_discount_client_setting($total_price,$cid)
{
	$sql_get_client_setting = " 
		SELECT * FROM Clients WHERE ID =  '$cid'
		  ";
		$rs_client_setting = mysql_query($sql_get_client_setting) ;
	    $client_detail = mysql_fetch_assoc($rs_client_setting) ;
		
		$is_apply_discount_client = check_sale_date_client_setting($cid);
		
		if($client_detail['is_enable_sale']==1 and $is_apply_discount_client==1)
		{
				if($client_detail['percentage_off']>0)
				{
					$total_price_discount = ($total_price * $client_detail['percentage_off'] ) / 100 ; 
					$total_price = $total_price - $total_price_discount ;
				}
		}
		
		$total_price = number_format($total_price,2);
		
		
		return trim($total_price); 
		
			
}


function second_friday_from_current_date()
{

	$dt = new DateTime();
	$dt->modify('next friday'); // Next friday from now
	$dt->modify('next friday'); // Next friday from next friday
	
	$second_friday = $dt->format('m/d/Y');
	
	return $second_friday ;

}


function check_sale_date($item_id)
{
	
	
	$item_id = mysql_escape_string($item_id);
	 
	 $sql_get_item_info = " 
	 	
		SELECT sale_price , sale_start_date , sale_end_date  
									FROM Items
									WHERE ID =  '$item_id'
									 
		  ";
		  
		
		$rs_item_info = mysql_query($sql_get_item_info) ;
	 
	    $item_detail = mysql_fetch_assoc($rs_item_info) ;
		
		$sale_price = $item_detail['sale_price'] ;
		$sale_start_date = $item_detail['sale_start_date'] ;
		$sale_end_date = $item_detail['sale_end_date'] ;
		$today_date = date("Y-m-d");
		
		if($sale_start_date=="0000-00-00" and $sale_end_date=="0000-00-00")
		{
			if($sale_price>0)
			{
				return 1 ;  // need to apply sale price
			}else{
				return 2 ; // not need to apply sale price
			}
		}
		
		
		if($sale_start_date!="0000-00-00" and $sale_end_date!="0000-00-00")
		{
			// check date is expire or not
			
			if($today_date==$sale_start_date)
			{
				// then sale price will apply
				return 1 ; 
				
			}else if($today_date==$sale_end_date)
			{
				// then sale price will apply
				return 1 ; 
			}else if( $sale_end_date > $today_date )
			{
				if($today_date > $sale_start_date)
				{
					// then sale price will apply
					return 1 ; 
				}else{
					return 2 ; // not need to apply sale tax
				}
			}
			
		}else{
					return 2 ; // not need to apply sale tax
		}
		
	
	
}

function calculate_percentage_item_price($item_id)
{
	$item_id = mysql_escape_string($item_id);
	  $sql_get_item_info = " 
		SELECT *   
									FROM Items
									WHERE ID =  '$item_id'
	";
		  
		
		$rs_item_info = mysql_query($sql_get_item_info) ;
	 
	    $item_detail = mysql_fetch_assoc($rs_item_info) ;
		
		$sale_type = $item_detail['sale_type'] ;
		$sale_price = $item_detail['sale_price'] ;
		$item_price = $item_detail['Price'] ;
		
		$percentage_off_item = $item_detail['percentage_off_item'] ;
		
		if($sale_type=="percentage")
		{
			if($percentage_off_item>0)
			{
				$discount_percentage_price = ( $item_price * $percentage_off_item ) / 100 ;
				$item_price = $item_price - $discount_percentage_price ;
				//echo  $item_price;
				return $item_price;
			}
		}
		
		if($sale_type=="specific_price")
		{
			return $sale_price ;
		}
		
		return $item_detail['Price'] ;
		
			
	
}


function check_sale_date_new($item_id)
{
	
	
	$item_id = mysql_escape_string($item_id);
	 
	 $sql_get_item_info = " 
	 	
		SELECT sale_price , sale_start_date , sale_end_date  
									FROM Items
									WHERE ID =  '$item_id'
									 
		  ";
		  
		
		$rs_item_info = mysql_query($sql_get_item_info) ;
	 
	    $item_detail = mysql_fetch_assoc($rs_item_info) ;
		
		$sale_type = $item_detail['sale_type'] ;
		
		$sale_price = $item_detail['sale_price'] ;
		$sale_start_date = $item_detail['sale_start_date'] ;
		$sale_end_date = $item_detail['sale_end_date'] ;
		$today_date = date("Y-m-d");
		
		if($sale_start_date=="0000-00-00" and $sale_end_date=="0000-00-00")
		{
			if($sale_price>0)
			{
				return 1 ;  // need to apply sale price
			}else{
				return 2 ; // not need to apply sale price
			}
		}
		
		
		if($sale_start_date!="0000-00-00" and $sale_end_date!="0000-00-00")
		{
			// check date is expire or not
			
			if($today_date==$sale_start_date)
			{
				// then sale price will apply
				return 1 ; 
				
			}else if($today_date==$sale_end_date)
			{
				// then sale price will apply
				return 1 ; 
			}else if( $sale_end_date > $today_date )
			{
				if($today_date > $sale_start_date)
				{
					// then sale price will apply
					return 1 ; 
				}else{
					return 2 ; // not need to apply sale tax
				}
			}
			
		}else{
					return 2 ; // not need to apply sale tax
		}
		
	
	
}




function get_item_price_group_name($item_id)
{
 	$item_id = mysql_escape_string($item_id);
	
	$sql_get_group_id = "SELECT * 
FROM  `item_groups` 
WHERE  group_name = 'Size' and `item_id` = $item_id ";
list($group_id) = mysql_fetch_row(mysql_query($sql_get_group_id));

if($group_id=="")
{
		$sql_get_group_id = "SELECT * 
	FROM  `item_groups` 
	WHERE  group_name like '%Size%' and `item_id` = $item_id ";
	list($group_id) = mysql_fetch_row(mysql_query($sql_get_group_id));
}
	
	
	$group_name = '';
	
/*	 $sql_get_group_name = " SELECT ig.group_name FROM item_group_options igp
	 inner join item_groups ig ON ig.group_id = igp.group_id
	 where igp.item_id = '$item_id' and igp.price>0  group by ig.group_id " ;*/
	 
	 	 $sql_get_group_name = " SELECT ig.group_name FROM item_group_options igp
	 inner join item_groups ig ON ig.group_id = igp.group_id
	 where igp.item_id = '$item_id' and igp.group_id = '$group_id'  group by ig.group_id " ;
	
	
	$rs_group = mysql_query($sql_get_group_name) ;
	
	if( mysql_num_rows($rs_group) >  0 )
	{
		list($group_name) = mysql_fetch_row($rs_group); 
	}
	
	return $group_name ;
}

function get_item_size_drop_down_list($item_id)
{
	$item_size_list = array();
	
	$item_id = mysql_escape_string($item_id);
	
	$sql_get_group_id = "SELECT * 
FROM  `item_groups` 
WHERE  group_name = 'Size' and `item_id` = $item_id ";
list($group_id) = mysql_fetch_row(mysql_query($sql_get_group_id));

if($group_id=="")
{
		$sql_get_group_id = "SELECT * 
	FROM  `item_groups` 
	WHERE  group_name like '%Size%' and `item_id` = $item_id ";
	list($group_id) = mysql_fetch_row(mysql_query($sql_get_group_id));
}
	
	
	 
	$sql_item_size = " SELECT * FROM item_group_options where item_id = '$item_id' and group_id = '$group_id' order by created_date_time asc ";
	$rs_item_size = mysql_query($sql_item_size) ;
	
	if( mysql_num_rows($rs_item_size) >  0 )
	{
		while($size_detail = mysql_fetch_assoc($rs_item_size) )
		{
			$value =  $size_detail['value'] ;
			$price =  $size_detail['price'] ;
			$price = number_format($price,2);
			$item_size_list[$value] = $price ;
		}
	}
	
	return $item_size_list ;
	
}

function get_item_color_drop_down_list($item_id)
{
	$item_color_list = array();
	
	$item_id = mysql_escape_string($item_id);
	
	$sql_get_group_id = "SELECT * 
FROM  `item_groups` 
WHERE  group_name = 'Color' and `item_id` = $item_id ";
list($group_id) = mysql_fetch_row(mysql_query($sql_get_group_id));
	
	 
	//$sql_item_color = " SELECT * FROM item_group_options where item_id = '$item_id' and price=0";
	
	$sql_item_color = " SELECT * FROM item_group_options where item_id = '$item_id' and price=0 and group_id='$group_id' ";
	
	$rs_item_color = mysql_query($sql_item_color) ;
	
	if( mysql_num_rows($rs_item_color) >  0 )
	{
		while($size_detail = mysql_fetch_assoc($rs_item_color) )
		{
			$color =  $size_detail['value'] ;
			$price =  $size_detail['price'] ;
			$price = number_format($price,2);
			$item_color_list[$color] = $price ;
		}
	}
	
	return $item_color_list ;
	
}

function get_item_color_image($item_id)
{
	$item_color_image_list = array();
	
	$item_id = mysql_escape_string($item_id);
	 
	 
	 
	 
	$sql_item_color_image = " SELECT * FROM item_group_options where item_id = '$item_id' and price=0";
	$rs_item_color_image = mysql_query($sql_item_color_image) ;
	
	if( mysql_num_rows($rs_item_color_image) >  0 )
	{
		while($color_image_detail = mysql_fetch_assoc($rs_item_color_image) )
		{
			$color_image =  $color_image_detail['color_image'] ;
			$value =  $color_image_detail['value'] ;		 
			$item_color_image_list[$value] = $color_image ;
		}
	}
	
	return $item_color_image_list ;
	
}


function get_default_price_item($item_id)
{

	$item_id = mysql_escape_string($item_id);
	
	$item_detail = get_item_detail_by_item_id($item_id);
	
	
	// pr_n($item_detail);
 	$price = 0;
	
 	if($item_detail['item_price_type']=="multi_quantity_price")
	{
		$price_multi = $item_detail['price_multi'] ;
		$price_multi_str =  trim( $price_multi ) ;
		$price_multi_arr = explode("\n",$price_multi_str);
		
		foreach($price_multi_arr as $price_str)
		{
			$price_arr = explode(",",$price_str);
			$select_qty = '';
			
			$selected = '';
						
			if($price_arr!="")
			{	
				$find_star_arr = explode("*",$price_arr[1]);
				
				
				if(isset($find_star_arr[1]))
				{
					$price_arr[1] = $find_star_arr[0];
					$price = $find_star_arr[0];
					$price = number_format($price,2);
				}
				
			}
			
			//pr_n($price_arr);
			 
			
			
		}
		
		
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;
	
	

}

function get_item_price_by_size_old($size,$item_id)
{
	
	$item_id = mysql_escape_string($item_id);
	
	$item_id_sess =  $item_id ;
	
	$item_detail = get_item_detail_by_item_id($item_id_sess);

 
	//pr_n($item_detail);
 	$price = 0;
	
 	if($item_detail['item_price_type']=="multi_quantity_price")
	{
		$price_multi = $item_detail['price_multi'] ;
		$price_multi_str =  trim( $price_multi ) ;
		$price_multi_arr = explode("\n",$price_multi_str);
		
		foreach($price_multi_arr as $price_str)
		{
			$price_arr = explode(",",$price_str);
			$select_qty = '';
			
			//pr_n($price_arr);
			
			 $size_val = $price_arr[0] ;
			
			 
			$size_price = $price_arr[1] ;
			
			$size = trim($size);
			$size_val = trim($size_val);
			
			if($size_val==$size)
			{
				$price = $size_price ;
				
			}
			
			
		}
		
		
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;

}



function get_item_price_by_size_new($size,$item_id)
{

	$item_size_detail = array();
	 
	 $size = mysql_escape_string($size);
	 $item_id = mysql_escape_string($item_id);
	 
	  $sql_get_item_size_detail = " SELECT * 
								FROM item_group_options
								WHERE item_id =  '$item_id'
								AND value =  '$size'
	  ";
	  
	
	
	$rs_item_size_detail = mysql_query($sql_get_item_size_detail) ;
	
	if( mysql_num_rows($rs_item_size_detail) >  0 )
	{
		 $item_size_detail = mysql_fetch_assoc($rs_item_size_detail) ;
		 $price = $item_size_detail['price'] ;
		 
	}else{
		
			 $sql_get_item_price = " SELECT * 
									FROM Items
									WHERE ID =  '$item_id'
									 
		  ";
		  
		
		 $rs_item_price = mysql_query($sql_get_item_price) ;
	 
	    $item_price_detail = mysql_fetch_assoc($rs_item_price) ;
		 $price = $item_price_detail['Price'] ;
	 
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;


}


function get_item_sale_price_by_size_new_with_percentage($size,$item_id)
{

	 $item_id = mysql_escape_string($item_id);
	 $size = mysql_escape_string($size);
	 
	  $sql_get_item_info = " 
		SELECT *   
									FROM Items
									WHERE ID =  '$item_id'
	";
	$rs_item_info = mysql_query($sql_get_item_info) ;
	$item_detail = mysql_fetch_assoc($rs_item_info) ;
	$sale_type = $item_detail['sale_type'] ;
	$sale_price = $item_detail['sale_price'] ;
	$item_price = $item_detail['Price'] ;
	$percentage_off_item = $item_detail['percentage_off_item'] ;
	
	 
	
	$item_size_detail = array();
	 
	  $sql_get_item_size_detail = " SELECT * 
								FROM item_group_options
								WHERE item_id =  '$item_id'
								AND value =  '$size'
	  ";
	  
	
	
	$rs_item_size_detail = mysql_query($sql_get_item_size_detail) ;
	
	if( mysql_num_rows($rs_item_size_detail) >  0 )
	{
		 $item_size_detail = mysql_fetch_assoc($rs_item_size_detail) ;
		 $price = $item_size_detail['sale_price'] ;
		 
		  $is_apply_sale_price = check_sale_date($item_id);
		 
		 
		 
		 if($sale_type=="percentage" and $is_apply_sale_price==1)
		{
			if($percentage_off_item>0)
			{
				$discount_percentage_price = ( $price * $percentage_off_item ) / 100 ;
				$price = $price - $discount_percentage_price ;
				//echo  $price;
			}
		}
		
		if($sale_type=="specific_price" and $is_apply_sale_price==1)
		{
			$price = $item_size_detail['sale_price'] ; 
		}
		
		if($is_apply_sale_price!=1)
		{
			$price = $item_size_detail['price'] ;
		}
		 
		 
		 
		 
	}else{
		
			 $sql_get_item_price = " SELECT * 
									FROM Items
									WHERE ID =  '$item_id'
									 
		  ";
		  
		
		 $rs_item_price = mysql_query($sql_get_item_price) ;
	 
	    $item_price_detail = mysql_fetch_assoc($rs_item_price) ;
		$price = $item_price_detail['sale_price'] ;
		
		 $is_apply_sale_price = check_sale_date($item_id);
		
		
		if($sale_type=="percentage" and $is_apply_sale_price==1)
		{
			if($percentage_off_item>0)
			{
				$discount_percentage_price = ( $item_price * $percentage_off_item ) / 100 ;
				$price = $item_price - $discount_percentage_price ;
				//echo  $price;
				 
			}
		}
		
		if($sale_type=="specific_price" and $is_apply_sale_price==1)
		{
			$price = $item_price_detail['sale_price'] ;
			//return $sale_price ;
		}
		
		if($is_apply_sale_price!=1)
		{
			$price = $item_price_detail['Price'] ;
		}
		
		
		
		
	 
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;


}



function get_item_sale_price_by_size_new($size,$item_id)
{

	 $item_id = mysql_escape_string($item_id);
	  $size = mysql_escape_string($size);
	
	$item_size_detail = array();
	 
	  $sql_get_item_size_detail = " SELECT * 
								FROM item_group_options
								WHERE item_id =  '$item_id'
								AND value =  '$size'
	  ";
	  
	
	
	$rs_item_size_detail = mysql_query($sql_get_item_size_detail) ;
	
	if( mysql_num_rows($rs_item_size_detail) >  0 )
	{
		 $item_size_detail = mysql_fetch_assoc($rs_item_size_detail) ;
		 $price = $item_size_detail['sale_price'] ;
		 
	}else{
		
			 $sql_get_item_price = " SELECT * 
									FROM Items
									WHERE ID =  '$item_id'
									 
		  ";
		  
		
		 $rs_item_price = mysql_query($sql_get_item_price) ;
	 
	    $item_price_detail = mysql_fetch_assoc($rs_item_price) ;
		 $price = $item_price_detail['sale_price'] ;
	 
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;


}



function get_item_price_by_size($size,$item_id)
{
	
	 $item_id = mysql_escape_string($item_id);
	  $size = mysql_escape_string($size);
	
	$item_id_sess =  $item_id ;
	
	
	
	$item_detail = get_item_detail_by_item_id($item_id_sess);

 
	//pr_n($item_detail);
 	$price = 0;
	
 	if($item_detail['item_price_type']=="multi_quantity_price")
	{
		$price_multi = $item_detail['price_multi'] ;
		$price_multi_str =  trim( $price_multi ) ;
		$price_multi_arr = explode("\n",$price_multi_str);
		
		foreach($price_multi_arr as $price_str)
		{
			$price_arr = explode(",",$price_str);
			$select_qty = '';
			
			//pr_n($price_arr);
			
			 $size_val = $price_arr[0] ;
			
			 
			$size_price = $price_arr[1] ;
			
			$size = trim($size);
			$size_val = trim($size_val);
			
			if($size_val==$size)
			{
				$price = $size_price ;
				
			}
			
			
		}
		
		
		
	}
	
	$price =  number_format( $price , 2 ) ;
	return $price;

}


function toSafeDisplay_edit_time_shop($userGeneratedValue) 
{
	 if(strpos($userGeneratedValue,"''") !== false) 
	  {   
			$userGeneratedValue = str_replace("''","'",$userGeneratedValue);
			return $userGeneratedValue;
				
	  }
	  
	  if(strpos($userGeneratedValue,"\'") !== false) 
	  {   
			$userGeneratedValue = str_replace("\'","'",$userGeneratedValue);
			return $userGeneratedValue;
				
	  }
	  
	  return $userGeneratedValue;

}



function get_item_category_by_item_id($item_id)
{
	
	
	 $item_id = mysql_escape_string($item_id);
	  
	
	$item_category_detail = array();
	
		$sql_get_item_category = "
		SELECT c.Name, c.ID AS cat_id
	FROM Category c
	INNER JOIN FormCategoryLink fcl ON fcl.CategoryID = c.ID
	WHERE fcl.FormID = $item_id limit 1 ";
	
	$rs_cat = mysql_query($sql_get_item_category) ;
		
	if( mysql_num_rows($rs_cat) >  0 )
	{
		$item_category_detail =  mysql_fetch_assoc($rs_cat) ;
	}	

	return $item_category_detail;

}

function get_parent_gategory($CID)
{
	$parent_category_list = array();
	
	$CID = mysql_escape_string($CID);
	 
	 $sql_get_parent_category = " SELECT * FROM Category where CID = '$CID'  and ParentID=0  and Active='Y' 
	 
	order by display_order ASC
	 
	 ";
	$rs_parent_cat = mysql_query($sql_get_parent_category) ;
	
	if( mysql_num_rows($rs_parent_cat) >  0 )
	{
		while($parent_cat_detail = mysql_fetch_assoc($rs_parent_cat) )
		{
			$cat_id =  $parent_cat_detail['ID'] ;
			
			$parent_category_list[$cat_id] = $parent_cat_detail['Name'] ;
		}
	}
	
	return $parent_category_list ;

}

function is_parent_category($CID,$cat_id)
{
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);

	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' and ParentID = 0  ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		return 1 ; // Yes it is parent category
		
	}else{
		
		return 0 ; // no it is not parent category
	}
}

function map_array($all_item_list)
{
	$item_list = array();
	
	if(!empty($all_item_list))
	{
		foreach($all_item_list as $item_list_arr)
		{
			foreach($item_list_arr as $item_detail_arr)
			{
				$item_list[] = $item_detail_arr ;	
			}
		}
	}
	
	return $item_list ;	
}

function get_all_sub_category_of_parent_category($CID , $cat_id)
{
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	
	$sql_get_sub_category = " SELECT * FROM Category where CID = '$CID'  and Active='Y' and ParentID = $cat_id  ";
	$sub_cat_array_list = array(); 
	
	$rs_sub_cat = mysql_query($sql_get_sub_category) ;
	
	if( mysql_num_rows($rs_sub_cat) >  0 )
	{
		while ( $sub_cat_detail = mysql_fetch_assoc($rs_sub_cat)  )
		{
			$sub_cat_array_list[$sub_cat_detail['ID']] = $sub_cat_detail['Name'] ;
		}
	}
	
	return $sub_cat_array_list ; 
	
}

function get_parent_category_name($CID,$cat_id)
{
	$cat_name = array();
	$cat_name_str = "";
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	 
	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		 $cat_detail = mysql_fetch_assoc($rs_cat)  ; 
		 
			$cat_id =  $cat_detail['ID'] ;
			$ParentID =  $cat_detail['ParentID'] ;
			
			if($ParentID==0)
			{	
					$cat_name_str = $cat_detail['Name'] ;
			}
			
			
			 
		 
	}
	
	return $cat_name_str ;

}

function get_category_detail($CID,$cat_id)
{
	$cat_detail = array();
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	 
	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		  $cat_detail = mysql_fetch_assoc($rs_cat)  ; 
	}
	
	return $cat_detail ;	
}

function get_category_detail_by_cat_id($CID,$cat_id)
{
	$cat_name = array();
	$cat_name_str = "";
	$ParentID = '';
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	 
	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		  $cat_detail = mysql_fetch_assoc($rs_cat)  ; 
		 
			$cat_id =  $cat_detail['ID'] ;
			$ParentID =  $cat_detail['ParentID'] ;
			
	}
	
	return $ParentID ;		
	
	
}	

function get_category_name($CID,$cat_id)
{
	$cat_name = array();
	$cat_name_str = "";
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	 
	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		 $cat_detail = mysql_fetch_assoc($rs_cat)  ; 
		 
			$cat_id =  $cat_detail['ID'] ;
			$ParentID =  $cat_detail['ParentID'] ;
			
			if($ParentID>0)
			{
				// it is sub category we need to get first parent of this.
				$sql_get_parent_detail = " SELECT * FROM Category where CID = '$CID'  and ID = '$ParentID'  and Active='Y' ";
				$rs_cat_parent = mysql_query($sql_get_parent_detail) ;
				
				if( mysql_num_rows($rs_cat_parent) >0 )
				{
					$parent_cat_detail =   mysql_fetch_assoc($rs_cat_parent)  ; 
					
					$cat_name_str = $parent_cat_detail['Name'] ;
				}
				
				if($cat_name_str!="")
				{
					$cat_name_str .=": ".$cat_detail['Name'] ;
				}
				
					
			}
			
			
			 
		 
	}
	
	return $cat_name_str ;

}

function get_category_name_for_breadcrum($CID,$cat_id)
{
	$cat_name = array();
	$cat_name_str = "";
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	 
	$sql_get_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' ";
	$rs_cat = mysql_query($sql_get_category) ;
	
	
	
	if( mysql_num_rows($rs_cat) >  0 )
	{
		 $cat_detail = mysql_fetch_assoc($rs_cat)  ; 
		 
			$cat_id =  $cat_detail['ID'] ;
			$ParentID =  $cat_detail['ParentID'] ;
			
			if($ParentID>0)
			{
				// it is sub category we need to get first parent of this.
				$sql_get_parent_detail = " SELECT * FROM Category where CID = '$CID'  and ID = '$ParentID'  and Active='Y' ";
				$rs_cat_parent = mysql_query($sql_get_parent_detail) ;
				
				if( mysql_num_rows($rs_cat_parent) >0 )
				{
					$parent_cat_detail =   mysql_fetch_assoc($rs_cat_parent)  ; 
					
					$cat_name_str = $parent_cat_detail['Name'] ;
				}
				
				if($cat_name_str!="")
				{
					$cat_name_str .="> ".$cat_detail['Name'] ;
				}
				
					
			}
			
			
			 
		 
	}
	
	return $cat_name_str ;

}

function get_parent_cat_id($CID,$cat_id)
{
	$CID = mysql_escape_string($CID);
	
	  $sql_get_sub_category = " SELECT * FROM Category where CID = '$CID'  and ID = '$cat_id'  and Active='Y' 
	  
	  order by display_order ASC
	  
	  ";
	$rs_sub_cat = mysql_query($sql_get_sub_category) ;
	
	if( mysql_num_rows($rs_sub_cat) >  0 )
	{
		$cat_detail = mysql_fetch_assoc($rs_sub_cat) ;
		$parent_id = $cat_detail['ParentID'] ;
		return $parent_id;
	}
	
}


 

function get_sub_gategory($CID,$parent_cat_id)
{
	$sub_category_list = array();
	
	$CID = mysql_escape_string($CID);
	$parent_cat_id = mysql_escape_string($parent_cat_id);
	 
	  $sql_get_sub_category = " SELECT * FROM Category where CID = '$CID'  and ParentID = '$parent_cat_id'  and Active='Y' 
	  
	  order by display_order ASC
	  
	  ";
	$rs_sub_cat = mysql_query($sql_get_sub_category) ;
	
	if( mysql_num_rows($rs_sub_cat) >  0 )
	{
		while($sub_cat_detail = mysql_fetch_assoc($rs_sub_cat) )
		{
			$cat_id =  $sub_cat_detail['ID'] ;
			
			$sub_category_list[$cat_id] = $sub_cat_detail['Name'] ;
		}
	}
	
	return $sub_category_list ;

}


function search_item($CID , $str )
{
	$item_list = array();	
	
	$CID = mysql_escape_string($CID);
	$str = mysql_escape_string($str);
	
	  $sql_get_item_list = " SELECT i.* FROM Items i
	inner join FormCategoryLink fcl ON i.ID = fcl.FormID
	 where
	  i.CID = '$CID' 
	  and i.status_item = 'Y'
	   
	  and ( 
	  		i.FormID like '%$str%' 			
            or i.FormID like '%$str%' 
			or i.item_title like '%$str%'
			 
                
          )
	 
	 
	  ";
	  
	  
	
	$rs_item_list = mysql_query($sql_get_item_list) ;
	
	if( mysql_num_rows($rs_item_list) >  0 )
	{
		while($item_detail = mysql_fetch_assoc($rs_item_list) )
		{	
			$item_list[] = $item_detail ;
		}
	}
	
	return $item_list ;
	
	
	
}

function get_item_list_by_category_id($CID , $cat_id)
{

	$item_list = array();	
	//pr_n($_POST);
	
	$CID = mysql_escape_string($CID);
	$cat_id = mysql_escape_string($cat_id);
	$UserID = $_SESSION['AID']; 
	
	$is_parent_category = is_parent_category($CID,$cat_id) ;
	if($is_parent_category==0)
	{
		// this is not parent category , we need to get parent category id
		 $parent_cat_id = get_category_detail_by_cat_id($CID,$cat_id);
		
	}
	
	
	
	if($cat_id==4)
	{
		
		/*$sql_get_item_list = " SELECT i.* FROM Items i
		inner join FormCategoryLink fcl ON i.ID = fcl.FormID
		 where
		  i.CID = '$CID' 
		  and i.status_item = 'Y'
		  and fcl.CategoryID = '$cat_id'
		  and i.UserID = $UserID
		 
		 
		  ";*/
		  
		  $sql_get_item_list = " 
		  
		  SELECT a.*
			FROM Items a
			JOIN FormCategoryLink b ON b.FormID=a.ID
			WHERE b.CategoryID IN (4)
			  AND a.CID=$CID
			  AND a.UserID='$UserID'
			  AND status_item='Y'
			GROUP BY a.ID
			ORDER BY a.FormID ASC
			LIMIT 500
		
		 
		 
		  ";
		
	}else if($cat_id==1935)
	{
		
		  $sql_get_item_list = " 
		  
		  SELECT a.*
			FROM Items a
			JOIN FormCategoryLink b ON b.FormID=a.ID
			WHERE b.CategoryID IN (1935)
			  AND a.CID=$CID
			  AND a.UserID='$UserID'
			  AND status_item='Y'
			GROUP BY a.ID
			ORDER BY a.FormID ASC
			LIMIT 500
		
		 
		 
		  ";
		
		  
	}else{
	 
		if($parent_cat_id>0)
		{
		
			/*$sql_get_item_list = " SELECT i.* FROM Items i
			inner join FormCategoryLink fcl ON i.ID = fcl.FormID
			 where
			  i.CID = '$CID' 
			  and i.status_item = 'Y'
			  and fcl.CategoryID IN ( '$cat_id' , '$parent_cat_id')
			
			 
			  ";*/
			  
			 $sql_get_item_list = " SELECT i.* ,  CAST(i.category_page_item_order AS SIGNED) as category_page_item_order FROM Items i
			inner join FormCategoryLink fcl ON i.ID = fcl.FormID
			 where
			  i.CID = '$CID' 
			  and i.status_item = 'Y'
			  and fcl.CategoryID IN ( '$cat_id' )
			order by category_page_item_order asc 
			 
			  ";
		
		}else{
		
			$sql_get_item_list = " SELECT i.* ,  CAST(i.category_page_item_order AS SIGNED) as category_page_item_order FROM Items i
			inner join FormCategoryLink fcl ON i.ID = fcl.FormID
			 where
			  i.CID = '$CID' 
			  and i.status_item = 'Y'
			  and fcl.CategoryID = '$cat_id'
			order by category_page_item_order asc 
			 
			  ";
		  
		 } 
		  
		
	}
	
	$rs_item_list = mysql_query($sql_get_item_list) ;
	
	if( mysql_num_rows($rs_item_list) >  0 )
	{
		while($item_detail = mysql_fetch_assoc($rs_item_list) )
		{	
			$item_list[] = $item_detail ;
		}
	}
	
	return $item_list ;


}

function get_item_detail_by_item_id($item_id)
{

   $CID =  $_SESSION['CID'];
	$item_detail = array();
	
	
	$item_id = mysql_escape_string($item_id);
	 
	   $sql_get_item_detail = " SELECT * FROM Items 
	 	where
	 	 ID = '$item_id'
		 and CID = '$CID'
	  ";
	  
	
	
	$rs_item_detail = mysql_query($sql_get_item_detail) ;
	
	if( mysql_num_rows($rs_item_detail) >  0 )
	{
		 $item_detail = mysql_fetch_assoc($rs_item_detail) ;
		 
	}
	
	return $item_detail ;


}





function pr_n($data)
{
	print_r("<pre>");
	print_r($data);
}


// below is example for calling this phone style function
function formatPhone_with_style_shop($ph,$style_type='')
{

	//start remove extra string from phone number
	
	preg_match_all('!\d+!', $ph, $matches);
   $ph_new = '';
	if(isset($matches[0]) and !empty($matches[0]))
	{
		foreach($matches[0] as $k=>$v)
		{
			$ph_new .= $v;
		}
	
	}
	
	$ph = $ph_new;
	
	//end remove extra string from phone number


	if($style_type==".")
	{
		if(strlen($ph)==7) return substr($ph,0,3) . "." . substr($ph,-4);
		elseif(strlen($ph)==10) return substr($ph,0,3) . "." . substr($ph,3,3) . "." . substr($ph,-4);
		else return $ph;
	
	}else if($style_type=="-")
	{
		if(strlen($ph)==7) return substr($ph,0,3) . "-" . substr($ph,-4);
		elseif(strlen($ph)==10) return substr($ph,0,3) . "-" . substr($ph,3,3) . "-" . substr($ph,-4);
		else return $ph;
	}else{
	
		if(strlen($ph)==7) return substr($ph,0,3) . "-" . substr($ph,-4);
		elseif(strlen($ph)==10) return substr($ph,0,3) . "-" . substr($ph,3,3) . "-" . substr($ph,-4);
		else return $ph;
	}
	
	
}

function parse_url_all($url){
    $url = substr($url,0,4)=='http'? $url: 'http://'.$url;
    $d = parse_url($url);
    $tmp = explode('.',$d['host']);
    $n = count($tmp);
    if ($n>=2){
        if ($n==4 || ($n==3 && strlen($tmp[($n-2)])<=3)){
            $d['domain'] = $tmp[($n-3)].".".$tmp[($n-2)].".".$tmp[($n-1)];
            $d['domainX'] = $tmp[($n-3)];
        } else {
            $d['domain'] = $tmp[($n-2)].".".$tmp[($n-1)];
            $d['domainX'] = $tmp[($n-2)];
        }
    }
    
	
	return $d;
}



include_once("shop_common_function_new.php");

?>	
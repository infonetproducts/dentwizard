<?php
include_once("../item_group_option_function.php");
include_once("../inventory_item_cat_allow_image_display.php");

if($action=="remove_to_cart_new")
{
	$item_detail = get_item_detail_by_item_id($item_id);
	$formid = $item_detail['FormID'] ;
	$_SESSION['Order'][$formid] = '';
	unset($_SESSION['Order'][$formid] );
	
}
 
if($action=="update_cart_new")
{
	
	sleep(1);
	
	unset($_POST['action']);
	
	//pr_n($_POST);
	//die;
	
	foreach($_POST as $item_id=>$item_qty)
	{
		//pr_n($_POST);
		
		$item_detail = get_item_detail_by_item_id($item_id);
		$formid = $item_detail['FormID'] ;		
		
		
		
		if($item_qty>0)
		{	
			$_SESSION['Order'][$formid] =  $item_qty ;
			
		}else{
		 
			$_SESSION['Order'][$formid] =  '' ;
			unset($_SESSION['Order'][$formid]);
		
		}
		 
	
	}
	
	$action="total_price_calculate" ;

}







if($action=="update_cart_by_item_id_new")
{
	sleep(1);
	
	$item_id = $_POST['item_id'] ;
	$cid = $_POST['cid'] ;
	$item_qty = $_POST['qty'] ;
	
	if($item_qty==0)
	{	
		unset($_SESSION['Order'][$item_id]);

	}else{
	
		$item_detail = get_item_detail_by_item_id($item_id);
	//	pr_n($item_detail);
	
		$k = $item_detail['FormID'] ;
	
		$_SESSION['Order'][$k] = $item_qty  ;
		$_SESSION['Order_type'][$k] = "N" ;
		$_SESSION['bccart'] = 0 ;
		
		$_POST['item_img'] = '';
		
		$_SESSION['Order_item_id'][$k] = $item_id  ;
		
		
		//$_SESSION['Order'][$item_id][] = $item_qty  ;
		 
 		
		if(isset( $_POST['item_img']) and  $_POST['item_img']!="")
		{
			$item_img = $_POST['item_img'] ;
		
		 
			if($item_img!="")
			{
				// $_SESSION['item_img_tmp'][$item_id][] = $item_img ;
				  
			}
		
		}
		
		
 		
	}


}


$total_price = 0 ;
if($action=="total_price_calculate_new")
{
 

	if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
	{
		foreach($_SESSION['Order'] as $formid=>$qty_sess)
		{
	
			$item_id_sess = $_SESSION['Order_item_id'][$formid];
			
			
			
			$item_details = get_item_detail_by_item_id($item_id_sess);
			 
			$item_detail = $item_details ;
			
			
			
	if($item_id_sess=="")
    {	// it is for custom order tab order
    
	//echo 'sss';
		$item = $formid;
		$itemdesc = $_SESSION['desc_custom'][$item];
        $item_title = $_SESSION['desc_custom'][$item];
        $price = $_SESSION['price_custom'][$item];
		
		$item_detail['item_type'] = 'standard';
		$item_detail['FormID'] = $formid ;
        $item_detail['item_title'] = $item_title;
		 $item_detail['Price'] = $price;
        $ff_item = $item;
		
		
		$item_details = $item_detail ;
    }	
		
			
			
			//pr_n($item_details);
			
			$item_id = $item_detail['ID'];
$item_type = $item_detail['item_type'];
$point_value = $item_detail['point_value'];
$inventory_image_allow = allow_inv_img_display($item_id);
$item_title = $item_detail['item_title'];

if($item_type=="custom" or $point_value!="" or $inventory_image_allow==1)
{
	$custom_notification = 1;
	
	
	 $arr_size =  count($_SESSION['custom_new'][$item_id]);
	
	for($i_new=0; $i_new<$arr_size; $i_new++)
	{
		
		 $qty_custom = $_SESSION['custom_new'][$item_id][$i_new]["Quantity_new"];
			
			
		$price = $item_detail['Price'];		
		$cart_item_total = $price * $qty_custom ;
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		$cart_item_total = number_format($cart_item_total,2);
		
		
		
			if( $qty_custom !="")
			{
				 $total_price += $item_details['Price'] * $qty_custom ;
			}
			
		}
			
			
	}else{
			
			
			 $total_price += $item_details['Price'] * $qty_sess ;
			 
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

		
		
	}else{
		echo "0.00";
	}
	
}
 



 


?>
<?php
ob_start();
include_once("setting.php");
 

if(isset($_POST['delivery_form']))
{

	header("location:checkout-payment-order-review.php");
	
	$_SESSION['delivery_form'] = $_POST ;
	
	unset($_POST['delivery_form']);
	die;
	
}else{
	
	if(isset($_SESSION['delivery_form']))
	{
		$_POST = $_SESSION['delivery_form'] ;
	}
	
}

$delivery_method = $_SESSION['delivery_form']['delivery_method'];
$odt = date("m/d/Y g:ia");

$last_four_digit_new = substr($_POST["CardNumber"],-4);

/*Payment Method: Credit Card (last 4 digits: 2424) <br/><br/>
*/


$msg = "

Thank you for your order. <br/><br/>

The details of your order are as follows: <br/><br/>

<strong>Order Number:</strong> $order_id  <br/>
<strong>Order Date:</strong> $odt <br/>
<strong>Payment Method:</strong> Credit Card <br/><br/> ";

$cart_total = '';

 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
	{
				
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_sess)
	{
	
		$item_detail = get_item_detail_by_item_id($item_id_sess);
				
				//pr_n($item_details);
				
				
		$item_image  = ''; 
		if($item_detail['ImageFile']!="")
		{
			$item_image = "pdf/$CID/".$item_detail['ImageFile'];
		}
		
		$item_id = $item_detail['ID'] ;
		//echo $_SESSION['Order'][$item_id]['size'];
		
		$size_lable = "";
		
		if(isset($_SESSION['size_item'][$item_id]))
		{
			$size = $_SESSION['size_item'][$item_id] ;
			$size = trim($size);
			if($size!="" and $size!="Please Select")
			{
				$item_detail['Price']  = get_item_price_by_size_new($size,$item_id);
				
				$size_lable = " <strong>Size:</strong> $size		 <br/> ";
				  
			}	
		}
		
		
		
		$price = $item_detail['Price'];		
		$cart_item_total = $price * $qty_sess ;
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		$cart_item_total = number_format($cart_item_total,2);
		
		
$msg .= "
<strong>Item ID:</strong> $item_detail[FormID]		<br/>
<strong>Item Title:</strong> $item_detail[item_title] <br/>
<strong>Quantity:</strong> $qty_sess <br/>
$size_lable
<strong>Price:</strong> $$price <br/><br/>";

}

}


$delivery_charge = '';
$Delivery_Method_lable = '';
	
	if($delivery_method=="3_to_5_days")
	{
		$delivery_charge = 0 ;
		
		$Delivery_Method_lable = "Pickup at School on ".second_friday_from_current_date();
		
	}
	
	if($delivery_method=="1_to_2_days")
	{
		$Delivery_Method_lable = "Pickup at Leader Graphics ".second_friday_from_current_date();
		$delivery_charge = 0 ;
	}
	
	if($delivery_method=="7_to_10_days")
	{
		$Delivery_Method_lable = "Standard Shipping" ;
		$delivery_charge = 10 ;
	}
	
	$cart_total = number_format($cart_total,2);
	
	
	
	
$sales_tax	= 0 ;
	
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
		 $sales_tax =  0 ; // 5.94 , it is salex tax on PA state zip code.
		
		 
	}	
}

$order_total = "";
$order_total = $cart_total + $delivery_charge + $sales_tax ;
$order_total = number_format($order_total,2);	
$sales_tax = number_format($sales_tax,2);	

$delivery_charge = number_format($delivery_charge,2);

if($delivery_charge>0)
{
	$shipping_handling_lable = "<strong>Shipping & Handling:</strong> $$delivery_charge <br/>";
}	



$msg .= "
<strong>Delivery Method:</strong> $Delivery_Method_lable <br/> 
$shipping_handling_lable <br/>

<strong>Order Total:</strong> $$order_total <br/><br/>

<strong>CUSTOMER INFORMATION</strong> <br/><br/>

<strong>Ordered By:</strong> $order_place_by <br/>
<strong>Email:</strong> $_POST[shipping_email] <br/>
<strong>Phone:</strong> $_POST[shipping_phone]	 <br/><br/>

<strong>SHIPPING ADDRESS </strong><br/>

$_POST[shipping_address_1] <br/>
$_POST[shipping_city], $_POST[shipping_state] $_POST[shipping_zip] <br/>

";

echo $msg ; 

?>


 



 
		  
 
 
 
  
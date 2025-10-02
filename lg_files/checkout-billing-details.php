<?php
ob_start();
include("setting.php");
 
$mycolor=$maincolor; //'#abe8fe';
include_once("include/start.php");
include_once("../item_group_option_function.php");
include_once("../inventory_item_cat_allow_image_display.php");


include_once("taxjar/common_function_taxjar.php");




/*print_r("<pre>");
print_r($_SESSION);
*/
// start code for check is standard order
$is_standard_order = 1;
if(isset($_SESSION['custom_tab_order']) and $_SESSION['custom_tab_order']==1)
{
	$is_standard_order = 0;
}

if(isset($_SESSION['custom_new']) and !empty($_SESSION['custom_new']))
{
	$is_standard_order = 0;
}
//echo $is_standard_order;
// end code for check is standard order

$AID = $_SESSION['AID'];
$CID = $_SESSION['CID'];
$sql_view_only = "select is_view_only , BudgetBalance from Users where CID=$CID and ID='$AID' " ; 
list($is_view_only,$BudgetBalance)=@mysql_fetch_row(mysql_query($sql_view_only));	

//echo $BudgetBalance;

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';

$store_locatoin_detail = array();
if(isset($_SESSION['store_location_id']) and $_SESSION['store_location_id']>0)
{
 $store_location_id = $_SESSION['store_location_id'];
 
 	$AID = $_SESSION['AID'];
	$CID = $_SESSION['CID'];
				

	 
	 
	   $sql_check_district_manager = "select sl.* , sdms.aid , sdm.district_manager_first_name , sdm.district_manager_last_name , sdm.email from store_district_manager_shops sdms
				 inner join store_location sl ON sl.Old_id = sdms.old_store_id 
				 inner join store_district_manager sdm ON sdm.aid= sdms.aid
				  where sdms.cid = '$CID'
				 and sdms.aid = '$AID'
				  and sl.id = '$store_location_id'
				 ";
	 
	 
	 $rs_district_manager = mysql_query($sql_check_district_manager);
	 if(mysql_num_rows($rs_district_manager)>0)
	 {
	 	$store_locatoin_detail = mysql_fetch_assoc($rs_district_manager);
		//pr($store_locatoin_detail);
	 }
 
}


if(!$uid)$uid=$_SESSION['AID'];

$server = $_SERVER['HTTP_HOST'];

if($action=='gds'){
	$ss=mysql_real_escape_string(trim($_REQUEST['ss']));
	if(!$ss) exit;
	$rs=mysql_query("select ID,DealerCode,Name,City,State from JDERecipients where DealerCode like '%$ss%' or Name like '%$ss%' limit 10");
	echo <<<EOM
<table align="center" style="min-width:500px;border:1px solid black;">
<tr><td colspan="5" align="right"><a href="javascript:document.getElementById('dealerpicker').innerHTML=''"><img border="0" src="/images/cross.png" /></a></td></tr>
EOM;
	while($row=@mysql_fetch_assoc($rs)){
		echo <<<EOM
<tr><td nowrap><a href="javascript:getdealer('$row[ID]')">$row[DealerCode] - $row[Name]</a></td><td>$row[City], $row[State]</td></tr>
EOM;
	}
	echo "</table>";
	exit;
}

if($action=='gd' && is_numeric($_REQUEST['ss'])){ // get dealer
	$ss = mysql_real_escape_string(trim($_REQUEST['ss']));
	$row = @mysql_fetch_row(mysql_query("select Name,Address1,Address2,City,State,Zip,DealerCode from JDERecipients where ID='$ss'"));
	if(!$row) echo "Error: Dealer Not Found!";
	else {
		foreach($row as $val){
			$val = str_replace(",","",$val);
		}
		@reset($row);
		echo implode(",", $row);
	}
	exit;
}


if($action=='shipping_address'){ // get dealer
	
	$ss = $_REQUEST['ss'];
	
	if($ss=="")
	{
		$AID = $_SESSION["AID"];
		$row = @mysql_fetch_row(mysql_query("select ShipToName,ShipToDept,Address1,Address2,City,State,Zip,country  from Users where ID='$AID'")) or die( mysql_error() );
		
	}else{
	
	$ss = mysql_real_escape_string(trim($_REQUEST['ss']));
	
	
	
	$row = @mysql_fetch_row(mysql_query("select ShipToName,ShipToDept,Address1,Address2,City,State,Zip,country , name_of_shipping_address from multiple_shipping_address where ID='$ss'"));
	
	}
	
	/*print_r("<pre>");
	print_r($row);
	
	die;
	*/
	
		 
	
		foreach($row as $val)
		{
			$val = str_replace(",","",$val);
		}
		@reset($row);
		echo implode(",", $row);
	
	exit;
}

$error = array();

if($action=='submit'){
 

	$notes = $_POST['Notes'];
	foreach($_POST as $k=>$v){
		
		if($k=="payment_method")
		{
			continue;
		}
		
		if (strpos($k, 'split_payment_method') !== false) 
		{
			continue;
		}
		
		if (strpos($k, 'payment_method') !== false) 
		{
    		//found 
			
			 
			
			if($v=="")
			{
			/*	include("htmlhead.php");
		include("menu.php");
		echo <<<EOM
<div style="text-align:center;padding:20px; font-size:12pt">
Error: Please <a href="javascript:history.go(-1)">go back</a> and fill in required fields.<br />
They are marked with red stars.<br /><br />Thank you! 
</div>
EOM;
		include("htmlfoot.php");
		exit;*/
		
		$error["payment_method"] = "fill in required fields. They are marked with red stars. ";
		
		
			}
		}
		
		
		if(!is_array($v)) $_POST[$k] = mysql_real_escape_string(trim($v));
			
	}

	
	/*print_r("<pre>");
	print_r($_POST);
	die;*/
	
//	list($login)=@mysql_fetch_row(mysql_query("select Login from Users where ID='$AID'"));
	$oid = date("md-His") . "-$uid";
	
	 
	
	if(!$_POST['Name']){
		/*include("htmlhead.php");
		include("menu.php");
		echo <<<EOM
<div style="text-align:center;padding:20px; font-size:12pt">
Error: Please <a href="javascript:history.go(-1)">go back</a> and fill in required fields.<br />
They are marked with red stars.<br /><br />Thank you!
</div>
EOM;
		include("htmlfoot.php");
		exit;*/
		
		$error["Name"] = "Please enter name ";
		
		
	}
	
	


	
	if(isset($_POST['Phone']) and $_POST['Phone']==""){
		
		/*include("htmlhead.php");
		include("menu.php");
		echo <<<EOM
<div style="text-align:center;padding:20px; font-size:12pt">
Error: Please <a href="javascript:history.go(-1)">go back</a> and fill in required fields.<br />
They are marked with red stars.<br /><br />Thank you!
</div>
EOM;
		include("htmlfoot.php");
		exit;*/
		
		
		$error["Phone"] = "Please enter phone ";
		
	}
	

	
	/*if(!isset($_POST['payment_method']) and isset($_POST['is_exist_payment_method']) ){
		
		include("htmlhead.php");
		include("menu.php");
		echo <<<EOM
<div style="text-align:center;padding:20px; font-size:12pt">
Error: Please <a href="javascript:history.go(-1)">go back</a> and fill in required fields.<br />
They are marked with red stars.<br /><br />Thank you! 
</div>
EOM;
		include("htmlfoot.php");
		exit;
	}*/
	
	
	if($_POST['ShipToName']=="")
	{
		$error["ShipToName"] = "Please enter shop to name ";
	
	}
	
	if($_POST['Company']=="")
	{
		//$error["Company"] = "Please enter company name ";
	
	}
	
	/*pr($error);
	die;*/
	
	if($_POST['ShipToName']=="" and $_POST['Company']==""){
		/*include("htmlhead.php");
		include("menu.php");
		echo <<<EOM
<div style="text-align:center;padding:20px; font-size:12pt">
Error: Please <a href="javascript:history.go(-1)">go back</a> and fill in required fields.<br />
They are marked with red stars.<br /><br />Thank you!
</div>
EOM;
		include("htmlfoot.php");
		exit;*/
		
		
		
		
	}
	
	if(!isset($_POST['confirm']) and isset($_POST['payment_method']) )
	{
		/*include("htmlhead.php");
		include("menu.php");*/
		// Error: You must agree to the mock-up disclaimer. Please check the box at the bottom of the previous page to agree.
/*	echo <<<EOM
<div style="text-align:left;padding:20px; font-size:12pt">
Error: You must agree to the mock-up disclaimer. Please check the box at the bottom of the 
<a href="javascript:history.go(-1)">previous page </a> to agree.<br />
<br />Thank you!
</div>
EOM;

		include("htmlfoot.php");
		exit;
		*/
		
		$error["Company"] = "You must agree to the mock-up disclaimer. Please check the box at the bottom of the page";
		
		
	}
	
	
	
	
	
if(empty($error))
{

$_SESSION['delivery_form']['shipping_zip'] = $_POST['Zip'];
$_SESSION['delivery_form']['shipping_state'] = $_POST['State'];
$_SESSION['delivery_form']['shipping_zip'] = $_POST['Zip'];


if(!$uid)$uid=$_SESSION['AID'];
$order_id = date("md-His") . "-$uid";

$sale_tax = calculate_sale_tax();

$amount_to_collect = 0 ;
if(isset($sale_tax['tax']['amount_to_collect']))
{
	$amount_to_collect = $sale_tax['tax']['amount_to_collect'] ;
	
	create_order_transaction_api($sale_tax,$order_id,$amount_to_collect);
} 


//die;
			 

	
	$name_of_shipping_address='';
	if(isset($_POST['save_address']))
	{
		$name_of_shipping_address= $_POST['name_of_shipping_address'];
	}
	
	$payment_method_bc = '';
	if(isset($_POST['payment_method']) and $_POST['payment_method']!="")
	{
			$payment_method_bc = $_POST['payment_method'] ;
	}
	
	$requestor = '';
	$cost_center = '';
	$tax_type = '';
	
	$requestor_email = "";
	
	
	if(isset($_POST['requestor_email']) and $_POST['requestor_email']!="")
	{
			$requestor_email = $_POST['requestor_email'] ;
	}
	
	if(isset($_POST['requestor']) and $_POST['requestor']!="")
	{
			$requestor = $_POST['requestor'] ;
	}
	
	if(isset($_POST['cost_center']) and $_POST['cost_center']!="")
	{
			$cost_center = $_POST['cost_center'] ;
	}
	
	if(isset($_POST['tax_type']) and $_POST['tax_type']!="")
	{
			$tax_type = $_POST['tax_type'] ;
	}
	
	
		
	
	
	/*foreach($_SESSION['Order'] as $form_id=>$o_qty)
	{
		$input_name = "payment_method_".$form_id;
		$input_name_split = "split_payment_method_".$form_id;
		
		if(isset($_POST[$input_name]))
		{
			$_SESSION['payment_method'][$form_id] = $_POST[$input_name] ;
			$payment_method_bc = $_POST[$input_name] ;
			
			
			if($_POST[$input_name]=="Split")
			{
				$_SESSION['split_payment_method'][$form_id] = mysql_escape_string( $_POST[$input_name_split] ) ;
			}else{
				$_SESSION['split_payment_method'][$form_id] = '';
				unset($_SESSION['split_payment_method'][$form_id]);
			}
			
			
		}
		
	}*/


/*	print_r("<pre>");
	print_r($_SESSION['split_payment_method']);
	
	die;*/
	
	

	$con = array(
	
	'Email'=>$_POST['Email'],
	'Name'=>$_POST['Name'],
	'Company'=>$_POST['Company'],
	'ShipToName'=>$_POST['ShipToName'],
	'Phone'=>$_POST['Phone'],
	'Address1'=>$_POST['Address1'],
	'Address2'=>$_POST['Address2'],
	'City'=>$_POST['City'],
	'State'=>$_POST['State'],
	'Zip'=>$_POST['Zip'],
	'country'=>$_POST['country'],
	'name_of_shipping_address' => $name_of_shipping_address,
	'custom_desc' => $_POST['custom_desc_final'],
	'payment_method' => $payment_method_bc,
	
	'requestor' => $requestor,
	'requestor_email' => $requestor_email,
	'cost_center' => $cost_center,
	'tax_type' => $tax_type,
	'dearler_name_with_id' => $_POST['dearler_name_with_id'],
	
	'created_from_order_type' => 'Order_Created_By_User'
	
	
		
		);
		
	
		
	
	if(isset($_POST['save_address']))
	{	
		foreach($_POST as $k=>$v)
		{
		$_POST[$k] = mysql_real_escape_string(trim($v));
		}
		
		$sql_add_shipping = "insert into multiple_shipping_address set 				
					ShipToName='$_POST[ShipToName]',
					ShipToDept='$_POST[Company]',
					Phone='$_POST[Phone]',
					Address1='$_POST[Address1]',
					Address2='$_POST[Address2]',
					City='$_POST[City]',
					State='$_POST[State]',
					Zip='$_POST[Zip]' , 
					country='$_POST[country]' ,
					user_id = '$AID',
					CID = '$CID',
					name_of_shipping_address = '$_POST[name_of_shipping_address]' 
					";
		mysql_query($sql_add_shipping) or die ( mysql_error() );
	}
		
	$user = @mysql_fetch_assoc(mysql_query("select * from Users where ID='$uid'"));
	if($_POST['BillCode']) $bc=$_POST['BillCode'];
	else $bc=$user['BillCode'];

	// check if order is business card order (check first item if is businesscard, then order is business card order)
	// and use business card billcode (37) instead of another.
	list($iid,$iiq)=@each($_SESSION['Order']); // get first item
	$iid=mysql_real_escape_string($iid);
	list($itemid)=@mysql_fetch_row(mysql_query("select ID from Items where FormID = '$iid'")); // get item record id
	list($isbc)=@mysql_fetch_row(mysql_query("select 1 from FormCategoryLink where FormID=$itemid and CategoryID='$bccat'")); // check if item carries business card category
	if($isbc) $bc = 37; #echo " THIS IS A BUSINESS CARD ORDER.";

	if(!$bc) $bc=1;
	@reset($_SESSION['Order']);
	
	
	//custom_desc_final
	if(isset($_SESSION['custom_tab_order']) and $_SESSION['custom_tab_order'] ==1)
	{
		if(isset($_POST["custom_desc_final"]))
		{
			$_SESSION['custom_desc_final'] = $_POST["custom_desc_final"];
			$_SESSION['custom_item_due_date_final'] = $_POST["custom_item_due_date_final"];
			
		}
	}
	
	
	
	//pr($_SESSION);
	//die;
	
	include_once("create_order.php");
	
	
	
	// this function we are using for business card order only
	//createOrder($oid,$uid,$bc,$con,$_POST['Notes'],$_SESSION['Order']); // $_SESSION['Order'] is array of item ids (FormID)
	
	
	unset($_SESSION['Order']);
	unset($_SESSION['custom_desc_final']);
	unset($_SESSION['custom_item_due_date_final']);	
	unset($_SESSION['file']);
	unset($_SESSION['file_resolution']);
	unset($_SESSION['link']);
	unset($_SESSION['link_resolution']);
	unset($_SESSION['desc_custom']);
	unset($_SESSION['custom_tab_order']);
	unset($_SESSION['qty']);
	unset($_SESSION['item_due_date']);
	unset($_SESSION['item_id_custom_order']);
	unset($_SESSION['Order_type']);
	unset($_SESSION['description']);
	
	unset($_SESSION['payment_method']);
	unset($_SESSION['split_payment_method']);
	
	unset($_SESSION['bccart']);
	
	header("Location: order_confirmation.php?a=confirm&oid=$order_id");
	exit;
	
}
	
}
 
 
 

?>
<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
<!-- Titlebar
================================================== -->

<section class="titlebar">
  <div class="container">
    <div class="sixteen columns">
      <h2>Checkout</h2>
      <nav id="breadcrumbs">
        <ul>
          <li><a href="#">Home</a></li>
          <li><a href="#">Shop</a></li>
          <li><a href="#">Checkout</a></li>
          <li>Order Details</li>
        </ul>
      </nav>
    </div>
  </div>
</section>
<!-- Content
================================================== -->
<!-- Container -->
<div class="container">
  
  <!-- Billing Details / Enc -->
  <!-- Checkout Cart -->
  
  <?php
  if(isset($error) and !empty($error))
  {
  
  
  
  ?>
  
  <div  align="center" style="color:red;">
	<?php
	foreach($error as $error_message)
	{
		?>
        <div><?php echo $error_message ; ?></div>
        <?php
	}
	?>
</div>
  <?php
  }
  ?>
  
<div  id="is_backorder" align="center" style="color:red;  display:none;">
This orders contains some items which are on backorder. They are indicated with a red asterisk * <br/>
</div>

<div  id="is_custom_noficiation" align="center" style="color:red;  display:none;">
Please Note: <span  style="color:black;">The Points Plus Value is shown in parathesis next to the price for each item.</span></div>

  
  
  <div class="eight columns">
    <div class="checkout-section cart">Shopping Cart</div>
    <!-- Cart -->
   <table class="checkout cart-table responsive-table">
      <?php
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			 ?>
      <tr>
        <th class="hide-on-mobile">Item</th>
        <th></th>
		
		 
		
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
      </tr>
      <?php 
	$cart_total = 0;
	$tmp_custom_price_items = 0 ;
	$total_sale_tax = 0  ;
	$customization_total_price = 0 ;
	
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
				
				//pr_n($item_details);
		
		if(isset($_SESSION['gift_card_is_gift_card_item'][$item_detail['ID']][$set_key]))
		{
			 $item_detail['Price'] = $_SESSION['gift_card_gift_price'][$item_detail['ID']][$set_key] ;
		}
				
				
		$item_image  = ''; 
		if($item_detail['ImageFile']!="")
		{
			$item_image = "pdf/$CID/".$item_detail['ImageFile'];
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
				$item_detail['Price'] = $price ;*/
				
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
		
		
		
		
		$cart_item_total = $price * $qty_sess ;
		
		$single_item_total = $price * $qty_sess ;
		
		
		
		//$cart_item_total = $price * $qty_sess ;
		//$single_item_total = $price * $qty_sess ;
		
		
		
		
		
		$sale_tax = $item_detail['sale_tax'];	
		$is_apply_sale_tax = $item_detail['is_apply_sale_tax'];	
		
		$sale_tax_price = 0 ;
		if($sale_tax>0 and $is_apply_sale_tax==1)
		{
			//$sale_tax_price = 	( $cart_item_total * $sale_tax ) / 100 ;
			$sale_tax_price = 	( $single_item_total * $sale_tax ) / 100 ;
			//$sale_tax_price = 	( $item_detail['Price'] * $qty_sess  ) / 100 ;
			
			$total_sale_tax += $sale_tax_price ; 
			
			//$cart_item_total = $cart_item_total + $sale_tax_price ;
		}
		//echo $sale_tax_price;
		
		
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		
		$cart_item_total = number_format($cart_item_total,2);
		
		
		if(isset($_SESSION['item_img_tmp'][$item_detail['ID']][$set_key]))
		{
				$item_image = $_SESSION['item_img_tmp'][$item_detail['ID']][$set_key] ;
		}
		
		
		
	?>
      <tr>
        <td class="hide-on-mobile"><img style="width:80px; height:80px;"src="<?php echo $item_image;?>" alt=""/></td>
       
	   <?php
				$gift_price_display = '';
				if(isset($_SESSION['gift_card_is_gift_card_item'][$item_detail['ID']][$set_key]))
				{
					$gift_price_display = "$".number_format($item_detail['Price'])." ";
				}
				?>
	   
	    <td class="cart-title"><a href="#"><?php echo $gift_price_display ; echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></a>
		
			<br/><?php echo $item_detail['FormID'];?>
				
				
				
				<?php
				
				if($sale_tax>0 and $is_apply_sale_tax==1)
				{
					//echo "<br/><strong>Sale Tax will apply on this item ".number_format($sale_tax,2)."%</strong>";
					echo "<br/><strong>A ".number_format($sale_tax,2)."% sales tax has been applied to this item.</strong>";
				}
			 
				
		if(isset($_SESSION['size_item'][$item_id][$set_key]))
		{
			$group_name = get_item_price_group_name($item_id) ;
			
			echo "<br/>$group_name: ";
			echo  $_SESSION['size_item'][$item_id][$set_key] ;
			
		}
				?>
				
				 
				
				<?php
					if(isset($_SESSION['color_item'][$item_id][$set_key]))
					{
						echo "<br/>Color: ";
						echo  $_SESSION['color_item'][$item_id][$set_key] ;
						
						
					}
				?>
				
				<?php
				
				
				
					if(isset($_SESSION['artwork_logo_item'][$item_id][$set_key]))
					{
						echo "<br/>Logo: ";
						echo  $_SESSION['artwork_logo_item'][$item_id][$set_key] ;
						
					}
					
					//print_r("<pre>");
					//print_r($_SESSION);
					
				?>
				
				
				
				<?php
				//print_r("<pre>");
				//print_r($_SESSION);
				
				
				
					if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
					{
					
						
						$custom_name_price = $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Name ($$custom_name_price): ";
						echo  $_SESSION['custom_name_tmp'][$item_id][$set_key] ;
						
						$tmp_custom_price_items += $custom_name_price ;
						
						 $customization_total_price += $custom_name_price ;
						
						if($_SESSION['CID']==120)
						{
							
							
							$total_sale_tax += ($custom_name_price * 7.25) / 100 ;
						}
						
					}
				?>
				
				<?php
					if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
					{
						$custom_number_price = $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Number ($$custom_number_price): ";
						echo  $_SESSION['custom_number_tmp'][$item_id][$set_key] ;
						
						$tmp_custom_price_items += $custom_number_price ;
						
						$customization_total_price += $custom_number_price ;
						
						if($_SESSION['CID']==120)
						{
							$total_sale_tax += ($custom_number_price * 7.25) / 100 ;
						}
						
						
					}
				?>
				
				
				
				
		
		</td>
        
		
		
			 
				
			
				 
				
		
		
		
		<td>$<?php echo $price;?></td>
        <td class="qty-checkout"><?php echo $qty_sess;?></td>
        <td class="cart-total">$<?php echo $cart_item_total;?></td>
      </tr>
      <?php
		}
		
		}
	
	?>
      <?php
	}?>
    </table>
    <!-- Apply Coupon Code / Buttons -->
    <table class="cart-table bottom">
     

<?php
$cart_total = number_format($cart_total,2);
 $sales_tax	= 0 ;
 $delivery_charge = '';

 
$order_total = "";
	
	$order_total = $cart_total + $delivery_charge + $sales_tax + $tmp_custom_price_items ;
	$order_total = number_format($order_total,2);	
	
if(!$uid)$uid=$_SESSION['AID'];	

$user=@mysql_fetch_assoc(mysql_query("select * from Users where ID='$uid'"));

$user[Name] =  stripslashes($user[Name]);

$user[Name] = preg_replace('/\\\\/', '', $user[Name]);
if(!$user['ShipToName'] && $user['Name']) $user['ShipToName'] = $user['Name'];
	
?>        
		<!-- <tr>
		<th class="checkout-totals"> <div class="checkout-subtotal"> Subtotal: <span>$<?php echo $cart_total;?></span> </div></th>

      </tr>		-->   
		 		
				
		 <tr>
		<th class="checkout-totals">
        
        <?php
        if(isset($custom_notification) and $custom_notification==1)
		{ 
		
		
		if($grandtot_point_plus=="")
		{
			$grandtot_point_plus = 0 ;
		}
		
	?>
    
      <div class="checkout-subtotal">Order Total: <strong class="order_total_td">$<strong class="ajax_total_price_new"><?php echo $order_total." ($grandtot_point_plus)";?></strong></strong></div>
    
    <?php
	}else{
	?>
    
        
        <div class="checkout-subtotal">Order Total: <strong class="order_total_td">$<strong class="ajax_total_price"><?php echo $order_total;?></strong></strong></div>
		
		 <?php
		 }
		 ?>
		
		</th>
        
		 </tr>
		 
		 
	 
		
		
    </table>
  </div>
  
  <div class="eight columns">
    <!-- Billing Details Content -->
    <div class="checkout-section active" id="order_details"><span>1</span> Shipping Details</div>
	
	<form method="post"   name="cart" id="billing_details">
	
	<input type="hidden" name="a" value="submit" />
	
    <div class="checkout-content">
       
	  
	  <span id="shipping_address_different">
	  
	  	  
		 
		 <input type="hidden" name="shipping_country" id="shipping_country" value="US" />
	
     
      <div class="half first">
        <label>Email Adress: <abbr>*</abbr></label>
        <input type="text" size="33" name="Email" value="<?php echo $user[Email];?>"  required />
      </div>
      
      <div class="half first">
        <label>Order Placed By: <abbr>*</abbr></label>
        <input type="text" size="33" name="Name" value="<?php echo $user[Name];?>" required />
      </div> 
     
     
      <div class="first">
        <label>Order Description: <abbr></abbr></label>
         <input type="text" size="33" id="" name="custom_desc_final" />
      </div> 
      
      <br/>
      
	  <?php 
	  if($_SESSION['CID']==42)
	  {
	  ?>
       <div class="half first">
        <label>Find Dealer: <abbr>*</abbr></label>
        	<input type="text" size="12" id="ddsrch" onkeyup="checkdealer()" />
			<div id="dealerpicker"></div>
      </div> 
      
      <br/>
      <?php
	  }
	  ?>
      
     
     <span><label><span><label>Select Shipping Address:</label>
							
				
                      
                      <select class="select-field-2 w-select"  name="shipping_address" id="shipping_address" onchange="return shipping_address_change(this.value)">

<option value="">Primary Shipping Address</option>

  <?php
  
$ship_rs =  mysql_query("select * from multiple_shipping_address where user_id = '$AID' and CID = '$CID' ");
if( mysql_num_rows( $ship_rs) > 0 )
{  
  while($ship_detail = @mysql_fetch_assoc($ship_rs) )
  {
  
  ?>
  
  <option value="<?php echo $ship_detail["ID"];?>"><?php echo $ship_detail["name_of_shipping_address"];?></option>
  
 
  
  <?php
  }
} 
  ?>


</select>


<script>

// ShipToName,ShipToDept,Address1,Address2,City,State,Zip,country 

function shipping_address_change(str){
	//if(str=='')return;
	var x=getXMLObj();
	x.open('get','checkout-billing-details.php?a=shipping_address&ss='+str, true);
	x.onreadystatechange=function(){if(x.readyState==4){
		if(!/,/.test(x.responseText)) { alert(x.responseText); }
		else {
			//document.getElementById('dealerpicker').innerHTML='';
			var tar=x.responseText.split(',');
			var f=document.cart;
			
			var ship_to_name = tar[0];
			ship_to_name = ship_to_name.trim();
			
            f.ShipToName.value = ship_to_name ;
			f.Company.value = tar[1];		
			f.Address1.value = tar[2];
			f.Address2.value = tar[3];
			f.City.value = tar[4];
			f.State.value = tar[5];
			f.Zip.value = tar[6];
            
            if( tar[7]=="")
            {
            	 tar[7] = "United States";
            }
            
             var opts = $('#country')[0].options;
for(var a in opts) { if(opts[a].value == tar[7]) { $('#country')[0].selectedIndex = a; break; } }
 
            if(tar[8]!=undefined){
            	f.name_of_shipping_address.value = tar[8];
            }else{
            	f.name_of_shipping_address.value = '';
            }
		
		}
	}};
	x.send(null);
}
</script>
							
							
						<input type="hidden" name="dearler_name_with_id" id="dearler_name_with_id" value="" />
            
            				
							</span> 
     
      
	  </span>
	  
    
    	 <div class="half first">
        <label>Ship To Name <abbr>*</abbr></label>
       
        
        <input required  type="text" size="33" id="ShipToName" name="ShipToName" value="<?php echo $user[ShipToName];?>" />
     
      </div> 
      
          <input  type="hidden"  size="33" name="Company" id="Company" value="" />
      
     <!--  <div class="half first">
        <label>Ship To Company: <abbr>*</abbr></label>
        
        
        <input required type="text"  size="33" name="Company" id="Company" value="<?php echo $user[ShipToDept];?>" />
        
     
      </div> -->
      
        <?php
		if(!empty($store_locatoin_detail))
		{
			$user[Address1] = $store_locatoin_detail['address'];
			$user[City] = $store_locatoin_detail['city'];
			$user[State] = $store_locatoin_detail['state'];
			$user[Zip] = $store_locatoin_detail['zip'];
		}
		?>
      
       <div class="half first">
        <label>Address 1: <abbr>*</abbr></label>
        <input type="text" required size="33" name="Address1" value="<?php echo $user[Address1];?>" id="Address1" />
     
      </div> 
      
      
      
      
       <div class="half first">
        <label>Address 2: </label>
        <input type="text"  size="33" name="Address2" value="<?php echo $user[Address2];?>" id="Address2" />
     
      </div> 
      
       <div class="half first">
        <label>City: <abbr>*</abbr></label>
        <input type="text"  required size="33" name="City" value="<?php echo $user[City];?>"  id="City"/>
     
      </div> 
      
        <br/>
      
       <div class="half first">
        <label>State/Province/Region: <abbr>*</abbr></label>
       <input type="text" required size="2" name="State" id="State" value="<?php echo  $user[State];?>" />
     
      </div> 
      
        
      
       <div class="half first">
        <label>Zip/Postal Code: <abbr>*</abbr></label>
       <input type="text" required name="Zip" id="Zip" value="<?php echo  $user[Zip];?>" />
     
      </div> 
      
       <div class="half first">
        <label>Country: <abbr>*</abbr></label>
      
        <select class="select-field-2 w-select" name="country" id="country">
        <option  value="Afghanistan">Afghanistan</option>
        <option  value="American Samoa">American Samoa</option>
        <option  value="Andorra">Andorra</option>
        <option  value="Angola">Angola</option>
        <option  value="Anguilla">Anguilla</option>
        <option  value="Antarctica">Antarctica</option>
        <option  value="Antigua And Barbuda">Antigua And Barbuda</option>
        <option  value="Argentina">Argentina</option>
        <option  value="Armenia">Armenia</option>
        <option  value="Aruba">Aruba</option>
        <option  value="Australia">Australia</option>
        <option  value="Austria">Austria</option>
        <option  value="Azerbaijan">Azerbaijan</option>
        <option  value="Bahamas">Bahamas</option>
        <option  value="Bahrain">Bahrain</option>
        <option  value="Bangladesh">Bangladesh</option>
        <option  value="Barbados">Barbados</option>
        <option  value="Belarus">Belarus</option>
        <option  value="Belgium">Belgium</option>
        <option  value="Belize">Belize</option>
        <option  value="Benin">Benin</option>
        <option  value="Bhutan">Bhutan</option>
        <option  value="Bolivia">Bolivia</option>
        <option  value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
        <option  value="Botswana">Botswana</option>
        <option  value="Bouvet Island">Bouvet Island</option>
        <option  value="Brazil">Brazil</option>
        <option  value="British Ocean Territory">British Ocean Territory</option>
        <option  value="Brunei">Brunei</option>
        <option  value="Bulgaria">Bulgaria</option>
        <option  value="Burkina Faso">Burkina Faso</option>
        <option  value="Burundi">Burundi</option>
        <option  value="Cambodia">Cambodia</option>
        <option  value="Cameroon">Cameroon</option>
        <option  value="Canada">Canada</option>
        <option  value="Cape Verde">Cape Verde</option>
        <option  value="Cayman Islands">Cayman Islands</option>
        <option  value="Central African Republic">Central African Republic</option>
        <option  value="Chad">Chad</option>
        <option  value="Channel Islands">Channel Islands</option>
        <option  value="Chile">Chile</option>
        <option  value="China">China</option>
        <option  value="hristmas Island">Christmas Island</option>
        <option  value="Cocos (Keeling)Islands">Cocos (Keeling)Islands</option>
        <option  value="Colombia">Colombia</option>
        <option  value="Comoros">Comoros</option>
        <option  value="Congo">Congo</option>
        <option  value="Cook Islands">Cook Islands</option>
        <option  value="Costa Rica">Costa Rica</option>
        <option  value="Croatia">Croatia</option>
        <option  value="Cuba">Cuba</option>
        <option  value="Cyprus">Cyprus</option>
        <option  value="Czech Republic">Czech Republic</option>
        <option  value="Dem Rep of Congo(Zaire)">Dem Rep of Congo(Zaire)</option>
        <option  value="Denmark">Denmark</option>
        <option  value="Djibouti">Djibouti</option>
        <option  value="Dominica">Dominica</option>
        <option  value="Dominican Republic">Dominican Republic</option>
        <option  value="East Timor">East Timor</option>
        <option  value="Ecuador">Ecuador</option>
        <option  value="Egypt">Egypt</option>
        <option  value="El Salvador">El Salvador</option>
        <option  value="England">England</option>
        <option  value="Equatorial Guinea">Equatorial Guinea</option>
        <option  value="Eritrea">Eritrea</option>
        <option  value="Estonia">Estonia</option>
        <option  value="Ethiopia">Ethiopia</option>
        <option  value="Falkland Islands">Falkland Islands</option>
        <option  value="Faroe Islands">Faroe Islands</option>
        <option  value="Fiji">Fiji</option>
        <option  value="Finland">Finland</option>
        <option  value="France">France</option>
        <option  value="French Guiana">French Guiana</option>
        <option  value="French Polynesia">French Polynesia</option>
        <option  value="French Southern Territories">French Southern Territories</option>
        <option  value="Gabon">Gabon</option>
        <option  value="Gambia">Gambia</option>
        <option  value="Georgia">Georgia</option>
        <option  value="Germany">Germany</option>
        <option  value="Ghana">Ghana</option>
        <option  value="Gibraltar">Gibraltar</option>
        <option  value="Greece">Greece</option>
        <option  value="Greenland">Greenland</option>
        <option  value="Grenada">Grenada</option>
        <option  value="GP">Guadeloupe</option>
        <option  value="Guadeloupe">Guam</option>
        <option  value="Guatemala">Guatemala</option>
        <option  value="Guinea">Guinea</option>
        <option  value="Guinea-Bissau">Guinea-Bissau</option>
        <option  value="Guyana">Guyana</option>
        <option  value="Haiti">Haiti</option>
        <option  value="Heard and McDonald Islands">Heard and McDonald Islands</option>
        <option  value="Honduras">Honduras</option>
        <option  value="Hong Kong">Hong Kong</option>
        <option  value="Hungary">Hungary</option>
        <option  value="Iceland">Iceland</option>
        <option  value="India">India</option>
        <option  value="Indonesia">Indonesia</option>
        <option  value="Iran">Iran</option>
        <option  value="Iraq">Iraq</option>
        <option  value="Ireland">Ireland</option>
        <option  value="Isle of	Man">Isle of	Man</option>
        <option  value="Israel">Israel</option>
        <option  value="Italy">Italy</option>
        <option  value="Ivory Coas">Ivory Coast</option>
        <option  value="Jamaica">Jamaica</option>
        <option  value="Japan">Japan</option>
        <option  value="Jordan">Jordan</option>
        <option  value="Kazakhstan">Kazakhstan</option>
        <option  value="Kenya">Kenya</option>
        <option  value="Kiribati">Kiribati</option>
        <option  value="Korea">Korea</option>
        <option  value="Korea (D.P.R.)">Korea (D.P.R.)</option>
        <option  value="Kuwait">Kuwait</option>
        <option  value="Kyrgyzstan">Kyrgyzstan</option>
        <option  value="Lao">Lao</option>
        <option  value="Latvia">Latvia</option>
        <option  value="Lebanon">Lebanon</option>
        <option  value="Lesotho">Lesotho</option>
        <option  value="Liberia">Liberia</option>
        <option  value="Libya">Libya</option>
        <option  value="Liechtenstein">Liechtenstein</option>
        <option  value="Lithuania">Lithuania</option>
        <option  value="Luxembourg">Luxembourg</option>
        <option  value="Macedonia">Macedonia</option>
        <option  value="Madagascar">Madagascar</option>
        <option  value="Malawi">Malawi</option>
        <option  value="Malaysia">Malaysia</option>
        <option  value="Maldives">Maldives</option>
        <option  value="Mali">Mali</option>
        <option  value="Malta">Malta</option>
        <option  value="Marshall Islands">Marshall Islands</option>
        <option  value="Martinique">Martinique</option>
        <option  value="Mauritania">Mauritania</option>
        <option  value="Mauritius">Mauritius</option>
        <option  value="Mayotte">Mayotte</option>
        <option  value="Mexico">Mexico</option>
        <option  value="Micronesia">Micronesia</option>
        <option  value="Moldova">Moldova</option>
        <option  value="Monaco">Monaco</option>
        <option  value="Mongolia">Mongolia</option>
        <option  value="Montserrat">Montserrat</option>
        <option  value="Morocco">Morocco</option>
        <option  value="Mozambique">Mozambique</option>
        <option  value="Myanmar">Myanmar</option>
        <option  value="Namibia">Namibia</option>
        <option  value="Nauru">Nauru</option>
        <option  value="Nepal">Nepal</option>
        <option  value="Netherlands">Netherlands</option>
        <option  value="Netherlands Antilles">Netherlands Antilles</option>
        <option  value="New Caledonia">New Caledonia</option>
        <option  value="New Zealand">New Zealand</option>
        <option  value="Nicaragua">Nicaragua</option>
        <option  value="Niger">Niger</option>
        <option  value="Nigeria">Nigeria</option>
        <option  value="Niue">Niue</option>
        <option  value="Norfolk Island">Norfolk Island</option>
        <option  value="Northern Ireland">Northern Ireland</option>
        <option  value="Northern Mariana Islands">Northern Mariana Islands</option>
        <option  value="Norway">Norway</option>
        <option  value="Oman">Oman</option>
        <option  value="Pakistan">Pakistan</option>
        <option  value="Palau">Palau</option>
        <option  value="Palestinian Territory,Occupied">Palestinian Territory,Occupied</option>
        <option  value="Panama">Panama</option>
        <option  value="Papua new Guinea">Papua new Guinea</option>
        <option  value="Paraguay">Paraguay</option>
        <option  value="Peru">Peru</option>
        <option  value="Philippines">Philippines</option>
        <option  value="Pitcairn Island">Pitcairn Island</option>
        <option  value="Poland">Poland</option>
        <option  value="Portugal">Portugal</option>
        <option  value="Puerto Rico">Puerto Rico</option>
        <option  value="Qatar">Qatar</option>
        <option  value="Reunion">Reunion</option>
        <option  value="Romania">Romania</option>
        <option  value="Russia">Russia</option>
        <option  value="Rwanda">Rwanda</option>
        <option  value="Saint Kitts And Nevis">Saint Kitts And Nevis</option>
        <option  value="Saint Lucia">Saint Lucia</option>
        <option  value="Saint Vincent And The Grenadines">Saint Vincent And The Grenadines</option>
        <option  value="Samoa">Samoa</option>
        <option  value="San Marino">San Marino</option>
        <option  value="Sao Tome and Principe">Sao Tome and Principe</option>
        <option  value="Saudi Arabia">Saudi Arabia</option>
        <option  value="Scotland">Scotland</option>
        <option  value="Senegal">Senegal</option>
        <option  value="Serbia and Montenegro">Serbia and Montenegro</option>
        <option  value="Seychelles">Seychelles</option>
        <option  value="Sierra Leone">Sierra Leone</option>
        <option  value="Singapore">Singapore</option>
        <option  value="Slovak Republic">Slovak Republic</option>
        <option  value="Slovenia">Slovenia</option>
        <option  value="Solomon Islands">Solomon Islands</option>
        <option  value="Somalia">Somalia</option>
        <option  value="South Africa">South Africa</option>
        <option  value="Spain">Spain</option>
        <option  value="Sri Lanka">Sri Lanka</option>
        <option  value="St Helena">St Helena</option>
        <option  value="St Pierre and Miquelon">St Pierre and Miquelon</option>
        <option  value="Sudan">Sudan</option>
        <option  value="Suriname">Suriname</option>
        <option  value="Svalbard And Jan Mayen Islands">Svalbard And Jan Mayen Islands</option>
        <option  value="Swaziland">Swaziland</option>
        <option  value="Sweden">Sweden</option>
        <option  value="Switzerland">Switzerland</option>
        <option  value="Syria">Syria</option>
        <option  value="Taiwan">Taiwan</option>
        <option  value="Tajikistan">Tajikistan</option>
        <option  value="Tanzania">Tanzania</option>
        <option  value="Thailand">Thailand</option>
        <option  value="Togo">Togo</option>
        <option  value="Tokelau">Tokelau</option>
        <option  value="Tonga">Tonga</option>
        <option  value="Trinidad And Tobago">Trinidad And Tobago</option>
        <option  value="Tunisia">Tunisia</option>
        <option  value="Turkey">Turkey</option>
        <option  value="Turkmenistan">Turkmenistan</option>
        <option  value="Turks And Caicos Islands">Turks And Caicos Islands</option>
        <option  value="Tuvalu">Tuvalu</option>
        <option  value="Uganda">Uganda</option>
        <option  value="Ukraine">Ukraine</option>
        <option  value="United Arab Emirates">United Arab Emirates</option>
        <option  value="United States" selected="selected" >United States</option>
        <option  value="Uruguay">Uruguay</option>
        <option  value="Uzbekistan">Uzbekistan</option>
        <option  value="Vanuatu">Vanuatu</option>
        <option  value="Vatican City State (Holy See)">Vatican City State (Holy See)</option>
        <option  value="Venezuela">Venezuela</option>
        <option  value="Vietnam">Vietnam</option>
        <option  value="Virgin Islands (British)">Virgin Islands (British)</option>
        <option  value="Virgin Islands (US)">Virgin Islands (US)</option>
        <option  value="Wales">Wales</option>
        <option  value="Wallis And Futuna Islands">Wallis And Futuna Islands</option>
        <option  value="Western Sahara">Western Sahara</option>
        <option  value="Yemen">Yemen</option>
        <option  value="Zambia">Zambia</option>
        <option  value="Zimbabwe">Zimbabwe</option>
      </select>
    
    </div>
    
     
       <div class="half first">
        <label>Phone: <abbr>*</abbr></label>
       <input type="text" required size="33" name="Phone" value="<?php echo $user[Phone];?>" />
     
      </div> 
      
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
  
  var opts = $('#country')[0].options;
for(var a in opts) { if(opts[a].value == '<?php echo $user["country"];?>') { $('#country')[0].selectedIndex = a; break; } }


 function display_shipping_address_name()
 {
	 if($('#save_address').attr('checked')) {
   		 $("#shipping_address_name").show();
	} else {
		$("#shipping_address_name").hide();
	}
 }
  
  </script>
  
  <div class="half ">
        <label>Save Address: <abbr>*</abbr></label>
       <input type="checkbox" value="1" id="save_address" name="save_address" onchange="return display_shipping_address_name();" <?php if(isset($_POST['save_address'])){ echo 'checked="checked"';}?>  /> (save shipping address for future orders)
     
      </div> 
    
    
     <div class="half first" <?php if(isset($user['save_address'])){ echo 'style="display:block;"'; }else{?> style="display:none;" <?php }?> id="shipping_address_name" >
        <label>Address Name: <abbr>*</abbr></label>
     <input type="input" value="" id="name_of_shipping_address" name="name_of_shipping_address" />
     
      </div> 
	  
	  
     
     <span><label>Requestor:</label>
     	 <input type="input" value="" id="requestor" name="requestor" size="33" />
     </span>
     
	 
     
	  <div class="first">
        <label>Requestor Email: <abbr></abbr></label>
         <input type="text" size="33" id="" name="requestor_email" id="requestor_email" />
      </div>  
   
       
     <span><label>Cost Center: <abbr>*</abbr></label>
     	 <input type="input" value="" required id="cost_center" name="cost_center" size="33" />
     </span>
     
    <!-- <span><label>Tax:</label>
     	   
    Self-Assess Tax <input type="radio" value="Self-Assess Tax " id="" name="tax_type"> <br/>
   
    Do Not Self-Assess Tax <input type="radio" value="Do Not Self-Assess Tax" id="" name="tax_type" >
    
     </span> -->
     
      
        
       <span><label>Additional Comments / Questions:</label>
     	 <textarea cols="80" rows="3" name="Notes"><?php echo $form[Notes];?></textarea>
     </span>
    
    
     <?php
 if(isset($custom_notification) and $custom_notification==1)
{
?>
    
     <span>
     <br/>
     <h4 align="center" style="    background: #777777;
    color: white;
    margin: 0px;
    padding: 2px;
    text-align: center;">Mock-Up Disclaimer
</h4>
     	<div  style="text-align:left;">
<strong>Please Note:</strong> Because this system is automated, Account Managers are responsible for ensuring the accuracy of all materials. Please double-check your order to make sure you�ve ordered the right types of items (e.g., poster, banner, etc.) Please then proofread to ensure the dealership name is accurate and spelled correctly. The system produces a mock-up for these purposes. Dealer Tire will not refund purchases for inaccurate orders. Thank you for your order!




</div>
     </span>
     
     <span>
     <input type="checkbox" value="1" name="confirm" id="confirm" <?php  if(isset($_POST['confirm'])){ echo 'checked';} ?> > I agree that all of the items in my cart are accurate and I approve this order.
     
     </span>
     
<?php }?>     
     
    
      <div class="clearfix"></div>
    </div>
    
	
	
	
	<div class="clearfix"></div>
  
  <!--  <a href="javascript:void(0);" onclick="ajax_submit_billing_details();" class="continue button color">Continue</a>-->
	
	<?php
	
	if($order_total > $BudgetBalance)
	{
	?>
		<div style="color:red;">You can not submit order because you do not have sufficient balance.</div>
	<?php
	}else{
	?>
	<input type="submit" name="submit_billing_form" class="continue button color" value="Submit Order" />
	
	<?php } ?>
   
   </form>
   
    <!--checkout-delivery.php-->
    <!--<a href="checkout-delivery.html">-->
	
   <!-- <div class="checkout-section" ><span>2</span> Delivery</div>
    
	</a> <a href="javascript:void(0);">
    	<div class="checkout-section"><span>3</span> Payment & Order Review</div>
    </a> -->
	
	</div>
  
  <!-- Checkout Cart / End -->
</div>
<!-- Container / End -->
<div class="margin-top-50"></div>


<script type="text/javascript" src="../javascript.js"></script>
<script type="text/javascript" src="../datepicker.js"></script>
<link rel="stylesheet" href="../datepicker.css" />
<script type="text/javascript" src="../js/prototype.js"></script>
<script type="text/javascript" src="../js/scriptaculous.js"></script>


<script>
var cursrch='';
checkdealer=function(sval){
	var sval=document.getElementById('ddsrch').value;
	var dp=document.getElementById('dealerpicker');
	if(sval.length<3) { cursrch=''; dp.innerHTML=''; return; }
	if(sval == cursrch) { return; }
	
	 
	
	cursrch = sval;
	var x=getXMLObj();
	x.open('get','checkout-billing-details.php?a=gds&ss='+sval, true);
	x.onreadystatechange=function(){if(x.readyState=='4'){
		document.getElementById('dealerpicker').innerHTML=x.responseText;
	}};
	x.send(null);
}
getdealer=function(str){
	if(str=='')return;
	var x=getXMLObj();
	x.open('get','checkout-billing-details.php?a=gd&ss='+str, true);
	x.onreadystatechange=function(){if(x.readyState==4){
		if(!/,/.test(x.responseText)) { alert(x.responseText); }
		else {
			document.getElementById('dealerpicker').innerHTML='';
			var tar=x.responseText.split(',');
			var f=document.cart;
			f.Company.value = tar[0];
			//f.Phone.value = tar[1];
			f.Address1.value = tar[1];
			f.Address2.value = tar[2];
			f.City.value = tar[3];
			f.State.value = tar[4];
			f.Zip.value = tar[5];
			
			f.dearler_name_with_id.value = tar[0]+' ('+tar[6]+')';
			
			//f.Notes.value = '\\nDealer: '+tar[6];
			f.Notes.value = 'Dealer: '+tar[6];
			
			f.dearler_name_with_id.value = tar[0]+' ('+tar[6]+')';
			
		
			
			document.getElementById("ShipToName").value='';
			
			
		}
	}};
	x.send(null);
}
</script>



<?php
//pr_n($_SESSION);

//session_destroy();
?>
<?php include("footer.php");?>


<script>

$(document).ready(function(e) {
   
	<?php
	if($is_backorder==1)
	{
	?>
	$("#is_backorder").show();
	<?php
	}
	?>
	
	<?php
	if(isset($custom_notification) and $custom_notification==1)
	{
	?>
	$("#is_custom_noficiation").show();
	<?php
	}
	?>
	
	
	
	
	
});

</script>



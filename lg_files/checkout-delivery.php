<?php
ob_start();
include("setting.php");

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
		
		//print_r($_POST);
	}
	
}


?>
<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
 

 
<section class="titlebar">
<div class="container">
	<div class="sixteen columns">
		<h2>Checkout</h2>
		
		<nav id="breadcrumbs">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Shop</a></li>
				<li><a href="#">Checkout</a></li>
				<li>Delivery</li>
			</ul>
		</nav>
	</div>
</div>
</section>


 

<!-- Container -->
<div class="container">
	
	
	<!-- Checkout Cart -->
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
	$cart_total = '';
	$tmp_custom_price_items = 0 ;
	
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
				  
			}	
		}else{
			
			$is_apply_sale_price = check_sale_date($item_id);
			 
			if($is_apply_sale_price==1)
			{
				$price = get_item_sale_price_by_size_new($size,$item_id);	
				$item_detail['Price'] = $price ; 
			}
			
		}
		
		
		
		$price = $item_detail['Price'];		
		$cart_item_total = $price * $qty_sess ;
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		$cart_item_total = number_format($cart_item_total,2);
		$cart_total = number_format($cart_total,2);
		
		if(isset($_SESSION['item_img_tmp'][$item_detail['ID']][$set_key]))
		{
				$item_image = $_SESSION['item_img_tmp'][$item_detail['ID']][$set_key] ;
		}
	?>
      <tr>
        <td class="hide-on-mobile"><img style="width:80px; height:80px;"src="<?php echo $item_image;?>" alt=""/></td>
        <td class="cart-title"><a href="#"><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></a>
		<br/><?php echo $item_detail['FormID'];?> 
	
	
	<?php
				
				
			 
				
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
				?>
				
				
				
				<?php
					if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
					{
						$custom_name_price = $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Name ($$custom_name_price): ";
						echo  $_SESSION['custom_name_tmp'][$item_id][$set_key] ;
						
						$tmp_custom_price_items += $custom_name_price ;
						
					}
				?>
				
				<?php
					if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
					{
						$custom_number_price = $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Number ($$custom_number_price): ";
						echo  $_SESSION['custom_number_tmp'][$item_id][$set_key] ;
						
						$tmp_custom_price_items += $custom_number_price ;
						
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

	
<?php
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
		if($_SESSION['CID']==56 or $_SESSION['CID']==59  or $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==78 or $_SESSION['CID']==72)
		{
			$delivery_charge = 0 ;
		}else{
			$delivery_charge = 10 ;
		}
	}
	
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
		// $sales_tax =  5.94 ; // 5.94 , it is salex tax on PA state zip code.
		
		 $sales_tax =  0  ;
		
		 
	}	
	
	
}







$order_total = "";

$order_total = $cart_total + $delivery_charge + $sales_tax + $tmp_custom_price_items ;
$order_total = number_format($order_total,2);	

$sales_tax = number_format($sales_tax,2);	




$delivery_charge = 0 ;
if(isset($_SESSION['delivery_form']['delivery_method']) and $cart_total>0)
{
	$delivery_method = $_SESSION['delivery_form']['delivery_method'];
 
	
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
		if($_SESSION['CID']==56 or $_SESSION['CID']==59  or $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==78 or $_SESSION['CID']==72)
		{
			$delivery_charge = 0 ;
		}else{
			$delivery_charge = 10 ;
		}
	}
	 
		
}

$delivery_charge = number_format($delivery_charge,2);	

?>
	
	<table class="cart-table bottom">
      <tr>
			<th class="checkout-totals"> 
				<div class="checkout-subtotal"> Subtotal: <span>$<?php echo $cart_total;?></span> </div> <br>
				
				
				<?php
					if($sales_tax>0)
					{
					?>
					
					<div class="checkout-subtotal">Salex Tax: <span>$<?php echo $sales_tax;?></span></div><br>
					
					<?php
					}
					?>
					
					
			<?php
					if($delivery_charge>0)
					{
					?>
					
					<div class="checkout-subtotal">Shipping & Handling: <span>$<?php echo $delivery_charge;?></span></div><br>
					
				<?php
				}
				?>
					
					
					<div class="checkout-subtotal summary">Order Total: <strong class="order_total_td">$<strong class="ajax_total_price"><?php echo $order_total;?></strong></strong></div>
				
				
				
			</th>
      </tr>
	  
    </table>
	
	</div>
	<!-- Checkout Cart / End -->
	
	<div class="eight columns" >
	
	<a href="checkout-billing-details.php"><div class="checkout-section"><span>1</span> Order Details <strong><i class="fa fa-edit"></i>Edit</strong> </div></a>
	
	<div class="checkout-content">
	
	<div class="four columns alpha ">
		<ul class="address-review" style="margin-left:10px;">
			<!--<li><strong>Shipping Address</strong></li>-->
			
			<?php
			
			if(isset($_SESSION['billing_form']['is_same_billing_address_as_shipping']) and $_SESSION['billing_form']['is_same_billing_address_as_shipping']==1)
			{
			?>
					
			<li>Same as Billing Address</li>
			
			<?php
			}else{
			?>
			
			
			<li><?php echo ucfirst($_SESSION['billing_form']['shipping_first_name']). " ". ucfirst($_SESSION['billing_form']['shipping_last_name']); ?></li>
			<li><?php echo  $_SESSION['billing_form']['shipping_email']?></li>
			<li><?php echo  $_SESSION['billing_form']['shipping_phone']?></li>
			 
			
		<?php
		}
		?>	
			
			
			<!--<li>Mr. Walter C. Brown</li>
			<li>49 Featherstone Street</li>
			<li>London</li>
			<li>EC1Y 8SY</li>
			<li>United Kingdom</li>-->
			
		</ul>
	</div>
	
	
 
	
	<div class="clearfix"></div>
	</div>
	
	<form  method="post" action="" name="delivery_form">
	<div class="eight columns">

	<div id="order_details"  style=" margin-left: -10px;
    margin-right: 9px;" class="checkout-section active"><span>2</span> Delivery</div>
		
		
		
		<div class="checkout-delivery active">

			<ul class="delivery-options">
			
			  
	  <?php
	  
	   if($CID==59  or $CID==78)
	   {
	   		$_POST['delivery_method'] = '7_to_10_days';
	   }
	   
	   
	  
	  if($CID!=56 and $CID!=63)
	  {
	  ?>
			
			<!--<li><strong>Choose a delivery option</strong></li>-->
			
		
		<?php
		}
		?>
			
			<li>
			
				
				
				
				<?php
				if($_SESSION['CID']!=56 and $_SESSION['CID']!=62 and $_SESSION['CID']!=63)
				{ 
				
					$is_required = '';
					if($_SESSION['CID']==69)
					{
						$is_required = 'required';
					}
				?>
				
				<div class="delivery-option"> 
					<input <?php echo $is_required;?>  onchange="show_extra_field(this.value);" id="delivery_method_2" type="radio" name="delivery_method" value="7_to_10_days" <?php if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="7_to_10_days"){ echo 'checked="checked"';}?> />

<?php
if($_SESSION['CID']==56  or $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==72 or $_SESSION['CID']==89)
		{
			//$delivery_charge = 0 ;
			?>
			<label for="shipping-address-2" class="checkbox">FREE Shipping
 <span>(7 - 10 days) $0.00 &nbsp;&nbsp;&nbsp; </span></label>
 
			<?php
		}else if($_SESSION['CID']==59 or  $_SESSION['CID']==78  )
		{
		?>	<label for="shipping-address-2" class="checkbox">FREE Delivery 
 <span>(10 - 14 Business Days) &nbsp;&nbsp;&nbsp; </span></label>
 
		
		 
		
		 
		<?php
		}else if($_SESSION['CID']==60 or $_SESSION['CID']==58 or $_SESSION['CID']==79)
		{
		?>
		<label for="shipping-address-2" class="checkbox">Store
 <span>(7 - 10 days) $10.00 &nbsp;&nbsp;&nbsp; </span></label>
		<?php
		}else{
		
		?>
		<label for="shipping-address-2" class="checkbox">Standard Shipping 
 <span>(7 - 10 days) $10.00 &nbsp;&nbsp;&nbsp; </span></label>
 
		<?php
			//$delivery_charge = 10 ;
		}
?>


					
 
				</div>
				
				
				<?php
				}
				?>
				
				
				<?php
				if($_SESSION['CID']==59)
				{ 
				?>  
				<div style="margin-top:20px;"><select name="facility_name" id="facility_name" class="select-field w-select" style="height:30px;">
					<option value="">Select Facility</option>
					<option value="LECOM SENIOR LIVING">LECOM SENIOR LIVING</option>
					<option value="MEDICAL ASSOCIATES">MEDICAL ASSOCIATES</option>
					<option value="MILLCREEK COMMUNITY HOSPITAL">MILLCREEK COMMUNITY HOSPITAL</option>
					<option value="PARKSIDE NORTH EAST">PARKSIDE NORTH EAST</option>
					<option value="PRESQUE ISLE">PRESQUE ISLE</option>
					<option value="REGENCY AT SOUTH SHORE">REGENCY AT SOUTH SHORE</option>
					<option value="VNA NURSES">VNA NURSES</option>
					<option value="WESTMINISTER">WESTMINISTER</option>
			</select>

				
				<?php
				}
				
				
				if($_SESSION['CID']==78)
				{ 
				?>  
				<div style="margin-top:20px;" ><br/><br/>
       Name of Facility:  
        <input size="50" type="text" name="facility_name" id="facility_name"  placeholder=""  value="<?php if(isset($_POST['facility_name'])){echo $_POST['facility_name'] ;}?>" /><br/><br/><br/>
		
		
		
				
       
				
				
				<!--<select name="facility_name" id="facility_name" class="select-field w-select" style="height:30px;">
					<option value="">Select Facility</option>
					<option value="LECOM SENIOR LIVING">LECOM SENIOR LIVING</option>
					<option value="MEDICAL ASSOCIATES">MEDICAL ASSOCIATES</option>
					<option value="MILLCREEK COMMUNITY HOSPITAL">MILLCREEK COMMUNITY HOSPITAL</option>
					<option value="PARKSIDE NORTH EAST">PARKSIDE NORTH EAST</option>
					<option value="PRESQUE ISLE">PRESQUE ISLE</option>
					<option value="REGENCY AT SOUTH SHORE">REGENCY AT SOUTH SHORE</option>
					<option value="VNA NURSES">VNA NURSES</option>
					<option value="WESTMINISTER">WESTMINISTER</option>
			</select>-->

				
				<?php
				}
				
				?>
				
				<?php
				if($CID==56)
				{
				?>
				
				<!--<div class="delivery-option">
					<input onchange="show_extra_field(this.value);" id="delivery_method_2" type="radio" name="delivery_method" value="1_to_2_days" <?php if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="1_to_2_days"){ echo 'checked="checked"';}?> />
					<label for="shipping-address-2" class="checkbox">Pickup at Leader Graphics on
 <?php echo second_friday_from_current_date();?> </label>
				</div>-->
			
			<?php
				}
				?>	
				
				<?php
				 
				
				if($CID==56 || $CID==63)
				{
					$_POST['delivery_method'] = '3_to_5_days';
				?>
				
				<div class="delivery-option">
					<input onchange="show_extra_field(this.value);" id="delivery_method" type="radio"  name="delivery_method" value="3_to_5_days" <?php if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="3_to_5_days"){ echo 'checked="checked"';}?> />
					<label for="shipping-address" class="checkbox">Pickup at School </label>
					
					
				</div>
				
				<?php
				}
				?>
				
				<?php
				 
				
				if($CID==61)
				{
					
					if(!isset($_POST['delivery_method']))
					{
						$_POST['delivery_method'] = "3_to_5_days";
					}
					
				?>
				
				<div class="delivery-option">
					<input onchange="show_extra_field(this.value);"  id="delivery_method" type="radio"  name="delivery_method" value="3_to_5_days" <?php if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="3_to_5_days"){ echo 'checked="checked"';}?> />
				
				
				<label for="shipping-address-2" class="checkbox">FREE Pickup 
 <span>(Leader Graphics 1107 Hess Ave, Erie, PA 16503) &nbsp;&nbsp;&nbsp; </span></label>
					
					
				</div>
				
				<?php
				}
				?>
				
				<?php
				if($CID==62)
				{
					$_POST['delivery_method'] = '3_to_5_days';
				?>
				<div class="delivery-option">
					<input onchange="show_extra_field(this.value);" id="delivery_method" type="radio"  name="delivery_method" value="3_to_5_days" <?php if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="3_to_5_days"){ echo 'checked="checked"';}?> />
					<label for="shipping-address" class="checkbox">Delivered to Titusville Area Hospital <span>(10 - 14 Business Days)  &nbsp;&nbsp;&nbsp; </span> </label>
					
					
				</div>
				
				<?php
				}
				?>
				
				
				<div   class="clearfix"></div>
			
			</li>
		</ul>
	
	<script>
	function show_extra_field(delivery_method)
	{
		if(delivery_method=="3_to_5_days")
		{
			$("#3_to_5_days").show();
			$("#7_to_10_days").hide();
			
		}else if(delivery_method=="7_to_10_days")
		{
				$("#3_to_5_days").hide();
				$("#7_to_10_days").show();
		}else{
			$("#3_to_5_days").hide();
			$("#7_to_10_days").hide();
		}
		
	}
	
	</script>
		
		<?php
		$css_3_to_5_days = 'style="display:none;"';
		if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="3_to_5_days")
		{
			$css_3_to_5_days = 'style="display:block;"';
		}
		
		$css_7_to_10_days = 'style="display:none;"';
		if(isset($_POST['delivery_method']) and $_POST['delivery_method']=="7_to_10_days")
		{
			$css_7_to_10_days = 'style="display:block;"';
		}
		
		 
		
		?>
		
		 <div class="checkout-content" id="3_to_5_days" <?php echo $css_3_to_5_days;?> >
       
	   <?php
	   if($_SESSION['CID']==62)
	   {
	   ?>
	   
	    <div >
        <label>Name of Facility: <abbr></abbr></label>
        <input size="50" type="text" name="student_name" id="student_name"  placeholder=""  value="<?php if(isset($_POST['student_name'])){echo $_POST['student_name'] ;}?>" />
		
		
		<p style="font-style:italic"><i>All orders will be processed on the 1st and 15th of every month.  After the order is processed, it may take up to 14 business days before the order is delivered to TAH.</i></p>
      </div>
	  
	  
	  
	  
	   <?php
	   }else{
	   ?>
	   
	   
	   <?php
	   if($CID!=61)
	   {
	   ?>
	   
	   <div class="half first">
        <label>Student Name: <abbr></abbr></label>
        <input type="text" name="student_name" id="student_name"  placeholder=""  value="<?php if(isset($_POST['student_name'])){echo $_POST['student_name'] ;}?>" />
      </div>
      <div class="half">
        <label>Homeroom Number: <abbr></abbr></label>
        <input type="text" name="homeroom_number" id="homeroom_number"  placeholder=""  value="<?php if(isset($_POST['homeroom_number'])){echo $_POST['homeroom_number'] ;}?>" />
		
		
		
      </div>
	  
	  <?php
	  }
	  ?>
	  
	  
	  <?php
	  
	  if($CID==56 || $CID==63 )
	  {
	  ?>
	  
	  <div class="delivery-option">
					<input id="is_gift_order" type="checkbox" name="is_gift_order" value="1" <?php if(isset($_POST['is_gift_order']) and $_POST['is_gift_order']=="1"){ echo 'checked="checked"';}?> />
					<label for="shipping-address-2" class="checkbox">This order is a gift (do not leave with student) </label>
				</div>
				
	  <?php
	  }
	  ?>
	  
		
		<?php
		}
		?>

		
      </div>
	  
	  <?php
	  if($CID!=59 and $CID!=78 )
	  {
	  ?>
	  
	   <div class="checkout-content" id="7_to_10_days" <?php echo $css_7_to_10_days;?> >
	   	
		<?php
		 if($CID==60 or $CID==58 or $CID==79)
		 {
		 
		?>
		<label>Store Number: <abbr></abbr></label>
      <input required type="text" class="input-text "  value="<?php if(isset($_POST['store_number'])){echo $_POST['store_number'] ;}?>"  name="store_number" id="store_number"  />
	  
		<?php
		}else{
		?>
		
		
		  <label>Shipping Address: <abbr></abbr></label>
      <input type="text" class="input-text "  value="<?php if(isset($_POST['shipping_address_1'])){echo $_POST['shipping_address_1'] ;}?>"  name="shipping_address_1" id="shipping_address_1"  />
	  
      <div class="half first">
        <label>Town / City: <abbr></abbr></label>
        <input type="text" placeholder=""  value="<?php if(isset($_POST['shipping_city'])){echo $_POST['shipping_city'] ;}?>" name="shipping_city" id="shipping_city"  />
      </div>
      <div class="half">
        <label>Postcode / Zip:<abbr></abbr></label>
        <input type="text" placeholder=""  value="<?php if(isset($_POST['shipping_zip'])){echo $_POST['shipping_zip'] ;}?>" name="shipping_zip" id="shipping_zip"  />
      </div>
      <div class="fullwidth">
        <label>State: <abbr></abbr></label>
        <select name="shipping_state" id="shipping_state"  >
          <option value="">Select a state&hellip;</option>
          <option value="AL" >Alabama</option>
          <option value="AK" >Alaska</option>
          <option value="AZ" >Arizona</option>
          <option value="AR" >Arkansas</option>
          <option value="CA" >California</option>
          <option value="CO" >Colorado</option>
          <option value="CT" >Connecticut</option>
          <option value="DE" >Delaware</option>
          <option value="DC" >District Of Columbia</option>
          <option value="FL" >Florida</option>
          <option value="GA" >Georgia</option>
          <option value="HI" >Hawaii</option>
          <option value="ID" >Idaho</option>
          <option value="IL" >Illinois</option>
          <option value="IN" >Indiana</option>
          <option value="IA" >Iowa</option>
          <option value="KS" >Kansas</option>
          <option value="KY" >Kentucky</option>
          <option value="LA" >Louisiana</option>
          <option value="ME" >Maine</option>
          <option value="MD" >Maryland</option>
          <option value="MA" >Massachusetts</option>
          <option value="MI" >Michigan</option>
          <option value="MN" >Minnesota</option>
          <option value="MS" >Mississippi</option>
          <option value="MO" >Missouri</option>
          <option value="MT" >Montana</option>
          <option value="NE" >Nebraska</option>
          <option value="NV" >Nevada</option>
          <option value="NH" >New Hampshire</option>
          <option value="NJ" >New Jersey</option>
          <option value="NM" >New Mexico</option>
          <option value="NY" >New York</option>
          <option value="NC" >North Carolina</option>
          <option value="ND" >North Dakota</option>
          <option value="OH" >Ohio</option>
          <option value="OK" >Oklahoma</option>
          <option value="OR" >Oregon</option>
          <option value="PA" >Pennsylvania</option>
          <option value="RI" >Rhode Island</option>
          <option value="SC" >South Carolina</option>
          <option value="SD" >South Dakota</option>
          <option value="TN" >Tennessee</option>
          <option value="TX" >Texas</option>
          <option value="UT" >Utah</option>
          <option value="VT" >Vermont</option>
          <option value="VA" >Virginia</option>
          <option value="WA" >Washington</option>
          <option value="WV" >West Virginia</option>
          <option value="WI" >Wisconsin</option>
          <option value="WY" >Wyoming</option>
          <option value="AA" >Armed Forces (AA)</option>
          <option value="AE" >Armed Forces (AE)</option>
          <option value="AP" >Armed Forces (AP)</option>
          <option value="AS" >American Samoa</option>
          <option value="GU" >Guam</option>
          <option value="MP" >Northern Mariana Islands</option>
          <option value="PR" >Puerto Rico</option>
          <option value="UM" >US Minor Outlying Islands</option>
          <option value="VI" >US Virgin Islands</option>
        </select>
      </div>
	  
	  <?php
	  }
	  ?>
	   
	   </div>
	  
	<?php
	}
	?>
	
		</div>
		<div   class="clearfix"></div>
		<!--<a href="checkout-payment-order-review.php" class="continue button color">Continue</a>-->
		
		<input style=" margin-left: -10px;
    margin-right: 9px;" type="submit" name="delivery_form" class="continue button color" value="Continue" />

			<a  href="javascript:void(0);"><div style=" margin-left: -10px;
    margin-right: 9px;" class="checkout-section"><span>3</span> Payment & Order Review</div></a>

		</div>
		
	</form>	
		
		<!-- CHeckout Content / End -->
</div>
	

</div>
<!-- Container / End -->

<div class="margin-top-50"></div>
 

 
<?php
//pr_n($_SESSION);

//session_destroy();
?>
<?php include("footer.php");?>

<?php
if($_SESSION['CID']==59 or $_SESSION['CID']==78)
{ 
?> 
	<script>
	var opts = $('#facility_name')[0].options;
for(var a in opts) { if(opts[a].value == '<?php echo $_POST["facility_name"];?>') { $('#facility_name')[0].selectedIndex = a; break; } }
	</script>
<?php
}
?>
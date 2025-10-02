<?php
ob_start();
include("setting.php");
include("stripe_key_setting.php");

if(isset($_SESSION['yes_redirect_confirmation']) and $_SESSION['yes_redirect_confirmation']==1)
{
	//print_r($_SESSION);
	$order_id = $_SESSION['order_id_tmp']; 
	unset($_SESSION['yes_redirect_confirmation']);
	unset($_SESSION['order_id_tmp']);
	header("location:order_confirmation.php?oid=$order_id");
	die;
}

//print_r($_SESSION);

$CID = $_SESSION['CID'];

$sql_get_shop_detail = " SELECT * FROM  `Clients` where ID = '$CID'   ";
$client_detail = mysql_fetch_assoc(mysql_query($sql_get_shop_detail));



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
				<li>Payment & Order Review</li>
			</ul>
		</nav>
	</div>
</div>
</section>


<!-- Content
================================================== -->

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
	
	
	<?php
	
	
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
		
		
		
		if($_SESSION['CID']==56 or $_SESSION['CID']==59 or  $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==78 or  $_SESSION['CID']==72 or  $_SESSION['CID']==89 )
		{
			$delivery_charge = 0 ;
		}else{
			$delivery_charge = 10 ;
		}
		
		
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
		// $sales_tax =  5.94 ; // 5.94 , it is salex tax on PA state zip code.
		
		 $sales_tax =  0  ;
		
		 
	}	
	
	
}




$order_total = "";
	
	$order_total = $cart_total + $delivery_charge + $sales_tax + $tmp_custom_price_items;
	$order_total = number_format($order_total,2);	
	
	
	
	$sales_tax = number_format($sales_tax,2);	
	
	$delivery_charge = number_format($delivery_charge,2);	
	
	
	
	?>

	
    </table>

			<!-- Apply Coupon Code / Buttons -->
			<table class="cart-table bottom">

				<tr>
				<th class="checkout-totals">
					<div class="checkout-subtotal">Subtotal: <span>$<?php echo $cart_total;?></span></div><br>
					
					
					<?php
					if($sales_tax>0)
					{
					?>
					
					<div class="checkout-subtotal">Salex Tax: <span>$<?php echo $sales_tax;?></span></div><br>
					
					<?php
					}
					?>
					
					<div class="checkout-subtotal">Shipping & Handling: <span>$<?php echo $delivery_charge;?></span></div><br>
					
					<div class="checkout-subtotal summary">Order Total: <strong class="order_total_td">$<strong class="ajax_total_price"><?php echo $order_total;?></strong></strong></div>
				</th>
				</tr>

			</table>
	</div>
	<!-- Checkout Cart / End -->


	<div class="eight columns">


	<!-- Checkout Content -->
	<a href="checkout-billing-details.php"><div class="checkout-section"><span>1</span> Billing Details <strong><i class="fa fa-edit"></i>Edit</strong> </div></a>
	<div class="checkout-content">
	
	<div class="four columns alpha">
		<ul class="address-review">
			
			<li><strong>Shipping Address</strong></li>
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
			 if($_SESSION['delivery_form']['delivery_method']=="7_to_10_days")
			 {
			 ?>
			 
			<li><?php echo  $_SESSION['delivery_form']['shipping_address_1']?></li>
			<li><?php echo  $_SESSION['delivery_form']['shipping_city']?></li>
			<li><?php echo  $_SESSION['delivery_form']['shipping_state']?> <?php echo  $_SESSION['delivery_form']['shipping_zip']?></li>
			
			<?php
			}
			?>
			 
			
		<?php
		}
		?>	
			
		</ul>
	</div>
	 
	<div class="clearfix"></div>
	</div>
	

		<a href="checkout-delivery.php"><div class="checkout-section"><span>2</span> Delivery <strong><i class="fa fa-edit"></i>Edit</strong> </div></a>
		<div class="checkout-delivery">

			<div class="eight columns alpha omega">
				<ul class="address-review delivery">
					
				<?php
				if($delivery_method=="3_to_5_days")
				{
					$delivery_charge = 0 ;
				?>
				
					<?php
					if($CID==56 || $CID==63)
					{
					?>
						<li><strong>Pickup at School</strong></li>
					<?php
					}
					?>
					
					<?php
					if($CID==62)
					{
					?>
					<li><strong>Delivered to Titusville Area Hospital (10 - 14 Business Days) </strong></li>
					<?php
					}
					?>
					
					<?php
					if($CID==61)
					{
					?>
						<li><strong>FREE Pickup (Leader Graphics 1107 Hess Ave, Erie, PA 16503)</strong></li>
					<?php
					}
					?>
				
				<?php }
				
				if($delivery_method=="1_to_2_days")
				{
					$delivery_charge = 0 ;
				?>	
					
					<li><strong>Pickup at Leader Graphics On <?php echo second_friday_from_current_date();?></strong></li>
					
					
				<?php 
				}
				
				?>
	
				
				<?php  
				
				if($delivery_method=="7_to_10_days")
				{
					if($_SESSION['CID']==56 or $_SESSION['CID']==59 or $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==78 )
		{
			$delivery_charge = 0 ;
		}else{
			$delivery_charge = 10 ;
		}
				?>	
					
					<!--<li><strong>Standard Shipping<span class="delivery-summary">
 (7 - 10 days) $10.00    </span></strong></li>-->
 <?php
 
 if($_SESSION['CID']==56  or $_SESSION['CID']==62 or $_SESSION['CID']==63 or $_SESSION['CID']==72 or $_SESSION['CID']==89 )
		{
			//$delivery_charge = 0 ;
			?>
			 
 
 <li><strong>FREE Shipping <span class="delivery-summary">
 (7 - 10 days) $0.00     </span></strong></li>
 
			<?php
		}else if($_SESSION['CID']==59 or $_SESSION['CID']==78  )
		{
		?>	 
 
 <li><strong>FREE Delivery <span class="delivery-summary">
 (10 - 14 Business Days)     </span></strong>
 
 
 <?php
  if($_SESSION['CID']==59 and $_SESSION['delivery_form']["facility_name"]!="") 
  {
 	echo "<br/><strong>Facility :</strong> ".$_SESSION['delivery_form']["facility_name"];
 ?>
 	
 <?php 
 }
 ?>
 
 <?php
  if($_SESSION['CID']==78 and $_SESSION['delivery_form']["facility_name"]!="") 
  {
 	echo "<br/><strong>Facility :</strong> ".$_SESSION['delivery_form']["facility_name"];
 ?>
 	
 <?php 
 }
 ?>
 
 </li>
 
		
		<?php
		}else if($_SESSION['CID']==60 or $_SESSION['CID']==58 or $_SESSION['CID']==79)
		{
		?>
		<li><strong>Store<span class="delivery-summary">
 (7 - 10 days) $10.00    </span></strong>
 
 <?php 
 echo "<br/><strong>Store Number :</strong> ".$_SESSION['delivery_form']["store_number"];
 ?>
 </li>
		
		
		
		
		<?php 
		
		
		
		}else{
		
		?>
		
		<li><strong>Standard Shipping<span class="delivery-summary">
 (7 - 10 days) $10.00    </span></strong></li>
 
		<?php
			//$delivery_charge = 10 ;
		}
		?>
					
					
				<?php 
				}
				
				?>
				
				
	
					
					
					
					
					
				</ul>
			</div>
			<div class="clearfix"></div>

		</div>
		<div class="clearfix"></div>

		<?php
						if($client_detail['is_payment_enable']==1)
						{
						?>

			<div id="order_details" class="checkout-section active"><span>3</span> Payment & Order Review</div>
            
            
            <?php
			}
			?>
			
			 <form id="payment-form" name="payment-form" data-name="payment-form" 
			 class="form" method="post" action="charge.php">
			
			<div class="checkout-summary">
				<div class="eight columns alpha omega">
					<ul class="address-review summary">
					
					<?php
					if($CID==85 or $CID==86 or $CID==89)
					{
						$lable_field_payment = "Billing Code";
						if($CID==89)
						{
							$lable_field_payment = "Billing Code";
						}
					
					?>
					    
					
						<li><strong><input onchange="select_pay_type(this.value)" <?php if(!isset($_POST['payment_type'])){ echo 'checked="checked"';}else{ if(isset($_POST['payment_type']) and $_POST['payment_type']=="credit_card"){ echo 'checked="checked"'; }} ?>   type="radio" name="payment_type" value="credit_card" /> Credit Card</strong></li>
						<li><strong><input onchange="select_pay_type(this.value)" <?php  if(isset($_POST['payment_type']) and $_POST['payment_type']=="billing_code"){ echo 'checked="checked"'; } ?> type="radio" name="payment_type" value="billing_code" /> <?php echo $lable_field_payment;?></strong></li>
						
					<?php
					}
					?>	
					
					<script>
					function select_pay_type(payment_type)
					{
						
					
						if(payment_type=="credit_card")
						{
							$(".credit_card_class").show();
							
							$(".billing_code_class").hide();
							
							$("#department_code").attr('required','');
							
							$("#business_reason").attr('required','');
							
							
						}else{
							
							$(".credit_card_class").hide();
							
							$(".billing_code_class").show();
							
							$("#department_code").attr('required','required');
							
							$("#business_reason").attr('required','required');
							
							
							
						}
						
						
						
					}
					</script>
						
						<span class="billing_code_class" style="display:none;">
							
						<?php
						$field_lable = "Department Code";
						if($CID==89)
						{
								$field_lable = "Billing Code";
						}
						
							if($CID!=89)
						{
					
						 ?>
							
							<li>If company is paying, please enter your department code AND business reason for ordering items.</li>	<?php
							}
							?>
							
							<li class="credit-card-fields">
							
							
							<span><label><?php echo $field_lable;?>:</label><input   type="text" value=""  data-name="department_code" id="department_code"   name="department_code" /><br/></span>
							</li>
							
							
							<?php
							if($CID!=89)
						{ ?>
							<li class="credit-card-fields">
							<span><label>Business Reason:</label><input   type="text" value=""  data-name="business_reason" id="business_reason"   name="business_reason" /><br/></span>
							</li>
							
							<?php
							}
							?>
							
							
						</span>
						
                        <?php
						if($client_detail['is_payment_enable']==1)
						{
						?>
                        
						<span class="credit_card_class">
						<li><strong>Credit Card</strong></li>
						<li>
							<ul class="payment-icons checkout">
								<!--<li><img src="images/visa.png" alt="" /></li>
								<li><img src="images/mastercard.png" alt="" /></li>
								<li><img src="images/skrill.png" alt="" /></li>
								<li><img src="images/moneybookers.png" alt="" /></li>
								<li><img src="images/paypal.png" alt="" /></li>-->
								
								<li><img src="images/cc_icons.png" class="image-3"></li>
								
							</ul>
							<div class="clearfix"></div>
						</li>
						
						<li class="credit-card-fields">
							
							
							<span><label>Full Name on Card:</label><input   type="text" value="" required data-name="card_name" id="card_name" required=""  name="name" /></span>
							
							<span><label>Credit Card Number:</label><input type="text" value="" data-name="card_number" data-stripe="number"  id="card_number" name="card_number"  required=""  /></span>

							<span><label>Month:</label>
							
							
							 <select id="exp_month" name="Exp_Month"  data-stripe="exp_month" required class="select-field-2 w-select">
                       <!-- <option value="Month">Month</option>-->
                        <option value="01">01</option>
                        <option value="02">02</option>
                        <option value="03">03</option>
                        <option value="04">04</option>
                        <option value="05">05</option>
                        <option value="06">06</option>
                        <option value="07">07</option>
                        <option value="08">08</option>
                        <option value="09">09</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                      </select>
							
							
							
							</span>
							
							
							<span><label>Year:</label>
							 <select id="exp_year" name="Exp_Year" data-stripe="exp_year" required="" class="select-field w-select">
                        <?php 
				  $y=date("Y");
				  for($i=$y;$i<$y+15;$i++)
				  {
				  ?>
				  <option value="<?php echo $i;?>"><?php echo $i;?></option>
                 
				   <?php 
				  }
				  ?>
				  
                      </select>
							
							</span>
							
							
							<span><label>Security Code:</label>
						 
							
							 <input type="text"  data-name="CVV"  id="CVV" size="5" maxlength="5" name="CVV" required=""  class="form_field security_code w-input">
							
							
							</span>
							
							
							 
							 
							
							
							<div class="clearfix"></div>
						</li>
						
						</span>
						
                        <?php
						}
						?>
                        
					</ul>
				</div>
			</div>
	
				<!--<a href="#" class="continue button color">Place Order</a>-->
				
                 <?php
						if($client_detail['is_payment_enable']==1)
						{
						?>
                
                <span class="credit_card_class" style="display:block;">
				<input id="btn_payment" style="font-size:16px;" type="submit" name="delivery_form" class="continue button color submit" value="Submit Order" />
				</span>
                
                <?php
				}
				?>
				 
                 
                  <?php
						if($client_detail['is_payment_enable']==0)
						{
						?>
                 
                 <span class="billing_code_class" style="display:block;">
				
				
					<input onclick="ajax_submit_directly_disable_payment();"  style="font-size:16px;" type="button" id="btn_direct" name="delivery_form" class="continue button color" value="Submit Order" />
				</span>
                 
                 
                  <?php
				}
				?>
				 
                 
				
				<span class="billing_code_class" style="display:none;">
				
				
					<input onclick="ajax_submit_directly();"  style="font-size:16px;" type="button" id="btn_direct" name="delivery_form" class="continue button color" value="Submit Order" />
				</span>
				
				
		<script>
	 
function ajax_submit_directly_disable_payment()
{
	 var str = "";	
	 str += "payment=disable";
	
	$("#btn_direct").prop('disabled', false);
	
	//alert(bill_code);
	
	var url = "charge.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			location.reload();
			//window.location =  result;
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}
			
function ajax_submit_directly()
{
	
	
	
	 var str = "";	
	
	var department_code = $("#department_code").val();
	var business_reason = $("#business_reason").val();
	
	if(department_code=="")
	{
		alert("Please enter department code");
		return false;
	}
	
	 
	
	if(business_reason=="")
	{
		alert("Please enter business reason");
		return false;
	}
	
	
	 str += "action_billcode=bill";
	// str += "&bill_code="+bill_code;
	 str += "&department_code="+department_code;
	 str += "&business_reason="+business_reason;
	 	
	
	$("#btn_direct").prop('disabled', false);
	
	//alert(bill_code);
	
	var url = "charge.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			location.reload();
			//window.location =  result;
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}
	
function ajax_submit_directly_bk()
{
	var bill_code = $("#bill_code").val();
	
	$("#tmp_bill_code").val(bill_code);
	$("#direct_form").submit();
}

		
		</script>				
				 
				
			<div align="center" id="ajax_loader" style="display:none;">	<img src="images/loader.gif"> </div>
			
			<strong><span  style="color:red;" class="payment-errors"></span></strong>
			
			</form>
			
			<!--<form action="charge.php" id="direct_form" method="post">
				<input type="hidden" name="bill_code" id="tmp_bill_code" />
				<input type="hidden" name="action"  value="billcode" />
			</form>-->
			
			 <?php
		  
		    if(isset($_SESSION['error_payment']))
			{
				
				?> 
                
                <div style="color:red;"> <?php echo $_SESSION['error_payment'];?></div>
            
            <?php
				unset($_SESSION['error_payment']);
			
		   }
			?>
			

		</div>
		<!-- Checkout Content / End -->




</div>
<!-- Container / End -->

<div class="margin-top-30"></div>
 

	  
 
<?php
//pr_n($_SESSION);

//session_destroy();
?>


<?php include("footer.php");?>

 
  <?php 
		  include_once("javascript_code.php");
		  ?>

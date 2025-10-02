<?php
include("setting.php");

/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/
$client_setting = get_client_setting($CID);

//pr_n($client_setting);
// $client_setting['is_enable_sale'];
//echo $client_setting['percentage_off'];

$shop_template = $client_setting['shop_template'];



?>


<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
<section class="titlebar">
	<div class="container">
		<div class="sixteen columns">
			<h2>Shopping Cart</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Shopping Cart</li>
				</ul>
			</nav>
		</div>
	</div>
</section>


<div class="container cart">

 <?php
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			
			}else{
			 ?>

				<h3 style="margin-top:-40px; margin-bottom: 10px;" align="center"><strong >Your shopping cart is empty.</strong></h3>

<?php
}
?>
<?php include_once("sale_item_discount_top_message.php");?>


	<div class="sixteen columns">
		
		<!-- Cart -->
		
			<form action='#' name="update_cart_form" id="update_cart_form">
			
			<span class="updated_cart_item">
		
		
		 <?php
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			 ?>
			 	
				<table class="cart-table responsive-table">

					<tr>
						<th>Item</th>
						<th>Description</th>
					 
						
						<th>Price</th>
						<th>Quantity</th>
						<th>Total</th>
						<th></th>
					</tr>
					
			 
			 
			 
			<?php 
	$cart_total = 0;
	$total_sale_tax = 0  ; 
	$customization_total_price = 0 ;
	//pr_n($_SESSION);
	
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
	{
	
	
	/*echo $item_id_sess ;
	pr_n($qty_sess);
	
	die;*/
	
		foreach($qty_arr as $set_key=>$qty_sess)
		{
	
	
		if($qty_sess ==0)
		{
			continue;
		}
		
		
	
	
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
				
				
				$item_detail['Price'] = $price ;
				
				
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
		
		$cart_item_total = $price * $qty_sess ;
		$single_item_total = $price * $qty_sess ;
		
		
		/*$cart_item_total = $price * $qty_sess ;
			$single_item_total = $price * $qty_sess ;*/
		
		
		
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
			 
			<tr id="item_id_<?php echo $item_detail['ID'] ; ?>">
				
				<td><img style="width:80px; height:80px;" src="<?php echo $item_image;?>" alt=""/></td>
				
				<?php
				$gift_price_display = '';
				if(isset($_SESSION['gift_card_is_gift_card_item'][$item_detail['ID']][$set_key]))
				{
					$gift_price_display = "$".number_format($item_detail['Price'])." ";
				}
				?>
				
				<td class="cart-title"><a href="#"><?php echo $gift_price_display;
				 $item_title = toSafeDisplay_edit_time_shop($item_detail['item_title']) ; 
				 echo  utf8_encode($item_title);
				 ?></a>
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
				?>
				
				
				
				<?php
					
					if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
					{
						$custom_name_price = $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Name ($$custom_name_price): ";
						echo  $_SESSION['custom_name_tmp'][$item_id][$set_key] ;
						
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
						
						$customization_total_price += $custom_number_price ;
						
						if($_SESSION['CID']==120)
						{
							$total_sale_tax += ($custom_number_price * 7.25) / 100 ;
						}
						
					}
				?>
				
				
				
				
				
				</td>
				
				
				
				 
		 
				
				
				<td>$<?php echo $price;?></td>
				
				<td>
					
					 	<div  class="qtyminus" onClick="minus_descrease('<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>');  "></div> 
						
						
						<input  type='text' id="<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>" name="<?php echo $item_detail['ID'] ; ?>[<?php echo $set_key;?>]" value='<?php echo $qty_sess;?>' class="qty" />
						
					
						 <div class="qtyplus" onClick="add_increase('<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>'); "></div> 
					
				</td>
				
				<td class="cart-total">$<?php echo $cart_item_total;?></td>
				
				<td><a href="javascript:void(0);" onClick="ajax_remove_item_from_shop_cart_page('<?php echo $item_detail['ID'] ; ?>','<?php echo $set_key;?>');  " class="cart-remove"></a></td>
				
				
			</tr>

		<?php
		}
	}
	?>
	
	 
			 

			</table>
				
			
				<?php
			} ?>		
				 
		
		 
	 
			
		</span>
		
				<input type="hidden" id="item_id_val" value="">
		
				<input type="hidden" name="action" value="update_cart">
			
			</form>
			
		
			
			<table class="cart-table bottom">

				<tr>
				<th>
					<!--<form action="#" method="get" class="apply-coupon">
						<input class="search-field" type="text" placeholder="Coupon Code" value=""/>
						<a href="#" class="button gray">Apply Coupon</a>
					</form>-->

					<div class="cart-btns">
						
						<?php
			
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			
			
			?>
            
            <?php
			if($shop_template=="Dealer_Tire_Custom_Shop")
			{
			?>
            
            <a onclick="open_dealer_code_modal();" class="button cart-btns">Proceed to Checkout</a>
            
            <?php
			}else if($CID==60)
			{
				// echo "country fair";
			?>
            	 <a onclick="open_store_number_modal();" class="button cart-btns">Proceed to Checkout</a>
           
           
             <?php
			}else if($CID==58)
			{
				// echo "country fair";
			?>
            	 <a onclick="open_store_number_modal();" class="button cart-btns">Proceed to Checkout</a>
            
            <?php
			
			}else{
			?>
            
			<a href="checkout-billing-details.php?#order_details" class="button cart-btns">Proceed to Checkout</a>
            
            <?php
			}
			?>
			
			<a href="index.php"  class="button cart-btns">Continue Shopping</a>	
			
			<!--<a href="javascript:void(0);"  onClick="ajax_update_cart();" class="button cart-btns">Update Cart</a>	-->	
			
			<?php
			}else{
			?>
			
			<a class="button gray cart-btns inactive_btn">Proceed to Checkout</a> 
			
			<a href="javascript:void(0);"  onClick="ajax_update_cart();" class="button gray cart-btns inactive_btn">Update Cart</a> 
			
			<?php
			}
			?>
						
						
					</div>
				</th>
				</tr>

			</table>
	</div>

<?php
$cart_total = number_format($cart_total,2);
	
	
$sales_tax	= 0 ;
	
if(isset($_SESSION['billing_form']['shipping_state']) and $_SESSION['billing_form']['shipping_state']=='PA')
{
	$shipping_state = $_SESSION['billing_form']['shipping_state'] ;
	$shipping_zip = $_SESSION['billing_form']['shipping_zip'] ;
	
	$sql_get_exits = "select * from pa_zipcodes where  zip_code = '$shipping_zip'
		 and state_name = '$shipping_state' ";
		 
	$rs_exits = mysql_query( $sql_get_exits );	
	
	if(mysql_num_rows($rs_exits)>0 and $total_price>0)
	{
		 //$sales_tax =  5.94 ; // 5.94 , it is salex tax on PA state zip code.
		 
		  $sales_tax =  0 ;
		
		 
	}	
	
	
}


$order_total = "";
	
$order_total = $cart_total + $delivery_charge + $sales_tax ;
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
		$delivery_charge = 10 ;
	}
	
	 
		
}

$delivery_charge = number_format($delivery_charge,2);	

?>


	
	
	<div class="eight columns cart-totals">
		<h3 class="headline">Cart Totals</h3><span class="line"></span><div class="clearfix"></div>

		<table class="cart-table margin-top-5">

			<?php
			if(isset($customization_total_price) and $customization_total_price > 0) 
			{
			?>
            <tr>
				<th>Customizations </th>
				<td><strong>$<span class="ajax_cart_total"><?php echo number_format($customization_total_price,2);?></span></strong></td>
			</tr>
            
            <?php
			}
			?>
            
            <tr>
				<th>Cart Subtotal</th>
				<!--<td><strong>$<span class="ajax_cart_total"><?php echo $cart_total;?></span></strong></td>-->
				
				<td><strong>$<span class="ajax_total_price"><?php echo $cart_total;?></span></strong></td>
			</tr>


			<?php
					if($delivery_charge>0)
					{
					?>
							
					<!--<tr>
						<th>Shipping & Handling</th>
						<td><strong> $<?php echo $delivery_charge;?></strong></td>
						
					</tr>-->
							
					<?php
					}
					?>
					
					
					 
			
			<?php
					if($total_sale_tax>0)
					{
						$total_sale_tax = number_format($total_sale_tax,2);
					?>
					
					
						<tr>
							<th>Salex Tax</th>
							<td><strong> $<?php echo $total_sale_tax;?></strong></td>
							
						</tr>	
					<?php
					}
					?>
					
					

			<tr>
				<th>Order Total</th>
				<td>
                
                <strong class="order_total_td">$<span class="ajax_total_price"><?php echo $cart_total;?></span></strong>
                
                </td>
                
                <?php 
					//echo	calculate_discount_client_setting($cart_total,$CID); 
				//pr_n($_SESSION);
				?>
                
			</tr>
			
			
			
			<tr>
				<th></th>
				<td style="color:red;">
				<span class="sale_discount_td"></span>
                </td>
               
			</tr>
			
			

		</table>
		
		

		
		
		<br>
		
		<!-- <a href="#" class="calculate-shipping"><i class="fa fa-arrow-circle-down"></i> Calculate Shipping</a> -->
		
	</div>

</div>

<div class="margin-top-40"></div>


		



<?php
//pr_n($_SESSION);

//session_destroy();
?>

<?php include("fancybox_javascript.php");?>

<?php include("footer.php");?>


<style>

a.cart-remove {
    background: #df2727;
    color: #fff;
}
</style>
<?php
session_start();
include_once("include/db.php");
include_once("shop_common_function.php");
$action = '';
if(isset( $_POST['action'] ))
{
	$action = $_POST['action'] ;
}

$CID = $_SESSION['CID'] ;

?>

  
	 

			<table class="cart-table responsive-table">

		 <?php
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			 ?>
	

			<tr>
				<th>Item </th>
				<th>Description</th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Total</th>
				<th></th>
			</tr>
 
			 
			<?php 
	$cart_total = '';
	
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
	foreach($qty_arr as $set_key=>$qty_sess)
	{
		if($qty_sess ==0)
		{
			continue;
		}
	
	
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
				// $item_detail['Price']  = get_item_price_by_size_new($size,$item_id);
				
				
				$is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					$item_detail['Price'] = get_item_sale_price_by_size_new($size,$item_id);
				}else{
					$item_detail['Price'] = get_item_price_by_size_new($size,$item_id);
				}
				
				
				
				  
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
			 
			<tr id="item_id_<?php echo $item_detail['ID'] ; ?>">
				<td><img style="width:80px; height:80px;"src="<?php echo $item_image;?>" alt=""/></td>
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
						
					}
				?>
				
				<?php
					if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
					{
						$custom_number_price = $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
						echo "<br/>Custom Number ($$custom_number_price): ";
						echo  $_SESSION['custom_number_tmp'][$item_id][$set_key] ;
						
					}
				?>
				
				
				
				</td>
				<td>$<?php echo $price;?></td>
				<td>
					
						<div class="qtyminus" onClick="minus_descrease('<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>'); "></div>
						<input type='text' id="<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>" name="<?php echo $item_detail['ID']; ?>[<?php echo $set_key;?>]" value='<?php echo $qty_sess;?>' class="qty" />
						<div class="qtyplus" onClick="add_increase('<?php echo $item_detail['ID'] ; ?>_<?php echo $set_key; ?>');  "></div>
					
				</td>
				<td class="cart-total">$<?php echo $cart_item_total;?></td>
				<td><a href="javascript:void(0);" onClick="ajax_remove_item('<?php echo $item_detail['ID'] ; ?>','<?php echo $set_key;?>');" class="cart-remove"></a></td>
			</tr>

		<?php
		}
		
		}
	
	?>
	
	
	<?php
	}?>	 
			 

			</table>
			 

			 
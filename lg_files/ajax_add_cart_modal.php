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

$item_id = $_POST['item_id'];
?>

  
	 

			<table class="cart-table responsive-table">

		 <?php
			 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
			{
			 ?>
	

			<tr>
				<th>Item</th>
				<th><!--Description--></th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Total</th>
				<th></th>
			</tr>
 
			 
			<?php 
	$cart_total = '';
	
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_sess)
	{
	
		$item_detail = get_item_detail_by_item_id($item_id_sess);
				
				//pr_n($item_details);
			
		if($item_detail['ID']!=$item_id)
		{
			Continue;
		}	
				
				
		$item_image  = ''; 
		if($item_detail['ImageFile']!="")
		{
			$item_image = "pdf/$CID/".$item_detail['ImageFile'];
		}
		
		$price = $item_detail['Price'];		
		$cart_item_total = $price * $qty_sess ;
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		$cart_item_total = number_format($cart_item_total,2);
		$cart_total = number_format($cart_total,2);
		
		
	?>
			 
			<tr id="item_id_<?php echo $item_detail['ID'] ; ?>">
				<td><img style="width:80px; height:80px;"src="<?php echo $item_image;?>" alt=""/></td>
				<td class="cart-title"><a href="#"><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></a></td>
				<td>$<?php echo $price;?></td>
				<td>
					
						<div class="qtyminus" onClick="minus_descrease('<?php echo $item_detail['ID'] ; ?>');"></div>
						<input type='text' id="<?php echo $item_detail['ID'] ; ?>" name="<?php echo $item_detail['ID'] ; ?>" value='<?php echo $qty_sess;?>' class="qty" />
						<div class="qtyplus" onClick="add_increase('<?php echo $item_detail['ID'] ; ?>');"></div>
					
				</td>
				<td class="cart-total">$<?php echo $cart_item_total;?></td>
				<td><a href="javascript:void(0);" onClick="ajax_remove_item('<?php echo $item_detail['ID'] ; ?>');" class="cart-remove"></a></td>
			</tr>

		<?php
		}
	
	?>
	
	
	<?php
	}?>	 
			 

			</table>
			
				<div  align="center"><a href="javascript:void(0);" onClick="close_fancybox();">Close Window</a></div>
			
			<input type="hidden" id="modal_need_to_close" value="Yes" />
			 

			 
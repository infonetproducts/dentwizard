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


<?php

if($action =="header_cart_item")
{

	if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
		{
		
		$total_items = count($_SESSION['Order']);	
		
		$lable = "item";
		
		if($total_items>1)
		{
				$lable = "items";
		}
		
	?>
	
	<div class="cart-amount">
		<span><?php echo $total_items ; ?> <?php echo $lable;?> in the shopping cart</span>
	</div>
	
	
		<ul>
		
	<?php 
	
	
	
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
		
		
	
		
		
			
		if($item_detail["item_price_type"]=="multi_quantity_price")
		{
			$price = get_default_price_item($item_detail['ID']);
		
		}
		
		$item_id = $item_detail['ID'] ;
		 
		
		//pr_n($_SESSION);
		
		//echo $_SESSION['Order'][$item_id]['size'];
		if(isset($_SESSION['size_item'][$item_id][$set_key]))
		{
			 $size = $_SESSION['size_item'][$item_id][$set_key] ;
			
			
			
			if($size!="")
			{
				//$price = get_item_price_by_size_new($size,$item_id);
				
				$is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					// $price = get_item_sale_price_by_size_new($size,$item_id);
					
					$price = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
					
				}else{
					$price = get_item_price_by_size_new($size,$item_id);
				}
				
				
				
				$item_detail['Price'] = $price ;
				  
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
		
		$price = number_format($price,2);
				
	?>
			<li>
				<a href="#"><img src="<?php echo $item_image; ?>" alt="" /></a>
				<a href="#"><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></a>
				<span><?php echo $qty_sess;?> x $<?php echo $price;?></span>
				<div class="clearfix"></div>
			</li>
	
	<?php
		}
	}
	?>	 
		</ul>
		
	<?php
	}
	

}

?>	
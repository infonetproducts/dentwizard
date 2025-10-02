<?php
session_start();
include_once("include/db.php");
include_once("shop_common_function.php");
include_once("../item_group_option_function.php");
include_once("../inventory_item_cat_allow_image_display.php");

$action = '';
if(isset( $_POST['action'] ))
{
	$action = $_POST['action'] ;
}

$CID = $_SESSION['CID'] ;

?>


<?php

if($action =="header_cart_item_new")
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
	
	
	
	foreach($_SESSION['Order'] as $formid=>$qty_sess)
	{
		 $item_id_sess = $_SESSION['Order_item_id'][$formid];
	
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
		
		$item_detail = get_item_detail_by_item_id($item_id_sess);
				
				//pr_n($item_details);
				
		
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
    }	
				
				
				
		$item_image  = ''; 
		if($item_detail['ImageFile']!="")
		{
			$item_image = "pdf/$CID/".$item_detail['ImageFile'];
		}
		
	 
		$item_id = $item_detail['ID'] ;
		 
		
		 
		
		
		$price = $item_detail['Price'];
		
		$price = number_format($price,2);
		
		
		
		
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
			?>
            
            <li>
				  <a href="#"><?php echo $item_detail['FormID']  ; ?></a>
                <!--<a href="#"><img src="<?php echo $item_image; ?>" alt="" /></a>-->
				<a href="#"><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></a>
				<span><?php echo $qty_custom;?> x $<?php echo $price;?></span>
				<div class="clearfix"></div>
			</li>
            
            
            
            <?php
			
			}
			
		}
		
	}else{		
		
		
		
		
				
	?>
			<li>
				<!--<a href="#"><img src="<?php echo $item_image; ?>" alt="" /></a>-->
                
                
                <a href="#"><?php echo $item_detail['FormID']  ; ?></a>
                
              
                
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
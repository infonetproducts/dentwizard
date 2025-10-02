<?php
include_once("include/db.php");
include_once("shop_common_function.php");
		
		$cat_id = $_POST['cat_id'] ;
		$CID = $_POST['cid'] ;
		
		$item_list = get_item_list_by_category_id($CID , $cat_id) ;
		
		?>
		
		<h3 class="headline"><?php echo get_category_name($CID,$cat_id);?></h3><span class="line"></span>
		
		<?php
		
		if(!empty($item_list))
		{
		
			$item_counter = 1 ;
		
			foreach($item_list as $item_detail )
			{
				$item_image  = ''; 
				if($item_detail['ImageFile']!="")
				{
				
					$item_image = "pdf/$CID/".$item_detail['ImageFile'];
				
				}
		
		
		$price = $item_detail['Price'];
		
		
		if($item_detail["item_price_type"]=="multi_quantity_price")
		{
			//$price = get_default_price_item($item_detail['ID']);
		
		}
		
		
		
		$price = number_format($price,2);
		
		?>
		 
		<div class="four shop columns">
			<figure class="product">
				<div class="mediaholder">
				<!--href="variable-product-page.html"-->
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>">
						<img alt="" src="<?php echo $item_image;?>"/>
						<div class="cover">
							<img alt="" src="<?php echo $item_image;?>"/>
						</div>
					</a>
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>" class="product-button"><i class="fa fa-shopping-cart"></i> QUICK LOOK </a>
				</div>

				<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>">
					<section>
						<span class="product-category"><?php echo $item_detail['FormID'] ; ?></span>
						<h5><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></h5>
						<span class="product-price">$<?php echo $price;?></span>
					</section>
				</a>
			</figure>
		</div>

 
 
		 <?php
			}
		
		}
		?>
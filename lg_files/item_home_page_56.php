 <div class="twelve columns">
 <div class="products">
 		<span id="ajax_item_list">

	<?php

		if(isset($_GET['catid']) and $_GET['catid']!="")
		{
			$cat_id = $_GET['catid'];
		}else{

		    
			 $sql_is_featured_homepage_cat = "select ID from Category where is_featured_homepage = 1 and CID = '$CID'  ";
			list($cat_id) = mysql_fetch_row(mysql_query($sql_is_featured_homepage_cat));
			//echo $cat_id ; 
			
			if($cat_id=="")
			{
			
					$cat_id = 847 ;
				
					if($_SESSION['CID']==56)
					{
						$cat_id = 847 ; 
						
					}
					
					if($_SESSION['CID']==58)
					{
						$cat_id = 881 ; 
						
					}
		}else{
			// echo 'I am here';
			$_GET['catid'] = $cat_id ; 
		}


		
		}
		
		$is_parent_category = is_parent_category($CID,$cat_id) ;
		
		
		if($is_parent_category==1)
		{
		
		?>
		
		<h3 class="headline" id="all"><?php echo get_parent_category_name($CID,$cat_id);?>: ALL</h3><span class="line"></span>
		<?php
		}else{
		?>
		<h3 class="headline"><?php echo get_category_name($CID,$cat_id);?></h3><span class="line"></span>
		<?php
		}
		?>
		
		<?php
		
		if(isset($_POST['str_search']) and $_POST['str_search']!="")
		{
			$str = $_POST['str_search'] ;
			
			$item_list = search_item($CID , $str ) ;
			
		}else{
		
			$item_list = get_item_list_by_category_id($CID , $cat_id) ;
			
		}
		
		
		if($is_parent_category==1)
		{
			// if parent category then we need to get all items from all sub category 
			
			$sub_category_list = get_all_sub_category_of_parent_category($CID , $cat_id) ;
			
			if(!empty($sub_category_list))
			{
				foreach($sub_category_list as $sub_cat_id=>$sub_cat_name)
				{
					$item_list_all[] = get_item_list_by_category_id($CID , $sub_cat_id) ;
				}
				
				$item_list = map_array($item_list_all) ;
			}
			
			//pr_n($item_list_all);
		}
		
//pr_n($item_list);
		
		
		
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

		<!-- Product #1 -->
		
		<div class="four columns">
			<figure class="product">
					<div class="mediaholder">
				<!--href="variable-product-page.html"-->
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>">
 						<img alt="" src="<?php echo $item_image;?>"/>
  						<div class="cover">
							<img alt="" src="<?php echo $item_image;?>"/>
						</div>
						
					</a>
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>"   class="product-button"><i class="fa fa-shopping-cart"></i> QUICK LOOK </a>
					
					
					
				</div>
				
			<!--				
						<a class="modalbox" href="#inline">click to open</a>			
			-->
				
				

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

</span>
 </div>
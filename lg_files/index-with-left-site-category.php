<?php
include("setting.php");

?>
<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
 <?php
	if($CID==56)
	{
	?>
<section class="parallax-titlebar fullwidth-element"  data-background="#000" data-opacity="0.45" data-height="160">
	
	
	
	<img src="images/titlebar_bg_01.jpg" alt="" />
	

	
	<div class="parallax-overlay"></div>
	<div class="parallax-content">
		<h2>Apparel Shop <span>Fort LeBoeuf School District</span></h2>

		<nav id="breadcrumbs">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Shop</a></li>
				<li>Products</li>
			</ul>
		</nav>
	</div>

</section>


	<?php
	}
	?>
	
	
<div class="container">

<?php include("left_side_category.php");?>


	<!-- Content
	================================================== -->
	<div class="twelve columns" style="display:none;">
		<select class="orderby">
			<option>Default Sorting</option>
			<option>Sort by Popularity</option>
			<option>Sort by Newness</option>
		</select>
	</div>

	<!-- Products -->
	<div class="twelve columns products" >
	
	

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
		
//
		
		$key_items = array();
		
		if(!empty($item_list))
		{
			 
		
			$item_counter = 1 ;
		
			foreach($item_list as $item_detail )
			{
				
				// start code for remove duplicate items
				if(empty($key_items))
				{
					$key_items[] = $item_detail['ID'] ;
					
				}else{
					
					if(in_array($item_detail['ID'],$key_items))
					{
						continue ;
						
					}else{
						$key_items[] = $item_detail['ID'] ;
					}
					
				}
				
				// end code for remove duplicate items
				
				
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
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>&catid=<?php echo $_GET['catid'];?>">
 						<img alt="" src="<?php echo $item_image;?>"/>
  						<div class="cover">
							<img alt="" src="<?php echo $item_image;?>"/>
						</div>
						
					</a>
					<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>&catid=<?php echo $_GET['catid'];?>"   class="product-button"><i class="fa fa-shopping-cart"></i> QUICK LOOK </a>
					
					
					
				</div>
				
				
			 
			<!--				
						<a class="modalbox" href="#inline">click to open</a>			
			-->
				
				

				<a href="item-detail.php?id=<?php echo $item_detail['ID'] ; ?>">
					<section>
						<span class="product-category"><?php echo $item_detail['FormID'] ; ?></span>
						<h5><?php $item_title = toSafeDisplay_edit_time_shop($item_detail['item_title']) ;
						
						echo  utf8_encode($item_title);
						
						 ?></h5>
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
		
		<span style="display:none;" id="ajax_loader"><img src="images/ajax-loading.gif" /></span>
		
		
		<div class="clearfix"></div>


		 
		<div class="pagination-container" style="display:none;" >
			<nav class="pagination">
				<ul>
					<li><a href="#" class="current-page">1</a></li>
					<li><a href="#">2</a></li>
					<li><a href="#">3</a></li>
				</ul>
			</nav>

			<nav class="pagination-next-prev">
				<ul>
					<li><a href="#" class="prev"></a></li>
					<li><a href="#" class="next"></a></li>
				</ul>
			</nav>
		</div>

	</div>

</div>

<div class="margin-top-15"></div>






<?php
//pr_n($_SESSION);
//session_destroy();
?>


<?php include("footer.php");?>

<?php  //include("fancybox_javascript.php");?>


<?php


?>

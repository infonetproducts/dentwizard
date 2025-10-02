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
			echo '<span style="display:none">'.$cat_id.'</span>' ; 
			
			if(isset($default_cat) and $default_cat!="")
			{
				$cat_id = $default_cat ; // this is user category , first category of user.
			}
			
			
			 // 569 , 788 , 1181 , 781
			 
			 
			 if($cat_id!="")
			 {
			 	$custom_cat_array = get_all_category_for_custom();
			 }
			 
			// pr_n($custom_cat_array);
			
			/*if($cat_id=="")
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
			 
				$_GET['catid'] = $cat_id ; 
			}
*/

		
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
		<div class="clear"></div> 
		<div class="row-1">
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
		
		<div class="three columns p-list">
			<figure class="product">
					<div class="mediaholder">
				<!--href="variable-product-page.html"-->
					<a  href="item-detail.php?id=<?php echo $item_detail['ID'];?>&catid=<?php echo $_GET['catid'];?>">
 						<img alt="" src="<?php echo $item_image;?>"/>
  						<div class="cover">
							<img alt="" src="<?php echo $item_image;?>"/>
						</div>
						
					</a>
					<a  href="item-detail.php?id=<?php echo $item_detail['ID'];?>&catid=<?php echo $_GET['catid'];?>"  class="product-button"><i class="fa fa-shopping-cart"></i> VIEW DETAILS </a>
					
					
					
				</div>
				
			<!--				
						<a class="modalbox" href="#inline">click to open</a>			
			-->
				
				<?php
				$item_id = $item_detail['ID'] ; 
				  $is_apply_sale_price = check_sale_date($item_id);
				?>
				

				 <a href="item-detail.php?id=<?php echo $item_detail['ID'];?>&catid=<?php echo $_GET['catid'];?>">
					<section>
						<span class="product-category"><?php echo $item_detail['FormID'] ; ?></span>
						<h5><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></h5>
						
						<?php
				 if($is_apply_sale_price==1)
				 {
				 	
					 $new_price = calculate_percentage_item_price($item_detail["ID"]);
				 
				 ?>
						  
						 
						 
						 <span class="product-price-discount">$<span id="ajax_p"><?php echo number_format($item_detail["Price"],2);?></span><i>$<span id="ajax_sale_price"><?php echo number_format($new_price,2);?></span>
						 
						 
						 
				 <?php
				 }else{
				 ?>
						
						<span class="product-price">$<?php echo $price;?></span>
						
				<?php
				}
				?>		
						
					</section>
				</a>
				

			</figure>
		</div>
		
		
		 
	
	
	 <?php
			}
		
		}
		?>
        
        
<script>
 function custom_item(id)
{
//	alert(id);
	
	var url = "ajax_check_custom_item_category.php";
		
		var str = "";	
		str = "item_id="+id;
		 
		 var request = $.ajax({
							url: url,
							type: "POST",
							data: str
							
						});
						
			request.done(function(msg) {
				
			    if (~msg.indexOf("2"))
			   {
			   
			   error_item_popup();
			   
			    //alert("Sorry, you cannot order items from more that one category / brand at the same time. Please complete the order for the items that are currently in your cart and then start a new order.");
			   	return false;
			   }else{
			   		self.location.href='../custom_order/customize.php?id='+id;
				//alert('here');
				console.log(msg);
					return false;
			   }
				
			});
			
			request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
				return false;
			});
	
	
	return false;

	 
			
			
	
}

function custom_item_for_other_item(obj)
{

	id = $(obj).attr('id_item');
	
//	alert(id);
	
//	var url = "ajax_check_custom_item_category.php";
	var url = "ajax_check_custom_item_category_for_other_item.php";
		
		var str = "";	
		str = "item_id="+id;
		 
		 var request = $.ajax({
							url: url,
							type: "POST",
							data: str
							
						});
						
			request.done(function(msg) {
				
			   if (~msg.indexOf("2"))
			   {
			   
			    $(obj).val('');
			   
			    alert("Sorry, you cannot order items from more that one category / brand at the same time. Please complete the order for the items that are currently in your cart and then start a new order.");
			   	return false;
			   }else{
			   		//self.location.href='custom/customize.php?id='+id;
				//alert('here');
				console.log(msg);
					return false;
			   }
				
			});
			
			request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
				return false;
			});
	
	
	return false;

	 
			
			
	
}	

</script>

<!--  <a class="popup-with-zoom-anim button color" id="btn_test" onclick="error_item_popup();" >Hello</a>
-->
 
        
</div>
</span>
 </div>
 </div>
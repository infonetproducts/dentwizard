<style>
table.cart-table td {
	padding: 0px !important;
	border-bottom: #eee 1px solid;
	border-top: none;
	border-right: none;
	vertical-align: middle;
}

</style>
	
<div class="twelve columns">
 <div class="products">
 
 
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
		
		
		
		if(isset($_POST['str_search']) and $_POST['str_search']!="")
		{
		?>
        
       <!-- <h3 class="headline" id="all">Below Item Found ALL</h3><span class="line"></span>
        -->
        <?php
		
		}else{
		
		
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
	   }
	   ?>    

		<!-- Cart -->
		<table class="cart-table responsive-table">

			<tr>
                <th width="15%">Item #</th>
                <th width="30%">Item Title</th>
                <th width="5%" >Inventory</th>
                <th width="5%">Price</th>
                
				<?php if($_SESSION['CID']==42){?>
				<th width="5%">Points Plus Value</th>
				<?php } ?>
				
                <th width="5%"></th>
                
                <?php
                if($is_view_only!=1)
                {
                ?>
                	<!--<th width="2%">Quantity</th>-->
                	<th width="15%"></th>
                
                <?php
                }else{
                ?>
              		<th width="15%"></th>
                <?php
                }
                ?>
			</tr>
					
			 

		 <?php
										if(isset($_POST['str_search']) and $_POST['str_search']!="")
		{
			$str = $_POST['str_search'] ;
			
			//$item_list = search_item($CID , $str ) ;			
			
			$item_list = search_item_new($CID , $str ) ;	
			
			//echo "<pre>";
			//print_r($item_list); die;
			
			
		}else{
		
			$item_list = get_item_list_by_category_id($CID , $cat_id) ;
			
		}
		
		
		if($is_parent_category==1 and !isset($_POST['str_search']))
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
                                        
                                        
                                        
											<tr>
												<td><?php echo $item_detail['FormID'] ; ?></td>
												<td  class="cart-title" ><?php  toSafeDisplay_edit_time_shop($item_detail['item_title']) ;
												$item_title = stripslashes($item_detail['item_title']);
												echo  utf8_encode($item_title);
												
												 ?></td>
												<td align="center"><?php echo $item_detail['InventoryQuantity'];?></td>
												<td align="center"><?php echo number_format($item_detail["Price"],2);?></td>
												
												<?php if($_SESSION['CID']==42){?>
												
												<td ><?php echo $item_detail['point_value'];?></td>
												
												<?php } ?>
												 
												<td >
                                                


                                                <?php
												$pdffolder = "pdf/$CID";
												
												// echo "Hello";
												if($item_detail['ImageFile'] && is_file("$pdffolder/$item_detail[ImageFile]")) 
												{
												
													echo <<<EOM
	<a target="_blank"  href="$pdffolder/$item_detail[ImageFile]"><i class="fa fa-image"></i></a> 
EOM;

}
												?>
                                                
                                                
                                                </td>
												
                                                
                                                
                                                
                                                <?php
global $is_view_only; 
												if($is_view_only!=1)
												{
												?>
													<?php
                                                    if($item_detail["item_type"]!="custom")
                                                    {
                                                    ?>
                                                   <!-- <td align="center" style="margin-top:5px;">
                    <input class="item_input_box" size="5" onkeyup="set_item_val('<?php echo $item_detail[ID];?>',this.value,'<?php echo $item_detail["item_title"];?>');" item_title="<?php echo $item_detail["item_title"];?>"  maxlength="4" type="text" id="kc_qty_item_id_<?php echo $item_detail[ID];?>" value="" />
                                                    </td>-->
                                                    
                                                    <?php
                                                    }
                                                    ?>
                                                    
                                                
                                                <td  >
                                                
                                                
                                                <?php
												 
												if($item_detail["item_type"]=="custom")
												{
												
												?>
                                                 <a href="#" onclick="custom_item('<?php echo $item_detail[ID];?>');">Customize</a>
                                                
                                                <?php
												
												}else{
												?>
                                                
                                   <!--             <button onclick="set_item_id_add_new('<?php echo $item_detail['ID'];?>');" type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#kt_modal_5" item_id="<?php echo $item_detail['ID'];?>">Add to Cart</button>-->
                                             
                                        <!--     <a data-toggle="modal" onclick="set_item_id_add_new('<?php echo $item_detail['ID'];?>');" href="javascript:void(0);"  item_id="<?php echo $item_detail['ID'];?>" class="button color">Add to Cart</a>-->
                                          
                                          
                                         <!-- <a item_id="<?php echo $item_detail['ID'];?>"  onclick="set_item_id_add_new('<?php echo $item_detail['ID'];?>');" class="popup-with-zoom-anim button color" href="#small-dialog" >Add to Cart</a>-->
                                            
                                            
                                             <!--<a  style="margin-top:10px;" item_id="<?php echo $item_detail['ID'];?>"  onclick="set_item_id_add_new('<?php echo $item_detail['ID'];?>');" class="popup-with-zoom-anim button color"  > Add to Cart</a>--> 
                                
								<a  style="margin-top:10px;" href="item-detail.php?id=<?php echo $item_detail['ID'];?>&catid=<?php echo $_GET['catid'];?>" class="button color"  >Item Detail</a>
								
								                
                                                <?php
												}
												?>
                                                
                                                
                                                
                                                </td>
                                                
                                                <?php
												}
												?>
                                                
                                                
                                                
											</tr>
                                            
                                            <?php
			}
		
		}
		?> 

			</table>
            
               <br/>            <br/>

		</div></div> 
 
</div>
   
        <?php 
		//include_once("ajax_add_cart_grid_modal.php");
		include_once("ajax_add_cart_grid_modal_new.php");
		?>
 <?php include("footer.php");?>


<?php 
//  include("fancybox_javascript.php");
  include("fancybox_javascript_new.php");

		 

?>

<?php


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
			    alert("Sorry, you cannot order items from more that one category / brand at the same time. Please complete the order for the items that are currently in your cart and then start a new order.");
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

function custom_item_for_other_item(id_item)
{

	//id = $(obj).attr('id_item');
	
	id =  id_item ; 
	
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
			   
			   // $(obj).val('');
			   var input_id = "kc_qty_item_id_"+id;
			   console.log(input_id);
			   
			   //$("#"+input_id).val('');
			   
			   $(".item_input_box").val('');
			   
			   
			   
			   error_item_popup();
			   
			   // alert("Sorry, you cannot order items from more that one category / brand at the same time. Please complete the order for the items that are currently in your cart and then start a new order.");
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


// fa fa-caret-right




</script>

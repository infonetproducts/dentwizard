<style>
table.cart-table td {
	padding: 5px !important;
	border-bottom: #eee 1px solid;
	border-top: none;
	border-right: none;
	vertical-align: middle;
}

</style>

   <h2 align="center">Order Business Cards</h2>
   
<div style="padding:5px 20px 5px 20px;">
The card(s) that you have available for ordering are listed below.  To preview your card, click the view icon.
If you would like to make any changes to the information on your card, click the pencil. When you are ready to order, select your desired quantity and click the Continue button.
</div>

<div style="padding:5px;text-align:center;">
	<input type="button" value="Create New Business Card" onclick="self.location.href='bceditindex.php?id=0'" />
</div>
   
   
     
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
        
        <form method="post" id="business_form" action="itemsindex.php">
            <input type="hidden" name="a" value="additems">
            <input type="hidden" name="cats" value="4">
        
		<table class="cart-table responsive-table">

			<tr>
                <th width="20%">Item #</th>
                <th width="30%">Item Title</th>
                <th width="5%" align="center">Price</th>
                <th width="10%"></th>
                <th width="10%"></th>
                    
               <?php
                if($is_view_only!=1)
                {
                ?>
                	<!--<th width="10%">Quantity</th>-->
                    
                    <th width="20%">&nbsp;</th>
                <?php
                }?>                        
                
              
			</tr>
					
			 

		 <?php
										if(isset($_POST['str_search']) and $_POST['str_search']!="")
		{
			$str = $_POST['str_search'] ;
			
			//$item_list = search_item($CID , $str ) ;			
			
			$item_list = search_item_new($CID , $str ) ;	
			
			
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
				
				$row = $item_detail;
				$is_cubicle_item = $row['is_cubicle_item'];
				if($is_cubicle_item==1)
				{
					continue;
				}
				
				
				
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
												echo stripslashes($item_detail['item_title']);
												
												 ?></td>
												 
												<td  ><?php echo number_format($item_detail["Price"],2);?></td>
												 
												 
												<td >
                                                

<a  href="bceditindex.php?id=<?php echo $item_detail['ID'];?>"><i class="fa fa-edit"></i></a> 
&nbsp;
<!--<a href="javascript:if(confirm('Are you sure you want to delete that?')) self.location.href='bceditindex.php?a=del&id=<?php echo $item_detail['ID'];?>'"><i class="fa fa-trash-o"></i></a>
-->
<a href="javascript:void(0);" onclick="delete_business_card('<?php echo $item_detail['ID'];?>');" ><i class="fa fa-trash-o"></i></a>




                                                <?php
												$pdffolder = "pdf/$CID";
												
												// echo "Hello";
												if($item_detail['ImageFile'] && is_file("$pdffolder/$item_detail[ImageFile]")) 
												{
												
													echo <<<EOM
	&nbsp;&nbsp;<a target="_blank"  href="$pdffolder/$item_detail[ImageFile]"><i class="fa fa-image"></i></a> 
EOM;

}
												?>
                                                
                                                
                                                </td>
												
                                                
                                                
                                            	<td >  
                                                
                                                
                                                </td>
                                                
                                                 <?php
                if($is_view_only!=1)
                {
                ?><!--
                	<td>
                        <select onchange="set_item_val_business_card(this.value);" name="Quantity[<?php echo $item_detail['FormID'];?>]">
                            <option value="">-</option>
                             <option value="250">250</option>
                             <option value="500">500</option>
                          
                         </select>
                    
                    </td>-->
                    
                      <td style="margin-top:5px;">
                   
                   			 <!-- <a  style="margin-top:10px;" item_id="<?php echo $item_detail['ID'];?>"  onclick="set_item_id_add_new_buss('<?php echo $item_detail['ID'];?>');" class="popup-with-zoom-anim button color"  >Add to Cart</a> 
           -->    <a  style="margin-top:10px;" href="item-detail.php?id=<?php echo $item_detail['ID'];?>&catid=<?php echo $_GET['catid'];?>" class="button color"  >Item Detail</a>                                   
                      </td>
					  
					  
                    
                <?php
                }
				?>   
                                                 
                                                
                                                
											</tr>
                                            
                                            <?php
			}
		
		?>
        
        
       <!-- <tr>
        	<td colspan="6" align="right">
           		 <input id="btn_add_to_order" type="submit" value="Continue">
            </td>
        </tr>-->
        
        <?php
		
		}
		?> 

			</table>
            
     
       </form>
     
     
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
if(isset($_SESSION['b_modal_show']) and  $_SESSION['b_modal_show']==1)
{


?> 
<script>
modal_error_business_card_mix_item();
</script>

<?php
unset($_SESSION['b_modal_show']);

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


 



<?php 
//  include("fancybox_javascript.php");
  //include("fancybox_javascript_new.php");

		 

?>

<input type="text" name="" id="qty_kc_buss" value="" />

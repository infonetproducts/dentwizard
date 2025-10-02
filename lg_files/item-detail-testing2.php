<?php
ob_start();
include("setting.php");

if(isset($_GET['id']))
{
	$item_id = $_GET['id'] ;
}


$item_detail = get_item_detail_by_item_id($item_id) ;

$item_image  = ''; 
if($item_detail['ImageFile']!="")
{
	$item_image = "pdf/$CID/".$item_detail['ImageFile'];

}


if(empty($item_detail))
{
	header("location:index.php");
	die;
}


$item_category_detail = get_item_category_by_item_id($item_id) ;

//pr_n($item_category_detail);

?>



<?php include("header.php");?>



<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
 
 
<!-- Titlebar
================================================== -->
<section class="titlebar">
<div class="container">
	<div class="sixteen columns">
		<h2>Shop</h2>
		
		<nav id="breadcrumbs">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Products</a></li>
				<!--<li><a href="#">Men's Wear</a></li>-->
				<li><?php echo $item_category_detail["Name"];?></li>
			</ul>
		</nav>
	</div>
</div>
</section>


<div class="container">

<!-- Slider
================================================== -->
	<div class="eight columns" >
		<div class="slider-padding">
		
		
			 <div id="product-slider" class="royalSlider rsDefault">
				<img id="default_img"  src="<?php echo $item_image;?>"  />
			 </div>
			 
			 
			 
			 
			 
				<input type="hidden" id="default"  value="<?php echo $item_image;?>"  >
				
				<?php
				 $item_color_image_list = get_item_color_image($item_id) ;
				 //pr_n($item_color_image_list);
				 
				 
				
	
				if(!empty($item_color_image_list))
				{
					foreach($item_color_image_list as $key_image=>$color_image)
					{
					
					 $tmp_image = '';
					if($color_image!="")
					{
					
						$tmp_image = "pdf/$CID/".$color_image;
					
					}

							
				?>
				
					<input type="hidden" id="<?php echo $key_image;?>"  value="<?php echo $tmp_image;?>"  >
					
				<?php
					}
				}
				?>
				
			 
			 
			
			 
			 
			 <div class="clearfix"></div>
		</div>
	</div>
	
<?php
if($item_detail["item_price_type"]=="multi_quantity_price")
{
	//$item_detail["Price"] = get_default_price_item($item_detail['ID']);

}
?>	


<!-- Content
================================================== -->
	<div class="eight columns">
		<div class="product-page">
			
			<!-- Headline -->
			<section class="title">
				<h2><?php echo toSafeDisplay_edit_time_shop($item_detail["item_title"]);?></h2>
				 <h3><strong style="font:20px;">$<span id="ajax_p"><?php echo number_format($item_detail["Price"],2);?></span></strong></h3> 
				
				<!--<span class="product-price-discount"><i>$<span id="ajax_p"><?php echo number_format($item_detail["Price"],2);?></span></i></span>-->
				
				    <div class="rating five-stars">
						<div class=""><?php echo $item_detail['FormID'];?></div>
					</div>
				
			 
				
				
				<!--<span class="product-price-discount">$39.00<i>$29.00</i></span>-->
				
				

				<div class="reviews-counter" style="display:none;">
					<div class="rating five-stars">
						<div class="star-rating"></div>
						<div class="star-bg"></div>
					</div>
					<span>3 Reviews</span>
				</div>
				
			</section>

			<!-- Text Parapgraph -->
			<section>
				<p class="margin-reset">
				<?php
				
				$str = toSafeDisplay_edit_time_shop($item_detail["Description"]);
				
				$str=str_replace('\r\n','<br>',$str);
				
				echo $str=str_replace('\"','"',$str); 
				
				 ?>
				
				<!--Maecenas consequat mauris nec semper tristique. Etiam fermentum augue ac vulputate pulvinar. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque <a href="#">ThemeForest</a> arcu sed mollis. Nulla egestas nulla elit, eu condimentum diam fringilla blandit.--></p>

				<!-- Share Buttons -->	
			<!--	<div class="share-buttons">
					<ul>
						<li><a href="#">Share</a></li>
						<li class="share-facebook"><a href="#">Facebook</a></li>
						<li class="share-twitter"><a href="#">Twitter</a></li>
						<li class="share-gplus"><a href="#">Google Plus</a></li>
						<li class="share-pinit"><a href="#">Pin It</a></li>
					</ul>
				</div>
				-->
				
				
			<!--	<div class="a2a_kit a2a_kit_size_32 a2a_default_style">
<a class="a2a_dd" href="https://www.addtoany.com/share"></a>
<a class="a2a_button_facebook"></a>
<a class="a2a_button_twitter"></a>
<a class="a2a_button_google_plus"></a>
<a class="a2a_button_linkedin"></a>
</div>
<script async src="https://static.addtoany.com/menu/page.js"></script>
				-->
				<div class="clearfix"></div>

			</section>


			<!-- Variables -->
			
					<input type="hidden" value="<?php echo $item_detail['ID'];?>" id="current_item_id" name="current_item_id" />
					<?php
					
					//pr_n($item_detail);
					
				if($item_detail['item_price_type']=="multi_quantity_price")
				{
					//$price_multi = $item_detail['price_multi'] ;
					
					//$price_multi_str =  trim( $price_multi ) ;
					//$price_multi_arr = explode("\n",$price_multi_str);
		
					//pr_n($price_multi_arr);
					
					$item_size_list = get_item_size_drop_down_list($item_detail['ID']) ;
					
					
				?>
				
				

				
					
					<style>
.dropbtn {
    background-color: #3498DB;
    color: white;
    padding: 16px;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
    background-color: #2980B9;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f1f1f1;
    min-width: 160px;
    overflow: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 10000;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown a:hover {background-color: #ddd;}

.show {display: block;}


</style>

 
					<style>
.dropbtn_color {
    background-color: #3498DB;
    color: white;
    padding: 16px;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropbtn_color:hover, .dropbtn_color:focus {
    background-color: #2980B9;
}

.dropdown_color {
    position: relative;
    display: inline-block;
}

.dropdown-content_color {
    display: none;
    position: absolute;
    background-color: #f1f1f1;
    min-width: 160px;
    overflow: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 10000;
}

.dropdown-content_color a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown_color a:hover {background-color: #ddd;}

.show_color {display: block;}


</style>

<?php
if(!empty($item_size_list))
{

	$group_name = get_item_price_group_name($item_detail['ID']) ;

?>				
				
<div class="four alpha columns alp">
 <!-- <label >Size</label>-->
 <label ><?php echo $group_name; ?></label>

<input type="hidden" id="validation" value="Please select <?php echo $group_name; ?>" />
  
  
<div class="dropdown">
<!--<input type="submit" onclick="myFunction()" class="dropbtn" id="dropbtn" value="Please select size" />
-->



<input type="submit" onclick="myFunction()" class="dropbtn" id="dropbtn" value="Please select <?php echo $group_name; ?>" />


  <div id="myDropdown" class="dropdown-content">
    <a href="javasript:void(0);" 
	onclick="ajax_get_price_by_size('<?php echo $CID;?>','<?php echo $item_detail['ID'];?>','Please select <?php echo $group_name; ?>');">
	PLEASE SELECT <?php echo strtoupper($group_name); ?></a>
	
	
						<?php
						foreach($item_size_list as $k_size=>$v_price)
						{
							//$price_arr = explode(",",$price_str);
							$select_qty = '';
							
							//pr_n($price_arr);
							
						?>
						
	<a href="javascript:void(0);" onclick="ajax_get_price_by_size('<?php echo $CID;?>','<?php echo $item_detail['ID'];?>','<?php echo $k_size;?>');"><?php echo $k_size;?></a>
   
   
   	<?php
						}
						?>
  </div>
</div></div>	


 

<input type="hidden" name="size_temp" id="size_temp" value="" />

<?php
}
?>

<?php
	$item_color_list = get_item_color_drop_down_list($item_id) ;
	


if(!empty($item_color_list))
{
?>
<div class="four alpha columns alp">
 <label >Color</label>
<div class="dropdown_color">
<input type="submit" onclick="myFunction_color()" class="dropbtn_color" id="dropbtn_color" value="Please select color" />

  <div id="myDropdown_color" class="dropdown-content_color">
    <a href="javasript:void(0);" 
	onclick="select_color('Please select color','<?php echo $item_image;?>');">
	PLEASE SELECT COLOR </a>
	
	
						<?php 
				
					
					if(!empty($item_color_list))
					{
					
					 
						foreach($item_color_list as $k_color=>$v_price)
						{
							
							
							$img_tm = '';
							if(isset($item_color_image_list[$k_color]) and $item_color_image_list[$k_color]!="")
							{
								$img_tm = "pdf/$CID/".$item_color_image_list[$k_color];
							}
							 
						?>
						
						
					<!--	foreach($item_color_image_list as $key_image=>$color_image)
					{
					
					 $tmp_image = '';
					if($color_image!="")
					{
					
						$tmp_image = "pdf/$CID/".$color_image;
					
					}-->
					  
						
	<a   href="javascript:void(0);" onclick="select_color('<?php echo $k_color;?>','<?php echo $img_tm;?>');"><?php echo $k_color;?></a>
   
   
   	<?php
						}
						
						?>
						
						
						
						<?php
						
						}
						?>
  </div>
</div>	</div>	

	
		<input type="hidden" name="color" id="color" value="Please select color" /> 		 
			 
<?php
}
?>				
			
			</section>	
					<div class="clearfix"></div>
				<?php 
				} 
				?>

				

   <link rel="stylesheet" type="text/css" href="image-picker/image-picker/examples.css">
  <link rel="stylesheet" type="text/css" href="image-picker/image-picker/image-picker/image-picker.css"> 
   <script src="https://code.jquery.com/jquery-3.0.0.min.js" type="text/javascript"></script>
   
  <script src="image-picker/image-picker/js/prettify.js" type="text/javascript"></script> 
  <script src="image-picker/image-picker/js/jquery.masonry.min.js" type="text/javascript"></script>
  <script src="image-picker/image-picker/js/show_html.js" type="text/javascript"></script>
  <script src="image-picker/image-picker/image-picker/image-picker.js" type="text/javascript"></script>		
<?php  
$item_logo_ids = $item_detail['item_logo_ids'] ; 
if($item_logo_ids!="")
{
	include("image-picker/index2.php");
	
}

?>
			


			<section class="linking">

					<form action='#'>
					    <div class="qtyminus"></div>
					    <input type='text' name="quantity" id="qty" value='1' class="qty" />
					    <div class="qtyplus"></div>
					</form>

					<a id="btn_add_to_cart" href="javascript:void(0);"  onClick="ajax_update_to_cart_by_item_id('<?php echo $CID;?>','<?php echo $item_detail['ID'];?>');" class="button adc">Add to Cart</a>
					
					
					
					
					<div  align="center" style=" display:none; left: -50px;
    margin-top: 10px;
    position: relative;" id="ajax_loader"><img src="images/loader.gif"></div>
	
	
	<div id="msg" style="color:red; display:none;"> <br/><br/>  This item has been added to your cart.</div>
					
					<div class="clearfix"></div>

			</section>

		</div>
	</div>

</div>


<div class="container" style="display:none;" >
	<div class="sixteen columns">
			<!-- Tabs Navigation -->
			<ul class="tabs-nav">
				<li class="active"><a href="#tab1">Item Description</a></li>
				<li><a href="#tab2">Additional Information</a></li>
				<!--<li><a href="#tab3">Reviews <span class="tab-reviews">(3)</span></a></li>-->
			</ul>

			<!-- Tabs Content -->
			<div class="tabs-container">

				<div class="tab-content" id="tab1">
				
				<p><?php $str = toSafeDisplay_edit_time_shop($item_detail["Description"]);
				  $str=str_replace('\r\n','<br>',$str); 
				echo $str=str_replace('\"','"',$str);?>
				
				</p>
				
				
					<!--<p>Lorem ipsum pharetra lorem felis. Aliquam egestas consectetur elementum class aptentea taciti sociosqu ad litora torquent perea conubia nostra lorem consectetur adipiscing elit. Donec vestibulum justo a diam ultricies pellentesque. Fusce vehicula libero arcu, vitae ornare turpis elementum at. Etiam posuere quam nec ligula dignissim iaculis donec eleifend laoreet ornare. Quisque mattis luctus est, a placerat elit pharetra.</p>-->
					
					
				</div>

				<div class="tab-content" id="tab2">

					<table class="basic-table">
						<tr>
							<th>Size</th>
							<td>Small, Medium, Largem, Extra Large</td>
						</tr>

						<tr>
							<th>Colors</th>
							<td>Blue, color, Red</td>
						</tr>

						<tr>
							<th>Material</th>
							<td>75% Cotton, 25% Polyester</td>
						</tr>

						<tr>
							<th>Weight</th>
							<td>N/A</td>
						</tr>
					</table>

				</div>

				<div class="tab-content" id="tab3">

					<!-- Reviews -->
					<section class="comments">

						<ul>
							<li>
								<div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&amp;s=70" alt="" /></div>
								<div class="comment-content"><div class="arrow-comment"></div>
									<div class="comment-by"><strong>John Doe</strong><span class="date">May 28, 2014</span>
										<div class="rating five-stars"><div class="star-rating"></div><div class="star-bg"></div></div>
									</div>
									<p>Maecenas dignissim euismod nunc, in commodo est luctus eget. Proin in nunc laoreet justo volutpat blandit enim. Sem felis, ullamcorper vel aliquam non, varius eget just.</p>
								</div>
							</li>

							<li>
								<div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&amp;s=70" alt="" /></div>
								<div class="comment-content"><div class="arrow-comment"></div>
									<div class="comment-by"><strong>Kathy Brown</strong><span class="date">May 18, 2014</span>
										<div class="rating five-stars"><div class="star-rating"></div><div class="star-bg"></div></div>
									</div>
									<p>Morbi velit eros, sagittis in facilisis non, rhoncus et erat. Nam posuere tristique sem, eu ultricies tortor imperdiet vitae. Curabitur lacinia neque non metus</p>
								</div>
							</li>

							<li>
								<div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&amp;s=70" alt="" /></div>
								<div class="comment-content"><div class="arrow-comment"></div>
									<div class="comment-by"><strong>John Doe</strong><span class="date">May 10, 2014</span>
										<div class="rating four-stars"><div class="star-rating"></div><div class="star-bg"></div></div>
									</div>
									<p>Commodo est luctus eget. Proin in nunc laoreet justo volutpat blandit enim. Sem felis, ullamcorper vel aliquam non, varius eget justo. Duis quis nunc tellus sollicitudin mauris.</p>
								</div>

							</li>
						 </ul>

						<!--<a href="#small-dialog" class="popup-with-zoom-anim button color">Add Review</a>

						 -->

					</section>

				</div>

			</div>
	</div>
</div>

<!-- Related Products -->
<div class="container">

	<!-- Headline -->
	<div class="sixteen columns">
		<h3 class="headline">Related Products</h3>
		<span class="line margin-bottom-0"></span>
	</div>

	<!-- Products -->
	<div class="products">

		 <?php 
		 $cat_id = $item_category_detail['cat_id'] ;
		 $CID = $_SESSION['CID'];
		$item_list = get_item_list_by_category_id($CID , $cat_id) ;
		
		if(!empty($item_list))
		{
		
			$item_counter = 1 ;
		
			foreach($item_list as $item_detail )
			{
				
				if($item_detail['ID']==$_GET['id'])
				{
					continue;
				}
				
				
				$item_image  = ''; 
				if($item_detail['ImageFile']!="")
				{
				
					$item_image = "pdf/$CID/".$item_detail['ImageFile'];
				
				}
		
		
		$price = $item_detail['Price'];
		
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
					<!--<a href="#inline" class="product-button modalbox"><i class="fa fa-shopping-cart"></i> Add to Cart</a>-->
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
		
		} ?>

	</div>
</div>

<div class="margin-top-50"></div>


<?php
//pr_n($_SESSION);
//session_destroy();
?>


<!--<a class="popup-with-zoom-anim button color" href="#small-dialog" >Open</a>-->
 
<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
     <h2>Error</h2>
     <p>Please select <?php if(isset($group_name)){echo $group_name;} ?>.</p>
</div>

<input type="hidden" id="current_id"  value="default"/>


<?php include("fancybox_javascript.php");?>

<?php include("footer.php");?>


  


  
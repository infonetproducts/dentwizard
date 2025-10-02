<?php
ob_start();
include("setting.php");
include("stripe_key_setting.php");

unset($_SESSION['billing_form']);
unset($_SESSION['Order']);
unset($_SESSION['delivery_form']);
unset($_SESSION['total_price']);
unset($_SESSION['custom_name_tmp']);
unset($_SESSION['custom_number_tmp']);
unset($_SESSION['custom_price_tmp']);
unset($_SESSION['custom_number_price_tmp']);
unset($_SESSION['artwork_logo_item']);
unset($_SESSION['size_item_temp']);
unset($_SESSION['color_item']);
unset($_SESSION['item_img_tmp']);
unset($_SESSION['size_item']);
unset($_SESSION['total_price_before_discount']);
unset($_SESSION['total_price_after_discount']);

unset($_SESSION['get_dealer_code']);
unset($_SESSION['get_dealer_code_balance']);
unset($_SESSION['set_dealer_discount']);

unset($_SESSION['promo_code_str']);
unset($_SESSION['set_promo_code_discount']);
unset($_SESSION['total_price_after_promo_code']);

unset($_SESSION['canvas_download_url_new']);
unset($_SESSION['sale_discount_total']);

unset($_SESSION['gift_card_is_gift_card_item']);
unset($_SESSION['gift_card_gift_price']);
unset($_SESSION['gift_card_item_price_type']);
unset($_SESSION['gift_card_custom_gift_email']);
unset($_SESSION['gift_card_custom_gift_from']);
unset($_SESSION['gift_card_custom_gift_message']);
unset($_SESSION['custom_gift_delivery_date']);
unset($_SESSION['gift_discount_amount']);

unset($_SESSION['is_sale_price_zero']);

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
		<h2>Order Confirmation</h2>
		
		<nav id="breadcrumbs">
			<ul>
				<li><a href="#">Home</a></li>
				<li><a href="#">Shop</a></li>
				<li><a href="#">Checkout</a></li>
				<li><a href="#">Payment & Order Review</a></li>
				<li>Order Confirmation</li>
			</ul>
		</nav>
	</div>
</div>
</section>


<div class="container">
	<div class="sixteen columns" align="center" >
			Your order has been submitted successfully. <br/>
			Your order id is : <?php echo $_GET['oid'];?>
		<!--<a href="images/portfolio/single-project-half-01.jpg" class="mfp-image" title="Green Leaves">
			<img alt="" src="images/portfolio/single-project-half-01.jpg"/>
		</a>-->
	</div>

</div>
<div class="margin-top-30"></div>
 

		  
 
<?php
//pr_n($_SESSION);

//session_destroy();
?>
<?php include("footer.php");?>

 
  <?php 
		  include_once("javascript_code.php");
		  ?>

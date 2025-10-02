<?php
$AID = $_SESSION['AID'];
$CID = $_SESSION['CID'];
$sql_view_only = "select is_view_only , BudgetBalance , Name from Users where CID=$CID and ID='$AID' " ;
list($is_view_only,$BudgetBalance,$Name_loggin_user)=@mysql_fetch_row(mysql_query($sql_view_only));	
$style_logo = '';
if($CID==56)
{
	$style_logo = 'style="height: 100px;
    width: 132px;" ';
}
if($CID==58)
{
	$style_logo = '';
}
if($CID==59)
{
	$style_logo = 'style="height:44px;
    width: 300px;" ';
}
if($CID==62)
{
	$style_logo = 'style="height:74px;
    width: 340px;" ';
}
if($CID==63)
{
	$style_logo = 'style="height: 155px;
    width: 163px;" ';
}
if($CID==61)
{
	$style_logo = 'style="height: 147px;
    width: 200px;" ';
}
?>
<style>
/*#wrapper img {
		height: auto;
		max-width: 100%;
	}*/
#logo { margin-top: 34px; }
</style>
<?php
if($CID==56)
{
?>
<style>
#logo a img {
	float: left;
	height: 43px;
}
</style>
<?php
}
?>


<?php 
include("css_update.php");
?>

<div class="clearfix"></div>
<!-- Header
================================================== -->
<div class="container">
	
	
	<div class="four columns"  >
		<div id="logo"  >
			<h1 ><a href="index.php"><img  <?php echo $style_logo;?>  src="<?php echo $logo_path;?>" alt="Shop" /></a></h1>
		</div>
	</div>
	<!-- Additional Menu -->
	<div class="twelve columns">
		<div id="additional-menu">
			<ul>
				
				<?php
				if($CID==58)
				{
				?>
				<li><a href="javascript:void(0);">Sign In</a></li>
				<li><a  href="javascript:void(0);">Register</a></li>
				<?php
				}
				?>
					
				<li>
				<?php echo $Name_loggin_user;?>  <br/>
				Budget Balance: $<?php echo number_format($BudgetBalance,2);?> <br/>
				<a href="shopping-cart.php"><!--shopping-cart.html -->Shopping Cart</a></li>
				<!--<li><a href="wishlist.html">WishList <span>(2)</span></a></li>-->
				<!--<li><a href="checkout-billing-details.php" >  Checkout</a></li>-->
			<!--	<li><a href="javascript:void(0);">My Account</a></li>-->
			</ul>
		</div>
		
		<!-- Mobile Budget Balance -->
		 	
		
		
		<div id="mobile-budget-balance" class="mobile-only">
			
			<div class="budget-info"> 
			
				<span class="budget-label">Budget for <?php echo $Name_loggin_user;?>:</span>
				<span class="budget-amount">$<?php echo number_format($BudgetBalance,2);?></span>
			</div>
		</div>
	</div>
	<!-- Shopping Cart -->
	<div class="twelve columns">
		<div id="cart">
			<!-- Button -->
			<div class="cart-btn">
				<a href="#" class="button adc">$<span class="ajax_total_price">0.00</span></a>
			</div>
			<div class="cart-list">
			<div class="arrow"></div>
				<span id="ajax_header_item_cart">
				
		
			</span>
				<div class="cart-buttons button">
					<a href="shopping-cart.php" class="view-cart" ><span data-hover="View Cart"><span>View Cart</span></span></a>
					<!--shopping-cart.html-->
					
					
					<span id="checkout_header_link">
					<a  href="checkout-billing-details.php" class="checkout"><span data-hover="Checkout">Checkout</span></a>
					</span>
					<!-- href="checkout-billing-details.html"-->
					
				</div>
				<div class="clearfix">
				</div>
			</div>
		</div>
		<!-- Search -->
		<nav class="top-search">
		
		<?php 
		$search_url_cat_id = '';
		if(isset($_GET['catid']))
		{
			$search_url_cat_id = $_GET['catid'];
		}else if(isset($_SESSION['catid']))
		{
			 $search_url_cat_id = $_SESSION['catid'] ;
		}
		
	 
		?>
		
		
			<form autocomplete="off" action="index.php?catid=<?php echo $search_url_cat_id; ?>" method="post">
				
				<input type="hidden" name="search_btn" value="search_btn" />
			
				<button><i class="fa fa-search"></i></button>
				<input class="search-field" value="<?php if(isset($_POST['str_search'])){echo $_POST['str_search'];}?>"  name="str_search" id="str_search" type="text" placeholder="Search" />
			</form>
		</nav>
	</div>
</div>
<?php
if($CID==58)
{
?>
<style>
#wrapper img {
    height: auto;
    max-width: 100%;
    align-items: center;
    padding: 20px;
}
#logo {
background-image: url(images/kfbg2.jpg);
    width: 544px;
    height: 100px;
    opacity: 379;
	}
	
#navigation{	
	    position: relative;
    top: -15px;
	}
</style>
<?php
}
?>
<?php
if($CID==59)
{
?>
<style>
/* #wrapper img {
    height: auto;
    max-width: 100%;
    align-items: center;
    padding: 20px;
}*/
</style>
<?php
}
?>
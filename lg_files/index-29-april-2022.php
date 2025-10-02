<?php
ob_start();
include("setting.php");

if($_REQUEST['logout']==1){
	$_SESSION['AID']='';
	$_SESSION['admin'] = '';
	$_SESSION['sysadmin'] = '';
	session_destroy();
	header("Location: index.php");
	exit;
}

$AID = $_SESSION['AID'];
$sql_view_only = "select is_view_only from Users where CID=$CID and ID='$AID' " ; 
list($is_view_only)=@mysql_fetch_row(mysql_query($sql_view_only));	



?>
<?php include("header.php");?>



<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
<?php
if($CID==58)
{
	// include("slider_top_home.php");
}
?>



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

<!--<script src="css2/jquery.js" type="text/javascript"></script>-->

  <?php  include("ajax_add_cart_grid_modal.php");?>
 
 

<?php 
include("left_side_category.php");
//echo $default_cat;
$custom_cat_list = get_all_category_for_custom();

if(isset($_POST['str_search']) and $_POST['str_search']!="")
{
				include('index_grid.php');
				die;
}else{


		if(isset($default_cat) and $default_cat!="")
		{
			if(in_array($default_cat,$custom_cat_list))
			{
				
				
				include('item_home_page_no_left_site_category.php'); // this for custom category
					
			}else{
				
				
				include('index_grid.php');
				die;
			}
			
		}else{
		
		 
		
			include('item_home_page_no_left_site_category.php');
		
		}
		
}

?> 

<style>
img {
padding:0px;

}

</style>

<!-- Post #1 -->

	 
  
		
</div>

<div class="margin-top-15"></div>






<?php
//pr_n($_SESSION);
//session_destroy();
?>



<?php include("footer.php");?>


<?php 
//  include("fancybox_javascript.php");
  include("fancybox_javascript_new.php");

		 

?>

<?php


?>

 
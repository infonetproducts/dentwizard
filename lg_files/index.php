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
$CID = $_SESSION['CID'];
$sql_view_only = "select is_view_only , BudgetBalance from Users where CID=$CID and ID='$AID' " ; 
list($is_view_only,$BudgetBalance)=@mysql_fetch_row(mysql_query($sql_view_only));	


if( !isset($_SESSION['catid']) and isset($_GET['catid']) )
{
	$_SESSION['catid'] = $_GET['catid'];
}


if(isset($_GET['catid']))
{

		if(is_numeric($_GET['catid']))
		{
			 $catid = $_GET['catid'] ;
		
			 $is_parent_cat = is_parent_category($CID,$catid);
			
			
			if($is_parent_cat==1)
			{
				$cat_detail = get_category_detail($CID,$catid);
				
				//print_r($cat_detail ); die;
				
				if($cat_detail['display_type']==0)
				{
					// table , it will display default left side with table
				}
				
				if($cat_detail['display_type']==1)
				{
					// grid
					
					
					
					
					include("index-with-left-site-category.php");
					die;
				}
				
			}else{
				$sub_cat_detail = get_category_detail($CID,$catid);
				$cat_detail = get_category_detail($CID,$sub_cat_detail['ParentID']);
				
				if($cat_detail['display_type']==0)
				{
					// table , it will display default left side with table
				}
				
				if($cat_detail['display_type']==1)
				{
					
					// grid
					include("index-with-left-site-category.php");
					die;
				}
				
			}
			
			
		}else{
			die("Please contact admin");
		}
}

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

if($CID==42)
{
	include("left_side_category.php"); // this is for dealer tire 42
}else{
	

	include("left_side_category_v2.php");
		
	
}

$custom_cat_list = get_all_category_for_custom();

//pr_n($custom_cat_list); die;

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
				
				$is_box_layout =1 ;
				if($is_box_layout==1)
				{
					include('item_home_page_in_box_layout.php');
				}else{
					include('index_grid.php');
				}
				
				
				
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


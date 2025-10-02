<?php
include("setting.php");

?>
<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
<?php include("slider_top_home.php");?>

<!-- Featured
================================================== -->

<?php
$cat_id = $_GET['cat'] ;

$item_list = get_item_list_by_category_id($CID , $cat_id) ;

if(!empty($item_list))
{

	$item_counter = 1 ;

	foreach($item_list as $item_detail )
	{
		

?>



<?php
if($item_counter==1)
{
?>
	<div class="container" >
<?php
}
?>


	<div class="one-third column">
		<a href="#" class="img-caption" >
			<figure>
				<img src="images/featured_img_1.jpg" alt="" />
				<figcaption>
					<h3><?php echo toSafeDisplay_edit_time_shop($item_detail['item_title']) ; ?></h3>
					<span>25% Off Summer Styles</span>
				</figcaption>
			</figure>
		</a>
	</div>

<?php
$item_counter++;

if($item_counter==4)
{
?>
	</div>
<?php
	$item_counter = 1 ;
}
?>

<?php



	}

}else{
?>
	
	<div class="container" >
		
	
	<div class="one-third column">
		
			<figure>
				<figcaption>
					<h3>Item not found ...</h3>
					
				</figcaption>
			</figure>
		</a>
	</div>

	
</div>
<?php
}
?>


<div class="clearfix"></div>





<?php //include("new_arrival_slider.php");?>

<?php //include("four_column.php");?>

<?php include("footer.php");?>
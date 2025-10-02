<?php
ob_start();
include("setting.php");

include_once("../item_group_option_function.php");
include_once("../inventory_item_cat_allow_image_display.php");

/*pr_n($_SESSION);
die;*/

$client_setting = get_client_setting($CID);


$uid=$_SESSION['AID'];

/*print_r("<pre>");
print_r($_SESSION);*/

if(!isset($_SESSION['Order']))
{
	$_SESSION['empty_bc_error'] = 1;
	header("location:index.php?catid=4");
}

// custom_order?link=yes&formid=&offset




//pr($_SESSION['Order_type']);


// save_item   qty   desc_custom
if(isset($_POST["add_to_card_confirm"]))
{


	header("Location:checkout-billing-details.php");	

	//header("Location:finalcart.php");	
	die;
}



//echo $_SESSION['preview'];

list($PDFFile)=@mysql_fetch_row(mysql_query("select PDFFile from Items where FormID='$_SESSION[preview]'  "));
//echo $PDFFile;

//pr_n($client_setting);
// $client_setting['is_enable_sale'];
//echo $client_setting['percentage_off'];

?>


<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 
<section class="titlebar">
	<div class="container">
		<div class="sixteen columns">
			<h2>Preview Cubicle Card</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Preview Cubicle Card</li>
				</ul>
			</nav>
		</div>
	</div>
</section>


<div class="container cart">


<span  align="center" style="color:black;  display:block; text-align:justify; padding:10px;">
Please Note: Because this system is automated, you are responsible for ensuring the accuracy of your cubicle sign. The system produces a preview for this purpose so please review your cubicle signs before placing your order. Thank you.  <br/>
</span>
 


 

	<div class="sixteen columns">
		
		<!-- Cart -->
		
			 
			<?php 
//echo "$pdffolder/$PDFFile"; 
$pdffolder = "pdf/$CID/";
if($PDFFile!="" and is_file("$pdffolder/$PDFFile")) 
{
?>

  
  <center><iframe id="fred" style="border:1px solid #666CCC" title="PDF in an i-Frame" src="<?php echo "$pdffolder/$PDFFile"; ?>" frameborder="1" scrolling="auto" height="680" width="760" ></iframe></center>

<?php
}
?>
		
 <div align="center">
 	<form method="post">
		<input type="checkbox" id="yes_agree">
		I agree that my business card is accurate and I approve this order.
		<br/><br/>
		<input type="submit" name="add_to_card_confirm" value="Add to Order" onClick="return validation_checkbox();">
	
	</form>
    
  </div>    
			
			
	</div>

 


	
	
	 

</div>

<div class="margin-top-40"></div>


		



<?php
//pr_n($_SESSION);

//session_destroy();
?>


<?php include("footer.php");?>


<?php 
include("fancybox_javascript_new.php");
?>

<script>

 function validation_checkbox()
{
	if($('#yes_agree').prop('checked')) 
	{
		//alert(" check");
	} else {
	  // alert("Please check the box at the bottom of the page to authorize the printing of this card.");
	  
	  $("#inline_business_card_preview").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
	  
	  
	  
	   return false;
	}
}

 

</script>
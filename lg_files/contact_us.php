<?php
ob_start();
include("setting.php");
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
		<h2>Contact Us</h2>
		
		<nav id="breadcrumbs">
			<ul>
				<li><a href="#">Home</a></li>
				<li>Contact Us</li>
			</ul>
		</nav>
	</div>
</div>
</section>


<div class="container">
	
	<div class="sixteen columns" align="center" >  <br> <br>
			<p align="center" class="style3">If you have any questions about your  order, please contact us.<br>
 

	<?php
	 $sql_get_contact_page = "
			select ContactPage from Clients where ID = '$CID'
	 ";
	list($ContactPage)=@mysql_fetch_row(mysql_query($sql_get_contact_page));
	
	?>
	<div align="justify">
		
			<div  align="center">
			<?php 
			if($ContactPage!="")
			{
				
				echo $_translated_text = nl2br($ContactPage); 
			}
			?>
			</div>
	</div>
 <!-- <strong>Tire Manufacturer POP &amp; Training Materials</strong><br>
  <a href="mailto:marketingoperations@dealertire.com">marketingoperations@dealertire.com</a> <br>  <br>

   
    <strong>Custom POP</strong><br>
	<a href="mailto:marketingsolutions@dealertire.com">marketingsolutions@dealertire.com</a> <br><br>
	
	
 
    <strong>Business Cards</strong><br>
	<a href="mailto:marketingservices@dealertire.com">marketingservices@dealertire.com</a> <br><br>
	
	
	  <strong>Points Plus</strong><br>
	<a href="mailto:pointspluslogin@dealertire.com">pointspluslogin@dealertire.com</a> <br><br>
 -->
  
  
  </p>
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

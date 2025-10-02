<!-- Footer
================================================== -->

<?php   include("fancybox_javascript_footer_news_letter.php");?>

<div id="footer">

	<!-- Container -->
	<div class="container">

		<div class="six columns">
			
			<h3 class="headline footer">ABOUT</h3>
			<span class="line"></span>
			<div class="clearfix"></div>
			
			<?php
			if($CID==56)
			{
			?>
			
			
			 Leader Graphics specializes in customized team clothing and apparel. If you can't find what you are looking for, please contact us. 
			
			<?php
			}else if($CID==58)
			{
			?> 
			 Kwik Fill /Red Apple Food Mart® now consists of nearly 280 company-owned locations and eight independently owned and operated franchise locations in New York, Pennsylvania and Ohio. We pride ourselves on selling American-Made United Refining Company gas, produced from North American crude oil.
			 <?php
			 }else if($CID==59)
			{
			 ?>
			 
				This site features custom apparel for
LECOM Health. If you can't find what
you are looking for, please <a  style="color:white;" href="contact_us.php">contact us</a>
			
			<?php
			 }else if($CID==60)
			{
			 ?>
			 
					Country Fair apparel shop.
			 <?php
			 }else if($CID==61)
			{
			 ?>
			 
			 McDowell High School is a public high school located in Millcreek Township, Pennsylvania a suburb of Erie. It is the only high school in the Millcreek Township School District. The school's mascot is the Trojan. McDowell has both a men's and women's Lacrosse Team.
			  <?php
			 }else if($CID==62)
			{
			 ?>
			 
			Titusville Area Hospital was founded in 1900 and at its peak was licensed for more than 90 beds. Today, having gained critical access hospital (CAH) designation TAH is licensed as a 25-bed acute care hospital. From its inception, our hospital has been on a mission to provide high quality healthcare emphasizing personal attention, compassion, and respect. Over our 100+ year history, we have influenced the health of many, today we assist more than 30,000 people each year with their health needs. For our high quality, cost efficient services, TAH has been nationally recognized as a “100 Top Hospital.”

			 
			  <?php
			 }else if($CID==63)
			{
			 ?>
			 
			 
			 
			 Leader Graphics specializes in customized team clothing and apparel. If you can't find what you are looking for, please contact us.
			 
			 
			  <?php
			 }else{ 
			 ?>
			 
			 
			 <?php echo $client_detail['about_us_text'];?>
			 
			 <?php
			  
			 }
			 ?>
			 
			 
			 
			 
			 
		</div>
		
	 

	<!--	<div class="four columns" style="display:none;">

		 
			<h3 class="headline footer">Customer Service</h3>
			<span class="line"></span>
			<div class="clearfix"></div>

			<ul class="footer-links">
				<li><a href="#">Order Status</a></li>
				<li><a href="#">Payment Methods</a></li>
				<li><a href="#">Delivery & Returns</a></li>
				<li><a href="#">Privacy Policy</a></li>
				<li><a href="#">Terms & Conditions</a></li>
			</ul>

		</div>

		<div class="four columns" style="display:none;">

			 
			<h3 class="headline footer">My Account</h3>
			<span class="line"></span>
			<div class="clearfix"></div>

			<ul class="footer-links">
				<li><a href="#">My Account</a></li>
				<li><a href="#">Order History</a></li>
				<li><a href="#">Wish List</a></li>
			</ul>

		</div>
-->
		<div class="six columns" style="float:right;"  >

			 
			<h3 class="headline footer">Newsletter 	 </h3>
			<span class="line"></span>
			<div class="clearfix"></div>
			<p>Sign up to receive email updates on new product announcements, gift ideas, special promotions, sales and more.</p>

			<form  method="post" onsubmit="return ajax_submit_news_letter()">
				<button class="newsletter-btn" on type="submit">Join</button>
				<input required class="newsletter" id="news_letter_email" id="news_letter_email" type="email" placeholder="mail@example.com" value=""/>
			</form>
			
		
			
			
		</div>

	</div>
	 

</div>


<!--<a class="popup-with-zoom-anim button color" href="#small-dialog" >Open</a>
-->


 
<!--<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
     <h2>Thank you</h2><br/>
     <p>Thanks for join news letter.</p>
</div>-->



<!-- Footer / End -->

<!-- Footer Bottom / Start -->
<div id="footer-bottom">

	<!-- Container -->
	<div class="container"> 

		<?php
		if($CID==56)
		{
		
		?>
		
		<div class="eight columns">© <?php echo date("Y");?> Leader Graphics. All Rights Reserved.</div>
		
		<?php
		}else if($CID==58)
		{
		?>
		
			<div class="nine columns">© <?php echo date("Y");?> KwikFill, a division of United Refining Company. All Rights Reserved.</div>
		
		 
		
		<?php
		}else if($CID==59)
		{
		?>
		
			<div class="nine columns">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<!--© <?php echo date("Y");?> Lake Erie College of Osteopathic Medicine. All Rights Reserved.--></div>
		
		<?php
		}else if($CID==60)
		{
		?>
		
			<div class="nine columns">© <?php echo date("Y");?> Country Fair, Inc. All Rights Reserved.</div>
		
		<?php
		}else if($CID==61)
		{
		?>
		
			<div class="nine columns">© <?php echo date("Y");?> McDowell Lacrosse, Inc. All Rights Reserved.</div>
		
		<?php
		}else if($CID==62)
		{
		?>
		
			<div class="nine columns">© <?php echo date("Y");?> Titusville Area Hospital. All Rights Reserved.</div>
		
		<?php
		}else{
		?>
		<div class="nine columns">© <?php echo date("Y");?> <?php echo $client_detail['footer_text'];?></div>
		
		<?php
		}
		?>
		
		<div class="seven columns">
			<ul class="payment-icons">
				<li><img src="images/visa.png" alt="" /></li>
				<li><img src="images/mastercard.png" alt="" /></li>
				<li><img src="images/amex.png" alt="" /></li>
				<li><img src="images/discover.png" alt="" /></li>
				<!--<li><img src="images/paypal.png" alt="" /></li>-->
			</ul>
		</div>

	</div>
	<!-- Container / End -->

</div>
<!-- Footer Bottom / End -->

<!-- Back To Top Button -->
<div id="backtotop"><a href="#"></a></div>

</div>


<!-- Java Script
================================================== -->


<?php
$page_name = basename($_SERVER['PHP_SELF']);
if($page_name=="item-detail.php")
{
?>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<?php


}else{
?>
	<script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>

<?php
}
?>

 


<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="scripts/jquery.jpanelmenu.js"></script>
<script src="scripts/jquery.themepunch.plugins.min.js"></script>
<script src="scripts/jquery.themepunch.revolution.min.js"></script>
<script src="scripts/jquery.themepunch.showbizpro.min.js"></script>
<script src="scripts/jquery.magnific-popup.min.js"></script>
<script src="scripts/hoverIntent.js"></script>
<script src="scripts/superfish.js"></script>
<script src="scripts/jquery.pureparallax.js"></script>
<script src="scripts/jquery.pricefilter.js"></script>
<script src="scripts/jquery.selectric.min.js"></script>
<script src="scripts/jquery.royalslider.min.js"></script>
<script src="scripts/SelectBox.js"></script>
<script src="scripts/modernizr.custom.js"></script>
<script src="scripts/waypoints.min.js"></script>
<script src="scripts/jquery.flexslider-min.js"></script>
<script src="scripts/jquery.counterup.min.js"></script>
<script src="scripts/jquery.tooltips.min.js"></script>
<script src="scripts/jquery.isotope.min.js"></script>
<script src="scripts/puregrid.js"></script>
<script src="scripts/stacktable.js"></script>
<script src="scripts/custom.js"></script>

 
<!--<script src="scripts/switcher.js"></script>
<div id="style-switcher" >
	<h2>Style Switcher <a href="#"></a></h2>
	
	<div><h3>Predefined Colors</h3>
		<ul class="colors" id="color1">
			<li><a href="#" class="green" title="Green"></a></li>
			<li><a href="#" class="blue" title="Blue"></a></li>
			<li><a href="#" class="orange" title="Orange"></a></li>
			<li><a href="#" class="navy" title="Navy"></a></li>
			<li><a href="#" class="yellow" title="Yellow"></a></li>
			<li><a href="#" class="peach" title="Peach"></a></li>
			<li><a href="#" class="beige" title="Beige"></a></li>
			<li><a href="#" class="purple" title="Purple"></a></li>
			<li><a href="#" class="celadon" title="Celadon"></a></li>
			<li><a href="#" class="pink" title="Pink"></a></li>
			<li><a href="#" class="red" title="Red"></a></li>
			<li><a href="#" class="brown" title="Brown"></a></li>
			<li><a href="#" class="cherry" title="Cherry"></a></li>
			<li><a href="#" class="cyan" title="Cyan"></a></li>
			<li><a href="#" class="gray" title="Gray"></a></li>
			<li><a href="#" class="darkcol" title="Dark"></a></li>
		</ul>
		
		<h3>Layout Style</h3>
		<div class="layout-style">
			<select id="layout-style"> 
				<option value="1">Boxed</option>
				<option value="2">Wide</option>
			</select>
		</div>
	
	<h3>Background Image</h3>
		 <ul class="colors bg" id="bg">
			<li><a href="#" class="bg1"></a></li>
			<li><a href="#" class="bg2"></a></li>
			<li><a href="#" class="bg3"></a></li>
			<li><a href="#" class="bg4"></a></li>
			<li><a href="#" class="bg5"></a></li>
			<li><a href="#" class="bg6"></a></li>
			<li><a href="#" class="bg7"></a></li>
			<li><a href="#" class="bg8"></a></li>
			<li><a href="#" class="bg9"></a></li>
			<li><a href="#" class="bg10"></a></li>
			<li><a href="#" class="bg11"></a></li>
			<li><a href="#" class="bg12"></a></li>
			<li><a href="#" class="bg13"></a></li>
			<li><a href="#" class="bg14"></a></li>
			<li><a href="#" class="bg15"></a></li>
			<li><a href="#" class="bg16"></a></li>
		</ul>
		
	<h3>Background Color</h3>
		<ul class="colors bgsolid" id="bgsolid">
			<li><a href="#" class="green-bg" title="Green"></a></li>
			<li><a href="#" class="blue-bg" title="Blue"></a></li>
			<li><a href="#" class="orange-bg" title="Orange"></a></li>
			<li><a href="#" class="navy-bg" title="Navy"></a></li>
			<li><a href="#" class="yellow-bg" title="Yellow"></a></li>
			<li><a href="#" class="peach-bg" title="Peach"></a></li>
			<li><a href="#" class="beige-bg" title="Beige"></a></li>
			<li><a href="#" class="purple-bg" title="Purple"></a></li>
			<li><a href="#" class="red-bg" title="Red"></a></li>
			<li><a href="#" class="pink-bg" title="Pink"></a></li>
			<li><a href="#" class="celadon-bg" title="Celadon"></a></li>
			<li><a href="#" class="brown-bg" title="Brown"></a></li>
			<li><a href="#" class="cherry-bg" title="Cherry"></a></li>
			<li><a href="#" class="cyan-bg" title="Cyan"></a></li>
			<li><a href="#" class="gray-bg" title="Gray"></a></li>
			<li><a href="#" class="dark-bg" title="Dark"></a></li>
		</ul>
	</div>
	
	<div id="reset"><a href="#" class="button color">Reset</a></div>
		
</div>-->

 
<?php 
include("common_javascript.php");
?>


<script>
function ajax_submit_news_letter()
{
	var news_letter_email = $("#news_letter_email").val();
	
	
	 var str = "";	
	 str += "action=news_letter_join";
	 str += "&news_letter_email="+news_letter_email;
	
	var url = "ajax_news_letter_join.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			//$("#inline").html(result);
			
			 
			
			
			   $("#inline_news_letter").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		
			
			
			//alert("Thanks for join news letter.");
			$("#news_letter_email").val('');
			
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
}

</script>

</body>
</html>
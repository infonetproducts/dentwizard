
<?php

 $sql_req= "SELECT requisitions FROM Clients where ID = '$CID' ";
 $rs_req = mysql_query($sql_req);
 list($requisitions)=@mysql_fetch_row($rs_req);
 
 
 $sql_user_custom= "SELECT custom_order FROM Users where ID = '$AID' ";
 $rs_custom_user_check = mysql_query($sql_user_custom);
 list($custom_order)=@mysql_fetch_row($rs_custom_user_check);
// echo $custom_order;
 
?>

<div class="container">
	<div class="sixteen columns">
		<a href="#menu" class="menu-trigger"><i class="fa fa-bars"></i> Menu</a>
		<nav id="navigation">
			<ul class="menu"  id="responsive">

				<li><a href="index.php" class="current homepage" id="current">Home</a></li>
                
				<?php
				if($custom_order==1)
	 			{
				?>
				<li class="demo-button">
				  <a href="custom_order.php?f=1">Custom Order</a>
				</li>
                <?php
				}
				?>
                
                <?php
				if($requisitions==1)
 
 				{
				?>
                 
                  <?php
				}
				?>
                

				<li class="demo-button">
				  <a href="shopping-cart.php">Cart</a>
				</li>

				<li class="demo-button">
				  <a href="track.php">Tracking</a>
				</li>

				<li class="demo-button">
				  <a href="profile.php">Profile</a>
				</li>

				<!--<li class="demo-button active">
				  <a href="#">Admin</a>
				</li>-->
				 

				<li class="demo-button">
				  <a <?php if(basename($_SERVER['PHP_SELF'])=="contact_us.php"){?>class="current"<?php }?> href="contact_us.php">Contact Us</a>
				</li>


				<li class="demo-button active">
				  <a href="../index.php?logout=1">Logout</a>
				</li>


<!--
				<li class="dropdown">
					<a href="#">Shortcodes</a>
					<ul>
						<li><a href="javascript:void(0);">Elements</a></li>
						<li><a href="javascript:void(0);">Typography</a></li>
						<li><a href="javascript:void(0);">Pricing Tables</a></li>
						<li><a href="javascript:void(0);">Icons</a></li>
					</ul>
				</li>


				<li class="dropdown">
					<a href="#">Portfolio</a>
					<ul>
						<li><a href="javascript:void(0);">3 Columns</a></li>
						<li><a href="javascript:void(0);">4 Columns</a></li>
						<li><a href="javascript:void(0);">Dynamic Grid</a></li>
						<li><a href="javascript:void(0);">Single Project</a></li>
					</ul>
				</li>
				

				<li class="dropdown">
					<a href="#">Blog</a>
					<ul>
						<li><a href="javascript:void(0);">Blog Standard</a></li>
						<li><a href="javascript:void(0);">Blog Masonry</a></li>
						<li><a href="javascript:void(0);">Single Post</a></li>
					</ul>
				</li>


				<li class="demo-button">
				  <a href="#">Get This Theme</a>
				</li>-->

			</ul>
		</nav>
	</div>
</div>
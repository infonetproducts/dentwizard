<div class="container">
	<div class="sixteen columns">
		<a href="#menu" class="menu-trigger"><i class="fa fa-bars"></i> Menu</a>
		<nav id="navigation">
			<ul class="menu"  id="responsive">

				<li><a href="index.php" class="current homepage" id="current">Home</a></li>

				<!--<li class="dropdown">
					<a <?php if(basename($_SERVER['PHP_SELF'])=="index.php"){?>class="current"<?php }?> href="#">Shop</a>
					<ul>-->
						
						<?php
						
			
			$parent_category_list = get_parent_gategory($CID) ;
			// pr_n($parent_category_list) ;
			if(!empty($parent_category_list))
			{
				foreach($parent_category_list as $cat_id => $cat_name )
				{
					$style = "";
					
					
				
					$sub_cat_list = get_sub_gategory($CID,$cat_id) ;
					
					if(!empty($sub_cat_list))
					{
						$style = 'class="dropdown"';
					}
				
			?>
						
						<li <?php echo $style;?>><a   href="index.php?catid=<?php echo $cat_id;?>#all"><?php echo $cat_name ; ?> </a>
						
					
						<?php
						if(!empty($sub_cat_list))
						{
						?>
						
						<ul>
							<?php
							foreach($sub_cat_list as $sub_cat_id => $sub_cat_name )
							{
							?>	
								<li><a href="index.php?catid=<?php echo $sub_cat_id;?>"><?php echo $sub_cat_name ; ?></a></li>
							<?php
							}
							?>
						
						</ul>
						
						<?php
						}
						?>
						
						
						
						</li> 
						
						 
						
						
						
				 <?php
				}
			
			}
			?>		
					 
						
					<!--</ul>
				</li>-->
				
				<li style="display:none;">
					<a href="#">Shop</a>
					<div class="mega">
						<div class="mega-container">

							
				<?php
						
			
			$parent_category_list = get_parent_gategory($CID) ;
			// pr_n($parent_category_list) ;
			if(!empty($parent_category_list))
			{
				foreach($parent_category_list as $cat_id => $cat_name )
				{
				
				
					//$sub_cat_list = get_sub_gategory($CID,$cat_id) ;
					
						$sub_cat_list = get_sub_gategory($CID,$cat_id) ;
				
			?>
							
							<div class="one-column">
								<ul>
									<li><span class="mega-headline"><a href="javascript:void(0);"><?php echo $cat_name ; ?></a></span></li>
									
									
								<?php
							foreach($sub_cat_list as $sub_cat_id => $sub_cat_name )
							{
						?>	
									<li><a href="index.php?catid=<?php echo $sub_cat_id;?>"><?php echo $sub_cat_name ; ?></a></li>
								 
						
						
							<?php
							}
							?>
						
									
								</ul>
							</div>

 
 <?php
				}
			
			}
?>
 

							<!--<div class="one-column">
								<ul>
									<li><span class="mega-headline">Featured Pages</span></li>
									<li><a href="javascript:void(0);">Business Homepage</a></li>
									<li><a href="javascript:void(0);">Default Shop</a></li>
									<li><a href="javascript:void(0);">Masonry Blog</a></li>
									<li><a href="javascript:void(0);">Variable Product</a></li>
									<li><a href="javascript:void(0);">Dynamic Grid</a></li>
								</ul>
							</div>-->

							<!--<div class="one-column hidden-on-mobile">
								<ul>
									<li><span class="mega-headline">Paragraph</span></li>
									<li><p>This <a href="#">Mega Menu</a> can handle everything. Lists, paragraphs, forms...</p></li>
								</ul>
							</div>-->

							<!--<div class="one-fourth-column hidden-on-mobile">
								<a href="#" class="img-caption margin-reset">
									<figure>
										<img src="images/menu-banner-01.jpg" alt="" />
										<figcaption>
											<h3>Jeans</h3>
											<span>Pack for Style</span>
										</figcaption>
									</figure>
								</a>
							</div>-->

							<!--<div class="one-fourth-column hidden-on-mobile">
								<a href="#" class="img-caption margin-reset">
									<figure>
										<img src="images/menu-banner-02.jpg" alt="" />
										<figcaption>
											<h3>Sunglasses</h3>
											<span>Nail the Basics</span>
										</figcaption>
									</figure>
								</a>
							</div>-->

							<div class="clearfix"></div>
						</div>
					</div>
				</li>

				<li class="demo-button active">
				  <a <?php if(basename($_SERVER['PHP_SELF'])=="contact_us.php"){?>class="current"<?php }?> href="contact_us.php">Contact Us</a>
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
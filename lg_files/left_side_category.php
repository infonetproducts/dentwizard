<!-- Sidebar
================================================== -->
<div class="four columns">
		<!-- Categories -->
		<div class="widget margin-top-0">
			<h3 class="headline">Categories</h3><span class="line"></span><div class="clearfix"></div>

			<ul id="categories">

<?php


$uid = $_SESSION['AID'];

list($mycats,$vid , $group_id)=@mysql_fetch_row(mysql_query("select FormCategoryIDs,VendorID , group_id from Users where ID='$uid'"));
if($vid && !$mycats) {
	header("Location: /admin");
	exit;
}
if($mycats && !strstr($mycats,',')){
	list($children)=@mysql_fetch_row(mysql_query("select count(*) from Category where ParentID='$mycats'"));
	// if user has 1 category defined, then just redirect to itemsindex, which will show items for that category only.
	if(!$children){
		header("Location: itemsindex.php");
		exit;
	}
}
$cat='';
if(isset($_REQUEST['getcatsel'])) $cat=$_REQUEST['getcatsel'];
if($mycats)
{
	if($mycats!="")
	{
	 $catand = "and a.ID in ($mycats)";
	 
	 $catand_group = "and a.ID NOT in ($mycats)";
	}
}else
{
	 $catand='';
	 $catand_group = "";
}
$tar = array_flip(explode(",", $mycats));
			
			
$sql_c = '';


  $sql_c = "
select * from ((select a.ID,a.Name,count(c.ID) from Category a left join Category c on c.ParentID=a.ID and c.Active='Y' where a.CID=$CID and a.ParentID=0 and a.Active='Y' $catand group by a.ID) union 
(select a.ID,a.Name,count(c.category_id) from Category a inner join category_group c on a.id = c.category_id
where c.group_id= $group_id and a.CID=$CID and a.ParentID=0 and a.Active='Y' $catand_group group by a.ID)) as t1
order by name  
";



if($catand!="" and $group_id!="0")
{	
	
 $sql_c = "select id,name,count_t from ((select a.ID,a.Name,count(c.ID)as count_t from Category a left join Category c on c.ParentID=a.ID and c.Active='Y' where a.CID=$CID and a.ParentID=0 and a.Active='Y' $catand  group by a.ID) union (select a.ID,a.Name,count(c.category_id) as count_t from Category a inner join category_group c on a.id = c.category_id where c.group_id= $group_id and a.CID=$CID and a.ParentID=0 and a.Active='Y' group by a.ID)) as t1 
group by id,name order by name ";

}else if($group_id!="0"){
	
	 $sql_c = "select a.ID,a.Name,count(c.category_id) as count_t from Category a inner join category_group c on a.id = c.category_id where c.group_id= $group_id and a.CID=$CID and a.ParentID=0 and a.Active='Y' 
group by id,name order by name";

	
}else{
	 $sql_c = "select a.ID,a.Name,count(c.ID)as count_t from Category a left join Category c on c.ParentID=a.ID and c.Active='Y' where a.CID=$CID and a.ParentID=0 and a.Active='Y' $catand  group by id,name order by name ";
	
}
 

//echo $sql_c ;
//echo '<br/><br/>';

$mainrs = mysql_query($sql_c);
 $numcats = mysql_num_rows($mainrs);
			
			
			
			
			
			
			
			//$parent_category_list = get_parent_gategory($CID) ;
			// pr_n($parent_category_list) ;
			
			$active_expand = 1; 
			
			$active_main_expand = 1  ;
			if(isset($_GET['catid']))
			{
				$active_main_expand = 0 ;
				$active_expand = 0 ;
			} 
			
			$default_cat = '';
			if(isset($_GET['catid']) and $_GET['catid']>0)
			{
				$default_cat =  $_GET['catid'] ;
			}
			
			//echo $numcats;
			//die;
			$is_first_cat = 1;
			if($numcats>0)
			{
				//foreach($parent_category_list as $cat_id => $cat_name )
				
				while($parent_category_detail = mysql_fetch_assoc($mainrs) )
				{
					// pr_n($parent_category_detail); 
					
						$cat_id_tmp = $parent_category_detail["ID"] ;
					
						$item_list_tmp_for_cat = get_item_list_by_category_id($CID , $cat_id_tmp) ;
						
						if($parent_category_detail['Name']=="Gift Cards")
						{
							//continue ; 
						}	
						
						if(empty($item_list_tmp_for_cat))
						{
							continue ; 
						}
						
						
						
						if($cat_id_tmp==4 and $is_first_cat==1 and !isset($_GET['catid']))
						{
							header("location:index.php?catid=4");
							die;	
						}else{
							
							if($is_first_cat==1 and !isset($_GET['catid']) and basename($_SERVER['PHP_SELF'])!="item-detail.php")
							{
						
								header("location:index.php?catid=$cat_id_tmp");
								die;	
							}
						
						}
						
						
						$is_first_cat++;
					//	pr_n($item_list);
					
					
					if(isset($parent_category_detail["name"]))
					{
						$cat_name = $parent_category_detail["name"];	
					}
					
					if(isset($parent_category_detail["id"]))
					{
						$cat_id = $parent_category_detail["id"];
					}
					
					
					if(isset($parent_category_detail["Name"]))
					{
						$cat_name = $parent_category_detail["Name"];	
					}
					
					if(isset($parent_category_detail["ID"]))
					{
						$cat_id = $parent_category_detail["ID"];
					}
					
					
				
				
				
				
					if($cat_name=="")
					{
						//continue;
					}
					
					
					if($default_cat=="")
					{
						$default_cat = $cat_id ;
						
					}
				
				
					if(isset($_GET['catid']))
					{
				
						if($cat_id==$_GET['catid'])
						{
							$active_main_expand = 1 ; 
							$active_expand = 1 ;
							//echo "Parent 1";
						}else{
							
							$parent_cat_id = get_parent_cat_id($CID,$_GET['catid']);
							$active_main_expand = 0 ; 
							$active_expand = 0 ;
							//echo "Parent 2";
							//echo $_GET['catid'];
							if($parent_cat_id==$cat_id)
							{
								//echo $parent_cat_id;
								$active_main_expand = 1 ; 
								$active_expand = 1 ;
							}
						
						
							
						}
				
					}
				
					$sub_cat_list = get_sub_gategory($CID,$cat_id) ;
				
			?>

				<li  >
                
                
                <?php
				if(empty($sub_cat_list))
				{
				?>
                
                 <a <?php if($active_main_expand==1){ echo 'class="active"';}?> onclick="open_main_cat('<?php echo $cat_id ;?>');"  href="index.php?catid=<?php echo $cat_id ;?>#all" ><?php echo $cat_name ; ?> </a> 
				
                <?php
				}else{
				?>
                
              <a <?php if($active_main_expand==1){ echo 'class="active"';}?> ><?php echo $cat_name ; ?> </a>
				
                <?php
				}
				?>
                	
					<?php
					$active_main_expand++;
					
					if(!empty($sub_cat_list))
					{
					?>
					
					<ul  <?php if($active_expand==1){?>style="display:block;"<?php }?> >
						<?php
							foreach($sub_cat_list as $sub_cat_id => $sub_cat_name )
							{
						?>
						
							<li>
                           
                           <!-- <a  href="index.php?catid=<?php echo $sub_cat_id ;?>#all"><?php echo $sub_cat_name ; ?></a>-->
                           
                           
                            <a <?php if($sub_cat_id==$_GET['catid']) { echo 'class="active"'; }?>  onclick="ajax_get_item_list('<?php echo $sub_cat_id;?>','<?php echo $CID;?>');" href="index.php?catid=<?php echo $sub_cat_id;?>"><?php echo $sub_cat_name ; ?></a>
                            
                            </li>
							
							<?php
							}
							?>	
						 
					</ul>
					
					<?php
					}
					?>
					
					
				</li>

				 <?php
				 $active_expand++;
				 
				}
			
			}
			?>

			</ul>
			<div class="clearfix"></div>

		</div>


	 	<!-- Widget -->
	 	<div class="widget" style="display:none;">
	 		<h3 class="headline">Filter By Price</h3><span class="line"></span><div class="clearfix"></div>

			<div id="price-range">
				<div class="padding-range"><div id="slider-range"></div></div>
				<label for="amount">Price:</label>
				<input type="text" id="amount"/>
				<a href="#" class="button color">Filter</a>
			</div>
			<div class="clearfix"></div>
	 	</div>

	</div>
	
	
	
	<!--<div class="four columns">

	 
		<div class="widget margin-top-0">
			<h3 class="headline">Categories</h3><span class="line"></span><div class="clearfix"></div>

			<ul id="categories">

				<li><a href="#">Accessories <span>(8)</span></a>
					<ul>
						<li><a href="#">Hats <span>(2)</span></a></li>
						<li><a href="#">Bags <span>(2)</span></a></li>
						<li><a href="#">Gloves  <span>(1)</span></a></li>
						<li><a href="#">Belts <span>(3)</span></a></li>

				<li><a href="#">Accessories <span>(8)</span></a>
					<ul>
						<li><a href="#">Hats <span>(2)</span></a></li>
						<li><a href="#">Bags <span>(2)</span></a></li>
						<li><a href="#">Gloves  <span>(1)</span></a></li>
						<li><a href="#">Belts <span>(3)</span></a></li>
					</ul>
				</li>
					</ul>
				</li>

				<li><a href="#">Jewelry <span>(12)</span></a>
					<ul>
						<li><a href="#">Rings <span>(3)</span></a></li>
						<li><a href="#">Necklaces  <span>(2)</span></a></li>
						<li><a href="#">Bracelets <span>(4)</span></a></li>
						<li><a href="#">Watches <span>(3)</span></a></li>
					</ul>
				</li>

				<li><a href="#">Gifts <span>(3)</span></a>
					<ul>
						<li><a href="#">Headphones <span>(1)</span></a></li>
						<li><a href="#">Books  <span>(2)</span></a></li>
						<li><a href="#">Gifts <span>(3)</span></a>
							<ul>
								<li><a href="#">Headphones <span>(1)</span></a></li>
								<li><a href="#">Books  <span>(2)</span></a></li>
								<li><a href="#">Gifts <span>(3)</span></a>
									<ul>
										<li><a href="#">Headphones <span>(1)</span></a></li>
										<li><a href="#">Books  <span>(2)</span></a></li>
										<li><a href="#">Gifts <span>(3)</span></a></li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</li>

				<li><a href="#">Men's Wear <span>(14)</span></a>
					<ul>
						<li><a href="#">Denim <span>(3)</span></a></li>
						<li><a href="#">Shirts <span>(4)</span></a></li>
						<li><a href="#">Jackets  <span>(2)</span></a></li>
						<li><a href="#">Suits <span>(3)</span></a></li>
						<li><a href="#">Pants <span>(2)</span></a></li>
					</ul>
				</li>

				<li><a href="#">Women's Wear <span>(20)</span></a>
					<ul>
						<li><a href="#">Denim <span>(2)</span></a></li>
						<li><a href="#">Skirts <span>(3)</span></a></li>
						<li><a href="#">Dresses <span>(5)</span></a></li>
						<li><a href="#">Shirts <span>(4)</span></a></li>
						<li><a href="#">Jumpsuits <span>(4)</span></a></li>
						<li><a href="#">Shoes <span>(2)</span></a></li>
					</ul>
				</li>

				<li><a href="#">Miscellaneous <span>(3)</span></a>
					<ul>
						<li><a href="#">Lamps <span>(1)</span></a></li>
						<li><a href="#">Mugs  <span>(2)</span></a></li>
					</ul>
				</li>

				<li><a href="#">Levels Example <span>(7)</span></a>
					<ul>
						<li><a href="#">Books <span>(1)</span></a></li>
						<li><a href="#">Headphones <span>(6)</span></a>
							<ul>
								<li><a href="#">Open <span>(2)</span></a>
									<ul>
										<li><a href="#">Sennheiser</a></li>
										<li><a href="#">Beyerdynamic</a></li>
									</ul>
								</li>
								<li><a href="#">Closed <span>(4)</span></a>
									<ul>
										<li><a href="#">Beyerdynamic</a></li>
										<li><a href="#">Denon</a></li>
										<li><a href="#">Sennheiser</a></li>
										<li><a href="#">AKG</a></li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</li>

			</ul>
			<div class="clearfix"></div>

		</div>


	 
	 	<div class="widget">
	 		<h3 class="headline">Filter By Price</h3><span class="line"></span><div class="clearfix"></div>

			<div id="price-range">
				<div class="padding-range"><div id="slider-range"></div></div>
				<label for="amount">Price:</label>
				<input type="text" id="amount"/>
				<a href="#" class="button color">Filter</a>
			</div>
			<div class="clearfix"></div>
	 	</div>

	</div>-->
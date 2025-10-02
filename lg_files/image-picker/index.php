 
					<style>
.dropbtn_logo {
    background-color: #3498DB;
    color: white;
    padding: 16px;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropbtn_logo:hover, .dropbtn_logo:focus {
    background-color: #2980B9;
}

.dropdown_logo {
    position: relative;
    display: inline-block;
}

.dropdown-content_logo {
    display: none;
    position: absolute;
    background-color: #f1f1f1;
    min-width: 160px;
    overflow: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 10000;
}

.dropdown-content_logo a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown_logo a:hover {background-color: #ddd;}

.show_logo {display: block;}


</style>
   
  
 
  <form method="post">
  <div id="container" style="width:100% !important; padding: 0; border: 0;">
 
   <?php
	  	 	$sql_client_logos = "select * from ClientLogos where ID IN ($item_logo_ids) ";
	$rs_client_logos = mysql_query($sql_client_logos);
	  ?>
    
    <div class="picker">
	<br>
     <label>Artwork (Choose Logo Option)</label>
	  <p>The logo cannot be previewed on this product but will be applied to the product on your order.</p>
	   
	   
	   <div class="four alpha columns alp">
 <label >Logo</label>
<div class="dropdown_logo">
<input type="submit" onclick="myFunction_logo()" class="dropbtn_logo" id="dropbtn_logo" value="Please select logo" />

  <div id="myDropdown_logo" class="dropdown-content_logo">
    <a href="javasript:void(0);" 
	onclick="select_logo('Please select logo','');">
	PLEASE SELECT COLOR </a>
	
	
						<?php 
				
					
					if(!empty($item_color_list))
					{
					
					 
						foreach($item_color_list as $k_color=>$v_price)
						{
							
							
							$img_tm = '';
							if(isset($item_color_image_list[$k_color]) and $item_color_image_list[$k_color]!="")
							{
								$img_tm = "pdf/$CID/".$item_color_image_list[$k_color];
							}
							 
						?>
						
						
					<!--	foreach($item_color_image_list as $key_image=>$color_image)
					{
					
					 $tmp_image = '';
					if($color_image!="")
					{
					
						$tmp_image = "pdf/$CID/".$color_image;
					
					}-->
					  
						
	<a   href="javascript:void(0);" onclick="select_logo('<?php echo $k_color;?>','<?php echo $img_tm;?>');"><?php echo $k_color;?></a>
   
   
   	<?php
						}
						
						?>
						
						
						
						<?php
						
						}
						?>
  </div>
</div>	</div>	

	
		<input type="hidden" name="logo" id="color" value="Please select logo" /> 		 
			 
<?php
}
?>	
	  
	 
	  
    </div>

  </div>

  

  </form>

  

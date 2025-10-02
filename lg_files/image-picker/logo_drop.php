



 
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
   
  
 
  <div id="container" style="width:100% !important; padding: 0; border: 0;">
 
   <?php
	  	 	$sql_client_logos = "select * from ClientLogos where ID IN ($item_logo_ids) ";
	$rs_client_logos = mysql_query($sql_client_logos);
	  ?>
    
    <div class="picker">
	<br><br>
     <label><br>Artwork (Choose Logo Option)</label>
	  <p>The logo cannot be previewed on this product but will be applied to the product on your order.</p>
	   <div class="four alpha columns alp">
<div class="dropdown_logo">
<input type="submit" onclick="myFunction_logo()" class="dropbtn_logo" id="dropbtn_logo" value="Please select logo" />
  <div id="myDropdown_logo" class="dropdown-content_logo">
    <a href="javasript:void(0);" 
	onclick="select_logo('Please select logo','');">
	PLEASE SELECT LOGO </a>
					 <?php
		// rs_client_logos
		
		if( mysql_num_rows($rs_client_logos) > 0 )
		{
			while( $client_logo = mysql_fetch_assoc($rs_client_logos) )
			{
				 $logo_image = "../pdf/$client_logo[CID]/".$client_logo['image_name'];
				 
				 $selected = '';
				 
				 if(in_array($client_logo['ID'],$item_logo_ids_arr))
				 {
				 	// $selected = 'selected="selected"';
				 }
				 
		?>
	<a   href="javascript:void(0);" onclick="select_logo('<?php echo $client_logo["Name"];?>','<?php echo $logo_image;?>');"><?php echo $client_logo["Name"];?></a>
   
   	<?php
						}
						
						?>
						
						
						
						<?php
						
						}
						?>
  </div>
</div>	</div>	

	
		<input type="hidden" name="artwork_logo" id="artwork_logo" value="Please select logo" /> 		 
			 

    </div>

  </div>
  
  
  <div id="imag_logo" >
	   <img style="width:200px; " style="display:none;" id="default_img_logo"  src="<?php echo $item_image;?>"  />
	 </div>
	  

  
 <style>
 
 .alp input#dropbtn_logo {
    width: 100% !important;
    text-align: left;
}

.dropdown_logo {
    position: relative;
    display: inline-block;
    width: 100%;
}

#dropbtn_logo
{
	margin-bottom:20px !important;

}

.four.alpha.columns.alp {
    margin-top: -36px !important;
}

</style>
  

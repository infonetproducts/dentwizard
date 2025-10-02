 
  
   
   

 
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
	   <select name="item_logos_id[]" multiple="multiple" class="image-picker">
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
				 	 $selected = 'selected="selected"';
				 }
				 
		?>
		
			<option <?php echo $selected ; ?>  data-img-src='<?php echo $logo_image; ?>' value='<?php echo $client_logo['ID'];?>'><?php echo $client_logo['Name'];?></option>
			
			 
		
		<?php
			}
		
		}
		?>
		
      </select>
	  
	 <!-- <select name="item_logos_id[]" multiple="multiple" class="image-picker">
        <option data-img-src='http://placekitten.com/220/200' value='1'>Cute Kitten 1</option>
        <option data-img-src='http://placekitten.com/180/200' value='2'>Cute Kitten 2</option>
        <option data-img-src='http://placekitten.com/130/200' value='3'>Cute Kitten 3</option>
        <option data-img-src='http://placekitten.com/270/200' value='4'>Cute Kitten 4</option>
      </select>-->
	  
	  
	  
    </div>
	
 
 

  </div>

  <script type="text/javascript">

    jQuery("select.image-picker").imagepicker({
      hide_select:  false,
    });

    jQuery("select.image-picker.show-labels").imagepicker({
      hide_select:  false,
      show_label:   true,
    });

    jQuery("select.image-picker.limit_callback").imagepicker({
      limit_reached:  function(){alert('We are full!')},
      hide_select:    false
    });

    var container = jQuery("select.image-picker.masonry").next("ul.thumbnails");
    container.imagesLoaded(function(){
      container.masonry({
        itemSelector:   "li",
      });
    });

  </script>

  </form>

  

<?php
include("../include/db.php");
$item_logo_ids = "5,6,7,8";
 


 
 $rs_client_logos =   mysql_query( "select * from ClientLogos where ID IN ($item_logo_ids) " )  ;
 

?>

<!doctype html>
<html lang="en">

<!-- Mirrored from rvera.github.io/image-picker/ by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 01 Nov 2018 08:55:22 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->
<head>
  <meta charset="utf-8">
  <title>Image Picker</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.css">
  <link rel="stylesheet" type="text/css" href="examples.css">
  <link rel="stylesheet" type="text/css" href="image-picker/image-picker.css">
   <script src="https://code.jquery.com/jquery-3.0.0.min.js" type="text/javascript"></script>
  <script src="js/prettify.js" type="text/javascript"></script>
  <script src="js/jquery.masonry.min.js" type="text/javascript"></script>
  <script src="js/show_html.js" type="text/javascript"></script>
  <script src="image-picker/image-picker.js" type="text/javascript"></script>

</head>
<body>
  <form method="post">
  <div id="container">
<div style="color:red;">
<?php
if(isset($message)){ echo $message ; }
?>
 </div>
 
 
  
    
    <div class="picker">
      
	  
	   <select name="item_logos_id[]" multiple="multiple" class="image-picker">
        <?php
		// rs_client_logos
		
		if( mysql_num_rows($rs_client_logos) > 0 )
		{
			while( $client_logo = mysql_fetch_assoc($rs_client_logos) )
			{
				 $logo_image = "../pdf/$client_logo[CID]/".$client_logo['image_name'];
				 
				 $selected = '';
				 
				 
				 
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

 
</body>

<!-- Mirrored from rvera.github.io/image-picker/ by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 01 Nov 2018 08:55:27 GMT -->
</html>


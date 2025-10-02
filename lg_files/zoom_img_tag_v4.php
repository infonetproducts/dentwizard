
<?php
$item_color_list = get_item_color_drop_down_list($item_id) ;
 $item_color_image_list = get_item_color_image($item_id) ;
//print_r($item_color_list);
//print_r($item_color_image_list);

if(!empty($item_color_list))
{
	$first_key = key($item_color_list); // First element's key
	//echo $first_key;
	 $color_img_first = $item_color_image_list[$first_key];
	$first_img = "pdf/$CID/".$color_img_first;
	?>
     <span id="color_img_box" style="display:none;">
    
     <a  
             id="Zoom-1"
                href="<?php echo $first_img;?>"
                data-image="<?php echo $first_img;?>"
                class="MagicZoom" 
            
        >
            <img id="default_img"  src="<?php echo $first_img;?>" srcset="<?php echo $first_img;?>"
                alt=""/>
        </a>
        
        
     
        
        <div class="selectors">
        
         <!-- <a
                data-zoom-id="Zoom-1"
                href="<?php echo $first_img;?>?h=1400"
                data-image="<?php echo $first_img;?>?h=400"
                data-zoom-image-2x="<?php echo $first_img;?>?h=2800"
                data-image-2x="<?php echo $first_img;?>?h=800"
            >
                <img srcset="<?php echo $first_img;?>?h=120 2x" src="<?php echo $first_img;?>?h=60"/>
            </a>-->
            
               <?php
			 	if(!empty($item_color_image_list))
				{
					foreach($item_color_image_list as $key_image=>$color_image)
					{
					
					 $tmp_image = '';
					if($color_image!="")
					{
					
						 $tmp_image = "pdf/$CID/".$color_image;
					
					}
					
					 list($width, $height, $type, $attr) = getimagesize($tmp_image);
				
				$width = 100;
				$height = 100 ;	 
		?>
        
            <a
              
                data-zoom-id="Zoom-1"
                href="<?php echo $tmp_image;?>"
                data-image="<?php echo $tmp_image;?>"
               
                
                
            >
                <img style="max-width:100px;max-height:100px;" srcset="<?php echo $tmp_image;?>" src="thumbnail.php?file=<?php echo $tmp_image;?>&width=<?php echo $width ;?>&height=<?php echo $height;?>&maxw=100&maxh=100"/>
            </a>
            
              <?php
					 }
				 
				}
				 ?>
			    
                
           
        </div>
    
    
    </span>
    
    <?php
	
} 

?>

 <span id="additional_img" style="display:block;">
 
 <a  
            
             id="Zoom-2"
                href="<?php echo $item_image;?>"
                data-image="<?php echo $item_image;?>"
                class="MagicZoom" 
                
        >
            <img id="default_img_2"  src="<?php echo $item_image;?>" srcset="<?php echo $item_image;?>"
                alt=""/>
        </a>
        
     
        
        <div class="selectors">
        
         <!-- <a
                data-zoom-id="Zoom-2"
                href="<?php echo $item_image;?>"
                data-image="<?php echo $item_image;?>"
                class="MagicZoom" 
            >
            
            <?php
			 
					
					 list($width, $height, $type, $attr) = getimagesize($item_image);
					 
				$width = 100;
				$height = 100 ;	
			?>
            
            
                <img srcset="<?php echo $item_image;?>" src="thumbnail.php?file=<?php echo $item_image;?>&width=<?php echo $width ;?>&height=<?php echo $height;?>&maxw=100&maxh=100"/>
            </a>-->
            
               <?php
			 	$sql_get_additional_image = "select * from items_additional_image where item_id = '$item_id' and cid = '$CID' order by  display_order asc ";
				$rs_additional_image = mysql_query($sql_get_additional_image);
				if(mysql_num_rows($rs_additional_image)>0)
				{ 
					
				?>
        
        
        <?php
		while($additiona_image_detail = mysql_fetch_assoc($rs_additional_image))
					{
					
					$image_path = "admin/additional_item_images/".$additiona_image_detail["file_name"];
					
					 list($width, $height, $type, $attr) = getimagesize($image_path);
					 
					 
				$width = 100;
				$height = 100 ;	
		?>
        
            <a
               
                 data-zoom-id="Zoom-2"
                href="admin/additional_item_images/<?php echo $additiona_image_detail["file_name"];?>"
                data-image="admin/additional_item_images/<?php echo $additiona_image_detail["file_name"];?>"
               
            >
                <img  style=" max-width:100px; max-height:100px;"  srcset="thumbnail.php?file=<?php echo $image_path;?>&width=<?php echo $width ;?>&height=<?php echo $height;?>&maxw=100&maxh=100" src="thumbnail.php?file=<?php echo $image_path;?>&width=<?php echo $width ;?>&height=<?php echo $height;?>&maxw=100&maxh=100"/>
            </a>
            
              <?php
					 }
				 
				}
				 ?>
			    
                
           
        </div>
        
      
 </span>   
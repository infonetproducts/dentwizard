<?
ob_start();
include("setting.php");

include_once("include/start.php");
if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';
$uid=$_SESSION['AID'];


$orderid = $_GET['orderid'];
$formid = $_GET['formid'];
$itemid = $_GET['itemid'];
$item = $formid;

 $sql = "select Description,MinQTY,MaxQTY,UserID,Price , ID,item_type , item_price_type , price_multi , require_file_upload , visibility , item_title from Items where FormID='$formid' ";
list($itemdesc,$min,$max,$u,$price,$item_id,$item_type,$item_price_type,$price_multi , $require_file_upload , $visibility,$item_title)=@mysql_fetch_row(mysql_query($sql));

if(isset($_GET["f"]) and $_GET["f"]==1)
{
	
		
}



$sql_order_detail = " select * from Orders where ID='$orderid'   ";
$rs_order_detail = mysql_query($sql_order_detail);
$row_order_detail = mysql_fetch_assoc($rs_order_detail);


$sql_order_items = "select * from OrderItems where OrderRecordID='$orderid' and FormID = '$formid' ";
$rs_items = mysql_query($sql_order_items);
$row_item = mysql_fetch_assoc($rs_items);





function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

// custom_order?link=yes&formid=&offset

if(isset($_GET['ov']) and $_GET['formid']!="")
{
	$k = $_GET['formid'];
	$v = '';
	/*$_SESSION['Order'][$k] = $v;
	$_SESSION['item_id_custom_order'] = $k;
	$_SESSION['Order_type'][$k] = 'Y'; */
	header("location:edit_custom_order.php");
}
if(isset($_GET['f']) and $_GET['f']==1)
{
	//unset($_SESSION['item_id_custom_order']);
}

if(isset($_GET['link']) and $_GET['link']=="yes")
{
	
	
	$formid = $_GET['formid'];
	$typeid    = $_GET['typeid'];
	// orderid
	
		mysql_query("delete from required_file_link where formid='$formid' and orderid	='$orderid' and id = '$typeid'	 ") or die ( mysql_error() );
	
	//unset($_SESSION['file'][$formid][$offset]);
	//unset($_SESSION['file_resolution'][$formid][$offset]);
	/// orderid=23039&itemid=6333&formid=819ec4
	header("location:edit_custom_order.php?orderid=".$_GET['orderid'].'&itemid='.$_GET['itemid'].'&formid='.$_GET['formid']);
	
}



//pr($_SESSION['Order_type']);


// save_item   qty   desc_custom
if(isset($_POST["save_item"]))
{
	

	
	
	
		 if($_POST["qty"]!="")
		 {
			$formid = $_POST["formid"];
			
			//$_SESSION['Order'][$formid] = $_POST["qty"];
			
			//$_SESSION['qty'][$formid] = $_POST["qty"];
			
			//$_SESSION['desc_custom'][$formid] = $_POST["desc_custom"];
			
			//$_SESSION['price_custom'][$formid] = $_POST["price_custom"];
			
			//$_SESSION['custom_tab_order'] = 1;
			
			//$_SESSION['Order_type'][$formid] = 'Y';
			
			$Quantity = $_POST["qty"];
			$item_title = mysql_escape_string($_POST["desc_custom"]);
			$FormDescription = mysql_escape_string($_POST["description_custom"]);
			$Price = $_POST["price_custom"];
			
			if($visibility=="Hidden")
			{
				
				 $sql_update_items = "update Items  set 
						
						item_title = '$item_title' ,
						Description = '$FormDescription' ,
						Price = '$Price' 
						where  FormID='$formid'  ";
	        
				mysql_query($sql_update_items) or die ( mysql_error() );
				
				
			$sql_update_orderitems = "update OrderItems  set 
					
						Quantity = '$Quantity' ,
						FormDescription = '$FormDescription' ,
						Price = '$Price' 
						
						where OrderRecordID ='$orderid' and FormID ='$formid'  ";
	        
			mysql_query($sql_update_orderitems) or die ( mysql_error() );
	
			
				
			}else{
			
			$sql_update_orderitems = "update OrderItems  set 
					
						Quantity = '$Quantity'   
						
						where OrderRecordID ='$orderid' and FormID ='$formid'  ";
	        
			mysql_query($sql_update_orderitems) or die ( mysql_error() );
	
			
			
			
			}
			
			$_POST = '';
			
			$sql_order_items = "select * from OrderItems where OrderRecordID='$orderid' and FormID = '$formid' ";
			$rs_items = mysql_query($sql_order_items);
			$row_item = mysql_fetch_assoc($rs_items);


			echo '<script type="text/javascript">
				
				window.opener.location.reload(true);
				
			   </script>';
			
		 }
		
	
}


if(isset($_POST["save_link"]))
{
	
	
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	
	
	if($_POST["link"]!="")
	{
		
		
			$formid = $_POST["formid"];
			//$_SESSION['link'][$formid][] = addhttp($_POST["link"]);
			//$_SESSION['link_resolution'][$formid][] = $_POST["link_resolution"];
			$link_value = addhttp($_POST["link"]);
			$sql_link = "
					insert required_file_link 
						set
							 orderid='$orderid',
							 cid='$CID', 
							 user_id='$AID',
							 link='$link_value',
							 type	 = 'link',
							 formid	 = '$formid'
							
					";
					
					
					mysql_query($sql_link) or die($sql_link); 
			
			
			
			
			$_POST = '';
			
		
	}
}




if(isset($_GET['file']) and $_GET['file']=="yes")
{
	
	
	
	$formid = $_GET['formid'];
	$typeid    = $_GET['typeid'];
	// orderid
	
		mysql_query("delete from required_file_link where formid='$formid' and orderid	='$orderid' and id = '$typeid'	 ") or die ( mysql_error() );
	
	//unset($_SESSION['file'][$formid][$offset]);
	//unset($_SESSION['file_resolution'][$formid][$offset]);
	/// orderid=23039&itemid=6333&formid=819ec4
	header("location:edit_custom_order.php?orderid=".$_GET['orderid'].'&itemid='.$_GET['itemid'].'&formid='.$_GET['formid']);
	
		
	// edit_custom_order.php?orderid=23039&itemid=6333&formid=819ec4
}


if(isset($_POST["save_notes"]))
{
	 $formid = $_POST["formid"];
	 $description = $_POST["description"];
	// custom_order_desc
	
	
	  $sql_exits_check = "  
	 
	 select * from required_note_file
						 
						where 
							orderid='$orderid'  
							and  formid='$formid' 
	 
	 ";
	 
	 
	$rs_note_exits =  mysql_query($sql_exits_check);
	
	if(mysql_num_rows($rs_note_exits)==0)
	{
		$sql_order = "select * from Orders where ID = '$orderid' "; 
		$order_info=@mysql_fetch_assoc(mysql_query($sql_order)) ;
		$CID_custom = $order_info["CID"];
		$uid = $order_info["UserID"];
		
			 
				
				$sql_desc = "
				insert into required_note_file 
					set
						 orderid='$orderid',
						 cid='$CID_custom', 
						 user_id='$uid',
						 notes='$description',
						 formid	 = '$formid' 
						 
				";
				
	  mysql_query($sql_desc) or die($sql_desc); 
			
 
	 
		
	}else{
	
	
	$sql_note_update = "update required_note_file  set 
						notes = '$description' 
		where orderid='$orderid' and formid='$formid'  ";
	mysql_query($sql_note_update) or die ( mysql_error() );
	
	
	}
	$_POST = '';
}


if(isset($_POST["save_to_continue"]))
{
	
	
	if($_POST["description"]!="")
	{
		$formid = $_POST["formid"];
		//$_SESSION['description'][$formid]  =  $_POST["description"] ;
		//$_SESSION['item_due_date'][$formid]  =  $_POST["item_due_date"] ;
		
		
	}
	
	if($_POST["item_due_date"]!="")
	{
		 $formid = $_POST["formid"];		
		 //$_SESSION['item_due_date'][$formid]  =  $_POST["item_due_date"] ;
		
		
	}
	
	$_POST = '';
	
	header("location:cart.php");
	exit;
	
}




// cart


$upload_path = "../admin/userfile/";

if(isset($_POST["upload_file_submit"]))
{
	
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	if($_POST["link_resolution"]!="")
		{
			
		
	
				if($_FILES["upload_file"]["name"]!="")
				{
					// userfile
					$file_name_user = time()."_".$_FILES["upload_file"]["name"];
					if(move_uploaded_file($_FILES["upload_file"]["tmp_name"],$upload_path.$file_name_user))
					{
					
						$formid = $_POST["formid"];
						//$_SESSION['file'][$formid][] = $file_name_user;
						//$_SESSION['file_resolution'][$formid][] = $_POST["link_resolution"];
						
						$resolution = $_POST["link_resolution"];
						
						
					
					//$_SESSION['link_resolution'][$item][$key_add];
					$uid = $AID;
					$sql_file = "
					insert required_file_link 
						set
							 orderid='$orderid',
							 cid='$CID', 
							 user_id='$uid',
							 filename='$file_name_user',
							 type	 = 'file',
							 formid	 = '$formid',
							 resolution = '$resolution'
							 
					";
					
					mysql_query($sql_file) or die($sql_file); 
					
					
						
						
						$_POST = '';
					
					}
				}
				
	
		}else{
			$error_file_resolution = "Please select resolution";
		}
}







?>




<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
 

<script type="text/javascript" src="../javascript.js"></script>
<script type="text/javascript" src="../datepicker.js"></script>
<link rel="stylesheet" href="../datepicker.css" />

<section  class="titlebar">
	<div class="container">
		<div class="sixteen columns">
			<h2>Edit Item To Order: <?php echo $order_detail["OrderID"]?></h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Add Item To Order</li>
				</ul>
			</nav>
		</div>
	</div>
</section>
<!-- Content
================================================== -->
<!-- Container -->
<div class="container">
  
  <!-- Billing Details / Enc -->
  <!-- Checkout Cart -->
  
   
  
 <div class="six columns centered">

	<div align="center">

		  <?php if($is_enable_custom_report=="Y"){?>
       <a style="color:#00a2db;" href="custom_orders_report.php">View Custom Order Report</a>
     
       <?php } ?>
	   
	   <?php
	   if($is_cubicle_order==1)
	   {
	   ?>
		| <a style="color:#00a2db;" href="order_cubical_signs.php">Order Cubicle Signs</a>
		<?php
		}
		?>
        
    </div>    

		<div class="tabs-container">
			 
             
             
        
            
			<div class="tab-content" id="tab1" style="display: block;">

			
            <div align="center">
            To re-order an existing item, search for your item below:
            
            
             <br/><br/>
        
         <strong>Item Search:</strong><input type="text" size="30" id="itemsrch" onkeyup="check_item()" autocomplete="off">
          (Enter Item ID or Description)
       
         <div id="itempicker"></div>
         
         To order a new item, please specify the item details and upload any required files using the form below.<br />
  You must SAVE each section upon completion.
  
  <br/>
  
  <em>When you are finished, click the&nbsp;<strong>Continue to Cart</strong>&nbsp;button.</em>
  
            
            </div>
                
             <br/>		   
              <p align="center" class="form-row form-row-wide" style="background-color:#00a2db">
				<label for="username"><strong>Item Details</strong></label>
                </p>  
      
         <?php
		/*print_r("<pre>");
		print_r($_SESSION['Order']);*/
		
	
		

		
	
	$item = trim($item);
	$quan = trim($quan);
	 $sql = "select Description,MinQTY,MaxQTY,UserID,Price , ID,item_type , item_price_type , price_multi , require_file_upload , visibility,item_title from Items where FormID='$item' ";
	list($itemdesc,$min,$max,$u,$price,$item_id,$item_type,$item_price_type,$price_multi , $require_file_upload , $visibility,$item_title)=@mysql_fetch_row(mysql_query($sql));
	$qbg='';
  
	 //$visibility;
	 
	 	$price  =  how_many_decimal_place_display_new($price,1);  
  	
	?> 
  
              <form method="post">
             <input type="hidden" name="type" value="item" />
             <input type="hidden" name="formid" value="<?php echo $item;?>" />
      
                  <?php
					if(isset($visibility) and $visibility=="")
					{
					?>
				<p class="form-row form-row-wide">
						<label for="username">Item #:</label>
                        <?php echo $item;?>
					
                    </p>
				<?php
					}
					?>
                    
                    
                    
                    
					 <p class="form-row form-row-wide">
						<label for="username">Item Title:</label>
                        
                         <?php
					if(isset($visibility) and $visibility=="")
					{
					?>
            				<?php echo $item_title;
			
					}
					?>
            
                
                <?php
					if(isset($visibility) and $visibility=="Hidden")
					{
					?>
                    
                    <input value="<?php echo $item_title;?>" name="desc_custom"  type="text" size="35" maxlength="50">
                    <?php
					}
					?> 
                        
                        
					
                    </p>
                    
                    
                    
                    
                    <p class="form-row form-row-wide">
						<label for="username">Item Description:</label>
                        
                          <?php
					if(isset($visibility) and $visibility=="")
					{
					?>
            <?php echo $itemdesc;
			
					}
			?>
            
             
             <?php
					if(isset($visibility) and $visibility=="Hidden")
					{
					?>
                    
                      <textarea  name="description_custom" cols="100" rows="5"><?php if(isset($itemdesc)){ echo $itemdesc; }?></textarea>
                    
                    
                <?php
					}
				?>
					
                    </p>
                    
                    
                    
                    
					 <p class="form-row form-row-wide">
						<label for="username">Quantity:</label>
                         
                         <input  onkeyup="calculate(this);" id="qty" name="qty" type="text" size="5" maxlength="15" value="<?php if(isset($row_item['Quantity'])){ echo $row_item['Quantity']; }?>"> 
					
                    </p>
                    
                     
          <?php
		  
		/*  $decimal_place =  how_many_decimal_place_display($price);
	      $price = number_format($price,$decimal_place );*/
	  
				
					if(isset($visibility) and $visibility=="")
					{
					
					// $_SESSION['price_custom'][$item];
					
					//print_r("<pre>");
					//print_r($_SESSION);
						
					?>
                    
                     <p class="form-row form-row-wide">
						<label for="username">Unit Price:</label>
                         
                      
           
             <?php
			if(isset($visibility) and $visibility=="")
					{
						
			?>
             
           
            
              <input  onkeyup="calculate(this);" id="price_custom" type="hidden" size="5" maxlength="15" value="<?php if(isset($price)){ echo $price; }?>">  
              
            <?php
					}
			?>
            
            
            $<?php echo $price;?>
					
                    </p>
                    
                    
                    
              <?php
					}
				  ?>        
                    
               
               <?php
					if(isset($visibility) and $visibility=="Hidden")
					{
						
					// $_SESSION['price_custom'][$item];
					
					//print_r("<pre>");
					//print_r($_SESSION);
						
					?>
					 <p class="form-row form-row-wide">
						<label for="username">Unit Price</label>
                        $<input onkeyup="calculate(this);" id="price_custom"  name="price_custom" type="text" size="5" maxlength="15" value="<?php if(isset($price)){ echo $price; }?>">  
					
                    </p>     	
				
     	
         <?php
					}
				  ?>
     
     
                   <?php 
				  $temp_total_price = '';
				 
				  
				   $temp_total_price = $row_item['Quantity'] * $price;
				 
				  
				  
				  ?>
                  
                  
                   
                  
                   <script type="text/javascript" src="../js/jquery.js"></script>
                    <script type="text/javascript" src="../common_function.js"></script>
				   <script type="text/javascript">
				   
				   
				 
				   
				   function calculate(obj)
				   {
					   
					   id = $(obj).attr('id');
					  //alert(id);
					   if(id=="qty")
					   {
						   qty = $(obj).val();
						   price_custom = $("#price_custom").val();
						  
						   if( price_custom!="" && qty!="" )
						   {
								total_price =   qty *  price_custom;
								//total_price = total_price.toFixed(2);
								
								total_price = how_many_decimal_place_display_new(total_price,1);
								
								if(Number(total_price))
								{
									// alert(total_price);
									 
									$("#total_price_custom").val(total_price);
								}else{
									$("#total_price_custom").val('');
								}
								
								if(price_custom=="0.00")
								{
									$("#total_price_custom").val('0.00');
								}
								
								
						   }
					   }
					   
					  if(id=="price_custom")
					  {
						   price_custom = $(obj).val();
						   qty = $("#qty").val();
						   if(price_custom!="" && qty!="")
						   {
								total_price =   qty *  price_custom;
								//total_price = total_price.toFixed(2);
								
								total_price = how_many_decimal_place_display_new(total_price,1);
								
								if(Number(total_price))
								{
									$("#total_price_custom").val(total_price);
								}else{
									$("#total_price_custom").val('');
								}
								
						   }
					   }
					   
					   if(id=="total_price_custom")
					   {
						   total_price_custom = $(obj).val();
						   price_custom = $("#price_custom").val();
						   qty = $("#qty").val();
						  
						   if( qty!="" && total_price_custom!="")
						   {
								unit_price =   total_price_custom /  qty;
								
								//unit_price = unit_price.toFixed(2);
							
								unit_price = how_many_decimal_place_display_new(unit_price);
								
								if(Number(unit_price))
								{
										$("#price_custom").val(unit_price);
								}else{
										$("#price_custom").val('');
								}
								
								
						   }else{
							   $("#price_custom").val('');
						   }
						   
					   }
					   
					   // total_price_custom  price_custom  qty
			}
			
			
			</script>
     
     		
            
					 <p class="form-row form-row-wide">
						<label for="username">Total Price:</label>
                       <?php
						
			if(isset($visibility) and $visibility=="")
					{
						
						//$temp_total_price  =  how_many_decimal_place_display_new($temp_total_price,1);  
						
					$temp_total_price  =  number_format($temp_total_price,2); 
				   $temp_total_price = str_replace(",","",$temp_total_price); 

			?>
             
           <input readonly="readonly"   id="total_price_custom" name="" type="text" size="5" maxlength="15" value="<?php if($temp_total_price!=""){echo $temp_total_price;}?>">  
           
           
            <?php
					}else{
						
						//	$temp_total_price  =  how_many_decimal_place_display_new($temp_total_price,1);  
						
						$temp_total_price  =  number_format($temp_total_price,2); 
				   $temp_total_price = str_replace(",","",$temp_total_price); 
			?>
                        
                          <input onkeyup="calculate(this);"  id="total_price_custom" name="" type="text" size="5" maxlength="15" value="<?php if($temp_total_price!=""){echo $temp_total_price;}?>">  
                        
                  <?php
					}
				  ?> 
                       
                          
                      
                	
                    </p>
                    
                     <p class="form-row form-row-small">
						 <input type="submit" name="save_item" value="Save Item Details" />     
                        </p>
                    
                    
     </form> 
     
     
     
    
     <form method="post" enctype="multipart/form-data">
                       
                       <input type="hidden" name="type" value="file" />
                        <input type="hidden" name="formid" value="<?php echo $item;?>" />
                        
                        
           
            <p align="center" class="form-row form-row-wide" style="background-color:#00a2db">
				<label for="username"><strong>Item File(s)</strong></label>
             </p>  
      
           
             <p class="form-row form-row-wide">
                <label for="username">Upload File(s) For This Item:</label>
                
                  <?php if(isset($error_file_resolution)){?>
                        
                        <span style="color:red;"><?php echo $error_file_resolution;?></span>
                        
                        <?php } ?> 
               
               
                <select name="link_resolution" >
                <option <?php if(isset($_POST["link_resolution"]) and $_POST["link_resolution"]=="Specify Resolution"){ echo 'selected="selected"'; }?>  value="">Specify Resolution</option>
                <option <?php if(isset($_POST["link_resolution"]) and $_POST["link_resolution"]=="High Resolution"){ echo 'selected="selected"'; }?> value="High Resolution">High Resolution</option>
                <option <?php if(isset($_POST["link_resolution"]) and $_POST["link_resolution"]=="Low Resolution"){ echo 'selected="selected"'; }?> value="Low Resolution">Low Resolution</option>
                </select>
                 <br/>
                 <input type="file" name="upload_file"   />
                 
                 <br/> <br/>
                 
                
              
            
            </p>
            
              <p class="form-row form-row-small">
              	<input type="submit" name="upload_file_submit" value="Upload File" />
              
              </p>
                        
                        
      </form>   
      
      
           <?php
					$sql_file_link = "select type , filename , link , resolution , id from required_file_link     where orderid='$orderid' and formid='$formid' and type ='file' ";
				$rs_link = mysql_query($sql_file_link);
				
				while(list($type,$filename,$link , $resolution , $require_file_table_id )=@mysql_fetch_row($rs_link))
				{
			
                    ?>
                    
                    
					 <p class="form-row form-row-wide">
						 
                          <a href="<?php echo $upload_path.$filename; ?>" target="_blank"> <?php echo wordwrap($filename, 70, "<br />\n",true);  ?> </a>
						
						
						
						 
 </a>
 

                    
                    (<?php echo $resolution;?>)
                    
                     <a href="edit_custom_order.php?file=yes&formid=<?php echo $formid;?>&typeid=<?php echo $require_file_table_id?>&orderid=<?php echo $orderid;?>&itemid=<?php echo $itemid;?>" ><img src="../images/delete.png" width="16" height="16" align="absmiddle" /></a>
					
                    </p>
                    
      
      
                      
                    
                    <?php
					
						}
					//}
					?>
                    
                          <form method="post">
                    	<input type="hidden" name="type" value="link" />
                        <input type="hidden" name="formid" value="<?php echo $item;?>" />
                        
                       
					 <p class="form-row form-row-wide">
						
                        <?php if(isset($error_link_resolution)){?>
            
            <span style="color:red;"><?php echo $error_link_resolution;?></span>
            
            <?php } ?>
                        
                        
                         <input value="<?php if(isset($_POST["link"])){ echo $_POST["link"]; }?>" name="link" type="text" size="30" maxlength="100" />
                            
                           
					
                    </p> 
                    
             <p class="form-row form-row-small">
              	 <input type="submit" name="save_link" value="Save Link" />
              
              </p>
                        
                      </form>       
                    
                    
					 <p class="form-row form-row-wide">
						
                        
                      	
					<?php
				$sql_file_link = "select type , filename , link , resolution , id from required_file_link     where orderid='$orderid' and formid='$formid' and type ='link' ";
				$rs_link = mysql_query($sql_file_link);
				
				while(list($type,$filename,$link , $resolution , $require_file_table_id )=@mysql_fetch_row($rs_link))
				{
					
					
					?>
                    
                    <a href="<?php echo $link;?>" target="_blank"> <?php echo wordwrap($link, 70, "<br />\n",true);  ?> </a>
                    
                   
                    <a href="edit_custom_order.php?link=yes&formid=<?php echo $formid;?>&typeid=<?php echo $require_file_table_id?>&orderid=<?php echo $orderid;?>&itemid=<?php echo $itemid;?>" ><img src="../images/delete.png" width="16" height="16" align="absmiddle" /></a>
                    <br/>
                    
                    <?php
					
						
					}
					?>
                       
					
                    </p>
                    
                    
         <form method="post">
        <input type="hidden" name="type" value="desc" />
        <input type="hidden" name="formid" value="<?php echo $item;?>" />
              <p align="center" class="form-row form-row-wide" style="background-color:#00a2db">
				<label for="username"><strong>Order Instructions</strong></label>
             </p>
             
             <?php
		   	$sql_note = "select 	id ,notes,custom_order_desc  from required_note_file     where orderid='$orderid' and formid='$formid'  ";
			$rs_note = mysql_query($sql_note);
			
			
			list($note_id,$notes)=@mysql_fetch_row($rs_note)
				
				?>
            
             <p class="form-row form-row-wide">
                <label for="username">Additional Notes / Order Instructions:</label>
                
                 <textarea name="description" cols="100" rows="5"><?php if(isset($notes)){ echo $notes; }?></textarea>
                <br/>
                
            
            </p>
                    
        <p class="form-row form-row-small">
              	  <input type="submit" name="save_notes" value="Save Order Instructions" />
              
              </p>
        
        </form>
        
        
        
					 <p class="form-row form-row-small">
					
                      <!--<input type="button" name="Submit5" value="Continue to Cart" onclick="self.location.href='shopping-cart.php'">--> 
                       
                      
    	<input onclick="location.href='track.php#<?php echo $orderid;?>'" type="button" name="save_final_item_order" value="Save Order & Return To Tracking Page"  >
            
                       
					
                    </p>
                    
         
     
    
			</div>
		</div>
	</div>
  <!-- Checkout Cart / End -->
</div>
<!-- Container / End -->
<div class="margin-top-50"></div>

  <script type="text/javascript">

function close_popup()
{	
	
	//window.location.href='track.php#<?php echo $orderid;?>';
	window.location = 'track.php#<?php echo $orderid;?>';
	self.close();	
	
}
</script>



<?php include("footer.php");?>


 
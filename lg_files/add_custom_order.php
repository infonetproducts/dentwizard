<?
ob_start();
include("setting.php");
include_once("include/start.php");
include_once("../category_function.php");
if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';
$uid=$_SESSION['AID'];



function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
$orderid = $_GET['orderid'];
// custom_order?link=yes&formid=&offset

$order_detail=@mysql_fetch_assoc(mysql_query("select * from Orders where ID='$orderid'"));

if(isset($_POST["save_final_item_order"]))
{
	
	 $k_custom = $_SESSION['item_id_custom_order_add']; 
	if($k_custom!="")
	{
		 $qty = $_SESSION['Order_add'][$k_custom]; 
		
		if($qty!="")
		{
			
			
$sql_select_item_custom = "select ID,Description,Price,InventoryAlertQuantity,VendorID,InventoryQuantity , ContactEmail from Items where CID=$CID
				and (FormID='$k_custom' or OtherIDs='$k_custom' or OtherIDs like '% $k_custom %')"; 
				
				 
			$itm_custom_order=@mysql_fetch_assoc(mysql_query($sql_select_item_custom)) ;
			
			$Price_custom = mysql_escape_string( $_SESSION["price_custom_add"][$k_custom] );
			$Description_custom = mysql_escape_string( $_SESSION["description_custom_add"][$k_custom] );
			
			$item_title = mysql_escape_string( $_SESSION["desc_custom_add"][$k_custom] );
			
			
			
			if(!$itm_custom_order['ID']) 
			{ 
				// no match!
				//echo 'item not found';
				//echo '<br/>';
				
				
				
				// mysql_escape_string(
				
				$insert_item_sql = "INSERT INTO Items SET
									FormID = '$k_custom',
									Description = '$Description_custom',
									item_title = '$item_title' ,
									CID = '$CID',
									visibility = 'Hidden',
									Price = '$Price_custom'
									
									";
				mysql_query($insert_item_sql) or die (mysql_error()) ;
				
				
				$sql_select_item_custom = "select ID,Description,Price,InventoryAlertQuantity,VendorID,InventoryQuantity , ContactEmail from Items where CID=$CID
				and (FormID='$k_custom' or OtherIDs='$k_custom' or OtherIDs like '% $k_custom %')"; 
				
				$itm_custom_order=@mysql_fetch_assoc(mysql_query($sql_select_item_custom)) ;
				
			}
			
		
		
		 $sql_orderitems_max = "select max(ID) as max_id from OrderItems where 
				 OrderRecordID='$orderid' "; 
				
				
		$rs_order_items_max = mysql_query($sql_orderitems_max);
		$row_order_items_max = @mysql_fetch_assoc($rs_order_items_max) ;
		
		
		$sequence_no = '';
		if(mysql_num_rows($rs_order_items_max) > 0 )
		{
			$sequence_no = 1 + $row_order_items_max['max_id'];	
						
		}else{
			$sequence_no = 1;	
		}
			
			
	$desc = $itm_custom_order["Description"]	;
	 $sql_insert_orderitems = "insert OrderItems set 
					OrderRecordID='$orderid',
					ID = '$sequence_no',
					ItemID='$itm_custom_order[ID]',
					FormID='$k_custom',
					FormDescription='$desc',
					Quantity='$qty',
					Price='$itm_custom_order[Price]' , 
					is_custom_order_item = 'Y'
					 ";
			
	mysql_query($sql_insert_orderitems) or die( mysql_error() );
			
	
			
		if(isset($_SESSION['file_add'][$k_custom]))
			{
				
				foreach($_SESSION['file_add'][$k_custom] as $key_add=>$file_name)
				{
					$resolution = $_SESSION['file_resolution_add'][$k_custom][$key_add];
					
					//$_SESSION['link_resolution'][$item][$key_add];
					
					$sql_file = "
					insert required_file_link 
						set
							 orderid='$orderid',
							 cid='$CID', 
							 user_id='$AID',
							 filename='$file_name',
							 type	 = 'file',
							 formid	 = '$k_custom',
							 resolution = '$resolution'
							 
					";
					
					mysql_query($sql_file) or die($sql_file); 
					
					
				}
				
				
				
			}
			
			
			if(isset($_SESSION['link_add'][$k_custom]))
			{
				
				foreach($_SESSION['link_add'][$k_custom] as $key_add=>$link_value)
				{
					
					
					//$resolution = $_SESSION['link_resolution'][$item][$key_add];
					
					$sql_link = "
					insert required_file_link 
						set
							 orderid='$orderid',
							 cid='$CID', 
							 user_id='$AID',
							 link='$link_value',
							 type	 = 'link',
							 formid	 = '$k_custom'
							
							 
					";
					
					
					mysql_query($sql_link) or die($sql_link); 
					
					
					
					
				}
			}
			
			if(isset($_SESSION['description_add'][$k_custom]))
			{ 
			
				//$notes = mysql_escape_string( $_SESSION['description_add'][$k_custom] ); 
				$desc_custom = mysql_escape_string( $_SESSION['description_add'][$k_custom] ); 
				
				
				$item_due_date = mysql_escape_string( $_SESSION['item_due_date_add'][$k_custom] ); 
				
				
				if($item_due_date!="")
				{
					$item_due_date = date("Y-m-d",strtotime($item_due_date));	
				}
				
				$sql_desc = "
				insert into required_note_file 
					set
						 orderid='$orderid',
						 cid='$CID', 
						 user_id='$AID',
						 item_due_date='$item_due_date',
						
						 formid	 = '$k_custom',
						 custom_order_desc = '$desc_custom'
				";
				
				mysql_query($sql_desc) or die($sql_desc); 
					 
			 
			}
			
			
			
		}
		
		
		
	}
	
	
	unset($_SESSION['Order_add'][$formid]);
	unset($_SESSION['file_add'][$formid]);
	unset($_SESSION['file_resolution_add'][$formid]);
	unset($_SESSION['link_add'][$formid]);
	unset($_SESSION['link_resolution_add'][$formid]);
	unset($_SESSION['item_due_date_add'][$formid]);
	unset($_SESSION['description_add'][$formid]);
	unset($_SESSION['qty_add'][$formid]);
	unset($_SESSION['price_custom_add'][$formid]);
	unset($_SESSION['desc_custom_add'][$formid]);
	unset($_SESSION['description_custom_add'][$formid]);
	unset($_SESSION['item_id_custom_order_add']);
	unset($_SESSION['Order_type_add'][$formid]);
	if(isset($_SESSION['qty'][$formid]))
	{
		unset($_SESSION['qty']);
		//unset($_SESSION['qty'][$formid]);
	}
	
	header("location:track.php");
	die;
	
}

if(isset($_GET['ov']) and $_GET['formid']!="")
{
	$k = $_GET['formid'];
	$v = '';
	$_SESSION['Order_add'][$k] = $v;
	$_SESSION['item_id_custom_order_add'] = $k;
	$_SESSION['Order_type_add'][$k] = 'Y'; 
	$orderid = $_GET['orderid'];
	header("location:add_custom_order.php?orderid=".$orderid);
	
	
}
if(isset($_GET['f']) and $_GET['f']==1)
{
	//unset($_SESSION['item_id_custom_order_add']);
	
	unset($_SESSION['Order_add'][$formid]);
	unset($_SESSION['file_add'][$formid]);
	unset($_SESSION['file_resolution_add'][$formid]);
	unset($_SESSION['link_add'][$formid]);
	unset($_SESSION['link_resolution_add'][$formid]);
	unset($_SESSION['item_due_date_add'][$formid]);
	unset($_SESSION['description_add'][$formid]);
	unset($_SESSION['qty_add'][$formid]);
	unset($_SESSION['price_custom_add'][$formid]);
	unset($_SESSION['desc_custom_add'][$formid]);
	unset($_SESSION['description_custom_add'][$formid]);
	unset($_SESSION['item_id_custom_order_add']);
	unset($_SESSION['Order_type_add'][$formid]);
	
	$orderid = $_GET['orderid'];
	
	
	
	header("location:add_custom_order.php?orderid=".$orderid);
	die;

	
	
	
}




if(isset($_GET['link']) and $_GET['link']=="yes")
{
	
	$formid = $_GET['formid'];
	$offset = $_GET['offset'];
	unset($_SESSION['link_add'][$formid][$offset]);
	unset($_SESSION['link_resolution_add'][$formid][$offset]);
	$orderid = $_GET['orderid'];
	header("location:add_custom_order.php?orderid=".$orderid);
	
}



if($action=='itemgds'){
	$CID = $_SESSION["CID"];
	$ss=mysql_real_escape_string(trim($_REQUEST['ss']));
	if(!$ss) exit;
	$rs=mysql_query("select ID,FormID,Description,item_title from Items where ( FormID like '%$ss%' or  Description like '%$ss%' ) and CID = '$CID' and visibility = '' limit 10");
	
	
	if(mysql_num_rows($rs)>0)
	{
	
	
	echo <<<EOM
<table align="center" style="min-width:500px;border:1px solid black;">

EOM;
	while($row=@mysql_fetch_assoc($rs)){
	
	
	$item_id_temp = $row['ID'] ;
	$item_cat_array = get_category_list_of_items($item_id_temp);
	$user_cat_array = get_category_list_of_user($uid);
	
	//pr($user_cat_array);
//pr($item_cat_array);
	//echo $uid;
	//die('here');
	
	$is_user_item = '' ; // blank means not user category item , 1 means user category item
	
	
	if(!empty($item_cat_array))
	{
		foreach($item_cat_array as $cat_id_item)
		{
			if( in_array($cat_id_item,$user_cat_array) )
			{
		
				if($is_user_item=="")
				{
					$is_user_item = 1 ;
				}
			
			}
			
		}
	
	}
	
	//echo $is_user_item;
	//die('here');
	
	if($is_user_item=="")
	{
		continue;
	}
	
	
	
		echo <<<EOM
<tr><td nowrap><a href="javascript:getitem('$row[ID]')">$row[FormID] - $row[item_title]</a></td></tr>
EOM;
	}
	echo "</table>";
	}
	exit;
}
if($action=='itemgd' && is_numeric($_REQUEST['ss'])){ // get dealer
	$CID = $_SESSION["CID"];
	$ss = mysql_real_escape_string(trim($_REQUEST['ss']));
	$row = @mysql_fetch_assoc(mysql_query("select ID,FormID,Description from Items where  ID='$ss' and CID='$CID' "));
	
	$orderid =  $_GET["orderid"];  
	
	
		if(isset($_SESSION['Order_type_add']))
		{
			$alraedy_exits = '';
			foreach($_SESSION['Order_type_add'] as $formid_k => $value_type)
			{
				if($value_type=="N")
				{
					
					if($formid_k==$row["FormID"])
					{
						if($alraedy_exits=="")
						{
							$alraedy_exits = 1;
						}
					}
					
				}
				
			}
		}
		
		
		// 
		$FormID = $row["FormID"];
		$sql_order_items = "select * from OrderItems where OrderRecordID='$orderid' and FormID = '$FormID' ";
		$rs_items = mysql_query($sql_order_items);
		$row_item = mysql_fetch_assoc($rs_items);
		
		if(mysql_num_rows($rs_items)  > 0 )
		{
			$alraedy_exits = 1;
		}

		
		
		if($alraedy_exits ==1)
		{
			echo "2**".$orderid;
			die;	
		}
	
		$k = $row["FormID"];
		$v = '';
		$_SESSION['Order_add'][$k] = $v;
		$_SESSION['item_id_custom_order_add'] = $k;
		$_SESSION['Order_type_add'][$k] = 'Y'; 
		echo "1**".$orderid;
		
		
		 
  
	
	exit;
}


//pr($_SESSION['Order_type']);


// save_item   qty   desc_custom
if(isset($_POST["save_item"]))
{
	
	
		 if($_POST["qty"]!="")
		 {
			$formid = $_POST["formid"];
			//$_SESSION['qty'][$formid][] = addhttp($_POST["link"]);
			
			$_SESSION['Order_add'][$formid] = $_POST["qty"];
			
			$_SESSION['qty_add'][$formid] = $_POST["qty"];
			
			$_SESSION['desc_custom_add'][$formid] = $_POST["desc_custom"];
			
			$_SESSION['description_custom_add'][$formid] = $_POST["description_custom"];
			
			$_SESSION['price_custom_add'][$formid] = $_POST["price_custom"];
			
			$_SESSION['custom_tab_order_add'] = 1;
			
			$_SESSION['Order_type_add'][$formid] = 'Y';
			
			$_SESSION['qty'][$formid] = $_POST["qty"];
			
			/*print_r("<pre>");
			print_r($_SESSION); die;*/
			
			$_POST = '';
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
			$_SESSION['link_add'][$formid][] = addhttp($_POST["link"]);
			$_SESSION['link_resolution_add'][$formid][] = $_POST["link_resolution"];
			
			
			/*print_r("<pre>");
			print_r($_SESSION['link']);
			
			print_r("<pre>");
			print_r($_SESSION['resolution']);*/
			
			$_POST = '';
			
		
	}
}


if(isset($_GET['remove_order']) and $_GET['remove_order']=="yes")
{
	$formid = $_GET['formid'];
	unset($_SESSION['Order_add'][$formid]);
	unset($_SESSION['file_add'][$formid]);
	unset($_SESSION['file_resolution_add'][$formid]);
	unset($_SESSION['link_add'][$formid]);
	unset($_SESSION['link_resolution_add'][$formid]);
	unset($_SESSION['item_due_date_add'][$formid]);
	unset($_SESSION['description_add'][$formid]);
	unset($_SESSION['qty_add'][$formid]);
	unset($_SESSION['price_custom_add'][$formid]);
	unset($_SESSION['desc_custom_add'][$formid]);
	unset($_SESSION['item_id_custom_order_add']);
	unset($_SESSION['Order_type_add'][$formid]);
	
	
	
	$orderid = $_GET['orderid'];
	header("location:add_custom_order.php?orderid=".$orderid);
	die;
	
	
}

if(isset($_GET['file']) and $_GET['file']=="yes")
{
	
	
	
	$formid = $_GET['formid'];
	$offset = $_GET['offset'];
	unset($_SESSION['file_add'][$formid][$offset]);
	unset($_SESSION['file_resolution_add'][$formid][$offset]);
	$orderid = $_GET['orderid'];
	header("location:add_custom_order.php?orderid=".$orderid);
	
		
	
}


if(isset($_POST["save_notes"]))
{
	
	 $formid = $_POST["formid"];
	
	if($_POST["description"]!="")
	{
		$_SESSION['description_add'][$formid]  =  $_POST["description"] ;
		
	}
	$_POST = '';
}


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
						$_SESSION['file_add'][$formid][] = $file_name_user;
						$_SESSION['file_resolution_add'][$formid][] = $_POST["link_resolution"];
						
						$_POST = '';
					
					}
				}
				
	
		}else{
			$error_file_resolution = "Please select resolution";
		}
}






if($_SESSION['item_id_custom_order_add'])
{
	$item = $_SESSION['item_id_custom_order_add'];
	
	
	
}else{
	$item = uniqid(true);
	$item = substr($item,-6);
	$_SESSION['item_id_custom_order_add'] = $item;
}



//$item = '00125BKPDDSET10';

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
			<h2>Add Item To Order: <?php echo $order_detail["OrderID"]?></h2>
			
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
	$sql = "select Description,MinQTY,MaxQTY,UserID,Price , ID,item_type , item_price_type , price_multi , require_file_upload , item_title from Items where FormID='$item' ";
	list($itemdesc,$min,$max,$u,$price,$item_id,$item_type,$item_price_type,$price_multi , $require_file_upload,$item_title)=@mysql_fetch_row(mysql_query($sql));
	$qbg='';
  	
	?> 
  
            <form method="post">
             <input type="hidden" name="type" value="item" />
             <input type="hidden" name="formid" value="<?php echo $item;?>" />
      
                  <?php
					if(isset($item_id) and $item_id!="")
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
                        
                         <?php echo $item_title;?>
                        
                                         
                        
                          <?php
					if(!isset($item_id))
					{
					?>
                    
                    <input class="input-text" value="<?php if(isset($_SESSION['desc_custom_add'][$item])){ echo $_SESSION['desc_custom_add'][$item]; }?>" name="desc_custom"  type="text" size="35" maxlength="50">
                    
                    <?php
					}
					?> 
                        
                        
					
                    </p>
                    
                    
                    
                    
                    <p class="form-row form-row-wide">
						<label for="username">Item Description:</label>
                        
                        <?php echo $itemdesc;?>
                        
                                         
                        
                          <?php
					if(!isset($item_id))
					{
					?>
                    
                    <textarea  name="description_custom" cols="87" rows="5"><?php if(isset($_SESSION['description_custom_add'][$item])){ echo $_SESSION['description_custom_add'][$item]; }?></textarea>
                    
                    <?php
					}
					?> 
					
                    </p>
                    
                    
                    
                    
					 <p class="form-row form-row-wide">
						<label for="username">Quantity:</label>
                         
                          <input name="qty"  onkeyup="calculate(this);" id="qty" type="text" size="5" maxlength="15" value="<?php if(isset($_SESSION['qty_add'][$item])){ echo $_SESSION['qty_add'][$item]; }?>">  
					
                    </p>
                    
                     
          <?php
					if(isset($item_id))
					{
					// $_SESSION['price_custom'][$item];
					
					//print_r("<pre>");
					//print_r($_SESSION);
						
					?>
                    
                     <p class="form-row form-row-wide">
						<label for="username">Unit Price:</label>
                         
                          <?php
			if(isset($item_id))
					{
						
						
						/*	$decimal_place =  how_many_decimal_place_display($price);
	      				$price = number_format($price,$decimal_place );*/
			?>
             
           
            
              <input  id="price_custom" type="hidden" size="5" maxlength="15" value="<?php if(isset($price)){ echo $price; }?>">  
              
            <?php
					}
			?>
            
            
            $<?php echo how_many_decimal_place_display_new($price); ?> 
					
                    </p>
                    
                    
                    
              <?php
					}
				  ?>        
                    
               
                <?php
					if(!isset($item_id))
					{
					// $_SESSION['price_custom'][$item];
					
					//print_r("<pre>");
					//print_r($_SESSION);
						
					?>
					 <p class="form-row form-row-wide">
						<label for="username">Unit Price</label>
                        $<input onkeyup="calculate(this);" id="price_custom"  name="price_custom" type="text" size="5" maxlength="15" value="<?php if(isset($_SESSION['price_custom_add'][$item])){ echo $_SESSION['price_custom_add'][$item]; }?>">    
					
                    </p>     	
				
     	
         <?php
					}
				  ?>
     
     
                   <?php 
				  $temp_total_price = '';
				
				  if(isset($_SESSION['qty'][$item]))
				  {  
				  
				  	 $temp_q_bc =  $_SESSION['qty'][$item]; 
					
					
					
					if(isset($_SESSION['price_custom_add'][$item]))
					{ 
					
						 $price_temp_bc = $_SESSION['price_custom_add'][$item];
					}else{
						$price_temp_bc = $price;
					}
					
					if($temp_q_bc!="")
					{ 
						
						 $temp_total_price = $temp_q_bc * $price_temp_bc;
					}
				  
				  }
				  
				  
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
			if(isset($item_id))
					{
					
					$temp_total_price  =  number_format($temp_total_price,2); 
				   $temp_total_price = str_replace(",","",$temp_total_price); 	
					
					//$temp_total_price  =  how_many_decimal_place_display_new($temp_total_price,1);  	
			?>
             
           <input readonly="readonly"   id="total_price_custom" name="" type="text" size="5" maxlength="15" value="<?php if($temp_total_price!=""){echo  $temp_total_price ;}?>">  
           
           
            <?php
					}else{
						
					// $temp_total_price  =  how_many_decimal_place_display_new($temp_total_price,1);  
					
						$temp_total_price  =  number_format($temp_total_price,2); 
				   $temp_total_price = str_replace(",","",$temp_total_price); 	
	
						
			?>
                        
                          $<input onkeyup="calculate(this);"  id="total_price_custom" name="" type="text" size="5" maxlength="15" value="<?php if($temp_total_price!=""){echo $temp_total_price;}?>">  
                        
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
					if(isset($_SESSION['file_add'][$item]))
					{
						
						foreach($_SESSION['file_add'][$item] as $key_add=>$file_name)
						{
					?>
                    
                    
                    
					 <p class="form-row form-row-wide">
						 
                         <a href="<?php echo $upload_path.$file_name; ?>" target="_blank"> <?php echo wordwrap($file_name, 70, "<br />\n",true);  ?> </a>  (<?php echo $_SESSION['file_resolution_add'][$item][$key_add];?>)
                         
                          <a href="add_custom_order.php?file=yes&formid=<?php echo $item;?>&offset=<?php echo $key_add;?>" ><img src="../images/delete.png" width="16" height="16" align="absmiddle" /></a>
					
                    </p>
                    
      
      
                      <?php
					
						}
					}
					?>
                    
                      <form method="post">
                    	<input type="hidden" name="type" value="link" />
                        <input type="hidden" name="formid" value="<?php echo $item;?>" />
                        
                       
					 <p class="form-row form-row-wide">
						
                        <?php if(isset($error_link_resolution)){?>
            
            <span style="color:red;"><?php echo $error_link_resolution;?></span>
            
            <?php } ?>
                        
                        
                        <label for="username">Specify File Link(s) For This Item:</label>
                         <input value="<?php if(isset($_POST["link"])){ echo $_POST["link"]; }?>" name="link" type="text" size="30" maxlength="100" /> <br/>
                           
					
                    </p> 
                    
             <p class="form-row form-row-small">
              	 <input type="submit" name="save_link" value="Save Link" />
              
              </p>
                        
                      </form>       
                    
                    
					 <p class="form-row form-row-wide">
						
                        
                         <?php
					if(isset($_SESSION['link_add'][$item]))
					{
						
						foreach($_SESSION['link_add'][$item] as $key_add=>$link_value)
						{
					?>
                    
                    <a href="<?php echo $link_value;?>" target="_blank"> <?php echo wordwrap($link_value, 70, "<br />\n",true);  ?> </a>
                    
                   
                    
                     <a href="add_custom_order.php?link=yes&formid=<?php echo $item;?>&offset=<?php echo $key_add;?>" ><img src="../images/delete.png" width="16" height="16" align="absmiddle" /></a>
                    
                    <br/>
                    
                    <?php
					
						}
					}
					?>
                       
					
                    </p>
                    
                    
      <form method="post">
        <input type="hidden" name="type" value="desc" />
        <input type="hidden" name="formid" value="<?php echo $item;?>" />
        	
              <p align="center" class="form-row form-row-wide" style="background-color:#00a2db">
				<label for="username"><strong>Order Instructions</strong></label>
             </p>
            
             <p class="form-row form-row-wide">
                <label for="username">Additional Notes / Order Instructions:</label>
                
                 <textarea name="description" cols="97" rows="5"><?php if(isset($_SESSION['description_add'][$item])){ echo $_SESSION['description_add'][$item]; }?></textarea>
                <br/>
                
            
            </p>
                    
        <p class="form-row form-row-small">
              	  <input type="submit" name="save_notes" value="Save Order Instructions" />
              
              </p>
        
        </form>
        
        
        
					 <p class="form-row form-row-small">
					
                      <!--<input type="button" name="Submit5" value="Continue to Cart" onclick="self.location.href='shopping-cart.php'">--> 
                       
                       <form method="post">
    	<input type="submit" name="save_final_item_order" value="Save Order & Return To Tracking Page"  >
    </form>
                       
                       
					
                    </p>
                    
         
     
    
			</div>
		</div>
	</div>
  <!-- Checkout Cart / End -->
</div>
<!-- Container / End -->
<div class="margin-top-50"></div>

 <script type="text/javascript">

function custom_item(id,set)
{
	//alert(id);
	self.location.href='custom/customize.php?id='+id+'&set='+set;
	//self.location.href='custom/customize.php?id='+id;
	return false;
}

var cursrch='';
check_item=function(sval){
	var sval=document.getElementById('itemsrch').value;
	var orderid= '<?php echo $_GET['orderid'];?>';
	var dp=document.getElementById('itempicker');
	if(sval.length<3) { cursrch=''; dp.innerHTML=''; return; }
	if(sval == cursrch) { return; }
	cursrch = sval;
	var x=getXMLObj();
	x.open('get','add_custom_order.php?orderid='+orderid+'&a=itemgds&ss='+sval, true);
	x.onreadystatechange=function(){if(x.readyState=='4'){
		document.getElementById('itempicker').innerHTML=x.responseText;
	}};
	x.send(null);
}
getitem=function(str){
	var orderid= '<?php echo $_GET['orderid'];?>';
	if(str=='')return;
	var x=getXMLObj();
	x.open('get','add_custom_order.php?orderid='+orderid+'&a=itemgd&ss='+str, true);
	x.onreadystatechange=function(){if(x.readyState==4){
		
		//alert(x.responseText);
		strarr = x.responseText.split("**");
		
		if(strarr[0]==2)
		{
			var r = alert("You are trying to add the same item to the order.Please go on the tracking page and update item.");
			return false;
				
		}
		
		
		if(strarr[0]==1)
		{
			//window.location.reload();	
			self.location.href='add_custom_order.php?orderid='+strarr[1];
		}
		
		
		
	}};
	x.send(null);
}


/*function close_popup()
{	
	
	//window.location.href='track.php#<?php echo $orderid;?>';
	window.location = 'track.php#<?php echo $orderid;?>';
	self.close();	
	
}*/


</script>



<?php include("footer.php");?>


 
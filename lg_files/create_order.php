<?php
//include_once("include/sendgrid_shop.php");
 

$order_date = date("m/d/Y");  

$order_place_by = $con["Name"] ;

//$CID = 56 ;

$CID =  $_SESSION['CID'] ;

$order_total = $_SESSION['total_price'] ;

if(isset($_SESSION['is_sale_price_zero']) and $_SESSION['is_sale_price_zero']==1)
{
	$order_total =  0 ;
}


$ShipToName  = $con["ShipToName"] ;
 
 if( isset($_SESSION['size_item']) and !empty($_SESSION['size_item']) )
 {
 	$_SESSION['size_item_temp'] = $_SESSION['size_item'] ; 
 
 }
	
//$order_id = date("md-His") . "-$user_id";

//$order_id = date("mdY-His") .generateRandomString_fc(6) ;

$shipping_charge = '';
$delivery_charge = '';
$Delivery_Method_lable = '';
$notes_tmp = '';
$is_gift_order_tmp = '';
	
	 
	 

	
	$dealer_code = '';
	$dealer_code_discount_amount = '';
	$dealer_code_email_txt = '';
	if(isset($_SESSION['get_dealer_code']))
	{
		$dealer_code =  $_SESSION['get_dealer_code'];
	}
	
	if(isset($_SESSION['set_dealer_discount']))
	{
		$dealer_code_discount_amount =  $_SESSION['set_dealer_discount'];
		
	}
	
	
	$sql_promo_code = "";
	if(isset($_SESSION['set_promo_code_discount']) and $_SESSION['set_promo_code_discount']>0)
	{
		$set_promo_code_discount = $_SESSION['set_promo_code_discount'];
		$promo_code_str_tmp = $_SESSION['promo_code_str'] ;
		$sql_promo_code = " , promo_code = '$promo_code_str_tmp' ";
	}
	
	$order_total = str_replace(',', '', $order_total);
	$for_email_body_order_total = number_format($order_total,2);
	
	$sale_discount_total_new = 0 ;
	if(isset($_SESSION['sale_discount_total']) and $_SESSION['sale_discount_total'] > 0)
	{
	 $sale_discount_total_new = $_SESSION['sale_discount_total']  ;
	}
	
	
	$custom_desc = '';
	
	$order_place_by = mysql_escape_string($order_place_by);
	$ShipToName = mysql_escape_string($ShipToName);
	$custom_desc = mysql_escape_string($custom_desc);
	$notes_tmp = mysql_escape_string($notes_tmp);
	
	if(isset($_SESSION['is_sale_price_zero']) and 	$_SESSION['is_sale_price_zero'] ==1)
	{
		$order_total = 0 ;
	}
	
	if(!$uid)$uid=$_SESSION['AID'];
	
	$stnm = '';
	if($con['ShipToName']) $stnm = $con['ShipToName'];
	
	 $total_sale_tax	 = $amount_to_collect ;
	
 	 $sql = <<<EOM
	
	insert Orders set 
	
	 OrderID='$order_id'
	 ,UserID='$uid'
	 
	 , total_sale_tax = '$total_sale_tax'
	 
	,sale_discount_total = '$sale_discount_total_new' 
	 	
	,CID = '$CID'	
	,OrderDate=now()	
	 
	,Email='$con[Email]'
	,Name='$con[Name]'
	,Company='$con[Company]'
	,order_place_by_name = '$order_place_by'
	,ShipToName='$stnm'
	,Phone='$con[Phone]'
	,Address1='$con[Address1]'
	,Address2='$con[Address2]'
	,City='$con[City]'
	,State='$con[State]'
	,Zip='$con[Zip]'
	,Notes='$notes'
	
	,requestor = '$con[requestor]'
	,requestor_email = '$con[requestor_email]'
	,cost_center = '$con[cost_center]'
	,tax_type = '$con[tax_type]'
	
	,item_due_date =  '$Requested_Delivery_Date'
	 ,custom_desc =  '$custom_desc'
 
	,order_total = '$order_total'
	,is_modern_site_order = '1'
	 
	 

EOM;



mysql_query($sql) or die ( mysql_error() ) ;

  $newid=mysql_insert_id();  

$shipping_address_tmp = $con[Address1];



/* print_r("<pre>");
print_r($_SESSION);*/

//$_SESSION['cart'][$_POST[Folder_Size]] = $_POST[Quantity] ;

/*print_r("<pre>");
print_r($_SESSION);
die;
*/
$total_sale_tax = 0  ;

if(isset($_SESSION['Order']))
	{
	 
	 
		 
		foreach($_SESSION['Order'] as $item_id=>$qty_arr)
		{
	
	
		
		/*pr_n($qty_arr);
		die;*/
	
	
			foreach($qty_arr as $set_key=>$v)
			{
					
			
			 
			 $sql_lookup_item_check = " select * from Items where ID = '$item_id'   ";
			$rs_lookup = mysql_query($sql_lookup_item_check);
			
			$items_detail = mysql_fetch_assoc($rs_lookup) ;
			
			$sql_gift_card_insert = '';
			
			
			$cappyhour_logo = "";
			$cappyhour_tonal = "";
			$sql_copyhour_insert = "";
			 
			
			
			
			$item_id =  $items_detail['ID'] ;
			
			$itm[ID] = $item_id ;
			
			$s = ''; 
			
			$canvas_download_url_db= ''; 
			
			if($items_detail["item_price_type"]=="multi_quantity_price")
			{ 
				$item_id =  $items_detail['ID'] ;
				
				if(isset($_SESSION['canvas_download_url_new'][$item_id][$set_key]))
				{
					$canvas_download_url_db = $_SESSION['canvas_download_url_new'][$item_id][$set_key] ;
					
				}	
				
				
				
				if(isset($_SESSION['size_item_temp'][$item_id][$set_key]))
				{
					$s = $_SESSION['size_item_temp'][$item_id][$set_key] ;
					
					
					if($s!="")
					{
						 
						$is_apply_sale_price = check_sale_date($item_id);
		
						if($is_apply_sale_price==1)
						{
							//$price_tmp  = get_item_sale_price_by_size_new($s,$item_id);
							$price_tmp  = get_item_sale_price_by_size_new_with_percentage($s,$item_id);
						}else{
							$price_tmp  = get_item_price_by_size_new($s,$item_id);
						}
								
						
						
						$items_detail['Price'] = $price_tmp ;
						
						
						
				if($items_detail['item_price_type']=="multi_quantity_price")
				{
					$range_price_detail = get_range_item_price_based_on_qty_and_size($item_id,$items_detail["CID"],$v,$s);
					if(!empty($range_price_detail))
					{
						$items_detail['Price'] = $range_price_detail['price'];
					}
					
					if(empty($range_price_detail))
					{
						$range_price_detail = get_range_item_price_based_on_qty($item_id,$items_detail["CID"],$v);
							if(!empty($range_price_detail))
							{
								$items_detail['Price'] = $range_price_detail['price'];
								$itm[Price] = $items_detail['Price'] ;
							}
					
					}
					
				}
						
						
						  
					}
					
				}else{
					
					 $is_apply_sale_price = check_sale_date($item_id);
			 
			 
			 
					if($is_apply_sale_price==1)
					{
						//$items_detail['Price'] = get_item_sale_price_by_size_new($s,$item_id);	
						
						$items_detail['Price'] = get_item_sale_price_by_size_new_with_percentage($s,$item_id);
						
					}
					
						// check if item in range price
						if($items_detail['item_price_type']=="multi_quantity_price")
						{
							$range_price_detail = get_range_item_price_based_on_qty($item_id,$items_detail["CID"],$v);
							if(!empty($range_price_detail))
							{
								$items_detail['Price'] = $range_price_detail['price'];
								$itm[Price] = $items_detail['Price'] ;
							}
						}
					
				
				}
				
				
			}else{
				
				
				$is_apply_sale_price = check_sale_date($item_id);
				 
				if($is_apply_sale_price==1)
				{
					//$items_detail['Price'] = get_item_sale_price_by_size_new($s,$item_id);	
					
					$items_detail['Price'] = get_item_sale_price_by_size_new_with_percentage($s,$item_id);
					
					 
				}
				
				
				
					// check if item in range price
					if($items_detail['item_price_type']=="multi_quantity_price")
					{
						$range_price_detail = get_range_item_price_based_on_qty($item_id,$items_detail["CID"],$v);
						if(!empty($range_price_detail))
						{
							$items_detail['Price'] = $range_price_detail['price'];
							$itm[Price] = $items_detail['Price'] ;
							
							
						}
					}
			
			}
			
			
			//pr_n($items_detail);
			//die("test");
			
			$s_color = '';
			
			if($items_detail["item_price_type"]=="multi_quantity_price")
			{ 
				$item_id =  $items_detail['ID'] ;
				if(isset($_SESSION['color_item'][$item_id][$set_key]))
				{
					$s_color = $_SESSION['color_item'][$item_id][$set_key] ;
					
				}
			}
			
			$artwork_logo = '';
			
			 
				$item_id =  $items_detail['ID'] ;
				if(isset($_SESSION['artwork_logo_item'][$item_id][$set_key]))
				{
					$artwork_logo = $_SESSION['artwork_logo_item'][$item_id][$set_key] ;
					
				}
			 
			$artwork_logo = mysql_escape_string($artwork_logo);
			
			
			$custom_name_price = '';
			$custom_number_price = '';
			$custom_name = '';
			$custom_number = '';
			
			
			
			if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
			{
				$custom_name_price = $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
				
				if($_SESSION['CID']==120)
				{
					$total_sale_tax += ( $custom_name_price * 7.25 ) / 100 ;
				}
			}
			
			if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
			{
				$custom_number_price = $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
				
				if($_SESSION['CID']==120)
				{
					$total_sale_tax += ( $custom_number_price * 7.25 ) / 100 ;
				}
				
			}
			
			if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
			{
				$custom_name = $_SESSION['custom_name_tmp'][$item_id][$set_key] ;
				
			}
			
			if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
			{
				$custom_number = $_SESSION['custom_number_tmp'][$item_id][$set_key] ;
				
			}
			
			
			$special_comment = '';
			if(isset($_SESSION['special_comment_tmp'][$item_id][$set_key]))
			{
				$special_comment = $_SESSION['special_comment_tmp'][$item_id][$set_key] ;
				$special_comment = mysql_escape_string($special_comment);
				
			}
			
			 


		 $custom_name = mysql_escape_string($custom_name);
		 $custom_number = mysql_escape_string($custom_number);
			
			
			
			//echo $size_item;
			
			//die('Hello');
			
			$itm[Description] = mysql_escape_string($itm[Description]);
			
			
			//$k = $item_detail['FormID'];
			
			$k = $itm['FormID'];
			
			list($k) = mysql_fetch_row(mysql_query("SELECT FormID  
FROM  `Items` 
WHERE  `ID` =  '$itm[ID]' "));
			
			
			$itm = $items_detail;
			
			
			$sale_tax = $items_detail['sale_tax'];	
			$is_apply_sale_tax = $items_detail['is_apply_sale_tax'];	
		
			$sale_tax_price = 0 ;
			
			$single_item_total = $itm['Price'] * $v ; 
			
			if($sale_tax>0 and $is_apply_sale_tax==1 )
			{
				// $sale_tax_price = 	( $itm['Price'] * $sale_tax ) / 100 ;
				
				$sale_tax_price = 	( $single_item_total * $sale_tax ) / 100 ;
				
				$total_sale_tax +=  $sale_tax_price ;
				
				//$itm['Price'] = $itm['Price'] + $sale_tax_price ;
				
				//$itm['Price'] = number_format($itm['Price'],2);
			}
			
			
			$waist_size = '';
			$length_inches = '';
			
			if($item_id==88940)
			{
				if(isset($_SESSION['waist_size'][$item_id][$set_key]))
				{
					$waist_size = $_SESSION['waist_size'][$item_id][$set_key] ;
					 
				}
				
				if(isset($_SESSION['length_inches'][$item_id][$set_key]))
				{
					$length_inches = $_SESSION['length_inches'][$item_id][$set_key] ;
					 
				}
			
			}
			
			
			
			
			
			
			   $sql = "
					  insert OrderItems set
						 OrderRecordID='$newid'	
						 
						 ,waist_size='$waist_size'
						 ,length_inches='$length_inches'		 
						,ItemID='$itm[ID]'
						,FormID='$k'
						,FormDescription='$itm[Description]'
						,Quantity='$v'
						,Price='$itm[Price]'
						,size_item = '$s'
						,color_item = '$s_color'
						,artwork_logo = '$artwork_logo'
						
						,custom_name_price = '$custom_name_price'
						,custom_number_price = '$custom_number_price'
						,custom_name = '$custom_name'
						,custom_number = '$custom_number'
						,special_comment = '$special_comment'
						
						, canvas_download_url = '$canvas_download_url_db'
						
						$sql_gift_card_insert
						
						$sql_copyhour_insert
				";
				
				 
				
				
				
			mysql_query($sql) or die ( mysql_error() ) ;
			 
			
			
		}
		
		
		
	
	}
	
	}
	

$OrderTOTAL = $order_total ;

if($OrderTOTAL > 0 ){ // do budget stuff...
		
		// $is_contain_custom_item_type=1 that means contain item type = custom then it will not update budget balance for all items.
		
			if(isset($_SESSION['custom_tab_order']) and $_SESSION['custom_tab_order']==1)
			{
				// if order is custom tab order that means we not need to charge 
			}else{
		
			list($budget,$budgetavail)=@mysql_fetch_row(mysql_query("select Budget,BudgetBalance from Users where ID='$uid' and Budget is not null"));
			if($budget){
				
				mysql_query("update Orders set previous_budget_amount='$budgetavail'  where ID='$newid'");	
				
				if($budgetavail >= $OrderTOTAL){ // just subtract order total from budget balance
				
					
					
					mysql_query("update Users set BudgetBalance=BudgetBalance - $OrderTOTAL where ID='$uid'");
					
					// Start script for store log for all transaction for budget balance
					$total_affected_row_bu = mysql_affected_rows();
					if($total_affected_row_bu ==1 )
					{	
						
					
					
					
						
						list($budget_bu_new,$budgetavail_bu_new)=@mysql_fetch_row(mysql_query("select Budget,BudgetBalance from Users where ID='$uid' and Budget is not null"));
						
						
						$order_dtm = date("Y-m-d H:i:s");
						$created_dtm = date("Y-m-d H:i:s");
						
						$sql_insert_bu_hi = "
						INSERT INTO budget_history
						SET
							order_dtm = '$order_dtm'
							,order_id = '$oid'
							,or_id = '$newid'
							,user_id = '$uid'
							,cid = '$cid'
							,amount = '$OrderTOTAL'
							,order_time_BudgetBalance = '$budgetavail'
							,after_order_BudgetBalance = '$budgetavail_bu_new'
							
							,reason = 'new order created'
							,reason_label = 'new_order_created'
							,created_dtm = '$created_dtm' " ;
							
							mysql_query($sql_insert_bu_hi);
					
						
						
						
						
						
						$id_all_type_bu = $newid;
						$log_type_bu = 'create_order';
						$action_title_log_bu ='Create Order';
						$created_dtm_log_bu = date('Y-m-d H:i:s');
						
						  $sql_log_bu ="INSERT INTO budget_log_all_trans SET
						
										cid ='$CID',
										user_id ='$uid',
										action_title ='$action_title_log_bu',
										log_type ='$log_type_bu',
										id ='$id_all_type_bu',
										created_dtm ='$created_dtm_log_bu'
												";
						mysql_query($sql_log_bu)or die(mysql_error());	
						
						$log_id_bu = mysql_insert_id();
						
						$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
											log_id ='$log_id_bu',
											field_name ='Budget',
											old_value ='$budget',
											new_value ='$budget_bu_new',
											created_dtm ='$created_dtm_log_bu'
											";
							mysql_query($sql_log_history_bu)or die(mysql_error());
							
							$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
											log_id ='$log_id_bu',
											field_name ='BudgetBalance',
											old_value ='$budgetavail',
											new_value ='$budgetavail_bu_new',
											created_dtm ='$created_dtm_log_bu',
											order_total = '$OrderTOTAL'
											";
							mysql_query($sql_log_history_bu)or die(mysql_error());
						
						
					}
					
					// End script for store log for all transaction for budget balance
					
					
					
					
					
				} else { // put order into "requires approval" status.
					// if it gets approved, then set order status to 'new'.
					// if it gets denied, then it needs a full cancel
					mysql_query("update Users set BudgetBalance=BudgetBalance - $OrderTOTAL where ID='$uid'");
					
					
					// Start script for store log for all transaction for budget balance
					$total_affected_row_bu = mysql_affected_rows();
					if($total_affected_row_bu ==1 )
					{		
						list($budget_bu_new,$budgetavail_bu_new)=@mysql_fetch_row(mysql_query("select Budget,BudgetBalance from Users where ID='$uid' and Budget is not null"));
						
						
						$order_dtm = date("Y-m-d H:i:s");
						$created_dtm = date("Y-m-d H:i:s");
						
						$sql_insert_bu_hi = "
						INSERT INTO budget_history
						SET
							order_dtm = '$order_dtm'
							,order_id = '$oid'
							,or_id = '$newid'
							,user_id = '$uid'
							,cid = '$cid'
							,amount = '$OrderTOTAL'
							,order_time_BudgetBalance = '$budgetavail'
							,after_order_BudgetBalance = '$budgetavail_bu_new'
							
							,reason = 'new order created'
							,reason_label = 'new_order_created'
							,created_dtm = '$created_dtm' " ;
							
							mysql_query($sql_insert_bu_hi);
						
						
						
						
						
						$id_all_type_bu = $newid;
						$log_type_bu = 'create_order';
						$action_title_log_bu ='Create Order';
						$created_dtm_log_bu = date('Y-m-d H:i:s');
						
						  $sql_log_bu ="INSERT INTO budget_log_all_trans SET
						
										cid ='$CID',
										user_id ='$uid',
										action_title ='$action_title_log_bu',
										log_type ='$log_type_bu',
										id ='$id_all_type_bu',
										created_dtm ='$created_dtm_log_bu'
												";
						mysql_query($sql_log_bu)or die(mysql_error());	
						
						$log_id_bu = mysql_insert_id();
						
						$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
											log_id ='$log_id_bu',
											field_name ='Budget',
											old_value ='$budget',
											new_value ='$budget_bu_new',
											created_dtm ='$created_dtm_log_bu'
											";
							mysql_query($sql_log_history_bu)or die(mysql_error());
							
							$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
											log_id ='$log_id_bu',
											field_name ='BudgetBalance',
											old_value ='$budgetavail',
											new_value ='$budgetavail_bu_new',
											created_dtm ='$created_dtm_log_bu',
											order_total = '$OrderTOTAL'
											";
							mysql_query($sql_log_history_bu)or die(mysql_error());
						
						
					}
					
					// End script for store log for all transaction for budget balance
					
					
					
					mysql_query("update Orders set Status='approvalreq' where ID='$newid'");
					$enc = endecrypt($encpass,"$newid:$newid",'');
					$enc = urlencode($enc);
					list($u)=@mysql_fetch_row(mysql_query("select URL from Clients where ID='$cid'"));
					if($u && !strstr($u, "://")) $u = "http://$u";
					$u .= "/budgetapp.php?p=$enc";
					$user=@mysql_fetch_assoc(mysql_query("select * from Users where ID='$uid'"));
					if(strstr($user['BudgetApprover'],"@")){
						$subject = "Over Budget Order - Approval Required";
						$url = "/admin/orders.php?" . urlencode("a=edit&id=$newid");
						$pp = urlencode(endecrypt($encpass, "$newid:$newid", ''));
						$appurl = "/admin/orders.php?a=approve&pp=$pp";
						$denurl = "/admin/orders.php?a=deny&pp=$pp";
						$message = <<<EOM
<div style="width:500px;">
$user[Name], who has a budget of \$$user[Budget] per $user[BudgetPeriod] and
a current available balance of \$$user[BudgetBalance], has placed an order for \$$OrderTOTAL.
Please review and approve or deny the order request.
<br /><br />
<b>ORDER ITEMS</b>
<table>
<tr><th>Item Number</th><th>Quantity</th></tr>
EOM;
						foreach($items as $k=>$v){
							$message .= <<<EOM
<tr><td>$k</td><td align="right">$v</td></tr>
EOM;
						}
						
					$server_url = "http://rwaf.co";
					if($CID==44)
					{
						$server_url = "http://lordfulfillment.com";	
					}
					
					if($CID==49)
					{
						$server_url = "http://sterisfulfillment.com";	
					}
					
					
						

						$message .= <<<EOM
</table>
<br />
Please select the appropriate link to approve or deny the request:<br /><br />
<a href="$server_url$appurl">APPROVE</a>
&nbsp; &nbsp; | &nbsp; &nbsp;
<a href="$server_url$denurl">DENY</a>
<br /><br />
Thank you!
</div>
EOM;

 //echo $message; die;
						//good_mail($fromaddr, $user['BudgetApprover'],$subject,$message,"From: $fromhdr\nMime-Version: 1.0\nContent-Type: text/html; charset=iso-8859-1");
						
						good_mail($fromaddr, "kcchoudhary2019@gmail.com",$subject,$message,"From: $fromhdr\nMime-Version: 1.0\nContent-Type: text/html; charset=iso-8859-1");
						
					}
				}
			}
			
			
			}
			
		}


	
$cart_type ='' ;
	
	 

$order_total = number_format($order_total,2);

$total_price = str_replace(',', '', $total_price);
 


// send confirmation email to user
$email = $con[Emails] ;

 
$odt = date("m/d/Y g:ia");

//$last_four_digit_new = substr($_POST["CardNumber"],-4);

/*Payment Method: Credit Card (last 4 digits: 2424) <br/><br/>
*/

$str_payment_method = "";

 

$msg = "

Thank you for your order. <br/><br/>

The details of your order are as follows: <br/><br/>

<strong>Order Number:</strong> $order_id  <br/>
<strong>Order Date:</strong> $odt <br/>
$str_payment_method ";

$cart_total = '';

$total_custom_price_item_tmp = 0 ;

 if(isset($_SESSION['Order']) and !empty($_SESSION['Order']))
	{
				
	foreach($_SESSION['Order'] as $item_id_sess=>$qty_arr)
	{
	
	/*
		echo $item_id_sess ;
		pr_n($qty_sess);
		die;
	*/
	
	foreach($qty_arr as $set_key=>$qty_sess)
	{
	
		$item_detail = get_item_detail_by_item_id($item_id_sess);
		
		if(isset($_SESSION['gift_card_is_gift_card_item'][$item_detail['ID']][$set_key]))
			{
			
			$item_detail['Price'] = $_SESSION['gift_card_gift_price'][$item_detail['ID']][$set_key] ;
		}		
				//pr_n($item_details);
				
				
		$item_image  = ''; 
		if($item_detail['ImageFile']!="")
		{
			$item_image = "pdf/$CID/".$item_detail['ImageFile'];
		}
		
		$item_id = $item_detail['ID'] ;
		//echo $_SESSION['Order'][$item_id]['size'];
		
		
		
		$special_comment_text = "";
		if(isset($_SESSION['special_comment_tmp'][$item_id][$set_key]))
		{
			$tmp_special_comment = $_SESSION['special_comment_tmp'][$item_id][$set_key] ;
			if($tmp_special_comment!="")
			{
				$special_comment_text = " <strong>Special Comment:</strong> $tmp_special_comment <br/> ";
			}
		}
		
		$size_lable = "";
		if(isset($_SESSION['size_item'][$item_id][$set_key]))
		{
			$size = $_SESSION['size_item'][$item_id][$set_key] ;
			$size = trim($size);
			if($size!="" and $size!="Please Select")
			{
				 
				$is_apply_sale_price = check_sale_date($item_id);

				if($is_apply_sale_price==1)
				{
					//$item_detail['Price']  = get_item_sale_price_by_size_new($size,$item_id);
					 $item_detail['Price'] = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
					
					
				}else{
					$item_detail['Price']  = get_item_price_by_size_new($size,$item_id);
				}
				
						
				if($item_detail['item_price_type']=="multi_quantity_price")
				{
					$range_price_detail = get_range_item_price_based_on_qty_and_size($item_id,$item_detail["CID"],$qty_sess,$size);
					if(!empty($range_price_detail))
					{
						$item_detail['Price'] = $range_price_detail['price'];
					}
					
					if(empty($range_price_detail))
					{
						$range_price_detail = get_range_item_price_based_on_qty($item_id,$item_detail["CID"],$v);
							if(!empty($range_price_detail))
							{
								$item_detail['Price'] = $range_price_detail['price'];
								 
							}
					
					}
					
					
					
				}
					
				
				
				$size_lable = " <strong>Size:</strong> $size <br/> ";
				  
			}	
			
		}else{
			
			$is_apply_sale_price = check_sale_date($item_id);
			 
			if($is_apply_sale_price==1)
			{
				//$item_detail['Price']   = get_item_sale_price_by_size_new($size,$item_id);	
				
				$item_detail['Price'] = get_item_sale_price_by_size_new_with_percentage($size,$item_id);
				 
			}
			
			if($item_detail['item_price_type']=="multi_quantity_price")
				{
					$range_price_detail = get_range_item_price_based_on_qty($item_id,$item_detail["CID"],$qty_sess);
					if(!empty($range_price_detail))
					{
						$item_detail['Price'] = $range_price_detail['price'];
					}
				}
			
			
		}
		
		if(isset($_SESSION['custom_name_price_tmp'][$item_id][$set_key]))
		{
			$total_custom_price_item_tmp += $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
		}
		
		if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
		{
			$total_custom_price_item_tmp += $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
		}
		
		
		
		
		$artwork_logo_lable = '';
		if(isset($_SESSION['artwork_logo_item'][$item_id][$set_key]))
		{
			$artwork_logo_item = $_SESSION['artwork_logo_item'][$item_id][$set_key] ;
			$artwork_logo_lable = " <strong>Artwork Logo:</strong> $artwork_logo_item <br/> ";
		}
		
		$custom_name_lable = '';
		if(isset($_SESSION['custom_name_tmp'][$item_id][$set_key]))
		{
			 $custom_name_price = $_SESSION['custom_name_price_tmp'][$item_id][$set_key] ;
			 $custom_name_tmp = $_SESSION['custom_name_tmp'][$item_id][$set_key] ;
			$custom_name_lable = " <strong>Custom Name ($$custom_name_price):</strong> $custom_name_tmp <br/> ";
		}
		
		$custom_number_lable = '';
		if(isset($_SESSION['custom_number_tmp'][$item_id][$set_key]))
		{
			 $custom_number_price_tmp = $_SESSION['custom_number_price_tmp'][$item_id][$set_key] ;
			 $custom_number_tmp = $_SESSION['custom_number_tmp'][$item_id][$set_key] ;
			$custom_number_lable = " <strong>Custom Number ($$custom_number_price_tmp):</strong> $custom_number_tmp <br/> ";
		}
		
		$price = $item_detail['Price'];
		 //die;
		
		
		$cart_item_total = $price * $qty_sess ;
		$single_item_total = $price * $qty_sess ;
		
		$sale_tax = $item_detail['sale_tax'];	
		$is_apply_sale_tax = $item_detail['is_apply_sale_tax'];	
		
		$sale_tax_price = 0 ;
		if($sale_tax>0 and $is_apply_sale_tax==1)
		{
			//$sale_tax_price = 	( $cart_item_total * $sale_tax ) / 100 ;
			
			$sale_tax_price = 	( $single_item_total * $sale_tax ) / 100 ;
			
			//$cart_item_total = $cart_item_total + $sale_tax_price ;
			
			//$price = $price + $sale_tax_price ;
		}
		//echo $sale_tax_price;
		
		
		
		
		$cart_total += 	$cart_item_total ;	
		
		$price = number_format($price,2);
		$cart_item_total = number_format($cart_item_total,2);
		
		
 $msg .= "
<strong>Item ID:</strong> $item_detail[FormID]		<br/>
<strong>Item Title:</strong> $item_detail[item_title] <br/>
<strong>Quantity:</strong> $qty_sess <br/>
$size_lable
$artwork_logo_lable
$custom_name_lable
$custom_number_lable
$special_comment_text
<strong>Price:</strong> $$price <br/><br/>";



}

}

}


	
$cart_total = number_format($cart_total,2);
	
	
	
	
$sales_tax	= 0 ;
	
 

$order_total = "";
$order_total = $cart_total + $delivery_charge + $sales_tax + $total_custom_price_item_tmp;
$order_total = number_format($order_total,2);	
$sales_tax = number_format($sales_tax,2);	

$delivery_charge = number_format($delivery_charge,2);

 
if($CID==118 and $delivery_method=="free_gift_card")
{
	$delivery_charge = 0 ;
	$Delivery_Method_lable = "Free Delivery" ;
}
 

if($delivery_charge>0)
{
	
}

if(isset($_SESSION['total_price']))
{
	$order_total = $_SESSION['total_price'] ; 
}

if(isset($_SESSION['is_sale_price_zero']) and 	$_SESSION['is_sale_price_zero'] ==1)
{
	$order_total = 0 ;
}

$txt_sale_tax = '';
$total_sale_tax_db = '';
if($total_sale_tax>0)
{
	$total_sale_tax_db = $total_sale_tax ; 
	$total_sale_tax = number_format($total_sale_tax,2);
	$txt_sale_tax = "<strong>Sale Tax:</strong> $$total_sale_tax <br/><br/>";	
	
}
	


$shipping_handling_lable = "";

$dealer_code_txt = '';
 

$promo_code_dis_msg = "";
$sql_promo_code = "";
 
// <strong>Order Total:</strong> $$for_email_body_order_total <br/><br/>
 

$shipping_delivery_method_str = "";
 

$msg .= "
$shipping_delivery_method_str

$txt_sale_tax
$dealer_code_txt

$promo_code_dis_msg



<strong>CUSTOMER INFORMATION</strong> <br/><br/>

<strong>Ordered By:</strong> $order_place_by <br/>
<strong>Company:</strong> $con[Company] <br/>
<strong>ShipTo Name (Attn):</strong>	$con[Name]  <br/>
<strong>Address:</strong> $con[Address1] <br/>
$con[City], $con[State] $con[Zip] <br/>
<strong>Country:</strong>	United States <br/>
<strong>Email:</strong> $con[Email] <br/>
<strong>Phone:</strong> $con[Phone]	 <br/><br/>


";

  
   


$sql_get_shop_detail = " SELECT * FROM  `Clients` where ID = '$CID'   ";
$client_detail = mysql_fetch_assoc(mysql_query($sql_get_shop_detail));

$subject = $client_detail['Name']." Shop Order Confirmation";

$admin_email = $con[Email];  
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");



$admin_email = "jkrugger@dmsys.co";
$admin_email = "kcchoudhary2019@gmail.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");




good_mail("jkrugger@dmsys.co",$email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");


$admin_email = "info@leadergraphic.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");

$admin_email = "jkrugger@dmsys.co";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");


$admin_email = "pat@leadergraphic.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");

/*$admin_email = "rickr@leadergraphic.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");
*/


 
$notification_email = $client_detail['notification_email'] ;
if($notification_email!="")
{
	$notification_email_arr = explode(",",$notification_email);
	foreach($notification_email_arr as $admin_email)
	{
		good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");
	}
}


?>
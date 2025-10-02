<?php
include_once("include/start.php");
if(!$uid) $uid = $_SESSION['AID'];

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';
if($action=="reorder")
{
	unset($_SESSION['Order']);
	unset($_SESSION['item_id_custom_order']);	
	unset($_SESSION['Order_type']);
	unset($_SESSION['qty']);
	unset($_SESSION['desc_custom']);
	unset($_SESSION['price_custom']);
	unset($_SESSION['custom_tab_order']);
	unset($_SESSION['file']);
	unset($_SESSION['file_resolution']);
	unset($_SESSION['link']);
	unset($_SESSION['link_resolution']);
	unset($_SESSION['description']);
	unset($_SESSION['Order_item_id']);
	
	
	$orderid = $_REQUEST['id'];
	
	 $id = $_REQUEST['id'];
	
	$rs = mysql_query("select * from Orders where CID=$CID and UserID='$uid' $sqlstatus order by OrderDate desc,OrderID desc");
	
	if(mysql_num_rows($rs)>0)
	{
		$order_detail = mysql_fetch_assoc($rs);
	
		$itemrs = mysql_query("select * from OrderItems where OrderRecordID='$id'");
		$numitems = @mysql_num_rows($itemrs);
		if($numitems>0)
		{
			while($item_detail = mysql_fetch_assoc(	$itemrs ))
			{
				
				$formid = $item_detail['FormID'];
				$k = $item_detail['FormID'];
				$v = $item_detail['Quantity'];
				
				$_SESSION['Order'][$formid] = $v;
				
				$_SESSION['Order_item_id'][$formid] = $item_detail["ItemID"];
				
				
				
				/*print_r("<pre>");	
				print_r($item_detail);*/
				$is_custom_order =  $item_detail["is_custom_order_item"];
    
				if($is_custom_order=="Y")
				{ 
				
					$_SESSION['item_id_custom_order'] = $k;
					$_SESSION['Order_type'][$k] = 'Y'; 
					
					
						$sql_note = "select id ,custom_order_desc  from required_note_file     where orderid='$orderid' and formid='$formid'  ";
			$rs_note = mysql_query($sql_note);
			
			
			list($note_id,$custom_order_desc)=@mysql_fetch_row($rs_note);
			
			$_SESSION['description'][$formid]  =  $custom_order_desc ;
					
					
			
					$_SESSION['qty'][$formid] = $v;
					
					$_SESSION['desc_custom'][$formid] = $custom_order_desc;
					
					$_SESSION['price_custom'][$formid] = $item_detail['Price'];
					
					$_SESSION['custom_tab_order'] = 1;
					
					$_SESSION['Order_type'][$formid] = 'Y';
					
					$sql_file_link = "select type , filename , link , resolution , id from required_file_link     where orderid='$orderid' and formid='$formid' and type ='file' ";
				$rs_link = mysql_query($sql_file_link);
				
				while(list($type,$filename,$link , $resolution , $require_file_table_id )=@mysql_fetch_row($rs_link))
				{
					$_SESSION['file'][$formid][] = $filename;
					$_SESSION['file_resolution'][$formid][] = $resolution;
					
				}
				
				
				$sql_file_link = "select type , filename , link , resolution , id from required_file_link     where orderid='$orderid' and formid='$formid' and type ='link' ";
				$rs_link = mysql_query($sql_file_link);
				
				while(list($type,$filename,$link , $resolution , $require_file_table_id )=@mysql_fetch_row($rs_link))
				{
					
					$_SESSION['link'][$formid][] = $link;
					$_SESSION['link_resolution'][$formid][] = $resolution;
					
				}
				
				}
				
				
			}
		}
		header("Location:shopping-cart.php");
		
	}else{
		header("Location:track.php");	
	}
}
?>
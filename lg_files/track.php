<?php
ob_start();
include("setting.php");
include("include/start.php");

if(!$uid) $uid = $_SESSION['AID'];

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';


if($action=='cancel_req' && is_numeric($_REQUEST['id_req']))
{
	$sql_req_update = "update OrderItems_req set status='3' where OrderRecordID='$_REQUEST[id_req]' "; 
	mysql_query($sql_req_update);
	header("Location: track.php");
	exit;
	
}

if($action=='delitem')
{
	$id = $_GET['id'];
	$itemid = $_GET['itemid'];
	$sequnce_id = $_GET['sequnce_id'];
	mysql_query("delete from OrderItems where OrderRecordID='$id' and ID='$sequnce_id' and ItemID = '$itemid'	 ");
	header("location:track.php");
	die;
}

if($action=='cancel' && is_numeric($_REQUEST['id'])){
	// replace budget
	$id = $_GET['id'];
	
	$sql_get_order_year = "SELECT YEAR( OrderDate ) AS order_year , is_custom_order
							FROM  `Orders` 
							WHERE ID = $id
							";
	list($order_year , $is_custom_order )=@mysql_fetch_row(mysql_query($sql_get_order_year));
	$current_year = date("Y");
	
	 
	
	
	//echo $order_year;
	
	//die;
	
	//echo $is_custom_order;
	
	//die;
	
	if($is_custom_order!="Y")
	{
	
	list($budg,$bal)=@mysql_fetch_row(mysql_query("select Budget,BudgetBalance from Users where ID='$uid'"));
	if($budg){
		
		/*$sql_order_items = "select a.Quantity,a.Price  from OrderItems a
			where a.OrderRecordID='$id' and a.Quantity != 0" ;*/
			
		$sql_order_items = "
		
		select a.Quantity,a.Price , a.kit_quantity , a.kit_price , a.kit_id from OrderItems a
			where a.OrderRecordID='$id' and a.Quantity != 0 and a.kit_id = 0
			
		union
		
		select a.Quantity,a.Price , a.kit_quantity , a.kit_price , a.kit_id from OrderItems a
			where a.OrderRecordID='$id' and a.Quantity != 0 and a.kit_id != 0 group by a.kit_id	
			
			
			" ;
			
		
		$irs = mysql_query($sql_order_items);
			
			
		while(list($q,$p,$kit_q,$kit_price,$kit_id)=@mysql_fetch_row($irs))
		{
			if($kit_id!=0)
			{
				
				$val = $kit_q * $kit_price;
				
			}else{
			
				$val = $q * $p;
			
			}
			
			if($order_year==$current_year)
			{
				$new_BudgetBalance  = $bal+$val ;
				
				if($new_BudgetBalance>$budg)
				{
					// if new budgetbalance is greater than , that is wrong calculating
					
					
				}else{
			
			
					if($val) mysql_query("update Users set BudgetBalance=BudgetBalance+$val where ID='$uid'");
			
			
				}
			
			}
				
			// Start script for store log for all transaction for budget balance
			$total_affected_row_bu = mysql_affected_rows();
			if($total_affected_row_bu ==1 )
			{	
			$u = $uid;	
			list($budget_bu_new,$budgetavail_bu_new)=@mysql_fetch_row(mysql_query("select Budget,BudgetBalance from Users where ID='$u' and Budget is not null"));
			
			$id_all_type_bu = $id;
			$log_type_bu = 'cancelled_order';
			$action_title_log_bu ='cancelled Order';  
			$created_dtm_log_bu = date('Y-m-d H:i:s');
			
			if(!isset($log_id_bu))
			{
						
			$sql_log_bu ="INSERT INTO budget_log_all_trans SET
			
				cid ='$CID',
				user_id ='$u',
				action_title ='$action_title_log_bu',
				log_type ='$log_type_bu',
				id ='$id_all_type_bu',
				created_dtm ='$created_dtm_log_bu'
						";
			mysql_query($sql_log_bu)or die(mysql_error());	
			
			$log_id_bu = mysql_insert_id();
			
			}
			
			$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
					log_id ='$log_id_bu',
					field_name ='Budget',
					old_value ='$budg',
					new_value ='$budget_bu_new',
					created_dtm ='$created_dtm_log_bu'
					";
			mysql_query($sql_log_history_bu)or die(mysql_error());
			
			$sql_log_history_bu ="INSERT INTO budget_log_all_trans_detail SET
					log_id ='$log_id_bu',
					field_name ='BudgetBalance',
					old_value ='$bal',
					new_value ='$budgetavail_bu_new',
					created_dtm ='$created_dtm_log_bu' 
					 
					";
			mysql_query($sql_log_history_bu)or die(mysql_error());
			
			
			}
			
			// End script for store log for all transaction for budget balance
			
			
			
			
		}
	}

	}

	// replace inventory
/*	
	$order_items = "select b.ID,a.Quantity,b.InventoryQuantity from OrderItems a join Items b on b.ID=a.ItemID
		where a.OrderRecordID='$id' and a.Quantity != 0 and b.InventoryQuantity is not null";*/
		
			
	$order_items = "
	
	select b.ID,a.Quantity,b.InventoryQuantity , a.kit_quantity , a.kit_id from OrderItems a join Items b on b.ID=a.ItemID
		where a.OrderRecordID='$id' and a.Quantity != 0 and b.InventoryQuantity is not null and a.kit_id = 0
		
		Union
		
			select b.ID,a.Quantity,b.InventoryQuantity , a.kit_quantity , a.kit_id from OrderItems a join Items b on b.ID=a.ItemID
		where a.OrderRecordID='$id' and a.Quantity != 0 and b.InventoryQuantity is not null and a.kit_id != 0 group by a.kit_id
		
		
		";
	
	$irs = mysql_query($order_items);
	while(list($ii,$oq,$iq,$kit_quantity,$kit_id)=@mysql_fetch_row($irs))
	{
		if($kit_id!=0)
		{
			// it will update only kit inventory type 
			list($previous_quantity_item)=@mysql_fetch_row(mysql_query("select InventoryQuantity from Items where ID='$kit_id'"));

		  
			
				$iq_kit  = $kit_quantity;
			    mysql_query("update Items set InventoryQuantity = InventoryQuantity  + $iq_kit where ID='$kit_id'");
				
				list($current_quantity_item)=@mysql_fetch_row(mysql_query("select InventoryQuantity from Items where ID='$kit_id'"));
				
				
				 list($unm_bb)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$_SESSION[AID]'"));


				$created_dtm_log = date("Y-m-d H:i:s");
				
				$sql_insert_item_log = "
					
					insert item_log set
						item_id = '$kit_id'
						,cid = '$CID'
						,user_id = '$_SESSION[AID]'
						,order_id = '$_REQUEST[id]'
						,item_quantity = '$iq_kit'
						,previous_quantity_before_order = '$previous_quantity_item'
						,current_quantity_after_order = '$current_quantity_item'
						,action = 'Inventory Added - Order Cancelled By $unm_bb'
						,action_db = 'order_cancelled_by_user_kit'
						,created_dtm = '$created_dtm_log'
				
				";
							
				mysql_query($sql_insert_item_log);
				
				
		
		}else{
		
				$previous_quantity_item = $iq;
		
		
				$iq += $oq;
				mysql_query("update Items set InventoryQuantity = '$iq' where ID='$ii'");
				
				 list($unm_bb)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$_SESSION[AID]'"));
				 
				$created_dtm_log = date("Y-m-d H:i:s");
				$sql_insert_item_log = "
					
					insert item_log set
						item_id = '$ii'
						,cid = '$CID'
						,user_id = '$_SESSION[AID]'
						,order_id = '$_REQUEST[id]'
						,item_quantity = '$oq'
						,previous_quantity_before_order = '$previous_quantity_item'
						,current_quantity_after_order = '$iq'
						,action = 'Inventory Added - Order Cancelled By $unm_bb'
						,action_db = 'order_cancelled_by_user'
						,created_dtm = '$created_dtm_log'
				
				";
							
				mysql_query($sql_insert_item_log);
				
				
		
		}
		
		
	}

	mysql_query("update Orders set Status='cancelled' where ID='$_REQUEST[id]' and UserID='$uid'");
	header("Location: track.php");
	exit;
}


?>


<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
<!-- Titlebar
================================================== -->
<section class="titlebar">
	<div class="container">
		<div class="sixteen columns">
			<h2>View Open and Recent Orders</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>View Open and Recent Orders</li>
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
  
   
  
 <div class="nine columns centered">

<?php
include_once("track_list.php");

?>
					
		<!--<table class="cart-table responsive-table">

			<tr>
                <th width="25%">Order ID</th>
                <th width="30%">OrderDate</th>
                <th width="5%" align="center">Order Description</th>
                <th width="10%">Ordered By</th>
                <th width="10%">ShipTo</th>
                <th width="10%">Company</th>
                <th width="10%">Status</th>
			</tr>
					
			 <tr>
				<td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
           </tr>
                                            
                                          
        
      

			</table>
     
    -->

			 
				 
		 
	</div>
  
  
   
  
   
  
  <!-- Checkout Cart / End -->
</div>
<!-- Container / End -->
<div class="margin-top-50"></div>


<script type="text/javascript" src="../javascript.js"></script>
<script type="text/javascript" src="../datepicker.js"></script>
<link rel="stylesheet" href="../datepicker.css" />
<script type="text/javascript" src="../js/prototype.js"></script>
<script type="text/javascript" src="../js/scriptaculous.js"></script>


 



<?php

function PhoneField($fname,$val){
	if($val && strlen($val) > 10) $val = substr($val, -10);
	elseif($val && strlen($val)<10) $val = '';
	if($val) {
		$val1 = substr($val,0,3);
		$val2 = substr($val,3,3);
		$val3 = substr($val, -4);
	} else $val1=$val2=$val3='';
	$str = <<<EOM
<input type="text" size="3" name="{$fname}[1]" maxlength="3" value="$val1" /> .
<input type="text" size="3" name="{$fname}[2]" maxlength="3" value="$val2" /> .
<input type="text" size="4" name="{$fname}[3]" maxlength="4" value="$val3" />
EOM;
	return $str;
}


//pr_n($_SESSION);

//session_destroy();
?>
<?php include("footer.php");?>


 <?php 
include("fancybox_javascript_new.php");
?>

<?php

echo <<<EOM
<script>
godel=function(itemid){
	document.getElementById('Quantity'+itemid).value='';
	document.cart.submit();
}
</script>
EOM;
list($login)=@mysql_fetch_row(mysql_query("select Login from Users where ID='$AID'"));
$d30 = date("Y-m-d", time() - (86400*30));
$sstring = $_REQUEST['ss'];
$sqlstr = mysql_real_escape_string(trim($sstring));

$status = $_REQUEST['s'];
if(!$status) $status='all';
$stch[$status] = 'selected="selected"';
if($status=='incomplete') $sqlstatus = "and Status in ('new','inprocess')";
elseif($status=='all') $sqlstatus = '';
else $sqlstatus = "and Status='$status'";

if($sstring){
	$rs = mysql_query("select a.* from Orders a left join OrderItems b on b.OrderRecordID=a.ID
		where a.CID=$CID and a.UserID='$AID'
		and (a.OrderID like '%$sqlstr%' or a.ShipToName like '%$sqlstr%' or b.FormID like '%$sqlstr%' or b.FormDescription like '%$sqlstr%' or a.custom_desc like '%$sqlstr%'   )
		$sqlstatus
		group by a.ID");
}else $rs = mysql_query("select * from Orders where CID=$CID and UserID='$uid' $sqlstatus order by OrderDate desc,OrderID desc");

list($numshipped)=@mysql_fetch_row(mysql_query("select count(*) from Orders where UserID='$uid' and Status = 'shipped' and ShipDate > '$d30'"));
if($numshipped==1) $is='is'; else $is='are';
$numorders = @mysql_num_rows($rs);
if(!$numorders)$numorders=0;
if($numorders==1) $s=''; else $s='s';
echo <<<EOM
<div align="center" style="padding:8px">
<form method="get" action="track.php">
<input type="hidden" name="a" value="find" />
<input type="submit" value="Search Orders" style="display:none" />

<p class="form-row form-row-wide">
    <label for="username"></label>
    <input placeholder="Search" type="text" name="ss" value="$sstring" size="15" />
</p>

<p class="form-row form-row-small">
   
    <input type="submit" value="Search" />
</p>

<p class="form-row form-row-wide">
    <label for="username">Status:</label>
    <select name="s" onchange="this.form.submit()">
    <option value="new" $stch[new]>NEW</option>
    <option value="inprocess" {$stch["inprocess"]}>INPROCESS</option>
    <option value="incomplete" {$stch["incomplete"]}>ALL OUTSTANDING</option>
    <option value="shipped" $stch[shipped]>SHIPPED</option>
    <option value="cancelled" $stch[cancelled]>CANCELLED</option>
    <option value="all" $stch[all]>ALL</option>
    </select>
</p>
</form>
</div>
<script type="text/javascript">
gocancel=function(oid,dispid){
	var x=confirm('Are you sure you want to cancel order '+dispid+'?');
	if(!x) return;
	self.location.href='track.php?a=cancel&id='+oid;
}

go_reorder=function(oid){
	var x=confirm('Are you sure you want to reorder ?');
	if(!x) return;
	self.location.href='reorder.php?a=reorder&id='+oid;
}



gocancel_req=function(oid,dispid){
	var x=confirm('Are you sure you want to cancel requisition '+dispid+'?');
	if(!x) return;
	self.location.href='track.php?a=cancel_req&id_req='+oid;
}
</script>
<div align="center" style="padding:7px"><b>$numorders order$s found</b></div>

<table class="cart-table responsive-table">
EOM;
while($row=@mysql_fetch_assoc($rs)){
	$itemrs = mysql_query("select * from OrderItems where OrderRecordID='$row[ID]'");
	$numitems = @mysql_num_rows($itemrs);
	switch($row['Status']){
		case 'new': $bg='orange'; break;
		case 'inprocess': $bg='yellow'; break;
		case 'shipped': $bg='lightgreen'; break;
		case 'cancelled': $bg='gray'; break;
	}
   	
     $title_order_date_time = '';
     $title_item_due_date = '';
     $is_custom_order =  $row[is_custom_order];
    
    if($is_custom_order=="Y")
    { 
    	$title_order_date_time = "Order Date Time"; 
    	$title_item_due_date = "Due Date"; 
    }else{
   	 	$title_item_due_date = "Date"; 
    }
    
    
    $order_date_new = date("m/d/Y",strtotime($row["OrderDate"]));
	
	$row['Status'] = strtoupper($row['Status']);
#	$ddt = date("m/d/Y", strtotime($row[DueDate]));




	echo <<<EOM
<tr><td>
<div >
EOM;

//$is_custom_order=="Y" and

if(  $row[custom_desc]!="")
    { 

echo <<<EOM
<div align="center"  style=";" ><strong>$row[custom_desc]</strong>  </div>
<p class="form-row form-row-medium">
	<input type="button" style="float: right; " onclick="set_reorder_order_id('$row[ID]','')" value="Reorder"><br/>
</p>
EOM;
}

if(  $row[custom_desc]=="")
    { 
echo <<<EOM
<div align="center"  style=" margin-bottom:-9px;" ><strong>$row[custom_desc]</strong><br/>  </div>

<input type="button" style="color: black;
position: relative;
top: -8px;
float: right;" onclick="go_reorder('$row[ID]')" value="Reorder"><br/>

EOM;
}

echo <<<EOM
<div style="border:1px outset gray">


<table class="cart-table responsive-table">
<tr>
<th>$title_order_date_time</th><th>$title_item_due_date</th>
<th>Status</th><th>Order ID</th><th>ShipTo Name</th><th>Number of Items</th></tr>
<tr style="background-color:#ffffff;">
EOM;

$order_date_time = '';
$item_due_date = '';
if($is_custom_order=="Y")
{ 
    $order_date_time = $row[OrderDate]; 
    $order_date_new = $row[item_due_date];
    
    
   $order_date_time =  date('m/d/Y g:i A', strtotime($order_date_time));
   
   $order_date_new =  date('m/d/Y', strtotime($order_date_new));
   
   if( $row[item_due_date]=="0000-00-00")
   {
   	$order_date_new = '';
    $order_date_time = '';
   }
   
}
 // style="background:white;opacity:.82;filter: alpha(opacity = 82);"
  

echo <<<EOM
<td>$order_date_time</td>
EOM;

echo <<<EOM
<td>$order_date_new</td><td><b>$row[Status]</b>
EOM;
	if($row['Status'] == 'NEW'){
		
       
        
        echo <<<EOM

<input type="button" style="color:red;" onclick="set_cancel_order_id('$row[OrderID]','$row[ID]')" value="cancel" />
EOM;
	}
	echo <<<EOM
    
</td><td>$row[OrderID]</td>
<td>$row[ShipToName]</td>
<td id="$row[ID]">$numitems</td>
</tr>
</table>
<table class="cart-table responsive-table">
<tr style="color:#000000;"><th nowrap>Item #
EOM;

if($is_custom_order=="Y")
{ 

/*echo <<<EOM
 &nbsp;&nbsp;<img style="" src="../images/add.png" onclick="popupwindow('add_custom_order.php?orderid=$row[ID]&f=1','Edit Custom Order Detail','720','600');">
EOM;*/

echo <<<EOM
 &nbsp;&nbsp;<a href="add_custom_order.php?orderid=$row[ID]&f=1"><img style="" src="../images/add.png"  ></a>
EOM;


}
 
echo <<<EOM
</th><th>Item Title</th><th >Quantity</th><th >Shipped</th>
EOM;

if($is_custom_order=="Y")
{
echo <<<EOM
<th></th>
EOM;
}
echo <<<EOM
</tr>
EOM;

	while($irow=@mysql_fetch_assoc($itemrs)){
		$numshipped = $irow['QtyShipped'];
        
        list($item_title) = mysql_fetch_row ( mysql_query("select item_title from Items where ID='$irow[ItemID]'"));
       
		echo <<<EOM
<tr  valign="top"><td style="color:#000000;" nowrap>$irow[FormID]</td>
EOM;


if($irow[is_custom_order_item]=="Y" and $is_custom_order=="Y")
{


echo <<<EOM
<td style="color:#000000;"><a style="color:blue;" href="edit_custom_order.php?orderid=$irow[OrderRecordID]&itemid=$irow[ItemID]&formid=$irow[FormID]">$item_title</a></td>
EOM;

}else{

	echo <<<EOM
<td style="color:#000000;">$item_title </td>
EOM;

}

echo <<<EOM
<td style="color:#000000;" >$irow[Quantity]</td>
<td style="color:#000000;" >$numshipped</td>
EOM;


if($irow[is_custom_order_item]=="Y")
{
?>

<td align="center">
<a onclick="return set_item_delete_id_from_order('<?php echo $irow[OrderRecordID]; ?>','<?php echo $irow[ItemID]; ?>','<?php echo $irow[ID]; ?>');" href="javascript:void(0);"><i class="fa fa-trash-o"></i></a>

<!--<img onclick="return godeli('<?php echo $irow[OrderRecordID]; ?>','<?php echo $irow[ItemID]; ?>','<?php echo $irow[ID]; ?>');" src="../images/cross.png">
-->

</td>

<?php
}

echo <<<EOM
</tr>
EOM;
	}
	echo <<<EOM
</table>
</div>
<div style="text-align:center;font-size:12px;font-weight:bold;padding:3px;margin:4px 0px 0px 0px;background-color:#f0f0f0;border:1px solid gray;">Shipment Details
<a href="javascript:tgldiv('ship$row[ID]')" style="border:1px solid black; font-weight:bold; font-size:10px;text-decoration:none;padding:0px 4px 0px 4px;">+</a>
</div>
<div id="ship$row[ID]" style="display:none">
<table class="cart-table responsive-table">

EOM;
	$shiprs = mysql_query("select * from OrderShipments where OrderRecordID='$row[ID]'");
	while($srow=@mysql_fetch_assoc($shiprs)){
		$dt = date("m/d/Y g:iA", strtotime($srow['ShipDate']));
		$shipmentid = sprintf("%05d", $row['ID']) . "-$srow[ID]";
		echo <<<EOM
<tr><td><div style="border:1px solid gray;background:#ffffff;padding:5px">Shipment ID: $shipmentid &nbsp; Date: <b>$dt</b> &nbsp; 
EOM;
		if($srow['TrackingID']) echo "Tracking ID: <b>$srow[TrackingID]</b>";
		echo <<<EOM
</div>
</td></tr>
<tr><td><div style="margin-left:20px;border:1px solid gray;border-top:0px;">
	<table class="cart-table responsive-table">
EOM;
		$sirs=mysql_query("select a.*,b.FormDescription from OrderShipmentItems a
			join OrderItems b on b.OrderRecordID=a.OrderRecordID and b.FormID=a.FormID
			where a.OrderRecordID=$row[ID] and a.OrderShipmentID=$srow[ID]
			order by b.FormDescription");
		while($sirow=@mysql_fetch_assoc($sirs)){
			echo <<<EOM
<tr><td nowrap>$sirow[FormID] </td><td>$sirow[FormDescription]</td><td nowrap align="right">qty <b>$sirow[Quantity]</b></td></tr>
EOM;
		}
		echo <<<EOM
</table>
</div>
</td></tr>
EOM;
	}
	echo <<<EOM
</table>
</div><!-- end "ship$srow[ID]" div -->
</div><!-- end of colored item div -->
</td></tr>
EOM;
}




echo <<<EOM
</table>
<script>
tgldiv=function(did){
	var div=document.getElementById(did);
	if(div.style.display == 'none') div.style.display='';
	else div.style.display='none';
}
</script>
EOM;


?>


<script>


function popupwindow(url, title, w, h) 
{
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	return  window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left).focus();;
   
} 



 function godeli(orderid,itemid,sequnce_id)
{
	if(confirm('Are you sure you want to delete that?'))	
		self.location.href='track.php?a=delitem&id='+orderid+'&itemid='+itemid+'&sequnce_id='+sequnce_id;
}

</script>

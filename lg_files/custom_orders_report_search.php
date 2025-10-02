<?php


// searchForm($val) prints the search form, and if $val != '', lists whatever results are found.
// list orders
function searchForm($val){

	if(isset($_SESSION["is_custom_order"]) and !empty($_SESSION["is_custom_order"]))
    {
    	if(!isset($_GET["is_custom_order"]))
        {
        	foreach($_SESSION["is_custom_order"] as $key_is_co => $value_is_co)
            {
            	$_GET[$key_is_co] = $value_is_co;
            }
        
        }
    }

	global $showstatus, $VendorID, $billcodes, $CID, $uid;
	$val=trim($val);
	$stch[$showstatus] = 'selected="selected"';
	$vendor = $_REQUEST['vendor'];
	if(isset($_REQUEST['bc'])) $billcode=$_REQUEST['bc'];
	elseif(isset($_SESSION['osbc'])) $billcode=$_SESSION['osbc'];
	$_SESSION['osbc'] = $billcode;
	if(isset($_REQUEST['cid']) && ($GLOBALS['VendorID'] || $_SESSION['sysadmin'])) {
		$useCID=$_REQUEST['cid'];
		if(!$useCID)$useCID=0;
	} else $useCID=$CID;
	echo <<<EOM
<style>
#grid th { color: white; text-align:left; border-bottom:1px solid black; padding:5px;background:#333333}
#grid td { border-bottom:1px solid #bbbbbb; padding:3px;}
#grid tr { vertical-align: top; }
</style>

<form method="get" action="custom_orders_report.php">
<input type="hidden" name="a" value="find" />
EOM;
?>
<?php

	if(1==3){ //$_SESSION['sysadmin'] || $VendorID){
		if(!$VendorID) $crs = mysql_query("select ID,Name from Clients order by Name");
		else $crs = mysql_query("select a.ID,a.Name from Clients a join VendorClient b on b.VID=$VendorID and b.CID=a.ID order by a.Name");
		echo <<<EOM
Client: <select name="cid" onchange="this.form.submit()"><option value="">All</option>
EOM;
		while(list($i,$n)=@mysql_fetch_row($crs)){
			if($i==$useCID) $ch='selected="selected"'; else $ch='';
			echo "<option value=\"$i\" $ch>$n</option>";
		}
		echo <<<EOM
</select>
EOM;
	}
	echo <<<EOM
<p class="form-row form-row-wide">
	<label for="username"></label>
	<input id="sstring" type="text" name="sstring" value="$val" size="15" />
 </p>
 
<p class="form-row form-row-small">
	<label for="username"></label>
	<input type="submit" value="Search" />
 </p> 
 
 
&nbsp; &nbsp;

EOM;
	echo <<<EOM

EOM;

	echo <<<EOM
<p class="form-row form-row-wide">
	<label for="username">Status:</label>
 <select name="s" onchange="this.form.submit()">
<option value="new" $stch[new]>NEW</option>
<option value="approvalreq" {$stch["approvalreq"]}>APPROVAL REQ</option>
<option value="inprocess" {$stch["inprocess"]}>INPROCESS</option>
<option value="incomplete" {$stch["incomplete"]}>ALL OUTSTANDING</option>
<option value="lateship" {$stch["lateship"]}>LATE SHIP</option>
<option value="shipped" $stch[shipped]>SHIPPED</option>
<option value="cancelled" $stch[cancelled]>CANCELLED</option>
EOM;
	
//	if($uid==1232)
	echo <<<EOM
<option value="partialship" $stch[partialship]>PARTIAL SHIP</option>
<option value="backorder" $stch[backorder]>BACKORDER</option>
EOM;
	echo <<<EOM
<option value="all" $stch[all]>ALL</option>
</select>
</p>
EOM;





?>

<?php

$dispto_new = '';;
$dispfrom_new = '';

if(isset($_GET['dtfrom']) and $_GET['dtfrom']!='')
{
	$dispfrom_new = $_GET['dtfrom'];
	
}

if(isset($_GET['dtto']) and $_GET['dtto']!='')
{		
	$dispto_new = $_GET['dtto'];
}

if($dispto_new!='')
{
	 $dispto_new = date("Y-m-d", strtotime($dispto_new));
}

if($dispfrom_new!='')
{
	 $dispfrom_new = date("Y-m-d", strtotime($dispfrom_new));
}


if($dispto_new!='' and $dispfrom_new!='')
{
	// $date_search = "and b_new.ShipDate between '$dispfrom_new' and '$dispto_new'";
	
	$dispto_new = date('Y-m-d', strtotime($dispto_new . ' + 1 day'));
	 
	 $date_search = "and a.OrderDate between '$dispfrom_new' and '$dispto_new'";
	 
	 $join_search_date = "join OrderShipments b_new on b_new.OrderRecordID=a.ID
	join OrderShipmentItems c_new on c_new.OrderRecordID=a.ID and c_new.OrderShipmentID=b_new.ID";
}



?>



<p class="form-row form-row-wide">
	<label for="username">From:</label>
<input type="hidden" name="excel" value="0" />
<input type="text" name="dtfrom" size="9" value="<?php if(isset($_GET['dtfrom'])) { echo $_GET['dtfrom']; }?>" />
<img align="absmiddle" src="../images/calendar.png" style="cursor:pointer" onclick="displayDatePicker('dtfrom');" />

</p>

<p class="form-row form-row-wide">
	<label for="username">to:</label>
<input type="text" name="dtto" size="9" value="<?php if(isset($_GET['dtto'])) { echo $_GET['dtto']; }?>" />
<img align="absmiddle" src="../images/calendar.png" style="cursor:pointer" onclick="displayDatePicker('dtto');" />

</p>

<p class="form-row form-row-small">
	<label for="username"></label>
	<input type="submit" value="Search"  name="search_new"/>
</p>

<?php

$sql_is_custom_order = '';
if(isset($_GET["is_custom_order"]) and $_GET["is_custom_order"]=="Y")
{
	$sql_is_custom_order = " and a.is_custom_order = 'Y'  ";
	
	$_SESSION["is_custom_order"] = $_GET;
	

}if(isset($_GET["is_custom_order"]) and $_GET["is_custom_order"]=="F")
{
	$sql_is_custom_order = " and a.is_custom_order != 'Y'  ";
	$_SESSION["is_custom_order"] = $_GET;
	
}if(isset($_GET["is_custom_order"]) and $_GET["is_custom_order"]=="all")
{
	//$sql_is_custom_order = " and a.is_custom_order != 'Y'  ";
	$_SESSION["is_custom_order"] = $_GET;
	
}




/*print_r("<pre>");
print_r($_SESSION["is_custom_order"]); */


$between_30_day = "and a.OrderDate BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()";


if(isset($_GET["a"]))
{
	$between_30_day = '';
	
}

if(isset($_GET['s']) and $_GET['s']!="shipped")
 {
	$join_search_date = "";
 }  



	echo <<<EOM
</form>
<script>document.getElementById('sstring').focus();</script>

EOM;
	$weedoutseen='';
	$oristatus=$showstatus;
#	if(1==1||$val || isset($_REQUEST['sstring'])){
	$page=$_REQUEST['page'];
	if(!$page)$page=1;
	$maxlimit = 200;
//	if($uid==1232) $maxlimit = 100;
	if($showstatus == 'partialship' || $showstatus=='backorder') $maxlimit = 5000;
	if($page>1) $limit = ($page-1)*$maxlimit . ", $maxlimit";
	else $limit = $maxlimit;
	if(!$VendorID){
		$vand='';
		if($vendor) $vand = "and c.VendorID = '$vendor'";
//			left join Items c on c.ID=b.ItemID
//				,case when unshp.ID is null then '1' else '' end as AllShipped
		$sql="select SQL_CALC_FOUND_ROWS a.*,u.Name as OrderedBy, case when bo.ID is null then '' else '1' end as BOQuantity,
				case when shp.ID is null then '' else '1' end as SomeShipped,
				case when unshp.ID is null then '1' else '' end as AllShipped
			from Orders a
			left join OrderItems b on b.OrderRecordID=a.ID
			left join OrderItems bo on bo.OrderRecordID=a.ID and bo.BOQuantity>0
			left join OrderItems shp on shp.OrderRecordID=a.ID and shp.Shipped != 0
			left join OrderItems unshp on unshp.OrderRecordID=a.ID and unshp.Shipped = 0 and unshp.BOQuantity=0
            
            $join_search_date 
            

			left join Users u on u.ID=a.UserID
			where (a.CID=$useCID or $useCID=0) and a.is_custom_order = 'Y' 
            
              $date_search
              
              $sql_is_custom_order
            
             ";
             
             
           // and a.UserID = '$AID' 
        // echo $sql;
        
            
		// if FORMID_ prefix is there, then match exactly to FormID and also only match if order hasn't shipped all of this item yet.
		if(substr($val,0,7)=='FORMID_'){
			$sql .= " and b.FormID='" . str_replace("FORMID_","",$val) . "' and b.QtyShipped<b.Quantity";
		}
		else $sql .= "
			and ('$val'='' or a.OrderID like '%$val%' or a.ClientOrderNumber like '%$val%'
			or a.Email like '%$val%' or a.Name like '%$val%'
			or a.ShipToName like '%$val%'
			or a.Phone like '%$val%'
            or a.Notes like '%$val%'
            or a.Company like '%$val%'
			or b.FormID like '%$val%' or b.FormDescription like '%$val%'
			) ";
            
          if(isset($_GET["c"]) and $_GET["c"]=="bo")
          {
          
               $sql .= "
                and (a.BillCode='$billcode' or '$billcode'='')
                and (a.Status='$showstatus' or '$showstatus'='all'
                or ('$showstatus'='incomplete' and a.Status in ('new','inprocess','approvalreq'))
                or ('$showstatus' in ('partialship','backorder','lateship') and a.Status not in ('shipped','cancelled'))
                )
                and ('$showstatus' != 'lateship' || (a.OrderDate < date_add(now(), interval -3 day) and unshp.ID is not null))
                $vand
                $between_30_day
                and b.BOQuantity > 0 AND b.FormID ='$val'
                group by a.ID
                ";
          
          }else{  
            
             $sql .= "
                and (a.BillCode='$billcode' or '$billcode'='')
                and (a.Status='$showstatus' or '$showstatus'='all'
                or ('$showstatus'='incomplete' and a.Status in ('new','inprocess','approvalreq'))
                or ('$showstatus' in ('partialship','backorder','lateship') and a.Status not in ('shipped','cancelled'))
                )
                and ('$showstatus' != 'lateship' || (a.OrderDate < date_add(now(), interval -3 day) and unshp.ID is not null))
                $vand
             
                group by a.ID
                ";
          }  
           
	} else { // special query for vendors to ensure only orders containing items assigned to that vendor
		if($showstatus=='new') {
			$showstatus='incomplete'; // will have to weed out seen in-process ones and show unseen in-process as new...
			$weedoutseen=1;
		}
        
        
 /*
		$sql="select SQL_CALC_FOUND_ROWS a.*,u.Name as OrderedBy, case when bo.ID is null then '' else '1' end as BOQuantity,
				case when shp.ID is null then '' else '1' end as SomeShipped,
				case when unshp.ID is null then '1' else '' end as AllShipped
			from Orders a
			left join OrderItems b on b.OrderRecordID=a.ID
			left join OrderItems bo on bo.OrderRecordID=a.ID and bo.BOQuantity>0
			left join OrderItems shp on shp.OrderRecordID=a.ID and shp.Shipped != 0
			left join OrderItems unshp on unshp.OrderRecordID=a.ID and unshp.Shipped = 0 and unshp.BOQuantity=0
			join Items c on c.ID=b.ItemID and c.VendorID='$VendorID'
			join VendorClient vc on vc.VID='$VendorID' and vc.CID=a.CID
			left join Users u on u.ID=a.UserID
			where (a.CID=$useCID or $useCID=0)
			and ('$val'='' or a.OrderID like '%$val%' or a.ClientOrderNumber like '%$val%'
			or a.Email like '%$val%' or a.Name like '%$val%'
			or a.ShipToName like '%$val%'
			or a.Phone like '%$val%'
			or b.FormID like '%$val%' or b.FormDescription like '%$val%'
			)
			and (a.BillCode='$billcode' or '$billcode'='')
			and (a.Status='$showstatus' or '$showstatus'='all'
			or ('$showstatus'='incomplete' and a.Status in ('new','inprocess','approvalreq'))
			or ('$showstatus'='partialship' and a.Status not in ('shipped','cancelled'))
			or ('$showstatus'='backorder' and a.Status not in ('shipped','cancelled'))
			)
			group by a.ID
			";*/
            
         
            
		 $sql="select SQL_CALC_FOUND_ROWS a.*,u.Name as OrderedBy, case when bo.ID is null then '' else '1' end as BOQuantity,
				case when shp.ID is null then '' else '1' end as SomeShipped,
				case when unshp.ID is null then '1' else '' end as AllShipped
			from Orders a
			left join OrderItems b on b.OrderRecordID=a.ID
			left join OrderItems bo on bo.OrderRecordID=a.ID and bo.BOQuantity>0
			left join OrderItems shp on shp.OrderRecordID=a.ID and shp.Shipped != 0
			left join OrderItems unshp on unshp.OrderRecordID=a.ID and unshp.Shipped = 0 and unshp.BOQuantity=0
			join Items c on c.ID=b.ItemID and c.VendorID='$VendorID'
			join VendorClient vc on vc.VID='$VendorID' and vc.CID=a.CID
            
            $join_search_date
            
			left join Users u on u.ID=a.UserID
			where (a.CID=$useCID or $useCID=0)
            
            $date_search
            
			and ('$val'='' or a.OrderID like '%$val%' or a.ClientOrderNumber like '%$val%'
			or a.Email like '%$val%' or a.Name like '%$val%'
			or a.ShipToName like '%$val%'
			or a.Phone like '%$val%'
			or b.FormID like '%$val%' or b.FormDescription like '%$val%'
			)
			and (a.BillCode='$billcode' or '$billcode'='')
			and (a.Status='$showstatus' or '$showstatus'='all'
			or ('$showstatus'='incomplete' and a.Status in ('new','inprocess','approvalreq'))
			or ('$showstatus'='partialship' and a.Status not in ('shipped','cancelled'))
			or ('$showstatus'='backorder' and a.Status not in ('shipped','cancelled'))
			) 
			group by a.ID
			";
            
            
	}
//if($uid==1232) echo $sql;
#	$rs = mysql_query($sql);
#	$numtot = mysql_num_rows($rs);
	$sql .= " order by a.OrderDate desc limit $limit";
   
// babulal
 //echo  $sql;
	$rs = mysql_query($sql);
	list($numtot)=@mysql_fetch_row(mysql_query("select found_rows()")); // YAY
	if($numtot>=$maxlimit) { // pages
		echo "<div style=\"text-align:left;font-size:12px;\">Page: ";
		$numpages = (int)($numtot/$maxlimit)+1;
		if($page > 5) $first = $page-5;
		else $first=1;
		if($first+10>$numpages) $last=$numpages; else $last=$first+10;
		for($a=$first; $a<=$last; $a++)
			if($a==$page) echo "<a href=\"custom_orders_report.php?page=$a\" style=\"font-size:18px;font-weight:bold;background:yellow;padding:10px;color:black;\">$a</a>";
			else echo "<a href=\"custom_orders_report.php?page=$a\" style=\"font-size:14px;padding:6px\">$a</a>";
		echo " &nbsp; @ $maxlimit per page</div>";
	}
	
	echo <<<EOM

<style>
.bo { color:red; font-weight:bold;}
</style>

<div style="text-align:right"><span style="color:red;font-weight:bold">Bold red text</span> indicates backordered items.<br>
<span style="color:blue">BLUE</span> status indicates some shipment.</div>
<br/>

<script src="js/jquery.js"></script>
<form method="post" >

<table id="grid" class="cart-table responsive-table" cellspacing="0" style="width:100%;border:1px solid gray;">
<tr><th colspan="2">Order ID </th><th>OrderDate</th><th>Order Description</th><th>Ordered By</th><th>ShipTo</th><th>Company</th><th>Status</th><th>&nbsp;</th><th>&nbsp;</th></tr>
EOM;
	while($row=@mysql_fetch_assoc($rs)){
//		if($row['Status'] == 'shipped') $row['AllShipped'] = 1;
		if($showstatus=='partialship' && (!$row['SomeShipped'] || $row['AllShipped'])) continue;
		if($showstatus=='backorder') {
			if(!$row['BOQuantity'] && !$row['AllShipped']) continue;
		}
		if($VendorID){
			list($seen) = @mysql_fetch_row(mysql_query("select 1 from OrderVendorView where OrderID='$row[ID]' and VendorID='$VendorID'"));
			if(($weedoutseen && $seen) || ($showstatus=='inprocess' && !$seen)) continue;
			elseif (!$seen) $row['Status'] = 'new';
		}
		$row['Status'] = strtoupper($row['Status']);
		$row['OrderDate'] = date("m/d/Y", strtotime($row['OrderDate']));
		if($row['BOQuantity']) $stclass = 'class="bo"';
		else $stclass='';
		$stcellclass='';
		if(($showstatus =='inprocess' || $showstatus=='incomplete') && $row['SomeShipped']) $stcellclass = ' style="color:blue"';
		echo <<<EOM
<tr $stclass><td nowrap><a href="custom_orders_report.php?a=edit&id=$row[ID]">$row[OrderID]</a></td><td></td>
<td>$row[OrderDate]</td><td>$row[custom_desc]</td><td>$row[Name]</td>
<td>$row[ShipToName]</td><td>$row[Company]</td><td $stcellclass>$row[Status]</td><td>
EOM;
if($row["is_custom_order"]=="Y")
{
?>

<!--<img src="../images/file_icon.png" onclick="popupwindow('custom_order_file_popup.php?orderid=<?php echo $row['ID'];?>','Required File Upload Detail','700','300');">-->

<img src="../images/file_icon.png" 
onclick="modal_order_all_item_detail('<?php echo $row['ID'];?>');" />

<?php
}else{
	
}
echo <<<EOM
</td>
<td nowrap>

</td></tr>
EOM;
	}
	echo <<<EOM

</table>
</form>
<script>
var tr=document.getElementById('grid').getElementsByTagName('tr');
for(var a=1; a<tr.length; a++){
	tr[a].onmouseover=function(){this.style.backgroundColor='lightyellow';}
	tr[a].onmouseout=function(){this.style.backgroundColor='';}
}
</script>
EOM;
}


?>

<script type="text/javascript">



function alteast_check_one_order()
{
	//orderid = "4830,4827";
	var orderid = '';
	$(".orderitemid").each(function(){
	 if($(this).is(':checked'))
	 {
		  if(orderid=="")
		  {
			  val = $(this).val();
			  orderid = val;
		  }else{
			  val = $(this).val(); 
			  orderid = orderid +','+ val;
			  
		  }
		  
	 }
	  
	});
	
if(orderid=="")
{
	alert("Error: Please select at least one order !");
	return false;
}else{
	if(confirm('Are you sure you want to delete that?')){
		
	}else{
		return false;	
	}		
}
	
}


function popupwindow(url, title, w, h) {
	
	
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
 return  window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left).focus();;
   
} 



</script>


<?
$mystring = $_SERVER['SCRIPT_NAME'];
$findme   = '/admin/';
$pos = strpos($mystring, $findme);
$admin_site = '';
if ($pos === false) {
	$admin_site = '';
} else {
  $admin_site = 1;
}

	$logo='';
	if($CID) {
		$logo=trim(`ls $gfxfolder/portallogo.*|head -1`);
		if($logo) {
			$logo = "$gfxfolder/" . basename($logo);
			$logo = "<img src=\"$logo\" border=\"0\" />";
		}
	}
	
	if($_SERVER["HTTP_HOST"]=="rwaf_dev.localhost.co")
	{
		$logo = "$gfxfolder/portallogo.png" ;
		$logo = "<img src=\"$logo\" border=\"0\" />";
	}
	
	list($nm,$budg,$bal,$renew)=@mysql_fetch_row(mysql_query("select Name,Budget,BudgetBalance,BudgetRenewDate from Users where ID='$_SESSION[AID]'"));
	if($budg) $nm = "<b>$nm</b><br />Budget Balance: \$" . number_format($bal,2) . "<br />Resets " . date("F j, Y", strtotime($renew));
	
	$nm_va = $nm;
	
	$nm =  stripslashes($nm);
	
	$nm = preg_replace('/\\\\/', '', $nm);

	
	
	$switch_to_dashbaord_user="";	
	if(isset($_SESSION['va_CID']))
	{
		$nm = ""; // only remove budget balance for virtual admin
		if(!strstr($_SERVER['PHP_SELF'], 'login.php') ) {
		// $switch_to_dashbaord_user = "<br/><a href='virtualadmin/dashboard.php'> Dashboard </a> ";
		}
	}
	
	if(!strstr($_SERVER['PHP_SELF'], 'new_user_registration.php') ) {
		
		$middlecell = "<td width=\"33%\" align=\"center\"><b>Welcome</b> $nm</b> $switch_to_dashbaord_user</td>";
	
	}
	
	if(strstr($_SERVER['PHP_SELF'], 'confirmation.php') ) {
		
		$middlecell = '';
	
	}

	if(strstr($_SERVER['PHP_SELF'], 'login.php') ) {
		
		$middlecell = '';
	
	}

	
	if(strstr($_SERVER['PHP_SELF'], 'registration_status.php') ) {
		
		$middlecell = '';
	
	}
	
	
	$nm = toSafeDisplay_edit_time($nm);
	
$nm = preg_replace('/\\\\/', '', $nm);
	
	
	if($_SESSION['sysadmin'] && isset($_SESSION['oriAID']) && is_numeric($_SESSION['oriAID']) && $_SESSION['oriAID'] && $_SESSION['oriAID'] != $_SESSION['AID']){
		// admin is acting as another user. Place message / undo link in header center.
		$middlecell = <<<EOM
<td align="center">Acting as $nm.<br /><a href="/admin/users.php?become=$_SESSION[oriAID]">Switch back to self.</a></td>
EOM;
	}
	
	
	$switch_to_dashbaord="";	
	if(isset($_SESSION['va_CID']))
	{
		// $switch_to_dashbaord = "<br/><a href='../virtualadmin/dashboard.php'> Dashboard </a> ";
	}
	
	if(stristr($_SERVER['PHP_SELF'], "admin/") && ($VendorID || $_SESSION['sysadmin'])) 
	{
		$middlecell = "<td width=\"33%\" align=\"center\">";
		if(!$VendorID) $crs = mysql_query("select ID,Name from Clients order by Name");
		else $crs = mysql_query("select a.ID,a.Name from Clients a join VendorClient b on b.VID=$VendorID and b.CID=a.ID order by a.Name");
		
		list($bc_new_is_super_admin)=@mysql_fetch_row(mysql_query("select is_virtual_admin from Users where ID='$_SESSION[AID]'"));
		
		if($bc_new_is_super_admin=="Y")
		{
		
		$middlecell .= <<<EOM
Client: <select onchange="self.location.href='/admin/clients.php?a=switch&b=orders&id='+this.value">
EOM;
		while(list($i,$n)=@mysql_fetch_row($crs)){
			if($i==$CID) $ch='selected="selected"'; else $ch='';
			$middlecell .= "<option value=\"$i\" $ch>$n</option>";
		}
		$middlecell .= <<<EOM
</select>  $switch_to_dashbaord </td>
EOM;
		
		}

	}
	
	 
	
	if(isset($_SESSION['va_CID']))
	{
		$middlecell = '';
		
		
		
		if($_SESSION['va_CID'] != $_SESSION['super_admin_oriCID'])
		{
			$nm_va = preg_replace('/\\\\/', '', $nm_va);
			
			if (strpos($_SERVER['PHP_SELF'],'/admin/') !== false) {
				
				$middlecell = <<<EOM
<td align="center">Acting as $nm_va.<br /><a href="../virtualadmin/client_switch.php?a=switch&b=orders&id=$_SESSION[super_admin_oriCID]">Switch back to self.</a></td>
EOM;
    
}else{

$nm_va = preg_replace('/\\\\/', '', $nm_va);
		$middlecell = <<<EOM
<td align="center">Acting as $nm_va.<br /><a href="virtualadmin/client_switch.php?a=switch&b=orders&id=$_SESSION[super_admin_oriCID]">Switch back to self.</a></td>
EOM;

}

		}
		
		if($_SESSION['AID'] != $_SESSION['oriAID'])
		{
			$middlecell = '';
			
			$nm_va = preg_replace('/\\\\/', '', $nm_va);
			
			//echo $_SESSION[AID];
			if(isset($_SESSION[oriAID]))
			{
				
			if (strpos($_SERVER['PHP_SELF'],'/admin/') !== false) {
				
				$middlecell = <<<EOM
<td align="center">Acting as $nm_va.<br /><a href="../virtualadmin/client_switch.php?a=switch&b=orders&id=$_SESSION[CID]">Switch back to self.</a></td>
EOM;
    
}else{

		$middlecell = <<<EOM
<td align="center">Acting as $nm_va.<br /><a href="virtualadmin/client_switch.php?a=switch&b=orders&id=$_SESSION[CID]">Switch back to self.</a></td>
EOM;

}
			}
			
		
			
		}
		
		if($_SESSION['va_CID'] == $_SESSION['CID'] and $_SESSION['AID'] == $_SESSION['oriAID'])
		{
			$middlecell = '';
		}
		
		
	}
?>
<html>
<head>
<title><? echo $companyname; ?> Fulfillment</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<style>
body,td,th { font-family: Arial,Helvetica,Sans-serif; font-size:10pt; }
h2 { font-size: 13pt; }
body { padding:0px; margin:0px; }
</style>
<script type="text/javascript" src="javascript.js"></script>
<script type="text/javascript" src="datepicker.js"></script>
<link rel="stylesheet" href="datepicker.css" />
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js"></script>
</head>

<?php
$style_login = '';
if($_SERVER["HTTP_HOST"]=="rwaf.co" and $_SERVER["PHP_SELF"]=="/login.php")
{
$style_login = 'height:380px;';	
}
?>

<body style="padding:10px">
<table  <?php if($admin_site==1){ echo 'width="1300"'; }else{ ?> width="1200" <?php } ?> align="center"
style="border:1px
 outset gray; border-radius:9px; -moz-border-radius:9px;
box-shadow: 5px 4px 5px #ccc;
padding:6px; <?php echo $style_login;?> ">
<?

if($_SERVER["HTTP_HOST"]=="rwaf.co" and $_SERVER["PHP_SELF"]=="/login.php")
{
echo <<<EOM
<tr><td colspan="3" align="center"><a style="text-decoration:none;" href="index.php"><img src="images/rw_associates.png" /></a>

$switch_to_dashbaord_user 

</td></tr>
EOM;

}else{

if($logo) echo <<<EOM
<tr><td width="33%"><a style="text-decoration:none;" href="index.php">$logo</a></td>$middlecell <td align="right" width="33%"><img src="images/rw_associates.png" /> </td></tr>
EOM;
else echo <<<EOM
<tr><td colspan="3" align="center"><a style="text-decoration:none;" href="index.php"><img src="images/rw_associates.png" /></a>$switch_to_dashbaord </td></tr>
EOM;

}

?>

<tr><td colspan="3">

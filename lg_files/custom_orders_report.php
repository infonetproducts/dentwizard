<?php
ob_start();
include("setting.php");
include("include/start.php");


$orderstatus = array('new','inprocess','approvalreq','shipped','cancelled');
#if(!$_SESSION['admin'] && !isset($AdminPerms[$MyMenuID])) { echo "Security error. Exiting."; exit; }

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';
$original_action=$action;


if(!$loggedin && $action != 'cancel') exit; // bullshit so non-user can approve/deny.


$sstring='';
if(isset($_REQUEST['sstring'])) $sstring = trim($_REQUEST['sstring']);
elseif(isset($_SESSION['osstring'])) $sstring = $_SESSION['osstring'];
$_SESSION['osstring'] = $sstring;

if(isset($_REQUEST['s'])) $showstatus=$_REQUEST['s'];
elseif(isset($_SESSION['ostatus'])) $showstatus=$_SESSION['ostatus'];
//elseif($sstring)$showstatus='all';
else $showstatus='incomplete';
$_SESSION['ostatus'] = $showstatus;





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
			<h2>Custom Order Report</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Custom Order Report</li>
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
include_once("custom_orders_report_search.php");
include_once("custom_orders_report_edit.php");
		 
if(!$action || $action=='find') {
	searchForm($sstring);
}elseif($action=='edit'){
	$id = $_REQUEST['id'];
	editForm($id);
}


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

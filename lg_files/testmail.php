<?php 
ob_start();
session_start();
include("include/db.php");
include("stripe_key_setting.php");
include_once("shop_common_function.php");


include_once("include/sendgrid_shop.php");


$msg = "";
ob_start();
require_once('email_body_order_confirmation.php');
$msg = ob_get_contents();
ob_end_clean();	

//$msg = "This is testing email body. ";

$subject = "Order Confirmation $order_id ";

//good_mail_folderscheap("jkrugger@dmsys.co",$email,$subject,$msg,"From: FoldersCheap <jkrugger@dmsys.co>");

$admin_email = "jkrugger@dmsys.co";
$admin_email = "kcchoudhary2019@gmail.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");


?>
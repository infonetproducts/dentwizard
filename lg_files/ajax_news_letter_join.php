<?php
session_start();
include_once("include/db.php");
include_once("shop_common_function.php");
include_once("include/sendgrid_shop.php");

$action = '';
if(isset( $_POST['action'] ))
{
	$action = $_POST['action'] ;
}

$CID = $_SESSION['CID'] ;

if( $action == "news_letter_join")
{
	
	$news_letter_email = $_POST['news_letter_email'] ;
	$created_dtm = date("Y-m-d H:i:s");
	
	$news_letter_email = mysql_escape_string($news_letter_email);
	
	$sql_insert = "insert into news_letter_user 
	set 
	
		cid = '$CID'
		,email = '$news_letter_email'
		,created_dtm = '$created_dtm'
	
	";
	
	mysql_query($sql_insert);

 
 
 $date_time = date("m/d/Y h:iA");
 
$msg = "

Below are the subscriber details: <br/><br/>

Mailing List: Fort LeBoeuf School District<br/> 
<strong>Date/Time: </strong> $date_time<br/> 
<strong>Email: </strong> $news_letter_email

<br/>

";



$subject = "You have a new subscriber (Fort LeBoeuf School District)";
 
 
$admin_email = "kcchoudhary2019@gmail.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");

$admin_email = "info@leadergraphic.com";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");


$admin_email = "jkrugger@dmsys.co";
good_mail("jkrugger@dmsys.co",$admin_email,$subject,$msg,"From: Shop <jkrugger@dmsys.co>");

}




 
?>

  
	  

			 
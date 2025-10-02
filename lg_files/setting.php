<?php
session_start();
include_once("include/db.php");
include_once("shop_common_function.php");

if(!isset($_SESSION['AID']))
{
	header("location:../login.php");
	die;
}


//pr_n($_SESSION);

$host_url_shop = $_SERVER['HTTP_HOST'] ;

if($_SERVER['HTTP_HOST']!="shop_dev.localhost.co")
{
	//$host_url_shop = "ftleboeuf.shopleadergraphics.com";
}

$arr_site_info = parse_url_all($host_url_shop) ;

//pr_n($arr_site_info);

if (strpos($host_url_shop, '_dev.localhost.') !== false) 
{
	// this is for local server  
	$host_url_shop = $arr_site_info['host'] ;  
	
}else if (strpos($host_url_shop, 'www.') !== false) 
{
	$host_url_shop = $arr_site_info['domain'] ;
	
}else{

	$host_url_shop = $arr_site_info['host'] ;  
	
}


if($_SERVER['HTTP_HOST']=="shopleadergraphics.com")
{
	//$host_url_shop = "ftleboeuf.shopleadergraphics.com";
	header("location:http://www.leadergraphic.com/");
	die;
}

if($_SERVER['HTTP_HOST']=="www.shopleadergraphics.com")
{
	//$host_url_shop = "ftleboeuf.shopleadergraphics.com";
	
		header("location:http://www.leadergraphic.com/");
	die;
	
	
}

if($_SERVER['HTTP_HOST']=="lgstore.com.com")
{
	//$host_url_shop = "ftleboeuf.shopleadergraphics.com";
	header("location:http://www.lgstore.com.com/");
	die;
}

if($_SERVER['HTTP_HOST']=="www.lgstore.com.com")
{
	//$host_url_shop = "ftleboeuf.shopleadergraphics.com";
	
		header("location:http://www.lgstore.com/");
	die;
	
	
}

 

 

//echo $_SERVER['HTTP_HOST'] ; 
$CID =  $_SESSION['CID'];
$sql_get_shop_detail = " SELECT * FROM  `Clients` where ID = '$CID'   ";
$client_detail = mysql_fetch_assoc(mysql_query($sql_get_shop_detail));

$logo_path = "";
$client_company_name = "";

if(!empty($client_detail))
{
	 $CID = $client_detail['ID'] ;
	 $client_company_name = $client_detail['Name'] ;
	 
	 $_SESSION['CID'] = $CID ;
	 $_SESSION['user_id_shop'] = $_SESSION['AID'] ;
	
	$logo_path = "gfx/$CID/portallogo.png";
	
//	$logo_path = "images/DT_Logo_Duo_RGB.png";
	
	
	
	if($client_detail['fullfilment_site_theme']=="fullfilment")
	{
		header("location:../index.php");
		die;
	}
	
	
	
}else{
	
	die("Please contact to administrator");
	
	
	
}    
// and $_SERVER['HTTP_HOST']!="www.tahstore.com"    and $_SERVER['HTTP_HOST']!="tahstore.com" 

if($_SERVER['HTTP_HOST']!="shop_dev.localhost.co" and $_SERVER['HTTP_HOST']!="shopkwikfill_dev.localhost.co" and $_SERVER['HTTP_HOST']!="rwaf2.co" and $_SERVER['HTTP_HOST']!="shopkwikfill.rwaf2.co"   and $_SERVER['HTTP_HOST']!="ftleboeuf.rwaf2.co" and $_SERVER['HTTP_HOST']!="shoplecom_dev.localhost.com"  and $_SERVER['HTTP_HOST']!="shopcountryfair_dev.localhost.com"  and $_SERVER['HTTP_HOST']!="shopmcdowelllacrosse_dev.localhost.com"  and $_SERVER['HTTP_HOST']!="tah_dev.localhost.com"  and $_SERVER['HTTP_HOST']!="robinson_dev.localhost.com" and $_SERVER['HTTP_HOST']!="shoplecom.com" and $_SERVER['HTTP_HOST']!="shopcountryfair.com"  and $_SERVER['HTTP_HOST']!="www.shoplecom.com" and $_SERVER['HTTP_HOST']!="www.shopcountryfair.com"     and $_SERVER['HTTP_HOST']!="apparel_dev.localhost.com"  and $_SERVER['HTTP_HOST']!="apparel.rwaf2.co" and $_SERVER['HTTP_HOST']!="kamalshop_dev.localhost.com"     and $_SERVER['HTTP_HOST']!="babulalshop_dev.localhost.com"     )  
{

	if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) 
	{
	
	}else {

		if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off" ){
			$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirect);
			exit();
		}else{
			
			
			//$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			//header('Location: ' . $redirect);
			
		}
	
	}

}


//$logo_path = "images/logo.png";

/*
print_r("<pre>");
print_r($client_detail);

die;*/

?>
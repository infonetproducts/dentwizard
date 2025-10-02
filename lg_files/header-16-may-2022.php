<!DOCTYPE html>
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

<!-- Basic Page Needs
================================================== -->
<meta charset="utf-8">
<title> 

<?php echo $client_company_name; ?>

 </title>

<!-- Mobile Specific Metas
================================================== -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<!-- CSS
================================================== -->
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/colors/green.css" id="colors">

<?php //include("css_dynamic_setting.php");

if($CID==56)
{
	include("css_dynamic_setting_56.php");
	
}else if($CID==58)
{
	include("css_dynamic_setting_58.php");
	
}else if($CID==59)
{
	include("css_dynamic_setting_59.php");
	
}else if($CID==60)
{
	include("css_dynamic_setting_60.php");
	
}else if($CID==62)
{
	include("css_dynamic_setting_62.php");
	
}else if($CID==63)
{
	include("css_dynamic_setting_63.php");
	
}else if($CID==61)
{
	include("css_dynamic_setting_61.php");
}else{
	// all client css 
	include("css_dynamic_setting.php");
	
}



?>
 
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

 

</head>

<body class="boxed">
<div id="wrapper">

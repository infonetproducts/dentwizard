<?php



if(isset($_GET['catid']) and $_GET['catid']==4)
{
	include_once("index_grid_businesscard.php");
	
}else if(isset($_GET['catid']) and $_GET['catid']==1935)
{
	include_once("index_grid_businesscard.php");
	
		
}else{
	include_once("index_grid_new.php");
}

die;
?>

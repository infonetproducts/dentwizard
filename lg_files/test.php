<?php
session_start();
if(isset($_GET['d']) and $_GET['d']==1)
{
	session_destroy();
	print_r("<pre>");
	print_r($_SESSION);
}else{
	print_r("<pre>");
	print_r($_SESSION);
}
?>
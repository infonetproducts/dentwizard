<?php
session_start();
pr($_SESSION);




function pr($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

?>
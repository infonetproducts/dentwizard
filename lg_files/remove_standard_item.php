<?
include_once("include/start.php");
$item_id = $_GET['id'];
$formid = $_GET['formid'];
$_SESSION['Order'][$formid] ;


if(isset($_SESSION['Order'][$formid]))
{
	unset($_SESSION['Order'][$formid]);
}

if(empty($_SESSION['Order']))
{
	unset($_SESSION['bccart']);
	unset($_SESSION['Order']);
}

//echo count($_SESSION['custom_new'][$item_id]); die;
/* print_r("<pre>");
print_r($_SESSION);
die;*/

header("Location:shopping-cart.php");

?>

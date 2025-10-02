<?
include_once("include/start.php");
$item_id = $_GET['id'];
$setter_array = $_GET['set'];
$formid = $_GET['formid'];



if(isset($_SESSION['custom_new'][$item_id][$setter_array]))
{
	unset($_SESSION['custom_new'][$item_id][$setter_array]);
}

//echo count($_SESSION['custom_new'][$item_id]); die;

if(count($_SESSION['custom_new'][$item_id])==0)
{
	unset($_SESSION['order'][$formid]);	
	unset($_SESSION['custom']);
	
}

header("Location:shopping-cart.php");

?>

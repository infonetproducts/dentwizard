<?php 
ob_start();
session_start();
include("include/db.php");
include("stripe_key_setting.php");
include_once("shop_common_function.php");

$error_msg = 'Transaction failed. Please check credit cart detail';


foreach($_POST as $k => $v )
{
	$_POST[$k] = mysql_escape_string($v);
}

$bill_code_tmp = '';
if(isset($_POST['action_billcode']) and $_POST['action_billcode']=="bill")
{
	$action = $_POST['action_billcode'] ;
	$department_code_tmp = $_POST['department_code'] ;
}




/*echo $bill_code_tmp;
die;*/

foreach($_SESSION['billing_form'] as $k_form=>$v_form)
{
	$_POST[$k_form] = $v_form;
}

foreach($_SESSION['delivery_form'] as $k_form=>$v_form)
{
	$_POST[$k_form] = $v_form;
}


/*print_r("<pre>");
print_r($_POST);

die; */

$ShipToName  = ucfirst($_POST['shipping_first_name']). " ". ucfirst($_POST['shipping_last_name']) ;

 $price = $_SESSION['total_price'] ;

if(isset($_SESSION['total_price_after_discount']))
{
	$_SESSION['total_price']  =  $_SESSION['total_price_after_discount'] ;
	$price = $_SESSION['total_price_after_discount'];
}



/*pr_n($_SESSION);

 echo $price ; 
 die;*/

//$price = 100;

//$price = .50 ;


$price = $price  * 100 ;

//$user_id = 5560 ; 

$user_id = '';

if($_SESSION['CID']==56)
{
	$user_id = 5560 ; 
}

if($_SESSION['CID']==58)
{
	$user_id = 5773 ; 
}

if($_SESSION['CID']==64)
{
	$user_id = 5994 ; 
}


if($_SESSION['CID']==59)
{
	$user_id = 5803 ; 
}

if($_SESSION['CID']==60)
{
	$user_id = 5824 ; 
}

if($_SESSION['CID']==61)
{
	$user_id = 5825 ; 
}

if($_SESSION['CID']==62)
{
	$user_id = 5897 ; 
}


if($_SESSION['CID']==63)
{
	$user_id = 5935 ; 
}

if($user_id=="")
{
	$user_id = $_SESSION['user_id_shop'] ;
} 





$order_id = date("md-His") . "-$user_id";

$testing_on = 1 ; // 1 means on and 2 means off




if($testing_on==1 and $_SERVER['HTTP_HOST']=='shopkwikfill_dev.localhost.co')
{
	include_once("create_order.php");
	
	header("location:order_confirmation.php?oid=$order_id");
	die;

}

if(isset($_POST['payment']) and $_POST['payment']=="disable")
{
	$_SESSION['yes_redirect_confirmation'] = 1 ;
	include_once("create_order.php");
	die;
}

if(isset($department_code_tmp) and $department_code_tmp!="")
{

	$_SESSION['yes_redirect_confirmation'] = 1 ;
	include_once("create_order.php");
	die;
}


if($testing_on==1)  // this is only local server because stripe not work on local server.
{
	if (strpos($_SERVER['HTTP_HOST'], '.localhost.') !== false) 
	{
		include_once("create_order.php");
		header("location:order_confirmation.php?oid=$order_id");
		die;
   
	}

}

 
 $name_pass_to_stripe = "$ShipToName $_POST[shipping_email] $order_id ";

try {
	require_once('Stripe/lib/Stripe.php');
	Stripe::setApiKey(secret_key_stripe);

	$charge = Stripe_Charge::create(array(

	"amount" => $price,
 	"currency" => "usd",
  	"card" => $_POST['stripeToken'],
  	"description" => "Charge for new shop. $name_pass_to_stripe "
));
	//send the file, this line will be reached if no error was thrown above
	//echo "<h1>Your payment has been completed. Transaction Detail is Below.</h1>";

/*print_r("<pre>");
print_r($charge);


die;*/


/*print_r("<pre>");
print_r($charge);


die;*/

  //you can send the file to this email:
     // echo $_POST['stripeEmail'];
	 
	 
	 
	$status = $charge->status;
	$balance_transaction = $charge->balance_transaction;
	$exp_month = $charge->source->exp_month;
	$exp_month = $charge->source->exp_month;
	$exp_year = $charge->source->exp_year;
	$last4 = $charge->source->last4;
	
	$failure_code = $charge->failure_code;
	$failure_message = $charge->failure_message;
	
	$stripe_transation_id = $charge->id ;

	include_once("create_order.php");

	//die('here');
	
	unset($_SESSION['error_payment']);
	unset($_SESSION['total_price']);
	
  
		header("location:order_confirmation.php?oid=$order_id");
	
		//header("location:release-of-medical-records.php");
		die;
		
 
  
}

catch(Stripe_CardError $e) {
	$_SESSION['error_payment'] =  $e->getMessage();
	header("location:checkout-payment-order-review.php");
	die;
}

//catch the errors in any way you like

 catch (Stripe_InvalidRequestError $e) {
  // Invalid parameters were supplied to Stripe's API
  
 	$_SESSION['error_payment'] =  $e->getMessage();
	header("location:checkout-payment-order-review.php");
	die;

} catch (Stripe_AuthenticationError $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
	$_SESSION['error_payment'] =  $e->getMessage();
	header("location:checkout-payment-order-review.php");
	die;

} catch (Stripe_ApiConnectionError $e) {
  // Network communication with Stripe failed
} catch (Stripe_Error $e) {
	
	$_SESSION['error_payment'] =  $e->getMessage();
	header("location:checkout-payment-order-review.php");
	die;

  // Display a very generic error to the user, and maybe send
  // yourself an email
} catch (Exception $e) {
	
	$_SESSION['error_payment'] =  $e->getMessage();
	header("location:checkout-payment-order-review.php");
	die;

  // Something else happened, completely unrelated to Stripe
}


/*
// this is posted by form
Array
(
    [card_name] => Kamal Choudhary
    [card_number] => 4242424242424242
    [PaymentType] => Visa
    [exp_month] => 01
    [exp_year] => 2028
    [CVV] => 123
    [Product] => 3 Opinion Expert Review
    [checkbox] => on
    [stripeToken] => tok_1AhG66BCnahZpst1YmenQYD1
)*/


?>
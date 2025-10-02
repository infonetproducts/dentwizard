<?php 
ob_start();
session_start();
include("include/db.php");
include("stripe_key_setting.php");
include_once("shop_common_function.php");

$error_msg = 'Transaction failed. Please check credit cart detail';

$price = 6.94 ;
 
$price = $price  * 100 ;

 

try {
	require_once('Stripe/lib/Stripe.php');
	Stripe::setApiKey(secret_key_stripe);

	/*$charge = Stripe_Charge::create(array(

	"amount" => $price,
 	"currency" => "usd",
  	"card" => $_POST['stripeToken'],
  	"description" => "Refund for order id: . $order_id "
));*/

$transaction_id = "ch_1D1fbrKqlOS2AYpBqWNLbxmm";

$Stripe = Stripe_Charge::retrieve($transaction_id);
$charge = $Stripe->refunds->create(array('amount' => $price ));



	//send the file, this line will be reached if no error was thrown above
	//echo "<h1>Your payment has been completed. Transaction Detail is Below.</h1>";

print_r("<pre>");
print_r($charge);


die;



	 
	 
	$status = $charge->status;
	$balance_transaction = $charge->balance_transaction;
	$exp_month = $charge->source->exp_month;
	$exp_month = $charge->source->exp_month;
	$exp_year = $charge->source->exp_year;
	$last4 = $charge->source->last4;
	
	$failure_code = $charge->failure_code;
	$failure_message = $charge->failure_message;
	
	$stripe_transation_id = $charge->id ;

	 

	//die('here');
	
	
	
  
		//header("location:order_confirmation.php?oid=$order_id");
	
		//header("location:release-of-medical-records.php");
		die;
		
 
  
}

catch(Stripe_CardError $e) {
	echo $_SESSION['error_payment'] =  $e->getMessage();
	//header("location:checkout-payment-order-review.php");
	die;
}

//catch the errors in any way you like

 catch (Stripe_InvalidRequestError $e) {
  // Invalid parameters were supplied to Stripe's API
  
 	echo $_SESSION['error_payment'] =  $e->getMessage();
	//header("location:checkout-payment-order-review.php");
	die;

} catch (Stripe_AuthenticationError $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
	echo $_SESSION['error_payment'] =  $e->getMessage();
	//header("location:checkout-payment-order-review.php");
	die;

} catch (Stripe_ApiConnectionError $e) {
  // Network communication with Stripe failed
} catch (Stripe_Error $e) {
	
	echo $_SESSION['error_payment'] =  $e->getMessage();
	//header("location:checkout-payment-order-review.php");
	die;

  // Display a very generic error to the user, and maybe send
  // yourself an email
} catch (Exception $e) {
	
	echo $_SESSION['error_payment'] =  $e->getMessage();
	//header("location:checkout-payment-order-review.php");
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
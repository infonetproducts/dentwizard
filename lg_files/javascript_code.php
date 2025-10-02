<!--<script src="js/jquery.js" type="text/javascript"></script>-->

 

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
  Stripe.setPublishableKey('<?php echo publishable_key_stripe;?>');
  
  
  $(function() {
  var $form = $('#payment-form');
  $form.submit(function(event) {
 
 
 // alert('test');
  
    // Disable the submit button to prevent repeated clicks:
    $(".submit").hide();
    $("#ajax_loader").show();
	
   
    $form.find('.submit').prop('disabled', true);

    // Request a token from Stripe:
    Stripe.card.createToken($form, stripeResponseHandler);
	
	console.log("dfdfd");
	
    // Prevent the form from being submitted:
    return false;
  });
});


function stripeResponseHandler(status, response) {
	console.log("function is calling : stripeResponseHandler");
	console.log(status);
	
	console.log(response);
	
  // Grab the form:
  var $form = $('#payment-form');

  if (response.error) { // Problem!

    // Show the errors on the form:
   // $form.find('.payment-errors').text(response.error.message);
     
	 // alert(response.error.message);
	 $('.payment-errors').html(response.error.message);
	 
	 
	 $("#ajax_loader").hide();
	 $(".submit").show();
	 
    $form.find('.submit').prop('disabled', false); // Re-enable submission

  } else { // Token was created!

    // Get the token ID:
    var token = response.id;

    // Insert the token ID into the form so it gets submitted to the server:
    $form.append($('<input type="hidden" name="stripeToken">').val(token));

    // Submit the form:
    $form.get(0).submit();
  }
};

</script>
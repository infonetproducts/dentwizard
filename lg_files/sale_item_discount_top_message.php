<?php
//pr_n($_SESSION['Order']);
if(isset($_SESSION['Order'][41102]) and !empty($_SESSION['Order'][41102]))
{

?>


<h5 style="margin-top:-40px; margin-bottom: 10px; " align="center"><strong style="color:red;" >Your cart contains a sale item. For every two T-shirts you purchase, the price will be discounted from $20 to $15 and will be reflected in your order total.</strong></h5>

<?php
}
?>
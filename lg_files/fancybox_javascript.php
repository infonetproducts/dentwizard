 
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<link rel="stylesheet" type="text/css" media="all" href="fancybox/jquery.fancybox.css">
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>-->

<!--above file is included from header.php file. jquery.min.js -->


<script type="text/javascript" src="fancybox/jquery.fancybox.js?v=2.0.6"></script>

 
 
<script  type="text/javascript"> 
	$(document).ready(function() 
	{
		ajax_cart_total() ;
		ajax_get_header_cart_item();
		
		$(".modalbox").fancybox(
		
		{		autoSize: true,	
		
		
			/*	autoSize: true,
				minWidth: 800,
				minHeight: 580*/
		}
		
		);
		 
	});
	
	
	function close_fancybox()
	{
		$.fancybox.close();
		
	}
	
</script>


<style>

.fancybox-close {
    position: absolute;
    top: 4px;
    right: 10px;
    width: 36px;
    height: 36px;
    cursor: pointer;
    z-index: 8040;
    /* left: -10px; */
}

</style>

<div id="inline_grid_add_to_card" class="modalbox" style="min-width:300px;" ></div>


<div id="inline" class="modalbox" style="min-width:300px;" >
	<br/>
	<table class="cart-table responsive-table">
			<tr>
				<th >Please select <?php if(isset($group_name)){echo $group_name;} ?></th>
			</tr>	
	</table>
	 
</div>
 
 

<div id="inline_logo" class="modalbox" style="min-width:300px;display:none;" >
	<br/>
	<table class="cart-table responsive-table">
			<tr>
				<th >Please select logo</th>
			</tr>	
	</table>
	 
</div> 

<div id="inline_name_validation" class="modalbox" style="min-width:300px; display:none;" >
	<br/>
	<table class="cart-table responsive-table">
			<tr>
				<th >Please enter Custom Name</th>
			</tr>	
	</table>
	 
</div> 


<div id="inline_number_validation" class="modalbox" style="min-width:300px;display:none;" >
	<br/>
	<table class="cart-table responsive-table">
			<tr>
				<th >Please enter Custom Number</th>
			</tr>	
	</table>
	 
</div> 


 
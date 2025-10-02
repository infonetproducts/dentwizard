 
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<link rel="stylesheet" type="text/css" media="all" href="fancybox/jquery.fancybox.css">
<script type="text/javascript" src="fancybox/jquery.fancybox.js?v=2.0.6"></script>

 
 
<script  type="text/javascript"> 
	$(document).ready(function() 
	{
		 
		
		$(".modalbox").fancybox(
		{		autoSize: true,	
		
		
			/*	autoSize: true,
				minWidth: 800,
				minHeight: 580*/
		}
		
		);
		 
	});
	
	
	function close_fancybox_news_letter()
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




<div id="inline_news_letter" class="modalbox" style="min-width:300px; display:none;" >
	<br/>
	<table class="cart-table responsive-table">
			<tr>
				<th >Thanks for join news letter</th>
			</tr>	
	</table>
	 
</div>

<div id="inline_size_message" class="modalbox" style="min-width:250px; display:none;" >
	Please select <?php if(isset($group_name)){echo $group_name;} ?>
	<!--<table class="cart-table responsive-table">
			<tr>
				<th style="color:red;" >Please select size.</th>
			</tr>	
	</table>-->
	
</div>

<div id="inline_color_message" class="modalbox" style="min-width:250px; display:none;" >
	Please select color.
	<!--<table class="cart-table responsive-table">
			<tr>
				<th style="color:red;" >Please select size.</th>
			</tr>	
	</table>-->
	
</div>




 
 
 
 
 
 
 
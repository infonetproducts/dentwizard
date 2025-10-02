<link rel="stylesheet" type="text/css" media="all" href="style.css">
<link rel="stylesheet" type="text/css" media="all" href="fancybox/jquery.fancybox.css">
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>-->

<!--above file is included from header.php file. jquery.min.js -->


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


<div id="inline_error_item_add_to_cart" class="modalbox" style="min-width:250px; display:none;" >
	 <h2>Error</h2>
    <p><br/>
    Sorry, you cannot order items from more that one category / brand at the same time. <br/>Please complete the order for the items that are currently in your cart and then start a new order.
    </p>
</div>



<div id="inline_delete_business_cart_modal" class="modalbox" style="min-width:250px; display:none;" >
   
     <h2>Confirmation</h2>
   
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
         
         Are you sure you want to delete that? <br/><br/>
         
           <a onclick="delete_buss_confirmation(this);" id="item_id_buss" delete_id="" class="button color">Yes</a>
         
           <!--<a onclick="self.location.href='index.php?catid=4#all'"  href="javascript:void(0);"  item_id="" class="button color btn_modal ">Cancel</a>-->
          
          
           <a onclick="self.location.href='continue_shopping.php?catid=<?php echo $_GET['catid'];?>'" class="button color">Cancel</a>
           
           
           
        </div>
       
     </p>
	
</div>


<div id="inline_delete_shipping_address_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Confirmation</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
         Are you sure you want to delete this address?<br/><br/>
           <a onclick="delete_shipping_add_confirmation(this);" id="item_id_buss" delete_id="" class="button color">Yes</a>
           <a onclick="self.location.href='profile.php'" class="button color">Cancel</a>
        </div>
     </p>
</div>


<div id="inline_order_cancal_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Confirmation</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
         
Are you sure you want to cancel order <span id="order_id_cancel"></span>?<br/><br/>
           <a onclick="cancel_order_confirmation(this);" id="cancel_order_id_btn" delete_id="" class="button color">Yes</a>
           <a onclick="self.location.href='track.php'" class="button color">Cancel</a>
        </div>
     </p>
</div>

<div id="inline_order_reorder_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Confirmation</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
         
Are you sure you want to reorder ? <span id="order_id_cancel"></span><br/><br/>
           <a onclick="reorder_order_confirmation(this);" id="reorder_order_id_btn" delete_id="" class="button color">Yes</a>
           <a onclick="self.location.href='track.php'" class="button color">Cancel</a>
        </div>
     </p>
</div>

<div id="inline_item_delete_from_order_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Confirmation</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
         
Are you sure you want to delete that?<br/><br/>
           <a onclick="delete_order_item_confirmation(this);" id="delete_item_btn" OrderRecordID="" ItemID="" sequence_id="" class="button color">Yes</a>
           <a onclick="self.location.href='track.php'" class="button color">Cancel</a>
        </div>
     </p>
</div>



 



<div id="inline_order_item_detail_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Order Item Detail</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
      		<span id="item_detail_ajax"></span>
          
          <!-- <a onclick="self.location.href='custom_orders_report.php?a=edit&id=<?php echo $_GET['id']; ?>&t=<?php echo time();?>'" class="button color">Close</a>-->
        </div>
     </p>
</div>




<div id="inline_order_all_item_detail_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Order Item Detail</h2>
     <p>     
          <div class="form-group" align="center" id="" style="display:block;">
      		<span id="all_item_detail_ajax"></span>
          
          <!-- <a onclick="self.location.href='custom_orders_report.php?a=edit&id=<?php echo $_GET['id']; ?>&t=<?php echo time();?>'" class="button color">Close</a>-->
        </div>
     </p>
</div>



<div id="inline_two_button_modal" class="modalbox" style="min-width:250px; display:none;" >
   
     <h2>Add to Cart</h2>
   
     <p>     
          <div class="form-group" align="center" id="div_btn_modal" style="display:block;">
         
        
        <div >You have added the following item to your cart:</div>
        <div > <strong>Item Title:</strong> <span id="title_span"></span> </div>
        <div >  <strong>Quantity:</strong> <span id="qty_span"></span> <br/> </div>
        
        
         <a onclick="self.location.href='continue_shopping.php?catid=<?php echo $_GET['catid'];?>'" class="button color">Continue Shopping</a>
         
           <a onclick="self.location.href='shopping-cart.php'"  href="javascript:void(0);"  item_id="" class="button color btn_modal ">Proceed to Checkout</a>
           
        </div>
       
     </p>
	
</div>

<div id="inline_qty_error_modal" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Error</h2>
     <p>     
      	  <div id="error_qty">Please enter quantity</div>
     </p>
</div>

<div id="inline_qty_error_modal_business" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Error</h2>
     <p>     
      	  <div id="error_qty_buss">Please select quantity</div>
     </p>
</div>


<div id="inline_business_card_preview" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Error</h2>
     <p>     
      	  <div id="error_qty">Please check the box at the bottom of the page to authorize the printing of this card.</div>
     </p>
</div>

<div id="inline_business_mix_item_error" class="modalbox" style="min-width:250px; display:none;" >
     <h2>Error</h2>
     <p>     
      	  <div id="error_qty">Business cards cannot be combined with any other items. Please complete your order or empty your cart and try again.
</div>
     </p>
</div>



<script> 

function delete_shipping_add_confirmation()
{
	var item_id = $("#item_id_buss").attr("delete_id");
	//alert(item_id);
	//window.location = "bceditindex.php?a=del&id="+item_id;
	self.location.href='add_shipping_address.php?id=<?php echo $_GET['id']?>&action=delete';
}


function set_cancel_order_id(order_id,ID)
{
	$("#order_id_cancel").html(order_id);
	$("#cancel_order_id_btn").attr('delete_id',ID);
	
	 $("#inline_order_cancal_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
	
	
	
	
	
}


function set_reorder_order_id(order_id,ID)
{
	$//("#order_id_reorder").html(order_id);
	$("#reorder_order_id_btn").attr('delete_id',order_id);
	
	 $("#inline_order_reorder_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
	
	
	
	
	
}

function cancel_order_confirmation(obj)
{
	var oid = $(obj).attr("delete_id");
	self.location.href='track.php?a=cancel&id='+oid;
}

function reorder_order_confirmation(obj)
{
	var oid = $(obj).attr("delete_id");
	self.location.href='reorder.php?a=reorder&id='+oid;
}



function set_item_delete_id_from_order(OrderRecordID,ItemID,ID)
{
	$("#delete_item_btn").attr('OrderRecordID',OrderRecordID);
	$("#delete_item_btn").attr('ItemID',ItemID);
	$("#delete_item_btn").attr('sequence_id',ID);
	
	 $("#inline_item_delete_from_order_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
	
	
	
	
	
}

function delete_order_item_confirmation(obj)
{
	var orderid = $(obj).attr("OrderRecordID");
	var itemid = $(obj).attr("ItemID");
	var sequnce_id = $(obj).attr("sequence_id");
	
	self.location.href='track.php?a=delitem&id='+orderid+'&itemid='+itemid+'&sequnce_id='+sequnce_id;
	//self.location.href='track.php?a=cancel&id='+oid;
}




function delete_buss_confirmation()
{
	var item_id = $("#item_id_buss").attr("delete_id");
	//alert(item_id);
	window.location = "bceditindex.php?a=del&id="+item_id;
}

function delete_business_card(item_id)
{
	$("#item_id_buss").attr("delete_id",item_id);
	 
	 
	  $("#inline_delete_business_cart_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}

   
function error_item_popup()
{
	  $("#inline_error_item_add_to_cart").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}

function modal_two_button()
{
	  $("#inline_two_button_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}

function modal_error_qty()
{
	  $("#inline_qty_error_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}

function modal_error_qty_business()
{
	  $("#inline_qty_error_modal_business").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}


function modal_order_all_item_detail(orderid)
{

	var str = "";	
	str = "orderid="+orderid;
	var url = "custom_order_file_popup_ajax_modal.php";
	 
	 var request = $.ajax({
						url: url,
						type: "GET",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			$("#all_item_detail_ajax").html(result);
			 
	  $("#inline_order_all_item_detail_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
			
	
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	

}


function modal_order_item_detail(orderid,formid)
{
	$("#item_detail_ajax").html('');
	
	var str = "";	
	str = "orderid="+orderid;
	str += "&formid="+formid;
	var url = "file_popup_ajax_modal_box.php";
	 
	 var request = $.ajax({
						url: url,
						type: "GET",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			$("#item_detail_ajax").html(result);
			 
	  $("#inline_order_item_detail_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
			
	
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
}







function set_item_id_add_new_buss(item_id)
{
	//alert(item_id);
	 
	var qty_kc = $("#qty_kc_buss").val();
	qty_kc = qty_kc.trim();
	
	//alert(  qty_kc );
	//return false;
	  
	if(qty_kc=="")
	{
		 modal_error_qty_business();
		//alert('test');
		
		return false;
	}else{
		$("#business_form").submit();
		
		// submit form using jquery
		
	}
	
}


function modal_error_business_card_mix_item()
{
	  $("#inline_business_mix_item_error").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
}

function close_fancybox_new()
{
		$.fancybox.close();
		
}

// modal_error_business_card_mix_item();
</script>
 
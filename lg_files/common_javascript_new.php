 <script>
 
 
 function ajax_remove_item_from_shop_cart_page_new(item_id)
{ 
	var str = "";	
	str = "item_id="+item_id;
	//str += "&set_key="+set_key;
	str += "&action=remove_to_cart_new";
	 
	//alert(str);
	
	$("#ajax_loader").show();
	
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			 $("#ajax_loader").hide();
			 
			  $("#item_id_"+item_id).remove();
			  
			  
			 if( $('#modal_need_to_close').length )         // use this if you are using id to check
			 {
			 		$.fancybox.close();
					location.href = "index.php";
			 }
			 
			 location.reload();
		
			ajax_cart_total() ;
			ajax_get_header_cart_item();
	
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}

 
 function ajax_cart_total_new()
{ 
	var str = "";	
	 
	str += "action=total_price_calculate_new";
	
	var url = "ajax_cart.php";
	 
	var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			
			result = result.trim();
			
			console.log('kamal'+result);
			
			$(".ajax_total_price").html(''+result);
			
			if(result>0)
			{
				// ajax_cart_total_after_client_discount();
			}
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
	
}
 
function ajax_update_to_cart_by_item_id_new()
{

// cid,item_id
 	var item_id = $("#item_id").val();
	var qty = $("#qty").val();
 
 	 
	
	var item_img =  $('#item_img').val();
	
	var cid = '<?php echo $_SESSION['CID'];?>';
	 
	 
	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&qty="+qty;
	 
	str += "&action=update_cart_by_item_id_new";
	 
	//alert(str);
	
	// $("#ajax_loader").show();
	
	
	if(qty=="")
	{
		
		$("#error_qty").show();	
		return false;
	}else{
		$("#error_qty").hide();	
	}		
	
	
	
	$("#btn_add_to_cart").hide();				
	
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			//$(".ajax_total_price").html(result);
			
			location.href = 'shopping-cart.php';
			
			return false;
			
			
			// $("#ajax_loader").hide();
	  		 $("#btn_add_to_cart").show();
			 
			  $("#checkout_header_link").show();
			 
		
			ajax_cart_total() ;
			ajax_get_header_cart_item();
			
			  $("#msg").show();
			
				  setInterval(function(){  $("#msg").hide(); }, 3000);
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}

function set_item_val_business_card(qty)
{
	$("#qty_kc_buss").val(qty);
	//alert(qty);
}

function set_item_val(item_id,qty,item_title)
{
	$("#qty_kc").val(qty);

	$("#qty_title").val(item_title);
	
	custom_item_for_other_item(item_id) ;
	
	//alert(qty);
}




function set_item_id_add_new(item_id)
{
	//alert(item_id);
	item_id = item_id.trim();
	$("#item_id_kc").val(item_id);
	//var qtyid_item = "kc_qty_item_id_"+item_id;
	//qtyid_item = qtyid_item.trim();
	//console.log(qtyid_item);
	//console.log('qtyid_item='+qtyid_item);
	//var qty_kc = $("#"+qtyid_item).val();
	var qty_kc = $("#qty_kc").val();
	var qty_title = $("#qty_title").val();
	
	console.log('qty='+qty_kc);
	console.log(qty_kc);
	
	
	//alert(qty_kc);
	$("#title_span").html(qty_title);
	$("#qty_span").html(qty_kc);
	
	
	qty_kc = qty_kc.trim();
	  
	if(qty_kc=="")
	{
		 modal_error_qty();
		
		
		//alert('test');
		
		return false;
	}else{
		
		modal_two_button();
		
	}		
	
	
	
	ajax_update_to_cart_by_item_id_new_2();
	
	
	
}


function ajax_update_to_cart_by_item_id_new_2()
{

// cid,item_id
 	var item_id = $("#item_id_kc").val();
	var qty = $("#qty_kc").val();
 
	
	var item_img =  $('#item_img').val();
	
	var cid = '<?php echo $_SESSION['CID'];?>';
	 
	 
	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&qty="+qty;
	 
	str += "&action=update_cart_by_item_id_new";
	 
	//alert(str);
	
	// $("#ajax_loader").show();
	
	
	if(qty=="")
	{
		
		$("#error_qty").show();	
		return false;
	}else{
		$("#error_qty").hide();	
	}		
	
	
	
	$("#btn_add_to_cart").hide();				
	
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			//$(".ajax_total_price").html(result);
			
			//location.href = 'index.php?catid=<?php echo $_GET['catid']?>';
			
			//return false;
			
			
			// $("#ajax_loader").hide();
	  		 $("#btn_add_to_cart").show();
			 
			  $("#checkout_header_link").show();
			 
		
			ajax_cart_total_new() ;
			ajax_get_header_cart_item_new();
			
			  $("#msg").show();
			
				  setInterval(function(){  $("#msg").hide(); }, 3000);
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}


function ajax_get_header_cart_item_new()
{ 
	var str = "";	
	 
	str += "action=header_cart_item_new";
	var url = "ajax_cart_header_new.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			$("#ajax_header_item_cart").html(result);
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
	
}




function ajax_update_cart_new()
{
	var str = "";	
	str += "action=update_cart_new";
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: $("#update_cart_form").serialize()
					});
					
		request.done(function(result) {
			//alert(result);
			
			result = result.trim();
			
			$(".ajax_total_price").html(result);
			
			 ajax_update_cart_item_new() ;
			 ajax_cart_total_new() ;
			 ajax_get_header_cart_item_new();
			
			location.reload();
			
			
			/*
			
			
			
	
			ajax_cart_total_only_items();*/
	
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}

 
function ajax_update_cart_item_new()
{
	var str = "";	
	 
	
	var url = "ajax_shopping_cart_item_new.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			$(".updated_cart_item").html(result);
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}
 
 </script>
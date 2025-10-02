<?php
include_once("common_javascript_new.php");
?>

<script>

function set_item_id_add(item_id)
{
	$("#item_id").val(item_id);
}



/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function myFunction() {
    document.getElementById("myDropdown").classList.toggle("show");
}

function myFunction_color() {
   //alert('testing');
	document.getElementById("myDropdown_color").classList.toggle("show_color");
}

function myFunction_logo() {
    //alert('testing');
	document.getElementById("myDropdown_logo").classList.toggle("show_logo");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {

    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}


window.onclick = function(event) {
  if (!event.target.matches('.dropbtn_color')) {

    var dropdowns = document.getElementsByClassName("dropdown-content_color");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show_color')) {
        openDropdown.classList.remove('show_color');
      }
    }
  }
}


window.onclick = function(event) {
  if (!event.target.matches('.dropbtn_logo')) {

    var dropdowns = document.getElementsByClassName("dropdown-content_logo");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show_logo')) {
        openDropdown.classList.remove('show_logo');
      }
    }
  }
}


function select_color_bk_9_june_2025(color,img_tm)
{
	//alert('test');
	
	//alert(color);
	
	var hide_id = $("#current_id").val();
	var show_id = color ;
	
	color = color.trim();
	
	
	
	if (img_tm!="")
	{
		// var img_tm = $("#"+color).val();
		 
		 
		 $('#default_img').attr('src',img_tm);
		 
		  $('#item_img').val(img_tm);
		 
	} 
	
 
	
	
	
	$("#current_id").val(color);
	
	
	$("#color").val(color);
	document.getElementById("dropbtn_color").value = color ;
	
	document.getElementById("myDropdown_color").classList.toggle("show_color");
	
}


function select_color(color,img_tm)
{
	
	 
	 if(color=="Please select color")
	 {
	 	
		$('#additional_img').show();
		$('#color_img_box').hide();
	 
	 }else{
		   
		    $('#additional_img').hide();
		    $('#color_img_box').show();
		  
	 
	 }
	
	 
	
	//alert(color);
	
	var hide_id = $("#current_id").val();
	var show_id = color ;
	
	color = color.trim();
	
	
	
	if (img_tm!="")
	{
		// var img_tm = $("#"+color).val();
		 
		 
		 $('#default_img').attr('src',img_tm);
		 
		  $('#item_img').val(img_tm);
		  
		  if( $('.default_img_color').length ) 
		  {
		  	
			
			$('.default_img_color').attr('src',img_tm);
		  }
		  
		  
		// alert(img_tm);
		 
	} 
	
	
	$("#current_id").val(color);
	
	
	$("#color").val(color);
	document.getElementById("dropbtn_color").value = color ;
	
	document.getElementById("myDropdown_color").classList.toggle("show_color");
	
	var page_name = "<?php echo basename($_SERVER['PHP_SELF']);?>";
	
	 
	
	
}





function select_logo(logo,img_tm)
{
	//alert('test');
	
	//alert(color);
	
	var hide_id = $("#current_id").val();
	var show_id = logo ;
	
	logo = logo.trim();
	
	
	
	if (img_tm!="")
	{
		// var img_tm = $("#"+color).val();
		$('#default_img_logo').show();
		 $('#default_img_logo').attr('src',img_tm);
		 
	}else{
	
		$('#default_img_logo').hide();
	}
	
 
	
	
	
	$("#current_id").val(logo);
	
	
	$("#artwork_logo").val(logo);
	document.getElementById("dropbtn_logo").value = logo ;
}


function open_main_cat(cat_id)
{
	location.href = 'index.php?catid='+cat_id+'#all';
}



function is_same_shipping_address()
{
	if($("#is_same_billing_address_as_shipping").prop('checked') == true)
     {
		$("#shipping_address_different").hide();
			
			
			
		$("#shipping_country").attr('required','');
		$("#shipping_first_name").attr('disabled',true);
		$("#shipping_last_name").attr('required','');
		$("#shipping_address_1").attr('disabled',true);
		$("#shipping_city").attr('required','');
		$("#shipping_zip").attr('disabled',true);
		$("#shipping_state").attr('required','');
		$("#shipping_email").attr('disabled',true);
		$("#shipping_phone").attr('required','');
			
		
						
	}else{
		
		$("#shipping_address_different").show();
	
		
		$("#shipping_country").attr('required','required');
		$("#shipping_first_name").attr('disabled',false);
		$("#shipping_last_name").attr('required','required');
		$("#shipping_address_1").attr('disabled',false);
		$("#shipping_city").attr('required','required');
		$("#shipping_zip").attr('disabled',false);
		$("#shipping_state").attr('required','required');
		$("#shipping_email").attr('disabled',false);
		$("#shipping_phone").attr('disabled',false);
		
		
		
		
	}
	

}



function add_increase(qty_id)
{
	var plus_val  = 0;
	plus_val = $("#"+qty_id).val();
	plus_val = parseInt(plus_val) ;
	plus_val = plus_val + 1 ; 
	
	//alert(qty_id);
	
	$("#"+qty_id).val(plus_val);
	
	$("#item_id_val").val(qty_id);
	 
	console.log(qty_id);
	
	 // Call update cart after a small delay
    setTimeout(function() {
        ajax_update_cart();
    }, 100);
	
	
	
	
	
}

function minus_descrease(input_name)
{
	var minus_val  = 0;
	minus_val = $("#"+input_name).val();
	minus_val = parseInt(minus_val) ;
	
	if(minus_val>0)
	{
		minus_val = minus_val - 1 ; 
	}
	
	$("#"+input_name).val(minus_val);
	
	console.log(input_name);
	
	 // Call update cart after a small delay
    setTimeout(function() {
        ajax_update_cart();
    }, 100);
	
	
}


function ajax_modal_box_add_cart_item(item_id)
{
	 var str = "";	
	 str += "action=add_to_cart_modal";
	 str += "&item_id="+item_id;
	
	var url = "ajax_add_cart_modal.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			$("#inline").html(result);
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}


function ajax_modal_box_add_grid_cart_item(item_id)
{
	 var str = "";	
	 str += "action=add_to_cart_modal";
	 str += "&item_id="+item_id;
	
	var url = "ajax_add_cart_grid_modal.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			$("#inline_grid_add_to_card").html(result);
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}





function ajax_update_cart_item()
{
	var str = "";	
	 
	
	var url = "ajax_shopping_cart_item.php";
	 
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

// ajax_submit_billing_details billing_details

function ajax_submit_billing_details()
{
	var str = "";	
	str += "action=billing_details";
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: $("#update_cart_form").serialize()
					});
					
		request.done(function(result) {
			//alert(result);
			
			location.href="checkout-delivery.php";
			
			//$(".ajax_total_price").html(result);
			
			
			/*ajax_update_cart_item() ;
			
			ajax_cart_total() ;
			ajax_get_header_cart_item();
	*/
	
	
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}




function ajax_update_cart()
{
    var str = "";	
    str += "action=update_cart";
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
            
            // Call functions sequentially to avoid race conditions
            ajax_update_cart_item().done(function() {
                ajax_cart_total().done(function() {
                    ajax_get_header_cart_item();
                    ajax_cart_total_only_items();
                });
            });
            
        });
        
        request.fail(function(jqXHR, textStatus) {
            //alert( "Request failed: " + textStatus );
            return false;
        });
    
    return false;
}


function ajax_update_cart_bk_31_july_2025()
{
	var str = "";	
	str += "action=update_cart";
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
			
			 
			
			ajax_update_cart_item() ;
			
			ajax_cart_total() ;
			ajax_get_header_cart_item();
	
			ajax_cart_total_only_items();
	
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;

}

function ajax_cart_total()
{ 
	var str = "";	
	 
	str += "action=total_price_calculate";
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
				ajax_cart_total_after_client_discount();
			}
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
	
}

function ajax_cart_total_after_client_discount()
{

 
/*var new_html = '<span class="product-price-discount">$<span id="ajax_p">26.00</span><i>$<span id="ajax_sale_price">23.40</span></i></span>';
$(".order_total_td").html(new_html);*/
 
	var str = "";	
	 
	str += "action=client_discount_total_price_calculate";
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
			
			if(result!="not_discount")
			{
			
				$(".order_total_td").html(result);
			
			}

			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
	
}



function ajax_cart_total_only_items()
{ 
	var str = "";	
	 
	str += "action=cart_total_calculate";
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			//alert(result);
			
			result = result.trim();
			
			$(".ajax_cart_total").html(result);
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	return false;
	
}



function ajax_add_to_cart(cid,item_id)
{ 
	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&action=add_to_cart";
	 
	//alert(str);
	
	//$("#ajax_loader").show();
	
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			
			result = result.trim();
			
			$(".ajax_total_price").html(result);
			
			// $("#ajax_loader").hide();
		
			ajax_cart_total() ;
			ajax_get_header_cart_item();
			
			ajax_modal_box_add_cart_item(item_id);
			
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}

 


function ajax_get_price_by_size(cid,item_id,size)
{ 
	
	//alert(size); 
	
	$("#size_temp").val(size);
	document.getElementById("dropbtn").value = size ;
	
	$("#myDropdown").removeClass('show');
	
	
	// alert(size);
	
	var qty = $("#qty").val();
	var is_apply_sale_price = $("#is_sale_price").val();
	
	

	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&size="+size;
	str += "&action=price_by_size";
	 
	$("#ajax_loader").show();
	
	is_apply_sale_price = is_apply_sale_price.trim();
				
			  if(is_apply_sale_price==1)
			  {
			  	 ajax_get_sale_price(cid,item_id,size) ;
			  }
					
	
	 var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			
			result = result.trim();
			
			$("#ajax_p").html(result);
			
			  $("#ajax_loader").hide();
			  
			  	
			  
			  
			  
			  
			return false;
			 
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	
}

 
function ajax_get_sale_price(cid,item_id,size)
{ 
	
	//alert(size); 
	
	$("#size_temp").val(size);
	document.getElementById("dropbtn").value = size ;
	
	$("#myDropdown").removeClass('show');
	
	
	// alert(size);
	
	var qty = $("#qty").val();
	var is_apply_sale_price = $("#is_sale_price").val();
	//alert(is_apply_sale_price);
	

	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&size="+size;
	str += "&action=sale_price_by_size";
	 
	$("#ajax_loader").show();
					
	
	 var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			
			result = result.trim();
			
			$("#ajax_sale_price").html(result);
			
			
			 
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	
}

function ajax_update_to_cart_by_item_id(cid,item_id)
{ 
	var qty = $("#qty").val();
 
 	var drop_down_val = '';
	var drop_down_val_color = '';
	var drop_down_val_logo = '';
	var special_comment = '';
	var is_canvas_item = '';
	var canvas_download_url = '';
	
	var waist_size = '';
	var length_inches = '';  
	
	if( $('#waist_size').length )         // use this if you are using id to check
	{
		var waist_size = $('#waist_size').val();	
	}
	
	
	if( $('#length_inches').length )         // use this if you are using id to check
	{
		var length_inches = $('#length_inches').val();	
	}
	
	
	   
	
	if( $('#special_comment').length )         // use this if you are using id to check
	{
		var special_comment = $('#special_comment').val();	
	}
		
	var is_gift_card_item = '';
	var gift_price = '';
	var item_price_type = '';
	var custom_gift_amount = '';
	var custom_gift_email = '';
	var custom_gift_from = '';
	var custom_gift_message = ''; 
	var custom_gift_delivery_date = '';
	
	var cappyhour_logo = "";
	var cappyhour_tonal = "";
	if( $('#cappyhour').length )         // use this if you are using id to check
	{
		cappyhour_logo = $('#cappyhour').val();
		cappyhour_tonal = "no";
		//cappyhour_tonal = $('#tonal').val();
		if( $('#tonal').length )  
	   { 
		
			if($("#tonal").prop("checked") == true)
			{
				cappyhour_tonal = "yes";
			}
		
		}
		
		
	}
	
	
	if( $('#is_gift_card_item').length )         // use this if you are using id to check
	{
		is_gift_card_item = $('#is_gift_card_item').val();
		gift_price = $('#gift_price').val();
		item_price_type = $('#item_price_type').val();
		custom_gift_amount = $('#custom_gift_amount').val();
		custom_gift_email = $('#custom_gift_email').val();
		custom_gift_from = $('#custom_gift_from').val();
		custom_gift_message = $('#custom_gift_message').val();
		custom_gift_delivery_date = $('#custom_gift_delivery_date').val();
		
		console.log("is_gift_card_item:"+is_gift_card_item);
		console.log("gift_price:"+gift_price);
		console.log("item_price_type:"+item_price_type);
		console.log("custom_gift_amount:"+custom_gift_amount);
		console.log("custom_gift_email:"+custom_gift_email);
		console.log("custom_gift_from:"+custom_gift_from);		
		console.log("custom_gift_message:"+custom_gift_message);
		console.log("custom_gift_delivery_date:"+custom_gift_delivery_date);
		//alert("Yes");
		//return false;
		
		 gift_price = gift_price.trim();
		 custom_gift_email = custom_gift_email.trim();
		 custom_gift_from = custom_gift_from.trim();
		 custom_gift_message = custom_gift_message.trim();
		 item_price_type = item_price_type.trim();
			
			if(item_price_type=="")
			{
				
				alert("Please select gift price");	
				
			
		
				
				
				return false;
			}
			
			if(custom_gift_email=="")
			{
					$("#inline_custom_message_modal").html("Please enter to email address");
				  $("#inline_custom_message_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
				
				
				//alert("Please enter to email address");	
				return false;
			}
			
			if(custom_gift_from=="")
			{
				//alert("Please enter from name");	
				
				$("#inline_custom_message_modal").html("Please enter from name");
				  $("#inline_custom_message_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
				
				
				
				return false;
			}
			
			if(custom_gift_message=="")
			{
				//alert("Please enter message");
				
				$("#inline_custom_message_modal").html("Please enter message");
				  $("#inline_custom_message_modal").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
				
				
				
				
				return false;	
			}
		
		
	
		
	}
	
	if( $('#is_canvas_item').length )         // use this if you are using id to check
	{
		var is_canvas_item = $('#is_canvas_item').val();	
	}
	
	if( $('#download_url').length )         // use this if you are using id to check
	{
		var canvas_download_url = $('#download_url').val();	
	}
	
	
	
	//alert(special_comment);
	//return false;
	
	if( $('#size_temp').length )         // use this if you are using id to check
	{
		//var drop_down_val = $('#select-size_temp :selected').text();
		
		var drop_down_val = $('#size_temp').val();		
		 drop_down_val = drop_down_val.trim();
		 
		 
		 var validation = $('#validation').val();
		 validation = validation.trim();
		
			//if(drop_down_val=='Please select size')
			if(drop_down_val==validation)
			{
				// $("#small-dialog").modal('show');
				
				  $("#inline_size_message").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		
			
		 
			
				//alert("Please select size.");
				
				return false;
			}
			
			if(drop_down_val=='')
			{
				// $("#small-dialog").modal('show');
				
				  $("#inline_size_message").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		
			
		 
			
				//alert("Please select size.");
				
				return false;
			}
		
		
	}
 
 	if( $('#color').length )         // use this if you are using id to check
	{
	
	
	
		var drop_down_val_color = $('#color').val();
		
		 drop_down_val_color = drop_down_val_color.trim();
		 
			if(drop_down_val_color=='Please select color')
			{
				// $("#small-dialog").modal('show');
				
				 $("#inline_color_message").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		 
			
				//alert("Please select size.");
				
				return false;
			}
		
		
	}
 
 
 		var custom_name = '';
	var custom_name_price = '';
	var custom_number = '';
	var custom_number_price = '';
	
	var is_name_validation = '';
	var is_number_validation   = '';
	
	 if( $('#custom_name').length )  
	 {
	 	 custom_name = $("#custom_name").val();
		 custom_name_price = $("#custom_name_price").val();
		 
		 is_name_validation = $("#is_name_validation").val(); 
		 
		 if(is_name_validation=="required" && custom_name=="" )
		 {
		 
		 	 $("#inline_name_validation").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		 	 
			return false;
		 }
		 
		 
		 
	 }
	 
	 if( $('#custom_number').length )  
	 {
	 	 custom_number = $("#custom_number").val();
		 custom_number_price = $("#custom_number_price").val();
		 
		 is_number_validation = $("#is_number_validation").val(); 
		 
		 
		 
		 if(is_number_validation=="required" && custom_number=="" )
		 {
		 
		 	 $("#inline_number_validation").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		 	 
			return false;
		 }
		 
		 
	 }
	 
 
	 
	 if( $('#artwork_logo').length )         // use this if you are using id to check
	{
	
		var drop_down_val_logo = $('#artwork_logo').val();
		
		//alert(drop_down_val_color);
		//alert('hello');
		//return false ;
		
		// alert(drop_down_val_logo);
		
		 drop_down_val_logo = drop_down_val_logo.trim();
		 
		
		// return false;
		 
			if(drop_down_val_logo=='Please select logo')
			{
				// $("#small-dialog").modal('show');
				
				 $("#inline_logo").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		 
			
				//alert("Please select size.");
				
				return false;
			}
		
		
	}
	
	
	
	
	var item_img =  $('#item_img').val();
	
	
	 
	 
	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&qty="+qty;
	str += "&size="+drop_down_val;
	str += "&color="+drop_down_val_color;
	str += "&artwork_logo="+drop_down_val_logo;
	
	str += "&custom_name="+custom_name;
	str += "&custom_name_price="+custom_name_price;
	str += "&custom_number="+custom_number;
	str += "&custom_number_price="+custom_number_price;
	str += "&item_img="+item_img;
	str += "&special_comment="+special_comment;
	
	str += "&canvas_download_url="+canvas_download_url;
	str += "&is_canvas_item="+is_canvas_item;
	
	
	
	str += "&is_gift_card_item="+is_gift_card_item;
	str += "&gift_price="+gift_price;
	str += "&item_price_type="+item_price_type;
	str += "&custom_gift_amount="+custom_gift_amount;
	str += "&custom_gift_email="+custom_gift_email;
	str += "&custom_gift_from="+custom_gift_from;
	str += "&custom_gift_message="+custom_gift_message;
	str += "&custom_gift_delivery_date="+custom_gift_delivery_date;
	
	str += "&cappyhour_logo="+cappyhour_logo;
	str += "&cappyhour_tonal="+cappyhour_tonal;
 
	str += "&waist_size="+waist_size;
	str += "&length_inches="+length_inches;
	
	str += "&action=update_cart_by_item_id";
	 
	//alert(str);
	
	 $("#ajax_loader").show();
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
			
			
			 $("#ajax_loader").hide();
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




function ajax_update_to_cart_by_item_id_with_size(cid,item_id)
{ 
	var qty = $("#qty").val();
	
	var item_id = $("#current_item_id").val();
	
	// alert(	item_id);	
	
	var drop_down_val = '';
	
	/*if( $('#select-size_temp').length )         
	{
		var drop_down_val = $('#select-size_temp :selected').text();
	}*/
	
	if( $('#size_temp').length )         
	{
		var drop_down_val = $('#size_temp').val();
	}
	
	alert(drop_down_val);
	
	return false;
	
	
	drop_down_val = drop_down_val.trim();
		
			if(drop_down_val=='Please Select size')
			{
				// $("#small-dialog").modal('show');
				
				/*  $("#inline_size_message").fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true // remove the trailing comma!!
        }).click();
		*/
			
		 
			
				//alert("Please select size.");
				
				return false;
			}
	
	var str = "";	
	str = "item_id="+item_id;
	str += "&cid="+cid;
	str += "&qty="+qty;
	str += "&size="+drop_down_val;
	str += "&action=update_cart_by_item_id_with_size";
	 
	 //alert(str);
	
	
	var url = "ajax_cart.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			
			result = result.trim();
			
			$("#ajax_p").html(result);
			
			//location.href = 'shopping-cart.php';
			
			
			
			return false;
			
			
			 $("#ajax_loader").hide();
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



function ajax_remove_item(item_id)
{ 
	var str = "";	
	str = "item_id="+item_id;
	str += "&action=remove_to_cart";
	 
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
		
			ajax_cart_total() ;
			ajax_get_header_cart_item();
			 
			ajax_cart_total_only_items();
	
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}



function ajax_remove_item_from_shop_cart_page(item_id,set_key)
{ 
	var str = "";	
	str = "item_id="+item_id;
	str += "&set_key="+set_key;
	str += "&action=remove_to_cart";
	 
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



function ajax_get_item_list(cat_id,cid)
{ 
	var str = "";	
	str = "cat_id="+cat_id;
	str += "&cid="+cid;
	 
	//alert(str);
	
	var url = "ajax_item_list.php";
	 
	 var request = $.ajax({
						url: url,
						type: "POST",
						data: str
					});
					
		request.done(function(result) {
			
			//alert(result);
			result = result.trim();
			
			
			$("#ajax_item_list").html(result);
			
			 
		
			
		});
		
		request.fail(function(jqXHR, textStatus) {
			//alert( "Request failed: " + textStatus );
			return false;
		});
	
	
	
	
	return false;
	
	
	
	
}

function ajax_get_header_cart_item()
{ 
	var str = "";	
	 
	str += "action=header_cart_item";
	var url = "ajax_cart_header.php";
	 
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



$(document).ready(function(){
    
  
	
	
	$('#select-size_temp').on('change', 'select', function() {
  //your stuff 
}).click(function(){

   	var drop_down_val = $('#select-size_temp :selected').text();
	 
	 
	 ajax_update_to_cart_by_item_id_with_size('<?php echo $CID;?>','<?php echo $item_detail['ID'];?>',drop_down_val);
	 
	//console.log(drop_down_val);
   
});
	 
	 
	 
 
 <?php
$page_name = basename($_SERVER['PHP_SELF']) ;
if( $page_name !="test.php" ) 
{

?>
	//	ajax_cart_total_new();
	//	ajax_get_header_cart_item_new();
 		ajax_cart_total() ;
		ajax_get_header_cart_item();


<?php
}
?>

			
<?php
$page_name = basename($_SERVER['PHP_SELF']) ;
if( $page_name =="checkout-delivery.php" ) 
{


	if(isset($_POST["shipping_country"]) and $_POST["shipping_country"]!="")
	{

?>

 $('#shipping_country').val('<?php echo $_POST["shipping_country"];?>');
 
 
 <?php
 }
 ?>
 
  $('#shipping_state').val('<?php echo $_POST["shipping_state"];?>');
 
  
  
 <?php
 }
 ?> 

 
	 
  
  });
  
  
  
  $(document).ready(function(){

	
	$(".fa fa-caret-right").remove();
});


</script>
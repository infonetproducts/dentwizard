<!--<a class="popup-with-zoom-anim button color" href="#small-dialog" >Open</a>-->
 
<!--<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
     <h2>Add to Cart</h2>
    
     <p>
     
      <div id="error_qty" style="color:red; display:none;">Please enter quantity</div>
                       

    
     <div class="form-group" align="center" id="div_btn_modal" style="display:block;">
     
     <a onClick="closePopup();" href="javascript:void(0);"  item_id="<?php echo $item_detail['ID'];?>" class="button color Close">Continue Shopping</a>
     
       <a onclick="self.location.href='shopping-cart.php'"  href="javascript:void(0);"  item_id="<?php echo $item_detail['ID'];?>" class="button color btn_modal ">Proceed to Checkout</a>
       
                                                
      
       
       
    </div>
     
     
     </p>
     
     
     
     
     
</div>
-->

<script>

function closePopup() 
{
  $.magnificPopup.close();
}

</script>

<input style="background-color:#0099CC; color:red;" type="hidden" id="item_id_kc" value="" />
<input   style="background-color: #66FF99; color:red;" type="hidden" class="form-control" id="qty_kc" name="qty_kc">
 <input   style="background-color: #66FF99; color:red;" type="hidden" class="form-control" id="qty_title" name="qty_title">

     
<?php
include_once("include/start.php");
$orderid = $_GET['orderid'];


 

?>


<?php
$sql_order_items = "select * from OrderItems where OrderRecordID='$orderid' and is_custom_order_item = 'Y' ";
$rs_items = mysql_query($sql_order_items);

if(mysql_num_rows($rs_items)>0)
{
	while($row_item = mysql_fetch_assoc($rs_items))
	{

$_GET['formid'] = $row_item['FormID'];


$formid = $_GET['formid'];

$sql = "select Description,MinQTY,MaxQTY,UserID,Price , ID,item_type , item_price_type , price_multi , require_file_upload from Items where FormID='$formid' ";
list($itemdesc,$min,$max,$u,$price,$item_id,$item_type,$item_price_type,$price_multi , $require_file_upload)=@mysql_fetch_row(mysql_query($sql));


	$price  =  how_many_decimal_place_display_new($price,1);

?>

<div align="center"  style="background-color:#FFFFFF">
  <table class="cart-table responsive-table"  width="650" border="2" align="center" cellpadding="6" cellspacing="0" bordercolor="#e0e0e0">
    <tr>
      <td bgcolor="#FFFFFF"><table width="100%" border="0" align="center" cellpadding="2" cellspacing="4">
          <tr>
            <td><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Item #:</strong> <?php echo $formid;?></font></div></td>
          </tr>
          
           <tr>
            <td><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Price :</strong> $<?php echo $price;?></font></div></td>
          </tr>
          
          
           <tr>
            <td><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Quantity :</strong> <?php echo $row_item['Quantity'];?></font></div></td>
          </tr>
          
          
          
          <tr>
            <td><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Description:</strong> <?php echo $itemdesc;?></font></div></td>
          </tr>
          <tr>
            <td><div align="left"></div></td>
          </tr>
          <tr>
            <td><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>File(s) for this item:</strong></font></div></td>
          </tr>
          <tr>
            <td>   
                <div align="left">
                  <ul>
                  
                  
                  <?php
				$sql_file_link = "select type , filename , link , resolution from required_file_link     where orderid='$orderid' and formid='$formid' ";
				$rs_link = mysql_query($sql_file_link);
				while(list($type,$filename,$link , $resolution )=@mysql_fetch_row($rs_link))
				{
				
					
				  ?>
                  
                  
					<?php
                    
                    if($type=="file")
                    {
                    ?>
                    
                   	 <li><font size="2" face="Arial, Helvetica, sans-serif"><a target="_blank" href="../admin/userfile/<?php echo $filename;?>"><?php echo $filename;?></a> (<?php echo $resolution;?>) </font></li>
                    
                    <?php
                    }
                    ?>
                    
                    <?php
                    
                    if($type=="link")
                    {
                    ?>
                    
                   	 <li><font size="2" face="Arial, Helvetica, sans-serif"><a target="_blank" href="<?php echo $link;?>"><?php echo $link;?></a>  </font></li>
                    
                    <?php
                    }
                    ?>
                 
                 
                 <?php
				 
				}
				 ?>  
                    
                  </ul>
            </div></td>
          </tr>
          
            <?php
			
			$sql_file_note = "select notes , item_due_date  from required_note_file    where orderid='$orderid' and  formid='$formid' ";
			$rs_link = mysql_query($sql_file_note);
			list($notes , $item_due_date )=@mysql_fetch_row($rs_link) ;
            ?>

          <tr>
            <td valign="top"><div align="left"></div></td>
          </tr>
          
          <!-- <tr>
            <td valign="top"><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Item Due Date: </strong> <?php
			
			if($item_due_date!="" and $item_due_date!="0000-00-00")
			{
			 
			 echo date('m/d/Y',strtotime($item_due_date));
			 
			}
			 
			 ?> </font></div></td>
          </tr>-->
         
          <tr>
            <td valign="top"><div align="left"><font size="2" face="Arial, Helvetica, sans-serif"><strong>Additional Notes / Order
            Instructions: </strong></font></div></td>
          </tr>
          <tr>
            <td valign="top"><div align="left"><font size="2" face="Arial, Helvetica, sans-serif">
            
            
          
            
           <?php echo $notes;?>
            
            
            </font></div></td>
          </tr>
      </table></td>
    </tr>
  </table>
</div>
<?php
	}
}
?>



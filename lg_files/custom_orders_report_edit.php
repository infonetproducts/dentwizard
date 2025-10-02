<?php

///////////////////////////////////////////////////////

// editForm - edit an Order entry.
function editForm($id){
	global $billcodes,$orderstatus,$VendorID,$CID,$pdffolder;
	if($id) {
		$order = @mysql_fetch_assoc(mysql_query("select * from Orders where ID='$id'"));
		if($order['CID'] != $CID && !$_SESSION['sysadmin'] && !$VendorID) die("Error: Parameter mismatch\n");
	}
	$is_custom_order =  $order['is_custom_order'];
	
	if(!$order['BillTo'] && $order['Status']=='new') $order['BillTo'] = <<<EOM
$companyname

EOM;
	if(!isset($_REQUEST['noauto']) && $order['Status']=='new'){
		mysql_query("update Orders set Status='inprocess' where ID='$id'");
		$order['Status'] = 'inprocess';
	}
	if($VendorID && $id)	mysql_query("replace OrderVendorView set OrderID='$id', VendorID='$VendorID'");
	echo <<<EOM
<h3 align="center">Order Details</h3>
<input type="hidden" name="a" value="save" />
<input type="hidden" name="id" value="$id" />
<style>
.grid th { text-align:right; font-weight:normal; border-bottom:1px solid #bbbbbb; }
.grid td { border-bottom: 1px solid #bbbbbb; }
</style>
<script>
altsubmit=function(act){
	if(!confirm('Are you sure you want to '+act+' this order?')) return;
	document.oo.a.value=act;
	document.oo.submit();
}
</script>
EOM;
	if($order['Status'] == 'approvalreq'){ // show approve/deny buttons
		#echo "HEY THERE!";
		echo <<<EOM
<div align="center">
<input type="button" value="APPROVE ORDER" onclick="altsubmit('approve')" />
<input type="button" value="DENY ORDER" onclick="altsubmit('deny')" />
<br />
<textarea rows="2" cols="60" name="approvalmsg"></textarea>
</div>
EOM;
	}
    ?>

<p class="form-row form-row-wide">
    <label for="username"><input type="button" onclick="self.location.href='custom_orders_report.php'" value="Back to List" /></label>
   
</p>


<p class="form-row form-row-wide">
    <label for="username"><strong>OrderID</strong></label>
    <?php echo $order[OrderID];?>
</p>

<?php if($order['OrderDate']) $order['OrderDate'] = date("m/d/Y", strtotime($order['OrderDate']));?>

<p class="form-row form-row-wide">
    <label for="username"><strong>OrderDate</strong></label>
    <?php echo $order[OrderDate];?>
</p>

<?php
if($order[custom_desc]!="")
{
?>
<p class="form-row form-row-wide">
    <label for="username"><strong>Order Description</strong></label>
    <input type="text" size="40" name="Email" value="<?php echo $order[custom_desc];?>" />
</p>    
<?php
}
?>

<?php
if($order[item_due_date]!="0000-00-00")
{
	$order[item_due_date] = date("m/d/Y",strtotime($order[item_due_date]));
?>
<p class="form-row form-row-wide">
    <label for="username"><strong>Item Due Date</strong></label>
   <input type="text" size="40" name="Email" value="<?php echo $order[item_due_date];?>" />
</p>    
<?php
}
?>

<p class="form-row form-row-wide">
    <label for="username"><strong>Email</strong></label>
   <input type="text" size="40" name="Email" value="<?php echo $order[Email];?>" />
</p>  

 <p class="form-row form-row-wide">
    <label for="username"><strong>Name</strong></label>
   <input type="text" size="40" name="Name" value="<?php echo $order[Name];?>" />
</p>

<?php

	$depts = depts($order['Dept']);
?>  

 <p class="form-row form-row-wide">
    <label for="username"><strong>Ship To Name</strong></label>
   <input type="text" size="40" name="ShipToName" value="<?php echo $order[ShipToName];?>" />
</p> 

 <p class="form-row form-row-wide">
    <label for="username"><strong>Ship To Company</strong></label>
   <input type="text" size="40" name="Company" value="<?php echo $order[Company];?>" />
</p> 


 <p class="form-row form-row-wide">
    <label for="username"><strong>Address 1</strong></label>
  <input type="text" size="50" name="Address1" value="<?php echo $order[Address1];?>" />
</p> 

 <p class="form-row form-row-wide">
    <label for="username"><strong>Address 2</strong></label>
   <input type="text" size="50" name="Address2" value="<?php echo $order[Address2];?>" />
</p> 

 <p class="form-row form-row-wide">
    <label for="username"><strong>City</strong></label>
   <input type="text" size="40" name="City" value="<?php echo $order[City];?>" />
</p> 

 <p class="form-row form-row-wide">
    <label for="username"><strong>State/Province/Region</strong></label>
   <input type="text" size="2" name="State" value="<?php echo $order[State];?>" />
</p> 

 <p class="form-row form-row-wide">
    <label for="username"><strong>Zip/Postal Code</strong></label>
  <input type="text" size="15" name="Zip" value="<?php echo $order[Zip];?>" />
</p>

 <p class="form-row form-row-wide">
    <label for="username"><strong>Country<?php echo $star;?></strong></label>
	 <select name="country" id="country">
        <option  value="Afghanistan">Afghanistan</option>
        <option  value="American Samoa">American Samoa</option>
        <option  value="Andorra">Andorra</option>
        <option  value="Angola">Angola</option>
        <option  value="Anguilla">Anguilla</option>
        <option  value="Antarctica">Antarctica</option>
        <option  value="Antigua And Barbuda">Antigua And Barbuda</option>
        <option  value="Argentina">Argentina</option>
        <option  value="Armenia">Armenia</option>
        <option  value="Aruba">Aruba</option>
        <option  value="Australia">Australia</option>
        <option  value="Austria">Austria</option>
        <option  value="Azerbaijan">Azerbaijan</option>
        <option  value="Bahamas">Bahamas</option>
        <option  value="Bahrain">Bahrain</option>
        <option  value="Bangladesh">Bangladesh</option>
        <option  value="Barbados">Barbados</option>
        <option  value="Belarus">Belarus</option>
        <option  value="Belgium">Belgium</option>
        <option  value="Belize">Belize</option>
        <option  value="Benin">Benin</option>
        <option  value="Bhutan">Bhutan</option>
        <option  value="Bolivia">Bolivia</option>
        <option  value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
        <option  value="Botswana">Botswana</option>
        <option  value="Bouvet Island">Bouvet Island</option>
        <option  value="Brazil">Brazil</option>
        <option  value="British Ocean Territory">British Ocean Territory</option>
        <option  value="Brunei">Brunei</option>
        <option  value="Bulgaria">Bulgaria</option>
        <option  value="Burkina Faso">Burkina Faso</option>
        <option  value="Burundi">Burundi</option>
        <option  value="Cambodia">Cambodia</option>
        <option  value="Cameroon">Cameroon</option>
        <option  value="Canada">Canada</option>
        <option  value="Cape Verde">Cape Verde</option>
        <option  value="Cayman Islands">Cayman Islands</option>
        <option  value="Central African Republic">Central African Republic</option>
        <option  value="Chad">Chad</option>
        <option  value="Channel Islands">Channel Islands</option>
        <option  value="Chile">Chile</option>
        <option  value="China">China</option>
        <option  value="hristmas Island">Christmas Island</option>
        <option  value="Cocos (Keeling)Islands">Cocos (Keeling)Islands</option>
        <option  value="Colombia">Colombia</option>
        <option  value="Comoros">Comoros</option>
        <option  value="Congo">Congo</option>
        <option  value="Cook Islands">Cook Islands</option>
        <option  value="Costa Rica">Costa Rica</option>
        <option  value="Croatia">Croatia</option>
        <option  value="Cuba">Cuba</option>
        <option  value="Cyprus">Cyprus</option>
        <option  value="Czech Republic">Czech Republic</option>
        <option  value="Dem Rep of Congo(Zaire)">Dem Rep of Congo(Zaire)</option>
        <option  value="Denmark">Denmark</option>
        <option  value="Djibouti">Djibouti</option>
        <option  value="Dominica">Dominica</option>
        <option  value="Dominican Republic">Dominican Republic</option>
        <option  value="East Timor">East Timor</option>
        <option  value="Ecuador">Ecuador</option>
        <option  value="Egypt">Egypt</option>
        <option  value="El Salvador">El Salvador</option>
        <option  value="England">England</option>
        <option  value="Equatorial Guinea">Equatorial Guinea</option>
        <option  value="Eritrea">Eritrea</option>
        <option  value="Estonia">Estonia</option>
        <option  value="Ethiopia">Ethiopia</option>
        <option  value="Falkland Islands">Falkland Islands</option>
        <option  value="Faroe Islands">Faroe Islands</option>
        <option  value="Fiji">Fiji</option>
        <option  value="Finland">Finland</option>
        <option  value="France">France</option>
        <option  value="French Guiana">French Guiana</option>
        <option  value="French Polynesia">French Polynesia</option>
        <option  value="French Southern Territories">French Southern Territories</option>
        <option  value="Gabon">Gabon</option>
        <option  value="Gambia">Gambia</option>
        <option  value="Georgia">Georgia</option>
        <option  value="Germany">Germany</option>
        <option  value="Ghana">Ghana</option>
        <option  value="Gibraltar">Gibraltar</option>
        <option  value="Greece">Greece</option>
        <option  value="Greenland">Greenland</option>
        <option  value="Grenada">Grenada</option>
        <option  value="GP">Guadeloupe</option>
        <option  value="Guadeloupe">Guam</option>
        <option  value="Guatemala">Guatemala</option>
        <option  value="Guinea">Guinea</option>
        <option  value="Guinea-Bissau">Guinea-Bissau</option>
        <option  value="Guyana">Guyana</option>
        <option  value="Haiti">Haiti</option>
        <option  value="Heard and McDonald Islands">Heard and McDonald Islands</option>
        <option  value="Honduras">Honduras</option>
        <option  value="Hong Kong">Hong Kong</option>
        <option  value="Hungary">Hungary</option>
        <option  value="Iceland">Iceland</option>
        <option  value="India">India</option>
        <option  value="Indonesia">Indonesia</option>
        <option  value="Iran">Iran</option>
        <option  value="Iraq">Iraq</option>
        <option  value="Ireland">Ireland</option>
        <option  value="Isle of	Man">Isle of	Man</option>
        <option  value="Israel">Israel</option>
        <option  value="Italy">Italy</option>
        <option  value="Ivory Coas">Ivory Coast</option>
        <option  value="Jamaica">Jamaica</option>
        <option  value="Japan">Japan</option>
        <option  value="Jordan">Jordan</option>
        <option  value="Kazakhstan">Kazakhstan</option>
        <option  value="Kenya">Kenya</option>
        <option  value="Kiribati">Kiribati</option>
        <option  value="Korea">Korea</option>
        <option  value="Korea (D.P.R.)">Korea (D.P.R.)</option>
        <option  value="Kuwait">Kuwait</option>
        <option  value="Kyrgyzstan">Kyrgyzstan</option>
        <option  value="Lao">Lao</option>
        <option  value="Latvia">Latvia</option>
        <option  value="Lebanon">Lebanon</option>
        <option  value="Lesotho">Lesotho</option>
        <option  value="Liberia">Liberia</option>
        <option  value="Libya">Libya</option>
        <option  value="Liechtenstein">Liechtenstein</option>
        <option  value="Lithuania">Lithuania</option>
        <option  value="Luxembourg">Luxembourg</option>
        <option  value="Macedonia">Macedonia</option>
        <option  value="Madagascar">Madagascar</option>
        <option  value="Malawi">Malawi</option>
        <option  value="Malaysia">Malaysia</option>
        <option  value="Maldives">Maldives</option>
        <option  value="Mali">Mali</option>
        <option  value="Malta">Malta</option>
        <option  value="Marshall Islands">Marshall Islands</option>
        <option  value="Martinique">Martinique</option>
        <option  value="Mauritania">Mauritania</option>
        <option  value="Mauritius">Mauritius</option>
        <option  value="Mayotte">Mayotte</option>
        <option  value="Mexico">Mexico</option>
        <option  value="Micronesia">Micronesia</option>
        <option  value="Moldova">Moldova</option>
        <option  value="Monaco">Monaco</option>
        <option  value="Mongolia">Mongolia</option>
        <option  value="Montserrat">Montserrat</option>
        <option  value="Morocco">Morocco</option>
        <option  value="Mozambique">Mozambique</option>
        <option  value="Myanmar">Myanmar</option>
        <option  value="Namibia">Namibia</option>
        <option  value="Nauru">Nauru</option>
        <option  value="Nepal">Nepal</option>
        <option  value="Netherlands">Netherlands</option>
        <option  value="Netherlands Antilles">Netherlands Antilles</option>
        <option  value="New Caledonia">New Caledonia</option>
        <option  value="New Zealand">New Zealand</option>
        <option  value="Nicaragua">Nicaragua</option>
        <option  value="Niger">Niger</option>
        <option  value="Nigeria">Nigeria</option>
        <option  value="Niue">Niue</option>
        <option  value="Norfolk Island">Norfolk Island</option>
        <option  value="Northern Ireland">Northern Ireland</option>
        <option  value="Northern Mariana Islands">Northern Mariana Islands</option>
        <option  value="Norway">Norway</option>
        <option  value="Oman">Oman</option>
        <option  value="Pakistan">Pakistan</option>
        <option  value="Palau">Palau</option>
        <option  value="Palestinian Territory,Occupied">Palestinian Territory,Occupied</option>
        <option  value="Panama">Panama</option>
        <option  value="Papua new Guinea">Papua new Guinea</option>
        <option  value="Paraguay">Paraguay</option>
        <option  value="Peru">Peru</option>
        <option  value="Philippines">Philippines</option>
        <option  value="Pitcairn Island">Pitcairn Island</option>
        <option  value="Poland">Poland</option>
        <option  value="Portugal">Portugal</option>
        <option  value="Puerto Rico">Puerto Rico</option>
        <option  value="Qatar">Qatar</option>
        <option  value="Reunion">Reunion</option>
        <option  value="Romania">Romania</option>
        <option  value="Russia">Russia</option>
        <option  value="Rwanda">Rwanda</option>
        <option  value="Saint Kitts And Nevis">Saint Kitts And Nevis</option>
        <option  value="Saint Lucia">Saint Lucia</option>
        <option  value="Saint Vincent And The Grenadines">Saint Vincent And The Grenadines</option>
        <option  value="Samoa">Samoa</option>
        <option  value="San Marino">San Marino</option>
        <option  value="Sao Tome and Principe">Sao Tome and Principe</option>
        <option  value="Saudi Arabia">Saudi Arabia</option>
        <option  value="Scotland">Scotland</option>
        <option  value="Senegal">Senegal</option>
        <option  value="Serbia and Montenegro">Serbia and Montenegro</option>
        <option  value="Seychelles">Seychelles</option>
        <option  value="Sierra Leone">Sierra Leone</option>
        <option  value="Singapore">Singapore</option>
        <option  value="Slovak Republic">Slovak Republic</option>
        <option  value="Slovenia">Slovenia</option>
        <option  value="Solomon Islands">Solomon Islands</option>
        <option  value="Somalia">Somalia</option>
        <option  value="South Africa">South Africa</option>
        <option  value="Spain">Spain</option>
        <option  value="Sri Lanka">Sri Lanka</option>
        <option  value="St Helena">St Helena</option>
        <option  value="St Pierre and Miquelon">St Pierre and Miquelon</option>
        <option  value="Sudan">Sudan</option>
        <option  value="Suriname">Suriname</option>
        <option  value="Svalbard And Jan Mayen Islands">Svalbard And Jan Mayen Islands</option>
        <option  value="Swaziland">Swaziland</option>
        <option  value="Sweden">Sweden</option>
        <option  value="Switzerland">Switzerland</option>
        <option  value="Syria">Syria</option>
        <option  value="Taiwan">Taiwan</option>
        <option  value="Tajikistan">Tajikistan</option>
        <option  value="Tanzania">Tanzania</option>
        <option  value="Thailand">Thailand</option>
        <option  value="Togo">Togo</option>
        <option  value="Tokelau">Tokelau</option>
        <option  value="Tonga">Tonga</option>
        <option  value="Trinidad And Tobago">Trinidad And Tobago</option>
        <option  value="Tunisia">Tunisia</option>
        <option  value="Turkey">Turkey</option>
        <option  value="Turkmenistan">Turkmenistan</option>
        <option  value="Turks And Caicos Islands">Turks And Caicos Islands</option>
        <option  value="Tuvalu">Tuvalu</option>
        <option  value="Uganda">Uganda</option>
        <option  value="Ukraine">Ukraine</option>
        <option  value="United Arab Emirates">United Arab Emirates</option>
        <option  value="United States" selected="selected" >United States</option>
        <option  value="Uruguay">Uruguay</option>
        <option  value="Uzbekistan">Uzbekistan</option>
        <option  value="Vanuatu">Vanuatu</option>
        <option  value="Vatican City State (Holy See)">Vatican City State (Holy See)</option>
        <option  value="Venezuela">Venezuela</option>
        <option  value="Vietnam">Vietnam</option>
        <option  value="Virgin Islands (British)">Virgin Islands (British)</option>
        <option  value="Virgin Islands (US)">Virgin Islands (US)</option>
        <option  value="Wales">Wales</option>
        <option  value="Wallis And Futuna Islands">Wallis And Futuna Islands</option>
        <option  value="Western Sahara">Western Sahara</option>
        <option  value="Yemen">Yemen</option>
        <option  value="Zambia">Zambia</option>
        <option  value="Zimbabwe">Zimbabwe</option>
      </select>
</p> 

   
  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript">
  
  var opts = $('#country')[0].options;
for(var a in opts) { if(opts[a].value == '<?php echo $order["country"];?>') { $('#country')[0].selectedIndex = a; break; } }
  
  </script>
 
    
  
<?php

echo <<<EOM
<script>
godeli=function(rid){
	if(confirm('Are you sure you want to delete that?'))
		self.location.href='custom_orders_report.php?a=delitem&id=$id&itemid='+rid;
}
shipitems=function(allorselected){
	//alert('test');
	if(allorselected=='all') document.oo.a.value='shipallitems';
	else document.oo.a.value='shipitems';
	document.oo.submit();
}
</script>
<style> .tfoo td { border:0px; } .tfoo th { font-weight:bold; }</style>
<h3>Items on this Order</h3>
<table class="tfoo cart-table responsive-table">
<tr valign="bottom">
<th>&nbsp;</th>
<th nowrap style="text-align:left"><br />Item ID</th>
<th style="text-align:right"><br />Description</th>
<th nowrap>Order<br />Quantity</th>
<th nowrap>Shipped /<br />Remaining</th>
<th nowrap>Back<br />Order</th>
<th nowrap>Quantity<br />Shippable</th>
<th nowrap style="font-weight:bold;">Quantity<br />to Ship</th>
<th nowrap><br />Price </th>
<th nowrap><br />Total</th>

<th>&nbsp;</th></tr>
EOM;
	$irs = mysql_query("select a.*,b.item_price_type,b.price_multi,b.high_res_pdf,b.Pages,b.PDFFile,b.ImageFile,b.UserID,b.ID as FormRecordID,b.VendorID , b.FormID , b.require_file_upload from OrderItems a left join Items b on b.ID=a.ItemID
		where a.OrderRecordID='$id' order by a.FormID");
	$boapplied=0;
    $order_total = 0;
    $total_pro_price = 0;
	while($irow=@mysql_fetch_assoc($irs)){
    //print_r("<pre>");
    // print_r($irow);
    $item_price_type = $irow["item_price_type"]; 
     $price_multi = $irow["price_multi"]; 
     
    if($item_price_type=="multi_quantity_price")
    {
   	 $price_multi_str =  trim( $price_multi ) ;
   	 $price_multi_arr = explode("\n",$price_multi_str);
     foreach($price_multi_arr as $price_str)
		{
			$price_arr = explode(",",$price_str);
			$select_qty = '';
			if($irow[Quantity]==$price_arr[0])
			{
				$select_qty = 'selected="selected"';
				$price = $price_arr[1];
                
                if($price_arr!="")
				{	
					$find_star_arr = explode("*",$price_arr[1]);
					
					if(isset($find_star_arr[1]))
					{
						$price = $find_star_arr[0];
					
					}
					
				}
                
                $irow[Price] = $price;
                $order_total  += $price*$irow[Quantity];     
    		    $total_pro_price =  $price*$irow[Quantity];
			}
		}
    
    }else{
     $order_total  += $irow[Price]*$irow[Quantity];     
     $total_pro_price =  $irow[Price]*$irow[Quantity];
    
    }
    
     
    
    
		if($VendorID && $VendorID != $irow['VendorID']) { continue;}
		$remaining = $irow['Quantity'] - $irow['QtyShipped']; // assume $max to be as many as we need.
		$max=$remaining;
		// now $max = the number we still need shipped
		$bo=$irow['BOQuantity'];
		// get current inventory if there is backorder
		$iq='';
		if($bo>0) list($iq)=@mysql_fetch_row(mysql_query("select InventoryQuantity from Items where ID=$irow[ItemID]"));
		if($iq>0){ // apply inventory to backorder
			if($iq >= $bo) {
				$iq -= $bo;
				$boapplied += $bo;
				$bo = 0;
			} else {
				$bo -= $iq;
				$boapplied += $iq;
				$iq = 0;
			}
			mysql_query("update Items set InventoryQuantity=$iq where ID='$irow[ItemID]'");
			mysql_query("update OrderItems set BOQuantity=$bo where OrderRecordID='$id' and ID='$irow[ID]'");
		}
		if($bo) { // if we still have BO, set $max to remaining non-BO amount minus amount backordered
			$max -= $bo;
		}
        ?>
        
      
        
        
        
        <?php
        
          $total_pro_price = number_format($total_pro_price,2);
        
		if($max<0) $max=0;
		$img='';
		if($irow['ImageFile'] && is_file("$pdffolder/$irow[ImageFile]")) $img="$pdffolder/$irow[ImageFile]";
        
         if($irow[low_resolution_image]!='')
        {
        	$path_image = "../custom/pdf/".$irow[low_resolution_image];
                    
            if(file_exists($path_image))
            {
             $img = $path_image;
            }
        
        	
        }
        
        
        
		if($img) $img = <<<EOM
<img align="absmiddle" src="..images/viewcover.gif" onclick="viewthumb('$irow[ID]','$img')" style="cursor:pointer;" />
EOM;

		else $img="&nbsp;";

        ?>
            
        <?php
		
        
		echo <<<EOM
<tr >
<td>
EOM;

?>






<?php

if($irow['is_custom_order_item']=="Y")
{
	?>

<!--<img src="../images/file_icon.png" onclick="popupwindow('file_popup.php?orderid=<?php echo $_GET['id'];?>&formid=<?php echo $irow['FormID'];?>','Required File Upload Detail','700','300');" />
-->

<img src="../images/file_icon.png" 
onclick="modal_order_item_detail('<?php echo $_GET['id'];?>','<?php echo $irow['FormID'];?>');" />

<?php
	
}else{


if($irow['require_file_upload']=='yes')
{
			?>

<!--<img src="../images/file_icon.png" onclick="popupwindow('file_popup.php?orderid=<?php echo $_GET['id'];?>&formid=<?php echo $irow['FormID'];?>','Required File Upload Detail','700','300');" />
-->

<img src="../images/file_icon.png" 
onclick="modal_order_item_detail('<?php echo $_GET['id'];?>','<?php echo $irow['FormID'];?>');" />



<?php
}

}
?>



<?php

$irow[Price] = number_format($irow[Price],2);

echo <<<EOM
</td>
<td nowrap id="cimg$irow[ID]" style="width:1%">
EOM;
		
      
        
        if($irow['PDFFile']) echo <<<EOM
<a target="_new" href="$pdffolder/$irow[PDFFile]">$irow[FormID]</a> $img
EOM;


		elseif($irow['UserID']) /* i.e. business card */ echo <<<EOM
<a target="_new" href="bc.php?id=$irow[FormRecordID]">$irow[FormID]</a> $img
EOM;


		if($irow['low_resolution_pdf']!="")
		{
        
       echo 
	   		'<span style="left:-5px;position: relative;top: 0px;"><a target="_blank"  href="../custom/pdf/'.$irow['low_resolution_pdf'].'"><img align="absmiddle" src="../images/pdf.png" /></a></span>';
		
		}
		
		
		if($irow['high_resolution_pdf']!="")
		{
        
       echo 
	   		'<span style="left:-2px;position: relative;top: 0px;"><a target="_blank"  href="../custom/pdf_high_resolution.php?id='.$irow['ID'].'&itemid='.$irow['ItemID'].'&orderrecordid='.$irow['OrderRecordID'].'"><img align="absmiddle" src="../images/pdf_high.png" /></a></span>';
		
		}
		
		
		if($irow['PDFFile']!="")
		{
        
       echo 
	   		'<span style="left:-5px;position: relative;top: 0px;"><a target="_blank"  href="'.$pdffolder.'/'.$irow['PDFFile'].'"><img align="absmiddle" src="../images/pdf.png" /></a></span>';
		
		}
		
		if($irow['require_file_upload']=='yes')
		{
			$desc = substr($irow[FormDescription],0,40);
			
		}else{
		
			$desc =  $irow[FormDescription] ;
		
		}
		
		if($irow['high_res_pdf']!="")
		{
        
       echo 
	   		'<span style="left:-2px;position: relative;top: 0px;"><a target="_blank"  href="high_res_pdf/'.$irow['high_res_pdf'].'"><img align="absmiddle" src="../images/pdf_high.png" /></a></span>';
		
		}
		
		
		
		
		
		else echo <<<EOM
$irow[FormID]
EOM;
		if($bo>0) $bostyle="background-color:red;color:white;font-weight:bold;padding:0px 3px 0px 3px;";
		else $bostyle='';
		echo <<<EOM
</td><td style="text-align: right;">$desc</td>
<td><input style="text-align:right" type="text" name="Quantity[$irow[ID]]" value="$irow[Quantity]" size="4" /></td>
<td align="right">$irow[QtyShipped] / $remaining<input type="hidden" name="QtyShipped[$irow[ID]]" value="$irow[QtyShipped]" /></td>
<td align="right"><span style="$bostyle">$bo</span></td>
<td align="right">$max<input type="hidden" name="Shippable" value="$max" /></td>
EOM;
		if($order['Status'] == 'inprocess' && $max>0) {
			echo <<<EOM
<td><input style="text-align:right" type="text" name="QuantityToShip[$irow[ID]]" value="0" size="4" /></td>
EOM;
		} else echo <<<EOM
<td align="right">n/a</td>
EOM;
		echo <<<EOM
<td><input style="text-align:right" type="text" name="Price[$irow[FormID]]" value="$irow[Price]" size="4" /></td>
<td>$total_pro_price</td>
<td>
</td>
</tr>
EOM;
	}
    
    
    
    	if($order_total){
       $orderTotal = number_format($order_total,2);
       
     
		echo <<<EOM
<tr>
    <td nowrap colspan="9" style="text-align:right">
  <strong> Order Total : </strong>
    </td>


    <td nowrap style="text-align:left">
     <strong> $orderTotal</strong>
    </td>
    <td nowrap  style="text-align:left"></td>
  
    
</tr>
EOM;
	}

    
	if($boapplied){
		echo <<<EOM
<tr><td nowrap colspan="10" style="text-align:center">
<span style="color:red;font-weight:bold;">$boapplied unit(s) of inventory have been applied to backorder.</span>
</td></tr>
EOM;
	}
	if($order['Status'] == 'inprocess') echo <<<EOM
<tr><td nowrap colspan="2">

</td>
<td colspan="4" align="right">
</td><td colspan="3">&nbsp;</td>
</tr>
EOM;

	echo <<<EOM
</table>
EOM;
	if($order['BillCode']==3) echo <<<EOM
<div style="text-align:center"><span style="color:red;background-color:yellow;">This is a JDE order. Please try to avoid splitting shipments on line items.</span></div>
EOM;
	echo <<<EOM
</td></tr>
<tr><td colspan="2">
<script>
settrackingid=function(sid){
	var x=prompt('Enter Tracking ID:','');
	if(!x || x=='') return;
	x=escape(x);
	self.location.href='custom_orders_report.php?a=settrackingid&id=$id&sid='+sid+'&trackingid='+x;
}

settrackingid_new=function(sid){
	
	
	//var trackingid = document.getElementByID('trackingid_'+sid).val();
	
	var trackingid =  document.getElementById('trackingid_'+sid).value;
	
	trackingid = trackingid.split(' ').join('');
	trackingid = trackingid.split(',').join('');
	
	if(trackingid=="")
	{
		
		alert("please enter tracking id");
		return false;	
	}
	
	
	
	var shipment_method= document.getElementById('shipment_method_'+sid).value;
	var shipping_amount= document.getElementById('shipping_amount_'+sid).value;
	var fulfillment_amount= document.getElementById('fulfillment_amount_'+sid).value;
	
	
	
	
	self.location.href='custom_orders_report.php?a=settrackingid&id=$id&sid='+sid+'&trackingid='+trackingid+'&shipment_method='+shipment_method+'&shipping_amount='+shipping_amount+'&fulfillment_amount='+fulfillment_amount;
	
	//self.location.href='custom_orders_report.php?a=settrackingid&id=$id&sid='+sid+'&trackingid='+x;
}


godelshipment=function(sid){
	if(confirm('Are you SURE you want to delete that shipment?')){
		self.location.href='custom_orders_report.php?a=delshipment&id=$id&sid='+sid;
	}
}
</script>
<h3 align="center">Shipment Records</h3>
<table align="center"  class="cart-table responsive-table">
EOM;
	$srs = mysql_query("select ID,ShipDate,TrackingID , shipment_method , shipping_amount , fulfillment_amount from OrderShipments where OrderRecordID='$id'");
	$scnt=0;
	while(list($osi,$osd,$ot,$shipment_method,$shipping_amount,$fulfillment_amount)=@mysql_fetch_row($srs)){
		$osd = date("m/d/Y g:iA", strtotime($osd));
		if($scnt) echo <<<EOM
<tr><td colspan="3">&nbsp;</td></tr>
EOM;
		echo <<<EOM
<tr ><td align="center" colspan="2" nowrap><b>
Date: $osd
</b>
<!--&nbsp;
<a href="javascript:settrackingid('$osi')">Tracking ID</a>
<span style="background:white;padding:1px 3px 1px 3px;font-weight:bold;">$ot</span>-->


</td></tr>
EOM;

?>

<tr>
<td colspan="3">
<!--<form method="get">

 <input name="id" type="hidden" size="20" maxlength="50" value="<?php echo $_GET["id"];?>" />
  <input name="a" type="hidden" size="20" maxlength="50" value="settrackingid" />
   <input name="sid" type="hidden" size="20" maxlength="50" value="1" />-->
  
  
  
    <table class="cart-table responsive-table" border="0" align="center" cellpadding="4" cellspacing="4" bgcolor="#a9a9a9">
      <tr>
        <td > 
              
   Tracking #
            <input name="" id="trackingid_<?php echo $osi;?>" type="text" size="20" maxlength="50" value="<?php echo $ot;?>" />
            
    <br/>
    
    <select name="" id="shipment_method_<?php echo $osi;?>">
      <option value="FedEx" <?php if(isset($shipment_method) and $shipment_method=="FedEx") { ?> selected="selected" <?php } ?> >FedEx</option>
      <option value="UPS" <?php if(isset($shipment_method) and $shipment_method=="UPS") { ?> selected="selected" <?php } ?>>UPS</option>
      <option value="USPS" <?php if(isset($shipment_method) and $shipment_method=="USPS") { ?> selected="selected" <?php } ?>>USPS</option>
    </select>
    
    Shipping: $
    
    <input name="" id="shipping_amount_<?php echo $osi;?>" type="text" size="7" maxlength="15" value="<?php echo $shipping_amount;?>" />
    
    
    Fulfillment: $
    <input name="" id="fulfillment_amount_<?php echo $osi;?>" type="text" size="7" maxlength="15" value="<?php echo $fulfillment_amount;?>" />
    
        
   </td>
      </tr>
    </table>
<!--  </form> --> 
    
</td>
</tr>

<?php
	 $irs = mysql_query("select a.ID,a.FormID,b.Description, a.Quantity
			from OrderShipmentItems a join Items b on b.FormID=a.FormID
			where a.OrderRecordID='$id' and a.OrderShipmentID='$osi' order by a.FormID");
		while(list($sii,$sfi,$sdesc,$sq)=@mysql_fetch_row($irs)){
			echo <<<EOM
<tr><td nowrap> &nbsp; $sfi</td><td>$sdesc</td><td>qty $sq</td></tr>
EOM;
		}
		echo <<<EOM
EOM;
		++$scnt;
	}
	if(!$scnt) echo "<tr><td align=\"center\">There are no shipment records for this order. Yet.</td></tr>";
	

$order[Notes] = str_replace('\r\n','<br>',$order[Notes]);

$breaks = array("<br />","<br>","<br/>");  
$order[Notes] = str_ireplace($breaks, "\r\n", $order[Notes]);  


	
	echo <<<EOM
</table>
EOM;

?>
<p class="form-row form-row-wide">
    <label for="username"><strong>Additional Comments / Questions</strong></label>
    <textarea cols="100" rows="3" name="Notes"><?php echo $order[Notes];?></textarea>
</p>

<p class="form-row form-row-wide">
    <label for="username"><strong>Bill Code</strong></label>
    <select name="BillCode">
    <?php
	@reset($billcodes);
	foreach($billcodes as $k=>$v){
		if($k==$order['BillCode']) $ch='selected="selected"';
		else $ch='';
		echo <<<EOM
<option value="$k" $ch>$k. $v</option>
EOM;
	}
	
	?>
    
    </select>
</p>


<p class="form-row form-row-wide">
    <label for="username"><strong>Order Status</strong></label>
    <select name="BillCode">
    <?php
	foreach($orderstatus as $k=>$v){
		if($v==$order['Status']) $ch='selected="selected"';
		else $ch='';
		$uv = strtoupper($v);
		echo <<<EOM
<option value="$v" $ch>$uv</option>
EOM;
	}
	
	?>
    
    </select>
</p>


<p class="form-row form-row-wide">
    <label for="username"><strong>Bill To</strong></label>
    <textarea valign="top" rows="4" cols="100" name="BillTo"><?php echo $order[BillTo];?></textarea>
</p>

<?php


echo <<<EOM
<tr><td colspan="2" align="center">
<script>
getchecked=function(){
	var chks = document.getElementsByTagName('input');
	var items='';
	var ucitems=''; // unchecked items... to use if none are checked.
	for(var a=0; a<chks.length; a++){
		if(!/^cbx/.test(chks[a].id)) continue;
		else if(chks[a].checked){
			if(items != '') items += ',';
			items += chks[a].value;
		} else {
			if(ucitems != '') ucitems += ',';
			ucitems += chks[a].value;
		}
	}
	if(items=='' && ucitems != '') items = ucitems;
	return items;
}
jobticket=function(orderid) {
	var items = getchecked(); // gets 'cbx*' items that are checked and returns csv list of their values
	var tar=items.split(',');
	var quans='';
	var elar = document.getElementsByTagName('input');
	for(var a=0; a<tar.length; a++){
		if(quans != '') quans += ',';
		var oqname= 'Quantity[' + tar[a] + ']';
		var sqname= 'QuantityToShip[' + tar[a] + ']';
		var tmpquan='';
		for (var b=0; b<elar.length; b++){
			if(elar[b].name == oqname) tmpquan = elar[b].value;
			if(elar[b].name == sqname && elar[b].value > '0') { tmpquan = elar[b].value; break; }
		}
		quans += tmpquan;
	}
	var url='jobticket.php?id='+orderid+'&items='+items+'&quans='+quans;
	window.open(url,url);
}
dopacking=function(orderid,packing) {
	var url='jobticket.php?id='+orderid + '&packing='+packing;
	window.open(url,url);
}


</script>
</td></tr>
<tr><td colspan="2" align="center" style="background:$GLOBALS[mycolor];">
<input type="button" onclick="self.location.href='custom_orders_report.php'" value="Back to List" />

EOM;
	echo <<<EOM
</td></tr>
</table>


EOM;
}


?>



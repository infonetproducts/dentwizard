<?php
ob_start();
include("setting.php");
include_once("include/start.php");
include_once("include/common_setting.php");
if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';

$error = array();

if($action=='save'){
	
	
	
	if($_POST["Password"]!="")
	{
		if($_POST["confirm_Password"]=="")
		{
			$error["confirm_Password"]  = "<span style='color:red;'> Please enter confirm password.</span>";
		}else{
			
		if($_POST["confirm_Password"]!=$_POST["Password"])
		{
			$error["confirm_Password"]  = "<span style='color:red;'> confirm password not match password.</span>";
		}	
			
			
		}
		
	}
	
	
	if($_POST["employee_type"]=="")
		{
			$error["employee_type"]  = "<span style='color:red;'> Please select employee type.</span>";
		}
	
	
	if($_POST["ShipToName"]=="")
	{
	
		//echo "Error: Please enter ship to name. Please go back and try again.";
		//$error["ShipToName"] = "<span style='color:red;'>Please enter ship to name.</span>";
		//exit;
	
	}
	
	if($_POST["Address1"]=="")
	{
	
		//echo "Error: Please enter address1. Please go back and try again.";
		//exit;
		//$error["Address1"] = "<span style='color:red;'>Please enter address1.</span>";
	
	}
	
	
	if($_POST["State"]=="")
	{
	
		//echo "Error: Please enter state. Please go back and try again.";
		//exit;
		
		//$error["State"] = "<span style='color:red;'>Please enter state.</span>";
	
	}
	
	
	if($_POST["Zip"]=="")
	{
	
		//echo "Error: Please enter zip. Please go back and try again.";
		//exit;
		//$error["Zip"] = "<span style='color:red;'>Please enter zip.</span>";
	
	}
	
	if(empty($error))
	{
	
	foreach($_POST as $k=>$v){
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}
	if(!$_POST['ShipToDept'])$_POST['ShipToDept']=$_POST['Dept'];
	
	// $sql_email_check = "select ID from Users where ID != '$AID' and Email = '$_POST[Email]' and CID = '$CID' ";
	 
	 $sql_email_check = "
	
	select ID from Users where ID != '$AID' and Email = '$_POST[Email]' and CID = '$CID'
	
	and is_virtual_admin!='Y'
	
	
	";
	
	list($echkid)=@mysql_fetch_row(mysql_query($sql_email_check));
	if($echkid) {
		//echo "Error: Select email invalid or unavailable. Please go back and try again.";
		//exit;
		$error["Email"] = "<span style='color:red;'>Select email invalid or unavailable.</span>";
		
	}
	
	
	
	$pw=$_POST['Password'];
	if($pw)
    {
        $pw = mysql_real_escape_string(endecrypt($pw,$pw,''));   
        $sql_email_check = "select ID from Users where Password = '$pw' and Email = '$_POST[Email]' ";            
        list($echkid_password)=@mysql_fetch_row(mysql_query($sql_email_check));
        if($echkid_password) {
            //die("Error: Please enter unique password. Please go back and try again.");
            $error["Password"]  = "<span style='color:red;'> Please enter unique password.</span>";
        }
   
   } 
	
	
	
	
	
	
	
	if(empty($error))
	{
	
		$pwstr='';
		if($_POST['Password']) {
			$pw = endecrypt($_POST['Password'], $_POST['Password'], '');
			$pw = mysql_real_escape_string($pw);
			$pwstr = "Password='$pw',";
		}
		mysql_query("update Users set $pwstr
			Name='$_POST[Name]',Email='$_POST[Email]',
			ShipToName='$_POST[ShipToName]',ShipToDept='$_POST[ShipToDept]',
			Phone='$_POST[Phone]',Address1='$_POST[Address1]',Address2='$_POST[Address2]',
			City='$_POST[City]',State='$_POST[State]',Zip='$_POST[Zip]' , country='$_POST[country]' 
			, employee_type='$_POST[employee_type]'
			where ID='$AID'") or die( mysql_error() );
	#		Dept='$_POST[Dept]',
	#		FormCategoryIDs='$_POST[FormCategoryIDs]'
		//header("Location: index.php");
		header("Location: profile.php");
		exit;
	}
	
	}
	
}


$row = @mysql_fetch_assoc(mysql_query("select * from Users where ID='$AID'"));
$depts = depts($row['Dept']);
$star = "<span style=\"color:red;font-weight:bold;\"> *</span>";

?>


<?php include("header.php");?>
<?php include("top_bar.php");?>
<?php include("header_top_menu_with_search.php");?>
<?php include("top_menu.php");?>
<!-- Titlebar
================================================== -->
<section class="titlebar">
	<div class="container">
		<div class="sixteen columns">
			<h2>Edit Profile</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Edit Profile</li>
				</ul>
			</nav>
		</div>
	</div>
</section>
<!-- Content
================================================== -->
<!-- Container -->
<div class="container">
  
  <!-- Billing Details / Enc -->
  <!-- Checkout Cart -->
  
   
  
 <div class="six columns centered">

		 

		<div class="tabs-container">
			 
            
			<div class="tab-content" id="tab1" style="display: block;">

				<!--<h3 class="headline">Login</h3><span class="line" style="margin-bottom:20px;"></span><div class="clearfix"></div>-->

					
				<form method="post" action="profile.php">
				<input type="hidden" name="a" value="save" />

					 <p class="form-row form-row-wide">
						<label for="username">Name<?php echo $star;?></label>
                        <input class="input-text" type="text" size="33" name="Name" value="<?php echo $row[Name];?>" />
					
                    </p>
                    
                    <?php
					if($CID==42)
					{
					?> 
					 <p class="form-row form-row-wide">
						<label for="username">Employee Type<?php echo $star;?></label>
                        <select name="employee_type" id="employee_type">
		 	<option value="">Please select </option>
			<?php 
			
			foreach($employee_type_arr as $k_e_type=>$v_e_type)
			{
				$selected_e_type = '';
				if($row[employee_type]==$k_e_type)
				{
					$selected_e_type = 'selected="selected"';
				}
				
				
			?>
				<option  <?php echo $selected_e_type;?> value="<?php echo $k_e_type;?>"><?php echo $v_e_type;?></option>
			<?php 
			}
			?>
			
			
		 </select>
					
                    	
                          
                          <?php
					if(isset($error['employee_type']))
					{
					?>
                    <br/><?php echo $error['employee_type'];?>
                    
                    <?php
					}
					?>
                    
                    
                    </p>
					<?php
					}
					?>
                    
                     <p class="form-row form-row-wide">
						<label for="username">Email Address<?php echo $star;?></label>
                       <input  class="input-text" type="text" size="33" name="Email" value="<?php echo $row[Email];?>" />
						
                         
                          
                          <?php
					if(isset($error['Email']))
					{
					?>
                    <br/><?php echo $error['Email'];?>
                    
                    <?php
					}
					?>
                         
                         
                    
                    </p>
                    
                     <p class="form-row form-row-wide">
						<label for="username">Password</label>
                     
                     <input class="input-text" type="password" size="15" maxlength="15" name="Password" value="" /> (blank to leave unchanged)
                         
                         
                          <?php
					if(isset($error['Password']))
					{
					?>
                    <br/><?php echo $error['Password'];?>
                    
                    <?php
					}
					?>
                    
                    </p>
                    
                    
                     <p class="form-row form-row-wide">
						<label for="username">Confirm Password</label>
                     
                    <input class="input-text" type="password" size="15" maxlength="15" name="confirm_Password" value="" /> 


                         
                          <?php
					if(isset($error['confirm_Password']))
					{
					?>
                    <br/><?php echo $error['confirm_Password'];?>
                    
                    <?php
					}
					?>
                    
                    
                    </p>
                    
                    
                     <p align="center" class="form-row form-row-wide">
						<label for="username"><strong>Primary Shipping Address</strong></label>
                    </p>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Ship To Name</label>
                     
                    <input class="input-text" type="text" size="33" name="ShipToName" value="<?php echo $row[ShipToName];?>" /> 
                    <?php
					if(isset($error['ShipToName']))
					{
					?>
                    <br/><?php echo $error['ShipToName'];?>
                    
                    <?php
					}
					?>
                    
                    </p>
                    
                    
                    <p class="form-row form-row-wide">
						<label for="username">Ship To Company</label>
                     
                   <input class="input-text" type="text" size="33" name="ShipToDept" value="<?php echo $row[ShipToDept];?>" />
                    
                    </p>
                    
                    
                    
                      <p class="form-row form-row-wide">
						<label for="username">Address 1</label>
                     
                  <input class="input-text" type="text" size="33" name="Address1" value="<?php echo $row[Address1];?>" />
                  
                    <?php
					if(isset($error['Address1']))
					{
					?>
                    <br/><?php echo $error['Address1'];?>
                    
                    <?php
					}
					?>
                    
                    </p>
                    
                    
                     <p class="form-row form-row-wide">
						<label for="username">Address 2</label>
                     
                 <input  class="input-text" type="text" size="33" name="Address2" value="<?php echo $row[Address2];?>" />
                    
                    </p>
                    
                    
                  <p class="form-row form-row-wide">
						<label for="username">City</label>
                  <input class="input-text" type="text" size="33" name="City" value="<?php echo $row[City];?>" />
                    </p>
                    
                     <p class="form-row form-row-wide">
						<label for="username">State/Province/Region</label>
                  <input class="input-text" type="text" size="2" name="State" value="<?php echo $row[State];?>" />
                  
                    </p>
                    
                    
                     <p class="form-row form-row-wide">
						<label for="username">Zip/Postal Code</label>
                  <input class="input-text" type="text"  name="Zip" value="<?php echo $row[Zip];?>" />
                  
                  
                       <?php
					if(isset($error['Zip']))
					{
					?>
                    <br/><?php echo $error['Zip'];?>
                    
                    <?php
					}
					?>
                    
                    
                  
                    </p>
                    
                    
                     <p class="form-row form-row-wide">
						<label for="username">Country</label>
                  
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
      
      
                       <?php
					if(isset($error['country']))
					{
					?>
                    <br/><?php echo $error['country'];?>
                    
                    <?php
					}
					?>
                    
                    
                  
                    </p>
                    
                    <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript">
  
  var opts = $('#country')[0].options;
for(var a in opts) { if(opts[a].value == '<?php echo $row["country"];?>') { $('#country')[0].selectedIndex = a; break; } }
  
  </script>
  
                    
                     <p class="form-row form-row-wide">
						<label for="username">Phone Number</label>
                  
                     <input type="text" size="33" name="Phone" value="<?php echo $row[Phone];?>" />
       
                    </p>
                    
                     
                     <p class="form-row form-row-wide">
						
                  
                        <input type="submit" value="SAVE" />
       
                    </p>
                    
                    
                     <p class="form-row form-row-wide">
                      <br/<br/>
                    </p>
                    
                     <p align="center" class="form-row form-row-wide">
						<label for="username"><strong>Additional Shipping Addresses</strong></label>
                    </p>
                    
                    
                    
                     <p class="form-row form-row-wide">
						<label for="username">The following is a list of your additional shipping addresses. To add a new shipping address, please click the &quot;Add Shipping Address&quot; button below. If you would like to make changes to an existing shipping address, please click the name on the shipping address below.</label>
                  
                    
       
                    </p>
                    
                     <?php
  
$ship_rs =  mysql_query("select * from multiple_shipping_address where user_id = '$AID' and CID = '$CID' ");
if( mysql_num_rows( $ship_rs) > 0 )
{  
  while($ship_detail = @mysql_fetch_assoc($ship_rs) )
  {
  
  ?>
                     <p align="center" class="form-row form-row-wide">
						 <a style="color:blue;" href="add_shipping_address.php?id=<?php echo $ship_detail["ID"];?>"><?php echo $ship_detail["name_of_shipping_address"];?></a> 
                  
                    
       
                    </p>
                 
            <?php
  }
  
  
  }else{
  ?>
  
   					  <p class="form-row form-row-wide">
						<label for="username"><em>You currently have no additional shipping addresses. </em></label>
                    </p>
  
  <?php
  
  }
  
  ?>  
 	</form>
     
   <p class="form-row form-row-wide">
						<label for="username">
                          <input type="submit" name="Submit" value="Add Shipping Address" onclick="self.location.href='add_shipping_address.php'">
                        
                        </label>
                    </p>
  
        
				 
			
                
			</div>

			 
				 
		</div>
	</div>
  
  
   
  
   
  
  <!-- Checkout Cart / End -->
</div>
<!-- Container / End -->
<div class="margin-top-50"></div>


<script type="text/javascript" src="../javascript.js"></script>
<script type="text/javascript" src="../datepicker.js"></script>
<link rel="stylesheet" href="../datepicker.css" />
<script type="text/javascript" src="../js/prototype.js"></script>
<script type="text/javascript" src="../js/scriptaculous.js"></script>


 



<?php

function PhoneField($fname,$val){
	if($val && strlen($val) > 10) $val = substr($val, -10);
	elseif($val && strlen($val)<10) $val = '';
	if($val) {
		$val1 = substr($val,0,3);
		$val2 = substr($val,3,3);
		$val3 = substr($val, -4);
	} else $val1=$val2=$val3='';
	$str = <<<EOM
<input type="text" size="3" name="{$fname}[1]" maxlength="3" value="$val1" /> .
<input type="text" size="3" name="{$fname}[2]" maxlength="3" value="$val2" /> .
<input type="text" size="4" name="{$fname}[3]" maxlength="4" value="$val3" />
EOM;
	return $str;
}


//pr_n($_SESSION);

//session_destroy();
?>
<?php include("footer.php");?>


 

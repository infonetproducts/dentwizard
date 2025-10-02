<?php
ob_start();
include("setting.php");
include_once("include/start.php");
include_once("include/common_setting.php");
// figure out business card cat...
$bccat='';
$sql_bccat = "select ID from Category where CID=$CID and Name like 'Business%Card%' order by ID limit 1" ; 
list($bccat)=@mysql_fetch_row(mysql_query($sql_bccat));

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';

$_REQUEST['TemplateID'] = 52 ;

// business card editor.
// id=0 means adding a new record.
// otherwise load #id for edit.

//echo $_SESSION['sysadmin'];

/*print_r("<pre>");
print_r($_SESSION);*/

$uid = $_SESSION['AID'];
if(!$uid)exit;

$id=$_REQUEST['id'];
if(!$id || !is_numeric($id)) $id='';
#if(!$id) { echo "Error: NO ID FOUND."; exit; }

if($action=='del' && $id){
	mysql_query("delete from Items where ID='$id' and UserID='$uid'");
	if(@mysql_affected_rows()==1){
		mysql_query("delete from BusinessCardData where ItemID='$id'");
	}
	echo "<script>history.go(-1)</script>";
	exit;
}


if($action=='save'){

 

	foreach($_REQUEST as $k=>$v){
		if(!is_array($v)) $vals[$k] = mysql_real_escape_string(trim($v));
	}
	if($id){
		list($u)=@mysql_fetch_row(mysql_query("select UserID from Items where ID='$id'"));
		if($u != $uid) { echo "Error: incorrect id."; exit; }
	}
	$phonefields = array('Phone','TollFree','Cellular','Fax');
	foreach($phonefields as $f){
		if($f) $$f = $_REQUEST[$f][1] . $_REQUEST[$f][2] . $_REQUEST[$f][3];
	}
	$isnew=0;

	if(!$id){
		$isnew=1;
		list($nm)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$uid'"));
		list($templatename)=@mysql_fetch_row(mysql_query("select Name from bcTemplate where ID='$_REQUEST[TemplateID]'"));
						
		$nm =  stripslashes($nm);
		

		$nm = mysql_real_escape_string(trim($nm));
		$iid = str_replace(" ","",$nm) . date("mdy_gia");
		
		$iid = str_replace("'","",$iid) ;
		
		$iid =  preg_replace('/\\\\/', '', $iid);
		
		mysql_query("insert Items set CID='$CID',FormID='$iid',Description='$templatename',MinQTY=250,MaxQTY=500,UserID='$uid' , item_type='on_demand' ,  item_title='$templatename' , is_cubicle_item = '1' ");
		$id=mysql_insert_id();
		if($id){
			list($vid)=@mysql_fetch_row(mysql_query("select VendorID from bcTemplate where ID='$_REQUEST[TemplateID]'"));
			if($vid){
				mysql_query("update Items set VendorID='$vid' where ID='$id'");
			}
			mysql_query("insert FormCategoryLink set CategoryID=$bccat, FormID=$id");
			mysql_query("insert BusinessCardData set ItemID='$id',TemplateID='$_REQUEST[TemplateID]'");
		}
	}
	// hardcoded "all possible fields" for bc data update.
	// Make sure to add here if adding elsewhere. =)
	$vals[Photo] = '' ;
	if($_FILES["Photo"]["name"]!="")
	{
		 $photo_name = time()."_".$_FILES["Photo"]["name"];
	 	 $upload_path = "gfx/$CID/$photo_name";
	 
		 if(move_uploaded_file($_FILES["Photo"]["tmp_name"],$upload_path))
			{
				$vals[Photo] = $photo_name;
			}
	}
	
	 
	
	mysql_query("update BusinessCardData set Name='$vals[Name]', CompanyName='$vals[CompanyName]', Title='$vals[Title]', Title2='$vals[Title2]',
		Address1='$vals[Address1]', Address2='$vals[Address2]', City='$vals[City]', State='$vals[State]', Zip='$vals[Zip]',
		Email='$vals[Email]',
		Phone='$Phone', PhoneExtension='$vals[PhoneExtension]', TollFree='$TollFree', TFExtension='$vals[TFExtension]',Cellular='$Cellular',Fax='$Fax',
		WebsiteURL='$vals[WebsiteURL]', Facebook='$vals[Facebook]', Twitter='$vals[Twitter]', NumYears='$vals[NumYears]' ,Photo='$vals[Photo]',CubicleNumber='$vals[CubicleNumber]' ,warehouse_id='$vals[warehouse_id]'
		where ItemID='$id'");
	if(!$isnew){
		system("rm -f $pdffolder/$id" . "_*.pdf");
		system("rm -f $pdffolder/$id" . "_*.jpeg");
		system("rm -f $pdffolder/$id" . "_*.png");
	}
	$fn = "$pdffolder/$id" . "_" . time() . ".pdf";
	system("php ../bc.php $id '$fn' $CID");
//	sleep(1);
	$gfn = preg_replace("/\.pdf$/",".png",$fn);
	system("convert -resize 350 -interlace none -density 300  $fn $gfn");
	mysql_query("update Items set PDFFile = '" . basename($fn) . "', ImageFile='" . basename($gfn) . "' where ID='$id'"); 

	if($_REQUEST["TemplateID"]==50)
	{
		$fn_back_site = "$pdffolder/$id" . "_back_site_" . time() . ".pdf";
		system("php ../bc_back_site_pdf.php $id '$fn_back_site' $CID");	
	}
	// start code for merging pdf
	$TemplateID_bb = $_REQUEST["TemplateID"] ;
	$line_pdf_data = @mysql_fetch_assoc(mysql_query("select * from line_pdf where template_id='$TemplateID_bb' "));
	
	if(isset($line_pdf_data['back_site_pdf']) and $line_pdf_data['back_site_pdf']!="")
	{
		$gfxfolder = dirname(__FILE__) . "/gfx/$CID";
		$pdffolder = dirname(__FILE__) . "/pdf/$CID";
		
		$back_site_pdf = $line_pdf_data['back_site_pdf'];
	    $back_site_pdf = "$gfxfolder/$back_site_pdf";
		
		if($TemplateID_bb==50)
		{
			$back_site_pdf = $fn_back_site;
		}
	
		$gs = '/usr/bin/gs';
		
		$infiles = array(
			$fn,
			$back_site_pdf
		); 
		
		$outfile = "$pdffolder/". time() . ".pdf";
		
		$args = "-dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite ";
		$args .= "-sOutputFile='{$outfile}' ";
		
		foreach ($infiles as $f)
		$args .= "'{$f}' ";
		system("$gs $args");
		
		rename($fn,time().".pdf");
		rename($outfile,$fn);
		
	}
	// end code for mergin pdf
	$_REQUEST['Quantity'][$iid] = 1 ;
	
	$_SESSION['Order_item_id'][$iid] =  $id;
	
	$_REQUEST['cats'] = 4 ;
	foreach($_REQUEST['Quantity'] as $k=>$v){
		if($v>0) 
        {
        	$_SESSION['Order'][$k] = $v;
			
			if($_REQUEST['cats']==4)
			{
				$_SESSION['preview'] = $k;
            }
			
            $_SESSION['Order_type'][$k] = 'N';
             
          
            
        }
	}

	$_SESSION['bccart'] = '1';
	 
//	die('dfdfd');
	header("Location: preview_cubicle_card.php");
	
	die;

	header("Location: itemsindex.php?cats[]=$bccat");
	exit;
}


if($id){
	$sql = "select a.*,b.* from Items a join BusinessCardData b on b.ItemID=a.ID where a.ID=$id and a.UserID=$uid limit 1";
	$rs = mysql_query($sql);
	$item = @mysql_fetch_assoc($rs);
} else {
	$item = @mysql_fetch_assoc(mysql_query("select `Name`, Email, Phone, Address1,Address2, City, State, Zip from Users where ID='$uid'"));
	$item['TemplateID'] = $_REQUEST['TemplateID'];
}

$templateid = $item['TemplateID'];

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
			<h2>Order Cubicle Signs</h2>
			
			<nav id="breadcrumbs">
				<ul>
					<li><a href="#">Home</a></li>
					<li>Order Cubicle Signs</li>
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
			<!-- Login -->
            <div style="margin:5px;">To order a Cubicle Sign, please complete the form below and click the Preview button. </div>
            
			<div class="tab-content" id="tab1" style="display: block;">

				<!--<h3 class="headline">Login</h3><span class="line" style="margin-bottom:20px;"></span><div class="clearfix"></div>-->

					
				<form method="post" action="order_cubical_signs.php" enctype="multipart/form-data">

				<input type="hidden" name="id" value="<?php echo $id;?>" />

				
                      <?php
					if(!$templateid)
					{
					?>
                 	 <input type="hidden" name="a" value="">
                    
                     
                       
                  
					   <p  class="form-row form-row-wide" >
						
                        
                        Please Select Template: 
                        <select  style="margin-bottom:20px;" name="TemplateID">
                        <option value="">--</option> 
                        <?php
						list($employee_type)=@mysql_fetch_row(mysql_query("select employee_type from Users where ID='$_SESSION[AID]'"));
						
						if($employee_type!="")
						{
							$sql_in = " employee_type IN ( 'all_type' , '$employee_type' ) and ";
							
						}else{
						
							$sql_in = " employee_type IN ( 'all_type' , '$employee_type' ) and ";
						}
						
						$trs = mysql_query("select ID,Name , employee_type from bcTemplate where CID=$CID order by Name");
	
	
	while(list($i,$n, $employee_type_template )=@mysql_fetch_row($trs)){
	
	list($SysAdmin_checker)=@mysql_fetch_row(mysql_query("select SysAdmin from Users where ID='$_SESSION[AID]'"));
	
	if($i==48)
	{
		
		
		if($SysAdmin_checker==1)
		{
		
		}else{
			continue;
		} 
	
	}
	
	if($i==49)
	{
		if($SysAdmin_checker==1)
		{
		
		}else{
			continue;
		} 
	
	}
	
	
	if($SysAdmin_checker==1)
	{
		// not need to check employee type for admin
		
		
		
	}else{
		
		//  need to check employee type for user
		
		if($employee_type_template!="")
		{
			$arr_db_emplyee_type = explode(",",$employee_type_template);
		}
			
		if(in_array($employee_type,$arr_db_emplyee_type))
		{
			// show
		}else{
			continue;
		}
					
			
	
	}
	
	
	
	
		echo <<<EOM
<option value="$i">$n</option>
EOM;
	}
						
						?>
					<br/>	
					</p>
                    
                   
				 

					<p class="form-row"> 
                   
						<input  type="submit" class="button" value="Continue" />
					</p> 

					 

					
				
                
                 <?php
					}
					?>


				 <?php
					if($templateid)
					{
					


?>

<input type="hidden" name="a" value="save" />
 <input type="hidden" name="TemplateID" value="<?php echo $templateid;?>" />	
<?php

$frs = mysql_query("select DBField from bcTemplateFields where bcTemplateID='$templateid' order by ID");
// parse into array of actual fields, since "DBField" may now contain other content + fields

$present = array();

while(list($f)=@mysql_fetch_row($frs)){
	if(!strstr($f, "!")) $present[$f]=1;
	else{
		if(preg_match_all("/(\!:[^:]+:\!)/", $f, $match)){
			foreach(array_values($match) as $m) {
				foreach($m as $tag){
					$present[substr($tag,2,-2)] = 1;
				}
			}
		}

	}
}

/*print_r("<pre>");
print_r($present);
*/
					
					
					$valPhone=PhoneField("Phone",$item['Phone']);
					$valTollFree=PhoneField("TollFree",$item['TollFree']);
					$valCellular=PhoneField("Cellular",$item['Cellular']);
					$valFax=PhoneField("Fax",$item['Fax']);

					$item[Name] =  stripslashes($item[Name]);
					
					
				// pr($present);
                    
                   


                  
                    
                    
                    if($present['Name']) 
                    {
                    ?>
                      <p class="form-row form-row-wide">
						<label for="username">Name</label>
						
                        <input class="input-text" type="text" size="30" name="Name" value="" />
					
                    </p>
                    
                    <?php
					}
					?>
                    
                    
                    <?php
                     if($present['RetailerName']) 
                    {
                    ?>
                      <p class="form-row form-row-wide">
						<label for="username">Retailer Name</label>
						
                        <input class="input-text" type="text" size="30" name="RetailerName" value="<?php echo $item[RetailerName];?>" />
					
                    </p>
                    
                    <?php
					}
					?>
                    
                    
                      
                    <?php
                     if($present['CompanyName']) 
                    {
                    ?>
                      <p class="form-row form-row-wide">
						<label for="username">Company Name</label>
						
                        <input class="input-text" type="text" size="30" name="CompanyName" value="<?php echo $item[CompanyName];?>" />
					
                    </p>
                    
                    <?php
					}
					?>
                    
                    
                    
                    
                    
                         
                    <?php
                   if($templateid==43)
				{
                    ?>
                     
                      <?php
					 if($present['Title']) 
					 {
					?>
                     
                      <p class="form-row form-row-wide">
						<label for="username">Title</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title" value="<?php echo $item[Title];?>" />
					
                    </p>
                     <?php
					}
					?>
                    
                    <?php
					 if($present['Title2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Title 2</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title2" value="<?php echo $item[Title2];?>" />  optional
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    <?php
}else if($templateid==50)
{
					?>
 
 
 
    <?php
					 if($present['Title']) 
					 {
					?>
                     
                      <p class="form-row form-row-wide">
						<label for="username">Title</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title" value="<?php echo $item[Title];?>" />
					
                    </p>
                     <?php
					}
					?>
                    
                    <?php
					 if($present['Title2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Title 2</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title2" value="<?php echo $item[Title2];?>" />  optional
					
                    </p>
                    <?php
					}
					?>


<?php
}else if($templateid==51)
{

?> 


<?php
					 if($present['Title']) 
					 {
					?>
                     
                      <p class="form-row form-row-wide">
						<label for="username">Title</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title" value="<?php echo $item[Title];?>" />
					
                    </p>
                     <?php
					}
					?>
                    
                    <?php
					 if($present['Title2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Title 2</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title2" value="<?php echo $item[Title2];?>" /> optional
					
                    </p>
                    <?php
					}
					?>


<?php

}else if($templateid==45)
{

?>   

<?php
					 if($present['Title']) 
					 {
					?>
                     
                      <p class="form-row form-row-wide">
						<label for="username">Title</label>
						
                        <input class="input-text" type="text"  maxlength="36" size="36" name="Title" value="<?php echo $item[Title];?>" />
					
                    </p>
                     <?php
					}
					?>
                    
                    <?php
					 if($present['Title2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Title 2</label>
						
                        <input class="input-text" type="text"  maxlength="36" size="33" name="Title2" value="<?php echo $item[Title2];?>" />  optional
					
                    </p>
                    <?php
					}
					?>

 
<?php

}else{

?>


<?php
					 if($present['Title']) 
					 {
					?>
                     
                      <p class="form-row form-row-wide">
						<label for="username">Title</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title" value="<?php echo $item[Title];?>" />
					
                    </p>
                     <?php
					}
					?>
                    
                    <?php
					 if($present['Title2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Title 2</label>
						
                        <input class="input-text" type="text"  maxlength="33" size="36" name="Title2" value="<?php echo $item[Title2];?>" /> optional
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    
                    
  <?php
					 if($present['Photo']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Photo</label>
						
                       <input type="file" name="Photo" >
					
                    </p>
                    <?php
					}
					?>  
                    
 			<?php
					 if($present['CubicleNumber']) 
					 {
					?>
                    
                      <!--<p class="form-row form-row-wide">
						<label for="username">CubicleNumber</label>
						
                       <textarea name="CubicleNumber" cols="90" rows="2"><?php echo $item["CubicleNumber"];?></textarea>
					
                    </p>-->
                    <?php
					}
					?>                                        


<?php

}

?> 
 
 
 
   <?php
					 if($present['DealerName']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Dealer Name</label>
				<input class="input-text" type="text" maxlength="27" size="30"  name="DealerName" value="<?php echo $item["DealerName"];?>"  />
					
                    </p>
                    <?php
					}
					?>
                    
 
 
 		 <?php
					 if($present['Address1']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Address 1</label>
				<input class="input-text" type="text" size="30" name="Address1" value="<?php echo $item[Address1];?>"   />
					
                    </p>
                    <?php
					}
					?>
 
 
 					<?php
					 if($present['Address2']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Address 2</label>
				<input class="input-text" type="text" size="30" name="Address2" value="<?php echo $item[Address2];?>" /> optional
					
                    </p>
                    <?php
					}
					?>
 
 				
                	<?php
					 if($present['City']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">City</label>
				<input class="input-text" type="text" size="20" name="City" value="<?php echo $item[City];?>" /> 
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    
                    <?php
					 if($present['State']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">State</label>
				<input class="input-text" type="text"  size="2" name="State" value="<?php echo $item[State];?>"  /> 
					
                    </p>
                    <?php
					}
					?>
 			
            
            		<?php
					 if($present['Zip']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Zip</label>
				<input class="input-text" type="text"  size="20" name="Zip" value="<?php echo $item[Zip];?>"  /> 
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    <?php
					 if($present['Email']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Email</label>
				<input class="input-text" type="text"  size="30" name="Email" value="<?php echo $item[Email];?>"  /> 
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    
                      <?php
					 if($present['Phone']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Phone</label>
                        <?php echo $valPhone;?>
                        
                          <?php
					 if($present['PhoneExtension']) 
					 {
					?>
                        
					<b>Ext:</b> <input class="input-text" type="text" name="PhoneExtension" size="6" value="<?php echo $item[PhoneExtension];?>" /> 
				
                	<?php
					}
					?>
                
                	
                    </p>
                    <?php
					}
					?>
 			
 
 				  <?php
					 if($present['TollFree']) 
					 {
					?>
                     <p class="form-row form-row-wide">
						<label for="username">Toll Free</label>
                    
                    
                       <?php echo $valTollFree;?>
                        
                          <?php
					 if($present['TFExtension']) 
					 {
					?>
                        
					<b>Ext:</b> <input class="input-text" type="text" name="TFExtension" size="6" value="<?php echo $item[TFExtension];?>" /> 
				
                	<?php
					}
					?>
                    
                     </p>
                    
                    <?php
					}
					?>
                    
                    
                     <?php
					 if($present['Cellular']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Mobile</label>
						<?php echo $valCellular ; ?>
					
                    </p>
                    <?php
					}
					?>
                    
                    
                     <?php
					 if($present['Fax']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Fax</label>
						<?php echo $valFax ; ?>
					
                    </p>
                    <?php
					}
					?> 
                    
                    
                       <?php
					 if($present['WebsiteURL']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Website URL</label>
				<input class="input-text" type="text"  size="30" name="WebsiteURL" value="<?php echo $item[WebsiteURL];?>"   /> 
					
                    </p>
                    <?php
					}
					?>
                    
                    
                    <?php
					 if($present['Facebook']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Facebook</label>
				<input class="input-text" type="text" size="30" name="Facebook" value="<?php echo $item[Facebook];?>"  /> 
					
                    </p>
                    <?php
					}
					?>
                      
                    
                    <?php
					 if($present['Twitter']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username">Twitter</label>
				<input class="input-text" type="text" size="30" name="Twitter" value="<?php echo $item[Twitter];?>"  /> 
					
                    </p>
                    <?php
					}
					?> 
                    
                    
                    <?php
					 if($present['NumYears']) 
					 {
					?>
                    
                      <p class="form-row form-row-wide">
						<label for="username"># of Years Awarded<br />Partner's Circle</label>
				<input class="input-text" type="text" size="3" name="NumYears" value="<?php echo $item[NumYears];?>" maxlength="2"   /> <br />
Only enter a number here if you are currently in the Partner's Circle.
					
                    </p>
                    <?php
					}
					?> 
                     
                    
                    
               <?php
			   if($templateid==50)
				{ // this is only for this template field 
			   ?>     
                  
               
                    
                    
                     
                    
                      <p class="form-row form-row-wide">
						<label for="username">Distribution Center</label>
				
                		<?php 
	$sql_get_warehouse = "select * from warehouse_address where cid = 42 order by warehouse ";
	$rs_warehouse = mysql_query($sql_get_warehouse);
?>	
	<select name="warehouse_id">
		<option value="">Please select</option>
		
<?php 
	while( $warehouse_detail = mysql_fetch_assoc($rs_warehouse) )
	{
		$selected_warehouse = '';
		
		if($warehouse_detail["id"]==$item['warehouse_id'])
		{
			$selected_warehouse = 'selected="selected"';	
		}
?>	
	<option <?php echo $selected_warehouse;?> value="<?php echo $warehouse_detail["id"];?>"><?php echo $warehouse_detail["warehouse"];?></option>

<?php
}
?>	
		 
	</select>
                
					
                    </p>
                     
         
         
              
                <?php
				}
				?>  
         
         
                   <p class="form-row form-row-wide"> 
                  <input type="submit" value="SAVE" /> 
                   
                  </p>
                  
                  
                   <p class="form-row form-row-wide"> 
                   
                  <input type="button" value="Cancel" onclick="self.location.href='index.php?catid=4#all'" />  
                  </p>
                     
                   <?php
				   }
				   ?> 
</form>
                
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


 

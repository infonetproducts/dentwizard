<?
ob_start();
include_once("include/start.php");
include_once("include/adminmenu.php");
include_once("include/common_setting.php");
//print_r($_SESSION);


// figure out business card cat...
if($bccat==''){ list($bccat)=@mysql_fetch_row(mysql_query("select ID from Category where CID=$CID and Name like 'Business%Card%' order by ID limit 1")); }

$AID=$_SESSION['AID'];

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';

function checkUIDCID($uid,$cid){
	return @mysql_fetch_row(mysql_query("select 1 from Users where ID='$uid' and CID=$cid"));
}

if(isset($_REQUEST['getchildren']) && is_numeric($_REQUEST['getchildren'])){
	echo <<<EOM
<table align="right" cellspacing="0" cellpadding="0" width="90%">
EOM;
	$rs = mysql_query("select ID,Name,Active from Category where CID=$CID and ParentID='$_REQUEST[getchildren]' order by Name");
	 $Cats_form = $_GET["gpid"];
	 
	   
	 
	 
	while(list($i,$n,$a)=@mysql_fetch_row($rs)){
		
			if(strstr(",$Cats_form,",",$i,")) $check_box_cat='checked="checked"'; else $check_box_cat='';	
		
		
		echo <<<EOM
<tr><td><div id="div$i" title="click to edit"><input type="checkbox"  name="FormCategoryIDs[]" value="$i" $check_box_cat  /><a class="st" href="javascript:goedit('$i')">$n</a></div></td>
<td width="35" nowrap></td><td width="1%" align="right" nowrap>
</td></tr>
EOM;

	}
	echo "</table>";
	exit;
}

if($action=='budgetexport'){
	$rs = mysql_query("select ID,Name,Email,Budget,BudgetBalance,BudgetRenewDate from Users where CID=$CID and Budget>0");
	$cnt=0;
	header("Content-Type: text/plain");
	while($row=@mysql_fetch_assoc($rs)){
		if(!$cnt) {
			echo implode(",", array_keys($row)) . "\n";
		}
		foreach($row as $k=>$v){ $row[$k] = str_replace(",", "", $v); }
		echo implode(",", array_values($row)) . "\n";
		++$cnt;
	}
	exit;
}
if($action=='glist'){
	$str=mysql_real_escape_string(trim($_REQUEST['str']));
	if(isset($_SESSION['va_CID']))
	{
		$CID = $_SESSION['va_CID'];
	}
	
	 $sql = "select ID,Name from Users where CID=$CID and Name like '%$str%' or Email like '%$str%'" ;
	$rs = mysql_query($sql);
	echo '<select id="bmget" name="BudgetManager"><option value="0">none</option>';
	while (list($i,$n)=@mysql_fetch_row($rs)){
		echo <<<EOM
<option value="$i">$n</option>
EOM;
	}
	echo <<<EOM
</select>
<script>document.getElementById('bmget').focus();</script>
EOM;
	exit;
}

if(isset($_REQUEST['become']) && is_numeric($_REQUEST['become']) && $_SESSION['sysadmin']){
	if(!isset($_SESSION['oriAID'])) $_SESSION['oriAID'] = $_SESSION['AID'];
	$_SESSION['AID'] = $_REQUEST['become'];
	list($cid,$aopt)=@mysql_fetch_row(mysql_query("select CID,AdminOpts from Users where ID='$_REQUEST[become]'"));
	$_SESSION['CID'] = $cid;
    
    
    if(isset($_SESSION["custom"]))
	{
		unset($_SESSION["custom"]);
	}
	if(isset($_SESSION["new_pdf_file_name"]))
	{
		unset($_SESSION["new_pdf_file_name"]);
	}
	if(isset($_SESSION["new_image_file_name"]))
	{
		unset($_SESSION["new_image_file_name"]);
	}
	if(isset($_SESSION["MAXIMUM_PREVIEW_WIDTH"]))
	{
		unset($_SESSION["MAXIMUM_PREVIEW_WIDTH"]);
	}
	if(isset($_SESSION["qty"]))
	{
		unset($_SESSION["qty"]);
	}
	
	if(isset($_SESSION["custom_new"]))
	{
		unset($_SESSION["custom_new"]);
	}
    
    if(isset($_SESSION["Order"]))
	{
		unset($_SESSION["Order"]);
	}
    
    
	if($aopt) header("Location: /admin");
	else header("Location: /");
	exit;
}else{
	
    if(isset($_REQUEST['become']) && is_numeric($_REQUEST['become']) && $_SESSION['super_admin_oriAID']){
	if(!isset($_SESSION['oriAID'])) $_SESSION['oriAID'] = $_SESSION['AID'];
	$_SESSION['AID'] = $_REQUEST['become'];
	list($cid,$aopt)=@mysql_fetch_row(mysql_query("select CID,AdminOpts from Users where ID='$_REQUEST[become]'"));
	$_SESSION['CID'] = $cid;
    
    
    if(isset($_SESSION["custom"]))
	{
		unset($_SESSION["custom"]);
	}
	if(isset($_SESSION["new_pdf_file_name"]))
	{
		unset($_SESSION["new_pdf_file_name"]);
	}
	if(isset($_SESSION["new_image_file_name"]))
	{
		unset($_SESSION["new_image_file_name"]);
	}
	if(isset($_SESSION["MAXIMUM_PREVIEW_WIDTH"]))
	{
		unset($_SESSION["MAXIMUM_PREVIEW_WIDTH"]);
	}
	if(isset($_SESSION["qty"]))
	{
		unset($_SESSION["qty"]);
	}
	
	if(isset($_SESSION["custom_new"]))
	{
		unset($_SESSION["custom_new"]);
	}
    
    if(isset($_SESSION["Order"]))
	{
		unset($_SESSION["Order"]);
	}
    
    
	if($aopt) header("Location: /admin");
	else header("Location: /");
	exit;
}
    
}

if($VendorID) $vendorid = $VendorID;
else {
	if(isset($_REQUEST['v'])) $_SESSION['u_vendorid']=$_REQUEST['v'];
	$vendorid = $_SESSION['u_vendorid'];
	if(!$vendorid)$vendorid='';
}

if($action=='del'){
	$id=$_REQUEST['id'];
	if(!$id) exit;
	if(!checkUIDCID($id,$CID)) die("Error: Parameter mismatch.\n");
	mysql_query("delete from Users where ID='$id'");
	header("Location: users.php");
	exit;
}
if($action=='save'){
	$id=$_REQUEST['id'];
	
	$_POST['Name'] = mysql_escape_string( $_POST['Name']);

if(isset( $_POST['primary_approve'] ))
{
     if($id!=0)
     {
        $sql_primary_approve_check = "select ID from Users where primary_approve = 'Y' and CID = '$CID' and ID !='$id' ";
		
		
     }else{
        $sql_primary_approve_check = "select ID from Users where primary_approve = 'Y' and CID = '$CID'";
     }   
 }   
 
 if($id)
 {
 	// getting user old detail 
	 $sql_get_old_user_detail = "select * from Users where CID = '$CID' and ID ='$id' ";
	 $ori_user_detail = @mysql_fetch_assoc(mysql_query($sql_get_old_user_detail));
	 
	/* print_r("<pre>");
	 print_r($ori_user_detail);
	 
	 die;*/
	 
	
 } 


list($primary_approve_already)=@mysql_fetch_row(mysql_query($sql_primary_approve_check));
if($primary_approve_already) 
{
	die("Error: Primary approver already exits");
}
    
  
   if($id!=0)
   {
   
    /*    $sql_email_check = "select ID from Users where ID != '$id' and Email = '$_POST[Email]' and CID = '$CID'   ";*/
        
             $sql_email_check = "select ID , is_virtual_admin  from Users where ID != '$id' and Email = '$_POST[Email]' and CID = '$CID' and is_virtual_admin = 'N' ";
             
        
        list($echkid)=@mysql_fetch_row(mysql_query($sql_email_check));
        if($echkid) {
       
       /* print_r('<pre>');
        print_r($echkid);
        die;
        */
        
          $sql_get_virtual_admin = "select parent_user_id, is_virtual_admin  from Users where ID = '$id'   ";
         list($parent_user_id,$is_virtual_admin)=@mysql_fetch_row(mysql_query($sql_get_virtual_admin));
         
         if($parent_user_id!="0")
         {
         	$_POST['Password'] = '';
			
         }else{ 
       
	  		 if($_POST['Email']=="pat@leadergraphic.com")
			 {
			 
			 }else{
        
           		 die("Error: Select email invalid or unavailable. Please go back and try again.");
				 
				} 
            
            }
            
        }
   
   }else{
   
   
   		 $sql_email_check = "select ID from Users where Email = '$_POST[Email]' and CID = '$CID'";
		
        
        list($echkid)=@mysql_fetch_row(mysql_query($sql_email_check));
        if($echkid) {
        
        
            die("Error: Select email invalid or unavailable. Please go back and try again.");
            
        }
        
       
        
        
        
        
   
   	
   } 
   
   
    $pw=$_POST['Password'];
	if($pw)
    {
        $pw = mysql_real_escape_string(endecrypt($pw,$pw,''));   
        $sql_email_check = "select ID from Users where Password = '$pw'  and Email = '$_POST[Email]' ";            
        list($echkid_password)=@mysql_fetch_row(mysql_query($sql_email_check));
        if($echkid_password) {
            die("Error: Please enter unique password. Please go back and try again.");
            
        }
   
   } 
   
    
    
	if($id) {
		$sql = "update Users set ";
		if(!checkUIDCID($id,$CID)) die("Error: Parameter mismatch.\n");
		
		list($oriinv,$invlog)=@mysql_fetch_row(mysql_query("select BudgetBalance,budget_balance_log from Users where ID='$id'"));
		
	}else $sql = "insert Users set CID=$CID, ";
	if(preg_match("/[^a-zA-Z0-9_]/", $_POST['Login'])){
		include("htmlhead.php");
		echo <<<EOM
Error: Login name can contain only letters, numbers and the underscore character.<br /><br />
The invalid Login value that you're trying to save now is <b>$_POST[Login]</b><br /><br />
Please go back and make required changes and try again.
EOM;
		include("htmlfoot.php");
		exit;
	}
	foreach($_POST as $k=>$v){
		if($k=='Phone') $v = preg_replace("/[^0-9]+/","",$v);
		if(!is_array($v)) $_POST[$k] = mysql_real_escape_string(trim($v));
	}
	$cats = implode(",", array_values($_POST['FormCategoryIDs']));
	if(is_array($_REQUEST['AdminOpts'])) $adminopts = implode(",", $_REQUEST['AdminOpts']);
	else $adminopts = '';
	if($_POST['BudgetRenewDate']) $budgetdate= "'" . date("Y-m-d",strtotime($_POST['BudgetRenewDate'])) . "'";
	else $budgetdate='null';
	if($_POST['Budget']) $budget="'" . preg_replace("/[^0-9\.]+/","",$_POST['Budget']) . "'";
	else $budget='null';
	$pw=$_POST['Password'];
	if($pw){
		$pw = mysql_real_escape_string(endecrypt($pw,$pw,''));
		$pwsql = "Password='$pw',";
	} else $pwsql='';

	$sql_parent_id_bc_new2 = "  ";

	if(!isset($_POST["is_virtual_admin"]))
    {
    	$_POST["is_virtual_admin"] = "N";
		$_POST["parent_user_id_bc"] = "0";
		
		$sql_parent_id_bc_new2 = " , parent_user_id = '$_POST[parent_user_id_bc]' ";
		
    }
	
	$iq = preg_replace("/[^0-9-]+/","",$_POST['InventoryQuantity']);
	
	$iqsql = ",BudgetBalance=$iq";
	
	if($_POST['InventoryQuantity_user_enter']=="")
	{
		$iq = 'null' ;
		$iqsql = '';
	}
	
	$is_redirect_same_page = '';
	
	if($iq != 'null' && $iq != $oriinv)
	{
		$_POST['BudgetBalance'] = $iq ;
		
		$is_redirect_same_page = 1 ; 
	
		if($id!=0)
		{	
			list($InventoryQuantity_old_for_item_log)=@mysql_fetch_row(mysql_query("select InventoryQuantity from Items where ID='$id'"));
			
			 if($_POST["InventoryQuantity_add_or_sub"]=="pos")
			 {
			 	list($unm)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$_SESSION[AID]'"));
					
				$InventoryQuantity_old_for_item_log_new = $InventoryQuantity_old_for_item_log + $_POST['InventoryQuantity_user_enter'];
				
				$item_quantity = $_POST['InventoryQuantity_user_enter'];
				
				$created_dtm_log = date("Y-m-d H:i:s");
				
				
					
			 }else{
			 
			 list($unm)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$_SESSION[AID]'"));
					
				$InventoryQuantity_old_for_item_log_new = $InventoryQuantity_old_for_item_log - $_POST['InventoryQuantity_user_enter'];
				
				$item_quantity = $_POST['InventoryQuantity_user_enter'];
				
				$created_dtm_log = date("Y-m-d H:i:s");
				 
			 
			 
			 }
		 
		 }
		 
		 $current_inventory_bccc = $oriinv;
	$invchange=$iq - $oriinv;
	$tar = explode("<br>", $invlog);
	if(@count($tar)>=100) array_pop($tar);
	list($unm)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$_SESSION[AID]'"));
		
	if($invchange>0)  $invchange = "+" . $invchange ;
	
	if(!$_POST['changereason']) $_POST['changereason'] = 'none given';
		
		
		if($_POST["InventoryQuantity_add_or_sub"]=="pos")
		{
			
			
	/*	array_unshift($tar, "$unm changed quantity by $invchange $kit_extra_log on " . date("m/d/Y \\a\\t g:ia") . ". Reason: $_POST[changereason] $kit_extra_log_id");
		*/
		
		$log_text = "$unm increased the budget by $$_POST[InventoryQuantity_user_enter] ";
		
		
		array_unshift($tar, "$log_text on " . date("m/d/Y \\a\\t g:ia") . ". Reason: $_POST[changereason] $kit_extra_log_id");
			
		}else{
		
			$log_text = "$unm decreased the budget by $$_POST[InventoryQuantity_user_enter]";

			array_unshift($tar, "$log_text on " . date("m/d/Y \\a\\t g:ia") . ". Reason: $_POST[changereason] $kit_extra_log_id");

		}
		
		
		@reset($tar);
		$invlog=implode("<br>", $tar);
		$invlog=mysql_real_escape_string($invlog);
		$iqsql .= ",budget_balance_log='$invlog'";	
		
		 
		 
	}
	 
	 
	
		

	$company_name = mysql_real_escape_string($_POST['company']);
	$lord_contact = mysql_real_escape_string($_POST['lord_contact']);

	$sql .= <<<EOM
employee_type='$_POST[employee_type]',Status='$_POST[Status]',$pwsql Name='$_POST[Name]',Title='$_POST[Title]',Email='$_POST[Email]',
Dept='$_POST[Dept]', ShipToName='$_POST[ShipToName]',ShipToDept='$_POST[ShipToDept]',
Phone='$_POST[Phone]',Address1='$_POST[Address1]',Address2='$_POST[Address2]',City='$_POST[City]',
State='$_POST[State]',Zip='$_POST[Zip]',FormCategoryIDs='$cats', AdminOpts='$adminopts',
Budget=$budget,BudgetRenewDate=$budgetdate,BudgetPeriod='$_POST[BudgetPeriod]', 
BudgetApprover='$_POST[BudgetApprover]',BudgetManager='$_POST[BudgetManager]',BillCode='$_POST[BillCode]' ,territory_number='$_POST[territory_number]', requisitions_approve_email = '$_POST[requisitions_approve_email]' , group_id =  '$_POST[group_id]' , required_password_reset =  '$_POST[required_password_reset]',
 primary_approve =  '$_POST[primary_approve]' ,  country =  '$_POST[country]'  , company = '$company_name' , lord_contact = '$lord_contact' , is_enable_custom_report = '$_POST[is_enable_custom_report]' , is_view_only = '$_POST[is_view_only]' , is_cubicle_order = '$_POST[is_cubicle_order]'
 $iqsql
EOM;
	
	/*if( ( $_SESSION['sysadmin'] or $_SESSION['va_AID']) && isset($_POST['SysAdmin'])) $sql .= ",VendorID='$vendorid', SysAdmin='$_POST[SysAdmin]', is_virtual_admin = '$_POST[is_virtual_admin]' , parent_user_id = '$_POST[parent_user_id_bc]' ";*/
	
	if( ( $_SESSION['sysadmin'] or $_SESSION['va_AID']) && isset($_POST['SysAdmin'])) $sql .= ",VendorID='$vendorid', SysAdmin='$_POST[SysAdmin]', is_virtual_admin = '$_POST[is_virtual_admin]' $sql_parent_id_bc_new2 ";
    
	if( ( $_SESSION['sysadmin'] or $_SESSION['va_AID']) && isset($_POST['is_become_user_admin'])) $sql .= " , is_become_user_admin = '$_POST[is_become_user_admin]' ";
    
    
    
    if(isset($_POST['custom_order'])) $sql .= ", custom_order='$_POST[custom_order]' ";
    
    if(isset($_POST['is_approve_user'])) $sql .= ", is_approve_user='$_POST[is_approve_user]' ";
    
    
   
    
    
	if($id) $sql .= " where ID='$id'";
#echo $sql;
	mysql_query($sql);
    
    
     if($id)
    {	
    
    	 $sql_user_is_parent = "select parent_user_id from Users  where ID = '$id' ";  
         $rs_user_parent = mysql_query($sql_user_is_parent);
         list($parent_user_id)=@mysql_fetch_row($rs_user_parent);
		 
		 
    
    	// in edit user detail mode
        
        if($parent_user_id==0)
        {
        
       
        
        if(isset($_POST["is_virtual_admin"]) and $_POST["is_virtual_admin"]=="Y")
        {
        	
              //$sql_client_get = "select ID,Name from Clients where ID != '$CID' order by Name"; 
              
               $sql_client_get = "select ID,Name from Clients  order by ID ";  
             $crs = mysql_query($sql_client_get);

            while(list($va_cid,$client_name)=@mysql_fetch_row($crs))
            {
            
            	/* $sql_check_already_account = "select * from Users where parent_user_id = '$id' and is_virtual_admin ='Y' and CID ='$va_cid' ";*/
                
               $user_email =  $_POST["Email"];
                
                $sql_check_already_account = "select * from Users where Email = '$user_email' and is_virtual_admin ='Y' and CID ='$va_cid' and   parent_user_id = '$id' ";
                
               
                 $crs_user = mysql_query($sql_check_already_account);
                 
                 if(mysql_num_rows($crs_user)==0)
                 {
                 	// we need to add virtual system admin user account
                    
                    
            	 $sql_get_system_admin_user = "select * from Users where CID ='$va_cid' and SysAdmin = '1' ";
                
                 $rs_systemadmin_user= mysql_query($sql_get_system_admin_user);
                
                 $user_detail = @mysql_fetch_assoc($rs_systemadmin_user);
                 
                 
                 if(empty($user_detail))
                 {
                 	 $sql_get_system_admin_user = "select * from Users where CID ='$va_cid'  ";
                
                	 $rs_systemadmin_user= mysql_query($sql_get_system_admin_user);
                
                 	 $user_detail = @mysql_fetch_assoc($rs_systemadmin_user);
                 
                 
                 }
                 
                 // if not user exits for client then
                  if(empty($user_detail))
                 {
                 	 $sql_get_system_admin_user = "select * from Users where ID ='$id'  ";
                
                	 $rs_systemadmin_user= mysql_query($sql_get_system_admin_user);
                
                 	 $user_detail = @mysql_fetch_assoc($rs_systemadmin_user);
                 
                 
                 }
                 
                 
                 
                 
                 if(!empty($user_detail))
                 {
                 	$sql_v_s_admin_user = "";
                    
                    
                    
                    foreach($user_detail as $k=>$v)
                    {
                    	if($k=="ID")
                        {
                        	continue;
                        }
                        
                        if($k=="Email")
                        {
                        	$v = $_POST["Email"];
                        }
                        
                        if($k=="Name")
                        {
                        	$v = $_POST["Name"];
                        }
                        
                        if($k=="CID")
                        {
                        	$v = $va_cid;
                        }
                        
                         if($k=="AdminOpts")
                        {
                       		 if($va_cid==50)
                             {
                             	$AdminOpts = "1,2,3,4,5,6,7,12,11,200,300";
                             }else{
                             	$AdminOpts = "1,2,3,4,5,6,7,12,11";
                             }
                             
                             $AdminOpts = mysql_escape_string($AdminOpts);
                             
                        	$v = $AdminOpts;
                        }
                        
                        if($k=="Budget")
                        {
                        	continue;
                        	$v='';
                        }
                        
                        if($k=="BudgetBalance")
                        {
                        	continue;
                        	$v='';
                        }
                        
                        
                         if($k=="Password")
                        {
                        	$v = '';
                        }
                        
                         if($k=="parent_user_id")
                        {
                        	$v = $id;
                        }
                        
                         if($k=="is_virtual_admin")
                        {
                        	$v = "Y";
                        }
                        
                        
                        if($sql_v_s_admin_user=="")
                        {
                        	$sql_v_s_admin_user = " $k = '$v' ";
                        }else{
                        	$sql_v_s_admin_user .= " , $k = '$v' ";
                        }
                        
                    }
                    
                     $sql_v_s_admin_user_final = '';
                    $sql_v_s_admin_user_final = "insert into Users SET ".$sql_v_s_admin_user;
                    
                    //echo $sql_v_s_admin_user_final;
                    //die;
                    if($sql_v_s_admin_user!="")
                    {
                    	mysql_query($sql_v_s_admin_user_final);
                    }
                    
                    
                 }
                 
                 
                 
                    
                    
                 }
            
            }
            
            
            
             
        }else{
        	  $sql_delete_virtual_systemadmin_user = "delete from Users where parent_user_id = '$id' and is_virtual_admin ='Y' ";  
            mysql_query($sql_delete_virtual_systemadmin_user);
        }
        
        }
        
    
    }
    
	
	
	
		
	 
			
		// start store log for user 
		if($id)
		{
			$id_all_type = $id;
			$log_type = 'update_user';
			$action_title_log ='Update User';
			$created_dtm_log = date('Y-m-d H:i:s');
			
		 	$sql_log ="INSERT INTO user_budget_log_history SET
			
								cid ='$CID',
								user_id ='$_SESSION[AID]',
								action_title ='$action_title_log',
								log_type ='$log_type',
								id ='$id_all_type',
								created_dtm ='$created_dtm_log'
										";
										
				mysql_query($sql_log)or die(mysql_error());	
					
				 $log_id = mysql_insert_id();
				
				 
				
				foreach($ori_user_detail as $k_db=>$v_db)
							{
							
								if(isset($_POST[$k_db]))
								{
									$v_form = $_POST[$k_db];
									
									if($k_db=='FormCategoryIDs')
									{
										$v_form = implode(',',$v_form);
									}
									
									if($k_db=='AdminOpts')
									{
										$v_form = implode(',',$v_form);
									}
									
									if($k_db=='BudgetRenewDate')
									{
										$v_form  = date( 'Y-m-d' , strtotime($v_form ) );
									}
									
									if($k_db=='Password')
									{
										if($v_form =="")
										{
											continue;
										}
									}
									
									
									
								
										if($v_db!=$v_form)
											{
												$sql_log_history ="INSERT INTO user_budget_log_history_detail SET
												log_id ='$log_id',
												field_name ='$k_db',
												old_value ='$v_db',
												new_value ='$v_form',
												created_dtm ='$created_dtm_log'
												";
												mysql_query($sql_log_history)or die(mysql_error());
											}	
								}
							
							}
			
			
			
			
			//die('need to store log');	
		}
		// end store log for user
	
	if($is_redirect_same_page==1)
	{
		
		header("Location: users.php?a=edit&id=$id");
		exit;
		
	}else{
    
		header("Location: users.php");
		exit;
	
	}
}

include("htmlhead.php");
include("menu.php");


$sstring='';
if(isset($_REQUEST['sstring'])) $sstring = trim($_REQUEST['sstring']);
elseif(isset($_SESSION['usstring'])) $sstring = $_SESSION['usstring'];

$_SESSION['usstring'] = $sstring;

if(!$action || $action=='find') {
	searchForm($sstring);
}elseif($action=='edit'){
	$id = $_REQUEST['id'];
	editForm($id);
}

include("htmlfoot.php");

///////////////////////////////////////////////////////
// editForm - edit a Form entry.
function editForm($id){
	global $vendorid,$VendorID,$CID,$billcodes;
	if($id) {
		if(!checkUIDCID($id,$CID)) die("Error: Parameter mismatch.\n");
		$form = @mysql_fetch_assoc(mysql_query("select * from Users where ID='$id'"));
	}
	if($form['VendorID']) $vendorid=$form['VendorID'];
    
    if($_GET['id']==0)
    {
    	$page_tt = 'Add User';
    }else{
    	$page_tt = 'Edit User';
    }
    
	echo <<<EOM
<h3 align="center">$page_tt</h3>
<form name="itmform" id="itmform"  method="post" action="users.php">
<input type="hidden" name="a" value="save" />
<input type="hidden" name="v" value="$vendorid" />
<input type="hidden" name="id" value="$id" />
<style>
.grid th { text-align:right; border-bottom:1px solid #bbbbbb;font-weight:normal; }
.grid td { border-bottom: 1px solid #bbbbbb; }
</style>
<table width="100%" cellspacing="0" cellpadding="6" class="grid"
	style="border:1px solid gray; boder-bottom:0px;background-color:#f5f5f5;">
<tr><td colspan="2" align="center" style="background:$GLOBALS[mycolor];">
<input type="button" onclick="this.form.submit()" value="SAVE" />
<input type="button" onclick="history.go(-1)" value="Cancel" />
</td></tr>
<tr><th>Status</th><td> <select name="Status">
EOM;

	if($CID==42)
    {
    	$statusary = array('Inactive','UnConfirmed','Active','Deleted');

    }else{

        $statusary = array('Inactive','UnConfirmed','Active');
    
    }
    
    
   if($form[is_register]==1)
   {
   		$statusary = array();
   		$statusary[0] = 'PENDING APPROVAL';
        $statusary[2] = 'ACTIVE';
        $statusary[3] = 'DENIED';
        
        
        if( $form[Status]==3)
       {
             $statusary = array();
             $statusary[3] = 'DENIED';
       }
       
       
        if( $form[Status]==2)
       {
             $statusary = array();
             $statusary[2] = 'ACTIVE';
       }
   }
   
   
   
    
    
	foreach($statusary as $k=>$v){
		if($k == $form['Status'])
        {
      		if($_GET['id']!=0)
         	{
         		$ch='selected="selected"'; 
            }
        }
         else{
         
         	$ch='';
         }
         
        
         if($_GET['id']==0)
         {
         	if($k == 2)
            {
                $ch='selected="selected"'; 
            }
         }
         
         
         
		echo "<option value=\"$k\" $ch>$v</option>";
	}
	echo <<<EOM
</select></td></tr>
EOM;

if(isset($_SESSION['va_CID']))
{

	$rs = mysql_query("select ID,CompanyName from Vendors order by CompanyName");
	$vopts = "";
	while(list($i,$cn)=@mysql_fetch_row($rs)){
		if($vendorid==$i)$ch='selected="selected"'; else $ch='';
		$vopts .= "<option value=\"$i\" $ch>$cn</option>";
	}
	//$disp='none';
	//if($vendorid) $disp = 'block';
	if($_SESSION['sysadmin']){
		echo <<<EOM
<tr><th>Vendor</th><td><select name="v"><option value="">NOT A VENDOR</option>$vopts</select></td></tr>
EOM;
	}
    
}
    
    
	echo <<<EOM
<tr><th>Access</th><td><div>
EOM;
#print_r($GLOBALS[menuitems]);
	require("include/adminmenu.php");
	$mu=$adminmenuitems;
	array_pop($mu);
	foreach($mu as $idx => $v){
		$idx = trim($idx);
		if($idx=='0') continue; // exclude 'Home' tab that non-admin admin has.
		if($idx=='15') continue; // no client perm. it's manually added to menu.
		$lbl = $v[0];
		if(strstr(",$form[AdminOpts],", ",$idx,")) $ch='checked="checked"'; else $ch='';
		echo <<<EOM
<label><input type="checkbox" name="AdminOpts[]" value="$idx" $ch />$lbl</label> &nbsp;
EOM;
	}
	echo <<<EOM
</div>
</td></tr>
EOM;



/*$depts = depts($form['Dept']);
<tr><th>Department/Area</th><td>
<select name="Dept">$depts</select>
</td></tr>
<tr><th>ShipTo Name</th><td><input type="text" size="20" name="ShipToName" value="$form[ShipToName]" /></td></tr>
<tr><th>ShipTo Department</th><td>
<input type="text" size="40" name="ShipToDept" value="$form[ShipToDept]" />
</td></tr>
*/
	if($_SESSION['sysadmin'] or $_SESSION['va_AID']){
		$ch0=$ch1='';
        $v_ch0=$v_ch1='';
		if($form['SysAdmin']) $ch1='checked="checked"'; else $ch0='checked="checked"';
        if($form['is_virtual_admin']=="Y") $v_ch1='checked="checked"'; else $v_ch0='checked="checked"';
		
		$v_ch_user0=$v_ch_user1='';
		 if($form['is_become_user_admin']=="Y") $v_ch_user1='checked="checked"'; else $v_ch_user0='checked="checked"';
        
		echo <<<EOM
<tr><th><!--System--> Admin</th><td>
<label><input type="radio" name="SysAdmin" value="1" $ch1 />Yes</label>
<label><input type="radio" name="SysAdmin" value="0" $ch0 />No</label>
</td></tr>



EOM;



if($_GET["id"]!=0)
{

list($bc_new_is_super_admin)=@mysql_fetch_row(mysql_query("select is_virtual_admin from Users where ID='$_SESSION[AID]'"));

if($bc_new_is_super_admin=="Y")
{
echo <<<EOM
<tr><th>Super Admin <!--Virtual--> </th><td>
<label><input type="radio" name="is_virtual_admin" value="Y" $v_ch1 />Yes</label>
<label><input type="radio" name="is_virtual_admin" value="N" $v_ch0 />No</label>
</td></tr>

<tr><th>Become User <!--Virtual--> </th><td>
<label><input type="radio" name="is_become_user_admin" value="Y" $v_ch_user1 />Yes</label>
<label><input type="radio" name="is_become_user_admin" value="N" $v_ch_user0 />No</label>
</td></tr>
EOM;

}
}

	}
    
    
	if($form['BudgetRenewDate']){
		$renew = date("m/d/Y", strtotime($form['BudgetRenewDate']));
	}
	$bpch[$form['BudgetPeriod']] = 'selected="selected"';
	
    
   $ch0=$ch1='';
		if($form['custom_order']) $ch1='checked="checked"'; else $ch0='checked="checked"';
		echo <<<EOM
<tr><th>Custom Order</th><td>
<label><input type="radio" name="custom_order" value="1" $ch1 />Yes</label>
<label><input type="radio" name="custom_order" value="0" $ch0 />No</label>
EOM;

$custom_report_enable_checked = '';
if(isset($form["is_enable_custom_report"]) and $form["is_enable_custom_report"]=="Y")
{
	$custom_report_enable_checked = 'checked="checked"';

}

echo <<<EOM
<label>&nbsp;&nbsp;<input $custom_report_enable_checked  type="checkbox" name="is_enable_custom_report" value="Y" id="is_enable_custom_report" />Enable Custom Order Report </label>

</td></tr>
EOM;

$chk_cubicle_enable = '';
	 
          
		echo <<<EOM
<tr><th>Cubicle Order Link</th><td>
EOM;

$chk_cubicle_enable = '';
if(isset($form["is_cubicle_order"]) and $form["is_cubicle_order"]=="1")
{
	$chk_cubicle_enable = 'checked="checked"';

}

echo <<<EOM
<label><input $chk_cubicle_enable  type="checkbox" name="is_cubicle_order" value="1" id="is_cubicle_order" />Enable </label>

</td></tr>
EOM;




 $ch0=$ch1='';
		if($form['is_approve_user']) $ch1='checked="checked"'; else $ch0='checked="checked"';
        
       




		echo <<<EOM
<tr><th>Approve Users</th><td>
<label><input type="radio" name="is_approve_user" value="1" $ch1 />Yes</label>
<label><input type="radio" name="is_approve_user" value="0" $ch0 />No</label>
EOM;



 $primary_approve_checked = '';
if(isset($form["primary_approve"]) and $form["primary_approve"]=="Y")
{
	$primary_approve_checked = 'checked="checked"';

}

echo <<<EOM
<label>&nbsp;&nbsp;<input $primary_approve_checked  type="checkbox" name="primary_approve" value="Y" id="primary_approve" />Primary Approver</label>


</td></tr>
EOM;


 		$chk_view_only = '';
	 
          
		echo <<<EOM
<tr><th>View Only</th><td>
EOM;

$chk_is_view_only = '';
if(isset($form["is_view_only"]) and $form["is_view_only"]=="1")
{
	$chk_is_view_only = 'checked="checked"';

}

echo <<<EOM
<label><input $chk_is_view_only  type="checkbox" name="is_view_only" value="1" id="is_view_only" />Enable </label>

</td></tr>
EOM;





/* Balance: \$<input type="text" size="9" name="BudgetBalance" value="$form[BudgetBalance]" />
*/


    echo <<<EOM
<tr><th><h3>Budget</h3></th><td>
Amount: \$<input type="text" size="9" name="Budget" value="$form[Budget]" /> <small><b><i>(Leave blank for no budget.)</i></b></small>
<br />

Renews on: <input type="text" size="10" name="BudgetRenewDate" id="BudgetRenewDate" value="$renew" />
<img align="absmiddle" src="images/calendar.png" style="cursor:pointer" onclick="displayDatePicker('BudgetRenewDate');" />
&nbsp;
Period: <select name="BudgetPeriod">
	<option $bpch[year] value="year">Yearly</option>
	<option $bpch[month] value="month">Monthly</option>
	<option $bpch[week] value="week">Weekly</option>
</select>
<br />
Approver Email: <input type="text" size="60" name="BudgetApprover" value="$form[BudgetApprover]" />
EOM;
	$repmanname='none';
	if($form['BudgetManager']>0) list($repmanname)=@mysql_fetch_row(mysql_query("select Name from Users where ID='$form[BudgetManager]'")); 
	echo <<<EOM
<br />
Report Manager: <input type="text" size="6" id="repman" onchange="if(this.value.length>=3)lC('repmanspan','users.php?a=glist&str='+this.value)" />
<span id="repmanspan"><select id="bmget" name="BudgetManager"><option value="0">none</option><option value="$form[BudgetManager]" selected="selected">$repmanname</option></select></span>
<small><i>(type 3+ letters of name or email and press TAB to fill list)</i></small><br/>
<a href="#" onclick="window.open('budget_manager.php?id=$id', '_blank', 'width=860,height=590')" style="color:blue;">View Spending Report & Enter Manual Budget Entries</a>
</td></tr>
EOM;


 
echo <<<EOM
<tr class="qty"><th>Balance: \$</th><td> 
<input type="hidden" name="InventoryQuantity" value="$form[BudgetBalance]" />
<input type="hidden" name="InventoryQuantity_user_enter" value="" />
<input type="hidden" name="InventoryQuantity_add_or_sub" value="" />
<b>$form[BudgetBalance]</b>
<input type="hidden" name="changereason" value="$form[BudgetBalance]" />
Add or substract:
<input type="text" size="6" id="addinventory" id="addinventory" value="0" /> 
EOM;

echo <<<EOM
<input type="button" value=" + " onclick="addinv('pos')" /> <input type="button" value=" - " onclick="window.open('budget_manager.php?id=$id', '_blank', 'width=860,height=590')" />
<span style="font-size:11px;" onclick="tglDiv('ilog')">Changes to this value are logged.</span><div id="ilog" style="display:none">
$form[budget_balance_log] 
</div></small></td></tr>

 
<script>
tglDiv=function(divid){
	var d=document.getElementById(divid);
	if(d.style.display=='none') d.style.display='block'; else d.style.display='none';
}

addinv=function(act){
	var v=parseInt(document.getElementById('addinventory').value);
	if(v && v != 0) {
		var r=prompt("Are you sure you want to do that? If so, enter an optional reason and click OK. Else Cancel.","");
		if(r==null) return;
		var ov=parseInt(document.itmform.InventoryQuantity.value);
		if(!ov)ov=0;
		if(act=='pos') ov+=v;
		else ov-=v;
		document.itmform.changereason.value=r;
		document.itmform.InventoryQuantity.value=ov;
		document.itmform.InventoryQuantity_user_enter.value=v;
		document.itmform.InventoryQuantity_add_or_sub.value=act;
		document.itmform.submit();
	}
}
</script>
EOM;


  $sql_req= "SELECT requisitions FROM Clients where ID = '$CID' ";
 $rs_req = mysql_query($sql_req);
 list($requisitions)=@mysql_fetch_row($rs_req);
    
  if($requisitions==1)
 
 {

 echo <<<EOM
<tr><th><h3>Requisitions </h3></th><td>
<br />
Approver Email: <input type="text" size="60" name="requisitions_approve_email" value="$form[requisitions_approve_email]" />
</td></tr>
EOM;

}

$form[Name] = toSafeDisplay_edit_time($form[Name]);

 echo <<<EOM
 
<tr><th>Title</th><td><input type="text" size="40" name="Title" value="$form[Title]" /></td></tr>

<tr><th>Name</th><td><input type="text" size="30" name="Name" value="$form[Name]" /></td></tr>
<tr><th>Company Name</th><td><input type="text" size="30" name="company" value="$form[company]" /></td></tr>
EOM;


if($CID==42)
{
global $employee_type_arr;
?>

<tr>
	<th>Employee Type</th>
	<td>
		 <select name="employee_type" id="employee_type">
		 	<option value="">Please select </option>
			<?php 
			
			foreach($employee_type_arr as $k_e_type=>$v_e_type)
			{
				$selected_e_type = '';
				if($form[employee_type]==$k_e_type)
				{
					$selected_e_type = 'selected="selected"';
				}
				
				
			?>
				<option  <?php echo $selected_e_type;?> value="<?php echo $k_e_type;?>"><?php echo $v_e_type;?></option>
			<?php 
			}
			?>
			
			
		 </select>
		 
		 <br/><?php echo $error['employee_type'];?>
	</td>
</tr>


<?php
}


if($CID==44)
{
echo <<<EOM
<tr><th>LORD Contact</th><td><input type="text" size="30" name="lord_contact" value="$form[lord_contact]" />
EOM;
}
echo <<<EOM
<tr><th>Bill Code</th><td><select name="BillCode"><option value="0">none</option>
EOM;




	@reset($billcodes);
	foreach($billcodes as $k=>$v){
		if($k==$form['BillCode']) $ch='selected="selected"';
		else $ch='';
		echo <<<EOM
<option value="$k" $ch>$v</option>
EOM;
	}
	echo <<<EOM
</select></td></tr>
EOM;

if($CID==112)
{
echo <<<EOM
<tr><th>Territory Number</th><td><input type="text" size="30" name="territory_number" value="$form[territory_number]" />
EOM;
}


echo <<<EOM
<tr><th>Email</th><td><input type="text" size="40" name="Email" value="$form[Email]" /></td></tr>
<tr><th>Phone</th><td><input type="text" size="20" name="Phone" value="$form[Phone]" /></td></tr>
<tr><th>Address 1</th><td><input type="text" size="40" name="Address1" value="$form[Address1]" /></td></tr>
<tr><th>Address 2</th><td><input type="text" size="40" name="Address2" value="$form[Address2]" /></td></tr>
<tr><th>City</th><td><input type="text" size="20" name="City" value="$form[City]" /></td></tr>
<tr><th>State</th><td><input type="text" size="3" name="State" value="$form[State]" /></td></tr>
<tr><th>Zip</th><td><input type="text" size="15" name="Zip" value="$form[Zip]" /></td></tr>

EOM;

?>

<tr><td align="right">Country</td><td>
    
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
        
		
		
		  
		<?php
		if($CID =="209" and $_GET['id']==0)
		{
		 ?>
        
			<option selected="selected"  value="Canada">Canada</option>
		<?php 
		}else{
		?>
		
			<option  value="Canada">Canada</option>
		
		<?php 
		}
		?>
		
		
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
       
		
		<?php
		if($CID !=209  )
		{
		 ?>
        
		<option  value="United States" selected="selected" >United States</option>
		
		<?php }else{ ?>
		
		<option  value="United States"  >United States</option>
		
		<?php
		}
		?>
		
		
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
      <?php // echo $row["country"];?>
      <br/>
      
        <?php echo $error[country];?>
    </td>
  </tr>
  
  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript">
  
  var opts = $('#country')[0].options;
for(var a in opts) { if(opts[a].value == '<?php echo $form["country"];?>') { $('#country')[0].selectedIndex = a; break; } }
  
  </script>

<tr>

<?php
if($form["parent_user_id"]==0)
 {
echo <<<EOM
<tr><th>Password</th><td><input type="text" size="15" maxlength="15" name="Password" value="" />

EOM;

 }

$required_password_checked = '';
if(isset($form["required_password_reset"]) and $form["required_password_reset"]=="Y")
{
	$required_password_checked = 'checked="checked"';

}


if($CID==44 and $_GET["id"]!=0)
{


/*if($form["required_password_reset"]=="Y" or $form["required_password_reset"]=="" )
{*/

if(isset($form["required_password_reset"]) and $form["required_password_reset"]=="Y")
{
?>

<input type="hidden" value="Y" name="required_password_reset" id="required_password_reset" />

<?php
}
?>

<!--<input <?php echo $required_password_checked;?>  type="checkbox" name="required_password_reset" id="required_password_reset" value="Y" /> Require Password Reset-->

<?php
//}

}
?>

<?php
echo <<<EOM
</td></tr>
EOM;

?>


<th width="100">User Group</th>

<td>

<?php
//and user_id = '$AID'
$AID = $_SESSION["AID"];
 $sql_user = "SELECT * 
FROM  `groups` where CID = '$CID'  ";
?>
<select name="group_id" id="group_id">

<option value="">None</option>

<?php


		$rs_group = mysql_query($sql_user);

while($row_group = mysql_fetch_assoc($rs_group))
{
	$seleted_group = ''; 
	if( isset($_POST["group_id"]) and $_POST["group_id"]!="" )
	{
		if( $row_group["group_id"] == $_POST["group_id"] )
		{
			$seleted_group = 'selected="selected"'; 	
		}
		
	}else{
		
		if( $form["group_id"] == $row_group["group_id"] )
		{
			$seleted_group = 'selected="selected"'; 	
		}
		
	}
	
	?>
    
    <option  <?php echo $seleted_group;?> value="<?php echo $row_group["group_id"];?>"><?php echo $row_group["group_name"];?></option>
    
    <?php
}
	?>

</select>

<small><i>(selecting a user group will override any categories selected below)</i></small><br/>
</td>

</tr>

<?php


	function listCats($Cats_form){
		
		
	global $CID;
	echo <<<EOM
<style>
#grid th { text-align:left; border-bottom:1px solid black; padding:5px;background:$GLOBALS[mycolor];}
#grid td { border-bottom:1px solid #bbbbbb; padding:3px;}
.plus { font-size:14px; color:black; text-decoration:none; border:1px outset gray; padding:0px 3px 0px 3px; }
.st { text-decoration:none; color:blue; }
</style>
<script type="text/javascript" src="../javascript.js"></script>
<script>
tglActive=function(cid){
	var el=document.getElementById('actlink'+cid);
	var cv=el.innerHTML;
	var nv='Y';
	if(/Y/.test(cv)) nv='N';
	var x=getXMLObj();
	x.open('get','cats.php?setactive='+cid+'&val='+nv, true);
	x.onreadystatechange=function(){ if(x.readyState=='4') el.innerHTML=nv; }
	x.send(null);
}
godel=function(eid){
	if(confirm('Are you sure you want to delete that?'))
		self.location.href='cats.php?a=del&id='+eid;
}
gosave=function(eid){
	var val=document.getElementById('Name'+eid).value;
	document.getElementById('div'+eid).onclick=function(){ goedit(eid); }
	if(val && val != ''){
		var x=getXMLObj();
		x.open('get','cats.php?a=save&id='+eid+'&Name='+escape(val), true);
		x.onreadystatechange=function(){ if(x.readyState=='4') { document.getElementById('div'+eid).innerHTML='<a class="st" href="javascript:goedit(\''+eid+'\')">'+val+'</a>'; } }
		x.send(null);
	}
}
unedit=function(eid,val){
	var el = document.getElementById('div'+eid);
	el.innerHTML='<a class="st" href="javascript:goedit(\''+eid+'\')">'+val+'</a>';
}
goedit=function(eid){
	var el=document.getElementById('div'+eid);
	var lnk=el.firstChild;
	var val=lnk.innerHTML;
	if(!val || val=='') { return; }
	if(/\<input/.test(val)) { return; }
	var str='';
	str += '<nobr><input type="text" size="25" id="Name'+eid+'" value="'+val+'" />';
	str += '<input type="button" value="save" onclick="gosave(\''+eid+'\')" />';
	str += '<input type="button" value="cancel" onclick="unedit(\''+eid+'\',\''+val+'\')" /></nobr>';
	el.innerHTML=str;
}
clickadd=function(){
	var el=document.getElementById('Name');
	if(el.value=='add new') {
		el.style.color='black';
		el.value='';
	}
}
getchild=function(pid,groupid){
	var elid='child'+pid;
	var el=document.getElementById(elid);
	if(el.innerHTML.length>5) { el.innerHTML=''; el.parentNode.parentNode.style.display='none'; return; }
	el.parentNode.parentNode.style.display=''; 
	lC(elid,'users.php?getchildren='+pid+'&gpid='+groupid)
}
newparent=function(eid){
	var posel=document.getElementById('np'+eid);
	var offset=getOffset(posel);
	var cp=document.getElementById('chooseparent');
	var cpnm=document.getElementById('XXX');
	cpnm.innerHTML=document.getElementById('div'+eid).firstChild.innerHTML;
	cp.style.top=(offset.top - 140) + 'px';
	cp.style.left=(offset.left - 300) + 'px';
	cp.style.display='block';
	lC('parentItems', 'cats.php?getparents='+eid);
	
}
function getOffset( el ) {
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
}
</script>
<br />
EOM;
	$sql="select a.ID,a.Name,a.Active,count(b.ID) from Category a left join Category b on b.ParentID=a.ID where a.CID=$CID and a.ParentID=0 group by a.ID order by a.Name";
	$rs = mysql_query($sql);
#	$numc=@mysql_num_rows($rs);
#	if($numc==1)$s='y'; else $s='ies';
#<div align="center"><b>$numc Categor$s Displayed</b></div>



	echo <<<EOM
<h3 align="center">Item Categories</h3>





<table id="grid" cellspacing="0" align="center" style="border:1px solid gray;min-width:60%">

EOM;


	while(list($i,$n,$a,$children)=@mysql_fetch_row($rs)){
    
    
		
       if(strstr(",$Cats_form,",",$i,")) $check_box_cat='checked="checked"'; else $check_box_cat='';	
    
    
		$divlink='&nbsp;';
		if($children>0) $divlink="<a href=\"javascript:getchild('$i','$Cats_form')\" class=\"plus\">+</a> ";
		echo <<<EOM
<tr><td width="25">$divlink</td><td><div id="div$i" title="click to edit"><input type="checkbox" name="FormCategoryIDs[]"  $check_box_cat value="$i" /><a class="st" href="javascript:goedit('$i')">$n</a></div></td>
<td nowrap width="1%"></td><td align="right" width="1%" nowrap>
EOM;
		
		echo <<<EOM



</td></tr>
EOM;
		if($children>0){
			echo <<<EOM
<tr style="display:none;"><td colspan="4"><div id="child$i"></div></td></tr>
EOM;
		}
	}
    
    
    
	echo <<<EOM
  

</table>


<div id="chooseparent" style="position:absolute; width:420px; height:400px; overflow:auto; border:1px outset gray; background-color:#ededed; padding:5px; display:none;">
Select Parent Category for <b><span id="XXX"></span></b><br />
<div id="parentItems"></div>
</div>
<script>
var tr=document.getElementById('grid').getElementsByTagName('tr');
for(var a=1; a<tr.length; a++){
	tr[a].onmouseover=function(){this.style.backgroundColor='lightyellow';}
	tr[a].onmouseout=function(){this.style.backgroundColor='';}
}
</script>
EOM;

	
}

echo <<<EOM
<tr valign="top"><td colspan="2">
EOM;

 $cats = $form['FormCategoryIDs'];
listCats($cats );

echo <<<EOM
</td>
</tr>
EOM;


 
 echo <<<EOM
 </table>
</td></tr>
<tr><td colspan="4" align="center" style="background:$GLOBALS[mycolor];">
<input type="button" onclick="this.form.submit()" value="SAVE" />
<input type="button" onclick="history.go(-1)" value="Cancel" />
</td></tr>
</table>
</form>

EOM;
}
// searchForm($val) prints the search form, and if $val != '', lists whatever results are found.
function searchForm($val){

	global $vendorid,$CID,$bccat;
	$val=trim($val);
	if(!isset($_SESSION['sysadmin']) || !$_SESSION['sysadmin']) $vendorid='';
	echo <<<EOM
<style>
#grid th { text-align:left; border-bottom:1px solid black; padding:5px;background:$GLOBALS[mycolor];}
#grid td {  border-bottom:1px solid #bbbbbb; padding:3px;}
#grid tr { vertical-align: top;	}
</style>
<div style="text-align:center;font-weight:bold;padding:12px;">
<form method="get" action="users.php">
<input type="hidden" name="a" value="find" />
<input id="sstring" type="text" name="sstring" value="$val" size="20" />
<input type="submit" value="Search Users" />
<input type="button" value="Add User" onclick="self.location.href='users.php?a=edit&id=0'" />

<input type="button" value="Manage User Groups" onclick="self.location.href='group.php'" />

<a target="_new" href="users.php?a=budgetexport">Budgets</a>
</form>
<script>document.getElementById('sstring').focus();</script>
</div>
EOM;
// parent_user_id = 0 and 

  $sql_sub_account_display = 'parent_user_id = 0 and' ;	
  if(isset($_SESSION['va_AID']) and isset($_GET['sub_ac']))
  {
  	 $sql_sub_account_display = '';
  }
  
  $sql_deleted_account = "";
  
  if($CID==42)
  {
  	$sql_deleted_account = "  and Status !=3 ";
  }
  

	if(1==1||$val || isset($_REQUEST['sstring'])){
     $sql_user = "select * from Users where $sql_sub_account_display  CID=$CID and (
			Name like '%$val%' or
			Login like '%$val%' or
			Email like '%$val%' or
			Dept like '%$val%' or
			City = '$val' or
			FormCategoryIDs like '%$val%'
			)
           
           $sql_deleted_account
           
			and (VendorID='$vendorid' or '$vendorid'='' or '$vendorid'='0')
			order by Name";
		$rs = mysql_query($sql_user);
		$numu=@mysql_num_rows($rs);
		if($numu==1) $s=''; else $s='s';
		echo <<<EOM
<script>
godel=function(fid){
	if(confirm('Are you sure you want to delete that?')){
		self.location.href='users.php?a=del&id='+fid;
	}
}
</script>
EOM;
		if($vendorid){
			list($vnm)=@mysql_fetch_row(mysql_query("select CompanyName from Vendors where ID='$vendorid'"));
			echo <<<EOM
<div align="center">Users for Vendor: <b>$vnm</b></div>
EOM;
		}
        
        $display_account="";
        if(isset($_SESSION['va_AID']))
        {
        
        $display_account= "<div align='left'><a href='users.php?v=&sub_ac=1'>Display Super Admin Accounts</a></div>";
        
        }
        
		echo <<<EOM
$display_account
<div align="center"><b>$numu User$s Displayed</b></div>
<table id="grid" cellspacing="0" style="width:100%;border:1px solid gray;">
<tr><th>Name</th><th>Location</th><th>Phone</th><th>Email</th><th>&nbsp;</th></tr>
EOM;
		while($row=@mysql_fetch_assoc($rs)){
			$ph = formatPhone($row['Phone']);
            
            $is_virtual_super_admin = "";
            if($row["parent_user_id"]!=0)
            {
               $is_virtual_super_admin = "(Super Admin)";
            }
            
			
		$row[Name] = toSafeDisplay_edit_time($row[Name]);
            
			echo <<<EOM
<tr><td>
<a href="users.php?a=edit&id=$row[ID]">$row[Name] </a> $is_virtual_super_admin</td>
<td>$row[City], $row[State]</td>
<td>$ph</td>
<td nowrap>$row[Email]</td><td nowrap align="right">
EOM;
			
            
            
       $user_aid = $_SESSION["AID"] ;
	list($is_virtual_admin,$SysAdmin ,$is_become_user_admin  )=@mysql_fetch_row(mysql_query("select is_virtual_admin , SysAdmin , is_become_user_admin  from Users where ID='$user_aid'"));
            
           // if( $SysAdmin=="1" and $row['is_virtual_admin'] =="Y")
		   
            if( $row['SysAdmin']==1 and $row['is_virtual_admin'] =="Y" and $is_virtual_admin=="Y")
            {
         
            	if($row["parent_user_id"]==0)
                {
				
				
            
            echo <<<EOM
<img align="absmiddle" src="images/dashboard_seeting_icon.png" onclick="popupwindow('dashboard_setting_popup.php?aid=$row[ID]','Required File Upload Detail','700','400');">
&nbsp;
EOM;
       			 }   
           
            
            }
            
            $cids = array_flip(explode(",", $row['FormCategoryIDs']));
			if(isset($cids[$bccat])){
				echo <<<EOM
<a href="prod.php?a=find&itmuid=$row[ID]"><img align="absmiddle" border="0" alt="business cards" title="business cards" src="images/edit_bc.png" /></a>
&nbsp;
EOM;
			}
			
			
			
			if($_SESSION['va_AID']) {
			 echo <<<EOM
<a href="users.php?become=$row[ID]">become</a> 
&nbsp;
EOM;

}else if($_SESSION['sysadmin'] and $is_become_user_admin=='Y'){
 echo <<<EOM
<a href="users.php?become=$row[ID]">become</a> 
&nbsp;
EOM;
}


			echo <<<EOM
<a href="users.php?a=edit&id=$row[ID]"><img align="absmiddle" border="0" src="images/edit_user.png" alt="edit" title="edit user" /></a>
&nbsp;
EOM;

if($row["parent_user_id"]==0)
{
echo <<<EOM
<a href="javascript:godel('$row[ID]')"><img align="absmiddle" border="0" src="images/cross.png" alt="delete" title="delete user" /></a>
EOM;

}else{
	echo <<<EOM
    &nbsp;&nbsp; 
EOM;
}

echo <<<EOM

</td></tr>
EOM;
		}
		echo <<<EOM

</table>
<script>
var tr=document.getElementById('grid').getElementsByTagName('tr');
for(var a=1; a<tr.length; a++){
	tr[a].onmouseover=function(){this.style.backgroundColor='lightyellow';}
	tr[a].onmouseout=function(){this.style.backgroundColor='';}
}

function popupwindow(url, title, w, h) {
	
	
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
 return  window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left).focus();;
   
} 

</script>


EOM;
	}
}


?>

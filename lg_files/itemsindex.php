<?
include_once("include/start.php");
ini_set("default_charset",'iso-8859-1');

$_REQUEST['cats'] = '4' ;

$size_of_cat_select = sizeOf($_REQUEST['cats']);
 //echo $bccat;
// figure out business card cat...
if($bccat=='')
	list($bccat)=@mysql_fetch_row(mysql_query("select ID from Category where CID=$CID and Name like 'Business%Card%' order by ID limit 1"));
	
list($is_view_only)=@mysql_fetch_row(mysql_query("select is_view_only from Users where CID=$CID and ID='$AID' "));	


if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';

if(isset($_SESSION['empty_bc_error']))
{

echo <<<EOM
<script>
alert('Please select quantity.');
</script>
EOM;

	$_SESSION['empty_bc_error'] = '';
	unset($_SESSION['empty_bc_error']);
}


if($action=='additems'){
	//print_r("<pre>");
	//print_r($_POST); die;
	
	if(isset($_SESSION['custom_new']) and !empty($_SESSION['custom_new']))
    {
    
    	//die('test');
	
    /*echo <<<EOM
<script>
alert('Business cards cannot be combined with any other items. Please complete your order or empty your cart and try again.');
history.go(-1);
</script>
EOM;
			   */ 
            $_SESSION['b_modal_show'] = 1 ;     
           $t = time();
           header("location:index.php?catid=4&t=$t"); 
          exit;  
               
        
    }
	
	
	if(isset($_SESSION['bccart'])) { // PREVENT MIXING OF BUSINESS CARD ITEMS WITH OTHER ITEMS...
		 $bcc=$_SESSION['bccart'];
       // die;
		if($bcc===0)
         {
         
          

 		   $_SESSION['b_modal_show'] = 1 ;     
           $t = time();
           header("location:index.php?catid=4&t=$t"); 
          exit; 

			exit;
           
		}
	}
    
    $required_file = 'no';
	foreach($_REQUEST['Quantity'] as $k=>$v){
		if($v>0) 
        {
        	$_SESSION['Order'][$k] = $v;
            
            
             $sql_get_item_info = " 
				SELECT * FROM Items WHERE FormID =  '$k'
				and CID = '$CID'						 
		 	 ";
             
             $rs_item_info = mysql_query($sql_get_item_info) ;
	    	 $item_detail_info = mysql_fetch_assoc($rs_item_info) ;
             if(!empty($item_detail_info))
             {
             	$_SESSION['Order_item_id'][$k] = $item_detail_info["ID"];
             }	 

             
          
			
			if($_REQUEST['cats']==4)
			{
				$_SESSION['preview'] = $k;
            }
			
            $_SESSION['Order_type'][$k] = 'N';
            
            list($require_file_upload)=@mysql_fetch_row(mysql_query("select require_file_upload from Items where FormID='$k'  "));
            
            
            
           if($require_file_upload=="yes")
           {
           
           		if($required_file=="no")
           		{
                	 $required_file = 'yes';
                }
           
           }
            
        }
	}
	
	if($_REQUEST['cats']==$bccat) {
		
        if($CID==44){
        	$_SESSION['bccart'] = '0';
		}else{
        	$_SESSION['bccart'] = '1';
        }
         if($CID==42){
        	if($_REQUEST['cats']==4)
			{
				header("Location: preview_bussiness_card.php");
			}else{
				header("Location: checkout-billing-details.php");
			}
		
        }else{
        	header("Location: shopping-cart.php");
        }
	} else {
		$_SESSION['bccart'] = '0';
        
        if($required_file=="yes")
        {
             header("Location: require_file.php");
        }else{
			header("Location: shopping-cart.php");
        }
	}
	exit;
}

?>
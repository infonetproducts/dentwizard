<?

include_once("include/start.php");

if(isset($_REQUEST['a'])) $action=$_REQUEST['a'];
else $action='';
$uid=$_SESSION['AID'];

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}


if(isset($_SESSION['custom_tab_order']) and $_SESSION['custom_tab_order'] ==1)
{
	if(isset($_REQUEST['fid'])) 
	{
		$item_fid = $_REQUEST['fid'];
		
		if($_SESSION['Order_type'][$item_fid]=="Y")
		{
			if(isset($_REQUEST['fid'])) 
			{
				$fid=$_REQUEST['fid'];
				$_SESSION['item_id_custom_order'] = $fid;
			}
			header('location:custom_order.php');
			die;
		
		}
	}
	
	
}


// require_file.php?link=yes&formid=&offset

if(isset($_GET['link']) and $_GET['link']=="yes")
{
	
	$formid = $_GET['formid'];
	$offset = $_GET['offset'];
	unset($_SESSION['link'][$formid][$offset]);
	unset($_SESSION['link_resolution'][$formid][$offset]);
	header("location:require_file.php");
	
}


if(isset($_POST["save_link"]))
{
	
	
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	
	
	if($_POST["link"]!="")
	{
		
		
			$formid = $_POST["formid"];
			$_SESSION['link'][$formid][] = addhttp($_POST["link"]);
			$_SESSION['link_resolution'][$formid][] = $_POST["link_resolution"];
			
			
			/*print_r("<pre>");
			print_r($_SESSION['link']);
			
			print_r("<pre>");
			print_r($_SESSION['resolution']);*/
			
			$_POST = '';
			
		
	}
}


if(isset($_GET['file']) and $_GET['file']=="yes")
{
	
	
	
	$formid = $_GET['formid'];
	$offset = $_GET['offset'];
	unset($_SESSION['file'][$formid][$offset]);
	unset($_SESSION['file_resolution'][$formid][$offset]);
	header("location:require_file.php");
	
		
	
}


if(isset($_POST["save_notes"]))
{
	
	//pr($_POST);
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	if($_POST["description"]!="")
	{
		$formid = $_POST["formid"];
		$_SESSION['description'][$formid]  =  $_POST["description"] ;
		$_SESSION['item_due_date_'.$formid][$formid]  =  $_POST["item_due_date_".$formid] ;
		
		$_POST = '';
	}
}


if(isset($_POST["save_to_continue"]))
{
	
	//pr($_POST);
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	if($_POST["description"]!="")
	{
		$formid = $_POST["formid"];
		$_SESSION['description'][$formid]  =  $_POST["description"] ;
		$_SESSION['item_due_date_'.$formid][$formid]  =  $_POST["item_due_date_".$formid] ;
		
		
	}
	
	if($_POST["item_due_date_".$formid]!="")
	{
		$formid = $_POST["formid"];		
		$_SESSION['item_due_date_'.$formid][$formid]  =  $_POST["item_due_date_".$formid] ;
		
		
	}
	
	$_POST = '';
	
	header("location:cart.php");
	exit;
	
}




// cart


$upload_path = "admin/userfile/";

if(isset($_POST["upload_file_submit"]))
{
	
	
	/*[type] => link
    [formid] => hynwincling
    [link_resolution] => High Resolution
    [link] => www.test.com
    [save_link] => Save Link*/
	
	if($_POST["link_resolution"]!="")
		{
			
		
	
				if($_FILES["upload_file"]["name"]!="")
				{
					// userfile
					$file_name_user = time()."_".$_FILES["upload_file"]["name"];
					if(move_uploaded_file($_FILES["upload_file"]["tmp_name"],$upload_path.$file_name_user))
					{
					
						$formid = $_POST["formid"];
						$_SESSION['file'][$formid][] = $file_name_user;
						$_SESSION['file_resolution'][$formid][] = $_POST["link_resolution"];
						
						
						/*print_r("<pre>");
						print_r($_SESSION['link']);
						
						print_r("<pre>");
						print_r($_SESSION['resolution']);*/
						
						$_POST = '';
					
					}
				}
				
	
		}else{
			$error_file_resolution = "Please select resolution";
		}
}


?>
 
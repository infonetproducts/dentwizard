<?php  
session_start();
include("include/db.php");


function identical_values( $arrayA , $arrayB ) { 

    sort( $arrayA ); 
    sort( $arrayB ); 

    return $arrayA == $arrayB; 
} 

$item_id = $_POST['item_id'];


list($item_type) = mysql_fetch_row(mysql_query("select item_type from Items where ID = '$item_id' "));

//echo $item_type;

$item_cat_array_new = array();
$rs_category_item_new = mysql_query("select CategoryID from FormCategoryLink where FormID = '$item_id' ");

if( mysql_num_rows($rs_category_item_new)>0 )
{
	while($cat_item_detail_new = mysql_fetch_assoc($rs_category_item_new) )
	{
		$item_cat_array_new[] = $cat_item_detail_new['CategoryID'] ; 
	}
}

/*
	print_r("<pre>");
	print_r($item_cat_array_new);
*/
			

if(!empty($_SESSION['custom_new']))
{
	foreach($_SESSION['custom_new'] as $item_id_cart => $custom_item_cart)
	{
		//echo $item_id_cart;
		/*print_r("<pre>");
		print_r($custom_item_cart);*/
		$item_cat_array = array();
		
		$rs_category_item = mysql_query("select CategoryID from FormCategoryLink where FormID = '$item_id_cart' ");
		
		if(mysql_num_rows($rs_category_item)>0)
		{
			while($cat_item_detail = mysql_fetch_assoc($rs_category_item) )
			{
				$item_cat_array[] = $cat_item_detail['CategoryID'] ; 
			}
		}
		
		/*print_r("<pre>");
print_r($item_cat_array);*/
		
		//$same_found = array_intersect($item_cat_array_new,$item_cat_array);
		
		$c = array_diff($item_cat_array_new,$item_cat_array);
		
		//$same_arr_cats = identical_values($item_cat_array_new,$item_cat_array);
		
		// $c = array_intersect($item_cat_array_new, $item_cat_array);
				
			/*print_r("<pre>");
			print_r($different_found);
				die;	*/	 
				
				if (count($c) > 0) {
					echo 2 ;
				}else{
					echo 1 ;
					// not found same category then error message need;
				}
				
		 
				/*if($same_arr_cats==1)
				{
					echo 1 ;
				}else{
					echo 2 ;
				}*/
	
		
	}
	
}else{

	if($item_type=="custom")
	{
	
		if(!empty($_SESSION['Order']))
		{
			foreach($_SESSION['Order'] as $form_id => $custom_item_cart)
			{
				//echo $item_id_cart;
				/*print_r("<pre>");
				print_r($custom_item_cart);*/
				$item_cat_array = array();
				
				list($item_id_cart)=@mysql_fetch_row(mysql_query("select ID from Items where FormID='$form_id'"));
					
				
				$rs_category_item = mysql_query("select CategoryID from FormCategoryLink where FormID = '$item_id_cart' ");
				
				if(mysql_num_rows($rs_category_item)>0)
				{
					while($cat_item_detail = mysql_fetch_assoc($rs_category_item) )
					{
						$item_cat_array[] = $cat_item_detail['CategoryID'] ; 
					}
				}
				
				/*print_r("<pre>");
		print_r($item_cat_array);
				*/
			
			$c = array_diff($item_cat_array_new,$item_cat_array);
				
				//$same_arr_cats = identical_values($item_cat_array_new,$item_cat_array);
				
		 
				///$c = array_intersect($item_cat_array_new, $item_cat_array);
				
				
					 
				
				if (count($c) > 0) {
					echo 2 ;
				}else{
					echo 1 ;
					// not found same category then error message need;
				}
				
		 
				/*if($same_arr_cats==1)
				{
					echo 1 ;
				}else{
					echo 2 ;
				}*/
				
			 
			
				
			}
		}
	}
	
}



	 


?>
	

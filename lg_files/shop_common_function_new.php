<?php




function search_item_new($CID , $str )
{

 

	$_GET["sstring"] = $str ;
	$_REQUEST['sstring'] = $str ;
	
	$uid = $_SESSION['AID'];
	list($mycats,$vid , $group_id)=@mysql_fetch_row(mysql_query("select FormCategoryIDs,VendorID , group_id from Users where ID='$uid'"));
	
	
	
	$item_list = array();	
	
	$CID = mysql_escape_string($CID);
	$str = mysql_escape_string($str);
	
	
	$uid = $_SESSION['AID'];

list($mycats,$vid , $group_id)=@mysql_fetch_row(mysql_query("select FormCategoryIDs,VendorID , group_id from Users where ID='$uid'"));


	if($mycats && (!isset($_REQUEST['cats']) || !is_array($_REQUEST['cats']))) $_REQUEST['cats'] = explode(",", $mycats);
	
	
	$tmpar=array();
if(is_array($_REQUEST['cats'])){
//if($uid==1232) echo "Hi!";
	$tmpar = array_flip($_REQUEST['cats']);
	$tmpar2 =array_flip(explode(",",$mycats));
	$orignal_cat = $_REQUEST['cats'];
    foreach($_REQUEST['cats'] as $ccid) {
		list($parentid)=@mysql_fetch_row(mysql_query("select ParentID from Category where ID='$ccid'"));
		if(!$parentid){ // this is a parent category ... check if no children are selected and select all of them if so...
			$tar = array();
			$foundsub=0;
			$rs = mysql_query("select ID from Category where ParentID=$ccid");// and ParentID in ($mycats)");
			while(list($i)=@mysql_fetch_row($rs)){ // load $tar with all the categories under this one unless use owns one, in which case just use what user owns.
				if(isset($tmpar2[$i]) || isset($tmpar[$i])) { ++$foundsub;}// if(!isset($tmpar[$i])) $_REQUEST['cats'][] = $i; }
				$tar[] = $i;
			}
			if(!$foundsub && count($tar)>0){
				$newar = array_merge($_REQUEST['cats'],$tar);
				$_REQUEST['cats'] = $newar;
			}
		}
	}
	reset($_REQUEST['cats']);
	 $num=@count($_REQUEST['cats']);
	
	$my_counter = 1; 
	foreach($_REQUEST['cats'] as $cat){
		if($num>1 && $bccat && $cat==$bccat) continue;
		
		if($size_of_cat_select>1 and $my_counter==1)
		{
			 
		}else if($size_of_cat_select==1)
		{
			if($catlist) $catlist .= ',';
			$catlist .= "$cat";
			
		}else{
			
			
			if($catlist) $catlist .= ',';
			$catlist .= "$cat";
		
		}
		$my_counter++;
	}
}

//echo $catlist;
	
	
	if($catlist) {
	$join = " join FormCategoryLink b on b.FormID=a.ID";
    
   if($CID==44)
   {
       $join .= " join FormCategoryLink c on b.FormID=c.FormID";
       $ifirst = 1;
       
      
       $maincat_arr = array();
       $subcat_arr = array();
       
       foreach($orignal_cat as $cat_id_new)
       {
       		list($parentid_new)=@mysql_fetch_row(mysql_query("select ParentID from Category where ID='$cat_id_new'"));
            if($parentid_new==0)
            {
            	$maincat_arr[] = $cat_id_new;
            }
            
            if($parentid_new!=0)
            {
            	$subcat_arr[] = $cat_id_new;
            }
            
            
       }
       
        if(!empty($maincat_arr))
       {
       		$maincat_arr = implode(",",$maincat_arr);
           // echo "maincat_arr";
            $and .= " and  b.CategoryID in( $maincat_arr) "; // category list as comma separated
       }
       
       if(!empty($subcat_arr))
       {
	   	//	echo "subcat_arr";
       		$subcat_arr = implode(",",$subcat_arr);
            $and .= " and  c.CategoryID in($subcat_arr) "; // subcategroy list as comma separated
       }
       
       

   }else{
    
	 $and .= " and b.CategoryID in ($catlist)";
    
		//echo "I am here ";
    }
}

if(isset($_REQUEST['sstring']) && $_REQUEST['sstring']){
	$sstring = mysql_real_escape_string(trim($_REQUEST['sstring']));
    
    if($CID==44)
    {
    	 
           
          $and .= " ( a.FormID like '%$sstring%' or a.OtherIDs like '%$sstring%' or a.keywords like '%$sstring%' or  a.Description like '%$sstring' or a.Description like '%$sstring%' or a.item_title like '%$sstring%')";
         
       
      
         
    }else{
	
	
	   $and .= "and (  a.FormID like '%$sstring%' or a.item_title like '%$sstring%' )";
    
       /* $and .= " and (a.FormID like '%$sstring%' or a.OtherIDs like '%$sstring%' or a.keywords like '%$sstring%' or (";
      
	  
        $tar = explode(" ", $sstring);
        $wcnt=0;
        foreach($tar as $str){
            if($wcnt) $and .= " and ";
            $sst="'%$str%'";
            if(strlen($str)==1 && count($tar)==1) $sst = "'$str%'"; 
			
            $and .= "(a.Description like $sst or a.Comments like $sst or a.item_title like $sst )";
            ++$wcnt;
        }
        $and .= "))";
		*/
		
		
    
    }
}


if($CID==44 and isset($_GET["sstring"]))
{

}else{

if($and) $and = substr($and,4); // chop first "and" off

}

$order_by_new = " order by a.FormID asc";

if(isset($_GET["sstring"]) and $_GET["sstring"]!="")
{
	$order_by_new = " order by a.item_title desc";
    if($CID==44)
    {
    	$order_by_new = " order by a.item_title ";
    }
}
if(isset($_GET["descri"]) and $_GET["descri"]!="")
{
    $order_user = $_GET["descri"];
    $order_by_new = " order by a.item_title $order_user";
}


if(isset($_GET["item"]) and $_GET["item"]!="")
{
    $order_user = $_GET["item"];
    $order_by_new = " order by a.FormID $order_user ";
}


if(isset($_GET["r"]) and $_GET["r"]!="")
{
    $order_user = $_GET["r"];
    $order_by_new = " order by a.RevisionDate $order_user ";
}
    
 

if($catlist==$bccat)
{

	if($CID==44)
    {
    
    if($and) $and .= " and ";
	$and .= " a.UserID in (0, $uid) "; // exclude other people's business cards, if not our own.
	 $sql = "select a.* from Items a $join where $and and a.CID=$CID and status_item='Y' group by a.ID $order_by_new limit 500";
    
	
    }else{
     $sql = "select a.* from Items a $join where $and and a.CID=$CID and a.UserID='$uid' and status_item='Y' group by a.ID $order_by_new limit 500";
}

}else {
	if($and) $and .= " and ";
	$and .= " a.UserID in (0, $uid) "; // exclude other people's business cards, if not our own.
	$sql = "select a.* from Items a $join where $and and a.CID=$CID and status_item='Y' group by a.ID $order_by_new limit 500";
}



//echo $sql;

   
	
	$sql_get_item_list = $sql;
	
	
	
	
	
	
	
	 /*$sql_get_item_list = " SELECT i.* FROM Items i
	inner join FormCategoryLink fcl ON i.ID = fcl.FormID
	 where
	  i.CID = '$CID' 
	  and i.status_item = 'Y'
	   
	  and ( 
	  		i.FormID like '%$str%' 			
            or i.FormID like '%$str%' 
			or i.item_title like '%$str%'
			 
                
          )
	 
	 
	  ";
	  */
	  
	  
	  
	  
	  
	  
	  
	   
	
	$rs_item_list = mysql_query($sql_get_item_list) ;
	
	if( mysql_num_rows($rs_item_list) >  0 )
	{
		while($item_detail = mysql_fetch_assoc($rs_item_list) )
		{	
			$item_list[] = $item_detail ;
		}
	}
	
	//pr_n($item_list);
	
	return $item_list ;
	
	
	
}






function search_item_new_bk_4_august_2025($CID , $str )
{

 

	$_GET["sstring"] = $str ;
	$_REQUEST['sstring'] = $str ;
	
	$uid = $_SESSION['AID'];
	list($mycats,$vid , $group_id)=@mysql_fetch_row(mysql_query("select FormCategoryIDs,VendorID , group_id from Users where ID='$uid'"));
	
	
	
	$item_list = array();	
	
	$CID = mysql_escape_string($CID);
	$str = mysql_escape_string($str);
	
	
	$uid = $_SESSION['AID'];

list($mycats,$vid , $group_id)=@mysql_fetch_row(mysql_query("select FormCategoryIDs,VendorID , group_id from Users where ID='$uid'"));


	if($mycats && (!isset($_REQUEST['cats']) || !is_array($_REQUEST['cats']))) $_REQUEST['cats'] = explode(",", $mycats);
	
	
	$tmpar=array();
if(is_array($_REQUEST['cats'])){
//if($uid==1232) echo "Hi!";
	$tmpar = array_flip($_REQUEST['cats']);
	$tmpar2 =array_flip(explode(",",$mycats));
	$orignal_cat = $_REQUEST['cats'];
    foreach($_REQUEST['cats'] as $ccid) {
		list($parentid)=@mysql_fetch_row(mysql_query("select ParentID from Category where ID='$ccid'"));
		if(!$parentid){ // this is a parent category ... check if no children are selected and select all of them if so...
			$tar = array();
			$foundsub=0;
			$rs = mysql_query("select ID from Category where ParentID=$ccid");// and ParentID in ($mycats)");
			while(list($i)=@mysql_fetch_row($rs)){ // load $tar with all the categories under this one unless use owns one, in which case just use what user owns.
				if(isset($tmpar2[$i]) || isset($tmpar[$i])) { ++$foundsub;}// if(!isset($tmpar[$i])) $_REQUEST['cats'][] = $i; }
				$tar[] = $i;
			}
			if(!$foundsub && count($tar)>0){
				$newar = array_merge($_REQUEST['cats'],$tar);
				$_REQUEST['cats'] = $newar;
			}
		}
	}
	reset($_REQUEST['cats']);
	 $num=@count($_REQUEST['cats']);
	
	$my_counter = 1; 
	foreach($_REQUEST['cats'] as $cat){
		if($num>1 && $bccat && $cat==$bccat) continue;
		
		if($size_of_cat_select>1 and $my_counter==1)
		{
			 
		}else if($size_of_cat_select==1)
		{
			if($catlist) $catlist .= ',';
			$catlist .= "$cat";
			
		}else{
			
			
			if($catlist) $catlist .= ',';
			$catlist .= "$cat";
		
		}
		$my_counter++;
	}
}

//echo $catlist;
	
	
	if($catlist) {
	$join = " join FormCategoryLink b on b.FormID=a.ID";
    
   if($CID==44)
   {
       $join .= " join FormCategoryLink c on b.FormID=c.FormID";
       $ifirst = 1;
       
      
       $maincat_arr = array();
       $subcat_arr = array();
       
       foreach($orignal_cat as $cat_id_new)
       {
       		list($parentid_new)=@mysql_fetch_row(mysql_query("select ParentID from Category where ID='$cat_id_new'"));
            if($parentid_new==0)
            {
            	$maincat_arr[] = $cat_id_new;
            }
            
            if($parentid_new!=0)
            {
            	$subcat_arr[] = $cat_id_new;
            }
            
            
       }
       
        if(!empty($maincat_arr))
       {
       		$maincat_arr = implode(",",$maincat_arr);
           // echo "maincat_arr";
            $and .= " and  b.CategoryID in( $maincat_arr) "; // category list as comma separated
       }
       
       if(!empty($subcat_arr))
       {
	   	//	echo "subcat_arr";
       		$subcat_arr = implode(",",$subcat_arr);
            $and .= " and  c.CategoryID in($subcat_arr) "; // subcategroy list as comma separated
       }
       
       

   }else{
    
	 $and .= " and b.CategoryID in ($catlist)";
    
		//echo "I am here ";
    }
}

if(isset($_REQUEST['sstring']) && $_REQUEST['sstring']){
	$sstring = mysql_real_escape_string(trim($_REQUEST['sstring']));
    
    if($CID==44)
    {
    	// $and .= "  a.Description like '%$sstring' or a.Description like '%$sstring%') ";
         
         
          $and .= " ( a.FormID like '%$sstring%' or a.OtherIDs like '%$sstring%' or a.keywords like '%$sstring%' or  a.Description like '%$sstring' or a.Description like '%$sstring%' or a.item_title like '%$sstring%')";
         
       
      
         
    }else{
    
        $and .= " and (a.FormID like '%$sstring%' or a.OtherIDs like '%$sstring%' or a.keywords like '%$sstring%' or (";
        // split string into words, make sure all words are found in either description or comments.
        $tar = explode(" ", $sstring);
        $wcnt=0;
        foreach($tar as $str){
            if($wcnt) $and .= " and ";
            $sst="'%$str%'";
            if(strlen($str)==1 && count($tar)==1) $sst = "'$str%'"; // if one char, then only find things starting with that char.
            $and .= "(a.Description like $sst or a.Comments like $sst or a.item_title like $sst )";
            ++$wcnt;
        }
        $and .= "))";
    
    }
}


if($CID==44 and isset($_GET["sstring"]))
{

}else{

if($and) $and = substr($and,4); // chop first "and" off

}

$order_by_new = " order by a.FormID asc";

if(isset($_GET["sstring"]) and $_GET["sstring"]!="")
{
	$order_by_new = " order by a.item_title desc";
    if($CID==44)
    {
    	$order_by_new = " order by a.item_title ";
    }
}
if(isset($_GET["descri"]) and $_GET["descri"]!="")
{
    $order_user = $_GET["descri"];
    $order_by_new = " order by a.item_title $order_user";
}


if(isset($_GET["item"]) and $_GET["item"]!="")
{
    $order_user = $_GET["item"];
    $order_by_new = " order by a.FormID $order_user ";
}


if(isset($_GET["r"]) and $_GET["r"]!="")
{
    $order_user = $_GET["r"];
    $order_by_new = " order by a.RevisionDate $order_user ";
}
    
 

if($catlist==$bccat)
{

	if($CID==44)
    {
    
    if($and) $and .= " and ";
	$and .= " a.UserID in (0, $uid) "; // exclude other people's business cards, if not our own.
	 $sql = "select a.* from Items a $join where $and and a.CID=$CID and status_item='Y' group by a.ID $order_by_new limit 500";
    
	
    }else{
     $sql = "select a.* from Items a $join where $and and a.CID=$CID and a.UserID='$uid' and status_item='Y' group by a.ID $order_by_new limit 500";
}

}else {
	if($and) $and .= " and ";
	$and .= " a.UserID in (0, $uid) "; // exclude other people's business cards, if not our own.
	$sql = "select a.* from Items a $join where $and and a.CID=$CID and status_item='Y' group by a.ID $order_by_new limit 500";
}



//echo $sql;

   
	
	$sql_get_item_list = $sql;
	
	
	
	
	
	
	
	 /*$sql_get_item_list = " SELECT i.* FROM Items i
	inner join FormCategoryLink fcl ON i.ID = fcl.FormID
	 where
	  i.CID = '$CID' 
	  and i.status_item = 'Y'
	   
	  and ( 
	  		i.FormID like '%$str%' 			
            or i.FormID like '%$str%' 
			or i.item_title like '%$str%'
			 
                
          )
	 
	 
	  ";
	  */
	  
	  
	  
	  
	  
	  
	  
	   
	
	$rs_item_list = mysql_query($sql_get_item_list) ;
	
	if( mysql_num_rows($rs_item_list) >  0 )
	{
		while($item_detail = mysql_fetch_assoc($rs_item_list) )
		{	
			$item_list[] = $item_detail ;
		}
	}
	
	//pr_n($item_list);
	
	return $item_list ;
	
	
	
}


?>
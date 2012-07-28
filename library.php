<?php 
require_once( 'database.php');
function initpage()
{
	session_start();
	$sess_id=md5(microtime());
	
	$_SESSION['Debug']=$_SESSION['Debug']."InInitPage(".$_SESSION['LMS_SID']."=".$_COOKIE['LMS_SID'].")";
	setcookie("LMS_SID",$sess_id,(time()+(LifeTime*60)));
	$_SESSION['LMS_SID']=$sess_id;
	$_SESSION['LifeTime']=time();
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
	$t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");
	$reg=new DB();				
	$reg->do_ins_query("INSERT INTO visitors(ip,vpage,uagent,referrer) values"		
			."('".$_SERVER['REMOTE_ADDR']."','".htmlspecialchars($_SERVER['PHP_SELF'])."','".$_SERVER['HTTP_USER_AGENT']
			."','<".$t.">');");
	if(isset($_REQUEST['show_src']))
	{
		if($_REQUEST['show_src']=="me")
		show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}	
	return;
}
function SearchForm()
{
echo '<div class="SearchBox">
		<div class="LSearch">Search: </div>
			<form action="http://paschimmedinipur.gov.in/search" id="cse-search-box">
			  <div>
				<input type="hidden" name="cx" value="011522023375570681042:wfkb9zxf3me" />
				<input type="hidden" name="cof" value="FORID:11" />
				<input type="hidden" name="ie" value="UTF-8" />
				<input type="text" class="SearchQuery" style="width:150px;" name="q" />
				<input type="submit" name="sa" style="margin:0px;padding:0px;display:none;" value="Search" />
			  </div>
			</form>
		<div class="RSearch" onclick="javascript:document.getElementById(\'cse-search-box\').submit();" style="cursor:pointer;">Go</div>
	  </div> ';
}
function pageinfo()
{
	$strfile=strtok($_SERVER['PHP_SELF'],"/");
	//echo $_SERVER['PHP_SELF'].' | '.$strfile;
	$str=strtok("/");
	//echo ' | '.$str;
	while( $str)
	{
		$strfile=$str;
		//echo ' | '.$strfile;
		$str=strtok("/");
	}
	$reg=new DB();
 	$visitor_num=$reg->do_sel_query("select * from visitors where vpage='".$_SERVER['PHP_SELF']."'");
	$_SESSION['ptime']=$reg->do_max_query("select max(vtime) from visitors where vpage like '".$_SERVER['PHP_SELF']."'");
	$_SESSION['LifeTime']=time();
	echo "<strong > Last Updated On:</strong> &nbsp;&nbsp;".date("l d F Y g:i:s A ",filemtime($strfile))
		." IST &nbsp;&nbsp;&nbsp;<b>Your IP: </b>".$_SERVER['REMOTE_ADDR']
		."&nbsp;&nbsp;&nbsp;<b>Visits:</b>&nbsp;&nbsp;".$visitor_num
		." <b>Last Visit:</b> ".date(" g:i:s A ",time())
		."";
	$reg->do_close();
	return;
}
function footerinfo()
{
	 echo 'Designed and Developed By <strong>National Informatics Centre</strong>, Paschim Medinipur District Centre<br/>'
	 		.'L. A. Building (2nd floor), Collectorate Compound, Midnapore<br/>'
			.'West Bengal - 721101 , India Phone : 91-3222-263506, Email: wbmdp(a)nic.in<br/>';
			//."DB_SID: ".$_SESSION['ID']." ORG: ".session_id()." Cookie:".$_COOKIE['LMS_SID']." VALID=".$_SESSION['Validity']." | ".LifeTime.$_SESSION['LMS_AUTH'];
}
function ToDate($AppDate)
{
	if($AppDate!="")
		return date("d-m-Y",strtotime($AppDate));
	else
		return date("d-m-Y",time());
}
function ToDBDate($AppDate)
{
	if ($AppDate=="")
		return date("Y-m-d",time());
	else
		return date("Y-m-d",strtotime($AppDate));
}
function CheckSess()
{
	$_SESSION['Debug']=$_SESSION['Debug']."InCheckSESS";
    if((!isset($_SESSION['LoggedOfficerID'])) && (!isset($_SESSION['BlockCode'])))
	{
		return "Browsing";
	}
	if(isset($_REQUEST['LogOut']))
    {
        return "LogOut";
    }
    else if($_SESSION['LifeTime']<(time()-(LifeTime*60)))
    {
        return "TimeOut(".$_SESSION['LifeTime']."-".(time()-(LifeTime*60)).")";
    }
    else if($_SESSION['LMS_SID']!=$_COOKIE['LMS_SID'])
    {
        $_SESSION['Debug']="(".$_SESSION['LMS_SID']."=".$_COOKIE['LMS_SID'].")";
		return "Stolen(".$_SESSION['LMS_SID']."=".$_COOKIE['LMS_SID'].")";
    }
    else
    {                                        
		return "Valid";
    }
}
function IntraNIC()
{
	$reg=new DB();
 	$AllowedIP=$reg->do_max_query("select count(*) from IntraNIC where ip='".$_SERVER['REMOTE_ADDR']."'");
	if(!$AllowedIP)
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}
}
	
function lms_auth()
{
	session_start();
	$_SESSION['Debug']=$_SESSION['Debug']."InLMS_AUTH";
    $SessRet=CheckSess();
	$reg=new DB();
	if($_REQUEST['NoAuth'])
		initpage();
	else
	{
		if($SessRet!="Valid")
        {
            
			$reg->do_ins_query("INSERT INTO lms_logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"
                    ."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".mysql_real_escape_string($t)."','".$_SERVER['HTTP_USER_AGENT']
                    ."','".$_SESSION['LoggedOfficerID']."','".mysql_real_escape_string($_SERVER['PHP_SELF'])."','".$SessRet.": ("
                    .$_SERVER['SCRIPT_NAME'].")','".mysql_real_escape_string($_SERVER['REQUEST_METHOD'])."','".mysql_real_escape_string($_SERVER['REQUEST_URI'])."');");    
			session_unset();
			session_destroy();
			session_start();
			$_SESSION=array();
			$_SESSION['Debug']=$_SESSION['Debug']."LMS_AUTH-!Valid";
			header("Location: login.php");
        }
        else
        {
			$_SESSION['Debug']=$_SESSION['Debug']."LMS_AUTH-IsValid";
			$sess_id=md5(microtime());
			setcookie("LMS_SID",$sess_id,(time()+(LifeTime*60)));
			$_SESSION['LMS_SID']=$sess_id;
			$_SESSION['LifeTime']=time();
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
            $t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");  
            $reg->do_ins_query("INSERT INTO visitors(ip,vpage,uagent,referrer) values"		
                    ."('".$_SERVER['REMOTE_ADDR']."','".htmlspecialchars($_SERVER['PHP_SELF'])."','".$_SERVER['HTTP_USER_AGENT']
                    ."','<".$t.">');");
            $reg->do_ins_query("INSERT INTO lms_logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"		
                    ."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".mysql_real_escape_string($t)."','".$_SERVER['HTTP_USER_AGENT']
                    ."','".$_SESSION['LoggedOfficerID']."','".mysql_real_escape_string($_SERVER['PHP_SELF'])."','Process (".$_SERVER['SCRIPT_NAME'].")','"
                    .mysql_real_escape_string($_SERVER['REQUEST_METHOD'])."','".mysql_real_escape_string($_SERVER['REQUEST_URI'])."');");
        }
	}
	if(isset($_REQUEST['show_src']))
	{
		if($_REQUEST['show_src']=="me")
		show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}	
	return;	
}

function bskp_auth()
{
	session_start();
	$_SESSION['Debug']=$_SESSION['Debug']."InLMS_AUTH";
    $SessRet=CheckSess();
	$reg=new DB();
	if($_REQUEST['NoAuth'])
		initpage();
	else
	{
		if($SessRet!="Valid")
        {
			$reg->do_ins_query("INSERT INTO bskp_logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"		
					."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".mysql_real_escape_string($t)."','".$_SERVER['HTTP_USER_AGENT']
					."','".$_SESSION['BlockCode']."','".mysql_real_escape_string($_SERVER['PHP_SELF'])."','".$SessRet.": (".$_SERVER['SCRIPT_NAME'].")','"
					.mysql_real_escape_string($_SERVER['REQUEST_METHOD'])."','".mysql_real_escape_string($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'])."');"); 
			session_unset();
			session_destroy();
			session_start();
			$_SESSION=array();
			$_SESSION['Debug']=$_SESSION['Debug']."LMS_AUTH-!Valid";
			header("Location: login.php");
        }
        else
        {
			$_SESSION['Debug']=$_SESSION['Debug']."LMS_AUTH-IsValid";
			$sess_id=md5(microtime());
			setcookie("LMS_SID",$sess_id,(time()+(LifeTime*60)));
			$_SESSION['LMS_SID']=$sess_id;
			$_SESSION['LifeTime']=time();
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
            $t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");  
            $reg->do_ins_query("INSERT INTO visitors(ip,vpage,uagent,referrer) values"		
                    ."('".$_SERVER['REMOTE_ADDR']."','".htmlspecialchars($_SERVER['PHP_SELF'])."','".$_SERVER['HTTP_USER_AGENT']
                    ."','<".$t.">');");
            $reg->do_ins_query("INSERT INTO bskp_logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"		
						."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".mysql_real_escape_string($t)."','".$_SERVER['HTTP_USER_AGENT']
						."','".$_SESSION['BlockCode']."','".mysql_real_escape_string($_SERVER['PHP_SELF'])."','Process (".$_SERVER['SCRIPT_NAME'].")','"
						.mysql_real_escape_string($_SERVER['REQUEST_METHOD'])."','".mysql_real_escape_string($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'])."');");
        }
	}
	if(isset($_REQUEST['show_src']))
	{
		if($_REQUEST['show_src']=="me")
		show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}	
	return;	
	
}

?>
<?php 
/***** Create an ftpcred.php in the same directory ******/
/***** Sample ftpcred.php *******/
/*
define("FTP_HOST_Name","ftp.example.com");
define("FTP_User","user@example.com");
define("FTP_Pass","pass");
*/
include('ftpcred.php');
define("FTP_Dir",".");

class ftp{ 
    public $conn; 

    public function __construct($url)
	{ 
        $this->conn = ftp_connect($url); 
    } 
    
    public function __call($func,$a)
	{ 
        if(strstr($func,'ftp_') !== false && function_exists($func))
		{ 
            array_unshift($a,$this->conn); 
            return call_user_func_array($func,$a); 
        }
		else
		{ 
            die("$func is not a valid FTP function"); 
        } 
    } 
} 

function CleanScript(&$ftp,$path)
{
	STATIC $i=0;
	if($i<2)
	{
	$local_file="./infected";
	if ($ftp->ftp_get($local_file, $path, FTP_BINARY))
	{
		$Org_contents = file_get_contents($local_file);
		$count=0;
		$pattern = array('#<.php\seval(.*);#','#<.php\s.error_reporting(.*)>#','#<script>try.n-=eval(.*)>#'); 
		$replace = array('<?php');
		$Rep_contents=preg_replace($pattern,$replace,$Org_contents,-1,$count);
		if(($count>0) && (strlen($Org_contents)>strlen($Rep_contents)))
		{
			if($ftp->ftp_rename($path,$path.'del'))
			{
				echo "<b>Original: </b>".strlen($Org_contents)."<b> Replaced: </b>".strlen($Rep_contents)."";
				file_put_contents("./cleaned",$Rep_contents);
				$i++;
				if ($ftp->ftp_put($path, "cleaned", FTP_BINARY))
				{
					echo "<b>Cleaned:</b>(".$i.") ".$path."(".strlen($Rep_contents).")"
					." <b><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?CD=".$path."del\">[Delete]</a></b>"
					." <b><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?RCD=".$path."del\">[Restore]</a></b><br/>";
				}
				else
					echo "<b>Infected(".$path.") Unable to Upload</b><br/>";
			}
			else
				echo "<b>Infected(".$path.") No Write Permission</b><br/>";
			
		}
	}
	}
}

function ShowFiles(&$ftp,$dir)
{
	STATIC $i=0;
	$files=$ftp->ftp_nlist($dir);
	if(!$files)
	{
		echo "<b>Unable to list may be not a directory: </b>".$dir."<br/>";
		if($ftp->ftp_size($dir)==-1)
		{
			ShowFiles($ftp,$dir);
		}
	}
	else
	foreach ($files as $key => $file)
	{
		$path=$dir."/".$file;
		if(($file!=".")&&($file!="..")&&($file!=".".$_SERVER['PHP_SELF']))
		{
			if($ftp->ftp_size($path)==-1)
				ShowFiles($ftp,$path);
			elseif((substr($file,-3)=='php')||(substr($file,-4)=='html'))
			{
				$i++;
				echo $i.") ".substr($path,-strlen($path)+1)." (".$ftp->ftp_size($path).')<br/>';
				CleanScript($ftp,$path);
			}
		}
	}
}

function CleanedFiles(&$ftp,$dir)
{
	STATIC $i=0;
	$files=$ftp->ftp_nlist($dir);
	if(!$files)
	{
		if($ftp->ftp_size($dir)==-1)
		{
			CleanedFiles($ftp,$dir);
		}
	}
	else
	foreach ($files as $key => $file)
	{
		$path=$dir."/".$file;
		if(($file!=".")&&($file!=".."))
		{
			if($ftp->ftp_size($path)==-1)
				CleanedFiles($ftp,$path);
			elseif((substr($file,-3)=='del'))
			{
				$i++;
				echo $i.") <b>Cleaned: </b>".substr($path,-strlen($path)+1)." (".$ftp->ftp_size($path)
						.") <b><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?CD=".$path."\">[Delete]</a></b>"
						." <b><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?RCD=".$path."\">[Restore]</a></b><br/>";
				DeleteCleaned($ftp,$path);
			}
		}
	}
}

function ListSites(&$ftp,$dir)
{
	STATIC $i=0;
	$files=$ftp->ftp_nlist($dir);
	if(!$files)
	{
		if($ftp->ftp_size($dir)==-1)
		{
			ListSites($ftp,$dir);
		}
	}
	else
	foreach ($files as $key => $file)
	{
		$path=$dir."/".$file;
		if(($file!=".")&&($file!="..")&&($file!=".".$_SERVER['PHP_SELF']))
		{
			if($ftp->ftp_size($path)==-1)
			{
				$i++;
				echo $i.") <b>Clean: </b><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?CFD=".$path."\">[+]</a>"
						." <a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?FTP_Dir=".$path."\">".$path."</a><br/>";
			}
			elseif((substr($file,-3)=='php')||(substr($file,-4)=='html'))
			{
				$i++;
				//echo $i.") Cleaning: ".substr($path,-strlen($path)+1)." (".$ftp->ftp_size($path).')<br/>';
				CleanScript($ftp,$path);
			}
		}
	}
}

function DeleteCleaned(&$ftp,$path)
{
	if($ftp->ftp_delete($path))
		echo "<b>Deleted:</b>".$path."<br/>";
	
}

function RestoreCleaned(&$ftp,$path)
{
	if($ftp->ftp_rename($path,substr($path,0,-3)))
		echo "<b>Restored:</b>".$path." To:".substr($path,0,-3);
	
}
// Example 
//echo FTP_HOST_Name.FTP_User.FTP_Pass;
echo $_SERVER['PHP_SELF'];
$ftp = new ftp(FTP_HOST_Name); 
$ftp->ftp_login(FTP_User,FTP_Pass); 
$ftp->ftp_set_option(FTP_TIMEOUT_SEC,600);
$ftp->ftp_pasv(TRUE);

if(isset($_REQUEST['FTP_Dir']))
{
	echo "<pre>";
	print_r($ftp->ftp_nlist($_REQUEST['FTP_Dir']));
	echo "</pre>";
	CleanedFiles($ftp,$_REQUEST['FTP_Dir']);
	ShowFiles($ftp,$_REQUEST['FTP_Dir']);
}
elseif(isset($_REQUEST['CFD']))
	ListSites($ftp,$_REQUEST['CFD']);
elseif(isset($_REQUEST['CD']))
	DeleteCleaned($ftp,$_REQUEST['CD']);
elseif(isset($_REQUEST['RCD']))
	RestoreCleaned($ftp,$_REQUEST['RCD']);
else
	ListSites($ftp,FTP_Dir);
?>
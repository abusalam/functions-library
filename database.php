<?php
require_once('MySQLServer.php');

$bskp_applicants="select `a`.`AppNo` AS `AppNo`,`a`.`App1Name` AS `App1Name`,`a`.`App1FName` AS `App1FName`,`a`.`Addr1` AS `Addr1`,`a`.`AppDate` AS `AppDate`,`a`.`SchDesc` AS `SchDesc`,`a`.`TotalCost` AS `TotalCost`,`a`.`Status` AS `Status`,`a`.`Remark` AS `Remark`,`b`.`BankID` AS `BankID`,`b`.`BankNm` AS `BankNm`,`br`.`BranchID` AS `BranchID`,`br`.`BranchNm` AS `BranchNm`,`bl`.`BlockCode` AS `BlockCode`,`bl`.`BlockName` AS `BlockName`,`bl`.`BlockHQName` AS `BlockHQName` from (((`bskp_applications` `a` join `bskp_branches` `br` on((`br`.`BranchID` = `a`.`BranchID`))) join `bskp_banks` `b` on((`b`.`BankID` = `br`.`BankID`))) join `bskp_blocks` `bl` on((`bl`.`BlockCode` = `b`.`BlockCode`)))";
$FormA="select concat_ws(_latin1'|',`a`.`App1Name`,`a`.`App1FName`) AS `ApplNmFNm`,`a`.`Addr1` AS `Addr1`,`a`.`SchDesc` AS `SchDesc`,`a`.`TotalCost` AS `TotalCost`,date_format(`a`.`AppDate`,'%d-%m-%Y') AS `AppDate`,`a`.`Remark` AS `Remark`,date_format(`b`.`ActionDate`,'%d-%m-%Y') AS `ActionDate`,`a`.`BlockCode` AS `BlockCode` from ((".$bskp_applicants.") `a` join `bskp_blockactions` `b` on(((`a`.`AppNo` = `b`.`AppNo`) and (`b`.`Status` = 'A'))))";
$FormE="select concat_ws(_latin1'|',`a`.`App1Name`,`a`.`App1FName`) AS `ApplNmFNm`,`a`.`Addr1` AS `Addr1`,`a`.`SchDesc` AS `SchDesc`,`a`.`TotalCost` AS `TotalCost`,date_format(`a`.`AppDate`,'%d-%m-%Y') AS `AppDate`,`a`.`Remark` AS `Remark`,date_format(`b`.`ActionDate`,'%d-%m-%Y') AS `ActionDate`,`a`.`BranchID` AS `BranchID` from (`bskp_applications` `a` join `bskp_blockactions` `b` on(((`a`.`AppNo` = `b`.`AppNo`) and (`b`.`Status` = 'A'))))";

class DB {
	public $conn;
	public $result;
	public $Debug;
	private function do_connect()
	{
		//$this->Debug=1;
		$this->conn=mysql_connect(HOST_Name,MySQL_User,MySQL_Pass);
		if(!$this->conn)
		{
			die('Could not Connect(database.php): '.mysql_error()."<br><br>");
		}
		//mysql_select_db('damncool_BSKP') or die('Cannot select database');
		mysql_select_db(MySQL_DB) or die('Cannot select database');
	}
	public function do_ins_query($querystr)
	{
		$this->do_connect();
		$this->result = mysql_query($querystr,$this->conn);
		if (!$this->result)
		{
			$message = 'Error(database): ' . mysql_error();
  			//$message .= 'Whole query: ' . $querystr."<br>";
			if($this->Debug)
				echo $message;	
			return 0;
		}
		return mysql_affected_rows($this->conn);
	}
	
	public function do_sel_query($querystr)
	{
		$this->do_connect();
		$this->result = mysql_query($querystr,$this->conn);
		if ((mysql_num_rows($this->result)<1) && ($this->Debug))
		{
				echo mysql_error($this->conn);
			return 0;
		}
		return mysql_num_rows($this->result);
	}

	public function get_row()
	{
		if (mysql_num_rows($this->result)>0)
			return mysql_fetch_assoc($this->result);
	}

	public function get_n_row()
	{
		if (mysql_num_rows($this->result)>0)
			return mysql_fetch_row($this->result);
	}
	
	public function show_sel($val,$txt,$query,$sel_val="-- Choose --")
	{
		$this->do_sel_query($query);
		$opt=mysql_num_rows($this->result);
		if($sel_val=="-- Choose --")
			echo "<option value=''>-- Choose --</option>";
		for($i=0;$i<$opt;$i++)
		{
			$row=$this->get_row();
			if($row[$val]==$sel_val)
				$sel="selected";
			else
				$sel="";
			echo '<option value="'.htmlspecialchars($row[$val])
				.'"'.$sel.'>'.htmlspecialchars($row[$txt]).'</option>';
		}
	}
	
	public function do_max_query($Query)
	{
		$this->do_sel_query($Query);
		$row= mysql_fetch_row($this->result);
		//echo "Whole Row: ".$row[0].$row[1];
		if ($row[0]==null)
			return 0;
		else
			return htmlspecialchars($row[0]);
	}
	
	public function ShowTable($QueryString)
	{ 
		// Connecting, selecting database 
		$this->do_connect();
		// Performing SQL query 
		$this->result = mysql_query($QueryString,$this->conn);  
		// Printing results in HTML 
		echo '<table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">'; 
		$i=0;
		while ($i<mysql_num_fields($this->result))
		{
			echo '<th>'.htmlspecialchars(mysql_field_name($this->result,$i)).'</th>';
			$i++;
		} 
		$i=0;
		while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{   
			echo "\t<tr>\n";   
			foreach ($line as $col_value)
				echo "\t\t<td>".$col_value."</td>\n";
			//$strdt=date("F j, Y, g:i:s a",$ntime); 
			//echo "\t\t<td>$strdt</td>\n";   
			echo "\t</tr>\n"; 
			$i++;
		} 
		echo "</table>\n"; 
		// Free resultset 
		mysql_free_result($this->result);  
		// Closing connection 
		mysql_close($this->conn); 
		return ($i);
	}
	public function ShowTableKiosk($QueryString)
	{ 
		// Connecting, selecting database 
		$this->do_connect();
		// Performing SQL query 
		$this->result = mysql_query($QueryString,$this->conn);  
		// Printing results in HTML 
		echo '<table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1" border="1">'; 
		echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;border: 1px solid black;"></td></tr>'; 
		$i=0; 
		while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{   
			$i=0; 
			foreach ($line as $col_value)
			{	echo "\t<tr>\n"; 
				echo '<th  style="background-color:#FFDA91;font-weight:bold;text-align:left;border: 1px solid black;">'.htmlspecialchars(mysql_field_name($this->result,$i)).'</th>';
				echo "\t\t".'<td style="border: 1px solid black;">'.$col_value."</td>\n";
			//$strdt=date("F j, Y, g:i:s a",$ntime); 
			//echo "\t\t<td>$strdt</td>\n";   
				echo "\t</tr>\n"; 
				$i++;
			}
			echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;border: 1px solid black;"></td></tr>'; 
		} 
		echo "</table>\n"; 
		// Free resultset 
		mysql_free_result($this->result);  
		// Closing connection 
		mysql_close($this->conn); 
		return ($i);
	}
	public function do_close()
	{
		// Free resultset 
		mysql_free_result($this->result);  
		// Closing connection 
		mysql_close($this->conn);
	}
		/*function EditTableV($QueryString)
	{ 
		$this->result = mysql_query($QueryString,$this->conn);
		echo "Total Records: ".mysql_num_rows($this->result)."\n<br />";
		// Printing results in HTML 
		echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
			.'"><table rules="all" frame="box" cellpadding="5" cellspacing="1">';
		//Update Table Data
		$col=1;
		if(isset($_REQUEST['Delete']))
		{
			$Data=new DB();
			$Query="Delete from ".mysql_field_table($this->result,0)
				." Where ".mysql_field_name($this->result,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
			//echo $Query;
			$Data->do_ins_query($Query);
			$this->result = mysql_query($QueryString,$this->conn);
			//echo 'Query failed: ' . mysql_error();  
		}
		if(isset($_POST[mysql_field_name($this->result,$col)]))
		{
			$Data=new DB();
			while ($col<mysql_num_fields($this->result))
			{
				$row=0;
				//echo $r."--".mysql_field_name($this->result,$col)."--".mysql_field_table($this->result,$col)
				//	.$_POST[mysql_field_name($this->result,$col)][$row];
				while($row<count($_POST[mysql_field_name($this->result,$col)]))
				{
					$Query="Update ".mysql_field_table($this->result,$col)
						." Set ".mysql_field_name($this->result,$col)."='".mysql_real_escape_string($_POST[mysql_field_name($this->result,$col)][$row])."'"
						." Where ".mysql_field_name($this->result,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->result,0)][$row])." LIMIT 1;";
					echo $Query."<br />";
					$Data->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			$this->result = mysql_query($QueryString,$this->conn); 
			//echo $Query."<br />";
		}
		//Print Collumn Names
		$i=0;
		//Print Rows
		$odd="";
		$RecCount=0;
		while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{   
			$RecCount++;
			$odd=$odd==""?"odd":"";
			//echo '<tr class="'.$odd.'">';
			$i=0;
			foreach ($line as $col_value)
			{  
				echo '<tr><td><b>('.($i+1).') '.mysql_field_name($this->result,$i).'</b></td>';
				echo '<td>';
				if($i==0)
				{
					$allow='readonly';
					echo '<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
						.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
				}
				else
					$allow='';
				echo '('.($i+1).') <input '.$allow.' type="text" size="'.(mysql_field_len($this->result,$i))
				.'" name="'.mysql_field_name($this->result,$i).'[]" value="'.htmlspecialchars($col_value).'" /> </td></tr>';     
				$i++;
			}   
			echo '<tr><td colspan="2" style="background-color:#F4A460;"></td></tr>'; 
		} 
		echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
		//if (isset($_REQUEST['RecFrom']) && ($_REQUEST['RecFrom']-$RecPerPage)>=0)
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']-$RecPerPage).'" >&lt; Prev</a></b>&nbsp;&nbsp;&nbsp;';
		//if (($RecCount==$RecPerPage) && (mysql_num_rows($this->result)>0))
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']+$RecPerPage).'" >Next &gt;</a></b>';
		echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>'; 
	}*/
	function EditTableV2($QueryString)
	{ 
		$this->result = mysql_query($QueryString,$this->conn);
		echo "Total Records: ".mysql_num_rows($this->result)."\n<br />";
		// Printing results in HTML 
		echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
			.'"><table rules="all" frame="box" cellpadding="5" cellspacing="1">';
		//Update Table Data
		$col=1;
		if(isset($_REQUEST['Delete']))
		{
			$Data=new DB();
			$Query="Delete from ".mysql_field_table($this->result,0)
				." Where ".mysql_field_name($this->result,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
			//echo $Query;
			$Data->do_ins_query($Query);
			$this->result = mysql_query($QueryString,$this->conn);
			//echo 'Query failed: ' . mysql_error();  
		}
		if(isset($_POST[mysql_field_name($this->result,$col)]))
		{
			$Data=new DB();
			while ($col<mysql_num_fields($this->result))
			{
				$row=0;
				//echo $r."--".mysql_field_name($this->result,$col)."--".mysql_field_table($this->result,$col)
				//	.$_POST[mysql_field_name($this->result,$col)][$row];
				while($row<count($_POST[mysql_field_name($this->result,$col)]))
				{
					$ColName=mysql_field_name($this->result,$col);
					if (empty($_POST[mysql_field_name($this->result,$col)][$row]))
						$POSTVal="NULL";
					elseif((substr($ColName,0,4)=="Date") && $POSTVal!="NULL")
					{
						$POSTVal="'".date("Y-m-d",strtotime($_POST[mysql_field_name($this->result,$col)][$row]))."' ";
					}
					else
						$POSTVal="'".mysql_real_escape_string($_POST[mysql_field_name($this->result,$col)][$row])."' ";
					$Query="Update ".mysql_field_table($this->result,$col)
						." Set ".$ColName."=".$POSTVal." "
						." Where ".mysql_field_name($this->result,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->result,0)][$row])." LIMIT 1;";
					//echo $Query."<br />";
					$Data->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			$this->result = mysql_query($QueryString,$this->conn); 
			//echo $Query."<br />";
		}
		//Print Collumn Names
		$i=0;
		//Print Rows
		$odd="";
		$RecCount=0;
		while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{   
			$RecCount++;
			$odd=$odd==""?"odd":"";
			//echo '<tr class="'.$odd.'">';
			$i=0;
			foreach ($line as $col_value)
			{  
				$ColName=mysql_field_name($this->result,$i);
				echo '<tr><td><b>('.($i+1).') '.$ColName.'</b></td><td>';
				if($i==0)
				{
					$allow='readonly';
					echo '<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
						.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
				}
				else
					$allow='';
				
				$ColVal=htmlspecialchars($col_value);
				//echo "Value: ".$ColVal."<br/>";
				if((substr($ColName,0,4)=="Date") && $ColVal!="")
				{
					//echo "Value: ".strtotime($ColVal)."<br/>";
					$ColVal=date("Y-m-d",strtotime($ColVal)); 
					//print_r(date_get_last_errors());
				}
				
				echo '('.($i+1).') <input '.$allow.' type="text" size="'.(mysql_field_len($this->result,$i))
				.'" name="'.mysql_field_name($this->result,$i).'[]" value="'.$ColVal.'" /> </td></tr>';     
				$i++;
			}   
			echo '<tr><td colspan="2" style="background-color:#F4A460;"></td></tr>'; 
		} 
		echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
		//if (isset($_REQUEST['RecFrom']) && ($_REQUEST['RecFrom']-$RecPerPage)>=0)
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']-$RecPerPage).'" >&lt; Prev</a></b>&nbsp;&nbsp;&nbsp;';
		//if (($RecCount==$RecPerPage) && (mysql_num_rows($this->result)>0))
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']+$RecPerPage).'" >Next &gt;</a></b>';
		echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>'; 
	}
	function EditTableV3($QueryString)
	{ 
		$R=$this->do_sel_query($QueryString);
		echo "<h3>Total Records: ".$R."</h3>";
		if($R)
		{
			
			// Printing results in HTML 
			echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
				.'"><table rules="all" frame="box" cellpadding="5" cellspacing="1">';
			//Update Table Data
			$col=1;
			if(isset($_REQUEST['Delete']))
			{
				$Data=new DB();
				$Query="Delete from ".mysql_field_table($this->result,0)
					." Where ".mysql_field_name($this->result,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
				//echo $Query;
				$Data->do_ins_query($Query);
				$this->result = mysql_query($QueryString,$this->conn);
				//echo 'Query failed: ' . mysql_error();  
			}
			$FieldName=mysql_field_name($this->result,$col);
			if(isset($_POST[$FieldName]))
			{
				$Data=new DB();
				while ($col<mysql_num_fields($this->result))
				{
					$row=0;
					//echo $r."--".mysql_field_name($this->result,$col)."--".mysql_field_table($this->result,$col)
					//	.$_POST[mysql_field_name($this->result,$col)][$row];
					$ColName=mysql_field_name($this->result,$col);
					while($row<count($_POST[mysql_field_name($this->result,$col)]))
					{
						//$Loop=0;
						if (empty($_POST[mysql_field_name($this->result,$col)][$row]))
						{
							$POSTVal="NULL";

						}
						elseif(substr($ColName,0,4)=="Date")
						{
							$POSTVal="STR_TO_DATE('".$_POST[mysql_field_name($this->result,$col)][$row]."','%e/%c/%y')";

						}
						else
							$POSTVal="'".mysql_real_escape_string($_POST[mysql_field_name($this->result,$col)][$row])."' ";
							
						//echo "Field: ".$Loop."-".$ColName.":".$POSTVal."<br/>";
						
						$Query="Update ".mysql_field_table($this->result,$col)
							." Set `".$ColName."`=".$POSTVal." "
							." Where ".mysql_field_name($this->result,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->result,0)][$row])." LIMIT 1;";
						//echo $Query."<br />";
						$Data->do_ins_query($Query);
						$row++;
					}
					$col++;
					//echo "Col:".$col." ".$FieldName."<br />";
				}
				$this->result = mysql_query($QueryString,$this->conn); 
				//echo $Query."<br />";
			}
			$i=0;
			//Print Rows
			$odd="";
			$RecCount=0;
			echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;"></td></tr>'; 
			while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
			{   
				$RecCount++;
				$odd=$odd==""?"odd":"";
				//echo '<tr class="'.$odd.'">';
				$i=0;
				foreach ($line as $col_value)
				{  
					$ColName=mysql_field_name($this->result,$i);
					$ColVal=htmlspecialchars($col_value);
					//echo "Value: ".$ColVal."<br/>";
					$DateFormat="";
					$DateValue="";
					if((substr($ColName,0,4)=="Date"))
					{
						if($ColVal!="")
						{
							$DateValue=date("jS F Y l",strtotime($ColVal));
							$ColVal=date("d/m/y",strtotime($ColVal)); 
						}
						$DateFormat=" (d/m/yy)";
						//print_r(date_get_last_errors());
					}
					echo '<tr><th style="background-color:#FFDA91;font-weight:bold;">('.($i+1).') '.$ColName.'</th><td>';
					if($i==0)
					{
						$allow='readonly';
						echo '<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
							.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
					}
					else
						$allow='';
					echo $DateFormat.'<input '.$allow.' type="text" maxlength="'.(mysql_field_len($this->result,$i)).'" size="'.((mysql_field_len($this->result,$i)>40)?40:mysql_field_len($this->result,$i))
					.'" name="'.mysql_field_name($this->result,$i).'[]" value="'.$ColVal.'" /> '.$DateValue.' </td></tr>';     
					$i++;
				}   
				echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;"></td></tr>'; 
			} 
			echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
			//if (isset($_REQUEST['RecFrom']) && ($_REQUEST['RecFrom']-$RecPerPage)>=0)
			//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
			//		.intval($_REQUEST['RecFrom']-$RecPerPage).'" >&lt; Prev</a></b>&nbsp;&nbsp;&nbsp;';
			//if (($RecCount==$RecPerPage) && (mysql_num_rows($this->result)>0))
			//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
			//		.intval($_REQUEST['RecFrom']+$RecPerPage).'" >Next &gt;</a></b>';
			echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>'; 
		}
	}
	
	function EditTable($QueryString)
	{ 
		$this->result = mysql_query($QueryString,$this->conn);
		echo "Total Records: ".mysql_num_rows($this->result)."\n<br />";
		// Printing results in HTML 
		echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
			.'"><table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">';
		//Update Table Data
		$col=1;
		if(isset($_REQUEST['Delete']))
		{
			$Data=new DB();
			
			$Query="Delete from ".mysql_field_table($this->result,0)
				." Where ".mysql_field_name($this->result,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
			//echo $Query;
			$Data->do_ins_query($Query);
			$this->result = mysql_query($QueryString,$this->conn);
			//echo 'Query failed: ' . mysql_error();  
		}
		if(isset($_POST[mysql_field_name($this->result,$col)]))
		{
			$Data=new DB();
			while ($col<mysql_num_fields($this->result))
			{
				$row=0;
				//echo $r."--".mysql_field_name($this->result,$col)."--".mysql_field_table($this->result,$col)
				//	.$_POST[mysql_field_name($this->result,$col)][$row];
				while($row<count($_POST[mysql_field_name($this->result,$col)]))
				{
					$Query="Update ".mysql_field_table($this->result,$col)
						." Set ".mysql_field_name($this->result,$col)."='".mysql_real_escape_string($_POST[mysql_field_name($this->result,$col)][$row])."'"
						." Where ".mysql_field_name($this->result,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->result,0)][$row])." LIMIT 1;";
					//echo $Query."<br />";
					$Data->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			$this->result = mysql_query($QueryString,$this->conn); 
			echo $Query."<br />";
		}
		//Print Collumn Names
		$i=0;
		echo '<tr><td colspan="4" style="background-color:#F4A460;"></td></tr><tr>';
		while ($i<mysql_num_fields($this->result))
		{
			echo '<th>('.($i+1).') '.mysql_field_name($this->result,$i).'</th>';
			$i++;
			if (($i%4)==0 && $i>1)
					echo '</tr><tr>';
		}
		echo '</tr><tr><td colspan="4" style="background-color:#F4A460;"></td></tr>';
		//Print Rows
		$odd="";
		$RecCount=0;
		while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{   
			$RecCount++;
			$odd=$odd==""?"odd":"";
			echo '<tr class="'.$odd.'">';
			$i=0;
			foreach ($line as $col_value)
			{  
				if (($i%4)==0 && $i>1)
					echo '</tr><tr>';
				echo '<td>';
				if($i==0)
				{
					$allow='readonly';
					echo '<input type="checkbox" name="RowSelected[]" value="'.htmlspecialchars($col_value).'"/>&nbsp;&nbsp;'
						.'<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
						.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
				}
				else
					$allow='';
				echo '('.($i+1).') <input '.$allow.' type="text" size="'.((mysql_field_len($this->result,$i)>40)?40:mysql_field_len($this->result,$i))
				.'" name="'.mysql_field_name($this->result,$i).'[]" value="'.htmlspecialchars($col_value).'" /> </td>';     
				$i++;
			}   
			echo '</tr><tr><td colspan="4" style="background-color:#F4A460;"></td></tr>'; 
		} 
		echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
		//if (isset($_REQUEST['RecFrom']) && ($_REQUEST['RecFrom']-$RecPerPage)>=0)
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']-$RecPerPage).'" >&lt; Prev</a></b>&nbsp;&nbsp;&nbsp;';
		//if (($RecCount==$RecPerPage) && (mysql_num_rows($this->result)>0))
		//	echo '<b><a style="text-decoration:none;" href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?RecFrom='
		//		.intval($_REQUEST['RecFrom']+$RecPerPage).'" >Next &gt;</a></b>';
		echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>'; 
	}
	/* function Show1By1($QueryString)
	{
		$this->do_sel_query($QueryString);
		$rowcount=mysql_num_rows($this->result);
		if($rowcount>0)
		{
			echo "<table>";
			while ($line = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
			{ 
				foreach ($line as $col_value)
				{  
					$row=$this->get_row();
					echo "<tr><td><b>".mysql_field_name($this->result,$i)
								."</b></td><td>".$row[$txt]."</td></tr>";
			}
			echo "</table>";
		}
		else
		{
			echo "<h3>Data Not Found!</h3>";
		}
	} */
	public function __sleep()
	{
    	$this->do_close(); 
		return array('conn','result','Debug');
  	}	
  	public function __wakeup()
	{
    	$this->do_connect();
  	}

}  	
function EditTable($QueryString)
{
	$Data=new DB();
	$Data->EditTable($QueryString);
}
?>
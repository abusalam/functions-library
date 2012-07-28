<?php  
require_once( 'database.php');
$Data=new DB();
$query = "SELECT FROM_UNIXTIME(left(`date`,10)) as `Date Time`,`body` as `SMS` FROM `SQLiteAdmin` WHERE `thread_id`=7 and `body` like '%-BOL:%'";

$result = $Data->do_query($query) or die('Query failed: ' . mysql_error());    
// Printing results in HTML  
echo "<table rules='all' border='1'>\n";  
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) 
{      
echo "\t<tr>\n";
foreach ($line as $col_value)
{     
    echo "\t\t<td>$col_value</td>\n";
}      
echo "\t</tr>\n";  
}  
echo "</table>\n";  
// Free resultset  
mysql_free_result($result);    
// Closing connection  
mysql_close($link);  
?>  
<?PHP
//********************************************************************************************************

 $SERVER = "localhost";
 $USER = "root";
 $PASS = "";
 $DB = "iherb";
 $dbsql = mysqli_connect($SERVER,$USER,$PASS);
 mysqli_select_db($dbsql,$DB);

 function SQLQ($Q){
 	global $dbsql;
 	mysqli_query($dbsql,"SET NAMES 'utf8'");
 	mysqli_query($dbsql,"SET CHARACTER SET 'utf8'");
// 	mysql_select_db($db,$dbsql);
	$outQ = mysqli_query($dbsql,$Q);

 	return $outQ;
 }
?>
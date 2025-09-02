<?php
// Move Data to Archive Tables
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("ajax_lib.php");


// if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) &&
if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) &&
	isset($_REQUEST['yearmonth']) ) {

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	// Start a Transaction
	$sql = "START TRANSACTION ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Revert Archive bit on contest table
	$sql = "UPDATE contest SET archived = '0' WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 1. Archive `entry` table
	// ------------------------
	// Determine Number of rows to archived
	$sql = "SELECT IFNULL(COUNT(*), 0) AS num_rows FROM entry WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_rows = $row['num_rows'];
	if ($num_rows == 0)
		return_error("Nothing to archive from entry table", __FILE__, __LINE__);

	// Remove existing ar_entry records
	$sql = "DELETE FROM ar_entry WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Copy entry to ar_entry
	$sql = "INSERT INTO ar_entry SELECT * FROM entry WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to archive data from 'entry' table", __FILE__, __LINE__);

	$sql = "DELETE FROM entry WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to purge data from 'entry' table", __FILE__, __LINE__);

	// 2. Archive `pic` table
	// ------------------------
	// Determine Number of rows to archived
	$sql = "SELECT IFNULL(COUNT(*), 0) AS num_rows FROM pic WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_rows = $row['num_rows'];
	if ($num_rows == 0)
		return_error("Nothing to archive from pic table", __FILE__, __LINE__);

	// Remove existing ar_pic records
	$sql = "DELETE FROM ar_pic WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Copy entry to ar_pic
	$sql = "INSERT INTO ar_pic SELECT * FROM pic WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to archive data from 'pic' table", __FILE__, __LINE__);

	$sql = "DELETE FROM pic WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to purge data from 'pic' table", __FILE__, __LINE__);

	// 3. Archive `pic_result` table
	// ------------------------
	// Determine Number of rows to archived
	$sql = "SELECT IFNULL(COUNT(*), 0) AS num_rows FROM pic_result WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_rows = $row['num_rows'];
	if ($num_rows == 0)
		return_error("Nothing to archive from pic_result table", __FILE__, __LINE__);

	// Remove existing ar_pic records
	$sql = "DELETE FROM ar_pic_result WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Copy entry to ar_pic
	$sql = "INSERT INTO ar_pic_result SELECT * FROM pic_result WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to archive data from 'pic_result' table", __FILE__, __LINE__);

	$sql = "DELETE FROM pic_result WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to purge data from 'pic_result' table", __FILE__, __LINE__);

	// 4. Archive `rating` table
	// ------------------------
	// Determine Number of rows to archived
	$sql = "SELECT IFNULL(COUNT(*), 0) AS num_rows FROM rating WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_rows = $row['num_rows'];
	if ($num_rows == 0)
		return_error("Nothing to archive from rating table", __FILE__, __LINE__);

	// Remove existing ar_pic records
	$sql = "DELETE FROM ar_rating WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Copy entry to ar_pic
	$sql = "INSERT INTO ar_rating SELECT * FROM rating WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to archive data from 'pic_result' table", __FILE__, __LINE__);

	$sql = "DELETE FROM rating WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != $num_rows)
		return_error("Unable to purge data from 'pic_result' table", __FILE__, __LINE__);

	// 5. Turn Archive Flag ON on contest table
	$sql = "UPDATE contest SET archived = '1', web_pics = 'ar,ar' WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6. Create htaccess redirection - Overwrite existing file
	$htaccess = "../../salons/$yearmonth/upload/.htaccess";
	file_put_contents($htaccess, "# Redirection instructions created on " . date("Y-m-d H:i") . chr(13) . chr(10));
	file_put_contents($htaccess, 'RewriteEngine On' . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($htaccess, 'RewriteBase "/salons/' . $yearmonth . '/upload/"' . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($htaccess, 'RewriteCond %{REQUEST_URI} !share/' . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($htaccess, 'RewriteCond %{REQUEST_URI} !dropped/' . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($htaccess, 'RewriteCond %{REQUEST_URI} !full/' . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($htaccess, 'RewriteRule "^([^/]+)/(tn|tnl)?/?([^/]+)$" "$1/ar/$3" [R]' . chr(13) . chr(10), FILE_APPEND);

	// COMMIT
	$sql = "COMMIT";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Return success
	$resArray = array();
	$resArray['success'] = TRUE;
	$resArray['msg'] = "Contest Data has been archived. Take a backup.";
	echo json_encode($resArray);

}
else
	return_error( "Invalid Parameters !", __FILE__, __LINE__);
?>

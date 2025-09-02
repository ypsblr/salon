<?php
// session_start();
include("../inc/session.php");
// include("../inc/connect.php");
include("../inc/lib.php");

if (isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	// include ("inc/load_common_data.php");

	// Determine Database and mysqldump program name
	if (preg_match("/localhost/i", $_SERVER['SERVER_NAME'])) {
		// For *** localhost ***
		$host = "localhost";
		$dbname = "yps_salondb";
		$mysqldump = "/usr/local/Cellar/mysql@5.7/5.7.34/bin/mysqldump";
		$user = "ypsin_salondbadm";
		$password = "MCnLOT8045FVzC1Y";
		$name = "Local Database on the System";
		$backup_file = "server_" . $dbname . "_" . date("Y-m-d-H-s") . ".mysql";
		// $DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "yps_salondb");
	}
	else if (preg_match("/salontest/i", $_SERVER['SERVER_NAME'])) {
		// For *** Server Test Database ***
		$host = "localhost";
		$dbname = "ypsin_salontestdb";
		$mysqldump = "mysqldump";
		$user = "ypsin_salondbadm";
		$password = "MCnLOT8045FVzC1Y";
		$name = "Database on Test System";
		$backup_file = "server_" . $dbname . "_" . date("Y-m-d-H-s") . ".mysql";
		// $DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salontestdb");
	}
	else {
		// For *** Server Main Database ***
		$host = "localhost";
		$dbname = "ypsin_salondb";
		$mysqldump = "mysqldump";
		$user = "ypsin_salondbadm";
		$password = "MCnLOT8045FVzC1Y";
		$name = "Database on Production Server";
		$backup_file = "server_" . $dbname . "_" . date("Y-m-d-H-s") . ".mysql";
		// $DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salondb");
	}

	// Set up Headers
	// $backup_path = $_SERVER['DOCUMENT_ROOT'] . "/db_backup/$backup_file";
	$log_file = $_SERVER['DOCUMENT_ROOT'] . "/logs/mysqldump_err.txt";
	// $last_line = system("$mysqldump --opt --no-tablespaces --log-error='$log_file' -r $backup_path -h$host -u$user -p$password $dbname", $err);
	// if ($err != 0) {
	// 	log_error("Backup of $name failed with error code ($err). Returned Line : $last_line", __FILE__, __LINE__);
	// }
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$backup_file\"");
	// header("Content-Length: " . filesize($backup_path)); // 64MB
	if (! passthru("$mysqldump --opt --no-tablespaces --log-error='$log_file' -h$host -u$user -p$password $dbname", $err))
		log_error("Backup of $name failed with error code ($err)", __FILE__, __LINE__);
}
else {
	echo "Invalid Session";
}

?>

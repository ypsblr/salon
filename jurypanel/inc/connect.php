<?php
// Set PHP Timezone
date_default_timezone_set("Asia/Kolkata");

// For *** localhost ***
// $DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "yps_salondb");
// For *** Server Main Database ***
//$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salondb");
// For *** Server Test Database ***
//$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salontestdb");

if (preg_match("/localhost/i", $_SERVER['SERVER_NAME'])) {
	// For *** localhost ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "yps_salondb");
}
else if (preg_match("/192\.168\.1/i", $_SERVER['SERVER_NAME'])) {
	// For *** local network testing ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "yps_salondb");
}
else if (preg_match("/salontest/i", $_SERVER['SERVER_NAME'])) {
	// For *** Server Test Database ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salontestdb");
}
else {
	// For *** Server Main Database ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salondb");
}

if (mysqli_connect_errno())
	die("DB Connection failed: " . mysqli_connect_error());

// Force Character Set to 8 bit
mysqli_set_charset($DBCON, 'latin1');

// Set Timezone
mysqli_query($DBCON, "SET time_zone = '+5:30' ") or die(mysqli_error($DBCON));

// Set Errorlog
// error_log value is better set in htaccess file using php_value directive
// ini_set("error_log", $_SERVER["DOCUMENT_ROOT"] . "/logs/error_log");

?>

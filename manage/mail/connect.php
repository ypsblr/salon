<?php
if (preg_match("/public_html\/salontest\/manage/i", __DIR__)) {
	// For *** Server Test Database ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salontestdb");
	define("SERVER_NAME", "salontest");
	define("SERVER_ADDRESS", "salontest.ypsbengaluru.in");
}
elseif (preg_match("/public_html\/salon\/manage/i", __DIR__)) {
	// For *** Server Main Database ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "ypsin_salondb");
	define("SERVER_NAME", "salon");
	define("SERVER_ADDRESS", "salon.ypsbengaluru.in");
}
else {
	// For *** localhost ***
	$DBCON = mysqli_connect("localhost", "ypsin_salondbadm", "MCnLOT8045FVzC1Y", "yps_salondb");
	define("SERVER_NAME", "localhost");
	define("SERVER_ADDRESS", "salon.localhost");
}

define("DOCUMENT_ROOT", "../..");
define("SALON_EMAIL", "salon@ypsbengaluru.in");
ini_set("error_log", "../../logs/error_log");

if (mysqli_connect_errno())
	die("DB Connection failed: " . mysqli_connect_error());

// Force Character Set to 8 bit
mysqli_set_charset($DBCON, 'latin1');

// Set Timezone
mysqli_query($DBCON, "SET time_zone = '+5:30' ") or die(mysqli_error($DBCON));
?>

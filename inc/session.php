<?php
// PHP Settings
date_default_timezone_set("Asia/Kolkata");
// Global Constants
define("THIS", basename($_SERVER['PHP_SELF'], ".php"));
// define("PIC_MAX_HEIGHT", MAX_PIC_HEIGHT);
// define("PIC_MAX_WIDTH", MAX_PIC_WIDTH);
// define("PIC_MAX_FILE_SIZE", MAX_PIC_FILE_SIZE_IN_MB * 1024 * 1024);
define("INDIA_DATE", date("Y-m-d"));
define("DATE_FORMAT", "D, M j, Y");
define("THEME", "body-orange");

if (session_status() == PHP_SESSION_NONE) {
    // Set session saving path
    if ( ! is_dir(__DIR__ . "/session") )
        mkdir(__DIR__ . "/session", 0755);

    //if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) == 0 )
    session_save_path(__DIR__ . "/session");

    session_start();
}

include_once("inc/connect.php");
include_once("inc/lib.php");
include_once("inc/get_contest.php");

// Encryption Key
$_SESSION['SALONBOND'] = sprintf("%x%x", rand(100001, 999999), rand(100001, 999999));

$_SESSION['SALON_SESSION_START'] = time();

define("SALONBOND", $_SESSION['SALONBOND']);
define("SALON_SESSION_DURATION", 60 * 60 * 1);	// Keep alive for 1 Hour
define("SALON_SESSION_KEY", "YPS1971SALON");

// Encrypt SESSION VARIABLES
// Moved to scripts.php to allow other included PHPs toset session variables
// $session_vars = [];
// foreach($_SESSION as $session_key => $session_value) {
// 	if (! in_array($session_key, array('info_msg', 'success_msg', 'err_msg')))
// 		$session_vars[$session_key] = $session_value;
// }
// define("SALON_SESSION_DATA", CryptoJsAes::encrypt(json_encode($session_vars), SALON_SESSION_KEY));

if (isset($_SESSION['USER_ID'])) {
	// $session_id = $entry_id = $uid = $_SESSION['USER_ID'];
	include_once("inc/get_user.php");
}

?>

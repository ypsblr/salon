<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("ajax_lib.php");

use Nullix\CryptoJsAes\CryptoJsAes;
// include("../inc/CryptoJsAes.php");

define("SALON_SESSION_DURATION", 60 * 60 * 1);	// Keep alive for 1 Hour
define("SALON_SESSION_KEY", "YPS1971SALON");

function return_session_status($status, $errmsg = "") {
	$retval = [];
	$retval['success'] = ($errmsg == "");
	$retval['msg'] = $errmsg;
	$retval['status'] = $status;
	echo json_encode($retval);
	die;
}

if (isset($_REQUEST['status']) && isset($_REQUEST['salond'])) {

	$session = json_decode(CryptoJsAes::decrypt($_REQUEST['salond'], SALON_SESSION_KEY), true);
	// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
	// debug_dump("DECRYPTED", $session, __FILE__, __LINE__);

	if ($_REQUEST['status'] == "LOGGED_OUT") {
		// Don't bother to check the idle time
		// Populate session only with logged-out session variables
		foreach($session as $session_key => $session_value) {
			if (! in_array($session_key, array('USER_ID', 'LOGIN_ID', 'USER_NAME', 'USER_AVATAR')))
				$_SESSION[$session_key] = $session_value;
		}
		return_session_status($_REQUEST['status']);
	}
	else {
		if ( $_REQUEST['status'] == "LOGGED_IN" && isset($session['SALON_SESSION_START']) && (time() - $session['SALON_SESSION_START']) < SALON_SESSION_DURATION ) {
			// Maintain Logged in status
			$_SESSION = $session;
			return_session_status($_REQUEST['status']);
		}
		else {
			// Change Status to LOGGED_OUT status
			// Unset logged in variables for future sessions
			if (isset($_SESSION['USER_ID'])) unset($_SESSION['USER_ID']);
			if (isset($_SESSION['LOGIN_ID'])) unset($_SESSION['LOGIN_ID']);
			if (isset($_SESSION['USER_NAME'])) unset($_SESSION['USER_NAME']);
			if (isset($_SESSION['USER_AVATAR'])) unset($_SESSION['USER_AVATAR']);
			// Return LOGGED_OUT status
			return_session_status("LOGGED_OUT");
		}
	}
}
else
	return_session_status("ERROR", "Error keeping session alive");
?>

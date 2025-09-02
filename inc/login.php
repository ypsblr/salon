<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("../inc/lib.php");
include_once('../inc/simple_html_dom.php');

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
// require("../inc/CryptoJsAes.php");


// Helper Functions
function get_img_src($html) {
	$doc = str_get_html($html);
	//debug_dump("html_doc", $doc, __FILE__, __LINE__);
	$img_src = "";
	foreach($doc->find('img') as $img_element){
		//debug_dump("img_element", $img_element, __FILE__, __LINE__);
		$img_src = $img_element->src;
	}

	return $img_src;
}

// DB handling functions

function get_user_profile($email_id, $yps_login_id, $phone = "") {
	global $DBCON;

	$sql = "SELECT * FROM profile WHERE email = '$email_id' OR yps_login_id = '$yps_login_id' ";
	if ($phone != "")
		$sql .= " OR phone LIKE '%" . substr($phone, -8) . "%' ";		// check for last 8 digits
	debug_dump("SQL", $sql, __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
		if ( $row['profile_disabled'] == '1' ) {
			if ( $row['profile_merged_with'] == 0)
				handle_error("This profile has been disabled. Please write to YPS for fixing.", __FILE__, __LINE__);
			else {
				$profile_id = $row['profile_merged_with'];
				$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' ";
				$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if ( mysqli_num_rows($query) > 0 ) {
					$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
					return $row;
				}
				else
					return false;
			}
		}
		else
			return $row;
	}
	else
		return false;
}

function create_new_yps_profile($user) {
	// Populate Session variables & go to new user registration page
	$_SESSION['first_name'] = name_ucfirst($user['first_name']);
	$_SESSION['last_name'] = name_ucfirst($user['last_name']);
	$_SESSION['gender'] = strtoupper($user['gender']);
	$_SESSION['phone'] = $user['phone'];
	$_SESSION['email'] = $user['email'];
	$_SESSION['yps_login_id'] = $user['yps_login_id'];
	$_SESSION['member_status'] = "active";
	// Copy Avatar from YPS
	if ($user['avatar'] != "") {
		$_SESSION['avatar'] = $user['avatar'];

		// Get avatar file name from the html
		$yps_avatar_url = get_img_src($user['avatar']);
		// debug_dump("yps_avatar_url", $yps_avatar_url, __FILE__, __LINE__);
		if ($yps_avatar_url != "") {
			// Copy Avatar File to avatar folder
			$avatar_file = $user['login'] . "-" . mt_rand(1111,9999) . ".jpg";
			// Copy to a temporary file
			file_put_contents("../res/tmp/" . $avatar_file, file_get_contents($yps_avatar_url));
			if (file_exists("../res/tmp/" . $avatar_file)) {
				// Resize and Copy to avatar directory
				list($width, $height) = getimagesize("../res/tmp/" . $avatar_file);
				if ($width > 0 && $height > 0) {
					$avatar_height = 120;
					$avatar_width = $width / $height * $avatar_height;
					$resized_image = imagecreatetruecolor($avatar_width, $avatar_height);
					$uploaded_image = imagecreatefromjpeg("../res/tmp/" . $avatar_file);
					imagecopyresized($resized_image, $uploaded_image, 0, 0, 0, 0, $avatar_width, $avatar_height, $width, $height);
					imagejpeg($resized_image, "../res/avatar/" . $avatar_file, 100);
					$_SESSION['avatar'] = $avatar_file;
				}
			}
		}
	}
}

function get_profile_id($email) {
	global $DBCON;

	$sql = "SELECT * FROM profile WHERE email = '$email' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$user = mysqli_fetch_array($query);
		return $user['profile_id'];
	}
	return '0';
}


//
// First Validate Inputs
//
if (empty($_SESSION['SALONBOND']) || empty($_REQUEST['ypsd'])) {
	// Must be an attack. Exit with error
	handle_error("Invalid Request. ", __FILE__, __LINE__);
}

// Decrypt the inputs
$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);

//
// Handle Request for Signing Up
//
if (isset($param['sign_up'])) {
	$email_id = trim($param['login_id']);
	$phone = trim($param['phone']);
	$yps_login_id = strtoupper(trim($param['login_id']));
	$profile = get_user_profile($email_id, $yps_login_id, $phone);

	//
	// Exit if the email is already registered on the Salon website
	//
	$profile_id = 0;
	if ( $profile == false )	{
		if (is_an_email(trim($param['login_id'])) && ! empty($param['phone'])) {
			$_SESSION['email'] = $param['login_id'];
			$_SESSION['phone'] = $param['phone'];
			header('Location: /sign_up.php');
			printf("<script>location.href='/sign_up.php'</script>");
			die();
		}
	}
	else {		// Salon profile exists
		$profile_id = $profile['profile_id'];
		list($err_msg, $is_yps_member, $yps_user) = yps_getuserbyemail(trim($param['login_id']));
		if ($is_yps_member) {
			if ($profile['yps_login_id'] != $yps_user['yps_login_id']) {
				// YPS Member with existing salon profile
				// Update yps_login_id and empty password from yps profile
				$sql = "UPDATE profile SET yps_login_id = '" . $yps_user['yps_login_id'] . "', password = '' WHERE profile_id = '$profile_id' ";
				mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
			create_new_yps_profile($yps_user);
			header('Location: /sign_up.php');
			printf("<script>location.href='/sign_up.php'</script>");
			die();
		}
		else {	// not YPS member
			if (is_an_email(trim($param['login_id'])) && ! empty($param['phone'])) {
				$_SESSION['email'] = $param['login_id'];
				$_SESSION['phone'] = $param['phone'];
				header('Location: /sign_up.php');
				printf("<script>location.href='/sign_up.php'</script>");
				die();
			}
		}
	}
	die();
}

//
// Handle Password Reset
//
if(isset($param['login_reset_password'])) {
	$login_id = strtoupper(trim($param['login_id']));
	$login_email = trim($param['login_id']);

	// Check if the profile exists
	if (! ($row = get_user_profile($login_email, $login_id))) {
		handle_error("Incorrect Email/YPS Login ID. Please Sign Up if not already registered.", __FILE__, __LINE__);
	}

	if ($row['yps_login_id'] != "") {
		handle_error("Dear YPS Member, please use password reset facilities on YPS website (www.ypsbengaluru.com).", __FILE__, __LINE__);
	}

	// Check if a new password was generated today. If so, send the same. Else Generate new Password.
	if ($row['last_password_reset'] == date('Y-m-d'))
		$new_password = $row['password'];
	else {
		// Generate New Password
		$new_password = "TMP" . mt_rand(11111,99999);
		$last_password_reset = date('Y-m-d');
		$sql = "UPDATE profile SET password = '$new_password', last_password_reset = '$last_password_reset' WHERE profile_id = '" . $row['profile_id'] . "' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Send Email
	$url = http_method() . $_SERVER['HTTP_HOST'];
	$replace_text = array($row['profile_name'], $row['email'], $new_password, $url);
	$text = array('{{NAME}}', '{{EMAIL}}', '{{PASSWORD}}', '{{URL}}');
	if (send_salon_email($row['email'], "Your request completed", 'password_reset', $text, $replace_text))
		$_SESSION['info_msg'] = "Generated password has been sent to your email. Please wait for the email. It may take some time.";
	else
		$_SESSION['err_msg'] = "Sending email with password failed !";

	// Destroy any saved cookies
	delete_session_variables();
	//delete_cookies();

	header("Location: /index.php");
	printf("<script>location.href='/index.php'</script>");
	die();
}

//
// Handle Login
//
if(isset($param['login_check']) && (! empty($param['login_id'])) && (! empty($param['login_password'])) ) {

	$login_id = strtoupper(trim($param['login_id']));
	$login_email = trim($param['login_id']);
	$password = trim($param['login_password']);

	// Check if the user is already registered
	$profile = get_user_profile($login_email, $login_id);
	debug_dump("profile", $profile, __FILE__, __LINE__);
	$profile_id = 0;
	$yps_login_id = "";
	// User already registered. Proceed with LOGIN
	if ($profile == false) {
		// Check for yps_user
		list($err_msg, $is_yps_member, $yps_user) = yps_getuserbyemail($login_id);
		if ($is_yps_member) {
			$yps_login_id = $yps_user['yps_login_id'];
			// Check for profile with the email stored in yps_user table
			$profile = get_user_profile($yps_user['email'], strtoupper($yps_user['email']));
			debug_dump("profile", $profile, __FILE__, __LINE__);
			if ($profile != false) {
				$profile_id = $profile['profile_id'];
				if ($profile['yps_login_id'] != $yps_user['yps_login_id']) {
					$sql = "UPDATE profile SET yps_login_id = '$yps_login_id', password = '' WHERE profile_id = '$profile_id' ";
					debug_dump("SQL", $sql, __FILE__, __LINE__);
					mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				}
			}
		}
	}
	if ($profile_id == 0) {
		handle_error("You are yet to Sign Up. Please Sign Up now using your Email or YPS Member ID.", __FILE__, __LINE__);
	}

	// Matching profile found
	// If yps_login_id is missing in profile, update it
	if ($is_yps_member && $yps_login_id != "" && $profile['yps_login_id'] != $yps_user['yps_login_id']) {
		$sql = "UPDATE profile SET yps_login_id = '$yps_login_id', password = '' WHERE profile_id = '$profile_id' ";
		debug_dump("SQL", $sql, __FILE__, __LINE__);
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// No more a yps_member but yps_login_id is not blank on profile, remove yps_login_id from profile, reset password
	if ( $yps_login_id == "" && ($profile['yps_login_id'] != "") ) {
		// Check if a new password was generated today. If so, send the same. Else Generate new Password.
		if ($profile['last_password_reset'] == date('Y-m-d')) {
			$new_password = $profile['password'];
			$last_password_reset = $profile['last_password_reset'];
		}
		else {
			// Generate New Password
			$new_password = "TMP" . mt_rand(11111,99999);
			$last_password_reset = date('Y-m-d');
		}
		$sql  = "UPDATE profile ";
		$sql .= "   SET password = '$new_password' ";
		$sql .= "     , last_password_reset = '$last_password_reset' ";
		$sql .= "     , yps_login_id = '' ";
		$sql .= " WHERE profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Send Email
		$url = http_method() . $_SERVER['HTTP_HOST'];
		$replace_text = array($profile['profile_name'], $profile['email'], $new_password, $url);
		$text = array('{{NAME}}', '{{EMAIL}}', '{{PASSWORD}}', '{{URL}}');
		send_salon_email($profile['email'], "YPS Membership status and password have been reset", 'password_reset', $text, $replace_text);
		handle_error("Your YPS membership is no more valid. Hence a temporary password has been generated and sent to your email. Please wait for the email and login using this password.", __FILE__, __LINE__);
	}
	// Retrieve latest profile
	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$profile = mysqli_fetch_array($query);

	// Now perform Authentication
	// If category != YPS, check password and login
	if ($profile['yps_login_id'] == "" && $profile['password'] != $password)
		handle_error("Your Login credentials are Incorrect. Check your Login ID and Password. Use [Reset Password] if you do not remember the password.", __FILE__, __LINE__);

	// Authenticate YPS Member
	if ($profile['yps_login_id'] != "") {
		list($err_msg) = yps_login($profile['yps_login_id'], $password, $profile['email']);
		if ($err_msg != "")
			handle_error("Unable to Log-in using YPS Credentials : " . $err_msg, __FILE__, __LINE__);
	}
	// Check against black_list
	$blacklist_in_vogue = false;
	list($blacklist_match, $blacklist_name) = check_blacklist($profile['profile_name'], $profile['email'], $profile['phone']);
	if ($blacklist_match == "MATCH" || $blacklist_match == "SIMILAR") {
		$blacklist_in_vogue = true;
		if ($profile['blacklist_match'] == "")
			mark_blacklist($profile['profile_id'], $blacklist_match);
		if (check_exception($profile['email'])) {
			$blacklist_in_vogue = false;
		}
	}
	if ($blacklist_in_vogue) {
		$_SESSION['info_msg'] = "You will not be able to participate in this Salon due to a match with Blacklist names published by a Patronage Organization. ";
		$_SESSION['info_msg'] .= "If this is not correct please contact the Salon Chairman. ";
	}
	$_SESSION['success_msg'] = "Login Successful. Opening your Page...";
	set_session_variables($profile);
	header("Location: /user_panel.php");
	printf("<script>location.href='/user_panel.php'</script>");
	die();
}

// Should not reach here
handle_error("Invalid Request!", __FILE__, __LINE__);
?>

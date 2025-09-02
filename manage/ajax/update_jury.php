<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

define("AVATAR_FOLDER", "../contests/avatar");

// Generate a random name for an avatar
function avatar_file_name($yps_login_id) {

	do {
		$file_name = strtoupper($yps_login_id) . "-" . rand(1000, 9999) . ".jpg";
	} while (file_exists(AVATAR_FOLDER . "/" . $file_name));

	return $file_name;
}


// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['user_id']) && isset($_REQUEST['edit-update']) ) {

	$user_id = $_REQUEST['user_id'];
	$user_name = strtoupper($_REQUEST['user_name']);
	$login = $_REQUEST['login'];
	$password = $_REQUEST['password'];
	$type = "JURY";
	$title = mysqli_real_escape_string($DBCON, $_REQUEST['title']);
	$honors = mysqli_real_escape_string($DBCON, $_REQUEST['honors']);
	$email = $_REQUEST['email'];
	$profile_file = $_REQUEST['profile_file'];
	$status = isset($_REQUEST['status']) ? "ACTIVE" : "INACTIVE";
	$profile = "";

	// Avatar
	$prev_avatar = $_REQUEST['prev_avatar'];
	$target_folder = "../../res/jury";

	if (isset($_FILES) && $_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE) {
		// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
		if (! ($_FILES['avatar']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading avatar. Try again", __FILE__, __LINE__, true);

		$file_name = $login . "." . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
		$target_file = $target_folder . "/" . $file_name;

		if (! move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file))
			return_error("Error in copying avatar", __FILE__, __LINE__, true);
		$avatar = $file_name;
	}
	else
		$avatar = $prev_avatar;

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert new Jury
	if ($user_id == 0) {
		// Find next jury id
		$sql = "SELECT MAX(user_id) as last_user_id FROM user ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$user_id = $row['last_user_id'] + 1;
		// Insert
		$sql  = "INSERT INTO user(user_id, user_name, login, password, type, avatar, title, honors, email, ";
		$sql .= "       profile, profile_file, status) ";
		$sql .= "     VALUES('$user_id', '$user_name', '$login', '$password', '$type', '$avatar', '$title', '$honors', '$email', ";
		$sql .= "     '$profile', '$profile_file', '$status' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add jury", __FILE__, __LINE__, true);
	}
	else {
		// Update
		$sql  = "UPDATE user ";
		$sql .= "   SET user_name = '$user_name' ";
		$sql .= "     , login = '$login' ";
		$sql .= "     , password = '$password' ";
		$sql .= "     , avatar = '$avatar' ";
		$sql .= "     , title = '$title' ";
		$sql .= "     , honors = '$honors' ";
		$sql .= "     , email = '$email' ";
		$sql .= "     , profile_file = '$profile_file' ";
		$sql .= "     , status = '$status' ";
		$sql .= " WHERE user_id = '$user_id' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to update jury (or) no changes have been made", __FILE__, __LINE__, true);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Return saved values for update to the table
	$sql  = "SELECT user_id, user_name, login, password, type, avatar, title, honors, email, profile_file, status ";
	$sql .= "  FROM user WHERE user_id = '$user_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['user'] = $row;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

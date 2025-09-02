<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

define("AVATAR_FOLDER", "../contests/avatar");

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

// Generate a random name for an avatar
function avatar_file_name($member_name) {

	$name = $member_name;
	if (substr($name, 0, 3) == "Mr.")
		$name = substr($name, 3);
	elseif (substr($name, 0, 3) == "Ms.")
		$name = substr($name, 3);
	elseif (substr($name, 0, 4) == "Mrs.")
		$name = substr($name, 4);
	elseif (substr($name, 0, 3) == "Dr.")
		$name = substr($name, 3);

	$name = strtolower(ltrim(rtrim($name)));

	$file_name = "";
	for ($i = 0; $i < strlen($name); ++ $i) {
		$c = substr($name, $i, 1);
		if ($c >= 'a' && $c <= 'z')
			$file_name .= $c;
		else
			$file_name .= "_";
	}
	return $file_name;
}


// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['member_id']) && isset($_REQUEST['edit-update']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$member_id = $_REQUEST['member_id'];
	$is_edit_member = ($_REQUEST['is_edit_member'] == '1');

	$member_login_id = $_REQUEST['member_login_id'];
	$member_password = $_REQUEST['member_password'];
	$level = $_REQUEST['level'];
	$sequence = $_REQUEST['sequence'];
	$role = $_REQUEST['role'];
	$role_name = mysqli_real_escape_string($DBCON, $_REQUEST['role_name']);
	$member_group = $_REQUEST['member_group'];
	$member_name = mysqli_real_escape_string($DBCON, $_REQUEST['member_name']);
	$honors = mysqli_real_escape_string($DBCON, $_REQUEST['honors']);
	$email = $_REQUEST['email'];
	$phone = $_REQUEST['phone'];
	$profile = "";
	$permissions = $_REQUEST['permissions'];
	if (isset($_REQUEST['sections']) && is_array($_REQUEST['sections']))
		$sections = implode("|", $_REQUEST['sections']);
	else
		$sections = "";
	$is_reviewer = (isset($_REQUEST['is_reviewer']) ? "1" : "0");
	$is_print_coordinator = (isset($_REQUEST['is_print_coordinator']) ? "1" : "0");
	$allow_downloads = (isset($_REQUEST['allow_downloads']) ? "1" : "0");
	if (isset($_REQUEST['address']) && is_array($_REQUEST['address'])) {
		$address_lines = [];
		foreach($_REQUEST['address'] as $line) {
			if (! empty($line))
				$address_lines[] = $line;
		}
		$address = mysqli_real_escape_string($DBCON, implode("|", $address_lines));
	}
	else
		$address = "";

	// Avatar
	$prev_avatar = $_REQUEST['prev_avatar'];
	$target_folder = "../../salons/$yearmonth/img/com";
	make_path($target_folder);

	if (isset($_FILES) && $_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE) {
		// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
		if (! ($_FILES['avatar']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading avatar. Try again", __FILE__, __LINE__, true);

		$file_name = avatar_file_name($member_name) . "." . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
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
	if (! $is_edit_member) {
		// Find next member_id id
		$sql = "SELECT MAX(member_id) as last_member_id FROM team WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$member_id = $row['last_member_id'] + 1;
		// Insert
		$sql  = "INSERT INTO team ( yearmonth, member_id, member_login_id, member_password, level, sequence, role, ";
		$sql .= "       role_name, member_group, member_name, honors, phone, email, profile, avatar, permissions, ";
		$sql .= "       sections, is_reviewer, is_print_coordinator, allow_downloads, address ) ";
		$sql .= "     VALUES ('$yearmonth', '$member_id', '$member_login_id', '$member_password', '$level', '$sequence', '$role', ";
		$sql .= "     '$role_name', '$member_group', '$member_name', '$honors', '$phone', '$email', '$profile', '$avatar', '$permissions', ";
		$sql .= "     '$sections', '$is_reviewer', '$is_print_coordinator', '$allow_downloads', '$address' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add team member", __FILE__, __LINE__, true);
	}
	else {
		// Update
		$sql  = "UPDATE team ";
		$sql .= "   SET member_login_id = '$member_login_id' ";
		$sql .= "     , member_password = '$member_password' ";
		$sql .= "     , level = '$level' ";
		$sql .= "     , sequence = '$sequence' ";
		$sql .= "     , role = '$role' ";
		$sql .= "     , role_name = '$role_name' ";
		$sql .= "     , member_group = '$member_group' ";
		$sql .= "     , member_name = '$member_name' ";
		$sql .= "     , honors = '$honors' ";
		$sql .= "     , phone = '$phone' ";
		$sql .= "     , email = '$email' ";
		$sql .= "     , profile = '$profile' ";
		$sql .= "     , avatar = '$avatar' ";
		$sql .= "     , permissions = '$permissions' ";
		$sql .= "     , sections = '$sections' ";
		$sql .= "     , is_reviewer = '$is_reviewer' ";
		$sql .= "     , is_print_coordinator = '$is_print_coordinator' ";
		$sql .= "     , allow_downloads = '$allow_downloads' ";
		$sql .= "     , address = '$address' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND member_id = '$member_id' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to update jury (or) no changes have been made", __FILE__, __LINE__, true);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Return saved values for update to the table
	$sql  = "SELECT * FROM team ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND member_id = '$member_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['member'] = $row;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

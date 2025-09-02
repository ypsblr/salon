<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
	isset($_REQUEST['upload_yps_members']) ) {

	$yearmonth = $_SESSION['admin_yearmonth'];

	// Upload CSV file
	if (isset($_FILES) && $_FILES['member-file']['error'] != UPLOAD_ERR_NO_FILE) {
		// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
		if (! ($_FILES['member-file']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading YPS Member CSV file. Try again", __FILE__, __LINE__, true);

		$target_file = "../../salons/$yearmonth/blob/yps_member.csv";

		if (! move_uploaded_file($_FILES['member-file']['tmp_name'], $target_file))
			return_error("Error in copying YPS Member upload file", __FILE__, __LINE__, true);
	}
	$csv = fopen($target_file, "r");
	if (! $csv) {
		$_SESSION['error_msg'] = "Unable to open CSV file!";
		die();
	}
	echo '<script>console.log("Truncating table")</script>';
	$sql  = "TRUNCATE TABLE yps_user ";
	mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = fgetcsv($csv)) {
	    echo '<script>console.log("Inserting rows !!")</script>';
		list($yps_login_id, $first_name, $last_name, $password, $email, $phone, $gender, $yps_avatar_url, $member_since, $subscription_starts,
				$address_street, $address_city, $address_state, $address_pin) = $row;
		// if ($yps_avatar_url != "") {
		// 	// Copy Avatar File to avatar folder
		// 	$avatar_file = $user->user_login . "-" . mt_rand(1111,9999) . ".jpg";
		// 	// Copy to a temporary file
		// 	file_put_contents("res/tmp/" . $avatar_file, file_get_contents($yps_avatar_url));
		// 	if (file_exists("res/tmp/" . $avatar_file)) {
		// 		// Resize and Copy to avatar directory
		// 		list($width, $height) = getimagesize("res/tmp/" . $avatar_file);
		// 		if ($width > 0 && $height > 0) {
		// 			$avatar_height = 120;
		// 			$avatar_width = $width / $height * $avatar_height;
		// 			$resized_image = imagecreatetruecolor($avatar_width, $avatar_height);
		// 			$uploaded_image = imagecreatefromjpeg("res/tmp/" . $avatar_file);
		// 			imagecopyresized($resized_image, $uploaded_image, 0, 0, 0, 0, $avatar_width, $avatar_height, $width, $height);
		// 			imagejpeg($resized_image, "res/avatar/" . $avatar_file, 100);
		// 		}
		// 	}
		// }
		$xfirst_name = mysqli_real_escape_string($DBCON, $first_name);
		$xlast_name = mysqli_real_escape_string($DBCON, $last_name);
		$xpassword = mysqli_real_escape_string($DBCON, $password);
		$xemail = mysqli_real_escape_string($DBCON, $email);
		$xyps_avatar_url = mysqli_real_escape_string($DBCON, $yps_avatar_url);
		$xaddress_street = mysqli_real_escape_string($DBCON, $address_street);
		$xaddress_city = mysqli_real_escape_string($DBCON, $address_city);
		$xaddress_state = mysqli_real_escape_string($DBCON, $address_state);
		$sql  = "INSERT INTO yps_user (yps_login_id, first_name, last_name, password, account_state, email, phone, gender, avatar, member_since, subscription_starts, ";
		$sql .= "            address_street, address_city, address_state, address_pin, country_id) ";
		$sql .= "VALUES ('$yps_login_id', '$xfirst_name', '$xlast_name', '$xpassword', 'active', '$xemail', '$phone', '$gender', " . '"' . $xyps_avatar_url . '", ';
		$sql .= "        '$member_since', '$subscription_starts', '$xaddress_street', '$xaddress_city', '$xaddress_state', '$address_pin', '101' ) ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
}
else {
	$_SESSION['err_msg'] = "Sorry !! Invalid Parameters!";
}

header("Location: " . $_SERVER['HTTP_REFERER']);
printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
?>

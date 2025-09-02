<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);



// Copy only alphabets for saving blobs and images
function collapsedName($name) {
	$fn = "";
	$name = strtolower($name);
	for ($i = 0; $i < strlen($name); ++ $i)
		$fn .= ($name[$i] >= "a" && $name[$i] <= "z") ? $name[$i] : "";
	return $fn;
}

function createThumbnail($pic, $tn, $longsize) {
	// Content type
	// header('Content-type: image/jpeg');

	// Compute new dimensions
	list($width, $height) = getimagesize($pic);
	if ($width > $height) {
		$new_width = $longsize;
		$new_height = round($longsize / $width * $height);
	}
	else {
		$new_height = $longsize;
		$new_width = round($longsize / $height * $width);
	}

	// imagick example
	// $image = new Imagick($filename);

	// If 0 is provided as a width or height parameter,
	// aspect ratio is maintained
	// $image->thumbnailImage(140, 0);
	// echo $image;
	// Output
	// $image->writeImage('blogs/thumbnail.jpg');
	// echo "Thumbnail: <BR> <img src='blogs/thumbnail.jpg'><BR>";
	// echo "Full Size: <BR> <img src='" .$filename . "'><BR>";

	// GD Example - Resample
	$image_p = imagecreatetruecolor($new_width, $new_height);
	$image = imagecreatefromjpeg($pic);
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	// imagecopyresized($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// Sharpen the image based on two things:
	//	(1) the difference between the original size and the final size
	//	(2) the final size

	$sharpness	= sharpen($width, $new_width);

	$sharpenMatrix	= array(
		array(-1, -2, -1),
		array(-2, $sharpness + 12, -2),
		array(-1, -2, -1)
	);
	$divisor		= $sharpness;
	$offset			= 0;
	imageconvolution($image_p, $sharpenMatrix, $divisor, $offset);

	// Output to thumbnail file
	imagejpeg($image_p, $tn, 100);

}


function sharpen($orig, $final) {
	$final	= $final * (750.0 / $orig);
	$a		= 52;
	$b		= -0.27810650887573124;
	$c		= .00047337278106508946;

	$result = $a + $b * $final + $c * $final * $final;

	return max(round($result), 0);
}

// function generateFileName($name, $extn, $file_dir) {
// 	$file_name = $name . $extn;
// 	if (file_exists($file_dir . $file_name)) {
// 		for ($i = 0; $i < 9; ++$i) {
// 			$file_name = $name . $i . $extn;
// 			if (! file_exists($file_dir . $file_name))
// 				return $file_name;
// 		}
// 		// exhausted all options, let original file be overwritten
// 		return $name . $extn;
// 	}
// 	else
// 		return $file_name;
// }

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) && (isset($_REQUEST['new_user']) || isset($_REQUEST['update_user'])) ) {

	$yearmonth = $_SESSION['admin_yearmonth'];

	// Gather Data for update
	$user_id = $_REQUEST['user_id'];
	$type = $_REQUEST["type"];
	$name = $_REQUEST['name'];
	$blob_name = collapsedName(substr($name, 3));	// Skip prefixes
	$profile_file_name = (isset($_REQUEST['new_user']) || $_REQUEST['profile_file'] == "") ? $blob_name . ".htm" : $_REQUEST['profile_file'];
	$user_name = mysqli_real_escape_string($DBCON, strtoupper($name));
	$login = $_REQUEST['login'];
	$password = $_REQUEST['password'];
	$title = mysqli_real_escape_string($DBCON, $_REQUEST['title']);
	$honors = mysqli_real_escape_string($DBCON, $_REQUEST['honors']);
	$profile = $_REQUEST['profile'];		// Not escaped as data will be written to file
	$status = "ACTIVE";

	// Refine avatar_file_name based on whether file has been uploaded
	if (isset($_FILES['picture']) && sizeof($_FILES['picture']) > 0 && trim($_FILES['picture']['tmp_name']) != "")
		$avatar_file_name = (isset($_REQUEST['new_user']) || $_REQUEST['avatar'] == "") ? $blob_name . ".jpg" : $_REQUEST['avatar'];
	else
		$avatar_file_name = "userpic.png";

	// Update Database
	$db_update_success = false;
	if (isset($_REQUEST['new_user'])) {
		$sql = "SELECT * FROM user WHERE login = '$login'";
		$qchk = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$count = mysqli_num_rows($qchk);
		if ($count == 0){
			$sql = "INSERT INTO user(user_name, login, password, type, avatar, title, honors, profile, profile_file, status) ";
			$sql .= "VALUES('$user_name', '$login', '$password', '$type', '$avatar_file_name', '$title', '$honors', '', '$profile_file_name', '$status')";
			mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$_SESSION['success_msg'] = "User Successfully Added :)";
			$db_update_success = true;
		}
		else {
			$_SESSION['err_msg'] = "User already exists !";
			$db_update_success = false;
		}
	}
	else {
		$sql = "UPDATE user ";
		$sql .= "SET user_name = '$name', ";
		$sql .= "    login = '$login', ";
		if ($password != "")
			$sql .= "    password = '$password', ";
		$sql .= "    type = '$type', ";
		if ($profilePic != "")
			$sql .= "    avatar = '$avatar_file_name', ";
		$sql .= "    title = '$title', ";
		$sql .= "    honors = '$honors', ";
		$sql .= "    profile = '', ";
		$sql .= "    profile_file = '$profile_file_name', ";
		$sql .= "    status = '$status' ";
		$sql .= "WHERE user_id = '$user_id' ";
		mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$_SESSION['success_msg'] = "Successfully Updated :)";
		$db_update_success = true;
	}

	if ($db_update_success) {
		// Create Avatar if file has been uploaded
		if (isset($_FILES['picture']) && sizeof($_FILES['picture']) > 0 && trim($_FILES['picture']['tmp_name']) != "") {
			$filename = $_FILES['picture']['name'];
			$tmp_name = $_FILES['picture']['tmp_name'];
			createThumbnail($tmp_name, "../../res/jury/" . $avatar_file_name, 180);	// existing picture will be overwritten
		}

		// Write out Profile Blob
		if ($profile != "") {
			file_put_contents("../../blob/jury/" . $profile_file_name, $profile);
		}

		// header("Location: ../user_admin.php");
		// printf("<script>location.href='../user_admin.php'</script>");
	}

	header("Location: " . $_SERVER['HTTP_REFERER']);
	printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>

<?php
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

function safe_name ($str) {
	$ret_str = "";
	for ($i = 0; $i < strlen($str); ++$i) {
		$char = substr($str, $i, 1);
		if ($char == " ")
			$ret_str .= "_";
		else if ( ($char >= "0" && $char <= "9") || ($char >= "A" && $char <="Z") || ($char >= "a" && $char <= "z") )
			$ret_str .= $char;
	}
	return $ret_str;
}

function die_with_error($errmsg) {
	echo $errmsg;
	die();
}

function zip_error($code) {
	switch ($code) {
		case ZipArchive::ER_EXISTS : return "ZIP File already exists";
		case ZipArchive::ER_INCONS : return "Corrupt ZIP File";
		case ZipArchive::ER_INVAL : return "Invalid Arguments";
		case ZipArchive::ER_MEMORY : return "Insufficent Memory to perform the operation";
		case ZipArchive::ER_NOENT : return "File Not Found";
		case ZipArchive::ER_NOZIP : return "Not a ZIP File";
		case ZipArchive::ER_OPEN : return "Unable to open the file";
		case ZipArchive::ER_READ : return "Unable to read data";
		case ZipArchive::ER_SEEK : return "Unable to seek specific file in the ZIP";
		default : return "No Error";
	}
}

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id'])  ) {
    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	define("SALON_HOME", $_SERVER['DOCUMENT_ROOT']);
	define("AVATAR_FOLDER", SALON_HOME . "/res/avatar");
	define("ZIP_FOLDER", SALON_HOME . "/generated/$yearmonth");

	if (! is_dir(ZIP_FOLDER)) {
		make_path(ZIP_FOLDER);
	}

	$zipfile = ZIP_FOLDER . "/avatars.zip";
	$zip = New ZipArchive;
	if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

    // Select Rows from ENTRY table
    // Get List of Awardees
    $sql  = "SELECT profile.profile_id, profile_name, avatar, awards, hms ";
	$sql .= "  FROM profile, entry ";
    $sql .= " WHERE entry.yearmonth = '$yearmonth' ";
    $sql .= "   AND entry.profile_id = profile.profile_id ";
    $sql .= "   AND (awards + hms) > 0 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    while ($row = mysqli_fetch_array($query)) {
		if ((! empty($row['avatar'])) && $row['avatar'] != 'user.jpg') {
			$upload_avatar = AVATAR_FOLDER . "/" . $row['avatar'];
			$zip_avatar = "avatar/" . sprintf("%04d-%s.jpg", $row['profile_id'], safe_name($row['profile_name']) );
			if ( ! $zip->addFile($upload_avatar, $zip_avatar) )
				die_with_error("Error adding " . $zip_avatar . " to ZIP");
		}
    }

	// Download ZIP File
	set_time_limit(300);				// Restart 300 seconds to finish creating the ZIP
	$zip->close();
	header('Content-Type: application/zip');
	header('Content-disposition: attachment; filename=' . basename($zipfile));
	header('Content-Length: ' . filesize($zipfile));
	readfile($zipfile);
}
else
	die_with_error("Invalid Parameters");
?>

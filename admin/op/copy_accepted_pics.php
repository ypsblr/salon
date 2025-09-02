<?php
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

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

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && isset($_REQUEST['section']) && isset($_REQUEST['start']) && isset($_REQUEST['size']) ) {

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];
	$section = decode_string_array($_REQUEST['section']);
	$start = $_REQUEST['start'];
	$size = $_REQUEST['size'];

	define("SALON_HOME", $_SERVER['DOCUMENT_ROOT']);
	define("PIC_FOLDER", SALON_HOME . "/salons/$yearmonth/upload/$section");
	define("ZIP_FOLDER", SALON_HOME . "/generated/$yearmonth/accepted_pics/$section");

	if (! is_dir(ZIP_FOLDER)) {
		make_path(ZIP_FOLDER);
	}

	$zipfile = ZIP_FOLDER . "/" . sprintf("%04d_accepted_pics.zip", $start + 1);
	$zip = New ZipArchive;
	if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

	// Get a list of pictures accepted and copy them
	$sql  = "SELECT pic.profile_id, profile.profile_name, pic.pic_id, pic.title, pic.picfile, award.award_id, award.award_name ";
	$sql .= "  FROM pic_result, award, pic, profile ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level <= 99 ";
	$sql .= "   AND award.section = '$section' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND pic.section = award.section ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$sql .= " ORDER BY pic.profile_id, pic.pic_id ";
	$sql .= " LIMIT $start, $size ";
	$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	while ($rpic = mysqli_fetch_array($qpic)) {
		$upload_pic = PIC_FOLDER . "/" . $rpic['picfile'];
		$zip_pic = "pics/" . sprintf("%04d-%s-%02d-%s-%04d-%s.jpg", $rpic['profile_id'], safe_name($rpic['profile_name']), $rpic['pic_id'], safe_name($rpic['title']), $rpic['award_id'], safe_name($rpic['award_name']));
		// Add created image to ZIP File
		if ( ! $zip->addFile($upload_pic, $zip_pic) )
			die_with_error("Error adding " . $zip_pic . " to ZIP");
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

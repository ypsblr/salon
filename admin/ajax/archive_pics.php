<?php
// Resize and Archive Pictures to "ar" folder
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("ajax_lib.php");

// Prevent stopping after 30 seconds
set_time_limit(0);

$debug_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";

/*** Resize and Copy Image ***/
function archive_pic($src, $target, $max_width = 1080, $max_height = 720) {

	list($width, $height, $img_type, $attr) = getimagesize($src);

	// Open Source
	$file_ext = image_type_to_extension($img_type);
	switch($file_ext) {
		case 'jpg':
			$src_pic = imagecreatefromjpeg($src);
			break;
		case 'jpeg':
			$src_pic = imagecreatefromjpeg($src);
			break;
		case 'png':
			$src_pic = imagecreatefrompng($src);
			break;
		case 'gif':
			$src_pic = imagecreatefromgif($src);
			break;
		default:
			$src_pic = imagecreatefromjpeg($src);
	}

	if ($width < $max_width && $height < $max_height) {
		// No resizing. Just copy file
		return copy($src, $target);
	}
	else {
		// Create Target File, Resize, Copy & Save
		// Resize Dimensions to fit max_width & $max_height
		// Set width to max_width
		$target_width = $max_width;
		// Resize Height sucht that width == max_width
		$target_height = round($height * $max_width / $width, 0);
		// If resize height is > max_height, reduce height and width
		if ( $target_height > $max_height ) {
			$target_width = round($target_width * $max_height / $target_height, 0);
			$target_height = $max_height;
		}

		$target_pic = imagecreatetruecolor($target_width, $target_height);

		imagecopyresized($target_pic, $src_pic, 0, 0, 0, 0, $target_width, $target_height, $width, $height);

		switch($file_ext) {
			case 'jpg' || 'jpeg':
				return imagejpeg($target_pic, $target, 85);
				break;
			case 'png':
				return imagepng($target_pic, $target);
				break;
			case 'gif':
				return imagegif($target_pic, $target);
				break;
			default:
				return imagejpeg($target_pic, $target, 85);
		}
	}
}
/******  End ****/

function x_error($errmsg, $file, $line) {
	global $lock_file;
	unlink($lock_file);

	$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	echo "|" . $errmsg;
	die();
}

function x_sql_error( $sql, $errmsg, $file, $line) {
	$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
	x_error( "Database operation failed !", $file, $line );	// Return json error
}

file_put_contents($debug_file, "Outside if" . chr(13) . chr(10), FILE_APPEND);

file_put_contents($debug_file, isset($_SESSION['admin_yearmonth']) . chr(13) . chr(10), FILE_APPEND);
file_put_contents($debug_file, isset($_SESSION['admin_id']) . chr(13) . chr(10), FILE_APPEND);
file_put_contents($debug_file, preg_match("/localhost/i", $_SERVER['SERVER_NAME']) . chr(13) . chr(10), FILE_APPEND);
file_put_contents($debug_file, isset($_REQUEST['section']) . chr(13) . chr(10), FILE_APPEND);
file_put_contents($debug_file, isset($_REQUEST['stub']) . chr(13) . chr(10), FILE_APPEND);

// if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) &&
// if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) &&
// 	isset($_REQUEST['section']) && isset($_REQUEST['stub']) ) {

if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && isset($_REQUEST['section']) && isset($_REQUEST['stub']) ) {

    file_put_contents($debug_file, "Inside if" . chr(13) . chr(10), FILE_APPEND);

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];
	$section = $_REQUEST["section"];
	$stub = $_REQUEST["stub"];

	$src_path = "../../salons/$yearmonth/upload/$section";
	$target_path = $src_path . "/ar";
	if (! file_exists($target_path))
		mkdir($target_path);

	// Create a Lockfile
	$lock_file = $stub . ".lck";
	$lckf = fopen($lock_file, "w");
	fwrite($lckf, "Archiving pictures under section $section");
	fclose($lckf);

	$output = fopen('copy_errors.txt', 'w');
	$copy_errors = false;

	// Get a List of Pictures
	$sql = "SELECT * FROM pic WHERE yearmonth = '$yearmonth' AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql) or x_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	for ($files_archived = 0; $row = mysqli_fetch_array($query); ++ $files_archived) {

		$src_file = $src_path . "/" . $row["picfile"];
		$target_file = $target_path . "/" . $row["picfile"];

        file_put_contents($debug_file, file_exists($target_file) . " ** " . $target_file . chr(13) . chr(10), FILE_APPEND);

        if (file_exists($target_file) == 1)
        {
            echo ".";
        }
        else 
        {
    		if ( ! archive_pic($src_file, $target_file) ) {
    			fprintf($output, "Error copying %s\n", $row['picfile']);
    			$copy_errors = true;
    			echo "E";
    		}
    		else
    			echo ".";
    
    		if ( $files_archived != 0 && ($files_archived % 100) == 0 ) {
    			ob_flush();
    			flush();
    		}
        }
	}

	fclose($output);
	unlink($lock_file);

	if ($copy_errors)
		echo "|Some files not archived";

}
else
	x_error( "Invalid Parameters !", __FILE__, __LINE__, true);
?>

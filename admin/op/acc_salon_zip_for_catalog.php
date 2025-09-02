<?php
//
// Generate Acceptance Data for Catalog
//
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

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

function die_with_error($errmsg) {
//	$_SESSION['err_msg'] = $errmsg;
//	header("Location: ".$_SERVER['HTTP_REFERER']);
//	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	echo $errmsg;
	die();
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

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) ) {
	// Generate Acceptance Data for Catalog Design
	$yearmonth = $_SESSION['admin_yearmonth'];

	define("SALON_HOME", $_SERVER['DOCUMENT_ROOT']);
	define("GENFILES_FOLDER", SALON_HOME . "/generated/" . $yearmonth);
	define("CATALOG_DATA_FOLDER", GENFILES_FOLDER . "/catalog_data");
	define("TARGET_FOLDER", CATALOG_DATA_FOLDER . "/acceptance_thumbnails");
	define("TARGET_PIC_FOLDER", TARGET_FOLDER . "/pic");

	if (! is_dir(GENFILES_FOLDER)) {
		mkdir(GENFILES_FOLDER);
	}
	if (! is_dir(CATALOG_DATA_FOLDER)) {
		mkdir(CATALOG_DATA_FOLDER);
	}
	if (! is_dir(TARGET_FOLDER)) {
		mkdir(TARGET_FOLDER);
	}

	// Create Temp Pic folder
	if (is_dir(TARGET_PIC_FOLDER))
		array_map("unlink", glob(TARGET_PIC_FOLDER . "/*"));	// Delete files under pic folder
	else
		mkdir(TARGET_PIC_FOLDER);		// Create pic folder

	//$_SESSION['success_msg'] = "";
	$success_msg = "";

	// Get Contest Name
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		die_with_error("Invalid Contest ID " . $yearmonth);
	$contest = mysqli_fetch_array($query);
	$contest_name = $contest['contest_name'];
	$contest_archived = ($contest['archived'] == '1');
	$is_international = ($contest['is_international'] == '1');

	// Get Data for the entire Salon
	// Get 1 picture for each author, with preference for picture with just acceptance as other pictures would already have been printed
	$sql  = "SELECT profile.profile_id, profile.profile_name, profile.city, pic.title, pic.picfile, pic.total_rating, pic.section,  ";
	$sql .= "       country.country_name, entry.awards, entry.hms, entry.acceptances ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, ar_entry entry, profile, country ";
	else
		$sql .= "  FROM pic_result, award, pic, entry, profile, country ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND entry.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND entry.profile_id = pic_result.profile_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= " ORDER BY profile.profile_name ASC, award.level DESC, pic.total_rating DESC ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	if (mysqli_num_rows($query) > 0) {
		// Create ZIP File for hoding data
		$zipfile = TARGET_FOLDER . "/ALL_acceptances.zip";
		$zip = New ZipArchive;
		if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
			die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

		// Open a CSV file for writing Data
		$data_file = "ALL_acceptance_list_for_catalog.csv";
		$csv_file = fopen(TARGET_FOLDER . "/" . $data_file, "w");

		// Write Column titles
		fputcsv($csv_file, array("Picture Title", "Author Name", ($is_international ? "Country" : "City"), "Picture File", "Wins"));

		$profile_id = 0;
		while ($res = mysqli_fetch_array($query)) {
			set_time_limit(30);		// 10 seconds for each picture
			if ($res['profile_id'] != $profile_id) {
				$profile_id = $res['profile_id'];
				$profile_name = ucwords(strtolower(utf8_decode($res['profile_name'])));
				$pic_title = ucwords(strtolower(utf8_decode($res['title'])));
				$section = $res['section'];
				$country = $res['country_name'];
				$city = $res['city'];
				$picfile = $res['picfile'];
				$wins = ($res['awards'] + $res['hms'] + $res['acceptances']) . " Acceptances";
				if ( ($res['awards'] + $res['hms']) > 0 )
					$wins .= " (incl " . ($res['awards'] + $res['hms']) . " awards )";
				$error = "";

				// Copy file
				$source_file = SALON_HOME . "/salons/$yearmonth/upload/$section/$picfile";
				$target_file = TARGET_PIC_FOLDER . "/" . $picfile;
				$zip_pic_file = "pic/" . sprintf("%04d-%s.jpg", $profile_id, safe_name($profile_name));

				// ZIP and Archive
				archive_pic($source_file, $target_file, 720, 480);
				if ( ! $zip->addFile($target_file, $zip_pic_file))
					die_with_error("Error adding " . $zip_pic_file . " to ZIP");

				// write csv data
				fputcsv($csv_file, array($pic_title, $profile_name, ($is_international ? $country : $city), basename($zip_pic_file), $wins));
			}
		}
		// Add the CSV File to the ZIP
		fclose($csv_file);

		// Add CSV File to ZIP
		if ( ! $zip->addFile(TARGET_FOLDER . "/" . $data_file, $data_file))
			die_with_error("Error adding " . $data_file . " to ZIP");

		$zip->close();

		// Send headers to download
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename=' . basename($zipfile));
		header('Content-Length: ' . filesize($zipfile));
		readfile($zipfile);
	}
}
else
	die_with_error("Invalid Parameters");
?>

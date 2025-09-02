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

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) ) {
	// Generate Acceptance Data for Catalog Design
	$yearmonth = $_SESSION['admin_yearmonth'];

	define("SALON_HOME", $_SERVER['DOCUMENT_ROOT']);
	define("AVATAR_FOLDER", SALON_HOME . "/res/avatar");
	define("GENFILES_FOLDER", SALON_HOME . "/generated/" . $yearmonth);
	define("LOG_FILE", GENFILES_FOLDER . "/logs/copy_errors.txt");
	define("CATALOG_DATA_FOLDER", GENFILES_FOLDER . "/catalog_data");
	define("TARGET_FOLDER", CATALOG_DATA_FOLDER . "/acceptance_thumbnails");

	if (! is_dir(GENFILES_FOLDER)) {
		mkdir(GENFILES_FOLDER);
		mkdir(GENFILES_FOLDER . "/logs");
	}
	if (! is_dir(CATALOG_DATA_FOLDER)) {
		mkdir(CATALOG_DATA_FOLDER);
		mkdir(TARGET_FOLDER);
	}

	if (! is_dir(TARGET_FOLDER))
		mkdir(TARGET_FOLDER);

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

	// $catalog_data_folder = "../../salons/" . $yearmonth . "/catalog_data";
	// Create Catalog Folder if it does not exist
	// if (! file_exists($catalog_data_folder))
	// 	mkdir($catalog_data_folder);

	// Get List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	while ($rsec = mysqli_fetch_array($qsec)) {

		set_time_limit(300);	// 5 minutes for each section

		$section_name = $rsec['section'];

		// Get Data for the Section
		$sql  = "SELECT profile.profile_id, profile.profile_name, profile.city, ";
		$sql .= "       pic.title, pic.picfile, pic.total_rating, country.country_name ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile, country ";
		else
			$sql .= "  FROM pic_result, award, pic, profile, country ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section = '$section_name' ";
		$sql .= "   AND award.level = '99' ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND profile.profile_id = pic.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " ORDER BY profile.profile_name ASC, profile.profile_id ASC, pic.total_rating DESC ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			// Check and create Target folder
			$target_folder = TARGET_FOLDER . "/" . $rsec['stub'];
			if (! is_dir($target_folder))
				mkdir($target_folder);
			// Create a pic folder
			$pic_folder = $target_folder . "/pic";
			if (! is_dir($pic_folder))
				mkdir($pic_folder);

			// Open a CSV file for writing Data
			$csv_file = fopen($target_folder . "/" . $rsec['stub'] . "_acceptance_list_for_catalog.csv", "w");

			// Write Column titles
			fputcsv($csv_file, array("Picture Title", "Author Name", ($is_international ? "Country" : "City"), "Picture File", "Error"));

			$profile_id = 0;
			while ($res = mysqli_fetch_array($query)) {
				if ($res['profile_id'] != $profile_id) {
					$profile_id = $res['profile_id'];
					$profile_name = ucwords(strtolower(utf8_decode($res['profile_name'])));
					$pic_title = ucwords(strtolower(utf8_decode($res['title'])));
					$city = $res['city'];
					$country = $res['country_name'];
					$picfile = $res['picfile'];
					$error = "";

					// Copy file after resizing to 1080 x 720
					$source_file = $_SERVER['DOCUMENT_ROOT'] . "/salons/$yearmonth/upload/$section_name/$picfile";
					$target_file = $pic_folder . "/" . sprintf("%04d-%s.jpg", $profile_id, safe_name($profile_name));
					if (! file_exists($target_file))
						archive_pic($source_file, $target_file);

					// if (! copy($source_file, $target_file))
					// 	$error = "File Copy Failed";

					// write csv data
					fputcsv($csv_file, array($pic_title, $profile_name, ($is_international ? $country : $city), basename($target_file),  $error));
				}
			}
		}
//		$_SESSION['success_msg'] .= "Generated CSV file under folder " . basename($target_folder) . "<br>";
		$success_msg .= "Generated CSV file for " . $rsec['stub'] . " under folder " . basename($target_folder) . "\n";
	}
//	header("Location: ".$_SERVER['HTTP_REFERER']);
//	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	echo $success_msg;
}
else
	die_with_error("Invalid Parameters");
?>

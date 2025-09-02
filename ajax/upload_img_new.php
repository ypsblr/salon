<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

function make_path($path) {
	if (! is_dir(dirname($path)))
		make_path(dirname($path));
	if (! is_dir($path))
		mkdir($path);
}

// Filter non-text and non-numerals
function plain_text($str) {
	$ret = "";
	for ($i = 0; $i < strlen($str); ++ $i) {
		$letter = substr($str, $i, 1);
		if (($letter >= '0' && $letter <= '9') || ($letter >= 'a' && $letter <= 'z') || ($letter >= 'A' && $letter <= 'Z'))
			$ret .= $letter;
	}
	return $ret;
}

// Add single quotes at the start and end of a string
function quote_string($str) {
	return "'" . $str . "'";
}

/*** Create Thumbnail Image ***/
function makeThumbnail($fileName = '', $upload_image, $thumb_path = '', $thumb_width = '', $thumb_height = '') {
	// $thumb_path	= $thumb_path . '/tn';
	// create thumbnail directory if not exists
	if(! is_dir($thumb_path))
		make_path($thumb_path);

	$thumbnail = $thumb_path . "/" . $fileName;

	// Check if thumbnail has been uploaded
	if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
		$tmp_name = $_FILES['thumbnail']['tmp_name'];
		if( ! move_uploaded_file($tmp_name, $thumbnail))
			handle_error('Picture upload failed. Please check the file for corruption or virus infection !', __FILE__, __LINE__);
	}
	else {
		// Create a resized thumbnail
		list($width, $height, $img_type, $attr) = getimagesize($upload_image);
		$file_ext = image_type_to_extension($img_type);

		$thumb_create = imagecreatetruecolor($thumb_width,$thumb_height);

		switch($file_ext){
			case 'jpg':
				$source = imagecreatefromjpeg($upload_image);
				break;
			case 'jpeg':
				$source = imagecreatefromjpeg($upload_image);
				break;
			case 'png':
				$source = imagecreatefrompng($upload_image);
				break;
			case 'gif':
				$source = imagecreatefromgif($upload_image);
				break;
			default:
				$source = imagecreatefromjpeg($upload_image);
		}

		imagecopyresized($thumb_create,$source,0,0,0,0,$thumb_width,$thumb_height,$width,$height);

		switch($file_ext){
			case 'jpg' || 'jpeg':
				imagejpeg($thumb_create,$thumbnail,100);
				break;
			case 'png':
				imagepng($thumb_create,$thumbnail,100);
				break;
			case 'gif':
				imagegif($thumb_create,$thumbnail,100);
				break;
			default:
				imagejpeg($thumb_create,$thumbnail,100);
		}
	}
}
/******  End ****/

// debug_dump("REQUEST", $_REQUEST, __FILE__, __LINE__);
// debug_dump("FILES", $_FILES, __FILE__, __LINE__);

if( isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "" &&
    isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) &&
	isset($_REQUEST['upload_section']) && trim($_REQUEST['img_title']) != '' ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];

	// Gather values passed
	$img_title = mysqli_real_escape_string($DBCON, trim($_REQUEST['img_title']));
	$upload_mode = $_REQUEST['upload_mode'];
	$pic_id = $_REQUEST['upload_pic_id'];
	$upload_section = $_REQUEST['upload_section'];
	$prev_picfile = $_REQUEST['upload_picfile'];

	$width = $_REQUEST['width'];
	$height = $_REQUEST['height'];
	$file_size = $_REQUEST['file_size'];
	$exif = mysqli_real_escape_string($DBCON, $_REQUEST['exif']);

	$upload_code = '';

	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' AND section = '$upload_section' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rsec = mysqli_fetch_array($query);
	$section_stub = $rsec['stub'];
	$max_pics_per_entry = $rsec['max_pics_per_entry'];
	$submission_last_date = $rsec['submission_last_date'];

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$sql = "SELECT * FROM entry WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$entry = mysqli_fetch_array($query);
	$max_sections = ($entry['digital_sections'] + $entry['print_sections']);

	// Check if the Upload is open
	$submissionTimezone = $contest['submission_timezone'];
	$date_in_submission_timezone = date_tz("now", $submissionTimezone);
	if ($date_in_submission_timezone > $submission_last_date)
		handle_error("Unable to upload! Picture submission under this section is closed.", __FILE__, __LINE__);

	$upload_dir = "../salons/$yearmonth/upload/" . $upload_section;

	if (! is_dir($upload_dir))
		make_path($upload_dir);

	// Perform Validations
	// File Size check and upload
	$picfile = $prev_picfile;
	$file_uploaded = false;
	debug_dump("FILES", $_FILES, __FILE__, __LINE__);
	if (isset($_FILES['submittedfile']) && $_FILES['submittedfile']['error'] == 0) {
		$tmp_name = $_FILES['submittedfile']['tmp_name'];
		$filesize = $_FILES['submittedfile']['size'];

		list($img_width, $img_height, $img_type, $attr) = getimagesize($tmp_name);
		if (image_type_to_extension($img_type) != '.jpg' && image_type_to_extension($img_type) != '.jpeg')
			handle_error('Image type not supported. Can only upload JPEG files !', __FILE__, __LINE__);

		if ($img_width > $contest['max_width'] || $img_height > $contest['max_height'])
			handle_error('Picture dimensions exceeds the permitted size (Width ' . $contest['max_width'] . ' x Height ' . $contest['max_height'] . ' pixels) !', __FILE__, __LINE__);

		if ($filesize > $contest['max_file_size_in_mb'] * 1024 * 1024)
			handle_error('File size exceeds ' . $contest['max_file_size_in_mb'] . ' MB Limit !', __FILE__, __LINE__);

		// Upload File
		//create upload directory if not exist
		$random = mt_rand(1111111, 9999999);
		$picfile = $profile_id . '_' . $random . '_' . date('dmyhis') . '.jpg';
		$fullPicPath = $upload_dir . "/" . $picfile;
		if( ! move_uploaded_file($tmp_name, $fullPicPath))
			handle_error('Picture upload failed. Please check the file for corruption or virus infection !', __FILE__, __LINE__);

		// Create Thumbnail
		makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tn", 480, $img_height * 480 / $img_width);

		// create large Thumbnail as well
		// makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tnl", 960, $img_height * 960 / $img_width);

		$file_uploaded = true;
		$submittedName = mysqli_real_escape_string($DBCON, trim($_FILES['submittedfile']['name']));
	}
	else if (isset($_REQUEST['dropped-file-name']) && $_REQUEST['dropped-file-name'] != "") {
		// Some file has been dropped and uploaded to dropped folder
		if ($_REQUEST['dropped-file-tmp-name'] == "" || $_REQUEST['dropped-file-upload-error'] != "")
			handle_error("Dropped File was not uploaded (" . $_REQUEST['dropped-file-upload-error'] . ")", __FILE__, __LINE__);

		$dropped_folder = "../salons/$yearmonth/upload/dropped";
		$dropped_file = $dropped_folder . "/" . $_REQUEST['dropped-file-tmp-name'];

		list($img_width, $img_height, $img_type, $attr) = getimagesize($dropped_file);
		if (image_type_to_extension($img_type) != '.jpg' && image_type_to_extension($img_type) != '.jpeg')
			handle_error('Dropped Image type not supported. Can only upload JPEG files !', __FILE__, __LINE__);

		if ($img_width > $contest['max_width'] || $img_height > $contest['max_height'])
			handle_error('Dropped Picture dimensions exceeds the permitted size (Width ' . $contest['max_width'] . ' x Height ' . $contest['max_height'] . ' pixels) !', __FILE__, __LINE__);

		$filesize = filesize($dropped_file);
		if ($filesize > $contest['max_file_size_in_mb'] * 1024 * 1024)
			handle_error('Dropped File size exceeds ' . $contest['max_file_size_in_mb'] . ' MB Limit !', __FILE__, __LINE__);

		// Upload File
		//create upload directory if not exist
		$random = mt_rand(1111111, 9999999);
		$picfile = $profile_id . '_' . $random . '_' . date('dmyhis') . '.jpg';
		$fullPicPath = $upload_dir . "/" . $picfile;
		if( ! rename($dropped_file, $fullPicPath))
			handle_error('Saving Dropped Picture failed. Please check the file for corruption or virus infection !', __FILE__, __LINE__);

		// Create Thumbnail
		makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tn", 480, $img_height * 480 / $img_width);

		// create large Thumbnail as well
		// makeThumbnail($picfile, $fullPicPath, $upload_dir . "/tnl", 960, $img_height * 960 / $img_width);

		$file_uploaded = true;
		//$submittedName = mysqli_real_escape_string($DBCON, trim($_FILES['submittedfile']['name']));
		$submittedName = mysqli_real_escape_string($DBCON, trim($_REQUEST['dropped-file-name']));
	}
	else if ($upload_mode == "new") {
		handle_error('Upload file not specified (OR) File could not be uploaded to server !', __FILE__, __LINE__);
	}

	if ($pic_id == 0) {
		// INSERT PIC record
		// $eseq = date('si-Hd');
		// Check for the number of pictures already uploaded under this section. This is to avoid more than 4 pictures
		// from getting uploaded by using multiple sections
		$sql  = "SELECT COUNT(*) AS num_uploads FROM pic WHERE pic.yearmonth = '$yearmonth' AND pic.profile_id = '$profile_id' AND pic.section = '$upload_section'";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row1 = mysqli_fetch_array($query, MYSQLI_ASSOC);

		$sql  = "SELECT max_pics_per_entry FROM section WHERE section.yearmonth = '$yearmonth' AND section.section = '$upload_section'";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row2 = mysqli_fetch_array($query, MYSQLI_ASSOC);
		
		if ($row1['num_uploads'] >= $row2['max_pics_per_entry'])
			handle_error("You have already uploaded " . $row['max_pics_per_entry'] . " pictures under $upload_section section !", __FILE__, __LINE__);

		// Start a transaction to be able to check if number of sections uploaded is within what is paid for
		$sql = "START TRANSACTION ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Proceed with upload
		$eseq = sprintf("%02X%02X-%02X%02X", rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255));
		$sql  = "INSERT INTO pic (yearmonth, profile_id, pic_id, title, section, location, submittedfile, picfile, ";
		$sql .= "                 width, height, file_size, exif, upload_code, eseq) ";
		$sql .= "SELECT '$yearmonth', '$profile_id', IFNULL(MAX(pic_id), 0)+1, '$img_title', '$upload_section', '', '$submittedName', '$picfile', ";
		$sql .= "       '$width', '$height', '$file_size', '$exif', '', '$eseq' ";
		$sql .= "  FROM pic ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Check if number of sections uploaded is within what is paid for
		$sql  = "SELECT COUNT(DISTINCT section) AS num_sections_uploaded FROM pic ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
		if ($row['num_sections_uploaded'] > $max_sections)
			handle_error("You are trying to upload under more than $max_sections sections you have chosen to participate !", __FILE__, __LINE__);

		// Commit upload
		$sql = "COMMIT ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Fetch inserted record
		$sql = "SELECT * FROM pic WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND eseq = '$eseq' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$pic = mysqli_fetch_array($query, MYSQLI_ASSOC);
	}
	else {
		// Edit Picture - Update existing record - Empty Notifications if a picture has been uploaded
		$sql  = "UPDATE pic ";
		$sql .= "   SET title = '$img_title' ";
		if ($file_uploaded) {
			$sql .= "     , submittedfile = '$submittedName' ";
			$sql .= "     , picfile = '$picfile' ";
			$sql .= "     , width = '$width' ";
			$sql .= "     , height = '$height' ";
			$sql .= "     , file_size = '$file_size' ";
			$sql .= "     , exif = '$exif' ";
			$sql .= "     , notifications = '' ";		// Empty notifications if a picture was uploaded
			$sql .= "     , reviewed = '0' ";
			$sql .= "     , no_accept = '0' ";
			$sql .= "     , reviewer_id = '0' ";
		}
		$sql .= "WHERE yearmonth = '$yearmonth' ";
		$sql .= "  AND profile_id = '$profile_id' ";
		$sql .= "  AND pic_id = '$pic_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		// Fetch inserted record
		$sql = "SELECT * FROM pic WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$pic = mysqli_fetch_array($query, MYSQLI_ASSOC);
	}

	// Update the Number of Uploads in entry table
	$sql  = "UPDATE entry ";
	$sql .= "   SET uploads = (SELECT COUNT(*) FROM pic ";
	$sql .= "					WHERE pic.yearmonth = entry.yearmonth ";
	$sql .= "					  AND pic.profile_id = entry.profile_id) ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id'";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Gather Filesize & Dimension Info
	$pic['file_size'] = filesize("../salons/$yearmonth/upload/$upload_section/$picfile");
	list($pic['width'], $pic['height']) = getimagesize("../salons/$yearmonth/upload/$upload_section/$picfile");

	// Check for potential duplicate issues
	// Check if the same image has been submitted multiple times
	$rejection_text = "";
	$pic_id = $pic['pic_id'];
	$submitted_file = basename($pic['submittedfile']);
	$pic_title = $pic['title'];
	$sql  = "SELECT pic_id, title, submittedfile, section, contest_name FROM pic, contest ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id != '$pic_id' ";
	$sql .= "   AND contest.yearmonth = pic.yearmonth ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$prev_file = basename($row['submittedfile']);
		// Verify submitted file
		if (preg_match("/" . preg_quote($submitted_file) . "/i", $prev_file) == 1 || preg_match("/" . preg_quote($prev_file) . "/i", $submitted_file) == 1)
			$rejection_text .= "<li>" . $submitted_file . " appears already submitted under " . $row['section'] . " in " . $row['contest_name'] . ".</li>";

		// Verify matching title
		if (plain_text($pic_title) == plain_text($row['title']))
			$rejection_text .= "<li>A picture with similar title was also submitted under " . $row['section'] . " in " . $row['contest_name'] . ".</li>";
	}

	// Check if the picture is among those accepted in the past
	$sql  = "SELECT pic.pic_id, title, submittedfile, section, contest_name ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, ar_pic pic, contest ";
	else
		$sql .= "  FROM pic_result, pic, contest ";
	$sql .= " WHERE pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth AND pic.profile_id = pic_result.profile_id AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND contest.yearmonth = pic_result.yearmonth ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$prev_file = basename($row['submittedfile']);
		// Verify submitted file
		if (preg_match("/" . plain_text($submitted_file) . "/i", plain_text($prev_file)) == 1 || preg_match("/" . plain_text($prev_file) . "/i", plain_text($submitted_file)) == 1)
			$rejection_text .= "<li>" . $submitted_file . " appears already accepted under " . $row['section'] . " in " . $row['contest_name'] . ".</li>";

		// Verify matching title
		if (plain_text($pic_title) == plain_text($row['title']))
			$rejection_text .= "<li>A picture with similar title was accepted under " . $row['section'] . " in " . $row['contest_name'] . ".</li>";
	}


	$pic['rejection_text'] = ($rejection_text == "") ? "" : "<ul>" . $rejection_text . "</ul>";

	$resArray = array();
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['pic'] = $pic;
	echo json_encode($resArray);
}
else {
	handle_error("Invalid Upload Request", __FILE__, __LINE__);
}
?>

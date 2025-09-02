<?php
/*
** This program is used to generate a FB Share Image for the participant
*/
session_start();
include("../../inc/connect.php");
include("../../inc/lib.php");

include_once('../../PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;

function die_with_error($errmsg) {
	$result = [];
	$result['status'] = "ERR";
	$result['errmsg'] = $errmsg;
	echo json_encode($result);
	die();
}

function ss_handle_error($errmsg, $file, $line) {
	$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": Operation failed in line $line of '$file'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Error Message: '$errmsg'" . chr(13) . chr(10), FILE_APPEND);
    if (!empty($_REQUEST))
        file_put_contents($log_file, print_r($_REQUEST, true) . chr(13) . chr(10), FILE_APPEND);
	if (!empty($_SESSION))
        file_put_contents($log_file, print_r($_SESSION, true) . chr(13) . chr(10), FILE_APPEND);

	$_SESSION['err_msg'] = $errmsg;
	die_with_error($errmsg);
}

function ss_sql_error($sql, $errmsg, $file, $line) {
	$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL Operation failed in line $line of '$file'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Error Message: '$errmsg'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);

	$_SESSION['err_msg'] = $errmsg;
	die_with_error($errmsg);
}

//
// Create and Return a Text Layer
//
function createTextLayer($text, $box, $size = 16, $color = "202020", $font = "PetitaMedium.ttf", $position = "MM" ) {
	$fontPath = "../../PHPImageWorkshop/font/" . $font;
	$fontColor = $color;
	$textRotation = 0;
	$fontSize = $size;
	$text_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);
	if ($text_layer->getWidth() > $box['width'])
		$text_layer->resizeByLargestSideInPixel($box['width'], true);

	return $text_layer;
}

//
// Create and return an image Layer for a specific width
//
function createImageLayerByWidth($file, $width, $border = 0, $border_color = "A0A0A0") {
	// Get Image Parameters
	if (file_exists($file)) {
		list($img_width, $img_height, $img_type) = getimagesize($file);
		if ($img_type != "2" && $img_type != "3")  // 2 - JPG,  3 - PNG
			return ImageWorkshop::initVirginLayer($width, 10);	// return empty layer
	}

	// Determine the height of the image by the width and create an empty layer
	$height = $width / $img_width * $img_height;
	$layer = ImageWorkshop::initVirginLayer($width, $height, ($border > 0 ? $border_color : null));

	// Create an Image Layer
	$img_layer = ImageWorkshop::initFromPath($file);

	// Resize Image Layer to fit the dimensions of the box
	if ($img_width > ($width - ($border * 2)) || $img_height > ($height - ($border * 2)) ) {
		$img_layer->resizeInPixel($width - ($border * 2), $height - ($border * 2), true, 0, 0, 'MM');
	}

	// Place the Image Layer on Empty Layer
	$layer->addLayerOnTop($img_layer, 0, 0, "MM");
	$layer->mergeAll();

	return $layer;
}

//
// Create and return an image Layer
//
function createImageLayer($file, $box, $border = 0, $border_color = "A0A0A0") {
	// Create an empty layer
	$layer = ImageWorkshop::initVirginLayer($box['width'], $box['height'], ($border > 0 ? $border_color : null));	// return empty layer

	// Get Image Parameters
	if (file_exists($file)) {
		list($img_width, $img_height, $img_type) = getimagesize($file);
		if ($img_type != "2" && $img_type != "3")  // 2 - JPG,  3 - PNG
			return $layer;	// return empty layer
	}

	// Create an Image Layer
	$img_layer = ImageWorkshop::initFromPath($file);

	// Resize Image Layer to fit the dimensions of the box
	if ($img_width > ($box['width'] - ($border * 2)) || $img_height > ($box['height'] - ($border * 2)) ) {
		$img_layer->resizeInPixel($box['width'] - ($border * 2), $box['height'] - ($border * 2), true, 0, 0, 'MM');
	}

	// Place the Image Layer on Empty Layer
	$layer->addLayerOnTop($img_layer, 0, 0, "MM");
	$layer->mergeAll();

	return $layer;
}

/*
 * Settings and Dimensions
 */
$slide_image = "img/share_poster.jpg";
$slide_width = 1920;
$slide_height = 1080;
$save_folder = "upload/share";
if (! is_dir($save_folder))
	mkdir($save_folder);

// Layer Dimensions
// Areas
$thumbnail_area = array("x" => 110, "y" => 50, "width" => 260, "height" => 980);
$profile_area = array("x" => 1300, "y" => 320, "width" => 500, "height" => 500);
$award_area = array("x" => 450, "y" => 250, "width" => 700, "height" => 700);

// Components
$avatar = array("width" => 150, "height" => 150);
$thumbnail = array("width" => 200, "height" => 200);
$name = array("width" => 500, "height" => 0);
$honors = array("width" => 500, "height" => 0);
$award = array("width" => 600, "height" => 0);


//$_SESSION['USER_ID'] = 672; // Asif for testing
//$_REQUEST['yearmonth'] = 202008;
//$_REQUEST['profile_id'] = 672;

if ( ! empty($_SESSION['USER_ID']) && (! empty($_REQUEST['yearmonth'])) && (! empty($_REQUEST['profile_id'])) ) {

	$profile_id = $_REQUEST['profile_id'];
    $yearmonth = $_REQUEST['yearmonth'];

	unset($_SESSION['err_msg']);

	// Check if already running
	if (! file_exists($slide_image)) {
		ss_handle_error("Unable to generate image for sharing. Design does not exist !", __FILE__, __LINE__);
	}

	// Remove execution time limit for PHP
	// set_time_limit(0);

	// Get contest details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or ss_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Load profile details
	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or ss_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		ss_handle_error("Unable to generate image for sharing. Incorrect user reference !", __FILE__, __LINE__);
	$profile = mysqli_fetch_array($query);

	// Check for Entry Awards
	$sql  = "SELECT award_name FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND entry_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$query = mysqli_query($DBCON, $sql) or ss_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$has_entry_awards = false;
	$entry_award_list = [];
	if (mysqli_num_rows($query) > 0) {
		$has_entry_awards = true;
		while ($row = mysqli_fetch_array($query))
			$entry_award_list[] = $row['award_name'];
	}

	// Assemble Pictures and Awards
	$sql  = "SELECT pic.pic_id, pic.section, title, picfile, ";
	$sql .= "       IFNULL(award.level, 999) AS level, IFNULL(award.sequence, 99) AS sequence, ";
	$sql .= "       IFNULL(award.section, '') AS award_section, IFNULL(award_name, '') AS award_name, ";
	$sql .= "       IFNULL(has_medal, 0) AS has_medal, IFNULL(has_pin, 0) AS has_pin, IFNULL(has_ribbon, 0) AS has_ribbon, ";
	$sql .= "       IFNULL(has_memento, 0) AS has_memento, IFNULL(has_gift, 0) AS has_gift, ";
	$sql .= "       SUM(rating) AS total_rating ";
	if ($contest_archived)
		$sql .= "  FROM ar_rating rating, ar_pic pic LEFT JOIN (ar_pic_result pic_result INNER JOIN award) ";
	else
		$sql .= "  FROM rating, pic LEFT JOIN (pic_result INNER JOIN award) ";
	$sql .= "         ON pic_result.yearmonth = pic.yearmonth ";
	$sql .= "        AND pic_result.profile_id = pic.profile_id ";
	$sql .= "        AND pic_result.pic_id = pic.pic_id ";
	$sql .= "        AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "        AND award.award_id = pic_result.award_id ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND rating.yearmonth = pic.yearmonth ";
	$sql .= "   AND rating.profile_id = pic.profile_id ";
	$sql .= "   AND rating.pic_id = pic.pic_id ";
	$sql .= " GROUP BY pic_id, section, title, picfile, level, sequence, award_section, award_name, has_medal, has_pin, has_ribbon, has_memento, has_gift ";
	$sql .= " ORDER BY level, sequence, total_rating ";
	$query = mysqli_query($DBCON, $sql) or ss_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		ss_handle_error("Unable to generate image for sharing. No pictures found !", __FILE__, __LINE__);

	$has_special_award = false;
	$special_award_list = [];
	$pic_list = [];
	$awards = 0;
	$acceptances = 0;
	$not_so_lucky = 0;
	for ($idx = 0; $row = mysqli_fetch_array($query, MYSQLI_ASSOC); ++ $idx) {
		$pic_list[$idx] = $row;
		$section = $row['section'];
		$picfile = $row['picfile'];
		// Get Image Sizes
		list($img_width, $img_height, $img_type, $attr) = getimagesize("upload/$section" . ($contest_archived ? "/ar/" : "/tn") . $picfile);
		$pic_list[$idx]['img_height'] = $img_height;
		$pic_list[$idx]['img_width'] = $img_width;
		// Gather other details
		if ($row['award_section'] == 'CONTEST') {
			$has_special_award = true;
			$special_award_list[] = $idx;
		}
		if ($row['level'] < 99)
			++ $awards;
		else if ($row['level'] == 99)
			++ $acceptances;
		else
			++ $not_so_lucky;
	}

	//
	// Create a Page from background image
	//
	// Create a Blank sheet with Section Name
	//
	$poster = ImageWorkshop::initFromPath($slide_image);

	//
	// FILL PROFILE AREA
	//
	// Add Thumbnail if available
	$profile_area_height = 0;
	$avatar_file = "../../res/avatar/" . $profile['avatar'];
	if ($profile['avatar'] != "" && $profile['avatar'] != "user.jpg" && file_exists($avatar_file)) {
		$avatar_layer = createImageLayer($avatar_file, $avatar, 1);
		$profile_area_height += $avatar_layer->getHeight();
	}

	// Add Name & Honors Text Layer right in the center
	$name_layer = createTextLayer($profile['profile_name'], $name, 36, "FFFFFF", "PetitaBold.ttf", "MM");
	$profile_area_height += ($name_layer->getHeight() + 40);

	if ($profile['honors'] != "") {
		$honors_layer = createTextLayer($profile['honors'], $name, 16, "FFFFFF", "PetitaMedium.ttf", "MM");
		$profile_area_height += ($honors_layer->getHeight() + 20);
	}

	$profile_area_layer = ImageWorkshop::initVirginLayer($profile_area['width'], $profile_area['height']);

	$y = ($profile_area['height'] - $profile_area_height) / 2;
	if (isset($avatar_layer)) {
		$profile_area_layer->addLayerOnTop($avatar_layer, 0, $y, "MT");
		$y += ($avatar_layer->getHeight() + 40);
	}

	$profile_area_layer->addLayerOnTop($name_layer, 0, $y, "MT");
	$y += ($name_layer->getHeight() + 20);

	if (isset($honors_layer))
		$profile_area_layer->addLayerOnTop($honors_layer, 0, $y, "MT");

	$profile_area_layer->mergeAll();

	// Add Name Group to the image
	$poster->addLayerOnTop($profile_area_layer, $profile_area['x'], $profile_area['y'], "LT");

	//
	// Add Thumbnails that can be accommodated
	//

	// Render thumbnails of top images
	$thumbnail_area_height = 0;
	$thumbanil_layers = [];
	for($idx = 0; $idx < sizeof($pic_list) && $pic_list[$idx]['award_name'] != ''; ++ $idx) {
		// Get Data
		$pic = $pic_list[$idx];
		$img_file = "upload/" . $pic['section'] . ($contest_archived ? "/ar/" : "/tn/") . $pic['picfile'];
		$award_name = $pic['award_name'];
		// Create pic and Award layers
		$pic_layer = createImageLayerByWidth($img_file, $thumbnail['width'], 2);  	// with 1 pixel border
		$award_name_layer = createTextLayer($award_name, $thumbnail, 12, "FFFFFF", "PetitaBold.ttf", "MM");

		if ( ($thumbnail_area_height + $pic_layer->getHeight() + 10 + $award_name_layer->getHeight()) > $thumbnail_area['height'] )
			break; // Cannot accommodate this picture - No space

		$pic_group_layer_height = $pic_layer->getHeight() + 10 + $award_name_layer->getHeight() + 20;
		$pic_group_layer = ImageWorkshop::initVirginLayer($thumbnail['width'], $pic_group_layer_height);
		$y = 0;
		$pic_group_layer->addLayerOnTop($pic_layer, 0, $y, 'LT');
		$y += $pic_layer->getHeight() + 10;
		$pic_group_layer->addLayerOnTop($award_name_layer, 0, $y, 'LT');

		$thumbnail_layers[] = $pic_group_layer;

		$thumbnail_area_height += $pic_group_layer_height;
	}

	// Create Thumbnail Areas
	$thumbnail_area_layer = ImageWorkshop::initVirginLayer($thumbnail_area['width'], $thumbnail_area['height']);

	$y = ($thumbnail_area['height'] - $thumbnail_area_height) / 2;  // Center vertically

	// Add pic_group_layers to Thumbnail Area
	foreach ($thumbnail_layers as $tnl) {
		$thumbnail_area_layer->addLayerOnTop($tnl, 0, $y, "LT");
		$y += $tnl->getHeight();
	}
	$thumbnail_area_layer->mergeAll();

	$poster->addLayerOnTop($thumbnail_area_layer, $thumbnail_area['x'], $thumbnail_area['y'], "LT");

	// List Awards
	$award_area_height = 0;
	$award_layers = [];
	for ($idx = 0; $idx < sizeof($pic_list) && $pic_list[$idx]['level'] < 99; ++ $idx) {
		$award_name = $pic_list[$idx]['award_name'];
		$section = $pic_list[$idx]['award_section'];
		$award_layer = createTextLayer($award_name . " - " . $section, $award, 96, "FFFFFF", "PetitaBold.ttf", "MM");
		$award_area_height += ($award_layer->getHeight() + 20);
		$award_layers[$idx] = $award_layer;
	}

	if (($acceptances + $awards) > 0) {
		$award_layer = createTextLayer(($acceptances + $awards) . " " . (($acceptances + $awards) > 1 ? "Acceptances" : "Acceptance"), $award, 96, "FFFFFF", "PetitaBold.ttf", "MM");
		$award_area_height += $award_layer->getHeight();
		$award_layers[] = $award_layer;
	}

	$award_group_layer = ImageWorkshop::initVirginLayer($award['width'], $award_area_height);
	$y = 0;
	foreach ($award_layers as $al) {
		$award_group_layer->addLayerOnTop($al, 0, $y, "LT");
		$y += ($al->getHeight() + 20);
	}
	$award_group_layer->mergeAll();
	if ($award_group_layer->getHeight() > $award_area['height']) {
		$award_group_layer->resizeInPixel($award_area['width'], $award_area['height'], true, 0, 0, "MM");
	}

	$award_area_layer = ImageWorkshop::initVirginLayer($award_area['width'], $award_area['height']);
	// Add a 70% opacity Black Layer
	$award_area_background_layer = ImageWorkshop::initVirginLayer($award_area['width'], $award_area['height'], "000000");
	$award_area_background_layer->opacity(70);
	$award_area_layer->addLayerOnTop($award_area_background_layer, 0, 0, "LT");
	$y = ($award_area_layer->getHeight() - $award_group_layer->getHeight()) / 2;
	$award_area_layer->addLayerOnTop($award_group_layer, 0, 0, "MM");

	$poster->addLayerOnTop($award_area_layer, $award_area['x'], $award_area['y'], "LT");



	// Write second image
	$savefile = "poster-" . sprintf("%04d", $profile['profile_id']) . ".jpg";
	$poster->save($save_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);


	$result = [];
	$result['status'] = "OK";
	$result['errmsg'] = "";
	echo json_encode($result);
}
else {
	ss_handle_error("Invalid Parameters" , __FILE__, __LINE__);
}

?>

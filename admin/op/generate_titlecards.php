<?php
include("../inc/connect.php");
include("../inc/lib.php");

include_once('../PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;

function assemble_honors($list, $maxlen) {
	$text = "";
	$first = true;
	foreach($list as $item) {
		if (strlen($text . $item) <= $maxlen)
			$text .= ($first ? "" : ", ") . $item;
		$first = false;
	}
	return $text . "...";
}

// Return string containing only alphabets
function str_alpha($str) {
	$str = trim($str);
	$retstr = "";
	for ($i = 0; $i < strlen($str); ++ $i)
		if ((substr($str, $i, 1) >= 'a' && substr($str, $i, 1) <= 'z') || (substr($str, $i, 1) >= 'A' && substr($str, $i, 1) <= 'Z'))
			$retstr .= substr($str, $i, 1);
		else if (substr($str, $i, 1) == ' ')
			$retstr .= "_";

	return $retstr;
}

// define("TEXT_COLOR", "0xf34508"); Text Color for 35th All India Salon
define("TEXT_COLOR", 0x318ba2);
define("GRAY_TEXT_COLOR", 0x808080);
define("GRAY_BACKGROUND_COLOR", 0x808080);
define("SS_WIDTH", 1800);
define("SS_HEIGHT", 1200);
define("SS_BGCOLOR", "202020");
//define("SS_BGLAYER_IMG", "YPS2019IS_SS_BACKDROP.jpg");
define("SS_TITLE_BGCOLOR", "202020");		// Black Layer
define("FIP_GOLD_BG", "201912_tc_fip_gold.png");
define("FIP_SILVER_BG", "201912_tc_fip_silver.png");
define("FIP_BRONZE_BG", "201912_tc_fip_bronze.png");
define("FIP_RIBBON_BG", "201912_tc_fip_ribbon.png");
define("YPS_GOLD_BG", "201912_tc_yps_gold.png");
define("PLAIN_BG", "201912_tc_plain.png");
define("YOUTH_GIFT_BG", "201912_tc_youth_gift.png");


// Generate Post Cards
$yearmonth = 201912;

// Check if already running
if (file_exists("titlecards_lock.txt")) {
	echo "Title Card generator is already running. Quitting";
	die;
}

// Create lock file
$file = fopen("titlecards_lock.txt","w");
echo fputs($file, "Title Cards Generator Running " . $folder . "!");
fclose($file);

// Folders
$bg_img_folder = "../salons/" . $yearmonth . "/img";
$avatar_folder = "../img/avatar";

// Create Slideshow Folder if it does not exist
$tc_folder = "../salons/" . $yearmonth . "/TC";
if (! is_dir($tc_folder))
	mkdir($tc_folder);

// Remove execution time limit for PHP
set_time_limit(0);

debug_dump("start", "Started TC Generator at : " . date("Y-m-d H:i:s"), __FILE__, __LINE__);


	// Get Award/Acceptance Details
	$offset = 0;

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Get All the Award Winning Images
	$sql  = "SELECT pic.profile_id, profile.profile_name, profile.honors, profile.avatar, profile.city, profile.state, ";
	$sql .= "       IFNULL(country.country_name, "") AS country_name, IFNULL(club.club_name, "") AS club_name, ";
	$sql .= "       section.stub, pic.pic_id, pic.title, pic.picfile, award.award_name ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, section, profile ";
	else
		$sql .= "  FROM pic_result, award, pic, section, profile ";
	$sql .= "  LEFT JOIN club ON club.club_id = profile.club_id ";
	$sql .= "  LEFT JOIN country ON country.country_id = profile.country_id ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level <= 9 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= " ORDER BY award.section, award.level, award.sequence, pic.title ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		// Loop through all awards
		while ($res = mysqli_fetch_array($query)) {
			$profile_name = ucwords(str_to_lower($res['profile_name']));
			$honors = $res['honors'];
			$avatar = $avatar_folder . "/" . (($res['avatar'] != "" && $res['avatar'] != "user.jpg") ? $res['avatar'] : "");
			$city = $res['city'];
			$state = $res['state'];
			$country = $res['country_name'];

			$picfile_array = json_decode($res['picfiles']);
			debug_dump("picfile_array", $picfile_array, __FILE__, __LINE__);
			foreach ($picfile_array as $i => $picfile) {
				if ($picfile == "")
					continue;
				$pic_link = "../upload/" . $contest['folder'] . ($contest_archived ? "/ar/" : "/") . $picfile;
				debug_dump("pic_link", $pic_link, __FILE__, __LINE__);

				list($pic_width, $pic_height) = getimagesize($pic_link);
				$pic_orientation = ($pic_width > $pic_height) ? "L" : "P";
				//
				// STEP 1 - Create a layer with just images & text
				// ===================================================
				//
				// Create Layer from Image File
				//
				$image = ImageWorkshop::initFromPath($pic_link);
				if ($pic_orientation == "L") {
					$image->resizeInPixel(SS_WIDTH, null, true, 0, 0, 'MM');
					//
					// create Best of Best Layer
					//
					$bob_layer = ImageWorkshop::initFromPath("../img/" . SS_BOB_L);
					//
					// Add name as Text Layer
					//
//					$fontPath = "../PHPImageWorkshop/font/SIFONN_BASIC.otf";
					$fontPath = "../PHPImageWorkshop/font/PetitaBold.ttf";
					//$fontColor = "000"; // Black
					$fontColor = "ffca08";	// Ochre
					$textRotation = 0;
					$fontSize = 32;
					$name_layer = ImageWorkshop::initTextLayer($name, $fontPath, $fontSize, $fontColor, $textRotation);
					//
					// Place it on Bob Layer
					$bob_layer->addLayeronTop($name_layer, 50, 0, "LM");
					//
					// Add Bob Layer to Image
					//
					$image->addLayerOnTop($bob_layer, 0, 100, "LB");
				}
				else {
					$image->resizeInPixel(SS_HEIGHT, null, true, 0, 0, 'MM');
					//
					// create Best of Best Layer
					//
					$bob_layer = ImageWorkshop::initFromPath("../img/" . SS_BOB_P);
					//
					// Add name as Text Layer
					//
					//$fontPath = "../../PHPImageWorkshop/font/SIFONN_BASIC.otf";
					$fontPath = "../PHPImageWorkshop/font/PetitaBold.ttf";
					$fontColor = "ffca08";	// Ochre
					$textRotation = 0;
					$fontSize = 32;
					$name_layer = ImageWorkshop::initTextLayer($name, $fontPath, $fontSize, $fontColor, $textRotation);
					//
					// Place it on Bob Layer
					$bob_layer->addLayeronTop($name_layer, 40, 20, "RT");
					//
					// Add Bob Layer to Image
					//
					$image->addLayerOnTop($bob_layer, 0, 100, "RB");
				}
				// Add Best of Best Layer
				// Write second image
				$savefile = sprintf("%s-%s-%d", $res['yps_login_id'], str_alpha($name), $i) . ".jpg";
				$image->save($pc_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);
				debug_dump("Saving", $savefile, __FILE__, __LINE__);
			} // foreach picfile
		} // while mysqli_fetch_array
	} // mysqli_num_rows


debug_dump("Ending", "Ended at : " . date("Y-m-d H:i:s"), __FILE__, __LINE__);
unlink("postcards_lock.txt");

?>

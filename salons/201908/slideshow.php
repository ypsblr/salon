<?php
include("../../inc/connect.php");
include("../../inc/lib.php");

include_once('../../PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;


// define("TEXT_COLOR", "0xf34508"); Text Color for 35th All India Salon
define("TEXT_COLOR", 0x318ba2);
define("GRAY_TEXT_COLOR", 0x808080);
define("SS_WIDTH", 1920);
define("SS_HEIGHT", 1080);
define("SS_BGCOLOR", "202020");


if (! empty($_REQUEST['contest']) && ! empty($_REQUEST['stub']) && ! empty($_REQUEST['level']) &&
        ($_REQUEST['level'] == "award" || $_REQUEST['level'] == "acceptance") ) {
    // Print Certificates for all awards under a single section identified by its short stub
    $yearmonth = $_REQUEST['contest'];
	$stub = $_REQUEST['stub'];
    $level = $_REQUEST['level'];

    // Create Slideshow Folder if it does not exist
    $slideshow_folder = "slideshow";
    if (! is_dir($slideshow_folder))
        mkdir($slideshow_folder);

	// Set execution time limit to 5 minutes
	set_time_limit(600);

	echo "Started at : " . date("Y-m-d H:i:s") . "<br>";

	// Get Section Details and Generate a Section Break
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth'AND stub = '$stub' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$sec = mysqli_fetch_array($query);

	// Create a Blank sheet with Section Name
    $section_break = ImageWorkshop::initVirginLayer(SS_WIDTH, SS_HEIGHT, SS_BGCOLOR);

	// Add Text Layer
	$text = $section = $sec['section'];
	$fontPath = "../../PHPImageWorkshop/font/PetitaBold.ttf";
	$fontColor = "C0C0C0";
	$textRotation = 0;
	$fontSize = 72;
	$title_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);

	$section_break->addLayerOnTop($title_layer, 0, 0, "MM");

	// Save
	$folder = $slideshow_folder . "/" . $level;
    if (! is_dir($folder))
        mkdir($folder);
	$savefile = $stub . "000.jpg";
	$section_break->save($folder, $savefile, true, null, $imageQuality = 85, $interlace = false);


    // Get Award/Acceptance Details
    $offset = 0;

    $sql  = "SELECT profile.profile_name, country.country_name, honors, avatar, ";
    $sql .= "       title, pic.section, picfile, award.award_name, award.level, award.sequence ";
    $sql .= "  FROM pic_result, award, pic, profile, country ";
    $sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
    $sql .= "   AND award.yearmonth = pic_result.yearmonth ";
    $sql .= "   AND award.award_id = pic_result.award_id ";
    $sql .= "   AND award.section = '$section' ";
	if ($level == 'acceptance')
		$sql .= "  AND award.level = '99' ";
	else
		$sql .= "  AND award.level < '99' ";
    $sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
    $sql .= "   AND pic.profile_id = pic_result.profile_id ";
    $sql .= "   AND pic.pic_id = pic_result.pic_id ";
    $sql .= "   AND profile.profile_id = pic.profile_id ";
    $sql .= "   AND country.country_id = profile.country_id ";
    $sql .= " ORDER BY award.level, award.sequence, profile.profile_name ";
    // $sql .= " LIMIT 10 OFFSET $offset ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		// Loop through all awards
		$idx = $offset;
		while ($res = mysqli_fetch_array($query)) {
			$name = $res['profile_name'];
			$country = $res['country_name'];
			$honors = trim($res['honors']);
			$avatar = $res['avatar'];
			$title = $res['title'];
			$section = $res['section'];
			$picfile = $res['picfile'];
			$award_name = $res['award_name'];
			// $stub = $res['stub'];

			// Create an empty Layer
			$image = ImageWorkshop::initVirginLayer(SS_WIDTH, SS_HEIGHT, SS_BGCOLOR);

			// Open Avatar Layer and resize to 100 x 100
			if (!empty($avatar) && $avatar != 'user.jpg' && file_exists("../../res/avatar/$avatar")) {
				$avatar_layer = ImageWorkshop::initFromPath("../../res/avatar/$avatar");
				$avatar_layer->resizeInPixel(100, 100, true, 0, 0, "LT");

				// Add Avatar Layer to the image
				$image->addLayerOnTop($avatar_layer, 20, 20, "LT");
			}

			// Open the picfile as Layer
			$pic_layer = ImageWorkshop::initFromPath("upload/$section/$picfile");
			$width = $pic_layer->getWidth();
			$height = $pic_layer->getHeight();

			// Resize and Add to the blank page
			$available_width = SS_WIDTH - 140; 	// 20px space on left + 100px avatar + 20px space on right
			$available_height = SS_HEIGHT - 50;		// For Title etc - 20px space on top + 24px font + 6px space below

			// Determine resize width & height
			if ($width > $available_width) {
				$height = $height * $available_width / $width;
				$width = $available_width;
				if ($height > $available_height) {
					$width = $width * $available_height / $height;
					$height = $available_height;
				}
				$pic_layer->resizeInPixel($width, $height, false);
			}
			elseif ($height > $available_height) {
				$width = $width * $available_height / $height;
				$height = $available_height;
				if ($width > $available_width) {
					$height = $height * $available_width / $width;
					$width = $available_width;
				}
				$pic_layer->resizeInPixel($width, $height, false);
			}

			// $pic_layer->resizeInPixel($width, $height, false);

			// $picture_width = $pic_layer->getWidth();
			// $picture_height = $pic_layer->GetHeight();

			$pic_x = 140 + ($available_width - $width) / 2;
			$pic_y = 50 + ($available_height - $height) / 2;

			$image->addLayerOnTop($pic_layer, $pic_x, $pic_y, 'LT');


			// Create Title Layer
			$text = '"' . $title . '" by ' . $name . ', ' . $country;
			//$fontPath = "../../PHPImageWorkshop/font/PetitaBold.ttf";
			//$fontColor = "C0C0C0";
			$textRotation = 0;
			$fontSize = 24;
			$title_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);
			$title_layer_width = $title_layer->GetWidth();

			// Create Honors Layer
			$honors_layer_width = 0;
			if (! empty($honors)) {
				$text = ", " . $honors;
				//$fontPath = "../../PHPImageWorkshop/font/PetitaBold.ttf";
				//$fontColor = "C0C0C0";
				$textRotation = 0;
				$fontSize = 18;
				$honors_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);
				$honors_layer_width = $honors_layer->getWidth();
			}

			$total_title_width = $title_layer_width + $honors_layer_width;
			$title_x_position = 140 + (($available_width - $total_title_width) / 2);

			// Add Title Layer
			$image->addLayerOnTop($title_layer, $title_x_position, 20, "LT");

			// Add Honors Layer
			if ($honors_layer_width > 0)
				$image->addLayerOnTop($honors_layer, $title_x_position + $title_layer_width + 8, 26, "LT");

			// Add Section & Award Information
			$text = $section . " - " . $award_name;
			//$fontPath = "font/PetitaMedium.ttf";
			//$fontColor = "C0C0C0";
			$textRotation = 90;
			$fontSize = 24;
			$title_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);

			$image->addLayerOnTop($title_layer, (($available_width - $width) / 2) + 110, 20 + (($available_height - $height) / 2), "LB");


			++ $idx;
			$folder = $slideshow_folder . "/" . $level;
			$savefile = $stub . sprintf("%03d", $idx) . ".jpg";
			$image->save($folder, $savefile, true, null, $imageQuality = 85, $interlace = false);
			// if ($idx == 10)
			//	break;		// Let us do it for first image
		}
		echo "Ended at : " . date("Y-m-d H:i:s") . "<br>";

	}
	else
		echo "No Award found for this section stub";
}
else
	echo "Invalid Parameters";

?>

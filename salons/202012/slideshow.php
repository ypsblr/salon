<?php
include("../../inc/connect.php");
include("../../inc/lib.php");

include_once('../../PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;


// define("TEXT_COLOR", "0xf34508"); Text Color for 35th All India Salon
define("TEXT_COLOR", 0x318ba2);
define("GRAY_TEXT_COLOR", 0x808080);
define("SS_BGCOLOR", "202020");

define("SS_BACKDROP", "img/IS2020_SS_BACKDROP.jpg");

define("SS_WIDTH", 1920);
define("SS_HEIGHT", 1080);

define("SS_AWARD_X", 80);
define("SS_AWARD_Y", 25);
define("SS_AWARD_HEIGHT", 625);

define("SS_PIC_LM", 180);
define("SS_PIC_RM", 0);
define("SS_PIC_TM", 0);
define("SS_PIC_BM", 0);

define("SS_OL_HEIGHT", 140);
define("SS_OL_WIDTH", SS_WIDTH - SS_PIC_LM - SS_PIC_RM);
define("SS_OL_MARGIN", 20);
define("SS_OL_X", SS_PIC_LM);
define("SS_OL_Y", SS_HEIGHT - SS_OL_MARGIN - SS_OL_HEIGHT);

define("SS_AVATAR_WIDTH", 100);
define("SS_AVATAR_HEIGHT", 100);

define("SS_TITLE_WIDTH", SS_OL_WIDTH / 2);

define("SS_PROFILE_WIDTH", SS_OL_WIDTH / 2);

function stop_with_error($errmsg, $file, $line) {
    echo "ERROR on line $line : $errmsg <br>";
    die();
}

function stop_sql_error($sql, $errmsg, $file, $line) {
    echo "ERROR on line $line : $errmsg <br>";
    echo "$sql <br>";
    die();
}

function award_color($level) {
    switch($level) {
        case 1 : { return "FFD700"; } // Gold
        case 2 : { return "D1D1E0"; } // Silver
        case 3 : { return "CD7F32"; } // Bronze
        case 9 : { return "3399FF"; } // Ribbon
        default : { return "47476B"; } // Acceptance
    }
}


if (! empty($_REQUEST['ss']) ) {
    // Print Certificates for all awards under a single section identified by its short stub
    list($yearmonth, $section) = explode("|", decode_string_array($_REQUEST['ss']));

	// Set execution time limit to 10 minutes
	set_time_limit(0);

    echo "Generating Slides for $section<br>";
	echo "Started at : " . date("Y-m-d H:i:s") . "<br>";

    // Get Contest Details
    $sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
    $query = mysqli_query($DBCON, $sql) or stop_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    if (mysqli_num_rows($query) == 0)
        stop_with_error("Salon $yearmonth not found<br>", __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
    $is_international = ($contest['is_international'] == 1);
    $contest_archived = ($contest['archived'] == 1);

	// Get Section Details and Generate a Section Break
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth'AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql) or stop_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    if (mysqli_num_rows($query) == 0)
        stop_with_error("Section $section not found<br>", __FILE__, __LINE__);
	$sec = mysqli_fetch_array($query);
    $stub = $sec['stub'];

    // Create Slideshow Folder if it does not exist
    $slideshow_folder = "../../tmp/$yearmonth/slideshow/$stub";
    if (! is_dir($slideshow_folder))
        mkdir($slideshow_folder, 0777, true);

	// Create a Blank sheet with Section Name
    $section_break = ImageWorkshop::initVirginLayer(SS_WIDTH, SS_HEIGHT, SS_BGCOLOR);

	// Add Text Layer
	$text = $section = $sec['section'];
    // $fontPath = "../../PHPImageWorkshop/font/PetitaBold.ttf";
    // $fontPath = "../../PHPImageWorkshop/font/GothamCond-Book.ttf";
	$fontPath = "../../PHPImageWorkshop/font/DejaVuSans.ttf";
	$fontColor = "C0C0C0";
	$textRotation = 0;
	$fontSize = 72;
	$title_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);

	$section_break->addLayerOnTop($title_layer, 0, 0, "MM");

	// Save
	$savefile = $stub . "-000.jpg";
	$section_break->save($slideshow_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);


    // Get Award/Acceptance Details
    $offset = 0;

    $sql  = "SELECT profile.profile_name, country.country_name, honors, avatar, city, state, ";
    $sql .= "       title, pic.section, picfile, award.award_name, award.level, award.sequence ";
    if ($contest_archived)
        $sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile, country ";
    else
        $sql .= "  FROM pic_result, award, pic, profile, country ";
    $sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
    $sql .= "   AND award.yearmonth = pic_result.yearmonth ";
    $sql .= "   AND award.award_id = pic_result.award_id ";
    $sql .= "   AND award.section = '$section' ";
    $sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
    $sql .= "   AND pic.profile_id = pic_result.profile_id ";
    $sql .= "   AND pic.pic_id = pic_result.pic_id ";
    $sql .= "   AND profile.profile_id = pic.profile_id ";
    $sql .= "   AND country.country_id = profile.country_id ";
    $sql .= " ORDER BY profile.profile_name, award.level, award.sequence ";
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
            $award_level = $res['level'];
            $location = ($is_international) ? $res['city'] . ", " . $res['country_name'] : $res['city'] . ", " . $res['state'];

            ++ $idx;

            // A. Create First Slide with just the Award and the Picture
            // =========================================================
			// Create a Layer from the template
			$image = ImageWorkshop::initFromPath(SS_BACKDROP);

			// Open the picfile as Layer
			$pic_layer = ImageWorkshop::initFromPath("upload/$section/$picfile");
			$width = $pic_layer->getWidth();
			$height = $pic_layer->getHeight();

			// Resize and Add to the blank page
			$available_width = SS_WIDTH - SS_PIC_LM - SS_PIC_RM; 	// 20px space on left + 100px avatar + 20px space on right
			$available_height = SS_HEIGHT - SS_PIC_TM - SS_PIC_BM;		// Picture to fill the entire vertical space

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

			$pic_x = SS_PIC_LM + ($available_width - $width) / 2;
			$pic_y = SS_PIC_TM + ($available_height - $height) / 2;

			$image->addLayerOnTop($pic_layer, $pic_x, $pic_y, 'LT');

            // Add Section & Award Information
			$text = $section . " - " . $award_name;
			$textRotation = 90;
			$fontSize = 24;
			$text_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, award_color($award_level), $textRotation);
            if ($text_layer->GetHeight() > SS_AWARD_HEIGHT) {
                $text_layer->resizeInPixel(null, SS_AWARD_HEIGHT, false);
            }
			$image->addLayerOnTop($text_layer, SS_AWARD_X, SS_AWARD_Y, "LB");

            // Save First Slide
			$savefile = sprintf("%s-%03d-A.jpg", $stub, $idx);
			$image->save($slideshow_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);


            // 2. Add User Information and Generate Slide B
            // ============================================
            // Create a semi transparent oerlay for text
            $overlay_layer = ImageWorkshop::initVirginLayer(SS_OL_WIDTH, SS_OL_HEIGHT, SS_BGCOLOR);
            $overlay_layer->opacity(50);    // Change Opacity of background
            // $image->addLayerOnTop($overlay_layer, SS_OL_X, SS_OL_Y, 'LT');

            // Add Image Title
            $textRotation = 0;
			$fontSize = 24;
			$text_layer = ImageWorkshop::initTextLayer($title, $fontPath, $fontSize, $fontColor, $textRotation);
			$title_layer_width = $title_layer->GetWidth();
            if ($text_layer->GetWidth() > SS_TITLE_WIDTH) {
                $text_layer->resizeInPixel(SS_TITLE_WIDTH, null, false);
            }
            $overlay_layer->addLayerOnTop($text_layer, SS_OL_MARGIN, SS_OL_MARGIN, "LT");

            // Open Avatar Layer and resize to 100 x 100 and add to the left of overlay layer
            $avatar_layer_width = 0;
			if (!empty($avatar) && $avatar != 'user.jpg' && file_exists("../../res/avatar/$avatar")) {
				$avatar_layer = ImageWorkshop::initFromPath("../../res/avatar/$avatar");
				$avatar_layer->resizeInPixel(SS_AVATAR_WIDTH, SS_AVATAR_HEIGHT, true, 0, 0, "LT");

				// Add Avatar Layer to the image
				$overlay_layer->addLayerOnTop($avatar_layer, SS_OL_MARGIN, SS_OL_MARGIN, "RT");

                $avatar_layer_width = $avatar_layer->GetWidth() + SS_OL_MARGIN;
			}

            $available_profile_width = SS_PROFILE_WIDTH - (2 * SS_OL_MARGIN) - $avatar_layer_width;
            $profile_y = SS_OL_MARGIN;
            $profile_x = SS_OL_MARGIN + $avatar_layer_width;

            // Add Profile Name
            $textRotation = 0;
			$fontSize = 24;
			$text_layer = ImageWorkshop::initTextLayer($name, $fontPath, $fontSize, $fontColor, $textRotation);
            if ($text_layer->GetWidth() > $available_profile_width) {
                $text_layer->resizeInPixel($available_profile_width, null, false);
            }
            $layer_height = $text_layer->GetHeight();
            $overlay_layer->addLayerOnTop($text_layer, $profile_x, $profile_y, "RT");

            $profile_y += $layer_height + 8;   // Advance with 8 pixel margin

            // Add Honors Layer
			if (! empty($honors)) {
                $textRotation = 0;
    			$fontSize = 14;
    			$text_layer = ImageWorkshop::initTextLayer($honors, $fontPath, $fontSize, $fontColor, $textRotation);
                if ($text_layer->GetWidth() > $available_profile_width) {
                    $text_layer->resizeInPixel($available_profile_width, null, false);
                }
                $layer_height = $text_layer->GetHeight();
                $overlay_layer->addLayerOnTop($text_layer, $profile_x, $profile_y, "RT");

                $profile_y += $layer_height + 8;   // Advance with 8 pixel margin
			}

            // Add Location
            $textRotation = 0;
			$fontSize = 18;
            $text_layer = ImageWorkshop::initTextLayer($location, $fontPath, $fontSize, $fontColor, $textRotation);
            if ($text_layer->GetWidth() > $available_profile_width) {
                $text_layer->resizeInPixel($available_profile_width, null, false);
            }
            $layer_height = $text_layer->GetHeight();
            $overlay_layer->addLayerOnTop($text_layer, $profile_x, $profile_y, "RT");

            $profile_y += $layer_height + 8;   // Advance with 8 pixel margin

			// Add Overla Layer to image and Generate Slide B
			$image->addLayerOnTop($overlay_layer, SS_OL_X, SS_OL_Y, "LT");
            $savefile = sprintf("%s-%03d-B.jpg", $stub, $idx);
			$image->save($slideshow_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);

			// if ($idx == 10)
			//	break;		// Let us do it for first image
		}
		echo "Ended at : " . date("Y-m-d H:i:s") . "<br>";

	}
	else {
		echo "No Award found for this section stub";
    }
}
else {
	echo "Invalid Parameters";
}
?>

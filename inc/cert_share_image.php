<?php
/*
** This program is used to generate a FB Share Image for the participant
*/
include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/share_img_lib.php");

// Validations
// if (empty($_REQUEST['code']))
// 	handle_error("Open from the results email", __FILE__, __LINE__);
//
// list ($si_yearmonth, $si_profile_id) = explode("|", decode_string_array($_REQUEST['code']));

function cert_share_image($si_yearmonth, $si_profile_id, $width, $height) {
	global $DBCON;
	global $contest_archived;
	global $table_pic;
	global $table_pic_result;

	// Gather Awards
	// Load profile details
	$sql = "SELECT * FROM profile WHERE profile_id = '$si_profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
	    handle_error("Unable to generate image for sharing. Incorrect user reference !", __FILE__, __LINE__);
	$si_profile = mysqli_fetch_array($query);

	// Check for Entry Awards
	$sql  = "SELECT award_name FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$si_yearmonth' ";
	$sql .= "   AND entry_result.profile_id = '$si_profile_id' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$si_has_entry_awards = false;
	$si_spl_award_list = [];
	if (mysqli_num_rows($query) > 0) {
		$si_has_entry_awards = true;
		// while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			// $si_spl_award_list[] = $row['award_name'];
	}

	// Check for Special Picture Awards
	$sql  = "SELECT pic.pic_id, pic.section, title, picfile, level, award_name ";
    $sql .= "  FROM $table_pic AS pic, $table_pic_result AS pic_result, award ";
	$sql .= " WHERE pic.yearmonth = '$si_yearmonth' ";
	$sql .= "   AND pic.profile_id = '$si_profile_id' ";
	$sql .= "   AND pic_result.yearmonth = pic.yearmonth ";
	$sql .= "   AND pic_result.profile_id = pic.profile_id ";
	$sql .= "   AND pic_result.pic_id = pic.pic_id ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.section = 'CONTEST' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
	    $si_spl_award_list[] = $row['award_name'];

	$si_spl_award_text = implode(",", $si_spl_award_list);

	// Assemble Pictures and Awards
	$sql  = "SELECT pic.pic_id, pic.section, title, picfile, level, award_name ";
    $sql .= "  FROM $table_pic AS pic, $table_pic_result AS pic_result, award ";
	$sql .= " WHERE pic.yearmonth = '$si_yearmonth' ";
	$sql .= "   AND pic.profile_id = '$si_profile_id' ";
	$sql .= "   AND pic_result.yearmonth = pic.yearmonth ";
	$sql .= "   AND pic_result.profile_id = pic.profile_id ";
	$sql .= "   AND pic_result.pic_id = pic.pic_id ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= " ORDER BY level, sequence ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$picfiles = [];
	$si_awards = 0;
	$si_acceptances = mysqli_num_rows($query);
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		if ($contest_archived)
			$picfiles[] = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $si_yearmonth . "/upload/" . $row['section'] . "/ar/" . $row['picfile'];
		else
			$picfiles[] = $_SERVER['DOCUMENT_ROOT'] . "/salons/" . $si_yearmonth . "/upload/" . $row['section'] . "/" . $row['picfile'];
	    $si_awards += ($row['level'] < 99 ? 1 : 0);
	}

	if ($si_acceptances > 0) {
	    $si_pic_award_text = $si_acceptances . " Acceptance" . ($si_acceptances == 1 ? "" : "s");
	    if ($si_awards > 0)
	        $si_pic_award_text .= ", " . $si_awards . " Award" . ($si_awards == 1 ? "" : "s");
	}

	$slide_width = round($width * 4.2, 0);
	$slide_height = round($height * 4.2, 0);
	$gap_size = 2;

	// Create the Poster
	$poster = new ShareImage(NULL, $slide_width, $slide_height);

	// Add area for rendering profile name and honors
	if ($si_acceptances > 0) {
	    // Add area for rendering thumbnail
	    $poster->createThumbnailArea(0, 0, $slide_width, $slide_height - 30, $gap_size);
	    $poster->addThumbnails($picfiles);
	    // Award area
		$pic_award_font = array("size" => 36, "color" => "FF0000", "font" => "PetitaBold.ttf");
	    $spl_award_font = array("size" => 36, "color" => "FF0000", "font" => "PetitaBold.ttf");
	    $poster->createAwardArea(20, $slide_height - 24 , $slide_width - (2 * 20), 24, $pic_award_font, $spl_award_font);
	    $poster->addAwards($si_pic_award_text, $si_spl_award_text);
	}
	// Save Share Poster
	$save_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/upload/share";
	if (! is_dir($save_folder))
		mkdir($save_folder);

	$savefile = "yps-cert-" . sprintf("%04d", $si_profile_id) . ".png";
	$poster->saveShareImg($save_folder, $savefile);

	return $save_folder . "/" . $savefile;
}
?>

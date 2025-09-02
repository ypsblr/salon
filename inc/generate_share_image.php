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
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$si_spl_award_list[] = $row['award_name'];
}

// Check for Special Picture Awards
$sql  = "SELECT pic.pic_id, pic.section, title, picfile, level, award_name ";
if ($contest_archived)
    $sql .= "  FROM ar_pic pic, ar_pic_result pic_result, award ";
else
   $sql .= "  FROM pic, pic_result, award ";
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
if ($contest_archived)
    $sql .= "  FROM ar_pic pic, ar_pic_result pic_result, award ";
else
   $sql .= "  FROM pic, pic_result, award ";
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
if (($num_wins = mysqli_num_rows($query)) == 0) {
    $slide_image = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/img/participant_poster.jpg";
}
$picfiles = [];
$si_awards = 0;
$si_acceptances = $num_wins;
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
        $si_pic_award_text .= "\n    " . $si_awards . " Award" . ($si_awards == 1 ? "" : "s");
        // $si_pic_award_text .= ", " . $si_awards . " Award" . ($si_awards == 1 ? "" : "s");
        // $si_pic_award_text = nl2br($si_pic_award_text);
    $slide_image = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/img/winner_poster.png";
}
else
    $slide_image = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/img/participant_poster.png";

if (! file_exists($slide_image))
    handle_error("Image Components missing. Please report to YPS.", __FILE__, __LINE__);

$sharedef_file = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/blob/sharedef.json";
if (! file_exists($slide_image))
    handle_error("Definition Components missing. Please report to YPS.", __FILE__, __LINE__);

$page = [];
$blocks = [];
$fields = [];

$sharedef_json = file_get_contents($sharedef_file);
$sharedef = json_decode($sharedef_json, true);
if (json_last_error() != JSON_ERROR_NONE)
	handle_error("Definition garbled. Please report to YPS.", __FILE__, __LINE__);

if (isset($sharedef["page"]))
	$page = $sharedef["page"];

if (isset($sharedef["blocks"]))
	$blocks = $sharedef["blocks"];

if (isset($sharedef["fields"]))
	$fields = $sharedef["fields"];

if (sizeof($page) == 0 || sizeof($blocks) == 0 || sizeof($fields) == 0)
	handle_error("Page Definition corrupted. Report to YPS", __FILE__, __LINE__);

$slide_width = $page["width"];
$slide_height = $page["height"];
$gap_size = $page["gap"];

// Create the Poster
$poster = new ShareImage($slide_image, $slide_width, $slide_height);

// Add area for rendering profile name and honors
$name_font = array("size" => $fields["name"]["font_size"], "color" => $fields["name"]["color"], "font" => $fields["name"]["font"]);
$honors_font = array("size" => $fields["honors"]["font_size"], "color" => $fields["honors"]["color"], "font" => $fields["honors"]["font"]);
$poster->createProfileArea ($blocks["profile"]["x"], $blocks["profile"]["y"], $blocks["profile"]["width"], $blocks["profile"]["height"], $name_font, $honors_font);
$si_name = ucwords(strtolower($si_profile['profile_name']));
$si_honors = $si_profile['honors'];
$poster->addProfile($si_name, $si_honors);

// Create Thumbnails of accepted images and award information
if ($si_acceptances > 0) {
    // Add area for rendering thumbnail
	$poster->createThumbnailArea ($blocks["thumbnails"]["x"], $blocks["thumbnails"]["y"], $blocks["thumbnails"]["width"], $blocks["thumbnails"]["height"], $gap_size);
    $poster->addThumbnails($picfiles);
	// $poster->addPartnerLogo("salons/202208/img/sponsor/zeiss_partner_logo.jpg", 120,
	// 				"www.zeiss.com/consumer-products/int/photography.html", 550, 660, 500, 140);
    // Award area
	$pic_award_font = array("size" => $fields["pic_wins"]["font_size"], "color" => $fields["pic_wins"]["color"], "font" => $fields["pic_wins"]["font"]);
	$spl_award_font = array("size" => $fields["spl_wins"]["font_size"], "color" => $fields["spl_wins"]["color"], "font" => $fields["spl_wins"]["font"]);
	$poster->createAwardArea ($blocks["wins"]["x"], $blocks["wins"]["y"], $blocks["wins"]["width"], $blocks["wins"]["height"], $pic_award_font, $spl_award_font);
    $poster->addAwards($si_pic_award_text, $si_spl_award_text);
}
// Save Share Poster
$save_folder = $_SERVER['DOCUMENT_ROOT'] . "/salons/$si_yearmonth/upload/share";
if (! is_dir($save_folder))
	mkdir($save_folder);

$savefile = "yps-poster-" . sprintf("%04d", $si_profile_id) . ".jpg";
$poster->saveShareImg($save_folder, $savefile);

?>

<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function blob_file($blob_type) {
	switch ($blob_type) {
		case "contest_description" : return "contest_description.htm";
		case "terms_conditions" : return "terms_conditions.htm";
		case "contest_announcement" : return "contest_announcement.htm";
		case "fee_structure" : return "fee_structure.htm";
		case "discount_structure" : return "discount_structure.htm";
		case "judging_description" : return "judging_description.htm";
		case "judging_report" : return "judging_report.htm";
		case "results_description" : return "results_description.htm";
		case "exhibition_description" : return "exhibition_description.htm";
		case "exhibition_report" : return "exhibition_report.htm";
		case "exhibition_schedule" : return "exhibition_schedule.htm";
		case "exhibition_invite_venue" : return "exhibition_invite_venue.htm";
		case "exhibition_invite_webinar" : return "exhibition_invite_webinar.htm";
		case "chair_blob" : return "exhibition_chair_blob.htm";
		case "guest_blob" : return "exhibition_guest_blob.htm";
		case "other_blob" : return "exhibition_other_blob.htm";
		case "chairman_message" : return "chairman_message.htm";
		default : return "custom_blob.htm";
	}
}

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['blob_type']) && isset($_REQUEST['blob_file']) ) {


	$yearmonth = $_REQUEST['yearmonth'];
	$blob_type = $_REQUEST['blob_type'];
	$blob_file = $_REQUEST['blob_file'];

	if ($blob_file == "")
		$blob_file = blob_file($blob_type);

	// Get file from Salon Folder if available
	if (file_exists("../../salons/$yearmonth/blob/$blob_file"))
		$blob_content = file_get_contents("../../salons/$yearmonth/blob/$blob_file");
	else if (file_exists("../template/blob/$blob_file"))
		$blob_content = file_get_contents("../template/blob/$blob_file");
	else
		$blob_content = file_get_contents("../template/blob/custom_blob.htm");
		// return_error("Unable to find the blob file or the template", __FILE__, __LINE__, true);


	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray["blob_file"] = $blob_file;
	$resArray["blob_content"] = $blob_content;
	echo json_encode($resArray, JSON_FORCE_OBJECT);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

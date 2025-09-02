<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function null_safe_date($date) {
	if ($date == NULL || $date == "")
		return "NULL";
	else
		return "'" . $date . "'";
}

function sort_on_price($catalog_list) {
	if (sizeof($catalog_list) <= 1)
		return $catalog_list;		// Nothing to sort

	// Do Bubble Sort on Price till no records are swapped
	do {
		$swaps = 0;
		for ($i = 1; $i < sizeof($catalog_list); ++ $i) {
			if ($catalog_list[$i]["price"] < $catalog_list[$i-1]["price"]) {
				$temp = $catalog_list[$i];
				$catalog_list[$i] = $catalog_list[$i-1];
				$catalog_list[$i-1] = $temp;
				++ $swaps;
			}
		}
	} while ($swaps > 0);

	return $catalog_list;
}


// Main Code
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['setup_exhibition']) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("There is no Salon for $yearmonth ", __FILE__, __LINE__, true);
	$salon = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Assemble values
	// Contest Data
	$update_end_date = null_safe_date($_REQUEST['update_end_date']);
	$exhibition_start_date = null_safe_date($_REQUEST['exhibition_start_date']);
	$exhibition_end_date = null_safe_date($_REQUEST['exhibition_end_date']);
	$exhibition_name = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_name']);
	$exhibition_venue = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue']);
	$exhibition_venue_address = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue_address']);
	$exhibition_venue_location_map = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue_location_map']);

	// Exhibition Data
	$is_virtual = isset($_REQUEST['is_virtual']) ? "1" : "0";
	$virtual_tour_ready = isset($_REQUEST['virtual_tour_ready']) ? "1" : "0";
	$exhibition_report_blob = $_REQUEST['exhibition_report_blob'];
	$schedule_blob = $_REQUEST['schedule_blob'];
	$email_message_blob = $_REQUEST['email_message_blob'];
	$dignitory_roles = $_REQUEST['dignitory_roles'];
	$dignitory_names = $_REQUEST['dignitory_names'];
	$dignitory_positions = $_REQUEST['dignitory_positions'];
	// $dignitory_avatars = $_REQUEST['dignitory_avatars'];
	$dignitory_blobs = $_REQUEST['dignitory_blobs'];

	// Get existing images
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$invitation_img = "";
		$email_header_img = "";
		$dignitory_avatars = ["", "", ""];
	}
	else {
		$exhibition = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$dignitory_avatars = explode("|", $exhibition['dignitory_avatars']);
		while(sizeof($dignitory_avatars) < 3) {
			array_push($dignitory_avatars, "");
		}
		$invitation_img = $exhibition['invitation_img'];
		$email_header_img = $exhibition['email_header_img'];
	}
	$target_folder = "../../salons/$yearmonth/img";
	// debug_dump("FILES", $_FILES, __FILE__, __LINE__);

	if (isset($_FILES)) {
		// Invitation Card Image
		if (isset($_FILES['invitation_img']['name'])) {
			if ($_FILES['invitation_img']['error'] != UPLOAD_ERR_NO_FILE) {
				if ($_FILES['invitation_img']['error'] != UPLOAD_ERR_OK) {
					return_error("Error in uploading Invitation Card. Try again", __FILE__, __LINE__, true);
				}
				else {
					$file_name = "exhibition-invite-card.jpg";
					$rename_file_name = "exhibition-invite-card_" . date("YmdHi") . ".jpg";
					$target_file = $target_folder . "/" . $file_name;
					if (file_exists($target_file)) {
						// Rename to keep the previous version
						rename($target_file, $target_folder . "/" . $rename_file_name);
					}
					if (! move_uploaded_file($_FILES['invitation_img']['tmp_name'], $target_file))
						return_error("Error in copying Invitation Card", __FILE__, __LINE__, true);
					$invitation_img = $file_name;
				}
			}
		}

		// Email Header Image
		if (isset($_FILES['email_header_img']['name'])) {
			if ($_FILES['email_header_img']['error'] != UPLOAD_ERR_NO_FILE) {
				if ($_FILES['email_header_img']['error'] != UPLOAD_ERR_OK) {
					return_error("Error in uploading Exhibition email banner. Try again", __FILE__, __LINE__, true);
				}
				else {
					$file_name = "exhibition-banner.jpg";
					$rename_file_name = "exhibition-banner_" . date("YmdHi") . ".jpg";
					$target_file = $target_folder . "/" . $file_name;
					if (file_exists($target_file)) {
						// Rename to keep the previous version
						rename($target_file, $target_folder . "/" . $rename_file_name);
					}
					if (! move_uploaded_file($_FILES['email_header_img']['tmp_name'], $target_file))
						return_error("Error in copying Exhibition email banner", __FILE__, __LINE__, true);
					$email_header_img = $file_name;
				}
			}
		}

		// Dignitory Avatars
		debug_dump("Dignitory Avatars", $_FILES['dignitory_avatars'], __FILE__, __LINE__);
		for ($idx = 0; $idx < sizeof($_FILES['dignitory_avatars']); ++$idx) {
			if (isset($_FILES['dignitory_avatars']['name'][$idx])) {
		 		if ($_FILES['dignitory_avatars']['error'][$idx] != UPLOAD_ERR_NO_FILE) {
					if ($_FILES['dignitory_avatars']['error'][$idx] != UPLOAD_ERR_OK) {
						return_error("Error in uploading avatar. Try again", __FILE__, __LINE__, true);
					}
					else {
						$file_name = str_replace(" ", "_", $dignitory_names[$idx]) . "." . pathinfo($_FILES['dignitory_avatars']['name'][$idx], PATHINFO_EXTENSION);
						$rename_file_name = str_replace(" ", "_", $dignitory_names[$idx]) . "_" . date("YmdHi") . "." . pathinfo($_FILES['dignitory_avatars']['name'][$idx], PATHINFO_EXTENSION);
						$target_file = $target_folder . "/" . $file_name;
						if (file_exists($target_file)) {
							// Rename to keep the previous version
							rename($target_file, $target_folder . "/" . $rename_file_name);
						}
						if (! move_uploaded_file($_FILES['dignitory_avatars']['tmp_name'][$idx], $target_file))
							return_error("Error in copying avatar", __FILE__, __LINE__, true);
						$dignitory_avatars[$idx] = $file_name;
					}
				}
			}
		}
	}

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert if New
	// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
	$sql  = "UPDATE contest ";
	$sql .= "   SET exhibition_start_date = $exhibition_start_date ";
	$sql .= "     , exhibition_end_date = $exhibition_end_date ";
	$sql .= "     , exhibition_name = '$exhibition_name' ";
	$sql .= "     , exhibition_venue = '$exhibition_venue' ";
	$sql .= "     , exhibition_venue_address = '$exhibition_venue_address' ";
	$sql .= "     , exhibition_venue_location_map = '$exhibition_venue_location_map' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$dignitory_roles_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_roles));
	$dignitory_names_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_names));
	$dignitory_positions_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_positions));
	$dignitory_avatars_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_avatars));
	$dignitory_blobs_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_blobs));

	// Check if Exbition record exists
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		// Create Exhibition Record
		$sql  = "INSERT INTO exhibition (yearmonth, schedule_blob, invitation_img, email_header_img, email_message_blob, ";
		$sql .= "                        dignitory_roles, dignitory_names, dignitory_positions, dignitory_avatars, ";
		$sql .= "                        dignitory_profile_blobs, is_virtual, virtual_tour_ready) ";
		$sql .= "VALUES('$yearmonth', '$schedule_blob', '$invitation_img', '$email_header_img', '$email_message_blob', ";
		$sql .= "       '$dignitory_roles_txt', '$dignitory_names_txt', '$dignitory_positions_txt', '$dignitory_avatars_txt', ";
		$sql .= "       '$dignitory_blobs_txt', '$is_virtual', '$virtual_tour_ready' )";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	else {
		$sql  = "UPDATE exhibition ";
		$sql .= "   SET schedule_blob = '$schedule_blob' ";
		$sql .= "     , invitation_img = '$invitation_img' ";
		$sql .= "     , email_header_img = '$email_header_img' ";
		$sql .= "     , email_message_blob = '$email_message_blob' ";
		$sql .= "     , dignitory_roles = '$dignitory_roles_txt' ";
		$sql .= "     , dignitory_names = '$dignitory_names_txt' ";
		$sql .= "     , dignitory_positions = '$dignitory_positions_txt' ";
		$sql .= "     , dignitory_avatars = '$dignitory_avatars_txt' ";
		$sql .= "     , dignitory_profile_blobs = '$dignitory_blobs_txt' ";
		$sql .= "     , is_virtual = '$is_virtual' ";
		$sql .= "     , virtual_tour_ready = '$virtual_tour_ready' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$salon_event = $_REQUEST['salon_event'];
	$sequence = $_REQUEST['photo_sequence'];

	$csv_file = "../../salons/$yearmonth/blob/photos.csv";
	$csv_tmp =  "../../salons/$yearmonth/blob/photos_tmp.csv";
	if (! file_exists($csv_file))
		return_error("Photo CSV not found", __FILE__, __LINE__);

	$file_tmp = fopen($csv_tmp, "w");
	$file_org = fopen($csv_file, "r");

	// copy rows from photos file to tmp file except the one matching the event and sequence
	while ($row = fgetcsv($file_org)) {
		if ($row[0] == $salon_event && $row[1] == $sequence)
			continue;		// skip copying
		else
			fputcsv($file_tmp, $row);
	}

	fclose($file_tmp);
	fclose($file_org);

	// Rename Temp as original
	rename($csv_tmp, $csv_file);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Request", __FILE__, __LINE__);

?>

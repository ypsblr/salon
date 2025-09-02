<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['blob_file']) ) {

	$blob_file = $_REQUEST['blob_file'];

	if ($blob_file == "")
		return_error("Blob File Name is blank", __FILE__, __LINE__);

	// Get file from Salon Folder if available
	if (file_exists("../../blob/jury/$blob_file"))
		$blob_content = file_get_contents("../../blob/jury/$blob_file");
	else
		$blob_content = file_get_contents("../template/blob/custom_blob.htm");

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray["blob_content"] = $blob_content;
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>

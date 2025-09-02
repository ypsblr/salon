<?php
include("../inc/session.php");
include "../inc/connect.php";
include("../inc/dindent/Indenter.php");
include_once("ajax_lib.php");


// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['blob_file']) && isset($_REQUEST['blob_content']) ) {

	$blob_file = $_REQUEST['blob_file'];
	$blob_content = $_REQUEST['blob_content'];
	$blob_file_basename = basename($blob_file, ".htm");

	if (file_exists("../../blob/jury/$blob_file"))
		rename("../../blob/jury/$blob_file", "../../blob/jury/$blob_file_basename" . "_" . date("YmdHi") . ".htm");

	// Indent to make HTML readable
	$indenter = new \Gajus\Dindent\Indenter(["indentation_character" => "\t"]);

	file_put_contents("../../blob/jury/$blob_file", $indenter->indent($blob_content));

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

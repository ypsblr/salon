<?php
include("../inc/session.php");
include "../inc/connect.php";
// include("../inc/dindent/Indenter.php");

include_once("ajax_lib.php");


// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['blob_type']) &&
	isset($_REQUEST['blob_file']) && isset($_REQUEST['blob_content']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$blob_type = $_REQUEST['blob_type'];
	$blob_file = $_REQUEST['blob_file'];

	// if ($blob_type == "textarea") {
	// 	$blob_content = mysqli_real_escape_string($DBCON, $_REQUEST['blob_content']);
	// 	list($table, $column, $textarea_id) = explode("|", $blob_file);
	// 	$sql  = "UPDATE $table ";
	// 	$sql .= "   SET $column = '$blob_content' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// }
	// else {
	$blob_content = $_REQUEST['blob_content'];
	$blob_file_basename = basename($blob_file, ".htm");

	if (file_exists("../../salons/$yearmonth/blob/$blob_file"))
		rename("../../salons/$yearmonth/blob/$blob_file", "../../salons/$yearmonth/blob/$blob_file_basename" . "_" . date("YmdHi") . ".htm");

	// Indent to make HTML readable
	// $indenter = new \Gajus\Dindent\Indenter(["indentation_character" => "\t"]);

	// file_put_contents("../../salons/$yearmonth/blob/$blob_file", $indenter->indent($blob_content));
	file_put_contents("../../salons/$yearmonth/blob/$blob_file", $blob_content);
	// }

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");

if (isset($_REQUEST['upload_code'])) {
	$upload_code = strrev($_REQUEST['upload_code']);	// Reverse to restore original sequence
	$entry_id = substr($upload_code, 0, 4);
	$pic_id = substr($upload_code, 4);
	$upload_error = "";

	$sql = "SELECT *, entry.name AS entry_name, award.name AS award_name, pic.section AS pic_section ";
	$sql .= " FROM entry, pic, award, result ";
	$sql .= " WHERE entry.entry_id = pic.entry_id ";
	$sql .= "   AND pic.pic_id = result.pic_id ";
	$sql .= "   AND result.award_id = award.award_id ";
	$sql .= "   AND award.type = 'pic' ";
	$sql .= "   AND award.level != 99 ";
	$sql .= "   AND entry.entry_id = '$entry_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	$qry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		$section = $res['pic_section'];
		$award_name = $res['award_name'];
		$entry_name = $res['entry_name'];
		$title = $res['title'];
		$msg = "Hi $entry_name, Please upload full resolution JPEG file for '$title' that won '$award_name' award.";
	}
	else
		$msg = "ERR";
}
else {
	$msg = "ERR";
}
echo $msg;
?>

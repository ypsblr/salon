<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

// function notify_error($context, $errmsg, $file, $line, $updated = false) {
// 	debug_dump($context, $errmsg, $file, $line);
// 	echo json_encode(array("status" => "ERROR", "context" => $context, "errmsg" => $errmsg, "file" => $file, "line" => $line, "updated" => $updated));
// 	die();
// }

// MAIN
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
 	isset($_REQUEST['yearmonth']) && isset($_REQUEST['member_login_id']) && isset($_REQUEST['section']) &&
    isset($_REQUEST['reviewed_till']) && isset($_REQUEST['reviewed_profiles']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$member_login_id = $_REQUEST['member_login_id'];
    $section = $_REQUEST['section'];
    $reviewed_till = $_REQUEST['reviewed_till'];

    $sql = "SELECT reviewed FROM team WHERE yearmonth = '$yearmonth' AND member_login_id = '$member_login_id' ";
    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    if (mysqli_num_rows($query) == 0)
        return_error("Invalid parameters");
    $row = mysqli_fetch_array($query, MYSQLI_ASSOC);
    if ($row['reviewed'] == NULL)
        $reviewed = [];
    else
        $reviewed = json_decode($row['reviewed'], true);

    $reviewed[$section] = array("reviewed_till" => $reviewed_till, "reviewed_profiles" => $_REQUEST['reviewed_profiles']);

	// UPDATE reviewed profiles
	$sql  = "UPDATE team ";
	$sql .= "   SET reviewed = '" . json_encode($reviewed) . "' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND member_login_id = '$member_login_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	echo json_encode(array("success" => TRUE, "msg" => ""));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Missing parameters", __FILE__, __LINE__);
}
?>

<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

// MAIN
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
 	isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
    $admin_id = $_SESSION['admin_id'];

    $no_accept = (isset($_REQUEST['tag_no_acc']) ? $_REQUEST['tag_no_acc'] : '0');

    // Gather User Information
    $sql = "SELECT member_id FROM team WHERE yearmonth = '$yearmonth' AND member_login_id = '$admin_id' ";
    $user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    $user_row = mysqli_fetch_array($user_query);
    $reviewer_id = $user_row['member_id'];

    // Update Reviewed Flag
    $sql  = "UPDATE pic SET reviewed = '1', reviewer_id = '$reviewer_id' ";
    if (isset($_REQUEST['tag_no_acc']))
        $sql .= "       , no_accept = '$no_accept' ";
    $sql .= " WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
    mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	echo json_encode(array("success" => true, "msg" => ""));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>

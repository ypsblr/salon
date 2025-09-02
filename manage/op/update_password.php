<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
	isset($_REQUEST['member_login_id']) && isset($_REQUEST['member_password']) &&
	isset($_REQUEST['member_password_new']) && isset($_REQUEST['member_password_confirm']) &&
   	$_SESSION['admin_id'] == $_REQUEST['member_login_id'] ) {

	$yearmonth = $_SESSION['admin_yearmonth'];
	$member_login_id = $_REQUEST['member_login_id'];
	$member_password = $_REQUEST['member_password'];
	$member_password_new = $_REQUEST['member_password_new'];
	$member_password_confirm = $_REQUEST['member_password_confirm'];

	if ($member_password_new != $member_password_confirm) {
		$_SESSION['err_msg'] = "New password and password confirmation are not same!";
	}
	else {
		$sql  = "UPDATE team ";
		$sql .= "   SET member_password = '$member_password_new' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND member_login_id = '$member_login_id' ";
		$sql .= "   AND member_password = '$member_password' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) == 1) {
			$_SESSION['success_msg'] = "New Password has been updated successfully!";
		}
		else {
			$_SESSION['err_msg'] = "Unable to update New Password. Check correctness of Current Password!";
		}
	}
}
else {
	$_SESSION['err_msg'] = "Sorry !! Invalid Parameters!";
}

header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
?>

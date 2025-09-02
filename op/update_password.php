<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("../inc/lib.php");

 if ( isset($_POST['update_password']) && isset($_SESSION['USER_ID']) ) {
	$profile_id = $_SESSION['USER_ID'];

	$opassword= $_POST['opassword'];
	$npassword= $_POST['npassword'];

	$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id' AND password = '$opassword' ";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	if ($count > 0){
		$sql = "UPDATE profile SET password = '$npassword' WHERE profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$_SESSION['success_msg'] = "Password has been updated successfully!";
	}
	else {
		$_SESSION['err_msg'] = "Unable to change the password !! Your Old password doesn't match!";
	}
}
header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='../user_panel.php'</script>");
?>

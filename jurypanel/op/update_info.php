<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

$session_id = $_SESSION['jury_id'];
$sql = "SELECT * FROM user where user_id = '$session_id'";
$user_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$user_row = mysqli_fetch_array($user_query);
$jury_login = $user_row['login'];
$jury_name = $user_row['name'];
$jury_pic = $user_row['picfile'];
$user_type = $user_row['type'];

if (isset($_POST['update_profile'])) {
	$name = strtoupper($_POST['name']);
	$name = str_replace("'", " ", $name);
	$npassword = strtoupper($_POST['npassword']);
	$opassword= strtoupper($_POST['opassword']);

	$sql = "SELECT * FROM user WHERE user_id='$session_id' AND password = '$opassword'";
	$qchk = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysql_num_rows($qchk);
	if ($count > 0){
		$sql = "UPDATE user SET name = '$name', password = '$npassword' WHERE user_id = '$session_id'";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$_SESSION['success_msg'] = "Personal Profile has been updated successfully!";
	}
	else {
		$_SESSION['err_msg'] = "Sorry !! your password doesn't match!";
	}
}
else
	$_SESSION['err_msg'] = "Sorry !! Invalid Parameters!";

header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
?>

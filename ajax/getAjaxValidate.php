<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
if (isset($_SESSION['USER_ID']))
	$uid = $_SESSION['USER_ID'];

include_once("../inc/connect.php");
include_once("ajax_lib.php");

function x_sql_error($sql, $errmsg, $file, $line) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	echo json_encode(false);
	die();
}

if(isset($_POST["captcha_code"])) {
	if((empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code'])== 0))
	{
		echo json_encode(true);
	}
	else{
		echo json_encode(false);
	}
}

if(isset($_REQUEST["email"])) {
	$email=  trim($_REQUEST['email']);
	$sql = "SELECT * FROM profile WHERE email ='$email' ";
	if (isset($uid))
		$sql .= " AND profile_id != '$uid'";	// In edit mode check for duplicates
	$qchk = mysqli_query($DBCON, $sql) or x_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if(mysqli_num_rows($qchk) > 0)
		echo json_encode(false);	// already exists - not a valid email
	else {
		// Check blacklist
		$sql = "SELECT * FROM blacklist WHERE email = '$email' AND expiry_date >= '" . date("Y-m-d") . "' ";
		$qchk = mysqli_query($DBCON, $sql) or x_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if(mysqli_num_rows($qchk) > 0)
			echo json_encode(false);	// in banned list - not a valid email
		else
			echo json_encode(true);
	}
}

if(isset($_POST["opassword"]) && isset($_SESSION['USER_ID'])) {
	$entry_id = trim($_SESSION['USER_ID']);
	$opassword= trim($_POST['opassword']);
	$sql = "SELECT * FROM profile WHERE password='$opassword' AND profile_id = '$entry_id'";
	$qchk = mysqli_query($DBCON, $sql) or x_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	if($count)
		echo json_encode(true);
	else
		echo json_encode(false);
}

if(isset($_POST["entry_id"])) {
	$entry_id= str_replace( "'", " ", trim($_POST['entry_id']) );
	$sql = "SELECT * FROM profile WHERE profile_id='$entry_id'";
	$qchk = mysqli_query($DBCON, $sql) or x_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$count = mysqli_num_rows($qchk);
	if($count)
		echo json_encode(true);
	else
		echo json_encode(false);
}

?>

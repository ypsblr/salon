<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");

// CryptoJs
use Nullix\CryptoJsAes\CryptoJsAes;
include("../inc/CryptoJsAes.php");

function clean_session() {
	if (isset($_SESSION['USER_ID'])) unset($_SESSION['USER_ID']);
	if (isset($_SESSION['LOGIN_ID'])) unset($_SESSION['LOGIN_ID']);
	if (isset($_SESSION['USER_NAME'])) unset($_SESSION['USER_NAME']);
	if (isset($_SESSION['USER_AVATAR'])) unset($_SESSION['USER_AVATAR']);
}

if ( empty($_REQUEST['ypsd']) ) {
	clean_session();
	die("Sorry ! Cannot !");
}
// Decrypt the inputs
$param = CryptoJsAes::decrypt($_REQUEST['ypsd'], "UN7U/yzk&%6GQwAA");

if ($param['admin'] != "sm" || $param['auth'] != "1.Iamsmtheadmin") {
	clean_session();
	die("Sorry ! Can do better !");
}

$sql = "SELECT * FROM profile WHERE profile_id = '" . $param['id'] . "' ";
$query = mysqli_query($DBCON, $sql) or die("Hmmm ! Something is not right !");
if (mysqli_num_rows($query) != 1)
	die("Hmmm ! I don't get it !");
$profile_row = mysqli_fetch_array($query);
$_SESSION['USER_ID'] = $profile_row['profile_id'];
$_SESSION['LOGIN_ID'] = $profile_row['email'];
$_SESSION['USER_NAME'] = $profile_row['salutation'] . " " . $profile_row['first_name'] . " " . $profile_row['last_name'];
$_SESSION['USER_AVATAR'] = $profile_row['avatar'];

header("Location: /index.php");
?>

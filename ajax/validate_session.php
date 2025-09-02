<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "")
	echo "ACTIVE";
else {
	//$_SESSION['err_msg'] = "Inactive for too long. Login again!";
	echo "DEAD";
}
?>

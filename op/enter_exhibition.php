<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("../inc/lib.php");

// sets the active section data so that user will land under the same section
if (isset($_REQUEST['enter_exhibition']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['visitor_name']) && isset($_REQUEST['visitor_email']) &&
	isset($_REQUEST['visitor_phone']) && isset($_REQUEST['visitor_whatsapp'])) {

    // Verify Captcha Confirmation
    if ( empty($_REQUEST['captcha_method']) ){
        handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }
    switch($_REQUEST['captcha_method']) {
        case "php" : {
            // Validate Captcha Code with Session Variable
            if ( empty($_SESSION['captcha_code']) || empty($_REQUEST['captcha_code']) || $_SESSION['captcha_code'] != $_REQUEST['captcha_code'] )
                handle_error("Authentication Failed. Check Validation Code !", __FILE__, __LINE__);
            break;
        }
        case "google" : {
            // Verify Google reCaptcha code for spam protection
            if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
                if ( ! (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != "" && verify_recaptcha($_POST['g-recaptcha-response']) == "") ) {
                    handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
                }
            }
            break;
        }
        default : handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$visitor_id = $_REQUEST['visitor_id'];
	$visitor_name = mysqli_real_escape_string($DBCON, $_REQUEST['visitor_name']);
	$visitor_email = mysqli_real_escape_string($DBCON, $_REQUEST['visitor_email']);
	$visitor_phone = $_REQUEST['visitor_phone'];
	$visitor_whatsapp = $_REQUEST['visitor_whatsapp'];

	if ($visitor_id == 0) {
		$sql  = "INSERT INTO visitor_book (yearmonth, visitor_id, profile_id, visitor_name, visitor_email, visitor_phone, visitor_whatsapp, visit_log, times_visited) ";
		$sql .= "SELECT '$yearmonth', IFNULL(MAX(visitor_id), 0) + 1, '$profile_id', '$visitor_name', '$visitor_email', '$visitor_phone', '$visitor_whatsapp', ";
		$sql .= "       CURRENT_TIMESTAMP, 1 ";
		$sql .= "  FROM visitor_book WHERE yearmonth = '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Set Session visitor id
		$sql = "SELECT MAX(visitor_id) AS visitor_id FROM visitor_book WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$res = mysqli_fetch_array($query);
		$_SESSION['VISITOR_ID'] = $res['visitor_id'];
	}
	else {
		$sql  = "UPDATE visitor_book ";
		$sql .= "   SET visitor_name = '$visitor_name' ";
		$sql .= "     , visitor_email = '$visitor_email' ";
		$sql .= "     , visitor_phone = '$visitor_phone' ";
		$sql .= "     , visitor_whatsapp = '$visitor_whatsapp' ";
		$sql .= "     , visit_log = CONCAT_WS('|', visit_log, CURRENT_TIMESTAMP) ";
		$sql .= "     , times_visited = times_visited + 1 ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND visitor_id = '$visitor_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$_SESSION['VISITOR_ID'] = $visitor_id;
	}

	// Launch the Exhibition in full screen
//	$exhibition = "/salons/$yearmonth/exhibition/exhibition.html";
	$exhibition = "/exhibition_hall.php?contest=" . $yearmonth;
	header('Location: ' . $exhibition);
	printf("<script>location.href='" . $exhibition . "'</script>");
}
else if (isset($_REQUEST['enter_exhibition']) && isset($_REQUEST['leaving']) && isset($_REQUEST['visitor_id']) && isset($_REQUEST['visitor_comments']) ) {

	// Verify Captcha Confirmation
    if ( empty($_REQUEST['captcha_method']) ){
        handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }
    switch($_REQUEST['captcha_method']) {
        case "php" : {
            // Validate Captcha Code with Session Variable
            if ( empty($_SESSION['captcha_code']) || empty($_REQUEST['captcha_code']) || $_SESSION['captcha_code'] != $_REQUEST['captcha_code'] )
                handle_error("Authentication Failed. Check Validation Code !", __FILE__, __LINE__);
            break;
        }
        case "google" : {
            // Verify Google reCaptcha code for spam protection
            if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
                if ( ! (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != "" && verify_recaptcha($_POST['g-recaptcha-response']) == "") ) {
                    handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
                }
            }
            break;
        }
        default : handle_error("Invalid Authentication !", __FILE__, __LINE__);
    }

	$yearmonth = $_REQUEST['yearmonth'];
	$visitor_id = $_REQUEST['visitor_id'];
	$visitor_comments = mysqli_real_escape_string($DBCON, $_REQUEST['visitor_comments']);
	$sql = "UPDATE visitor_book SET visitor_comments = '$visitor_comments' WHERE yearmonth = '$yearmonth' AND visitor_id = '$visitor_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	header('Location: /index.php');
	printf("<script>location.href='/index.php'</script>");
}
else {
	$_SESSION['err_msg'] = "Unable to complete. Invalid request !";
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
}
?>

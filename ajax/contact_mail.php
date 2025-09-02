<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include_once("../inc/connect.php");
include_once("ajax_lib.php");


// Spam Text Filter
// Ignore requests from identified email domains/emails
function spam_match($text, $list) {
	foreach ($list as $match)
		if (stristr($text, trim($match)) != FALSE)
			return true;
	return false;
}

// Filter out poorly formed names
function name_ok ($name) {
	if (strchr($name, " ") == false)	// Need full name = FirstName <space> LastName
		return false;

	for ($i = 0; $i < strlen($name); $i ++) {
		$chr = substr($name, $i, 1);
		if (! ($chr == " " || ($chr >= "A" && $chr <= "Z") || ($chr >= 'a' && $chr <= 'z')) )
			return false;
	}

	return true;
}


// $sql = "SELECT * FROM email_template WHERE template_code = 'contact_us' ";
// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// $res = mysqli_fetch_array($query);
// $email_body = $res['email_body_blob'];

$sql = "SELECT * FROM spam_filter WHERE spam_type = 'email' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$spam_email_list = array();
while ($res = mysqli_fetch_array($query))
	$spam_email_list = array_merge($spam_email_list, explode(',', $res['spam_list']));

$sql = "SELECT * FROM spam_filter WHERE spam_type = 'text' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$spam_text_list = array();
while ($res = mysqli_fetch_array($query))
	$spam_text_list = array_merge($spam_text_list, explode(',', $res['spam_list']));


$resArray = array();

if(isset($_POST['btn_send'])) {
	// Verify Google reCaptcha code for spam protection
	if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
		if ( ! (isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != "" && verify_recaptcha($_POST['g-recaptcha-response']) == "") ) {
			handle_error("Click on I am not Robot before submitting !", __FILE__, __LINE__);
		}
	}

	if (isset($_POST['email']) && $_POST['email'] == "wasteoftime@nosuchmail.com") {		//SPAM check
		$full_name = $_POST['full_name'];
		$email = $_POST['your_email'];
		$phone = $_POST['phone'];
		$message = $_POST['message'];
		$contest_yearmonth = $_POST['contest'];

		$sql = "SELECT contest_name FROM contest WHERE yearmonth = '$contest_yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$res = mysqli_fetch_array($query);
		$contest_name = $res['contest_name'];

		if (name_ok($full_name) && spam_match($email, $spam_email_list) == false && spam_match($message, $spam_text_list) == false) {

			$subject= "User Query related to " . $contest_name;

			if (file_exists("../salons/$contest_yearmonth/blob/partner_data.php")) {
				include("../salons/$contest_yearmonth/blob/partner_data.php");
				$partner_footer = partner_email_footer($contest_yearmonth);
			}
			else
				$partner_footer = "";

			$replacement = array($contest_yearmonth, $contest_name, $full_name, $phone, $message, http_method() . $_SERVER['HTTP_HOST'], $partner_footer );
			$token = array('{{CONTEST_ID}}', '{{SALON}}', '{{NAME}}', '{{PHONE}}', '{{QUERY}}', '{{URL}}', '{{PARTNERS}}');

			if (send_salon_email($email, $subject, 'contact_us', $token, $replacement)) {
				$resArray['success'] = TRUE;
				$resArray['msg'] = $_SESSION['success_msg'] = "Your query has been forwarded to the YPS Salon/Contest Committee !";
				echo json_encode($resArray);
				die;
			}
			else
				handle_error("Email send failed ! Check correctness of details provided !", __FILE__, __LINE__);
		}
		else
			handle_error("Unable to send email with the details provided", __FILE__, __LINE__);
	}
	else
		handle_error("Unable to send email to the email ID provided !", __FILE__, __LINE__);
}
else
	handle_error("Message not sent due to authentication failure !", __FILE__, __LINE__);
?>

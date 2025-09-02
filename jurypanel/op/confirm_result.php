<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// Global Parameters specific to this Salon
define("SALON_HAS_CASH_AWARDS", false);
define("MAX_LEVEL_FOR_FULL_UPLOAD", 9);
if (preg_match("/localhost/i", $_SERVER['SERVER_NAME']))
	define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);
else
	define("SALON_ROOT", http_method() . "salon.ypsbengaluru.in");


function replace_values ($str, $pairs) {
	foreach ($pairs as $key => $value)
		$str = str_replace("[" . $key . "]", $value, $str);
	return $str;
}

function load_message ($file, $pairs) {
	$message = file_get_contents($file);
	if ($message == "")
		return "";

	$message = replace_values($message, $pairs);

	return $message;
}

function send_mail($to, $subject, $message, $cc_to = "") {
	global $counterr;
	global $mail_failed;
	global $mail_suffix;

	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html" . "\r\n";
	$headers .= "From: <salon@ypsbengaluru.in>" . "\r\n";

	// $headers = "MIME-Version: 1.0" . "\r\n";
	// $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	// $headers .= "From: <salon@ypsbengaluru.in>" . "\r\n";
	$headers .= "Cc: <salon@ypsbengaluru.in>" . ($cc_to == "" ? "" : "," . $cc_to) . "\r\n";

	if (strpos($_SERVER['HTTP_HOST'], "localhost") == false) {
		if( ! mail($to,$subject,wordwrap($message),$headers)) {
			$mail_failed .= $to . "' ";
			$counterr ++;
		}
	}
	else {
		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $message);
	}
}

function print_results($award) {

	global $contest;
	global $jury_yearmonth;
	global $section;
	global $jury_id;
	global $jury;
	global $recognition_data;
	global $DBCON;

	$row_template = <<<TEMPLATE
<tr>
	<td width="25%"><span class="award-name">[award-name]</span></td>
	<td width="25%"><img src='[thumbnail]' style='width:120px;height:auto' ></td>
	<td>
		<p class="pic-title">[title]</p>
		<p class="honors">by</p>
		<p class="profile-name">[winner-name]</p>
		<p class="honors">[winner-honors]</p>
		<p class="address">[winner-city], [winner-state], [winner-country]</p>
	</td>
</tr>
TEMPLATE;

	$award_id = $award['award_id'];

	$sql  = "SELECT pic.section, pic.title, pic.picfile, pic.location, ";
	$sql .= "       profile.profile_name, profile.city, profile.state, country.country_name, profile.honors, profile.email, profile.phone, profile.avatar, ";
	$sql .= "       pic_result.ranking ";
	$sql .= "  FROM pic_result, pic, profile, country ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic_result.award_id = '$award_id' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= " ORDER BY pic_result.ranking, pic_result.profile_id, pic_result.pic_id ";

	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$mail_text = "<table class='table-data'><tbody>";

	while ($res = mysqli_fetch_array($qry)) {
		if ($res['avatar'] != "" && is_file("../res/avatar/" . $res['avatar']))
			$avatar = SALON_ROOT . "/res/avatar/" . $res['avatar'];
		else
			$avatar = "";

		$values = array (
					"award-name" => $award['award_name'],
					"thumbnail" => SALON_ROOT . "/salons/$jury_yearmonth/upload/" . $section . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"avatar" => $avatar,
					"winner-name" => $res['profile_name'],
					"winner-honors" => $res['honors'],
					"winner-city" => $res['city'],
					"winner-state" => $res['state'],
					"winner-country" => $res['country_name']);
		$mail_text .= replace_values($row_template, $values);

	}
	$mail_text .= "</tbody></table>";
	return $mail_text;
}

// Print Section Level Picture Awards
function print_section_awards() {

	global $contest;
	global $jury_yearmonth;
	global $section;
	global $jury_id;
	global $jury;
	global $recognition_data;
	global $DBCON;

	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND section = '$section' ";
	$sql .= "   AND level < 99 ";
	$sql .= " ORDER BY award_group, level, sequence";
	$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$mail_text = "";
	if (mysqli_num_rows($qaw) > 0) {
		$mail_text .= "<h2>Awards for " . $section . " Section</h2>";
		while ($raw = mysqli_fetch_array($qaw))
			$mail_text .= print_results($raw);
	}

	return $mail_text;
}

// Print Contest Level Picture Awards
function print_contest_awards() {

	global $contest;
	global $jury_yearmonth;
	global $section;
	global $jury_id;
	global $jury;
	global $recognition_data;
	global $DBCON;

	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND section = 'CONTEST' ";
	$sql .= " ORDER BY award_group, level, sequence";
	$qaw = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$mail_text = "";
	if (mysqli_num_rows($qaw) > 0) {
		$mail_text .= "<h2>Special Picture Awards</h2>";
		while ($raw = mysqli_fetch_array($qaw))
			$mail_text .= print_results($raw);
	}

	return $mail_text;
}

function pdate($datestr) {
	return date("F j, Y", strtotime($datestr));
}

function send_confirmation_mail() {

	global $contest;
	global $committee;
	global $jury_yearmonth;
	global $section;
	global $jury_id;
	global $jury;
	global $recognition_data;
	global $DBCON;

	$values = array(
				"server-address" => SALON_ROOT,
				"contest-name" => $contest['contest_name'],
				"jury-name" => $jury['user_name'],
				"section-name" => $section,
				// "contest-results" => print_contest_awards(),
				"section-results" => print_section_awards(),
				"salon-chairman" => $committee['Chairman']['member_name'],
				"recognition-data" => $recognition_data);

	$mail_body = load_message("confirm_result_mail.htm", $values);

	$subject = "Confidential : " . $contest['contest_name'] . " - Tentative Results for " . $section;

	$cc = 	(isset($committee['Chairman']) ? $committee['Chairman']['email'] . "," : "") .
			(isset($committee['Secretary']) ? $committee['Secretary']['email'] . "," : "") .
			(isset($committee['Webmaster']) ? $committee['Webmaster']['email'] . "," : "") .
			"sastry.vikas@gmail.com,mettursmurali@gmail.com";

	send_mail($jury['email'], $subject, $mail_body, $cc);

}

// Main Section
if (isset($_SESSION['jury_yearmonth']) && isset($_SESSION['jury_id']) &&
	isset($_REQUEST['confirm_result']) && $_REQUEST['confirm_result'] == '1' && isset($_REQUEST['send_confirmation_mail']) &&
	(! empty($_REQUEST['yearmonth'])) && (! empty($_REQUEST['section'])) && (! empty($_REQUEST['jury_id']))  ) {

	$jury_yearmonth = $_REQUEST['yearmonth'];
	$jury_id = $_REQUEST['jury_id'];
	$section = $_REQUEST['section'];

	// Global Data
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Jury Data
	$sql = "SELECT * FROM user WHERE user_id = '$jury_id'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$jury = mysqli_fetch_array($query);

	// Chairman & Secretary
	$sql = "SELECT * FROM team WHERE yearmonth = '$jury_yearmonth' AND role IN ('Chairman', 'Secretary') ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$committee = [];
	while ($res = mysqli_fetch_array($query))
		$committee[$res['role']] = $res;

	// Assemble Recognition Data
	$sql = "SELECT * FROM recognition WHERE yearmonth = '$jury_yearmonth' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$recognition_data  = "<h3>This Salon is recognized by:</h3>";
	$recognition_data .= "<table class='table-data' width='100%'><tbody>";
	$target_dir = SALON_ROOT . "/salons/$jury_yearmonth/img/recognition/";
	while ($res = mysqli_fetch_array($qry)) {
		$recognition_data .= "<tr>";
		$recognition_data .= "<td><img src='" . $target_dir . $res['logo'] . "' style='width:80px;' ></td>";
		$recognition_data .= "<td><h4>" . $res['organization_name'] . "</h4><p><a href='" . $res['website'] . "'>" . $res['website'] . "</a></p></td>";
		$recognition_data .= "<td><h3>" . $res['recognition_id'] . "</h3></td>";
		$recognition_data .= "</tr>";
	}
	$recognition_data .= "</tbody></table>";

	// Send Email
	send_confirmation_mail();

	$_SESSION['success_msg'] = "A confirmation email for the results has been sent.";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid request ";

	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>

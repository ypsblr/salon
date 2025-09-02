<?php

function encode_string_array ($stringArray) {
    $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),2))), '+/=', '_,');

    return $s;
}

function decode_string_array ($stringArray) {
    $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '_,', '+/=')))));
    return $s;
}

function orderSuffix($i) {
	$str = "$i";
	$t = $i > 9 ? substr($str,-2,1) : 0;
	$u = substr($str,-1);
	if ($t==1)
		return $str . 'th';
	else {
		switch ($u) {
			case 1: return $str . '<sup>st</sup>';
			case 2: return $str . '<sup>nd</sup>';
			case 3: return $str . '<sup>rd</sup>';
			default: return $str . '<sup>th</sup>';
	   }
	}
}

// Log error and exit
function log_error($errmsg, $phpfile, $phpline) {
    $log_file = DOCUMENT_ROOT . "/logs/errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
}

function return_error($errmsg, $phpfile, $phpline) {

    log_error($errmsg, $phpfile, $phpline);

	die();
}

// Write a variable dump to file
function debug_dump($name, $value, $phpfile, $phpline) {
    $log_file = DOCUMENT_ROOT . "/logs/debug.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Dump of '$name' requested in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, print_r($value, true) . chr(13) . chr(10), FILE_APPEND);
}

// LOG SQL Errors for debugging
// Usage: $query = mysqli_query($con, $sql) or sql_die($sql, mysqli_error($con), __FILE__, __LINE__);
function log_sql_error($sql, $errmsg, $phpfile, $phpline) {
    $log_file = DOCUMENT_ROOT . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
}

function sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	die();
}


// Sends mail out when run from server
// Otherwise stores in htm file
function send_mail($to, $subject, $message, $cc_to = "") {
	static $mail_suffix = 0;

	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html" . "\r\n";
	$headers .= "From: " . SALON_EMAIL . "\r\n";

	if (SERVER_NAME == 'localhost') {
		if (! is_dir("mails"))
			mkdir("mails");
		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $message);
		return true;
	}
    elseif (SERVER_NAME == "salontest" ) {
        if (! is_dir("mails"))
			mkdir("mails");
		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $message);
		return true;
    }
	else {
        if ($cc_to != "nocc")
    	   $headers .= "Cc: " . SALON_EMAIL . ($cc_to == "" ? "" : "," . $cc_to) . "\r\n";
		return mail($to, $subject, wordwrap($message), $headers, " -fsalon@ypsbengaluru.in");
	}
}

// Return Exif Data stored in pic table as string
function exif_str($exif_json) {
    if ($exif_json == "")
		return "NO EXIF";

    try {
        $exif = json_decode($exif_json, true);
        $exif_strings = [];
        if (! empty($exif["camera"]))
            $exif_strings[] = $exif["camera"];
        if (! empty($exif["iso"]))
            $exif_strings[] = "ISO " . $exif["iso"];
        if (! empty($exif["program"]))
            $exif_strings[] = $exif["program"];
        if (! empty($exif["aperture"]))
            $exif_strings[] = $exif["aperture"];
        if (! empty($exif["speed"]))
            $exif_strings[] = $exif["speed"];

        if (sizeof($exif_strings) > 0)
            return implode(", ", $exif_strings);
        else
            return "";
    }
    catch (Exception $e) {
        // Not a proper exif
        return "";
    }
}

// Return date in string format at a specified timezone
function strtotime_tz($time_str, $tz) {
	$cur_tz = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret_time = strtotime($time_str);
	date_default_timezone_set($cur_tz);

	return $ret_time;
}

// Return formated date in different timezone
function date_tz($time_str, $tz) {
	$cur_tz = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret_time = date("Y-m-d", strtotime($time_str));
	date_default_timezone_set($cur_tz);

	return $ret_time;
}

//
// Function to format name
// Split into words and if the word has more than 3 letters or has a vowel after the first letter then capitalize
//
function format_name($name) {
    $parts = preg_split("/\s+/", $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    for ($i = 0; $i < sizeof($parts); ++ $i) {
        if (strlen($parts[$i]) == 1 || strlen($parts[$i]) > 3)
            $parts[$i] = ucfirst(strtolower($parts[$i]));
        else {
            // If the name comtains vowel after the first letter, it could be a proper name
            if (preg_match("/[aeiouy]/i", $parts[$i]))
                $parts[$i] = ucfirst(strtolower($parts[$i]));
            else
                $parts[$i] = strtoupper($parts[$i]);
        }
    }
    return implode(" ", $parts);
}

function format_title($title) {
    return ucwords(strtolower($title));
}

function format_place($place) {
    return ucwords(strtolower($place));
}

// Fetches rejection text for display
function rejection_text($notifications) {
	static $rejection_reasons = [];
	global $DBCON;

	if (sizeof($rejection_reasons) == 0) {
		// Gather List of Rejection Reasons
		// Get Notifications List
		$sql  = "SELECT template_code, template_name ";
		$sql .= "  FROM email_template ";
		$sql .= " WHERE template_type = 'user_notification' ";
		$sql .= "   AND will_cause_rejection = '1' ";
		$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$rejection_reasons = array();
		while ($rntf = mysqli_fetch_array($qntf))
			$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];
	}

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

function jury_notifications($contest_yearmonth, $profile_id, $pic_id, $contest_archived = false) {
	global $DBCON;

	$sql  = "SELECT yearmonth, MIN(rating) AS min_score, GROUP_CONCAT(DISTINCT tags SEPARATOR '|') AS jury_notifications ";
	if ($contest_archived)
		$sql .= "  FROM ar_rating rating ";
	else
		$sql .= "  FROM rating ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$sql .= "   AND pic_id = '$pic_id' ";
	$sql .= " GROUP BY yearmonth ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query)) {
		if ($row['min_score'] == '1') {
			$notifications = explode("|", $row['jury_notifications']);
			$notification_list = [];
			foreach ($notifications as $notification) {
				if ($notification != "" && (! isset($notification_list[$notification])))
					$notification_list[$notification] = $notification;
			}
			return implode(", ", $notification_list);
		}
	}
	return "";
}

function replace_values ($str, $pairs) {
	foreach ($pairs as $key => $value)
		$str = str_replace("[" . $key . "]", $value, $str);
	return $str;
}

define("MAX_TOKEN_LENGTH", 128);
function replace_blocks($message, $pairs) {
	$block_opening = "[if_begin:";
	$block_closing = "[if_end:";

	$pos = 0;
	while ($pos = strpos($message, $block_opening, $pos)) {
		// Find the Begin Token
		$opening_token_start = $pos;
		$pos = strpos($message, "]", $pos + strlen($block_opening));
		if ( (! $pos) || ($pos - $opening_token_start) > MAX_TOKEN_LENGTH)
			break;

		$opening_token_end = $pos + strlen("]");
		$token_name = substr($message, $opening_token_start + strlen($block_opening), $opening_token_end - $opening_token_start - strlen($block_opening) - strlen("]"));

		// Find the end token
		$pos = strpos($message, $block_closing . $token_name . "]", $pos);
		if (! $pos)
			break;

		$closing_token_start = $pos;
		$pos += strlen($block_closing . $token_name . "]");
		$closing_token_end = $pos;

		// Substitute
		if ( isset($pairs[$token_name])) {
			// Start the next scan from the position of the message where the block was found, otherwise the scan will skip some blocks
			// This also enables nested blocks
			$pos = $opening_token_start;
			if ($pairs[$token_name]) {
				// Include the block without the begin and end tokens
				$message = str_replace($block_opening . $token_name . "]", "", $message);
				$message = str_replace($block_closing . $token_name . "]", "", $message);
				// $messsage = replace_values($message, array($block_opening . $token_name . "]" => "", $block_closing . $token_name . "]" => ""));
			}
			else {
				// remove the block by copying what was before the block and what was after
				$message = substr($message, 0, $opening_token_start) . substr($message, $closing_token_end);
			}
		}
	}

	return $message;
}

function load_message ($file, $pairs) {
	$message = file_get_contents($file);
	if ($message == "")
		return "";

	// Remove conditional blocks before replacing values
	$message = replace_blocks($message, $pairs);

	$message = replace_values($message, $pairs);
	// Process a second time to replace values in data passed
	$message = replace_values($message, $pairs);

	return $message;
}

// Formats date in "Month date, Year" format
function print_date($datestr) {
	if ($datestr == NULL)
		return "<Not Set>";
	else
		return date("D, jS M Y", strtotime($datestr));
}

function http_method() {
	if (SERVER_NAME == "salontest" || SERVER_NAME == "salon")
		return "https://";
	else
		return "http://";
}

function sort_partner_by_sequence($a, $b){
	if ($a['sequence'] == $b['sequence'])
		return 0;

	if ($a['sequence'] < $b['sequence'])
	 	return -1;
	else
	 	return 1;
}

function get_salon_values($yearmonth) {
	global $DBCON;

	// Fetch Contest Details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon = mysqli_fetch_array($query);

	// Get details of the Team
	$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' ORDER BY level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contestTeam = array();
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$contestTeam[$row['role']] = $row;
	}

	// Get final upload dates
	$sql  = "SELECT section, stub, section_type, cut_off_score, submission_last_date ";
	$sql .= "  FROM section ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= " ORDER BY section_sequence ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$digital_submission_last_date = NULL;
	$print_submission_last_date = NULL;
	$has_print_sections = false;
	$cut_off_table  = "<table class='table' style='margin-left: 30px;'>";
	$cut_off_table .= "<tr><th>Section</th><th>Cut-off Score</th></tr>";
	while ($row = mysqli_fetch_array($query)) {
		$cut_off_table .= "<tr><td>" . $row['section'] . "</td><td>" . $row['cut_off_score'] . "</td></tr>";
		if ($row['section_type'] == "D" && (is_null($digital_submission_last_date) || $row['submission_last_date'] > $digital_submission_last_date) ) {
			$digital_submission_last_date = $row['submission_last_date'];
		}
		if ($row['section_type'] == "P" && (is_null($print_submission_last_date) || $row['submission_last_date'] > $print_submission_last_date) ) {
			$print_submission_last_date = $row['submission_last_date'];
			$has_print_sections = true ;
		}
	}
	$cut_off_table .= "</table>";

	// Exhibition Details
	$exhibition = [];
	$dignitory_roles = [];
	$dignitory_names = [];
	$dignitory_positions = [];
	$dignitory_avatars = [];
	$dignitory_blobs = [];
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$exhibition = mysqli_fetch_array($query);
		$dignitory_roles = explode("|", $exhibition['dignitory_roles']);
		$dignitory_names = explode("|", $exhibition['dignitory_names']);
		$dignitory_positions = explode("|", $exhibition['dignitory_positions']);
		$dignitory_avatars = explode("|", $exhibition['dignitory_avatars']);
		$dignitory_blobs = explode("|", $exhibition['dignitory_profile_blobs']);
	}

	// Assemble Recognition Data
	$sql = "SELECT * FROM recognition WHERE yearmonth = '$yearmonth' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$recognition_data  = "<h2>This Salon is recognized by:</h2>";
	$recognition_data .= "<table class='table-data' width='100%'><tbody>";

	$target_dir = http_method() . SERVER_ADDRESS . "/salons/$yearmonth/img/recognition/";
	while ($res = mysqli_fetch_array($qry)) {
		$recognition_data .= "<tr>";
		$recognition_data .= "<td><img src='" . $target_dir . $res['logo'] . "' style='width:80px;' ></td>";
		$recognition_data .= "<td><h4>" . $res['organization_name'] . "</h4><p><a href='" . $res['website'] . "'>" . $res['website'] . "</a></p></td>";
		$recognition_data .= "<td><h3>" . $res['recognition_id'] . "</h3></td>";
		$recognition_data .= "</tr>";
	}
	$recognition_data .= "</tbody></table>";

	// Assemble Partner Data
	$partner_data = "";
	// New Method with partner data in JSON file
	if (file_exists("../../salons/$yearmonth/blob/partners.json")) {
		$partner_json = json_decode(file_get_contents("../../salons/$yearmonth/blob/partners.json"), true);
		usort($partner_json['partners'], "sort_partner_by_sequence");
		if (sizeof($partner_json['partners']) > 0) {
			$partner_data  = "<p><span style='font-size: 20px; font-weight: bold'>Salon supported by</span></p>";
			$partner_data .= "<table width='100%' cellpadding='8' style='border-bottom: 1px solid #d0d0d0; border-top: 1px solid #d0d0d0; border-collapse: collapse;'>";
			foreach ($partner_json['partners'] as $partner) {
				// $partner = $partner_json['partners'][$idx];
				$logo = http_method() . SERVER_ADDRESS . "/salons/$yearmonth/img/sponsor/" . rawurlencode($partner['logo']);
				$partner_data .= "<tr>";
				$partner_data .= "<td width='120' style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse;'>";
				if (! empty($partner['logo']) && file_exists("../../salons/$yearmonth/img/sponsor/" . $partner['logo'])) {
					if ( ! empty($partner['website']))
						$partner_data .= "<a href='" . $partner['website'] . "'><img style='max-width:120px; max-height:120px;' src='" . $logo . "' ></a>";
					else
						$partner_data .= "<img style='max-width:120px; max-height:120px;' src='" . $logo . "' >";
				}
				$partner_data .= "</td>";
				$partner_data .= "<td style='border-bottom: 1px solid #d0d0d0; border-collapse: collapse; vertical-align:top;'>";
				$partner_data .= "<h4 style='margin: 2px 0px 4px 0px;'>" . $partner['name'] . "</h4>";
				if (!empty($partner['tagline']))
					$partner_data .= "<br><i>" . $partner['tagline'] . "</i>";
				if (!empty($partner['text']))
					$partner_data .= "<br><b>" . $partner['text'] . "</b>";
				if (!empty($partner['website']))
					$partner_data .= "<br><a href='" . $partner['website'] . "'>" . $partner['website'] . "</a>";
				if (!empty($partner['email']))
					$partner_data .= "<br><a href='mailto:" . $partner['email'] . "'>" . $partner['email'] . "</a>";
				if (!empty($partner['phone']))
					$partner_data .= "<br>Contact_No: " . $partner['phone'];
				$partner_data .= "</td>";
				$partner_data .= "</tr>";
			}
			$partner_data .= "</table>";
		}
	}
	// Support for old method
	else if (file_exists("../../salons/$yearmonth/blob/partner_data.php")) {
		include_once("../../salons/$yearmonth/blob/partner_data.php");
		if ( (! empty($partners)) && sizeof($partners) > 0) {
			$partner_data  = "<p><span style='font-size: 20px; font-weight: bold'>Salon supported by</span></p>";
			$partner_data .= partner_email_footer($yearmonth, $partners, http_method() . SERVER_ADDRESS);
		}
	}

	// Value Substitution Array used in merging blobs
	$contest_values = array(
						"yps-website" => "https://ypsbengaluru.com",
						"salon-website" => http_method() . SERVER_ADDRESS,
						"yearmonth" => $salon['yearmonth'],
						"is-salon-archived" => ($salon['archived'] == '1'),
						"salon-name" => $salon['contest_name'],
						"registration-last-date" => print_date($salon['registration_last_date']),
						"digital-submission-last-date" => print_date($digital_submission_last_date),
						"print-submission-last-date" => print_date($print_submission_last_date),
						"has-print-sections" => $has_print_sections,
						"submission-timezone-name" => $salon['submission_timezone'],
						"cut-off-table" => $cut_off_table,
						"max-pic-width" => $salon['max_width'],
						"max-pic-height" => $salon['max_height'],
						"max-pic-file-size-in-mb" => $salon['max_file_size_in_mb'],
						"judging-start-date" => print_date($salon['judging_start_date']),
						"judging-end-date" => print_date($salon['judging_end_date']),
						"result-date" => print_date($salon['results_date']),
						"update-end-date" => print_date($salon['update_end_date']),
						"is-judging-remote" => ($salon['judging_mode'] == "REMOTE"),
						"is-judging-venue" => ($salon['judging_mode'] == "VENUE"),
						"judging-venue" => $salon['judging_venue'],
						"judging-venue-address" => $salon['judging_venue_address'],
						"judging-venue-location-map" => $salon['judging_venue_location_map'],
						"exhibition-name" => $salon['exhibition_name'],
						"exhibition-start-date" => print_date($salon['exhibition_start_date']),
						"exhibition-end-date" => print_date($salon['exhibition_end_date']),
						"exhibition-venue" => $salon['exhibition_venue'],
						"exhibition-venue-address" => $salon['exhibition_venue_address'],
						"exhibition-venue-location-map" => $salon['exhibition_venue_location_map'],
						"exhibition-invitation-img" => isset($exhibition['invitation_img']) ? $exhibition['invitation_img'] : "",
						"exhibition-email-header-img" => isset($exhibition['email_header_img']) ? $exhibition['email_header_img'] : "",
						"exhibition-chair-role" => (isset($dignitory_roles[0]) ? $dignitory_roles[0] : ""),
						"exhibition-chair-name" => (isset($dignitory_names[0]) ? $dignitory_names[0] : ""),
						"exhibition-chair-position" => (isset($dignitory_positions[0]) ? $dignitory_positions[0] : ""),
						"exhibition-chair-avatar" => (isset($dignitory_avatars[0]) ? $dignitory_avatars[0] : ""),
						"exhibition-chair-blob" => (isset($dignitory_blobs[0]) ? $dignitory_blobs[0] : ""),
						"exhibition-guest-role" => (isset($dignitory_roles[1]) ? $dignitory_roles[1] : ""),
						"exhibition-guest-name" => (isset($dignitory_names[1]) ? $dignitory_names[1] : ""),
						"exhibition-guest-position" => (isset($dignitory_positions[1]) ? $dignitory_positions[1] : ""),
						"exhibition-guest-avatar" => (isset($dignitory_avatars[1]) ? $dignitory_avatars[1] : ""),
						"exhibition-guest-blob" => (isset($dignitory_blobs[1]) ? $dignitory_blobs[1] : ""),
						"exhibition-other-role" => (isset($dignitory_roles[2]) ? $dignitory_roles[2] : ""),
						"exhibition-other-name" => (isset($dignitory_names[2]) ? $dignitory_names[2] : ""),
						"exhibition-other-position" => (isset($dignitory_positions[2]) ? $dignitory_positions[2] : ""),
						"exhibition-other-avatar" => (isset($dignitory_avatars[2]) ? $dignitory_avatars[2] : ""),
						"exhibition-other-blob" => (isset($dignitory_blobs[2]) ? $dignitory_blobs[2] : ""),
						"catalog-release-date" => print_date($salon['catalog_release_date']),
						"catalog-file-download" => $salon['catalog_download'],
						"catalog-file-view" => $salon['catalog'],
						"chairman-role" => $contestTeam['Chairman']['role_name'],
						"salon-chairman" => $contestTeam['Chairman']['member_name'],
						"secretary-role" => $contestTeam['Secretary']['role_name'],
						"salon-secretary" => $contestTeam['Secretary']['member_name'],
						"recognition-data" => $recognition_data,
						"partner-data" => $partner_data,
						);

		return $contest_values;
}

function log_mail_error($queue_id, $profile_id, $error_action, $error_message) {
	global $DBCON;
	$skipped = ($error_action == "skipped" ? "1" : "0");
	$failed = ($error_action == "failed" ? "1" : "0");
	$sql  = "INSERT INTO mail_error (queue_id, profile_id, error, skipped, failed) ";
	$sql .= "VALUES ('$queue_id', '$profile_id', '$error_message', '$skipped', '$failed' ) ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}

// SPAM Check
function is_spam($mail_text) {
	$spamwords_urgency = [
		"access *(now)*", "act *(immediately|now!*)*", "action *(required)*", "apply *(here|now!*|online)*", "(become|being) *a* *member",
		"before *(it'*s)* *(too)* *late", "buy *(direct|now|today)*", "call *(free(\/now)*|now!*|me)*",
		"can +(we)+ +(have)+.*(minute)+.*(your)+.*(time.*)+", "cancel(lation)* +(now|required)+", "claim +now",
		"click *(below|here|(me)* +to +download|now|(this|the) +link|to +get|to +remove)*", "contact +us +immediately", "deal +ending +soon",
		"do +it +(today|now)", "don'*t +(delete|hesitate|waste +time)", "exclusive +deal", "expire(s* +today)*", "final +call",
		"for +(instant +access|only|you)", "friday +before", "get +(it +(away|now)|now|paid|started( +now)*)",  "great +offer", "hurry +up",
		"immediately", "info(rmation)* +you +requested", "instant", "limited +time", "new +customers +only", "now( +only)*", "offer +expires",
		"once +in +life *time", "only", "(order|purchase) +(now|today)", "please +read", "sign *up +free( +today)*",
		"(supplies|stock)( +are)* +limited", "take +action( +now)*", "this +won'*t +last", "time +limited", "today", "top +urgent",
		"trial", "urgent", "what +are +you +waiting +for\?*", "while +supplies +last", "you +are( +a)* +winner"
	];

	$spamwords_shady = [
		"(0|zero) +down", "all([ -]+(natural(\/new)*|new))*", "Allowance", "as +seen +on( +oprah)*", "at +no +cost", "auto +email +removal",
		"avoid( +bankruptcy)*", "beneficial +offer", "Beneficiary", "bill +1618", "brand +new +paper", "bulk +email", "buying +judge*ments",
		"cable +converter", "calling +creators", "can +you +help +us", "cancel +at +any +time", "(cannot|can't) +be +combined", "celebrity",
		"cell +phone +cancer +scam", "certified", "chance", "cheap( +meds)*", "cialis",
		"claims( +(not +to +be +selling +anything|to +in +accordance +with|to +be +legal)*)", "clearance", "collect( +child +support)*",
		"compare( +(now|online|rates*|prices*)*)", "compete +for +your +business", "Confidentiality", "congratulations",
		"consolidate( +your)* +debt( +and +credit)*", "copy +(accurately|dvds*)", "covid", "cures( +baldness)*", "diagnostics*",
		"diet", "dig +up +dirt +on +friends", "direct +(email|marketing)", "eliminat +debt", "explode( +your)* +business", "fast +viagara +delivery",
		"finance", "financial(ly)*( +(advice|independence|independent)*)*", "for +new +customers*( +only)*", "foreclosure",
		"free( +(access|money|gift|bonus|cell *phone|dvd|grant|information|installation|instant|iphone|laptop|leads|macbook|offer|priority +mail|sample|website))*[!]*",
		"get", "gift +(card|certificate|included)", "giv(ing|e)( +it)* +away", "gold", "great( +deal)*", "greetings +of + the+ day",
		"growth harmone", "guarantee(d +(deposit|income|payment))*", "have +you +been +turned +down", "hello", "hidden +(charges|costs|fees)",
		"high +score", "home +(based +business|mortgage)", "human( +growth +harmone)*", "if +only +it +were +that +easy",
		"important +(information|notification)", "instant +weight +loss", "insurance +lose +weight", "internet +marketing",
		"investment +decision", "invoice", "it\'*s +effective", "job +alert", "junk", "lambo", "laser +printer", "last day", "legal( +notice)*",
		"life( +|time)*( +(insurance|access|deal))*", "limited( +(amount|number|offer|supply|time +offer|time +only))*", "loan",
		"long +distance +phone +(number|offer)", "lose +weight( +(fast|spam))", "lottery", "lower +(interest +rates*|monthly +payment|your +mortgage +rate)",
		"lowest +((insurance|interest) +)*rates*", "luxury( +car)*", "mail +in +order +form", "mark +this +(as +not|not +as) +junk",
		"mass +email", "medical", "medicine", "meet +(girls|me|singles|women)", "member( +stuff)*", "message +contains +disclaimer",
		"message +from", "million(s|aire)", "mlm","multi-*level +marketing", "name", "near you", "never before", "new", "new +domain +extensions",
		"nigerian", "no +(age +restrictions|catch|claim +forms|cost|credit +(check|experience)|deposit +required|disappointment|experience|fees)",
		"no +(gimmicks*|hidden( +(costs|fees))*|interests*|inventory|investment( +required)*|medical +exams*|middleman|obligation)",
		"no +(payment +required|purchase +necessary|questions +asked|selling|strings +attached)", "no-obligation", "nominated +bank +account",
		"not +(intended|junk|s[cp]am)", "notspam", "number +1", "obligation", "off( +(everything|shore))*", "offers*( +extended)*", "offshore",
		"one +hundred +percent", "one-time", "online +(biz +opportunity|degree|income|job)", "open", "opportunity", "opt-*in",
		"orders*( +(shipped +by( +shopper)*|status))*", "outstanding +values*", "passwords*", "pay +your +bills", "per (day|week|month|year)",
		"perfect", "performance", "phone", "please( +open)*", "presently", "print +((from|form) +signature|out +and +fax)", "priority +mail",
		"privately +owned +funds", "prizes", "problem +with +(shipping|your +order)", "produced +and +sent +out", "(pure +)*profits*", "promise +you",
		"purchase", "quotes", "rate", "real +thing", "rebate", "reduce +debt", "refinanced* +home", "refund", "regarding",
		"remov(al|es)( +(instructions|wrinkles))*", "replica +watches", "request( +(now|today))*", "requires +(initial +)*investment",
		"reverses +aging", "risk free", "rolex", "round +the +world", "s +1618", "safeguard +notice", "sales*", "save( +(\$|€|big( +month)*|money|now))*",
		"score +with +babes", "search +engine +optimization", "section +301", "see +for +yourself", "seen +on", "serious( +(case|offer|only)*)",
		"sex", "shop(per|ping)*( +(now|spree))*", "snoring", "social +security +number", "soon", "spam( +free)*",
		"special (deal|discount|for +you|offer)", "stainless +steel", "stocks* +(alert|disclaimer +statement|pick)",
		"stop +(calling +me|emailing +me|further +distribution|snoring)", "strong +buy", "stuff +on +sale", "subject +to( +cash)*",
		"subscribe( +(for +free|now))*", "super +promo", "supplies", "take +action +now", "(hidden +charges|prizes)",
		"an +ad(vertisement)*", "terms", "the +best +rates", "credit +card +details", "the +following +form", "(comply|accordance) +with +spam",
		"no +refund", "giving +it +away", "this +isn'*t +(junk|spam|a +scam|)", "timeshare( +offers*)*", "traffic", "unlimited +trial",
		"(u\.*s\.$ +dollars|euros*)", "undisclosed( +recipient)*", "university +diplomas*", "unsecured +(credit|debt)", "unsolicited",
		"urgent +response", "vacation( +offers*)*", "valium", "viagara", "vicodin", "vip", "visit +our +website", "credit +card +details",
		"warranty +expired", "we +hate +spam", "we +honor +all", "website +visitors", "weekend +getaway", "weight +loss",
		"what\'*s +keeping +you", "while +(available|in +stock|stocks +last|you +sleep)", "who +really +wins", "win(ner|ning)*",
		"won", "xanax", "xxx", "you +have +been +(chosen|selected)", "your +(chance|status)", "zero +(chance|percent|risk)",
	];

	$spamwords_overpromise = [
		"\#1", "\%( +(free|satisfied))*", "0\%( +risk)*", "100\%( +(free|more|off|satisfied))*", "99(\.90)*\%", "access +for +free",
		"additional +income", "amaz(ed|ing)( +(offer|stuff))*", "be +(amazed|surprised|your +own +boss)", "believe +(me|us)",
		"best +(bargain|deal|offer|price|rates)", "big +bucks", "bonus", "boss", "can\'*t +live +without", "cancel",
		"consolidate +debt", "double +your +(cash|income|money)", "drastically +reduced", "earn( +extra)* +(cash|income|money)",
		"eliminate +bad +credit", "expect +to +earn", "extra( +(cash|income|money))*", "fantastic( +(deal|offer))*", "fast( +cash)*",
		"financial +freedom", "free +(access|consultation|gift|hosting|info|investment|membership|money|preview|quote|trial)",
		"full +refund", "get +out +of +debt", "giveaway", "guaranteed", "increase +(sales|traffic)", "incredible +(deal|offer)",
		"join +(billions|thousands|millions( of americans)*)", "lowe(r|st) +(rates|price)", "make +money", "million( +dollars)*",
		"miracle", "money +back", "month +trial +offer", "more +internet +traffic", "number +(1|one)", "once +in +a +life( +)*time",
		"one +hundred +percent +guaranteed", "one +time", "pennies +a +day", "potential +earnings", "prize", "promise",
		"pure +profit", "risk[- ]free", "satisfaction +guaranteed", "save +big( +money)*", "save +up +to", "special +promotion",
		"the +best", "thousands", "unbeatable +offer", "unbelievable", "unlimited( +trial)*", "Wonderful", "will +not +believe +your( +own)* +eyes"
	];

	$spamwords_money = [
		"\$\$\$", "€€€", "£££", "[0-9]+\% off", "a +few +bob", "accept +(cash|debit|credit) +cards", "affordable( +deal)*",
		"avoid +bankruptcy", "bad +credit", "bank", "bankruptcy", "bargain", "billing( +address)*", "billion( +dollars)*",
		"billionaire", "cards* +accepted", "cash( +(bonus|[- ]out))", "(cash)+", "casino", "cents +on +the +dollar",
		"(check|cheque)( +or +money +order)*", "claim +your +discount", "costs*", "credit( +(bureaus*|card( +offers)*|or +debit))*",
		"deal", "debt", "discount", "dollars", "double +your( +wealth)*",
		"earn( +(\$|cash|extra +income|from +home|monthly|per +(month|week)|your +degree))*", "easy +(income|terms)",
		"f *r *e *e", "for (free|just)", "get( +your)* +money", "hidden + assets", "huge +discount", "income( +from +home)*",
		"increase +(revenue|sales|traffic|your +chances)", "initial +investment", "instant +(earnings*|income)", "insurance",
		"investment( +advice)", "lifetime", "loans", "make +\$", "money([- ]+making|back +guarantee|)"
// Income
// Income from home
// Increase revenue
// Increase sales/traffic
// Increase your chances
// Initial investment
// Instant earnings
// Instant income
// Insurance
// Investment
// Investment advice
// Lifetime
// Loans
// Make $
// Money
// Money making
// Money-back guarantee
// Money-making
// Monthly payment
// Mortgage
// Mortgage rates
// Offer
// One hundred percent free
// Only $
// Price
// Price protection
// Prices
// Profits
// Quote
// Rates
// Refinance
// Save $
// Serious cash
// Subject to credit
// US dollars
// Why pay more?
// Your income
	];

	return false;
}


?>

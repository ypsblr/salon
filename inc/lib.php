<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Nullix\CryptoJsAes\CryptoJsAes;

// Load PHP Mailer components
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/SMTP.php';

require __DIR__ . "/CryptoJsAes.php";

// function debug_to_console($data) {
//     $output = $data;
//     if (is_array($output))
//         $output = implode(',', $output);

//     echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
// }

function orderSuffix($i) {
   $str = "$i";
   $t = $i > 9 ? substr($str,-2,1) : 0;
   $u = substr($str,-1);
   if ($t==1)
	   return $str . 'th';
   else
		switch ($u) {
			case 1: return $str . 'st';
			case 2: return $str . 'nd';
			case 3: return $str . 'rd';
			default: return $str . 'th';
		}
}

function encode_string_array ($stringArray) {
    $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),2))), '+/=', '_,');

    return $s;
}

function decode_string_array ($stringArray) {
    $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '_,', '+/=')))));
    return $s;
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

// Returns Age in Years, Months and Days
function age_today($date_of_birth) {
	$now = date_create();
	$dob = date_create($date_of_birth);

	$interval = date_diff($dob, $now);
	$diff_str = $interval->format("%y|%m|%d");
	return explode("|", $diff_str);
}

// Returns Age in Years, Months and Days
function age_on($date_of_birth, $target_date) {
	$target = date_create($target_date);
	$dob = date_create($date_of_birth);

	$interval = date_diff($dob, $target);
	$diff_str = $interval->format("%y|%m|%d");
	return explode("|", $diff_str);
}

// Check if an email entered is a valid email format
function is_an_email($str) {
	if (filter_var($str, FILTER_VALIDATE_EMAIL) == false)
		return false;
	else
		return true;
}

// Formats date in "Month date, Year" format
function print_date($date) {
	return date("M j, Y", strtotime($date));
}

// Return string containing only alphabets
function str_alpha($str) {
	$str = trim($str);
	$retstr = "";
	for ($i = 0; $i < strlen($str); ++ $i)
		if ((substr($str, $i, 1) >= 'a' && substr($str, $i, 1) <= 'z') || (substr($str, $i, 1) >= 'A' && substr($str, $i, 1) <= 'Z'))
			$retstr .= substr($str, $i, 1);

	return $retstr;
}

//Return string with punctuations and space removed
function str_nosep($str) {
	$str = trim($str);
	//$tokens = strtok($str, " ,;");
	//return implode("", $tokens);
	return preg_replace("/[ ,;.@]/", "", $str);
}

//Substitute Quotes in Strings to make it safe
function str_replace_quotes($str) {
	$str = str_replace("'", "&#039;", $str);
	$str = str_replace('"', "&quot;", $str);
	return $str;
}

//
// Convert first letters of name to upper case
//
function capitalize($str, $capitalize) {
	$excludes = array("of", "to", "in", "the", "for", "a", "an", "and");
	if (in_array(strtolower($str), $excludes))
		if ($capitalize)
			return ucfirst(strtolower($str));
		else
			return strtolower($str);
	else
		return ucfirst(strtolower($str));

}

function name_ucfirst($str) {
	//
	// Break into array
	//
	$separators = array(" ", ".", ",");
	$arr = array();
	$index = 0;
	$part = "";
	$first_term = true;
	for ($i = 0; $i < strlen($str); ++ $i) {
		if (in_array($str[$i], $separators)) {
			$arr[$index] = capitalize($part, $first_term);
			++ $index;
			$arr[$index] = $str[$i];	// separator
			++ $index;
			$part = "";
			$first_term = false;
		}
		else
			$part .= $str[$i];
	}
	if (! empty($part))
		$arr[$index] = capitalize($part, $first_term);

	return implode('', $arr);
}



// Expands a List into a comma separated text with 'and' or 'or' added before the last
function expand_list($list, $last_separator = 'and') {
	// Find the last
	$last = '';
	foreach ($list AS $item)
		$last = $item;

	// Create the expanded text
	$expanded = "";
	$first = true;
	foreach ($list AS $item) {
		if (! $first)
			$expanded .= ($item == $last) ? " " . $last_separator . " " : ", ";
		$expanded .= $item;
		$first = false;
	}
	return $expanded;
}

// convert_data - set of functions to convert input data passed to merge_data function
function convert_data($matches) {
    // The matched expression should contain "function_name|data1~data2..."
    if (preg_match("/\{[^|}]+\|[^|}]+\}/", $matches[0])) {
        // Delete opening and closing braces
        $expr = substr($matches[0], 1, -1);
        list($function, $args) = explode("|", $expr);
        switch ($function) {
            case "to_local_time" : {
                return date("Y-m-d H:i:s", strtotime($args));
            }
            case "local_tz" : {
                return date("T");
            }
        }
    }
    else {
        return $matches[0];     // Not a valid expression return
    }
}

// Merge Data Variables into a string message
function merge_data($message_blob, $data, $yearmonth = "")
{
	global $contest_yearmonth;

	if ($message_blob == "")
		return "";

	if ($yearmonth == "")
		$yearmonth = $contest_yearmonth;

	if (file_exists("salons/$yearmonth/blob/" . $message_blob)) {
		$message = file_get_contents("salons/$yearmonth/blob/" . $message_blob);
		foreach ($data as $variable => $value)
			$message = str_replace("[" . $variable . "]", $value, $message);

	    // Replace callbacks - Callbacks appear with opening and closing braces
	    // Within braces the syntax is function_name|arg1,arg2 and so on
	    $message = preg_replace_callback("/\{.*\}/", "convert_data", $message);

		return $message;
	}
	else
		return "";
}

// Send a Standard Email using format defined in database
function send_salon_email($email_id, $subject, $email_code, $text, $replace_text, $call_backs = false) {
	global $DBCON;
	static $mail_suffix = 0;

	$sql = "SELECT * FROM email_template WHERE template_code = '$email_code'";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query)) {
		$email_format = file_get_contents("../template/email/" . $row['email_body_blob']);

		$email_format = str_replace($text, $replace_text, $email_format);
		// Perform call back with templates taken from email format
		if ($call_backs) {
			foreach($call_backs AS $dynamic_template => $call_back_function) {
				// Find the Starting & End position of template and check if they are properly placed
				if (($start = strpos($email_format, "<<<" . $dynamic_template)) && ($end = strpos($email_format, $dynamic_template . ">>>")) && $end > $start ) {
					// Calculate Positions & Lengths
					$replace_start = $start;
					$template_start = $start + strlen("<<<" . $dynamic_template);
					$template_end = $end;
					$replace_end = $end + strlen($dynamic_template . ">>>");
					// Call the call_back function with template
					$template = substr($email_format, $template_start, $template_end - $template_start);
					$filled_template = $call_back_function($template);
					$email_format = substr_replace($email_format, $filled_template, $replace_start, $replace_end - $replace_start);
				}
				else
					return false;
			}
		}

		$cc_to = str_replace($text, $replace_text, $row['cc_to']);
		// $headers = "MIME-Version: 1.0" . "\r\n";
		// $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        // $headers .= 'From: YPS Salon <salon@ypsbengaluru.in>' . "\r\n";
		// $headers .= 'From: YPS Salon <salon@ypsbengaluru.in>' . "\r\n";
		// if ($cc_to != "")
		//	$headers .= "Cc: $cc_to\r\n";

		// Dump the text in case of localhost (test system)
        // if (preg_match("/localhost/i", $_SERVER['SERVER_NAME']) || preg_match("/salontest/i", $_SERVER['SERVER_NAME']) ) {
		if ( preg_match("/localhost/i", $_SERVER['SERVER_NAME']) ) {
			if (! is_dir("mails"))
				mkdir("mails");
			file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $email_format);
			return true;
		}
		else {
			//return mail($email_id, $subject, $email_format, $headers);
            // Send Email using PHPMailer
            // Instantiation and passing `true` enables exceptions
            $mail = new PHPMailer(true);

            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                        		// Send using SMTP

				// Option 1 - Unauthenticated using localhost - Works on GoDaddy server
				$mail->Host          = 'localhost';   	                     	// Localhost
				$mail->SMTPAuth      = false;                                   // No Authentication
				$mail->SMTPAutoTLS   = false;                               	// No TLS
				$mail->Port          = 25;                                    	// Regular SMTP port

				// Option 2 - Using Professional Mail SMTP Server - Works when run on desktop - Connection refused when run from hosting server - Cross domain connection
				// $mail->Host          = 'smtpout.secureserver.net';   	      	// Professional Email SMTP Server
				// $mail->SMTPAuth      = true;                                   	// Authenticate
				// $mail->SMTPAutoTLS   = false;                               	  	// For GoDaddy
				// $mail->SMTPAuth      = true;                                 	// Enable SMTP authentication
				// $mail->Username      = 'salon@ypsbengaluru.in';              	// Mail username
				// $mail->Password      = '***';                      				// Mil password
				// $mail->SMTPSecure    = 'tls';         							// Enable TLS encryption
				// $mail->Port          = 587;                                    	// TLS

				// Option 3 - Using Local server with CPANEL authentication - Works on Godaddy
				// $mail->Host       = 'sg2plcpnl0161.prod.sin2.secureserver.net';             // Set the SMTP server to send through
				// $mail->SMTPAuth   = true;                                   		// Authenticate
				// $mail->SMTPAutoTLS   = false;                               		// For GoDaddy
				// $mail->SMTPAuth   = true;                                 		// Enable SMTP authentication
				// $mail->Username   = 'cwhys6gqg37b';              				// CPANEL username
				// $mail->Password   = '***';                      					// CPANEL password
				// $mail->SMTPSecure = 'tls';         								// Enable TLS encryption
				// $mail->Port       = 587;                                    		// For GoDaddy with TLS

				// Option 4 - Using Relay Server - Does not work
				// $mail->Host          = 'relay-hosting.secureserver.net';   	    // Relay Server
				// $mail->SMTPAuth      = false;                                   	// No Authentication
				// $mail->SMTPAutoTLS   = false;                               		// No TLS
				// $mail->SMTPAuth      = true;                                 	// Disable SMTP authentication
				// $mail->SMTPSecure    = 'none';         							// Disable TLS encryption
				// $mail->Port          = 25;                                    	// Regular SMTP port

                //Recipients
                $mail->setFrom('salon@ypsbengaluru.in', 'YPS Salons');
                $mail->addReplyTo('salon@ypsbengaluru.in', 'YPS Salons');
                // $mail->addCC('salon@ypsbengaluru.in');

                // Add additional CC recipients (comma separated list of emails)
                if (! empty($cc_to)) {
                    foreach (explode(",", $cc_to) as $email)
                        $mail->addCC($email);
                }

                // Content
                $mail->addAddress($email_id);                       // Add a recipient
                $mail->isHTML(true);                                // Set email format to HTML
                $mail->Subject = $subject;
                $mail->Body = $email_format;

                $mail->send();

                // debug_dump("mail", $mail, __FILE__, __LINE__);
                return true;
            } catch (Exception $e) {
                return false;
            }
		}
	}

	return false;
}

// Write a variable dump to file
function debug_dump($name, $value, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/debug.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Dump of '$name' requested in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, print_r($value, true) . chr(13) . chr(10), FILE_APPEND);
}

// Log General Errors and Go Home
function log_error($errmsg, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": Operation failed in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Error Message: '$errmsg'" . chr(13) . chr(10), FILE_APPEND);
    if (!empty($_REQUEST)) {
        file_put_contents($log_file, "Dump of REQUEST:" . chr(13) . chr(10), FILE_APPEND);
        file_put_contents($log_file, print_r($_REQUEST, true) . chr(13) . chr(10), FILE_APPEND);
        if ( isset($_REQUEST['ypsd']) && isset($_SESSION['SALONBOND']) && class_exists("Nullix\CryptoJsAes\CryptoJsAes") ) {
            file_put_contents($log_file, "Dump of Encrypted Data:" . chr(13) . chr(10), FILE_APPEND);
            $ypsd = CryptoJsAes::decrypt($_REQUEST['ypsd'], $_SESSION['SALONBOND']);
            file_put_contents($log_file, print_r($ypsd, true) . chr(13) . chr(10), FILE_APPEND);
        }
    }
	if (!empty($_SESSION)) {
        file_put_contents($log_file, "Dump of SESSION:" . chr(13) . chr(10), FILE_APPEND);
        file_put_contents($log_file, print_r($_SESSION, true) . chr(13) . chr(10), FILE_APPEND);
    }
}

function handle_error($errmsg, $phpfile, $phpline) {
	log_error($errmsg, $phpfile, $phpline);
	// Is it POST Method
	if ( (! empty($_SERVER['HTTP_X_REQUESTED_WITH'])) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "xmlhttprequest") {
		// Ajax Call with JSON return value
		$resArray = array();
		$resArray['success'] = FALSE;
		$resArray['msg'] = $errmsg;
		echo json_encode($resArray);
	}
	else {
		if (! empty($_SESSION['err_msg'])) {
			// Already in the middle of handling an error. Go to a safe place
			header("Location: /index.php");
			printf("<script>location.href='/index.php'</script>");
		}
		else {
			// Return back to calling function
			if (isset($_SERVER['HTTP_REFERER'])) {
				$_SESSION['err_msg'] = $errmsg;
				header("Location: ".$_SERVER['HTTP_REFERER']);
				printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] ."'</script>");
			}
			else {
				// Looks like the page is launched from email
                $_SESSION['err_msg'] = $errmsg;
                header("Location: /index.php");
    			printf("<script>location.href='/index.php'</script>");
				// Return not found
				// header("HTTP/1.0 404 Not Found", true, 404);
			}
		}
	}
	die();
}


// LOG SQL Errors for debugging
// Usage: $query = mysqli_query($con, $sql) or sql_die($sql, mysqli_error($con), __FILE__, __LINE__);
function log_sql_error($sql, $errmsg, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
}

function sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	handle_error("Data Operation failed. Please report to YPS.", $phpfile, $phpline);
}

//
// Verify authenticity using Google recaptcha
//
function verify_recaptcha($user_token) {
	$yps_secret = "6LfpiFQUAAAAANvKz1nrcTHOGLJ8Bymz4QOKLKRQ";

	// Invoke YPS Authentication Service
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$post_fields = array();
	$post_fields["secret"] = $yps_secret;
	$post_fields["response"] = $user_token;
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

	$result = json_decode(curl_exec($ch), true);
	//debug_dump("YPS Login Data", $result, __FILE__, __LINE__);

	if ($result && $result['success'] == true)
		return "";
	else
		return implode($result['error-codes']);
}


//
// yps_authenticate
// Check User Credentials with Wordpress and returns data
// Function will return error message. Empty string if successful
//
function yps_login($login_id, $password, $email = "") {

	global $DBCON;

	$err_msg = "";
	$first_name = "";
	$last_name = "";
	$user_gender = "";
	$user_email = "";
	$user_avatar = "";
	$user_status = "";
	$user_phone = "";
	$user_address = "";
	$user_city = "";
	$user_state = "";
	$user_pin = "";
	$user_login = "";

	// Invoke YPS Authentication Service
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/authenticate.php");
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_HEADER, false);
	// $post_fields = array();
	// $post_fields["username"] = $login_id;
	// $post_fields["password"] = $password;
	// $post_fields["email"] = $email;
	// curl_setopt($ch, CURLOPT_POST, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	//
	// $result = json_decode(curl_exec($ch), true);
	//debug_dump("YPS Login Data", $result, __FILE__, __LINE__);

	// Fetch yps_user data and validate password
	$sql  = "SELECT * FROM yps_user ";
	$sql .= " WHERE yps_login_id = '$login_id' OR email = '$email' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	
	if (mysqli_num_rows($query) == 0) {
	    debug_to_console(10.1);
		$data->status = "ERR";
		$data->errmsg = 'Cannot Login. User Account Status: NOT ACTIVE';
		echo json_encode($data);
	}
	else {
	    debug_to_console(10.2);
		$result = mysqli_fetch_array($query);
		// Validate PASSWORD
		include $_SERVER['DOCUMENT_ROOT'] . "/inc/phpass.php";

		$hasher = new PasswordHash(8, true);

		if ($hasher->CheckPassword($password, $result['password'])) {
		    debug_to_console(10.3);
			$first_name = $result['first_name'];
			$last_name = $result['last_name'];
			$user_gender = $result['gender'];
			$user_email = $result['email'];
			$user_avatar = $result['avatar'];
			$user_phone = $result['phone'];
			$user_address = $result['address_street'];
			$user_city = $result['address_city'];
			$user_state = $result['address_state'];
			$user_pin = $result['address_pin'];
			$user_login = $result['login'];
			return array("", $result);
		}
		else {
		    debug_to_console(10.4);
			$data->status = "ERR";
			$data->errmsg = 'Cannot Login. Incorrect Member ID / Password';
			return array($data->errmsg, $result);
			// echo json_encode($data);

		}
	}

    debug_to_console(10.5);
	if ($result && $result['status'] == "OK") {
	    debug_to_console(10.6);
		$user_status = $result['member_status'];
		// Add logic to validate member status
		if ($user_status != 'active')
			$err_msg = 'Cannot Login. User Account Status: NOT ACTIVE';
		else {
		    debug_to_console(10.7);
			$first_name = $result['first_name'];
			$last_name = $result['last_name'];
			$user_gender = $result['gender'];
			$user_email = $result['email'];
			$user_avatar = $result['avatar'];
			$user_phone = $result['phone'];
			$user_address = $result['address_street'];
			$user_city = $result['address_city'];
			$user_state = $result['address_state'];
			$user_pin = $result['address_pin'];
			$user_login = $result['login'];
			// $data->country = $swmp_user->country;
		}
	}
	else {
	    debug_to_console(10.8);
		$err_msg = "Validation Failed !!! ";
	}
	// return array($err_msg, $first_name, $last_name, $user_gender, $user_email, $user_avatar, $user_status, $user_login, $user_phone, $user_address, $user_city, $user_state, $user_pin);
	return array($err_msg, $result);
}


// YPS Get list of Users
// Returns an array of login, first_name, last_name, email
function yps_users() {

	global $DBCON;

	$yps_users = array();

	// Invoke YPS Authentication Service
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserlist.php");
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_HEADER, false);
	// $post_fields = array();
	// $post_fields["magic"] = "ypsmagic1971onwards";
	// curl_setopt($ch, CURLOPT_POST, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	//
	// $result = json_decode(curl_exec($ch), true);
	//
	// if ($result && $result['status'] == "OK") {
	// 	$err_msg = "";
	// 	$user_list = $result['user_list'];
	// }
	// else {
	// 	$err_msg = ($result ? $result['errmsg'] : "Data Fetch failed");
	// 	$user_list = array();
	// }

	// New userlist function using local mysql_list_tables
	$sql = "SELECT * FROM yps_user ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$data->status = "ERR";
		$data->errmsg = "YPS Member List returned empty !";
		echo json_encode($data);
	}
	else {
		$return_list = array();
		while ($user = mysqli_fetch_array($query)) {
			// $swpm_user = SwpmMemberUtils::get_user_by_user_name($user->user_login);
			// if($swpm_user->account_state == "active") {
			$return_list[] = array(
								"login" => $user['yps_login_id'],
								"email" => $user['email'],
								"name" => $user['first_name'] . " " . $user['last_name'],
								"status" => 0,
								"account_state" => 'active',
								"member_since" => $user['member_since'],
								"subscription_starts" => $user['subscription_starts']
							);
			// }
		}
		$data->status = "OK";
		$data->errmsg = "";
		$data->user_list = $return_list;
		echo json_encode($data);
	}


	return array($err_msg, $user_list);
}

// YPS Get user details on email
// Returns an array of login, first_name, last_name, email
function yps_getuserbyemail($email) {

	global $DBCON;

	$yps_users = array();

	// Invoke YPS Authentication Service
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserbyemail.php");
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_HEADER, false);
	// $post_fields = array();
	// $post_fields['email'] = $email;
	// $post_fields["magic"] = "ypsmagic1971onwards";
	// curl_setopt($ch, CURLOPT_POST, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

	$sql  = "SELECT * FROM yps_user ";
	$sql .= " WHERE yps_login_id = '$email' OR email = '$email' ";
	debug_dump("SQL", $sql, __FILE__, __LINE__);
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$is_yps_member = false;
		$err_msg = 'Cannot Login. Not active YPS Account';
	}

	$is_yps_member = false;
	$yps_user = array();
	$yps_user = mysqli_fetch_array($query, MYSQLI_ASSOC);
	debug_dump("yps_user", $yps_user, __FILE__, __LINE__);

	if ($yps_user['account_state'] == 'active') {
		$err_msg = "";
		$is_yps_member = true;
	}
	else {
		$err_msg = "No active membership found";
		$is_yps_member = false;
	}

	return array($err_msg, $is_yps_member, $yps_user);
}




// 
//
//






// YPS Get user details on email
// Returns an array of login, first_name, last_name, email
// function yps_getuserbyemail($email) {

// 	global $DBCON;

// 	$yps_users = array();

// 	// Invoke YPS Authentication Service
// // 	$ch = curl_init();
// // 	curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserbyemail.php");
// // 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// // 	curl_setopt($ch, CURLOPT_HEADER, false);
// // 	$post_fields = array();
// // 	$post_fields['email'] = $email;
// // 	$post_fields["magic"] = "ypsmagic1971onwards";
// // 	curl_setopt($ch, CURLOPT_POST, true);
// // 	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

// 	$sql  = "SELECT * FROM yps_user ";
// 	$sql .= " WHERE yps_login_id = '$email' OR email = '$email' ";
// 	debug_dump("SQL", $sql, __FILE__, __LINE__);
// 	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
// 	debug_dump("VAR", $query, __FILE__, __LINE__);
// 	if (mysqli_num_rows($query) == 0) {
// 		$is_yps_member = false;
// 		$err_msg = 'Cannot Login. Not active YPS Account';
// 	}

// 	$is_yps_member = false;
// 	$yps_user = array();
// 	$yps_user = mysqli_fetch_array($query);
// 	debug_dump("yps_user", $yps_user, __FILE__, __LINE__);

// 	if ($yps_user != null && $yps_user['account_state'] == 'active') {
// 		$err_msg = "";
// 		$is_yps_member = true;
// 	}
// 	else {
// 		$err_msg = "No active membership found";
// 		$is_yps_member = false;
// 	}

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, "https://www.ypsbengaluru.com/svc/getuserbyemail.php");
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_HEADER, false);
//     $post_fields = array();
//     $post_fields['email'] = $email;
//     $post_fields["magic"] = "ypsmagic1971onwards";
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
//     $result = curl_exec($ch);

// 	debug_dump("RESULT", $result, __FILE__, __LINE__);
    
//     $json = json_decode($result, true);
//     // echo $json;
    
//     $juser = $json["user"];
//     // var_dump($json["status"]);
//     // echo $json["status"];
//     curl_close($ch);
//     // fclose($fp);

// 	$jerr_msg = "";
// 	$jis_yps_member = false;
//     if ($json["status"] == "OK")
//         $jis_yps_member = true;

// 	if ($is_yps_member == false) {
// 		$jerr_msg = "No active membership found";
// 	}


// 	return array($err_msg, $is_yps_member, $yps_user, $jerr_msg, $jis_yps_member, $juser);
// }

// Blacklist check functions
function strip_vowels($a) {
	$a = strtolower($a);
	// Remove Vowels and pseudo-vowels except when it is the first letter
	$strip_chars = "aeiouhy";
	$str = "";

	// Ignore the first letter of the word
	for ($i = 0; $i < strlen($a); ++$i) {
		if ($i == 0)
			$str .= $a[$i];
		else if (strpos($strip_chars, $a[$i]) == false)
			$str .= $a[$i];
	}
	return $str;
}

// match name after breaking into words
function processed_words($a) {
	$words = [];
	$word = strtok($a, " ");
	while ($word) {
		$words[] = $word;
		$word = strtok(" ");
	}

	// Combine adjascent single-letter words (initials)
	$fused_words = [];
	$long_words = [];
	$fused_index = -1;
	$prev_single_letter = false;
	for ($i = 0; $i < sizeof($words); ++ $i) {
		if (strlen($words[$i]) == 1) {
			if (! $prev_single_letter)
				++ $fused_index;
			$fused_words[$fused_index] = $words[$i];
			$prev_single_letter = true;
		}
		else {
			$prev_single_letter = false;
			$long_words[] = strip_vowels($words[$i]);
		}
	}

	$words = array_merge($fused_words, $long_words);
	sort($words);
	return $words;
}

function partial_name_match($a, $b) {
	$a_words = processed_words($a);
	$b_words = processed_words($b);
	if (sizeof($a_words) != sizeof($b_words))
		return false;

	for ($i = 0; $i < sizeof($a_words); ++ $i) {
		$a_word = $a_words[$i];
		$b_word = $b_words[$i];
		$matches = similar_text($a_word, $b_word);
		// OK to have 1 mis-matched letter
		if ($matches < (max(strlen($a_word), strlen($b_word)) -1))
			return false;
	}
	return true;
}

// match name after breaking into words
function words($a) {
	$words = [];
	$word = strtok($a, " ");
	while ($word) {
		$words[] = $word;
		$word = strtok(" ");
	}
	return $words;
}

function name_match($a, $b) {
	$a_words = words($a);
	$b_words = words($b);
	if (sizeof($a_words) != sizeof($b_words))
		return false;

	for ($i = 0; $i < sizeof($a_words); ++ $i) {
		if (strtolower($a_words[$i]) != strtolower($b_words[$i]))
			return false;
	}
	return true;
}

function get_number($a) {
	$b = "";
	for ($i = strlen($a) - 1; $i > 0; -- $i)
		$b = ($a[$i] >= '0' && $a[$i] <= '9') ? $a[$i] . $b : "";

	return $b;
}

// Compare last 10 digits only
function phone_match($a, $b) {
	$a_ten = substr(get_number($a), -10);
	$b_ten = substr(get_number($b), -10);
	if ($a_ten == "" || $b_ten == "")
		return false;
	// Strip non-numerics
	return ($a_ten == $b_ten);
}

// Convert to lower case and match
function email_match($a, $b) {
	// don't match empty strings
	if ($a == "" || $b == "")
		return false;
	return (strtolower($a) == strtolower($b));
}

// Check if an exception has been recorded
function check_exception($email) {
	global $DBCON;

	if ($email == "")
		return false;
	$sql = "SELECT * FROM blacklist_exception WHERE email = '$email' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	return (mysqli_num_rows($query) > 0);
}

// Main routine, runs the details through blacklist
function check_blacklist($name, $email = "", $phone = "") {
	global $DBCON;

	$sql  = "SELECT * FROM blacklist ";
	$sql .= " WHERE entity_type = 'INDIVIDUAL' ";
	$sql .= "   AND withdrawn = '0' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		if (email_match($email, $row['email']) || phone_match($phone, $row['phone']) || name_match($name, $row['entity_name'])) {
			return array("MATCH", $row['entity_name']);
		}
		if (partial_name_match($name, $row['entity_name'])) {
			return array("SIMILAR", $row['entity_name']);
		}
	}
	return array("", "");
}

// Set Blacklist flags in profile
function mark_blacklist($profile_id, $match) {
	global $DBCON;

	$sql = "UPDATE profile SET blacklist_match = '$match', blacklist_exception = '0' WHERE profile_id = '$profile_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}

//
// Random Password Generator
//
function generate_password($length = 8, $complex=3) {
	$min = "abcdefghijklmnopqrstuvwxyz";
	$num = "0123456789";
	$maj = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$symb = "!@#$%^&*()_-=+;:,.?";
	$chars = $min;
	if ($complex >= 2) { $chars .= $num; }
	if ($complex >= 3) { $chars .= $maj; }
	if ($complex >= 4) { $chars .= $symb; }
	do {
		$password = substr( str_shuffle( $chars ), 0, $length );
	} while (strspn($password, $min) > 0 && ($complex >= 2 ? strspn($password, $num) > 0 : true) && ($complex >= 3 ? strspn($password, $maj) > 0 : true) && ($complex >= 4 ? strspn($password, $symb) > 0 : true));
	return $password;
}

function set_session_variables($profile_row) {
	$_SESSION['USER_ID'] = $profile_row['profile_id'];
	$_SESSION['LOGIN_ID'] = $profile_row['email'];
	$_SESSION['USER_NAME'] = $profile_row['salutation'] . " " . $profile_row['first_name'] . " " . $profile_row['last_name'];
	$_SESSION['USER_AVATAR'] = $profile_row['avatar'];
}

function delete_session_variables() {
	if (isset($_SESSION['USER_ID'])) unset($_SESSION['USER_ID']);
	if (isset($_SESSION['LOGIN_ID'])) unset($_SESSION['LOGIN_ID']);
	if (isset($_SESSION['USER_NAME'])) unset($_SESSION['USER_NAME']);
	if (isset($_SESSION['USER_AVATAR'])) unset($_SESSION['USER_AVATAR']);
}

function set_cookies($row) {
	$login_id = $row['email'];
	setcookie("YPS_SALON_LOGIN_ID", $login_id, time() + (86400 * 180));		// Remember for 180 days from now
	setcookie("YPS_SALON_PASSWORD", $row["password"], time() + (86400 * 180));
}

function delete_cookies() {
	setcookie("YPS_SALON_LOGIN_ID", "", time() - 3600);		// Valid till 1 hour back
	setcookie("YPS_SALON_PASSWORD", "", time() - 3600);
}


function http_method() {
	if (isset($_SERVER['HTTPS']) && (! empty($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] != "off")
		return "https://";
	else
		return "http://";
}

?>

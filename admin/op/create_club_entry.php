<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);

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


if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) && isset($_REQUEST['create_club_entry']) ) {

	// Assemble Information
	// Form Data
	$yearmonth = $_SESSION['admin_yearmonth'];
	$club_id = $_REQUEST['club_id'];
	$club_entered_by = $_REQUEST['profile_id'];
	$entrant_category = $_REQUEST['entrant_category'];
	$fee_code = $_REQUEST['fee_code'];
	$promised_group_size = $_REQUEST['promised_group_size'];
	$group_code = isset($_REQUEST['group_code']) ? $_REQUEST['group_code'] : "";
	$discount_percentage = is_numeric($_REQUEST['discount_percentage']) ? $_REQUEST['discount_percentage'] / 100 : 0;		// store in fraction
	$payment_mode = $_REQUEST['payment_mode'];
	$allow_standard_discounts = $_REQUEST['allow_standard_discounts'];
	$participation_code_list = $_REQUEST['participation_codes'];
	$participation_codes = implode("|", $participation_code_list);

	// Get Data for generating Email
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_name = $contest['contest_name'];
	$fee_model = $contest['fee_model'];

	// Derived Information
	// Entrant Category
	$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$yearmonth' AND entrant_category = '$entrant_category' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$ec_data = mysqli_fetch_array($query);
	$currency = $ec_data['currency'];
	$fee_group = $ec_data['fee_group'];
	$discount_group = $ec_data['discount_group'];
	$entrant_category_name = $ec_data['entrant_category_name'];

	// Safety check to see that the club has not already issued discount coupons
	$sql  = "SELECT * FROM coupon WHERE yearmonth = '$yearmonth' AND club_id = '$club_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0)
		return_error("The Club has already issued coupons. Cannot modify !", __FILE__, __LINE__);

	$discount_code = 'CLUB';

	if ($allow_standard_discounts == "YES") {
		// Fee Model = "FEE"
		// The discount table provides a discount amount for each participation_code
		// Our interest is to find if there are any group_codes matching the promised_group_size
		// and other parameters
		$group_code = "";
		$date = date("Y-m-d");
		// Find Group Code
		$sql  = "SELECT DISTINCT group_code ";
		$sql .= "  FROM discount ";
		$sql .= " WHERE discount.yearmonth = '$yearmonth' ";
		$sql .= "   AND discount_code = '$discount_code' ";
		$sql .= "   AND discount.fee_code = '$fee_code' ";
		$sql .= "   AND discount_group = '$discount_group' ";
		$sql .= "   AND discount.currency = '$currency' ";
		$sql .= "   AND discount.minimum_group_size <= '$promised_group_size' ";
		$sql .= "   AND discount.maximum_group_size >= '$promised_group_size' ";
		$sql .= "   AND discount_start_date <= '$date' ";
		$sql .= "   AND discount_end_date >= '$date' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			return_error("Unable to find any Published Discount for details provided !");

		$row = mysqli_fetch_array($query);
		$group_code = $row['group_code'];
	}
	else if($fee_model == "POLICY") {
		// Non standard discounts
		// If a specific discount percentage is provided, create a special group_code and
		// set the discount to the percentage specified
		// else set the group code to the group code selected
		if ($discount_percentage > 0) {
			// Determine the last date till when the fees are applicable
			// Can be different from registration_end_date for Early Bird Rates
			$sql  = "SELECT IFNULL(MAX(fee_end_date), '') AS discount_end_date ";
			$sql .= "  FROM fee_structure ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND fee_code = '$fee_code' ";
			$sql .= "   AND fee_group = '$fee_group' ";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$fee_row = mysqli_fetch_array($query);
			if ($fee_row['discount_end_date'] == "")
				return_error("No Fee defined for the selected Fee Group", __FILE__, __LINE__);
			if ($fee_row['discount_end_date'] < date("Y-m-d"))
				return_error("Fee defined for the selected Fee Group has expired", __FILE__, __LINE__);

			$discount_start_date = date("Y-m-d");
			$discount_end_date = $fee_row['discount_end_date'];
			$discount_round_digits = ($currency == "USD") ? 2 : 0;

			// Create a Group Discount Code unique to the club
			$group_code = sprintf("GDC_%04d", $club_id);

			// This applies only in case fee_model is defined as POLICY in the contest table
			$participation_code = $fee_model;

			// Re-create discount entry
			$sql = "DELETE from discount WHERE yearmonth = '$yearmonth' AND fee_code = '$fee_code' AND group_code = '$group_code' ";
			mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

			$sql  = "INSERT INTO discount (yearmonth, discount_code, fee_code, discount_group, participation_code, currency, group_code, ";
			$sql .= "       minimum_group_size, maximum_group_size, discount_start_date, discount_end_date, discount_percentage, discount_round_digits ) ";
			$sql .= "VALUES ('$yearmonth', '$discount_code', '$fee_code', '$discount_group', '$participation_code', '$currency', '$group_code', ";
			$sql .= "       '$promised_group_size', '999', '$discount_start_date', '$discount_end_date', '$discount_percentage', '$discount_round_digits' ) ";
			mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
	}

	// Write out Club Entry
	$sql = "SELECT * FROM club_entry WHERE yearmonth = '$yearmonth' AND club_id = '$club_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		// INSERT
		$sql  = "INSERT INTO club_entry (yearmonth, club_id, club_entered_by, currency, ";
		$sql .= "       payment_mode, entrant_category, fee_code, group_code, minimum_group_size, participation_codes) ";
		$sql .= "VALUES ('$yearmonth', '$club_id', '$club_entered_by', '$currency', ";
		$sql .= "       '$payment_mode', '$entrant_category', '$fee_code', '$group_code', '$promised_group_size', '$participation_codes') ";
		mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	else {
		$sql  = "UPDATE club_entry ";
		$sql .= "   SET club_entered_by = '$club_entered_by' ";
		$sql .= "     , payment_mode = '$payment_mode' ";
		$sql .= "     , currency = '$currency' ";
		$sql .= "     , entrant_category = '$entrant_category' ";
		$sql .= "     , fee_code = '$fee_code' ";
		$sql .= "     , group_code = '$group_code' ";
		$sql .= "     , minimum_group_size = '$promised_group_size' ";
		$sql .= "     , participation_codes = '$participation_codes' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND club_id = '$club_id' ";
		mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Send confirmation email
	$sql = "SELECT * FROM profile WHERE profile_id = '$club_entered_by' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$profile = mysqli_fetch_array($query);
	$profile_name = $profile['profile_name'];
	$email = $profile['email'];

	$sql = "SELECT * FROM club WHERE club_id = '$club_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club = mysqli_fetch_array($query);
	$club_name = $club['club_name'];

	// Determine Discount Percentage for printing
	if ($allow_standard_discounts != "YES" && $fee_model = 'POLICY') {
		$sql  = "SELECT * FROM discount ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND discount_code = 'CLUB' ";
		$sql .= "   AND fee_code = '$fee_code' ";
		$sql .= "   AND discount_group = '$discount_group' ";
		$sql .= "   AND participation_code = 'POLICY' ";
		$sql .= "   AND currency = '$currency' ";
		$sql .= "   AND group_code = '$group_code' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$club_discount = mysqli_fetch_array($query);
		$discount_percentage_for_email = $club_discount['discount_percentage'];
	}

	// Get Participation Code Descriptions
	$participation_description_list = [];
	foreach ($participation_code_list as $participation_code) {
		$sql  = "SELECT * FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND fee_code = '$fee_code' ";
		$sql .= "   AND fee_group = '$fee_group' ";
		$sql .= "   AND participation_code = '$participation_code' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$participation_description_list[] = $row['description'];
	}


	$subject= "Discount set up for " . $club_name;
//	$replacement = array($url, $contest_name, $profile_name, $club_name, $entrant_category_name, $minimum_group_size, $payment_mode, sprintf("%.2f %%", $discount_percentage * 100 );
//	$token = array('{{URL}}', '{{SALON}}', '{{NAME}}', '{{CLUB_NAME}}', '{{ENTRANT_CATEGORY}}', '{{MINIMUM_GROUP_SIZE}}', '{{PAYMENT_MODE}}', '{{DISCOUNT}}');

	$message = load_message("template/discount_communication.htm",
							array(
								"server-link" => SALON_ROOT,
								"participant-name" => $profile_name,
								"salon-name" => $contest_name,
								"club-name" => $club_name,
								"entrant-category" => $entrant_category_name,
								"minimum-group-size" => $promised_group_size,
								"payment-mode" => $payment_mode,
								"discount" => ($allow_standard_discounts == "YES") ? "As published" : sprintf("%.2f %%", $discount_percentage_for_email * 100 ),
								"participations" => implode(", ", $participation_description_list)
							));

	send_mail($email, $subject, $message);
	$_SESSION['success_msg'] = "Discount successfully created for " . $club_name . "! ";

	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>

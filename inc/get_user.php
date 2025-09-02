<?php

// Copy columns from entry to profile
function set_entry_ec_values(&$profile, $entry = false) {
	$profile['entrant_category'] = (! $entry) ? "" : $entry['entrant_category'];
	$profile['entrant_category_name'] = (! $entry) ? "" : $entry['entrant_category_name'];
	$profile['participation_code'] = (! $entry) ? "" : $entry['participation_code'];
	$profile['digital_sections'] = (! $entry) ? "" : $entry['digital_sections'];
	$profile['print_sections'] = (! $entry) ? "" : $entry['print_sections'];
	$profile['fees_payable'] = (! $entry) ? "" : $entry['fees_payable'];
	$profile['discount_applicable'] = (! $entry) ? "" : $entry['discount_applicable'];
	$profile['payment_received'] = (! $entry) ? "" : $entry['payment_received'];
	$profile['fee_code'] = (! $entry) ? "" : $entry['fee_code'];
	$profile['currency'] = (! $entry) ? "" : $entry['currency'];
	$profile['agree_to_tc'] = (! $entry) ? "" : $entry['agree_to_tc'];
	$profile['uploads'] = (! $entry) ? "" : $entry['uploads'];
	$profile['awards'] = (! $entry) ? "" : $entry['awards'];
	$profile['hms'] = (! $entry) ? "" : $entry['hms'];
	$profile['acceptances'] = (! $entry) ? "" : $entry['acceptances'];
	$profile['score'] = (! $entry) ? "" : $entry['score'];
	$profile['can_create_club'] = (! $entry) ? "" : $entry['can_create_club'];
	$profile['fee_waived'] = (! $entry) ? "" : $entry['fee_waived'];
	$profile['acceptance_reported'] = (! $entry) ? "" : $entry['acceptance_reported'];
	$profile['award_group'] = (! $entry) ? "" : $entry['award_group'];
	$profile['fee_group'] = (! $entry) ? "" : $entry['fee_group'];
	$profile['discount_group'] = (! $entry) ? "" : $entry['discount_group'];
	$profile['age_minimum'] = (! $entry) ? "" : $entry['age_minimum'];
	$profile['age_maximum'] = (! $entry) ? "" : $entry['age_maximum'];
}

// Copy columns from club to profile
function set_club_values(&$profile, $club = false) {
	$profile['club_name'] = (! $club) ? "" : $club['club_name'];
	$profile['club_contact'] = (! $club) ? "" : $club['club_contact'];
	$profile['club_website'] = (! $club) ? "" : $club['club_website'];
	$profile['club_logo'] = (! $club) ? "" : $club['club_logo'];
	$profile['club_address'] = (! $club) ? "" : $club['club_address'];
	$profile['club_phone'] = (! $club) ? "" : $club['club_phone'];
	$profile['club_email'] = (! $club) ? "" : $club['club_email'];
	$profile['club_mem_for_discount'] = (! $club) ? "" : $club['club_mem_for_discount'];
}

// Copy columns from Club Entry
function set_club_entry_values(&$profile, $club_entry = false) {
	global $DBCON;
	global $contest_yearmonth;

	$profile['club_entered_by'] = (! $club_entry) ? "" : $club_entry['club_entered_by'];
	$profile['club_currency'] = (! $club_entry) ? "" : $club_entry['currency'];
	$profile['club_payment_mode'] = (! $club_entry) ? "" : $club_entry['payment_mode'];
	$profile['club_entrant_category'] = (! $club_entry) ? "" : $club_entry['entrant_category'];
	$profile['club_fee_code'] = (! $club_entry) ? "" : $club_entry['fee_code'];
	$profile['club_group_code'] = (! $club_entry) ? "" : $club_entry['group_code'];
	$profile['club_minimum_group_size'] = (! $club_entry) ? "0" : $club_entry['minimum_group_size'];
	$profile['club_participation_codes'] = (! $club_entry) ? "" : $club_entry['participation_codes'];
	$profile['club_paid_participants'] = (! $club_entry) ? "0" : $club_entry['paid_participants'];
	$profile['club_total_fees'] = (! $club_entry) ? "0.00" : $club_entry['total_fees'];
	$profile['club_total_discount'] = (! $club_entry) ? "0.00" : $club_entry['total_discount'];
	$profile['club_total_payment_received'] = (! $club_entry) ? "0.00" : $club_entry['total_payment_received'];

	$sql = "SELECT profile_name FROM profile WHERE profile_id = '" . $profile['club_entered_by'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query))
		$profile['club_entered_by_name'] = $row['profile_name'];
	else
		$profile['club_entered_by_name'] = "";

	$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$contest_yearmonth' AND entrant_category = '" . $profile['entrant_category'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($row = mysqli_fetch_array($query)) {
		$profile['club_entrant_category_name'] = $row['entrant_category_name'];
		$profile['club_fee_group'] = $row['fee_group'];
		$profile['club_discount_group'] = $row['discount_group'];
	}
	else {
		$profile['club_entrant_category_name'] = '';
		$profile['club_fee_group'] = '';
		$profile['club_discount_group'] = '';
	}

}

// Copy columns from coupon to profile
function set_coupon_values(&$profile, $coupon = false) {
	$profile['coupon_coupon_text'] = (! $coupon) ? "" : $coupon['coupon_text'];
	$profile['coupon_discount_code'] = (! $coupon) ? "" : $coupon['discount_code'];
	$profile['coupon_participation_code'] = (! $coupon) ? "" : $coupon['participation_code'];
	$profile['coupon_fee_code'] = (! $coupon) ? "" : $coupon['fee_code'];
	$profile['coupon_digital_sections'] = (! $coupon) ? "" : $coupon['digital_sections'];
	$profile['coupon_print_sections'] = (! $coupon) ? "" : $coupon['print_sections'];
	$profile['coupon_fees_payable'] = (! $coupon) ? "" : $coupon['fees_payable'];
	$profile['coupon_discount_applicable'] = (! $coupon) ? "" : $coupon['discount_applicable'];
	$profile['coupon_payment_received'] = (! $coupon) ? "" : $coupon['payment_received'];
}

// Result Withheld Values
function set_result_withheld_values(&$profile, $rwh = false) {
	$profile['withhold_type'] = (! $rwh) ? "" : $rwh['withhold_type'];
	$profile['withhold_list'] = (! $rwh) ? "" : $rwh['withhold_list'];
	$profile['withhold_reason'] = (! $rwh) ? "" : $rwh['withhold_reason'];
	$profile['withhold_notes'] = (! $rwh) ? "" : $rwh['withhold_notes'];
	$profile['withhold_date'] = (! $rwh) ? "" : $rwh['withhold_date'];
	$profile['withhold_status'] = (! $rwh) ? "" : $rwh['withhold_status'];
	$profile['withdrawal_date'] = (! $rwh) ? "" : $rwh['withdrawal_date'];
	$profile['withdrawal_notes'] = (! $rwh) ? "" : $rwh['withdrawal_notes'];
	if ( (! $rwh) || $rwh['withhold_status'] != 'ACTIVE')
		$profile['results_withheld'] = true;
	else
		$profile['results_withheld'] = false;
}


// Get All Details of the user under $tr_user array
function get_user($profile_id) {
	global $DBCON;
	global $contest_yearmonth;

	// Get Contest details
	$sql  = "SELECT * FROM contest WHERE yearmonth = '$contest_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Get Basic profile information of registered user
	$sql  = "SELECT * FROM profile ";
	$sql .= " WHERE profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$tr_user = mysqli_fetch_array($query);

		// Get Country Name
		$sql = "SELECT * FROM country WHERE country_id = '" . $tr_user['country_id'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query);
			$tr_user['country_name'] = $row['country_name'];
			$tr_user['dial_prefix'] = $row['dial_prefix'];
		}
		else {
			$tr_user['country_name'] = "Incorrect Country";
			$tr_user['dial_prefix'] = "-";
		}

		// If the participant has registered for the current contest, get entry information
		if ($contest_archived)
			$sql  = "SELECT * FROM ar_entry entry, entrant_category ";
		else
			$sql  = "SELECT * FROM entry, entrant_category ";
		$sql .= " WHERE entry.yearmonth = '$contest_yearmonth' ";
		$sql .= "   AND entry.profile_id = '$profile_id' ";
		$sql .= "   AND entrant_category.yearmonth = '$contest_yearmonth' ";
		$sql .= "   AND entry.entrant_category = entrant_category.entrant_category ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
		set_entry_ec_values($tr_user, $row);

		// Get Club Details
		$sql = "SELECT * FROM club WHERE club_id = '" . $tr_user['club_id'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		set_club_values($tr_user, $row);

		// Get Club Entry details if found
		$sql = "SELECT * FROM club_entry WHERE yearmonth = '$contest_yearmonth' AND club_id = '" . $tr_user['club_id'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		set_club_entry_values($tr_user, $row);

		// Get Coupon details if found
		$sql = "SELECT * FROM coupon WHERE yearmonth = '$contest_yearmonth' AND (email = '" . $tr_user['email'] . "' OR profile_id = '" . $tr_user['profile_id'] . "' )";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		set_coupon_values($tr_user, $row);

		// Results withheld details
		// uncomment when withholding facility is introduced
		// $sql = "SELECT * FROM result_withheld WHERE yearmonth = '$contest_yearmonth' AND profile_id = '" . $tr_user['profile_id'] . "' ";
		// $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		// $row = mysqli_fetch_array($query);
		// set_result_withheld_values($tr_user, $row); 	

		return $tr_user;
	}
	else {
		// Remove all stored information on user
		delete_session_variables();
		delete_cookies();
		handle_error("User not found !", __FILE__, __LINE__);
	}

	return false;
}

if (! empty($_SESSION['USER_ID'])) {
	if ($tr_user = get_user($_SESSION['USER_ID']))
		set_session_variables($tr_user);		// Refresh Session Variables again
	else
		handle_error("Unable to validate user. Login again.", __FILE__, __LINE__);
}
else {
	handle_error("User not logged in !", __FILE__, __LINE__);
}

?>

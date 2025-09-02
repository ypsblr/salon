<?php
//
// Display Categories either in Table or Modal Dialog Form
//

// Get List of Entrant Categories
function ec_get_entrant_category_list($award_group = "") {
	global $DBCON;
	global $contest_yearmonth;

	$sql  = "SELECT * FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$contest_yearmonth' ";
	if ($award_group != "")
		$sql .= "   AND award_group = '$award_group' ";
	$sql .= " ORDER BY entrant_category ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$ec_list = array();
	while ($row = mysqli_fetch_array($query)) {
		$ec_list[$row['entrant_category']] = $row;
	}

	return $ec_list;

}
//- gent_entrant_category_list()

// Expand country codes to country names list
function ec_get_country_names_list($codes){
	global $DBCON;

	$sql  = "SELECT * FROM country ";
	$sql .= "WHERE country_id IN ($codes) ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$names_list = array();
	while($row = mysqli_fetch_array($query))
		$names_list[] = $row['country_name'];

	return $names_list;
}
?>

<?php

// Generate a Details of Entrant Category in readable format
function ec_print_ec_details($row) {
	$entrant_category = $row['entrant_category'];
?>
	<p class="text text-lead"><b><u><?= $row['entrant_category_name']; ?></u></b></p>
	<p class="text text-color"><big><b>Eligibility Criteria</b></big></p>
	<ul>
<?php
	if ($row['yps_membership_required']) {
		$yps_member_types = array();
		foreach(explode(",", $row['yps_member_prefixes']) AS $member_prefix) {
			switch ($member_prefix) {
				case "LM" : $yps_member_types[] = "Life"; break;
				case "HM" : $yps_member_types[] = "Honorary"; break;
				case "IM" : $yps_member_types[] = "Individual"; break;
				case "JA" : $yps_member_types[] = "Junior Associate"; break;
				default: $yps_member_types[] = "Unknown (" . $member_prefix . ")";
			}
		}
?>
		<li><b>Membership</b> - A YPS <?php echo expand_list($yps_member_types, 'or');?> Member.</li>
<?php
	}
	if ($row['gender_must_match']) {
?>
		<li><b>Gender</b> - A <?=$row['gender_match'];?>.</li>
<?php
	}
	if ($row['age_within_range']) {
?>
		<li><b>Age</b> - between <?=$row['age_minimum'];?> and <?=$row['age_maximum'];?>.</li>
<?php
	}
	if ($row['country_must_match']) {
		if ($row['country_codes'] != "") {
?>
			<li><b>Country</b> - Must be resident in <?php echo expand_list(ec_get_country_names_list($row['country_codes']), "or");?>.</li>
<?php
		}
		else {
?>
			<li><b>Country</b> - Must not be residing in <?php echo expand_list(ec_get_country_names_list($row['country_excludes']), "or");?>.</li>
<?php
		}
	}
	else {
?>
		<li>There are no country restrictions.</li>
<?php
	}
	if ($row['state_must_match']) {
?>
		<li><b>State</b> - Must be resident in <?php echo expand_list(explode(',', $row['state_names']), "or");?>.</li>
<?php
	}
?>
	</ul>
	<p class="text text-color"><big><b>Features</b></big></p>
	<ul>
<?php
	if ($row['can_create_club']) {
?>
		<li><b>Club</b> - Participant can enter a Club into the Salon.</li>
<?php
	}
	else {
?>
		<li><b>Club</b> - Participant registered under <?=$entrant_category;?> category CANNOT create or enter a Club into the Salon.</li>
<?php
	}
	if ($row['fee_waived']) {
?>
		<li><b>Free Participation</b> - Participant registered under <?=$entrant_category;?> category can upload pictures in all sections without paying Salon Fee.</li>
<?php
	}
	else {
?>
		<li><b>Fee payment required</b> - Participant registered under <?=$entrant_category;?> category must pay the required Salon Fee before pictures can be uploaded.</li>
<?php
	}
	if ($row['acceptance_reported']) {
?>
		<li><b>Acceptance Reported</b> - Details of pictures accepted under the Salon will be reported to the organizations providing recognition to this Salon.</li>
<?php
	}
	else {
?>
		<li><b>No Acceptance, No reporting</b> - Pictures uploaded by participants who have registered under <?=$entrant_category;?> category are not eligible for reporting Acceptance to the organizations providing recognition to this Salon.</li>
<?php
	}
?>
	</ul>
<?php
}
// -function ec_print_ec_details($row)
?>

<?php
// Generate Displayable Entrant Category List
function ec_generate_ec_description($ec_list) {
	global $DBCON;
	global $contest_yearmonth;

	foreach($ec_list AS $entrant_category => $row) {
?>
		<div class="row">
			<div class="col-sm12">
				<p><b><?=$entrant_category;?> Category</b></p>
				<?php ec_print_ec_details($row);?>
			</div>
		</div>
		<br>
<?php
	}
}
// -ec_generate_ec_description($ec_list)
?>

<?php
// Get a list of Eligible Entrant Categories for the profile for this Salon
function ec_get_eligible_ec_list($yearmonth, $tr_user) {
	global $DBCON;

	$yps_mem_prefix = substr($tr_user['yps_login_id'], 0, 2);
	$gender = $tr_user['gender'];
	$country_id = $tr_user['country_id'];
	$state = $tr_user['state'];

	$sql = "SELECT results_date FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// list($age_years, $age_months, $age_days) = age_today($tr_user['date_of_birth']);
	list($age_years, $age_months, $age_days) = age_on($tr_user['date_of_birth'], $contest['results_date']);

	$sql  = "SELECT * FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";

	// match membership
	if ($tr_user['yps_login_id'] == "")	// Non YPS Participant
		$sql .= " AND yps_membership_required = '0' ";
	else {
		// $sql .= " AND (yps_membership_required = '0' OR (yps_membership_required = '1' AND FIND_IN_SET('$yps_mem_prefix', yps_member_prefixes))) ";
		$sql .= " AND yps_membership_required = '1' ";
		$sql .= " AND FIND_IN_SET('$yps_mem_prefix', yps_member_prefixes) ";
	}

	// Gender
	$sql .= " AND (gender_must_match = '0' OR (gender_must_match = '1' AND FIND_IN_SET('$gender', gender_match))) ";

	// Age within range
	$sql .= " AND (age_within_range = '0' OR (age_within_range = '1' AND ('$age_years' BETWEEN age_minimum AND age_maximum))) ";

	// Country Match
	$sql .= " AND (country_must_match = '0' OR (country_must_match = '1' AND FIND_IN_SET('$country_id', country_codes)) ";
	$sql .= "                               OR (country_must_match = '1' AND country_codes = '' AND NOT FIND_IN_SET('$country_id', country_excludes)) ) ";

	// State Match
	$sql .= " AND (state_must_match = '0' OR (state_must_match = '1' AND FIND_IN_SET('$state', state_names))) ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$ec_list = array();
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$ec_list[$row['entrant_category']] = $row;

		return $ec_list;
	}
	else
		return false;
}
// -ec_get_eligible_ec_list($yearmonth, $tr_user) {
?>

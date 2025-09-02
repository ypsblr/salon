<?php
/*
 * Functions used in User related PHPs
 */
function user_print_address($tr_user) {
?>
	<p class="text"><?= $tr_user['address_1'];?></p>
<?php 	if (! empty($tr_user['address_2'])) { ?>
	<p class="text"><?= $tr_user['address_2'];?></p>
<?php 	} ?>
<?php if (! empty($tr_user['address_3'])) { ?>
	<p class="text"><?= $tr_user['address_3'];?></p>
<?php } ?>
	<p class="text"><?=$tr_user['city'];?> - <?=$tr_user['pin'];?></p>
	<p class="text"><?=$tr_user['state'];?>, <?=strtoupper($tr_user['country_name']);?></p>
<?php
}
//- user_print_address()
?>

<?php
// Print Participation selected
function user_participation_str($tr_user) {
	global $contestDigitalSections;
	global $contestPrintSections;

	$str = "";
	if ($tr_user['digital_sections'] == 0 && $tr_user['print_sections'] == 0)
		$str .= "Yet to Select";
	else {
		$pstr = array();
		if ($contestDigitalSections > 0 && $tr_user['digital_sections'] > 0)
			$pstr[] = $tr_user['digital_sections'] . " Digital Sections ";
		if ($contestPrintSections > 0 && $tr_user['print_sections'] > 0)
			$pstr[] = $tr_user['print_sections'] . " Print Sections";
		$str = expand_list($pstr, "and");
	}
	return $str;
}
//- user_participation_str()
?>

<?php
// Get list of Clubs/Groups
function user_get_club_list() {
	global $DBCON;

	$sql = "SELECT * FROM club ORDER BY club_name";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club_list = array();
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$club_list[$row['club_id']] = $row;

	return $club_list;
}
//- user_get_club_list()
?>

<?php
// Get a specific contest
function user_get_contest($yearmonth) {
	global $DBCON;

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0)
		return mysqli_fetch_array($query);
	else
		return false;
}
?>

<?php
// Check and return Entry Award
function user_get_entry_award($yearmonth, $profile_id) {
	global $DBCON;

	// Check if the entrant has won an entrant Award
	$sql  = "SELECT * FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = '$yearmonth' ";
	$sql .= "   AND entry_result.award_id = award.award_id ";
	$sql .= "   AND entry_result.profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$entry_awards = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$entry_awards[] = $row;
		return $entry_awards;
	}
	else
		return [];
}
?>

<?php
function user_append_pic_result(&$pic, $award) {
	$pic['award_id'] = ($award != false) ? $award['award_id'] : 0;
	$pic['ranking'] = ($award != false) ? $award['ranking'] : 0;
	$pic['level'] = ($award != false) ? $award['level'] : 0;
	$pic['sequence'] = ($award != false) ? $award['sequence'] : 0;
	$pic['award_name'] = ($award != false) ? $award['award_name'] : "";
	$pic['has_medal'] = ($award != false) ? $award['has_medal'] : 0;
	$pic['has_pin'] = ($award != false) ? $award['has_pin'] : 0;
	$pic['has_ribbon'] = ($award != false) ? $award['has_ribbon'] : 0;
	$pic['has_memento'] = ($award != false) ? $award['has_memento'] : 0;
	$pic['has_gift'] = ($award != false) ? $award['has_gift'] : 0;
	$pic['has_certificate'] = ($award != false) ? $award['has_certificate'] : 0;
	$pic['cash_award'] = ($award != false) ? $award['cash_award'] : 0.0;
}
?>

<?php
// Check if user has any Acceptance
function user_has_acceptances($yearmonth, $profile_id) {
	global $DBCON;


	$sql = "SELECT archived FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$sql  = "SELECT COUNT(*) AS num_acceptances ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award ";
	else
		$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);

	return ($row['num_acceptances'] > 0);
}
?>

<?php
// Get Picture List with Award Information
function user_get_picture_list($yearmonth, $profile_id) {
	global $DBCON;

	$sql = "SELECT archived FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$pic_list = array();
	// Get a List of Pictures Submitted
	if ($contest_archived)
		$sql  = "SELECT * FROM ar_pic pic ";
	else
		$sql  = "SELECT * FROM pic ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$sql .= " ORDER BY section, pic_id";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	for ($index = 0; $row = mysqli_fetch_array($query, MYSQLI_ASSOC); ++$index) {
		$pic_list[$index] = $row;

		// Get Award/Acceptance Information for this picture
		if ($contest_archived)
			$sql  = "SELECT * FROM ar_pic_result pic_result, award ";
		else
			$sql  = "SELECT * FROM pic_result, award ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.section = '" . $row['section'] . "' ";
		$sql .= "   AND pic_result.award_id = award.award_id ";
		$sql .= "   AND pic_result.profile_id = '$profile_id' ";
		$sql .= "   AND pic_result.pic_id = '" . $row['pic_id'] . "' ";
		$qawd = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		user_append_pic_result($pic_list[$index], mysqli_fetch_array($qawd));

	}
	return $pic_list;
}
?>

<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// Generates Statistics for current salon. Called when a particular statistics is not available

// $stat_yearmonth should be set before including this file

//$stat_refresh = false;		// A generic toggle to regenerate statistics
//$stat_updated = false;

function ar_fix($sql) {
	global $translate_table;

	foreach ($translate_table as $placeholder => $table_name)
		$sql = str_replace($placeholder, $table_name, $sql);

	return $sql;
}


//
// INIT - Prepare Pic & Entry Tables
//
function prepare_pic_entry($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	//
	// UPDATE PIC with total_rating
	//
	// Clear totals
	$sql = <<<SQL
		UPDATE {pic}
		   SET pic.total_rating = 0
		 WHERE pic.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Build Totals
	$sql = <<<SQL
		UPDATE {pic}
		   SET pic.total_rating = (SELECT IFNULL(SUM({rating}), 0) FROM rating WHERE rating.yearmonth = pic.yearmonth AND rating.profile_id = pic.profile_id AND rating.pic_id = pic.pic_id)
		 WHERE pic.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// UPDATE ENTRY - uploads, awards, hms, acceptances & total_score
	//

	// Step 0 - Clear existing data
	$sql = <<<SQL
		UPDATE {entry}
		   SET uploads = 0
		     , awards = 0
			 , hms = 0
			 , acceptances = 0
			 , score = 0
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 1 - UPLOADS
	$sql = <<<SQL
		UPDATE {entry}
		   SET uploads = (SELECT COUNT(*) FROM {pic} WHERE pic.yearmonth = entry.yearmonth AND pic.profile_id = entry.profile_id)
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2 - AWARDS
	$sql = <<<SQL
		UPDATE {entry}
		   SET awards = (
					SELECT COUNT(*) FROM {pic_result}, award
					 WHERE pic_result.yearmonth = entry.yearmonth
					   AND pic_result.profile_id = entry.profile_id
					   AND award.yearmonth = pic_result.yearmonth
					   AND award.section != 'CONTEST'
					   AND award.award_type = 'pic'
					   AND award.award_id = pic_result.award_id
					   AND award.level < 9
						)
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3 - HMS
	$sql = <<<SQL
		UPDATE {entry}
		   SET hms = (
					SELECT COUNT(*) FROM {pic_result}, award
					 WHERE pic_result.yearmonth = entry.yearmonth
					   AND pic_result.profile_id = entry.profile_id
					   AND award.yearmonth = pic_result.yearmonth
					   AND award.section != 'CONTEST'
					   AND award.award_type = 'pic'
					   AND award.award_id = pic_result.award_id
					   AND award.level = 9
						)
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4 - ACCEPTANCES
	$sql = <<<SQL
		UPDATE {entry}
		   SET acceptances = (
					SELECT COUNT(*) FROM {pic_result}, award
					 WHERE pic_result.yearmonth = entry.yearmonth
					   AND pic_result.profile_id = entry.profile_id
					   AND award.yearmonth = pic_result.yearmonth
					   AND award.section != 'CONTEST'
					   AND award.award_type = 'pic'
					   AND award.award_id = pic_result.award_id
					   AND award.level = 99
						)
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5 - TOTAL SCORE
// 	$sql = <<<SQL
// 		UPDATE {entry}
// 		   SET score = acceptances + (hms * 2) + (awards * 3)
// 		 WHERE yearmonth = '$stat_yearmonth'
// SQL;
	// Revised scoring model using award_weight
	$sql = <<<SQL
		UPDATE {entry}
		   SET entry.score = (
			   SELECT IFNULL(sum(award_weight), 0) FROM award, {pic_result}
			    WHERE award.yearmonth = entry.yearmonth
				  AND award.award_type = 'pic'
				  AND award.section != 'CONTEST'
				  AND award.award_weight > 0
				  AND pic_result.yearmonth = award.yearmonth
				  AND pic_result.award_id = award.award_id
				  AND pic_result.profile_id = entry.profile_id
			   )
		 WHERE entry.yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6 - Update Section Statistics
	// Section is common across all award groups. Hence overall numbers are updated (ALL AGES + YOUTH)
	// 6.0 - Clear all data
	$sql = <<<SQL
		UPDATE section
		   SET num_entrants = 0
		     , num_pictures = 0
			 , num_awards = 0
			 , num_hms = 0
			 , num_acceptances = 0
			 , num_winners = 0
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.1 - Number of people participating
	$sql = <<<SQL
		UPDATE section
		   SET num_entrants = (
				SELECT COUNT(DISTINCT pic.profile_id)
				  FROM {pic}
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.2 - Number of Uploads
	$sql = <<<SQL
		UPDATE section
		   SET num_pictures = (
				SELECT COUNT(*)
				  FROM {pic}
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.3 - Number of Pictures winning awards
	$sql = <<<SQL
		UPDATE section
		   SET num_awards = (
				SELECT COUNT(*)
				  FROM {pic}, {pic_result}, award
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				   AND pic_result.yearmonth = pic.yearmonth
				   AND pic_result.profile_id = pic.profile_id
				   AND pic_result.pic_id = pic.pic_id
				   AND award.yearmonth = pic_result.yearmonth
				   AND award.award_id = pic_result.award_id
				   AND award.award_type = 'pic'
				   AND award.level < 9
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.4 - Number of Pictures winning certificates
	$sql = <<<SQL
		UPDATE section
		   SET num_hms = (
				SELECT COUNT(*)
				  FROM {pic}, {pic_result}, award
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				   AND pic_result.yearmonth = pic.yearmonth
				   AND pic_result.profile_id = pic.profile_id
				   AND pic_result.pic_id = pic.pic_id
				   AND award.yearmonth = pic_result.yearmonth
				   AND award.award_id = pic_result.award_id
				   AND award.award_type = 'pic'
				   AND award.level = 9
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.5 - Number of Pictures winning awards
	$sql = <<<SQL
		UPDATE section
		   SET num_acceptances = (
				SELECT COUNT(*)
				  FROM {pic}, {pic_result}, award
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				   AND pic_result.yearmonth = pic.yearmonth
				   AND pic_result.profile_id = pic.profile_id
				   AND pic_result.pic_id = pic.pic_id
				   AND award.yearmonth = pic_result.yearmonth
				   AND award.award_id = pic_result.award_id
				   AND award.award_type = 'pic'
				   AND award.level = 99
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 6.5 - Number of Winners
	$sql = <<<SQL
		UPDATE section
		   SET num_winners = (
				SELECT COUNT(DISTINCT pic.profile_id)
				  FROM {pic}, {pic_result}, award
				 WHERE pic.yearmonth = section.yearmonth
				   AND pic.section = section.section
				   AND pic_result.yearmonth = pic.yearmonth
				   AND pic_result.profile_id = pic.profile_id
				   AND pic_result.pic_id = pic.pic_id
				   AND award.yearmonth = pic_result.yearmonth
				   AND award.award_id = pic_result.award_id
				   AND award.award_type = 'pic'
				)
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	return true;
}
// END OF prepare_pic_entry


//
// Generate Statistics for "Participation By Section"
//
function participation_by_section ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	//
	// 1. Statistics by Section
	// ========================
	// stat_segment = 'Participation by Section'
	//

	// Delete any existing statistics
	$sql = <<<SQL
		DELETE FROM stats_participation
		WHERE yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// SQL to generate statistics

	// Step 1 - Initialize rows with section names
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence)
		SELECT '$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', section, 1
		FROM section
		WHERE yearmonth = '$stat_yearmonth'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// & Total Row
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_total_row)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', 'TOTAL', 1, 999, 1)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2 - Part A - Update Number of people  who uploaded pictures By Section
	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
				SELECT COUNT(DISTINCT pic.profile_id)
				  FROM {pic}, {entry}, entrant_category
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND pic.section = stats_participation.stat_row
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2 - Part B - Number of people who uploaded pictures across all sections
	// Note this is not a total of participants in each section as many people participate across many sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_participated = (
				SELECT COUNT(DISTINCT pic.profile_id)
				  FROM {pic}, {entry}, entrant_category
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3 - Part A - Update Number of Pictures Uploaded under each section
	$sql  = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND pic.section = stats_participation.stat_row
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3 - Part B - Pictures uploaded across all sections
	// Note this is not a total of participants in each section as many people participate across many sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4 - Part A - Update Number of Pictures winning Awards by Section
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic.section = stats_participation.stat_row
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4 - Part B - Update Number of Pictures winning Awards across all Sections
	// Note this is not a total of participants in each section as many people participate across many sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5 - Part A - Update Number of Pictures winning Honorable Mentions / Certificates by section
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic.section = stats_participation.stat_row
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5 - Part B - Update Number of Pictures winning Honorable Mentions / Certificates across all sections
	// Note this is not a total of participants in each section as many people participate across many sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6 - Part A - Update Number of Pictures winning Acceptances for each sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic.section = stats_participation.stat_row
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6 - Part B - Update Number of Pictures winning Acceptances across all sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7 - Part A - Update Number of Winners - Winning Award or HM or Acceptance by each sections
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic.section = stats_participation.stat_row
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7 - Part B - Update Number of Winners - Winning Award or HM or Acceptance across all sections
	// & Total Row
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update Section Sequence
	// $section_sequence = array('MONOCHROME OPEN PRINT', 'COLOR OPEN PRINT', 'MONOCHROME OPEN DIGITAL', 'COLOR OPEN DIGITAL', 'NATURE DIGITAL', 'TRAVEL DIGITAL');
	// for ($i = 0; $i < sizeof($section_sequence); ++$i) {
		// $sql  = "UPDATE stats_participation ";
		// $sql .= "SET stat_row_sequence = '" . ($i + 1) . "' ";
		// $sql .= "WHERE stats_participation.yearmonth = '$stat_yearmonth' ";
		// $sql .= "  AND stat_category = '$stat_category' ";
		// $sql .= "  AND stat_segment = '$stat_segment' ";
		// $sql .= "  AND stat_row = '" . $section_sequence[$i] ."' ";
		// mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// }
	$sql = <<<SQL
		UPDATE stats_participation, section
		   SET stat_row_sequence = section.section_sequence
		 WHERE stats_participation.yearmonth = '$stat_yearmonth'
		   AND award_group = '$award_group'
		   AND stat_category = '$stat_category'
		   AND stat_segment = '$stat_segment'
		   AND section.yearmonth = stats_participation.yearmonth
		   AND section.section = stat_row
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Finally embed cut_off_score incto section name
	$sql = <<<SQL
		UPDATE stats_participation, section
		   SET stat_row = CONCAT(stat_row, '<br><small>[ CUTOFF SCORE : ', section.cut_off_score, ' ]</small>')
		 WHERE stats_participation.yearmonth = '$stat_yearmonth'
		   AND award_group = '$award_group'
		   AND stat_category = '$stat_category'
		   AND stat_segment = '$stat_segment'
		   AND section.yearmonth = stats_participation.yearmonth
		   AND section.section = stat_row
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	return true;
}

//
// 2. Statistics by Country
// ========================
//
// Generate Statistics for "Participation By Country"
//
function participation_by_country ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	//
	// DELETE existing Data
	//
	$sql = <<<SQL
		DELETE FROM stats_participation
		WHERE yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Step 1 - Initialize rows with country names
	//          1. Insert India so that it appears on top for all statistics
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence)
		SELECT '$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', country.country_name, '$stat_segment_sequence', 1
		  FROM country
		 WHERE country.country_id = '101'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Step 1 - Initialize rows with country names
	//          2. Insert Other participating countries
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence)
		SELECT '$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', country.country_name, '$stat_segment_sequence', 0
		  FROM country
		 WHERE country.country_id IN (
		 			SELECT DISTINCT country_id
					  FROM {pic}, {entry}, entrant_category, profile
					 WHERE pic.yearmonth = '$stat_yearmonth'
					   AND entry.yearmonth = pic.yearmonth
					   AND entry.profile_id = pic.profile_id
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = '$award_group'
					   AND profile.profile_id = entry.profile_id
					)
		   AND country.country_id != '101'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_total_row)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', 'TOTAL', 2, 999, 1)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2 - Update Number of people Registered
	/***** Not possible to get this information from archive tables **********
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry, country, ar_pic
			WHERE entry.country = country.id
			  AND stats_participation.stat_row = country.name
			  AND ar_pic.yearmonth = '$stat_yearmonth'
			  AND entry.entry_id = ar_pic.entry_id
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2.A - Update Totals row with number of people registered
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry, ar_pic
			WHERE ar_pic.yearmonth = '$stat_yearmonth'
			  AND ar_pic.entry_id = entry.entry_id
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	************/


	// Step 3 - Update Number of people participated

	$sql = <<<SQL
		UPDATE stats_participation
  		   SET stat_participated = (
					SELECT COUNT(*) FROM {entry}, entrant_category, profile, country
					WHERE entry.yearmonth = stats_participation.yearmonth
					  AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = stats_participation.yearmonth)
					  AND entrant_category.yearmonth = entry.yearmonth
					  AND entrant_category.entrant_category = entry.entrant_category
					  AND entrant_category.award_group = stats_participation.award_group
					  AND profile.profile_id = entry.profile_id
					  AND country.country_id = profile.country_id
					  AND country.country_name = stats_participation.stat_row
					)
		 WHERE stats_participation.yearmonth = '$stat_yearmonth'
		   AND award_group = '$award_group'
		   AND stat_category = '$stat_category'
		   AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// 3.A - Update Totals row with number of people participated
	$sql = <<<SQL
		UPDATE stats_participation
  		   SET stat_participated = (
					SELECT COUNT(*) FROM {entry}, entrant_category, profile, country
					WHERE entry.yearmonth = stats_participation.yearmonth
					  AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = stats_participation.yearmonth)
					  AND entrant_category.yearmonth = entry.yearmonth
					  AND entrant_category.entrant_category = entry.entrant_category
					  AND entrant_category.award_group = stats_participation.award_group
					  AND profile.profile_id = entry.profile_id
					  AND country.country_id = profile.country_id
					)
		 WHERE stats_participation.yearmonth = '$stat_yearmonth'
		   AND award_group = '$award_group'
		   AND stat_category = '$stat_category'
		   AND stat_segment = '$stat_segment'
		   AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 4 - Update Number of Pictures Uploaded

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile, country
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND country.country_id = profile.country_id
				   AND country.country_name = stats_participation.stat_row
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4.A - Update Totals with number of pictures updated
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile, country
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND country.country_id = profile.country_id
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 5 - Update Number of Pictures winning Awards

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					  AND country.country_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//Step 5.A Update Totals row with number of pictures winning awards
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 6 - Update Number of Pictures winning Honorable Mentions / Certificates

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					  AND country.country_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6.A - Update Total Rows with number of pictures with Honorable Mention
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7 - Update Number of Pictures winning Acceptances

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					  AND country.country_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7.A - Update Total row with number of pictures winning acceptance
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8 - Update Number of Winners

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					  AND country.country_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8.A - Update Total Row with Number of Winners
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile, country
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND country.country_id = profile.country_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Update Sequence based on performance
	$sql = <<<SQL
		SELECT * FROM stats_participation
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence != '1'
		  AND stat_row_sequence != '999'
		ORDER BY stat_total_score DESC
SQL;
	$stat_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row_sequence = 1;
	while($stat_row = mysqli_fetch_array($stat_query)) {
		++ $row_sequence;
		$sql  = "UPDATE stats_participation ";
		$sql .= "SET stat_row_sequence = '$row_sequence' ";
		$sql .= "WHERE stats_participation.yearmonth = '$stat_yearmonth' ";
		$sql .= "  AND award_group = '$award_group' ";
		$sql .= "  AND stat_category = '$stat_category' ";
		$sql .= "  AND stat_segment = '$stat_segment' ";
		$sql .= "  AND stat_row = '" . $stat_row['stat_row'] ."' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	return true;

}	// end of statistics by country


//
// 3. Participation by club
// =======================
// 6-Dec-2020 : Added support to generate statistics only for Clubs and not for groups
//
function participation_by_club ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	$yps_club = "YOUTH PHOTOGRAPHIC SOCIETY, BENGALURU";

	//
	// DELETRE exiusting Statistics
	//
	$sql = <<<SQL
		DELETE FROM stats_participation
		WHERE yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 1 - Initialize rows with club names
	//
	// Intert YPS, Others, Total
	// --
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', '$yps_club', '$stat_segment_sequence', 1)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Insert Other Clubs
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence)
		SELECT '$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', club.club_name, '$stat_segment_sequence', 0
		  FROM club
		 WHERE club.club_type = 'CLUB'
		   AND club.club_id IN (
		 			SELECT DISTINCT profile.club_id
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = '$stat_yearmonth'
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = '$stat_yearmonth')
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = '$award_group'
					   AND profile.profile_id = entry.profile_id
					   AND profile.club_id = club.club_id
					)
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Insert - Non Club Participants
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_total_row)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', 'INDIVIDUAL (NO CLUB)', '$stat_segment_sequence', 998, 0)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Insert Total
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_total_row)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', 'TOTAL', '$stat_segment_sequence', 999, 1)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 2 - Update Number of people Registered
	/********** Not possible to get this information from archive tables **********
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry, ar_pic
			WHERE stats_participation.stat_row = club
			  AND ar_pic.yearmonth = '$stat_yearmonth'
			  AND entry.entry_id = ar_pic.entry_id
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2.A - Update Total Row with number of people registered
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry, ar_pic
			WHERE entry.entry_id = ar_pic.entry_id
			  AND ar_pic.yearmonth = '$stat_yearmonth'
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	****************/

	// Step 3 - Update Number of people participated - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile, club
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					   AND profile.club_id = club.club_id
					   AND club.club_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3.A - Update Number of people participated - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					   AND profile.yps_login_id != ''
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3.B - Update Number of people participated - NON_CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					   AND profile.yps_login_id = ''
					   AND ( profile.club_id = '0'
						     OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB')
					       )
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3.C - Update Total row with number of people participated
	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 4 - Update Number of Pictures Uploaded - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile, club
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND club.club_id = profile.club_id
				   AND club.club_name = stats_participation.stat_row
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4.A - Update Number of Pictures Uploaded - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND profile.yps_login_id != ''
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4.B - Update Number of Pictures Uploaded - NON-CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND profile.yps_login_id = ''
				   AND ( profile.club_id = 0
					     OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB')
					   )
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4.C - Update Total Row with Number of Pictures Uploaded
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
  		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5 - Update Number of Pictures winning Awards - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, club
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND club.club_id = profile.club_id
					  AND club.club_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5.A - Update Number of Pictures winning Awards - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id != ''
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5.B - Update Number of Pictures winning Awards - NON-CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id = ''
					  AND ( profile.club_id = '0'
						    OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB' )
						  )
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5.C - Update Total Row with Number of Pictures Winning Awards
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6 - Update Number of Pictures winning HM - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, club
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND club.club_id = profile.club_id
					  AND club.club_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6.A - Update Number of Pictures winning HM - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id != ''
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6.B - Update Number of Pictures winning HMs - NON-CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id = ''
					  AND ( profile.club_id = '0'
						    OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB')
						  )
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6.C - Update Total Row with Number of Pictures Winning HMs
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 7 - Update Number of Pictures winning Acceptance - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile, club
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND club.club_id = profile.club_id
					  AND club.club_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7.A - Update Number of Pictures winning Acceptance - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id != ''
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7.B - Update Number of Pictures winning Acceptance - NON-CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id = ''
					  AND ( profile.club_id = '0'
						    OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB')
						  )
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7.C - Update Total Row with Number of Pictures Winning Acceptance
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 8 - Update Number of Winners - NON-YPS

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile, club
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND club.club_id = profile.club_id
					  AND club.club_name = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8.A - Update Number of Winners - YPS
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id != ''
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = '$yps_club'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8.B - Update Number of Winners - NON-CLUB
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND profile.yps_login_id = ''
					  AND ( profile.club_id = '0'
						    OR profile.club_id IN (SELECT club_id FROM club WHERE club_type != 'CLUB')
						  )
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence = '998'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8.C - Update Total Row with Number of Winners
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 9 - Update Total_score

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_total_score = ((stat_awards * 3) + (stat_hms * 2) + stat_acceptances)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);



	// Step 10 - Assign sequence based on performance
	$sql = <<<SQL
		SELECT * FROM stats_participation
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row_sequence != '1'
		  AND stat_row_sequence != '998'
		  AND stat_row_sequence != '999'
		ORDER BY stat_total_score DESC
SQL;
	$stat_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row_sequence = 1;
	while($stat_row = mysqli_fetch_array($stat_query)) {
		++ $row_sequence;
		$sql  = "UPDATE stats_participation ";
		$sql .= "SET stat_row_sequence = '$row_sequence' ";
		$sql .= "WHERE stats_participation.yearmonth = '$stat_yearmonth' ";
		$sql .= "  AND award_group = '$award_group' ";
		$sql .= "  AND stat_category = '$stat_category' ";
		$sql .= "  AND stat_segment = '$stat_segment' ";
		$sql .= "  AND stat_row = '" . mysqli_escape_string($DBCON, $stat_row['stat_row']) ."' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	return true;

}		// End of Participation by club

//
// 4. Participation by Gender
// =======================
//
function participation_by_gender ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	//
	// DELETE existing rows
	//
	$sql = <<<SQL
		DELETE FROM stats_participation
		WHERE yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	//
	// Step 1 - Initialize rows with gender names
	//
	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence)
		SELECT DISTINCT '$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', gender, '$stat_segment_sequence'
		FROM entry, entrant_category, profile
		WHERE entry.yearmonth = '$stat_yearmonth'
		  AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth')
		  AND entrant_category.yearmonth = entry.yearmonth
		  AND entrant_category.entrant_category = entry.entrant_category
		  AND entrant_category.award_group = '$award_group'
		  AND profile.profile_id = entry.profile_id
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Total Row

	$sql = <<<SQL
		INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_total_row)
		VALUES('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment', 'TOTAL', '$stat_segment_sequence', 999, 1)
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2 - Update Number of people Registered
	/************* Not possible to get this information from archive tables ***********
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry
			WHERE stats_participation.stat_row = gender
			  AND entry_id IN (SELECT DISTINCT entry_id IN ar_pic WHERE ar_pic.yearmonth = '$stat_yearmonth')
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 2.1 - Update Total
	$sql = <<<SQL
UPDATE stats_participation
SET stat_registered = (
			SELECT COUNT(*) FROM entry
			WHERE entry_id IN (SELECT DISTINCT entry_id FROM ar_pic WHERE ar_pic.yearmonth = '$stat_yearmonth'
			)
WHERE stats_participation.yearmonth = '$stat_yearmonth'
  AND stat_category = '$stat_category'
  AND stat_segment = '$stat_segment'
  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	****************/

	// Step 3 - Update Number of people participated

	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					   AND profile.gender = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 3.1 - Update Totals Row

	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_participated = (
		 			SELECT COUNT(*)
					  FROM {entry}, entrant_category, profile
					 WHERE entry.yearmonth = stats_participation.yearmonth
					   AND entry.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE yearmonth = stats_participation.yearmonth)
					   AND entrant_category.yearmonth = entry.yearmonth
					   AND entrant_category.entrant_category = entry.entrant_category
					   AND entrant_category.award_group = stats_participation.award_group
					   AND profile.profile_id = entry.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 4 - Update Number of Pictures Uploaded
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				   AND profile.gender = stats_participation.stat_row
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 4.A - Update Total Number of Pictures Uploaded
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_pictures = (
				SELECT COUNT(*)
				  FROM {pic}, {entry}, entrant_category, profile
				 WHERE pic.yearmonth = stats_participation.yearmonth
				   AND entry.yearmonth = pic.yearmonth
				   AND entry.profile_id = pic.profile_id
				   AND entrant_category.yearmonth = entry.yearmonth
				   AND entrant_category.entrant_category = entry.entrant_category
				   AND entrant_category.award_group = stats_participation.award_group
				   AND profile.profile_id = entry.profile_id
				)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 5 - Update Number of Pictures winning Awards

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.gender = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 5.1 - Update Total Row

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_awards = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level < 9
					  AND profile.profile_id = pic.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);


	// Step 6 - Update Number of Pictures winning Honorable Mentions / Certificates

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					  AND profile.gender = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 6.1 - Update Total Row
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_hms = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 9
					  AND profile.profile_id = pic.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7 - Update Number of Pictures winning Honorable Mentions / Certificates

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					  AND profile.gender = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 7.1 - Update Total Row
	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_acceptances = (
					SELECT COUNT(*) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND award.level = 99
					  AND profile.profile_id = pic.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8 - Update Number of Winners

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					  AND profile.gender = stats_participation.stat_row
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 8.1 - Update Total Row

	$sql = <<<SQL
		UPDATE stats_participation
		SET stat_winners = (
					SELECT COUNT(DISTINCT pic.profile_id) FROM {pic}, award, {pic_result}, profile
					WHERE pic.yearmonth = stats_participation.yearmonth
					  AND pic_result.yearmonth = pic.yearmonth
					  AND pic_result.profile_id = pic.profile_id
					  AND pic_result.pic_id = pic.pic_id
					  AND award.yearmonth = pic_result.yearmonth
					  AND award.award_id = pic_result.award_id
					  AND award.award_group = stats_participation.award_group
					  AND award.section != 'CONTEST'
					  AND award.award_type = 'pic'
					  AND profile.profile_id = pic.profile_id
					)
		WHERE stats_participation.yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
		  AND stat_segment = '$stat_segment'
		  AND stat_row = 'TOTAL'
SQL;
	mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Step 9 - Update Total_score

	$sql = <<<SQL
		UPDATE stats_participation
		   SET stat_total_score = ((stat_awards * 3) + (stat_hms * 2) + stat_acceptances)
		 WHERE stats_participation.yearmonth = '$stat_yearmonth'
		   AND award_group = '$award_group'
		   AND stat_category = '$stat_category'
		   AND stat_segment = '$stat_segment'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	return true;

}		// End of Participation By Gender

//
// 5. TOP 5 YPS Entrants
// =======================
//
function top_entrants ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	//
	// DELETE existing rows under TOP Category
	//
	$sql = <<<SQL
		DELETE FROM stats_participation
		WHERE yearmonth = '$stat_yearmonth'
		  AND award_group = '$award_group'
		  AND stat_category = '$stat_category'
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// *********
	// Create Segments from Entrant Categories
	// *********
	$sql  = "SELECT entrant_category, entrant_category_name ";
	$sql .= "  FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$stat_yearmonth' ";
	$sql .= "   AND award_group = '$award_group' ";
	$top5query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$segment_sequence = 0;
	while ($top5row = mysqli_fetch_array($top5query)) {
		$entrant_category = $top5row['entrant_category'];
		++ $segment_sequence;
		$stat_segment_name = str_replace("[category]", $top5row['entrant_category_name'], $stat_segment);

		// Select Top 5 Performers in the Category
		$sql = <<<SQL
			SELECT profile_name, profile.profile_id, uploads, awards, hms, acceptances, score
			  FROM {entry}, profile
			 WHERE entry.yearmonth = '$stat_yearmonth'
			   AND entry.entrant_category = '$entrant_category'
			   AND profile.profile_id = entry.profile_id
			   AND score > 0
			 ORDER BY score DESC, profile.profile_name
			 LIMIT 5
SQL;
		$query = mysqli_query($DBCON, ar_fix($sql)) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row_sequence = 0;
		while ($row = mysqli_fetch_array($query)) {
			++ $row_sequence;
			$profile_name = $row['profile_name'];
			$profile_id = $row['profile_id'];
			$uploads = $row['uploads'];
			$awards = $row['awards'];
			$hms = $row['hms'];
			$acceptances = $row['acceptances'];
			$score = $row['score'];
			$sql  = "INSERT INTO stats_participation (yearmonth, award_group, stat_category, stat_segment, stat_row, ";
			$sql .= "									stat_segment_sequence, stat_row_sequence, stat_profile_id, stat_pictures, ";
			$sql .= "									stat_awards, stat_hms, stat_acceptances, stat_total_score) ";
			$sql .= "VALUES ('$stat_yearmonth', '$award_group', '$stat_category', '$stat_segment_name', '$profile_name', ";
			$sql .= "        '$segment_sequence', '$row_sequence', '$profile_id', '$uploads', ";
			$sql .= "        '$awards', '$hms', '$acceptances', '$score') ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}	// while ($row = mysqli_fetch_array($query))
	} 		// while ($top5row = mysqli_fetch_array($top5query)

	return true;
}


//
// Stat Bits
//
// 6. ANALYSIS BY AGE
// ==================
//
function stats_bits ($stat_yearmonth, $award_group, $stat_category, $stat_segment, $stat_segment_sequence) {

	global $DBCON;

	$stat_segment = 'Analysis By Age';
	$stat_segment_sequence = 1;

	// Delete existing rows
	$sql = <<<SQL
		DELETE FROM stats_bits
		WHERE yearmonth = '$stat_yearmonth'
		  AND stat_segment = '$stat_segment';
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// YOUNGEST
	$stat_row_sequence = 1;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, profile_id, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Youngest Participant', '$stat_segment_sequence', '$stat_row_sequence', profile_id,
			   IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) AS age
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
		 ORDER BY age
		 LIMIT 1
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// SENIOR MOST
	++ $stat_row_sequence;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, profile_id, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Senior Most Participant', '$stat_segment_sequence', '$stat_row_sequence', profile_id,
			   IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) AS age
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
		 ORDER BY age DESC
		 LIMIT 1
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Number of participants < 18 years of age
	++ $stat_row_sequence;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Number of Junior Participants (under 18 years)', '$stat_segment_sequence', '$stat_row_sequence', COUNT(*)
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) <= 18
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Number of Participants > 60 years of age
	++ $stat_row_sequence;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Number of Senior Participants (above 60 years)', '$stat_segment_sequence', '$stat_row_sequence', COUNT(*)
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) > 60
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Number of participants > 80 years of age
	++ $stat_row_sequence;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Number of Super Senior Participants (above 80 years)', '$stat_segment_sequence', '$stat_row_sequence', COUNT(*)
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) > 80
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Average age
	++ $stat_row_sequence;
	$sql = <<<SQL
		INSERT INTO stats_bits (yearmonth, stat_segment, stat_row, stat_segment_sequence, stat_row_sequence, stat_value)
		SELECT '$stat_yearmonth', '$stat_segment', 'Average age of participants', '$stat_segment_sequence', '$stat_row_sequence',
				ROUND(AVG(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth)))), 0)
		  FROM profile
		 WHERE profile.profile_id IN (SELECT DISTINCT profile_id FROM pic WHERE pic.yearmonth = '$stat_yearmonth' )
		   AND IFNULL(YEAR(FROM_DAYS(DATEDIFF(NOW(), date_of_birth))), 45) BETWEEN 6 AND 120
SQL;
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	return true;
}


//
// RUN Statistics
// ============================
//
if ( isset($_REQUEST['generate_stats']) && isset($_REQUEST['run']) && sizeof($_REQUEST['run']) > 0 &&
 	 isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {

	$yearmonth = $_SESSION['admin_yearmonth'];

	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$translate_table = [];
	if ($contest_archived) {
		$translate_table = array("{entry}" => "ar_entry entry", "{pic}" => "ar_pic pic",
									"{pic_result}" => "ar_pic_result pic_result", "{rating}" => "ar_rating rating");
	}
	else {
		$translate_table = array("{entry}" => "entry", "{pic}" => "pic",
									"{pic_result}" => "pic_result", "{rating}" => "rating");
	}

	$errors = "";
	foreach ($_REQUEST['run'] as $run_code) {
		list($category, $award_group, $segment, $sequence, $routine) = explode("|", $run_code);
		if ( ! $routine ($yearmonth, $award_group, $category, $segment, $sequence) )
			$errors .= ($errors == "" ? "" : ", ") . $run['segment'] . " for " . $award_group;
	}
	if ($errors != "") {
		return_error( "Errors in generating stats for " . $errors );
	}
	$_SESSION['success_msg'] = "Statistics generated for selected items !";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	return_error("Invalid Parameters !");
}


?>

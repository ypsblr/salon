<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function error_exit($errmsg, $phpfile, $phpline, $logout = false) {
    $_SESSION['err_msg'] = $errmsg;
	log_error($errmsg, $phpfile, $phpline);
	header("Location: /jurypanel/projector_home.php");
	print("<script>location.href='/jurypanel/projector_home.php'</script>");
	die($errmsg);
}

function sql_error_exit($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	$_SESSION['err_msg'] = "SQL Operation failed. Please report to YPS to check using Contact Us page.";
    header("Location: /jurypanel/projector_home.php");
	print("<script>location.href='/jurypanel/projector_home.php'</script>");
	die($errmsg);
}


if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type'] == "PROJECTOR" && isset($_SESSION['jury_yearmonth']) ) {

	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	// Basic Checks
	// 1. All pics have been scored
	$sql  = "SELECT COUNT(*) AS missing_scores ";
	$sql .= "  FROM pic, assignment ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.yearmonth = pic.yearmonth ";
	$sql .= "   AND assignment.section = pic.section ";
	$sql .= "   AND CONCAT_WS('|', pic.profile_id, pic.pic_id, assignment.user_id) NOT IN ( ";
	$sql .= "       SELECT CONCAT_WS('|', rating.profile_id, rating.pic_id, rating.user_id) ";
	$sql .= "         FROM rating WHERE rating.yearmonth = '$jury_yearmonth' ";
	$sql .= "       ) ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['missing_scores'] != '0') {
		error_exit($row['missing_scores'] . " scores are missing ", __FILE__, __LINE__);
	}

	// 2. All awards have been assigned
	$sql  = "SELECT COUNT(*) AS not_assigned FROM award ";
	$sql .= " WHERE award.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.award_id NOT IN ( ";
	$sql .= "       SELECT DISTINCT pic_result.award_id FROM pic_result ";
	$sql .= "        WHERE pic_result.yearmonth = award.yearmonth ";
	$sql .= "       ) ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['not_assigned'] != '0') {
		error_exit($row['not_assigned'] . " awards have not been assigned to pictures", __FILE__, __LINE__);
	}


	// Perform Actions
	// update_pic
	if (isset($_REQUEST['update_pic'])) {
		$sql  = "UPDATE pic ";
		$sql .= "   SET total_rating = ( ";
		$sql .= "       SELECT IFNULL(SUM(rating), 0) FROM rating ";
		$sql .= "        WHERE rating.yearmonth = pic.yearmonth ";
		$sql .= "          AND rating.profile_id = pic.profile_id ";
		$sql .= "          AND rating.pic_id = pic.pic_id ) ";
		$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Update Entry
	if (isset($_REQUEST['update_entry'])) {
		// Create Transaction
		$sql = "START TRANSACTION";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Fix number of uploads
		$sql  = "UPDATE entry ";
		$sql .= "   SET uploads = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic ";
		$sql .= "        WHERE pic.yearmonth = entry.yearmonth ";
		$sql .= "          AND pic.profile_id = entry.profile_id ) ";
		$sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update number of awards
		$sql  = "UPDATE entry ";
		$sql .= "   SET awards = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE pic_result.yearmonth = entry.yearmonth ";
		$sql .= "          AND pic_result.profile_id = entry.profile_id ";
		$sql .= "          AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "          AND award.award_id = pic_result.award_id ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level < 9 ) ";
		$sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update number of Honorable Mentions
		$sql  = "UPDATE entry ";
		$sql .= "   SET hms = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE pic_result.yearmonth = entry.yearmonth ";
		$sql .= "          AND pic_result.profile_id = entry.profile_id ";
		$sql .= "          AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "          AND award.award_id = pic_result.award_id ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level = 9 ) ";
		$sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update number of Acceptances
		$sql  = "UPDATE entry ";
		$sql .= "   SET acceptances = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE pic_result.yearmonth = entry.yearmonth ";
		$sql .= "          AND pic_result.profile_id = entry.profile_id ";
		$sql .= "          AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "          AND award.award_id = pic_result.award_id ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level = 99 ) ";
		$sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Update number of Score
		// $sql  = "UPDATE entry ";
		// $sql .= "   SET score = (3 * awards) + (2 * hms) + acceptances ";
		// $sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		$sql  = "UPDATE entry ";
		$sql .= "   SET entry.score = ( ";
		$sql .= "          SELECT IFNULL(SUM(award_weight), 0) FROM award, pic_result ";
		$sql .= "           WHERE award.yearmonth = entry.yearmonth ";
		$sql .= "             AND award.award_type = 'pic' ";
		$sql .= "             AND award.section != 'CONTEST' ";					// Contest Level awards do not carry weight
		$sql .= "             AND award.award_weight > 0 ";
		$sql .= "             AND pic_result.yearmonth = award.yearmonth ";
		$sql .= "             AND pic_result.award_id = award.award_id ";
		$sql .= "             AND pic_result.profile_id = entry.profile_id ";
		$sql .= "       ) ";
		$sql .= "WHERE entry.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Commit Transaction
		$sql = "COMMIT";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Update Section
	if (isset($_REQUEST['update_section'])) {
		// Create Transaction
		$sql = "START TRANSACTION";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of Entrants
		$sql  = "UPDATE section ";
		$sql .= "   SET num_entrants = ( ";
		$sql .= "       SELECT COUNT(DISTINCT profile_id) FROM pic ";
		$sql .= "        WHERE pic.yearmonth = section.yearmonth ";
		$sql .= "          AND pic.section = section.section ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of Winners
		$sql  = "UPDATE section ";
		$sql .= "   SET num_winners = ( ";
		$sql .= "       SELECT COUNT(DISTINCT pic.profile_id) FROM pic, entry ";
		$sql .= "        WHERE pic.yearmonth = section.yearmonth ";
		$sql .= "          AND pic.section = section.section ";
		$sql .= "          AND entry.yearmonth = pic.yearmonth ";
		$sql .= "          AND entry.profile_id = pic.profile_id ";
		$sql .= "          AND entry.score > 0 ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of Pictures
		$sql  = "UPDATE section ";
		$sql .= "   SET num_pictures = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic ";
		$sql .= "        WHERE pic.yearmonth = section.yearmonth ";
		$sql .= "          AND pic.section = section.section ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of Awards
		$sql  = "UPDATE section ";
		$sql .= "   SET num_awards = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE award.yearmonth = section.yearmonth ";
		$sql .= "          AND award.section = section.section ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level < 9 ";
		$sql .= "          AND pic_result.yearmonth = award.yearmonth ";
		$sql .= "          AND pic_result.award_id = award.award_id ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of HMs
		$sql  = "UPDATE section ";
		$sql .= "   SET num_hms = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE award.yearmonth = section.yearmonth ";
		$sql .= "          AND award.section = section.section ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level = 9 ";
		$sql .= "          AND pic_result.yearmonth = award.yearmonth ";
		$sql .= "          AND pic_result.award_id = award.award_id ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Number of Awards
		$sql  = "UPDATE section ";
		$sql .= "   SET num_acceptances = ( ";
		$sql .= "       SELECT COUNT(*) FROM pic_result, award ";
		$sql .= "        WHERE award.yearmonth = section.yearmonth ";
		$sql .= "          AND award.section = section.section ";
		$sql .= "          AND award.award_type = 'pic' ";
		$sql .= "          AND award.level = 99 ";
		$sql .= "          AND pic_result.yearmonth = award.yearmonth ";
		$sql .= "          AND pic_result.award_id = award.award_id ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Cut-off-score
		$sql  = "UPDATE section ";
		$sql .= "   SET cut_off_score = ( ";
		$sql .= "       SELECT IFNULL(MIN(total_rating), 0) FROM pic, pic_result, award ";
		$sql .= "        WHERE pic.yearmonth = section.yearmonth ";
		$sql .= "          AND pic.section = section.section ";
		$sql .= "          AND pic_result.yearmonth = pic.yearmonth ";
		$sql .= "          AND pic_result.profile_id = pic.profile_id ";
		$sql .= "          AND pic_result.pic_id = pic.pic_id ";
		$sql .= "          AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "          AND award.award_id = pic_result.award_id ";
		$sql .= "          AND award.section != 'CONTEST' ";				// Ignore ratings for contest level awards
		$sql .= "       ) ";
		$sql .= " WHERE section.yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Commit Transaction
		$sql = "COMMIT";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	}

    // Assign Award Sponsors to Results
    if (isset($_REQUEST['assign_sponsors'])) {
        $num_updates = 0;

        // Create Transaction
        $sql = "START TRANSACTION";
        mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);

        // Get a List of Awards
    	$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' ";
    	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

    	while ($row = mysqli_fetch_array($query)) {

    		$award_id = $row['award_id'];

    		// Get Sponsorship records for the award and assign them to sponsorship slots
    		$sql  = "SELECT * FROM sponsorship ";
    		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    		$sql .= "   AND sponsorship_type = 'AWARD' ";
    		$sql .= "   AND link_id = '$award_id' ";
    		$sql .= " ORDER BY sponsorship_no ";
    		$spq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    		if (mysqli_num_rows($spq) > 0) {

    			// Create a table of rows for the award, expanding the number_of_units to separate rows
                // This is used later to assign the sponsirship_no to the results
    			$sponsorship_slot = array();
    			while ($spr = mysqli_fetch_array($spq)) {
                    // create number of rows equivalent to number_of_units
    				for ($i = 0; $i < $spr['number_of_units']; ++$i)
    					$sponsorship_slot[] = $spr['sponsorship_no'];
    			}

    			// Fetch relevant results
    			switch ($row['award_type']) {
                    // Picture Results
    				case 'pic' : {
                        $sql = "SELECT * FROM pic_result WHERE yearmonth = '$jury_yearmonth' AND award_id = '$award_id' ";

    					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

    					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {

    						if (isset($sponsorship_slot[$row_no])) {
    							$sql  = "UPDATE pic_result ";
    							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
    							$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    							$sql .= "   AND award_id = '$award_id' ";
    							$sql .= "   AND profile_id = '" . $resr['profile_id'] . "' ";
    							$sql .= "   AND pic_id = '" . $resr['pic_id'] . "' ";
    							// fprintf($output, "%s ;\r\n", $sql);
    							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    							++ $num_updates;
    						}
    					}
    					break;
    				}
    				case 'entry' : {
    					$sql = "SELECT * FROM entry_result WHERE yearmonth = '$jury_yearmonth' AND award_id = '$award_id' ";
    					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {

    						if (isset($sponsorship_slot[$row_no])) {
    							$sql  = "UPDATE entry_result ";
    							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
    							$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    							$sql .= "   AND award_id = '$award_id' ";
    							$sql .= "   AND profile_id = '" . $resr['profile_id'] . "' ";
    							// fprintf($output, "%s ;\r\n", $sql);
    							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    						}

    					}
    					break;
    				}
    				case 'club' : {
    					$sql = "SELECT * FROM club_result WHERE yearmonth = '$jury_yearmonth' AND award_id = '$award_id' ";
    					$resq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    					for ($row_no = 0; $resr = mysqli_fetch_array($resq); ++$row_no) {

    						if (isset($sponsorship_slot[$row_no])) {
    							$sql  = "UPDATE club_result ";
    							$sql .= "   SET sponsorship_no = '" . $sponsorship_slot[$row_no] . "' ";
    							$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    							$sql .= "   AND award_id = '$award_id' ";
    							$sql .= "   AND club_id = '" . $resr['club_id'] . "' ";
    							// fprintf($output, "%s ;\r\n", $sql);
    							mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    						}

    					}
    					break;
    				}
    			}	// switch by award_type
    		}	// there are sponsorships for this award
        }   // For each award
        // Commit Transaction
        $sql = "COMMIT";
        mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    }

	// Publish Results
	if (isset($_REQUEST['publish'])) {
		$sql = "UPDATE contest SET results_ready = 1 WHERE yearmonth = '$jury_yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Assign Entry Award
	if (isset($_REQUEST['award']) && isset($_REQUEST['assign'])) {
		$sql  = "INSERT INTO entry_result (yearmonth, award_id, profile_id, ranking) ";
		$sql .= "VALUES ('$jury_yearmonth', '" . $_REQUEST['award'] . "', '" . $_REQUEST['assign'] . "', 1) ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Remove Entry Award
	if (isset($_REQUEST['award']) && isset($_REQUEST['remove'])) {
		$sql  = "DELETE FROM entry_result ";
		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND award_id = '" . $_REQUEST['award'] . "' ";
		$sql .= "   AND profile_id = '" . $_REQUEST['remove'] . "' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Assign Club Award
	if (isset($_REQUEST['clubaward']) && isset($_REQUEST['clubassign'])) {
		$sql  = "INSERT INTO club_result (yearmonth, award_id, club_id, ranking) ";
		$sql .= "VALUES ('$jury_yearmonth', '" . $_REQUEST['clubaward'] . "', '" . $_REQUEST['clubassign'] . "', 1) ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Remove Club Award
	if (isset($_REQUEST['clubaward']) && isset($_REQUEST['clubremove'])) {
		$sql  = "DELETE FROM club_result ";
		$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
		$sql .= "   AND award_id = '" . $_REQUEST['clubaward'] . "' ";
		$sql .= "   AND club_id = '" . $_REQUEST['clubremove'] . "' ";
		mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Gather Statistics for display
	// 1. Pictures
	$sql = "SELECT COUNT(*) AS num_pictures, COUNT(NULLIF(total_rating, 0)) AS ratings_updated FROM pic WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_pictures = $row['num_pictures'];
	$num_pictures_with_ratings = $row['ratings_updated'];

	// 2. Entry
	$sql  = "SELECT COUNT(NULLIF(uploads, 0)) AS with_uploads, COUNT(NULLIF(awards, 0)) AS with_awards, ";
	$sql .= "       COUNT(NULLIF(hms, 0)) AS with_hms, COUNT(NULLIF(acceptances, 0)) AS with_acceptances, ";
	$sql .= "       COUNT(NULLIF(score, 0)) AS num_winners ";
	$sql .= "  FROM entry WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_entry_with_uploads = $row['with_uploads'];
	$num_entry_with_awards = $row['with_awards'];
	$num_entry_with_hms = $row['with_hms'];
	$num_entry_with_acceptances = $row['with_acceptances'];
	$num_unique_winners = $row['num_winners'];

	// 3. Award Status
	// Picture Awards
	$sql  = "SELECT IFNULL(SUM(number_of_awards), 0) AS number_of_awards ";
	$sql .= "  FROM award ";
	$sql .= " WHERE award.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level < 99 ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_pic_awards = $row['number_of_awards'];

	$sql  = "SELECT COUNT(*) AS num_results ";
	$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level < 99 ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_pic_results = $row['num_results'];

	// Acceptances
	$sql  = "SELECT COUNT(DISTINCT award.award_id) AS num_awards, COUNT(*) AS num_results ";
	$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level = 99 ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_acceptance_awards = $row['num_awards'];
	$num_acceptance_results = $row['num_results'];

	// Unawarded
	$sql  = "SELECT COUNT(*) AS unawarded FROM award ";
	$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.award_id NOT IN ( ";
	$sql .= "           SELECT DISTINCT pic_result.award_id ";
	$sql .= "             FROM pic_result ";
	$sql .= "            WHERE pic_result.yearmonth = award.yearmonth ) ";
	$sql .= "   AND award.award_type = 'pic' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_unawarded = $row['unawarded'];

	// Entry Awards
	$sql  = "SELECT IFNULL(SUM(number_of_awards), 0) AS number_of_awards ";
	$sql .= "  FROM award ";
	$sql .= " WHERE award.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.award_type = 'entry' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_entry_awards = $row['number_of_awards'];

	$sql  = "SELECT COUNT(*) AS num_results ";
	$sql .= "  FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.award_type = 'entry' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_entry_results = $row['num_results'];

	// Club Awards
	$sql  = "SELECT SUM(number_of_awards) AS number_of_awards ";
	$sql .= "  FROM award ";
	$sql .= " WHERE award.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.award_type = 'club' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_club_awards = $row['number_of_awards'];

	$sql  = "SELECT COUNT(*) AS num_results ";
	$sql .= "  FROM club_result, award ";
	$sql .= " WHERE club_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = club_result.yearmonth ";
	$sql .= "   AND award.award_id = club_result.award_id ";
	$sql .= "   AND award.award_type = 'club' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$num_club_results = $row['num_results'];

	// 4. Section Statistics
	$sql  = "SELECT IFNULL(SUM(num_entrants), 0) AS num_entrants, IFNULL(SUM(num_pictures), 0) AS num_pictures, IFNULL(SUM(num_awards), 0) AS num_awards, ";
	$sql .= "       IFNULL(SUM(num_hms), 0) AS num_hms, IFNULL(SUM(num_acceptances), 0) AS num_acceptances, IFNULL(SUM(num_winners), 0) AS num_winners ";
	$sql .= "  FROM section WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$section_entrants = $row['num_entrants'];
	$section_pictures = $row['num_pictures'];
	$section_awards = $row['num_awards'];
	$section_hms = $row['num_hms'];
	$section_acceptances = $row['num_acceptances'];
	$section_winners = $row['num_winners'];

	$sql  = "SELECT COUNT(*) AS num_entrants, COUNT(NULLIF(score, 0)) AS num_winners FROM entry ";
	$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND entry.profile_id IN ( ";
	$sql .= "             SELECT DISTINCT pic.profile_id FROM pic ";
	$sql .= "              WHERE pic.yearmonth = entry.yearmonth ) ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$section_computed_entrants = $tmpr['num_entrants'];
	$section_computed_winners = $tmpr['num_winners'];

	$sql  = "SELECT COUNT(*) AS num_awards ";
	$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
    $sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.level < 9 ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$section_computed_awards = $tmpr['num_awards'];

	$sql  = "SELECT COUNT(*) AS num_hms ";
	$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level = 9 ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$section_computed_hms = $tmpr['num_hms'];

	$sql  = "SELECT COUNT(*) AS num_acceptances ";
	$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level = 99 ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$section_computed_acceptances = $tmpr['num_acceptances'];

    // Sponsor Assignment
    $sql  = "SELECT COUNT(*) AS num_awards_with_sponsorship, SUM(sponsored_awards) AS num_sponsorships FROM award ";
    $sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    $sql .= "   AND sponsored_awards > 0 ";
    $tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
    $num_awards_with_sponsorship = $tmpr['num_awards_with_sponsorship'];
	$num_sponsorships = $tmpr['num_sponsorships'];

    $sql  = "SELECT COUNT(DISTINCT link_id) AS num_awards_sponsored, SUM(number_of_units) AS num_sponsorships_taken FROM sponsorship ";
    $sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    $sql .= "   AND sponsorship_type = 'AWARD' ";
    $tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
    $num_awards_sponsored = $tmpr['num_awards_sponsored'];
	$num_sponsorships_taken = $tmpr['num_sponsorships_taken'];

    $sql  = "SELECT COUNT(*) AS num_sponsorships_assigned FROM pic_result ";
    $sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    $sql .= "   AND sponsorship_no != 0 ";
    $tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$num_sponsorships_assigned_pic = $tmpr['num_sponsorships_assigned'];

    $sql  = "SELECT COUNT(*) AS num_sponsorships_assigned FROM entry_result ";
    $sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    $sql .= "   AND sponsorship_no != 0 ";
    $tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$num_sponsorships_assigned_entry = $tmpr['num_sponsorships_assigned'];

    $sql  = "SELECT COUNT(*) AS num_sponsorships_assigned FROM club_result ";
    $sql .= " WHERE yearmonth = '$jury_yearmonth' ";
    $sql .= "   AND sponsorship_no != 0 ";
    $tmpq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$tmpr = mysqli_fetch_array($tmpq);
	$num_sponsorships_assigned_club = $tmpr['num_sponsorships_assigned'];

    $num_sponsorships_assigned = $num_sponsorships_assigned_pic + $num_sponsorships_assigned_entry + $num_sponsorships_assigned_club;
    $num_sponsorships_not_assigned = $num_sponsorships_taken - $num_sponsorships_assigned;

	// Get Entry Awards List
	$sql = "SELECT * FROM entry_result WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$entry_award_list = array();
	while ($row = mysqli_fetch_array($query)) {
		if (isset($entry_award_list[$row['award_id']]))
			$entry_award_list[$row['award_id']][] = $row['profile_id'];
		else
			$entry_award_list[$row['award_id']] = array($row['profile_id']);
	}

	// Get Club Awards List
	$sql = "SELECT * FROM club_result WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club_award_list = array();
	while ($row = mysqli_fetch_array($query)) {
		if (isset($club_award_list[$row['award_id']]))
			$club_award_list[$row['award_id']][] = $row['club_id'];
		else
			$club_award_list[$row['award_id']] = array($row['club_id']);
	}
?>



<!DOCTYPE html>
<html>
<head>

    <!-- Page title -->
	<title>Youth Photographic Society | Projection Panel</title>

	<?php include("inc/header.php");?>

</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | PROJECTION PANEL  </h1>
			<p>Please Wait. </p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>
	<!--[if lt IE 7]>
	<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->

	<!-- Header -->
	<div id="header">
		<div class="color-line"></div>
		<div id="logo" class="light-version">
			<span>Youth Photographic Society</span>
		</div>
		<nav role="navigation">
			<div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
			<div class="small-logo">
				<span class="text-primary">PROJECTION APP</span>
			</div>
			<form role="search" class="navbar-form-custom" method="post" action="#">
				<div class="form-group">
					<input type="text" placeholder="Search something special" class="form-control" name="search">
				</div>
			</form>
			<div class="mobile-menu">
				<button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
					<i class="fa fa-edit"></i>
				</button>
				<div class="collapse mobile-navbar" id="mobile-collapse">
					<ul class="nav navbar-nav">
						<li>
							<a class="" href="index.php">Logout</a>
						</li>
						<li>
							<a data-toggle="modal" data-target="#myModal">Profile</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>
	</div>

	<!-- Navigation -->
	<?php include("inc/projector_sidebar.php");?>


	<div id="wrapper">
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>Welcome to Results Administration</h2>
				</div>
			</div>
			<!-- Update Results in Entry & Pic -->
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<div class="panel-tools">
								<a class="showhide"><i class="fa fa-chevron-up"></i></a>
								<a class="closebox"><i class="fa fa-times"></i></a>
							</div>
							<h2>Administration Actions</h2>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-2">
									<div class="hpanel">
										<div class="panel-body text-center h-200">
											<h3>Pictures</h3>
											<br>
											<h4 class="m-xs font-extra-bold text-success">Uploaded: <?php echo $num_pictures;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Rated: <?php echo $num_pictures_with_ratings;?></h4>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_admin.php?update_pic" class="btn btn-info btn-sm">Update Picture Scores</a>
										</div>
									</div>
								</div>

								<div class="col-sm-2">
									<div class="hpanel">
										<div class="panel-body text-center h-200">
											<h3>Participants</h3>
											<br>
											<h4 class="m-xs font-extra-bold text-success">Participants: <?php echo $num_entry_with_uploads;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Awarded: <?php echo $num_entry_with_awards;?></h4>
											<h4 class="m-xs font-extra-bold text-success">HMs: <?php echo $num_entry_with_hms;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Acceptances: <?php echo $num_entry_with_acceptances;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Winners: <?php echo $num_unique_winners;?></h4>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_admin.php?update_entry" class="btn btn-info btn-sm">Update Participant Scores</a>
										</div>
									</div>
								</div>

								<div class="col-sm-2">
									<div class="hpanel">
										<div class="panel-body text-center h-200">
											<h3>Sections</h3>
											<br>
											<h4 class="m-xs font-extra-bold text-success">Entrants : <?=$section_entrants;?> / <?=$section_computed_entrants;?></h4>
                                            <span class="text-default pull-right">Sum / Common</span><br>
											<h4 class="m-xs font-extra-bold text-success">Winnerss : <?=$section_winners;?> / <?=$section_computed_winners;?></h4>
                                            <span class="text-default pull-right">Sum / Common</span><br>
											<h4 class="m-xs font-extra-bold text-success">Pictures : <?=$section_pictures;?> / <?=$num_pictures;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Awards : <?=$section_awards;?> / <?=$section_computed_awards;?></h4>
											<h4 class="m-xs font-extra-bold text-success">HMs : <?=$section_hms;?> / <?=$section_computed_hms;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Acceptances : <?=$section_acceptances;?> / <?=$section_computed_acceptances;?></h4>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_admin.php?update_section" class="btn btn-info btn-sm">Update Section Scores</a>
										</div>
									</div>
								</div>

                                <div class="col-sm-2">
									<div class="hpanel">
										<div class="panel-body text-center h-200">
											<h3>Awards</h3>
											<br>
											<h4 class="m-xs font-extra-bold text-success">Picture : <?=$num_pic_results;?> / <?=$num_pic_awards;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Acceptance : <?=$num_acceptance_results;?> / <?=$num_acceptance_awards;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Unawarded : <?=$num_unawarded;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Entry : <?=$num_entry_results;?> / <?=$num_entry_awards;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Club : <?=$num_club_results;?> / <?=$num_club_awards;?></h4>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_admin.php?publish" class="btn btn-danger btn-sm">Publish results</a>
										</div>
									</div>
								</div>

                                <div class="col-sm-3">
									<div class="hpanel">
										<div class="panel-body text-center h-200">
											<h3>Sponsored Awards</h3>
											<br>
											<h4 class="m-xs font-extra-bold text-success">Offered Awards / Slots : <?= $num_awards_with_sponsorship;?> / <?= $num_sponsorships;?></h4>
                                            <h4 class="m-xs font-extra-bold text-success">Sponsored Aswards / Slots : <?= $num_awards_sponsored;?> / <?= $num_sponsorships_taken;?></h4>
                                            <h4 class="m-xs font-extra-bold text-success">Assigned Pic / Entry / Club : <?= $num_sponsorships_assigned_pic;?> / <?= $num_sponsorships_assigned_entry;?> / <?= $num_sponsorships_assigned_club;?></h4>
                                            <h4 class="m-xs font-extra-bold text-success">Assigned Total : <?= $num_sponsorships_assigned;?></h4>
											<h4 class="m-xs font-extra-bold text-success">Not Assigned : <?= $num_sponsorships_not_assigned;?></h4>
                                            <p>After results are finalized, run this option to associate each result with a sponsor to print on certificates</p>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_admin.php?assign_sponsors" class="btn btn-info btn-sm">Assign Sponsors</a>
										</div>
									</div>
								</div>

							</div>
						</div>
						<div class="panel-footer">
							<p id="msg-line">Have a Good Day</p>
						</div>
					</div>
				</div>
			</div>
			<!-- Assign Entry Awards -->
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<div class="panel-tools">
								<a class="showhide"><i class="fa fa-chevron-up"></i></a>
								<a class="closebox"><i class="fa fa-times"></i></a>
							</div>
							<h2>Finalize Entry Awards</h2>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12">
									<form method="post" action="projector_admin.php?entrant_award" class="form-inline" >
										<input type="hidden" name="entrant_award" value="entrant_award">  <!-- to identify this form -->
										<div class="form-group">
											<select class="form-control input-group-select" placeholder="Select an Award" name="entry_award_id" >
											<?php
												$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND award_type = 'entry'";
												$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$number_of_entry_awards = 0;
                                                $award_id = (isset($_REQUEST['entry_award_id']) ? $_REQUEST['entry_award_id'] : 0);
                                                $award_section = "CONTEST";
                                                $award_group = "";
												while ($row = mysqli_fetch_array($query)) {
                                                    if ($award_id == $row['award_id']) {
                                                        $award_section = $row['section'];
                                                        $award_group = $row['award_group'];
                                                    }
                                                    // if ($award_id == 0)
                                                    //     $award_id = $row['award_id'];       // First award_block
											?>
												<option value="<?php echo $row['award_id'];?>"
														<?= ($award_id == $row['award_id']) ? "selected" : "";?> >
													<?php echo $row['award_name'] . "-" . $row['number_of_awards']; ?>
												</option>
											<?php
												}
											?>
											</select>
										</div>
										<div class="input-group">
											<span class="input-group-btn" style="padding-left: 15px;">
												<button class="btn btn-info" type="submit" name="view-candidates"><i class="glyphicon glyphicon-ok-circle small"></i> View Candidates</button>
											</span>
										</div>
                                        <div class="input-group">
											<span class="input-group-btn" style="padding-left: 15px;">
												<button class="btn btn-info" type="submit" name="view-awardee"><i class="glyphicon glyphicon-ok-circle small"></i> View Awardees</button>
											</span>
										</div>
										<div class="row">
											<?php
                                                if ($award_id != 0) {
                                                    if (isset($_REQUEST['view-candidates'])) {
                                                        // View candidates for the award
                                                        if ($award_section == "CONTEST") {
                                                            // Overall Entry Awards
        													$sql  = "SELECT profile.profile_id, profile.profile_name, profile.yps_login_id, entry.entrant_category, club.club_name, ";
        													$sql .= "       profile.honors, profile.avatar, profile.city, profile.state, country.country_name, ";
        													$sql .= "       entry.uploads, entry.awards, entry.hms, entry.acceptances, entry.score ";
                                                            $sql .= "  FROM entry, entrant_category, country, profile ";
        													// $sql .= "  FROM entry, entrant_category, award, country, profile ";
        													$sql .= "  LEFT JOIN club ON profile.club_id = club.club_id ";
        													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
        													// $sql .= "   AND award.yearmonth = entry.yearmonth ";
        													// $sql .= "   AND award.award_id = '$award_id' ";
        													$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
        													$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
                                                            $sql .= "   AND entrant_category.award_group = '$award_group' ";
        													// $sql .= "   AND entrant_category.award_group = award.award_group ";
        													$sql .= "   AND profile.profile_id = entry.profile_id ";
        													$sql .= "   AND country.country_id = profile.country_id ";
        													$sql .= " ORDER BY entry.score DESC ";
        													$sql .= " LIMIT 4 ";
                                                        }
                                                        else {
                                                            // Section specific Entry Awards
                                                            $sql  = "SELECT profile.profile_id, profile.profile_name, profile.yps_login_id, entry.entrant_category, club.club_name, ";
        													$sql .= "       profile.honors, profile.avatar, profile.city, profile.state, country.country_name, ";
        													$sql .= "       entry.uploads, entry.awards, entry.hms, entry.acceptances, ";
                                                            $sql .= "       SUM(award_weight) AS score ";
        													$sql .= "  FROM entry, entrant_category, award, pic_result, country, profile ";
        													$sql .= "  LEFT JOIN club ON profile.club_id = club.club_id ";
        													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
                                                            $sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
        													$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
        													$sql .= "   AND entrant_category.award_group = '$award_group' ";
                                                            $sql .= "   AND pic_result.yearmonth = entry.yearmonth ";
                                                            $sql .= "   AND pic_result.profile_id = entry.profile_id ";
        													$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
        													$sql .= "   AND award.award_id = pic_result.award_id ";
                                                            $sql .= "   AND award.section = '$award_section' ";
        													$sql .= "   AND profile.profile_id = entry.profile_id ";
        													$sql .= "   AND country.country_id = profile.country_id ";
                                                            $sql .= " GROUP BY profile.profile_id ";
                                                            $sql .= " HAVING score = '4' ";
        													$sql .= " ORDER BY score DESC ";
        													// $sql .= " LIMIT 20 ";
                                                        }
                                                    }
                                                    else {
                                                        // Already assigned awards
                                                        $sql  = "SELECT profile.profile_id, profile.profile_name, profile.yps_login_id, entry.entrant_category, club.club_name, ";
    													$sql .= "       profile.honors, profile.avatar, profile.city, profile.state, country.country_name, ";
    													$sql .= "       entry.uploads, entry.awards, entry.hms, entry.acceptances, entry.score ";
    													$sql .= "  FROM entry, entry_result, country, profile ";
    													$sql .= "  LEFT JOIN club ON profile.club_id = club.club_id ";
    													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
    													$sql .= "   AND entry_result.yearmonth = entry.yearmonth ";
    													$sql .= "   AND entry_result.award_id = '$award_id' ";
                                                        $sql .= "   AND entry_result.profile_id = entry.profile_id ";
    													$sql .= "   AND profile.profile_id = entry.profile_id ";
    													$sql .= "   AND country.country_id = profile.country_id ";
    													$sql .= " ORDER BY profile.profile_name ";
                                                    }
    												$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                                    $idx = 0;
    												while ($row = mysqli_fetch_array($query)) {
											?>
											<div class="col-sm-3">
												<div class="hpanel">
													<div class="panel-body text-center h-200">
														<h3>
															<?php
																if (isset($entry_award_list[$award_id]) &&
																	in_array($row['profile_id'], $entry_award_list[$award_id])) {
															?>
															<i class="fa fa-check-circle"></i>
															<?php
																}
															?>
															<?=$row['profile_name'];?>
														</h3>
														<p class="text-muted"><small><?=$row['honors'];?></small></p>
														<p class="text-muted"><small><?php echo ($row['yps_login_id'] == "") ? $row['club_name'] : "Youth Photographic Society";?></small></p>
                                                        <p class="text-muted"><?=$row['city'];?>, <?=$row['state'];?>, <?=$row['country_name'];?></p>
														<img src="/res/avatar/<?=$row['avatar'];?>" style="max-width: 80px; max-height: 80px; border: solid 1px #808080;" >
														<!-- <br>
														<h4 class="m-xs font-extra-bold text-success">Uploads: <?=$row['uploads'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Awards: <?=$row['awards'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Hon. Men: <?=$row['hms'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Acceptances: <?=$row['acceptances'];?></h4> -->
														<br>
														<h4 class="m-xs font-extra-bold text-success">Total Score: <?=$row['score'];?></h4>
														<br>
														<hr>
														<?php
															$sql  = "SELECT award.award_id, award.award_name, award.award_weight, award.level, pic.title, pic.section, pic.picfile ";
															$sql .= "  FROM pic_result, pic, award ";
															$sql .= " WHERE pic_result.yearmonth = '$jury_yearmonth' ";
															$sql .= "   AND pic_result.profile_id = '" . $row['profile_id'] . "' ";
															$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
															$sql .= "   AND award.award_id = pic_result.award_id ";
                                                            if ($award_section != 'CONTEST')
                                                                $sql .= "   AND award.section = '$award_section' ";
															$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
															$sql .= "   AND pic.profile_id = pic_result.profile_id ";
															$sql .= "   AND pic.pic_id = pic_result.pic_id ";
															$awq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
                                                            $awards = 0;
                                                            $hms = 0;
															while ($awr = mysqli_fetch_array($awq)) {
                                                                if ($awr['level'] < 9)
                                                                    ++ $awards;
                                                                elseif ($awr['level'] == 9)
                                                                    ++ $hms;
																$tn_path = "/salons/" . $jury_yearmonth . "/upload/" . $awr['section'] . "/tn/" . $awr['picfile'];
														?>
														<div class="row" style="border-bottom: solid 1px #d0d0d0; padding-top: 4px;">
															<div class="col-sm-3"><img src="<?=$tn_path;?>" class="img-responsive"></div>
															<div class="col-sm-9">
																<p><?=$awr['title'];?></p>
																<p><b><?=$awr['section'] . " - " . $awr['award_name'] . " (Wt: " . $awr['award_weight'] . ")";?></b></p>
															</div>
														</div>
                                                        <?php
                                                            }
                                                        ?>
                                                        <p class="lead">Awards : <?= $awards;?></p>
                                                        <p class="lead">HMs : <?= $hms;?></p>
													</div>
													<div class="panel-footer text-center">
                                                        <?php
                                                            if (isset($_REQUEST['view-candidates'])) {
                                                        ?>
														<p><a href="projector_admin.php?award=<?=$award_id;?>&assign=<?=$row['profile_id'];?>" class="btn btn-info btn-sm">Assign</a></p>
                                                        <?php
                                                            }
                                                            else {
                                                        ?>
														<p><a href="projector_admin.php?award=<?=$award_id;?>&remove=<?=$row['profile_id'];?>" class="btn btn-info btn-sm">Remove</a></p>
                                                        <?php
                                                            }
                                                        ?>
													</div>
												</div>
											</div>
											<?php
                                                            ++ $idx;
                                                            if ($idx % 4 == 0) {
                                            ?>
                                            <div class="clearfix"></div>
                                            <?php
                                                            }
                                                        } // for each profile
                                                    } // $award_id != 0
                                            ?>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<p id="msg-line">Have a Good Day</p>
						</div>
					</div>
				</div>
			</div>	<!-- / entry_awards -->
			<!-- Assign Club Awards -->
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-heading">
							<div class="panel-tools">
								<a class="showhide"><i class="fa fa-chevron-up"></i></a>
								<a class="closebox"><i class="fa fa-times"></i></a>
							</div>
							<h2>Finalize Club Awards</h2>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12">
									<form method="post" action="projector_admin.php?entrant_award" class="form-inline" >
										<input type="hidden" name="entrant_award" value="entrant_award">  <!-- to identify this form -->
										<div class="form-group">
											<select class="form-control input-group-select" placeholder="Select an Award" name="club_award_id" >
											<?php
												$sql = "SELECT * FROM award WHERE yearmonth = '$jury_yearmonth' AND award_type = 'club'";
												$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$number_of_entry_awards = 0;
                                                $award_id = (isset($_REQUEST['club_award_id']) ? $_REQUEST['club_award_id'] : 0);
												while ($row = mysqli_fetch_array($query)) {
                                                    if ($award_id == 0)
                                                        $award_id = $row['award_id'];       // First award if nothing has been selected
											?>
												<option value="<?php echo $row['award_id'];?>"
														<?= ($award_id == $row['award_id']) ? "selected" : "";?> >
													<?php echo $row['award_name'] . "-" . $row['number_of_awards']; ?>
												</option>
											<?php
												}
											?>
											</select>
										</div>
										<div class="input-group">
											<span class="input-group-btn" style="padding-left: 15px;">
												<button class="btn btn-info" type="submit" name="view-clubs"><i class="glyphicon glyphicon-ok-circle small"></i> View Clubs</button>
											</span>
										</div>
                                        <div class="input-group">
											<span class="input-group-btn" style="padding-left: 15px;">
												<button class="btn btn-info" type="submit" name="view-awarded-clubs"><i class="glyphicon glyphicon-ok-circle small"></i> View Awarded Clubs</button>
											</span>
										</div>
										<div class="row">
											<?php
												if (isset($_REQUEST['view-clubs']) && isset($_REQUEST['club_award_id'])) {
													$sql  = "SELECT club.club_id, club.club_name, country.country_name, club.club_logo, ";
													$sql .= "       COUNT(DISTINCT entry.profile_id) AS num_members, IFNULL(SUM(entry.uploads), 0) AS club_uploads, ";
													$sql .= "       IFNULL(SUM(entry.awards), 0) AS club_awards, IFNULL(SUM(entry.hms), 0) AS club_hms, ";
													$sql .= "       IFNULL(SUM(entry.acceptances), 0) AS club_acceptances, IFNULL(SUM(entry.score), 0) AS club_score ";
													$sql .= "  FROM entry, award, entrant_category, profile, club, country ";
													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND award.yearmonth = entry.yearmonth ";
                                                    $sql .= "   AND award.award_id = '$award_id' ";
													// $sql .= "   AND award.award_id = '" . $_REQUEST['club_award_id'] . "' ";
													$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
													$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
													$sql .= "   AND entrant_category.award_group = award.award_group ";
													$sql .= "   AND profile.profile_id = entry.profile_id ";
													$sql .= "   AND club.club_id = profile.club_id ";
													$sql .= "   AND country.country_id = club.club_country_id ";
													$sql .= " GROUP BY club.club_id ";
													$sql .= " ORDER BY club_score DESC ";
													$sql .= " LIMIT 4 ";
                                                }
                                                else {
                                                    $sql  = "SELECT club.club_id, club.club_name, country.country_name, club.club_logo, ";
													$sql .= "       COUNT(DISTINCT entry.profile_id) AS num_members, IFNULL(SUM(entry.uploads), 0) AS club_uploads, ";
													$sql .= "       IFNULL(SUM(entry.awards), 0) AS club_awards, IFNULL(SUM(entry.hms), 0) AS club_hms, ";
													$sql .= "       IFNULL(SUM(entry.acceptances), 0) AS club_acceptances, IFNULL(SUM(entry.score), 0) AS club_score ";
													$sql .= "  FROM entry, club_result, profile, club, country ";
													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND club_result.yearmonth = entry.yearmonth ";
													$sql .= "   AND club_result.award_id = '$award_id' ";
													$sql .= "   AND profile.profile_id = entry.profile_id ";
                                                    $sql .= "   AND profile.club_id = club_result.club_id ";
													$sql .= "   AND club.club_id = profile.club_id ";
													$sql .= "   AND country.country_id = club.club_country_id ";
													$sql .= " GROUP BY club.club_id ";
													$sql .= " ORDER BY club_name ";
                                                }
												$query = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												while ($row = mysqli_fetch_array($query)) {
													// Calculate Number of Winners
													$sql  = "SELECT COUNT(*) AS num_winners ";
													$sql .= "  FROM entry, profile ";
													$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND profile.club_id = '" . $row['club_id'] . "' ";
													$sql .= "   AND (entry.awards + entry.hms + entry.acceptances) > 0 ";
													$sql .= "   AND profile.profile_id = entry.profile_id ";
													$subquery = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$subrow = mysqli_fetch_array($subquery);
													$club_num_winners = $subrow['num_winners'];
											?>
											<div class="col-sm-3">
												<div class="hpanel">
													<div class="panel-body text-center h-200">
														<h3>
															<?php
																if (isset($club_award_list[$award_id]) && in_array($row['club_id'], $club_award_list[$award_id])) {
															?>
															<i class="fa fa-check-circle"></i>
															<?php
																}
															?>
															<?=$row['club_name'];?>
														</h3>
														<p class="text-muted"><small><?=$row['country_name'];?></small></p>
														<img src="/res/club/<?php echo ($row['club_logo'] == "") ? "club.jpg" : $row['club_logo'];?>" style="max-width: 80px; max-height: 80px;" >
														<br>
														<h4 class="m-xs font-extra-bold text-success">Participants: <?=$row['num_members'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Pictures: <?=$row['club_uploads'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Awards: <?=$row['club_awards'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Hon. Men: <?=$row['club_hms'];?></h4>
														<h4 class="m-xs font-extra-bold text-success">Acceptances: <?=$row['club_acceptances'];?></h4>
														<br>
														<h4 class="m-xs font-extra-bold text-success">Winners: <?= $club_num_winners;?></h4>
														<br>
														<h4 class="m-xs font-extra-bold text-success">Total Score: <?=$row['club_score'];?></h4>
														<hr>
														<?php
															$sql  = "SELECT profile.profile_id, profile.profile_name, profile.avatar, ";
															$sql .= "       entry.uploads, entry.awards, entry.hms, entry.acceptances  ";
															$sql .= "  FROM entry, profile ";
															$sql .= " WHERE entry.yearmonth = '$jury_yearmonth' ";
															$sql .= "   AND profile.profile_id = entry.profile_id ";
															$sql .= "   AND profile.club_id = '" . $row['club_id'] . "' ";
															$sql .= " ORDER BY entry.score DESC ";
															$awq = mysqli_query($DBCON, $sql) or sql_error_exit($sql, mysqli_error($DBCON), __FILE__, __LINE__);
															while ($awr = mysqli_fetch_array($awq)) {
																$avatar_path = "/res/avatar/" . $awr['avatar'];
														?>
														<div class="row" style="border-bottom: solid 1px #d0d0d0; padding-top: 4px;">
															<div class="col-sm-3"><img src="<?=$avatar_path;?>" class="img-responsive"></div>
															<div class="col-sm-9">
																<p><b><?=$awr['profile_name'];?></b></p>
																<p>UPL: <?=$awr['uploads'];?>, AW: <?=$awr['awards'];?>, HM: <?=$awr['hms'];?>, AC: <?=$awr['acceptances'];?></p>
															</div>
														</div>
														<?php
															} // while $awr
														?>
													</div>
													<div class="panel-footer text-center">
                                                        <?php
                                                            if (isset($_REQUEST['view-clubs'])) {
                                                        ?>
														<p><a href="projector_admin.php?clubaward=<?=$award_id;?>&clubassign=<?=$row['club_id'];?>" class="btn btn-info btn-sm">Assign</a></p>
                                                        <?php
                                                            }
                                                            else {
                                                        ?>
														<p><a href="projector_admin.php?clubaward=<?=$award_id;?>&clubremove=<?=$row['club_id'];?>" class="btn btn-info btn-sm">Remove</a></p>
                                                        <?php
                                                            }
                                                        ?>
													</div>
												</div>
											</div>
											<?php
												} // while $row
											?>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<p id="msg-line">Have a Good Day</p>
						</div>
					</div>
				</div>
			</div>	<!-- / club_awards -->
		</div>

<?php
      include("inc/profile_modal.php");
?>

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Vendor scripts -->
<!--
<script src="plugin/jquery-flot/jquery.flot.js"></script>
<script src="plugin/jquery-flot/jquery.flot.resize.js"></script>
<script src="plugin/jquery-flot/jquery.flot.pie.js"></script>
<script src="plugin/flot.curvedlines/curvedLines.js"></script>
<script src="plugin/peity/jquery.peity.min.js"></script>
-->

<!-- App scripts -->
<!--
<script src="custom/js/charts.js"></script>
-->


<!-- Simple Javascript to mediate results upload -->
<script>
// Global Variable
var server_url = "http://salon.localhost/jurypanel/svc/put_table_data.php";

// Since $.post executes async queries, results may not arrive on the next line. Hence this function is called when $.post data is received
function send_data_to_server(tableref, table_data) {

	// As simple as this -> just send data to the server
	$("#msg-line").html("Uploading " + tableref + " Data");
	$.post (server_url,
			{ "data" : table_data, "user" : "projector", "auth" : "projector!@#" },
			function (data, status) {
				if (status == "success") alert(data);
				$("#msg-line").html("Finshed Uploading " + tableref + " Data");
				alert("Finshed Uploading " + tableref + " Data");
			}
	);


}

function isValidJSON(str)
{
	try {
		JSON.parse(str);
	}
	catch(e) {
		return false;
	}
	return true;
}

function upload_rating() {
	// Simple Results Uploader
	// Calls a local script to assemble data
	// Then calls a generic server script to load data into the target table

	// Retrieve & upload rating data
	$("#msg-line").html("Assembling Rating Data");
	$.post (
		"svc/get_table_data.php",
		{ "table" : "rating" },
		function (data, status) {
			if (status == "success") {
				if (isValidJSON(data))
					send_data_to_server("Rating", data);	// Send Data to Server when ready with valid data
				else
					alert(data);	// Probably error message
			}
			else
				alert(status);
		},
		"text"
	);
}

function upload_result() {
	// Simple Results Uploader
	// Calls a local script to assemble data
	// Then calls a generic server script to load data into the target table

	// Retrieve & upload result data
	$("#msg-line").html("Assembling Result Data");
	$.post (
		"svc/get_table_data.php",
		{ "table" : "result" },
		function (data, status) {
			if (status == "success") {
				if (isValidJSON(data))
					send_data_to_server("Result", data);	// Send Data to Server when ready with valid data
				else
					alert(data);	// Probably error message
			}
			else
				alert(status);
		},
		"text"
	);
}


</script>


</body>

</html>

<?php
}
else
{
	debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
	$_SESSION['err_msg'] = "Invalid Request";
	header("Location: /jurypanel/projector_home.php" );
	printf("<script>location.href='/jurypanel/projector_home.php'</script>");
}

?>

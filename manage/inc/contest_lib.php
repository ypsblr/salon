<?php


function get_contest($yearmonth = 0) {
	global $DBCON;

	if ($yearmonth == 0) {
		if (isset($_SESSION['admin_yearmonth']))
			$yearmonth = $_SESSION['admin_yearmonth'];
		else {
			$sql = "SELECT MAX(yearmonth) AS last_contest FROM contest";
			$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$yearmonth = $row['last_contest'];
		}
	}
	// Fetch Contest Details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);

	return $row;
}

function get_contest_list() {
	global $DBCON;

	$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$contest_list = [];
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$contest_list[] = $row;

	return $contest_list;
}

function get_section_list($yearmonth) {
	global $DBCON;

	// Prepare List of Sections
	$section_list_list = [];
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$section_list[$row['section']] = $row;

	return $section_list;
}

function get_salon_values($yearmonth) {
	// Get Contest Values
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return [];
	$row = mysqli_fetch_array($query);

	// Get Exhibition Details
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$exhibition_invitation_img = "";
	$exhibition_email_header_img = "";
	$exhibition_chief_guest_name = "";
	$exhibition_chief_guest_position = "";
	$exhibition_chief_guest_avatar = "";
	$exhibition_guest_of_honor_name = "";
	$exhibition_guest_of_honor_position = "";
	$exhibition_guest_of_honor_avatar = "";
	if (mysqli_num_rows($query) > 0) {
		$exhibition = mysqli_fetch_array($query);
		$exhibition_invitation_img = $exhibition['invitation_img'];
		$exhibition_email_header_img = $exhibition['email_header_img'];
		$roles = explode("|", $exhibition['dignitory_roles']);
		$names = explode("|", $exhibition['dignitory_names']);
		$positions = explode("|", $exhibition['dignitory_positions']);
		$avatars = explode("|", $exhibition['dignitory_avatars']);
		if ($cg_idx = array_search("Chief Guest", $roles)) {
			$exhibition_chief_guest_name = $names['cg_idx'];
			$exhibition_chief_guest_position = $positions['cg_idx'];
			$exhibition_chief_guest_avatar = $avatars['cg_idx'];
		}
		if ($cg_idx = array_search("Guest of Honor", $roles)) {
			$exhibition_guest_of_honor_name = $names['cg_idx'];
			$exhibition_guest_of_honor_position = $positions['cg_idx'];
			$exhibition_guest_of_honor_avatar = $avatars['cg_idx'];
		}
	}

	// Upload End Dates
	$sql  = "SELECT section_type, MAX(submission_last_date) AS last_date ";
	$sql .= "  FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$digital_last_date = NULL;
	$print_last_date = NULL;
	$has_print_sections = false;
	while ($section = mysqli_fetch_array($query)) {
		if ($section['section_type'] == "P") {
			$print_last_date = $section['last_date'];
			$has_print_sections = true;
		}
		else {
			$digital_last_date = $section['last_date'];
		}
	}

	// Get Team Data
	// Chairman
	$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND role = 'Chairman' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$chairman_role_name = "";
		$chairman_name = "";
	}
	else {
		$team = mysqli_fetch_array($query);
		$chairman_role_name = $team['role_name'];
		$chairman_name = $team['member_name'];
	}
	// Secretary
	$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' AND role = 'Secretary' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$secretary_role_name = "";
		$secretary_name = "";
	}
	else {
		$team = mysqli_fetch_array($query);
		$secretary_role_name = $team['role_name'];
		$secretary_name = $team['member_name'];
	}

	// Arrange them into pairs
	$contest_values = array(
						"server-address" => http_method() . $_SERVER['SERVER_NAME'],
						"yps-website" => "https://ypsbengaluru.com",
						"yearmonth" => $yearmonth,
						"salon-name" => $row['contest_name'],
						"registration-start-date" => $row['registration_start_date'],
						"registration-last-date" => $row['registration_last_date'],
						"digital-upload-last-date" => $digital_last_date,
						"print-upload-last-date" => $print_last_date,
						"has-print-sections" => $has_print_sections,
						"submission-timezone" => $row['submission_timezone_name'],
						"max-pic-width" => $row['max_width'],
						"max-pic-height" => $row['max_height'],
						"max-pic-file-size-in-mb" => $row['max_file_size_in_mb'],
						"judging-start-date" => $row['judging_start_date'],
						"judging-end-date" => $row['judging_end_date'],
						"result-date" => $row['results_date'],
						"update-start-date" => $row['update_start_date'],
						"update-end-date" => $row['update_end_date'],
						"judging-venue" => $row['judging_venue'],
						"judging-venue-address" => $row['judging_venue_address'],
						"judging-venue-location-map" => $row['judging_venue_location_map'],
						"exhibition-name" => $row['exhibition_name'],
						"exhibition-start-date" => $row['exhibition_start_date'],
						"exhibition-end-date" => $row['exhibition_end_date'],
						"exhibition-venue" => $row['exhibition_venue'],
						"exhibition-venue-address" => $row['exhibition_venue_address'],
						"exhibition-venue-location-map" => $row['exhibition_venue_location_map'],
						"exhibition-invitation-img" => $exhibition_invitation_img,
						"exhibition-email-header-img" => $exhibition_email_header_img,
						"exhibition-chief-guest-name" => $exhibition_chief_guest_name,
						"exhibition-chief-guest-position" => $exhibition_chief_guest_position,
						"exhibition-chief-guest-avatar" => $exhibition_chief_guest_avatar,
						"exhibition-guest-of-honor-name" => $exhibition_guest_of_honor_name,
						"exhibition-guest-of-honor-position" => $exhibition_guest_of_honor_position,
						"exhibition-guest-of-honor-avatar" => $exhibition_guest_of_honor_avatar,
						"catalog-release-date" => $row['catalog_release_date'],
						"catalog-file" => $row['catalog_download'],
						"chairman-role" => $chairman_role_name,
						"salon-chairman" => $chairman_name,
						"secretary-role" => $secretary_role_name,
						"salon-secretary" => $secretary_name,
						);


}

?>

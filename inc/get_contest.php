<?php
// Get the current Salon yearmonth
if (! empty($_REQUEST['yearmonth'])) {
	$contest_yearmonth = $_REQUEST['yearmonth'];
	$_SESSION['yearmonth'] = $contest_yearmonth;
}
else if (! empty($_REQUEST['set_yearmonth'])) {
	$contest_yearmonth = $_REQUEST['set_yearmonth'];
	$_SESSION['yearmonth'] = $contest_yearmonth;
}
else if (! empty($_SESSION['yearmonth']))
	$contest_yearmonth = $_SESSION['yearmonth'];
else {
	// If SESSION is not set, set to the latest
	$sql = "SELECT max(yearmonth) AS yearmonth FROM contest";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$contest_yearmonth = $row['yearmonth'];
	$_SESSION['yearmonth'] = $contest_yearmonth;
}

// Fetch Contest Details
$sql = "SELECT * FROM contest WHERE yearmonth = '$contest_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);

$contestName = $row["contest_name"];

$contestIsSalon = ($row["is_salon"] == 1);
$contestIsInternational = ($row["is_international"] == 1);
$contestIsNoToPastAcceptance = ($row["is_no_to_past_acceptance"] == 1);

$contestDescriptionBlob = $row["contest_description_blob"];
$termsConditionsBlob = $row["terms_conditions_blob"];
$contestAnnouncementBlob = $row["contest_announcement_blob"];

$contestFeeStructureBlob = $row["fee_structure_blob"];
$contestDiscountStructureBlob = $row["discount_structure_blob"];

$registrationStartDate = $row["registration_start_date"];
$registrationLastDate = $row["registration_last_date"];
$submissionTimezone = $row['submission_timezone'];
$submissionTimezoneName = $row['submission_timezone_name'];
$judgingStartDate = $row["judging_start_date"];
$judgingEndDate = $row["judging_end_date"];
$resultsDate = $row["results_date"];
$updateStartDate = $row['update_start_date'];
$updateEndDate = $row['update_end_date'];
$exhibitionStartDate = $row["exhibition_start_date"];
$exhibitionEndDate = $row["exhibition_end_date"];

$contestHasJudgingEvent = ($row["has_judging_event"] == 1);
$contestHasExhibition = ($row["has_exhibition"] == 1);
$contestHasCatalog = ($row["has_catalog"] == 1);

$judgingDescriptionBlob = $row["judging_description_blob"];
$judgingVenue = $row["judging_venue"];
$judgingVenueAddress = $row["judging_venue_address"];
$judgingVenueLocationMap = $row["judging_venue_location_map"];
$judgingReportBlob = $row["judging_report_blob"];
$judgingPhotosPhp = $row["judging_photos_php"];

$resultsReady = ($row["results_ready"] == 1);
$resultsDescriptionBlob = $row["results_description_blob"];

$certificatesReady = ($row["certificates_ready"] == 1);

$exhibitionName = $row["exhibition_name"];
$exhibitionDescriptionBlob = $row["exhibition_description_blob"];
$exhibitionVenue = $row["exhibition_venue"];
$exhibitionVenueAddress = $row["exhibition_venue_address"];
$exhibitionVenueLocationMap = $row["exhibition_venue_location_map"];
$exhibitionReportBlob = $row["exhibition_report_blob"];
$exhibitionPhotosPhp = $row["exhibition_photos_php"];
$inaugurationPhotosPhp = $row["inauguration_photos_php"];

$catalogReleaseDate = $row["catalog_release_date"];
$catalogReady = ($row["catalog_ready"] == 1);
$contestCatalog = $row["catalog"];
$contestCatalogDownload = $row["catalog_download"];
$catalogOrderLastDate = $row["catalog_order_last_date"];
$catalogPriceInINR = $row['catalog_price_in_inr'];
$catalogPriceInUSD = $row['catalog_price_in_usd'];
$catalogCanBeOrdered = ($catalogOrderLastDate != null && $catalogOrderLastDate >= date("Y-m-d"));

$contestMaxPics = $row["max_pics_per_entry"];
$contestMaxWidth = $row["max_width"];
$contestMaxHeight = $row["max_height"];
$contestMaxFileSizeInMB = $row['max_file_size_in_mb'];
$contestFeeModel = $row["fee_model"];

$contest_archived = ( $row['archived'] == '1' );

define ("MAX_PIC_WIDTH", $contestMaxWidth);
define ("MAX_PIC_HEIGHT", $contestMaxHeight);
define ("MAX_PIC_FILE_SIZE_IN_MB", $contestMaxFileSizeInMB);

$sql = "SELECT MIN(minimum_group_size) AS minimum_group_size FROM discount WHERE yearmonth = '$contest_yearmonth' AND discount_group = 'GENERAL' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$contestHasDiscounts = (mysqli_num_rows($query) > 0);
$row = mysqli_fetch_array($query);
$discount_min_group_size = $row['minimum_group_size'];

// Get Details of the exhibition
$sql = "SELECT * FROM exhibition WHERE yearmonth = '$contest_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
if ($exhibition = mysqli_fetch_array($query)) {
	$exhibitionSet = true;
	$exhibitionScheduleBlob = $exhibition['schedule_blob'];
	$exhibitionInvitationImg = $exhibition['invitation_img'];
	$exhibitionEmailHeaderImg = $exhibition['email_header_img'];
	$exhibitionEmailMessageBlob = $exhibition['email_message_blob'];
	$exhibitionDignitoryRoles = explode("|", $exhibition['dignitory_roles']);
	$exhibitionDignitoryNames = explode("|", $exhibition['dignitory_names']);
	$exhibitionDignitoryPositions = explode("|", $exhibition['dignitory_positions']);
	$exhibitionDignitoryAvatars = explode("|", $exhibition['dignitory_avatars']);
	$exhibitionDignitoryProfielBlobs = explode("|", $exhibition['dignitory_profile_blobs']);
	$exhibitionIsVirtual = ($exhibition['is_virtual'] == 1);
	$exhibitionVirtualTourReady = ($exhibition['virtual_tour_ready'] == 1);
}
else {
	$exhibitionSet = false;
	$exhibitionScheduleBlob = "";
	$exhibitionInvitationImg = "";
	$exhibitionEmailHeaderImg = "";
	$exhibitionEmailMessageBlob = "";
	$exhibitionDignitoryRoles = [];
	$exhibitionDignitoryNames = [];
	$exhibitionDignitoryPositions = [];
	$exhibitionDignitoryAvatars = [];
	$exhibitionDignitoryProfielBlobs = [];
	$exhibitionIsVirtual = false;
	$exhibitionVirtualTourReady = false;
}



// Get details of the Team
$sql = "SELECT * FROM team WHERE yearmonth = '$contest_yearmonth' ORDER BY level, sequence";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$contestTeam = array();
while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
	$contestTeam[$row['role']] = $row;
}

// define ("DATE_IN_SUBMISSION_TIMEZONE", date("Y-m-d", strtotime_tz("now", $submissionTimezone)));
define ("DATE_IN_SUBMISSION_TIMEZONE", date_tz("now", $submissionTimezone));
define ("REGISTRATION_CLOSED", strtotime_tz("now", $submissionTimezone) > strtotime_tz($registrationLastDate . " 23:45", $submissionTimezone));
define ("THREE_DAYS_TO_JUDGING", date("Y-m-d", strtotime("-3 days", strtotime($judgingStartDate))));

// Generate contestSectionList
//
$sql  = "SELECT * FROM section WHERE yearmonth = '$contest_yearmonth' ORDER BY section_type, section";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

$contestSectionList = array();	// Associative array with section name as the key
$contestDigitalSections = 0;
$contestPrintSections = 0;
$submissionLastDates = array();
$cut_off_table  = "<table class='table' style='margin-left: 30px;'>";
$cut_off_table .= "<tr><th>Section</th><th>Cut-off Score</th></tr>";

while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
	$cut_off_table .= "<tr><td>" . $row['section'] . "</td><td>" . $row['cut_off_score'] . "</td></tr>";
	$contestDigitalSections += ($row['section_type'] == 'D' ? 1 : 0);
	$contestPrintSections += ($row['section_type'] == 'P' ? 1 : 0);
    $contestSectionList[$row["section"]] = $row;
	if (empty($submissionLastDates[$row['submission_last_date']]))
		$submissionLastDates[$row['submission_last_date']] = $row['section'];
	else
		$submissionLastDates[$row['submission_last_date']] .= "," . $row['section'];
}
$cut_off_table .= "</table>";

// Value Substitution Array used in merging blobs
$contest_values = array(
					"contest-name" => $contestName,
					"salon-name" => $contestName,
					"registration-start-date" => date(DATE_FORMAT, strtotime($registrationStartDate)),
					"registration-last-date" => date(DATE_FORMAT, strtotime($registrationLastDate)),
					"max-pic-width" => $contestMaxWidth,
					"max-pic-height" => $contestMaxHeight,
					"max-pic-file-size-in-mb" => $contestMaxFileSizeInMB,
					"submission-timezone-name" => $submissionTimezoneName,
					"judging-start-date" => date(DATE_FORMAT, strtotime($judgingStartDate)),
					"judging-end-date" => date(DATE_FORMAT, strtotime($judgingEndDate)),
					"result-date" => date(DATE_FORMAT, strtotime($resultsDate)),
					"cut-off-table" => $cut_off_table,
					"update-start-date" => date(DATE_FORMAT, strtotime($updateStartDate)),
					"update-end-date" => date(DATE_FORMAT, strtotime($updateEndDate)),
					"judging-venue" => $judgingVenue,
					"judging-venue-address" => $judgingVenueAddress,
					"judging-venue-location-map" => $judgingVenueLocationMap,
					"exhibition-name" => $exhibitionName,
					"exhibition-start-date" => date(DATE_FORMAT, strtotime($exhibitionStartDate)),
					"exhibition-end-date" => date(DATE_FORMAT, strtotime($exhibitionEndDate)),
					"exhibition-venue" => $exhibitionVenue,
					"exhibition-venue-address" => $exhibitionVenueAddress,
					"exhibition-venue-location-map" => $exhibitionVenueLocationMap,
					"exhibition-invitation-img" => $exhibitionInvitationImg,
					"exhibition-email-header-img" => $exhibitionEmailHeaderImg,
					"catalog-release-date" => date(DATE_FORMAT, strtotime($catalogReleaseDate)),
					"catalog-file" => $contestCatalog,
					"chairman-role" => $contestTeam['Chairman']['role_name'],
					"salon-chairman" => $contestTeam['Chairman']['member_name'],
					"secretary-role" => $contestTeam['Secretary']['role_name'],
					"salon-secretary" => $contestTeam['Secretary']['member_name']
					);


// Generate List of Entrant Categories
$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$contest_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

$contestEntrantCategoryList = array();
while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
	$contestEntrantCategoryList[$row['entrant_category']] = $row;

// Enforce Minimum Age. Not set anywhere. Define Minimum Age to 8 and Maximum Age to 120
define("MINIMUM_PARTICIPANT_AGE", "8");			// Not defined anywhere else
define("MAXIMUM_PARTICIPANT_AGE", "120");			// Not defined anywhere else

// Participant's age must be more than MINIMUM_PARTICIPANT_AGE as at the start date of contest
define("LATEST_DOB", date("Y-m-d", strtotime("- " . MINIMUM_PARTICIPANT_AGE . " years")));
// Participant's age must be less than MAXIMUM_PARTICIPANT_AGE as at the results date
define("EARLIEST_DOB", date("Y-m-d", strtotime("- " . MAXIMUM_PARTICIPANT_AGE . " years")));

// Check if the Contest Has Sponsorship Opportunities
$contestHasSponsorship = false;
$sql = "SELECT * FROM opportunity WHERE yearmonth = '$contest_yearmonth' ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$contestHasSponsorship = (mysqli_num_rows($query) > 0 ? true : $contestHasSponsorship);

$sql = "SELECT * FROM award WHERE yearmonth = '$contest_yearmonth' AND sponsored_awards > 0 ";
$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$contestHasSponsorship = (mysqli_num_rows($query) > 0 ? true : $contestHasSponsorship);

// Check if there are discounts
//$sql = "SELECT * FROM discount WHERE yearmonth = '$contest_yearmonth' ";
//$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//$contestHasDiscounts = (mysqli_num_rows($query) > 0);

?>

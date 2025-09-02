<?php
//
// Generate Acceptance Data for Catalog
//
// Modification History:
// 2021/12/21 - (a) Proper casing of names, (b) proper casing of titles, (c) filtered honors list
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");
include("../inc/honors.php");

function die_with_error($errmsg) {
//	$_SESSION['err_msg'] = $errmsg;
//	header("Location: ".$_SERVER['HTTP_REFERER']);
//	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	echo $errmsg;
	die();
}

function safe_name ($str) {
	$ret_str = "";
	for ($i = 0; $i < strlen($str); ++$i) {
		$char = substr($str, $i, 1);
		if ($char == " ")
			$ret_str .= "_";
		else if ( ($char >= "0" && $char <= "9") || ($char >= "A" && $char <="Z") || ($char >= "a" && $char <= "z") )
			$ret_str .= $char;
	}
	return $ret_str;
}

if ( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) ) {

	// Generate Acceptance Data for Catalog Design
	$yearmonth = $_SESSION['admin_yearmonth'];
	//$_SESSION['success_msg'] = "";
	$success_msg = "";

	// Get Contest Name
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		die_with_error("Invalid Contest ID " . $yearmonth);
	$contest = mysqli_fetch_array($query);
	$contest_name = $contest['contest_name'];
	$is_international = ($contest['is_international'] == 1);
	$contest_archived = ($contest['archived'] == 1);

	// Check if sponsors have been assigned to results
	// First check if there are sponsorships for this Salon
	$sql  = "SELECT IFNULL(COUNT(*), 0) AS num_sponsorships, IFNULL(SUM(number_of_units), 0) AS num_awards_sponsored FROM sponsorship ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND sponsorship_type = 'AWARD' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$number_of_sponsorships = $row['num_sponsorships'];
	$number_of_awards_sponsored = $row['num_awards_sponsored'];
	if ($number_of_sponsorships > 0) {
		// Check if the sponsors have been assigned to results
		$sql  = "SELECT IFNULL(COUNT(*), 0) AS results_with_sponsor ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result ";
		else
			$sql .= "  FROM pic_result ";
		$sql .= " WHERE yearmonth = 0 AND sponsorship_no > 0 ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$results_with_sponsor = $row['results_with_sponsor'];
	}

	// $catalog_data_folder = "../../salons/" . $yearmonth . "/catalog_data";
	$catalog_data_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/$yearmonth/catalog_data";

	// Create Catalog Folder if it does not exist
	if (! is_dir($catalog_data_folder))
		mkdir($catalog_data_folder);

	$target_folder = $catalog_data_folder . "/award_list";
	if (! is_dir($target_folder))
		mkdir($target_folder);

	// Create ZIP File
	$zipfile = $target_folder . "/award_list_for_catalog.zip";
	$zip = New ZipArchive;
	if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

	// Get List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($rsec = mysqli_fetch_array($qsec)) {
		$section_name = $rsec['section'];
		// Get Data for the Section
		$sql  = "SELECT profile.profile_id, profile.profile_name, profile.honors, pic.section, pic.title, pic.picfile, pic.total_rating, country.country_name, city, ";
		$sql .= "       award_group, award_name, has_memento, has_gift, cash_award, IFNULL(sponsor_name, '') AS sponsor_name, IFNULL(award_name_suffix, '') AS award_name_suffix ";
		if ($contest_archived)
			$sql .= "  FROM award, ar_pic pic, profile, country, ar_pic_result pic_result ";
		else
			$sql .= "  FROM award, pic, profile, country, pic_result ";
		$sql .= "  LEFT JOIN (sponsorship INNER JOIN sponsor) ";
		$sql .= "    ON sponsorship.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
		$sql .= "   AND sponsorship.link_id = pic_result.award_id ";
		$sql .= "   AND sponsorship.sponsorship_no = pic_result.sponsorship_no ";
		$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section = '$section_name' ";
		$sql .= "   AND award.level < '99' ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND profile.profile_id = pic.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " ORDER BY award_group, award.level, award.sequence, profile.profile_name ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {

			// Open a CSV file for writing Data
			$data_file = $target_folder . "/" . $rsec['stub'] . "_award_table_for_catalog.csv";
			$csv_file = fopen($data_file, "w");

			// Write Column titles
			if ($number_of_sponsorships > 0)
				fputcsv($csv_file, array("Award Name", "Picture Title", "Author", "Honors", "Location", "Value", "Sponsor", "Award Suffix"));
			else
				fputcsv($csv_file, array("Award Name", "Picture Title", "Author", "Honors", "Location"));

			$profile_id = 0;
			while ($res = mysqli_fetch_array($query)) {
				$pic_title = format_title($res['title']);
				$profile_name = format_name($res['profile_name']);
				$honors = honors_text($res['honors'], $res['profile_id']);
				$place = $is_international ? format_place($res['city']) . ", " . $res['country_name'] : format_place($res['city']);
				$award_description = $res['has_memento'] == '1' ? "Memento" : "";
				$award_description = $res['has_gift'] == '1' ? "Gift" : $award_description;
				$award_description = $res['cash_award'] > 0 ? sprintf("Rs. %d", $res['cash_award']) : $award_description;
				if ($number_of_sponsorships > 0)
					fputcsv($csv_file, array($res['award_name'], $pic_title, $profile_name, $honors, $place,
											 $award_description, $res['sponsor_name'], $res['award_name_suffix']));
				else
					fputcsv($csv_file, array($res['award_name'], $pic_title, $profile_name, $honors, $place));
			}
			// Add CSV File to ZIP
			fclose($csv_file);
			if ( ! $zip->addFile($data_file, basename($data_file)) )
				die_with_error("Error adding " . basename($data_file) . " to ZIP");
		}
	}

	// Download ZIP File
	$zip->close();
	header('Content-Type: application/zip');
	header('Content-disposition: attachment; filename=' . basename($zipfile));
	header('Content-Length: ' . filesize($zipfile));
	readfile($zipfile);
}
else
	die_with_error("Invalid Parameters");
?>

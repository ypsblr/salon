<?php
//
// Generate Acceptance Data for Catalog
//
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

function die_with_error($errmsg) {
//	$_SESSION['err_msg'] = $errmsg;
//	header("Location: ".$_SERVER['HTTP_REFERER']);
//	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	echo $errmsg;
	die();
}

function die_with_sql_error($sql, $errmsg, $file, $line) {
	log_sql_error($sql, $errmsg, $file, $line);
	echo "Database operation failed !";
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

function zip_error($code) {
	switch ($code) {
		case ZipArchive::ER_EXISTS : return "ZIP File already exists";
		case ZipArchive::ER_INCONS : return "Corrupt ZIP File";
		case ZipArchive::ER_INVAL : return "Invalid Arguments";
		case ZipArchive::ER_MEMORY : return "Insufficent Memory to perform the operation";
		case ZipArchive::ER_NOENT : return "File Not Found";
		case ZipArchive::ER_NOZIP : return "Not a ZIP File";
		case ZipArchive::ER_OPEN : return "Unable to open the file";
		case ZipArchive::ER_READ : return "Unable to read data";
		case ZipArchive::ER_SEEK : return "Unable to seek specific file in the ZIP";
		default : return "No Error";
	}
}

		function debug_to_console($data) {
            $output = $data;
            if (is_array($output))
                $output = implode(',', $output);
        
            echo "<script>alert('Debug Objects: " . $output . "' );</script>";
        }


if (isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id'])) {
	// Generate Acceptance Data for Catalog Design
	$yearmonth = $_SESSION['admin_yearmonth'];
	$success_msg = "";
	// $for = $_REQUEST["for"];

	// Get Contest Name
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		die_with_error("Invalid Contest ID " . $yearmonth);
	$contest = mysqli_fetch_array($query);
	$contest_name = $contest['contest_name'];
	$contest_archived = ($contest['archived'] == '1');

	$salon_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/" . $yearmonth;
	$data_folder = $salon_folder . "/reporting_data";

	// Create Catalog Folder if it does not exist
	if (! file_exists($salon_folder))
		mkdir($salon_folder);
	if (! file_exists($data_folder))
		mkdir($data_folder);

	// Get a list of patronages
	$sql = "SELECT short_code FROM recognition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$csv_for_list = [];
	while ($row = mysqli_fetch_array($query)) {
		if (in_array($row['short_code'], ['PSA', 'FIAP', 'FIP', 'MOL'])) {
			$csv_for_list[] = $row['short_code'];
			if (! file_exists($data_folder . "/" . $row['short_code']))
				mkdir($data_folder . "/" . $row['short_code']);
		}
	}

    array_push($csv_for_list, 'REST');
	if (! file_exists($data_folder . "/" . 'REST'))
		mkdir($data_folder . "/" . 'REST');

	// Get a List of sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$section_query = mysqli_query($DBCON, $sql) or die_with_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$stub_list = [];

	while ($section_row = mysqli_fetch_array($section_query)) {
		$section_name = $section_row['section'];
		$section_stub = $section_row['stub'];
		$stub_list[] = $section_stub;

		// Get Data for the section
		// Get 1 picture for each author, with preference for picture with just acceptance as other pictures would already have been printed
		$sql  = "SELECT profile.profile_id, profile.profile_name, profile.first_name, profile.last_name, profile.email, country.country_name, ";
		$sql .= "       profile.yps_login_id, profile.club_id, profile.salutation, ";
		$sql .= "       IFNULL(club.club_name, '') AS club_name, IFNULL(club.club_type, '') AS club_type, ";
		$sql .= "       pic.title, award.level, award.award_name, award.recognition_code ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result, ar_entry entry, entrant_category, award, ar_pic pic, ";
		else
			$sql .= "  FROM pic_result, entry, entrant_category, award, pic, ";
		$sql .= "       profile LEFT JOIN club ON club.club_id = profile.club_id, country ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND entry.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND entry.profile_id = pic_result.profile_id ";
		$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
		$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
		$sql .= "   AND entrant_category.acceptance_reported = '1' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section = '$section_name' ";
		$sql .= "   AND award.award_group = entrant_category.award_group ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND profile.profile_id = pic_result.profile_id ";
		$sql .= "   AND country.country_id = profile.country_id ";
		$sql .= " ORDER BY profile.last_name, profile.first_name, profile.profile_id, pic.title ";
		$query = mysqli_query($DBCON, $sql) or die_with_sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		
		
// 		$psasql  = "SELECT profile.profile_id, profile.profile_name, profile.first_name, profile.last_name, profile.email, country.country_name, ";
// 		$psasql .= "       profile.yps_login_id, profile.club_id, profile.salutation, ";
// 		$psasql .= "       IFNULL(club.club_name, '') AS club_name, IFNULL(club.club_type, '') AS club_type, ";
// 		$psasql .= "       pic.title ";
// 		if ($contest_archived)
// 			$psasql .= "  FROM ar_entry entry, entrant_category, ar_pic pic, ";
// 		else
// 			$psasql .= "  FROM entry, entrant_category, pic, pic_result ";
// 		$psasql .= "   AND entry.yearmonth = '$yearmonth' ";
// 		$psasql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
// 		$psasql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
// 		$psasql .= "   AND entrant_category.acceptance_reported = '1' ";
// 		$psasql .= "   AND pic.yearmonth = '$yearmonth' ";
// 		$psasql .= "   AND pic.profile_id = pic_result.profile_id ";
// 		$psasql .= "   AND pic.pic_id = pic_result.pic_id ";
// 		$psasql .= "   AND profile.profile_id = pic_result.profile_id ";
// 		$psasql .= "   AND country.country_id = profile.country_id ";
// 		#psasql .= "   AND pic.pic_id not in (select pic_result.pic_id)"
// 		$psasql .= " ORDER BY profile.last_name, profile.first_name, profile.profile_id, pic.title ";
// 		$psaquery = mysqli_query($DBCON, $psasql) or die_with_sql_error($psasql, mysqli_error($DBCON), __FILE__, __LINE__);


        $psasql = "SELECT profile.profile_name, profile.first_name, profile.last_name, profile.email, country.country_name, ";
        $psasql .= " profile.yps_login_id, profile.club_id, profile.salutation, pic.title FROM `pic`, profile, country ";
        $psasql .= " where pic.yearmonth=202407 ";
        $psasql .= " and concat(pic.profile_id, pic.pic_id) not in (SELECT concat(profile_id, pic_id) ";
        $psasql .= " from pic_result where yearmonth=202407) ";
        $psasql .= " AND pic.profile_id = profile.profile_id AND profile.country_id = country.country_id ";
        $psasql .= " AND pic.section = '$section_name' ";
        $psasql .= " ORDER BY profile.last_name, profile.first_name, profile.profile_id, pic.title ";
        $psaquery = mysqli_query($DBCON, $psasql) or die_with_sql_error($psasql, mysqli_error($DBCON), __FILE__, __LINE__);

		if (mysqli_num_rows($query) > 0) {
			// Open a CSV file for writing Data
			$csv_file = [];
			foreach ($csv_for_list as $csv_for) {
				$csv_file[$csv_for] = fopen("$data_folder/$csv_for/$section_stub" . "_acceptance_list.csv", "w");

				// Write Column titles
				switch ($csv_for) {
					case "PSA" : {
						if (isset($csv_file['PSA']))
							fputcsv($csv_file['PSA'], array("Family Name", "Given Name", "Country", "Image Title", "Email", "Award"));
						break;
					}
					case "REST" : {
						if (isset($csv_file['REST']))
							fputcsv($csv_file['REST'], array("Family Name", "Given Name", "Country", "Image Title", "Email", "Award"));
						break;
					}
					case "FIAP" : {
						if (isset($csv_file['FIAP']))
							fputcsv($csv_file['FIAP'], array("Family Name", "Given Name", "Country", "Image Title", "FIAP Award", "Other Award"));
						break;
					}
					case "FIP" : {
						if (isset($csv_file['FIP']))
							fputcsv($csv_file['FIP'], array("Family Name", "First Name", "Country", "Email", "Image Title", "Award Name", "Club Name"));
						break;
					}
					case "MOL" : {
						if (isset($csv_file['MOL']))
							fputcsv($csv_file['MOL'], array("Salutation", "Family Name", "Given Name", "Country", "Email", "Image Title", "Award"));
						break;
					}
				}
			}

			// Process Rows
			while ($res = mysqli_fetch_array($query)) {
				$last_name = ucwords(strtolower($res['last_name']));
				$first_name = ucwords(strtolower($res['first_name']));
				$country = $res['country_name'];
				$pic_title = ucwords(strtolower($res['title']));
				$email = $res['email'];
				$reco = $res['recognition_code'];
				$level = $res['level'];
				$award_name = $res['award_name'];

				// Write CSV for PSA
				if (isset($csv_file['PSA'])) {
					$award_code = "A";	// default to acceptance
					$award_code = ($reco == "PSA" && $level == 1) ? "G" : $award_code;
					$award_code = ($reco == "PSA" && $level > 1 && $level < 9) ? "P" : $award_code;
					$award_code = ($reco != "PSA" && $level < 9) ? "M" : $award_code;
					$award_code = ($level == 9) ? "H" : $award_code;
					fputcsv($csv_file['PSA'], array($last_name, $first_name, $country, $pic_title, $email, $award_code));
				}
				// Write CSV for FIAP
				if (isset($csv_file['FIAP'])) {
					$fiap_award = "";		// Defaults to blank for Acceptance
					$other_award = "";
					if ($level < 99) {
						$fiap_award = ($reco == "FIAP") ? $award_name : $fiap_award;
						$other_award = ($reco != "FIAP") ? $award_name : $other_award;
					}
					fputcsv($csv_file['FIAP'], array($last_name, $first_name, $country, $pic_title, $fiap_award, $other_award));
				}
				// Write CSV for FIP
				if (isset($csv_file['FIP'])) {
					$club_name = ($res['yps_login_id'] != "") ? "Youth Photographic Society" : $res['club_name'];
					$club_name = ($res['club_type'] == 'CLUB') ? $club_name : "";
					$club_name = preg_match("/\*\*/", $club_name) ? "" : $club_name;
					$award_name = ($level < 99) ? $award_name : "";
					fputcsv($csv_file['FIP'], array($last_name, $first_name, $country, $email, $pic_title, $award_name, $club_name));
				}
				// Write CSV for MOL
				if (isset($csv_file['MOL'])) {
					$salutation = $res['salutation'];
					$award_name = ($level < 99) ? $award_name : "";
					fputcsv($csv_file['MOL'], array($salutation, $last_name, $first_name, $country, $email, $pic_title, $award_name));
				}
			}
			
			while ($res = mysqli_fetch_array($psaquery)) {
				$last_name = ucwords(strtolower($res['last_name']));
				$first_name = ucwords(strtolower($res['first_name']));
				$country = $res['country_name'];
				$pic_title = ucwords(strtolower($res['title']));
				$email = $res['email'];
				// $reco = $res['recognition_code'];
				// $level = $res['level'];
				$award_code = 'R'; // Rejected pic
				
				fputcsv($csv_file['REST'], array($last_name, $first_name, $country, $pic_title, $email, $award_code));

			}


		}
	}

	// Close files
	foreach ($csv_for_list as $csv_for)
		fclose($csv_file[$csv_for]);

	// Generate ZIP File
	$zipfile = $data_folder . "/reporting_data.zip";
	$zip = New ZipArchive;
	$zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	if ($zip_open) {
		foreach ($csv_for_list as $csv_for) {
			foreach ($stub_list as $stub) {
				$file_path = "$data_folder/$csv_for/$stub" . "_acceptance_list.csv";
				if (file_exists($file_path)) {
					$zip_path = "$csv_for/$stub" . "_acceptance_list.csv";
					if ( ! $zip->addFile($file_path, $zip_path))
						die_with_error("Error adding " . $zip_path . " to ZIP");
				}
			}
		}
	}
	else
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

	$zip->close();

	// Send headers to download
	header('Content-Type: application/zip');
	header('Content-disposition: attachment; filename=reporting_data.zip');
	header('Content-Length: ' . filesize($zipfile));
	readfile($zipfile);
}
else
	die_with_error("Invalid Parameters");
?>

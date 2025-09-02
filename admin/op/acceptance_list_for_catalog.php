<?php
//
// Generate Acceptance Data for Catalog
//
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

function th($term, $rows=0, $cols=0) {
	return "<th" . ($rows > 1 ? " rowspan='" . $rows . "'" : "") . ($cols > 1 ? " colspan='" . $cols . "'" : "") . ">" . $term . "</th>" . chr(13) . chr(10);
}

function td($term, $rows=0, $cols=0) {
	return "<td" . ($rows > 1 ? " rowspan='" . $rows . "'" : "") . ($cols > 1 ? " colspan='" . $cols . "'" : "") . ">" . $term . "</td>". chr(13) . chr(10);
}

function excel_escape_double_quote($text) {
	$out = "";
	for ($i = 0; $i < strlen($text); ++ $i) {
		$c = substr($text, $i, 1);
		if ($c == '"')
			$out .= '""';
		else
			$out .= $c;
	}
	return $out;
}

function array_double_quote($terms) {
	$target = [];
	foreach($terms as $term) {
		$target[] = '"' . excel_escape_double_quote($term) . '"';
	}
	return $target;
}

function excel_concat($terms) {
	return "=" . implode("&CHAR(10)&", array_double_quote($terms));
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

	$catalog_data_folder = $_SERVER['DOCUMENT_ROOT'] . "/generated/$yearmonth/catalog_data";

	// Create Catalog Folder if it does not exist
	if (! is_dir($catalog_data_folder))
		mkdir($catalog_data_folder);

	$target_folder = $catalog_data_folder . "/acceptance_list";
	if (! is_dir($target_folder))
		mkdir($target_folder);

	// Create ZIP File
	$zipfile = $target_folder . "/acceptance_list_for_catalog.zip";
	$zip = New ZipArchive;
	if ( ! ($zip_open = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) )
		die_with_error("Error " . zip_error($zip_open) . " while creating $zipfile");

	// Get List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$qsec = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($rsec = mysqli_fetch_array($qsec)) {
		$section_name = $rsec['section'];

		// Border Style
		$html = "<style>table, th, td { border: 1px solid #aaa; border-collapse: collapse; }</style>" . chr(13) . chr(10);
		$html .= "<table>" . chr(13) . chr(10);
		// $html .= "<tr><th>Author</th><th>Honors</th><th>Cleaned Honors</th><th>Title</th><th>City</th></tr>";
		$html .= "<tr><th>Name</th><th>Honors</th><th>Title</th><th>City, Country</th></tr>" . chr(13) . chr(10);

		// Get a list of Profile IDs
		// Exclude Youth
		// Limit to Section
		$sql  = "SELECT profile.profile_id, profile_name, honors, city, IFNULL(country_name, '') AS country_name, COUNT(*) AS num_acceptances ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, ar_entry entry, entrant_category, ";
		else
			$sql .= "  FROM pic_result, award, pic, entry, entrant_category, ";
		$sql .= "       profile LEFT JOIN country ON country.country_id = profile.country_id ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section != 'CONTEST' ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND pic.section = '$section_name' ";
		$sql .= "   AND entry.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND entry.profile_id = pic_result.profile_id ";
		$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
		$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
		$sql .= "   AND entrant_category.acceptance_reported = '1' ";
		$sql .= "   AND profile.profile_id = entry.profile_id ";
		$sql .= " GROUP BY profile.profile_id, profile_name, honors, city, country_name ";
		$sql .= " ORDER BY country_name, profile_name ";
		$qprof = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = 2;
		while ($profile = mysqli_fetch_array($qprof)) {
			$profile_id = $profile['profile_id'];
			$number_of_acceptances = $profile['num_acceptances'];
			$author = format_name($profile['profile_name']);
			$honors = honors_text($profile['honors'], $profile['profile_id']);

			$location = $is_international ? format_place($profile['city']) . ", " . $profile['country_name'] : format_place($profile['city']);

			// Get Data for the Section
			$sql  = "SELECT pic.title ";
			if ($contest_archived)
				$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic ";
			else
				$sql .= "  FROM pic_result, award, pic ";
			$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
			$sql .= "   AND pic_result.profile_id = '$profile_id' ";
			$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND award.award_id = pic_result.award_id ";
			$sql .= "   AND award.section != 'CONTEST' ";
			$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND pic.profile_id = pic_result.profile_id ";
			$sql .= "   AND pic.pic_id = pic_result.pic_id ";
			$sql .= "   AND pic.section = '$section_name' ";
			$sql .= " ORDER BY pic.title ";
			$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($qpic) > 0) {
				$first = true;
				while ($rpic = mysqli_fetch_array($qpic)) {
					$pic_title = format_title($rpic['title']);
					$html .= "<tr>";
					$html .= $first ? td($author, $number_of_acceptances) : "";
					// $html .= $first ? td($honors, $number_of_acceptances) : "";
					$html .= $first ? td($honors, $number_of_acceptances) : "";
					$html .= td($pic_title);
					$html .= $first ? td($location, $number_of_acceptances) : "";
					$html .= "</tr>";
					$first = false;
					++ $row;
				}
			}
		}
		$html .= "</table>" . chr(13) . chr(10);

		// Write out Data
		$data_file = $target_folder . "/" . $rsec['stub'] . "_acceptance_table_for_catalog.html";
		file_put_contents($data_file, $html);
		if ( ! $zip->addFile($data_file, basename($data_file)) )
			die_with_error("Error adding " . basename($data_file) . " to ZIP");
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

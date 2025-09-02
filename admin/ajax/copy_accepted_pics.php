<?php
//header('Content-Type: text/csv; charset=utf-8');
//header('Content-Disposition: attachment; filename=acceptance_data.csv');
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("ajax_lib.php");

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

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) ) {

    // Set contest
    $yearmonth = $_SESSION["admin_yearmonth"];

	define("SALON_HOME", $_SERVER['DOCUMENT_ROOT']);
	define("AVATAR_FOLDER", SALON_HOME . "/res/avatar");
	define("GENFILES_FOLDER", SALON_HOME . "/generated/" . $yearmonth);
	define("LOG_FILE", GENFILES_FOLDER . "/logs/copy_errors.txt");

	if (! is_dir(GENFILES_FOLDER)) {
		mkdir(GENFILES_FOLDER);
	}
	if (! is_dir(GENFILES_FOLDER . "/logs")) {
		mkdir(GENFILES_FOLDER . "/logs");
	}

	$output = fopen(LOG_FILE, 'w');

	// Copy Awarded Pictures under each Section
	// $target_path = "../../salons/" . $yearmonth . "/upload/Accepted";
	$target_path = GENFILES_FOLDER . "/ACCEPTED";
	if (! is_dir($target_path))
		mkdir($target_path);

	// Determine if contest is archived
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Get a List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	while ($srow = mysqli_fetch_array($query)) {

		$section = $srow['section'];

		// Check if Directory exists
		$section_path = "../../salons/" . $yearmonth . "/upload/" . $section;
		if (file_exists($section_path)) {
			$target_folder = $target_path . "/" . $srow['stub'];
			// $target_path = $section_path . "/Award";
			if (! file_exists($target_folder))
				mkdir($target_folder);

			// Get a list of pictures accepted and copy them
			$sql  = "SELECT pic.profile_id, profile.profile_name, pic.pic_id, pic.title, pic.picfile ";
			if ($contest_archived)
				$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile ";
			else
				$sql .= "  FROM pic_result, award, pic, profile ";
			$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
			$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND award.award_id = pic_result.award_id ";
			$sql .= "   AND award.level = 99 ";
			$sql .= "   AND award.section != 'CONTEST' ";
			$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND pic.section = '$section' ";
			$sql .= "   AND pic.profile_id = pic_result.profile_id ";
			$sql .= "   AND pic.pic_id = pic_result.pic_id ";
			$sql .= "   AND profile.profile_id = pic.profile_id ";
			$sql .= " ORDER BY pic.profile_id, pic.pic_id ";
			$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
			$num_pics = 0;
			while ($rpic = mysqli_fetch_array($qpic)) {
				$source_file = $section_path . "/" . $rpic['picfile'];
				$target_file = $target_folder . "/" . sprintf("%04d-%02d-%s-%s.jpg", $rpic['profile_id'], $rpic['pic_id'], safe_name($rpic['profile_name']), safe_name($rpic['title']));
				if (! copy($source_file, $target_file))
					fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $rpic['profile_name'] . " under " . $section . chr(13) . chr(10));
				++ $num_pics;
			}
			echo printf("Copied %d pictures under %s\r\n", $num_pics, $section);
		}
		else {
			fprintf($output, "Could not find folder " . $section_path . chr(13) . chr(10));
			die();
		}

	}

	fclose($output);
}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>

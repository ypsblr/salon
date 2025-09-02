<?php
// Archive Pictures without Acceptances
// ====================================
// "Moves" all pictures related to Pictures not having any Awards and Acceptances
//        to an Archive Folder named like "ARCH" under each section
// At this juncture, retains all Thumbnails for the purpose of Certificate Generation and other screens
//
// This will permit us to remove older folders which do not have acceptances after downloading them,
// 		 to release space for future Salons.
// Archived Pictures are not accessed in any screen
//
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("../inc/lib.php");

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

	$output = fopen('copy_errors.text', 'w');

	// Do not do anything for Salon in progress
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	if (date("Y-m-d") <= $contest['exhibition_end_date']) {
		fprintf($output, "Cannot run the option for Salon(s) in progress." . chr(13) . chr(10));
		die();
	}

	// Get a List of Sections
	$sql = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($srow = mysqli_fetch_array($query)) {

		$section = $srow['section'];

		// Check if Directory exists
		$section_path = "../../salons/" . $yearmonth . "/upload/" . $section;
		if (is_dir($section_path)) {

			// Create an Acceptance Folder if it does not exist
			$target_path = "../../salons/" . $yearmonth . "/upload/" . $section . "/ARCH";
			if (! is_dir($target_path)) {
				mkdir($target_path);
			}

			// 1 - Process pictures not having picture awards
			//     Move them to the Archive Folder
			$sql  = "SELECT pic.profile_id, pic.pic_id, pic.picfile, SUM(IFNULL(pic_result.award_id, 0)) AS is_pic_accepted ";
			$sql .= "  FROM pic ";
			$sql .= "  LEFT JOIN pic_result ";
			$sql .= "         ON pic_result.yearmonth = pic.yearmonth ";
			$sql .= "        AND pic_result.profile_id = pic.profile_id ";
			$sql .= "        AND pic_result.pic_id = pic.pic_id ";
			$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
			$sql .= "   AND pic.section = '$section' ";
			$sql .= " GROUP BY pic.profile_id, pic.pic_id, pic.picfile ";
			$sql .= " ORDER BY pic.profile_id, pic.pic_id ";
			$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$pics_to_retain = array();
			$num_pics = 0;
			while ($rpic = mysqli_fetch_array($qpic)) {
				if ($rpic['is_pic_accepted']) {
					// Keep a list of picture files to retain
					$pics_to_retain[] = $rpic['picfile'];
				}
				else {
					// Move the Non-Accepted Image File to Archive Folder
					// Check if file exists
					if (file_exists($section_path . "/" . $rpic['picfile'])) {
						rename($section_path . "/" . $rpic['picfile'], $target_path . "/" . $rpic['picfile']);
					}
					else {
						// If file is in the Archive
						if (! file_exists($target_path . "/" . $rpic['picfile']))
							fprintf($output, "%s : Error moving %s (%d/%d) : %s\r\n", $section, $rpic['picfile'], $rpic['profile_id'], $rpic['pic_id'], "File does not exist !");
					}
				}
				++ $num_pics;
			}
			fprintf($output, "%s : Archived %d pictures\r\n", $section, $num_pics);

			// 2. Copy Orphan files that were uploaded and then replaced
			//
			$pics_on_server = scandir($section_path);
			$excess_pics = array_diff($pics_on_server, $pics_to_retain);
			foreach($excess_pics as $ep) {
				if ($ep != "." && $ep != ".." && (! is_dir($sectionpath . "/" . $ep)) )
					rename($section_path . "/" . $ep, $target_path . "/" . $ep);
			}
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

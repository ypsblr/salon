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

	// Avatar Folder
	// $avatar_folder = $_SERVER['DOCUMENT_ROOT'] . "../../res/avatar";
	$avatar_folder = AVATAR_FOLDER;

	// Copy Awarded Pictures under each Section
	// $target_path = "../../salons/" . $yearmonth . "/upload/Awarded";
	$target_path = GENFILES_FOLDER . "/AWARDED";
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
		$section_path = SALON_HOME . "/salons/" . $yearmonth . "/upload/" . $section;
		if (is_dir($section_path)) {
			// Create an Acceptance Folder if it does not exist
			$target_folder = $target_path . "/" . $srow['stub'];
			// $target_path = $section_path . "/Award";
			if (! is_dir($target_folder))
				mkdir($target_folder);

			// Get a list of pictures awarded and copy them
			$sql  = "SELECT pic.profile_id, profile.profile_name, profile.avatar, pic.pic_id, pic.title, pic.picfile, award.award_name ";
			if ($contest_archived)
				$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile ";
			else
				$sql .= "  FROM pic_result, award, pic, profile ";
			$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
			$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND award.award_id = pic_result.award_id ";
			$sql .= "   AND award.level <= 9 ";
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
				$profile_id = $rpic['profile_id'];
				$pic_id = $rpic['pic_id'];
				$profile_name = safe_name($rpic['profile_name']);
				$title = safe_name($rpic['title']);
				$award_name = safe_name($rpic['award_name']);
				// Copy Picture
				$target_file = $target_folder . "/" . sprintf("%04d-%02d-PIC-%s-%s-%s.jpg", $profile_id, $pic_id, $profile_name, $title, $award_name);
				if (! copy($source_file, $target_file))
					fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $profile_name . " under " . $section . chr(13) . chr(10));
				++ $num_pics;
				// Copy Avatar if present
				if ($rpic['avatar'] != "" && $rpic['avatar'] != "user.jpg" && file_exists($avatar_folder . "/" . $rpic['avatar'])) {
					// Copy Avatar into the same folder
					$target_avatar = $target_folder . "/" . sprintf("%04d-%02d-AVATAR-%s.jpg", $profile_id, $pic_id, $profile_name);
					if (! copy($avatar_folder . "/" . $rpic['avatar'], $target_avatar))
						fprintf($output, "Error copying avatar : " . $rpic['avatar'] . " of " . $profile_name . " under " . $section . chr(13) . chr(10));
				}
			}
			printf("Copied %d pictures under %s.\r\n", $num_pics, $section);
		}
		else {
			fprintf($output, "Could not find folder " . $section_path . chr(13) . chr(10));
			die();
		}

	}

	//
	// Copy All Pictures related to Best Entrant(s)
	//
	$sql  = "SELECT award.award_id, award.award_name, profile.profile_id, profile.profile_name, profile.avatar ";
	$sql .= "  FROM entry_result, award, profile ";
	$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND profile.profile_id = entry_result.profile_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	if (mysqli_num_rows($query) > 0) {
		// $target_path = "../../salons/" . $yearmonth . "/upload/BestParticipants";
		$target_path = GENFILES_FOLDER . "/BEST_PARTICIPANTS";
		if (! is_dir($target_path))
			mkdir($target_path);
	}
	while ($row = mysqli_fetch_array($query)) {
		$profile_id = $row['profile_id'];

		// Create a Folder for the profile
		$target_folder = $target_path . "/" . $profile_id . "_" . safe_name($row['profile_name']);
		if (! is_dir($target_folder))
			mkdir($target_folder);

		// Copy Avatar if present
		if ($row['avatar'] != "" && $row['avatar'] != "user.jpg" && file_exists($avatar_folder . "/" . $row['avatar'])) {
			$target_avatar = $target_folder . "/" . sprintf("AVATAR-%04d-%s.jpg", $profile_id, $profile_name);
			if (! copy($avatar_folder . "/" . $row['avatar'], $target_avatar))
				fprintf($output, "Error copying avatar : " . $row['avatar'] . " of " . $profile_name . " under Best Entrants " . chr(13) . chr(10));
		}

		// Get a list of pictures awarded and copy them
		$sql  = "SELECT pic.pic_id, pic.title, pic.picfile, award.award_name, pic.section ";
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
		$sql .= " ORDER BY pic.pic_id ";
		$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
		$num_pics = 0;
		while ($rpic = mysqli_fetch_array($qpic)) {
			$source_file = "../../salons/" . $yearmonth . "/upload/" . $rpic['section'] . "/" . $rpic['picfile'];
			$pic_id = $rpic['pic_id'];
			$title = safe_name($rpic['title']);
			$award_name = safe_name($rpic['award_name']);
			$target_file = $target_folder . "/" . sprintf("PIC-%02d-%s-%s.jpg", $rpic['pic_id'], $title, $award_name);
			if (! copy($source_file, $target_file))
				fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $row['profile_name'] . " under " . $row['profile_name'] . chr(13) . chr(10));
			++ $num_pics;
		}
		printf("Copied %d awarded/accepted pictures under %s.\r\n", $num_pics, $row['profile_name']);
	}

	//
	// Copy All Award Winning Pictures + 1 accepted picture for each club member related to Best Club(s)
	//
	$sql  = "SELECT award.award_id, award.award_name, club.club_id, club.club_name ";
	$sql .= "  FROM club_result, award, club ";
	$sql .= " WHERE club_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = club_result.yearmonth ";
	$sql .= "   AND award.award_id = club_result.award_id ";
	$sql .= "   AND club.club_id = club_result.club_id ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	if (mysqli_num_rows($query) > 0) {
		// $target_path = "../../salons/" . $yearmonth . "/upload/BestClubs";
		$target_path = GENFILES_FOLDER . "/BEST_CLUBS";
		if (! is_dir($target_path))
			mkdir($target_path);
	}
	while ($row = mysqli_fetch_array($query)) {
		$club_id = $row['club_id'];

		// Create a Folder for the profile
		$target_folder = $target_path . "/" . $club_id . "_" . safe_name($row['club_name']);
		if (! is_dir($target_folder))
			mkdir($target_folder);

		// Get a list of pictures awarded and copy them
		$sql  = "SELECT pic.profile_id, profile.profile_name, pic.pic_id, pic.title, pic.picfile, award.award_name, award.level, pic.section ";
		if ($contest_archived)
			$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile ";
		else
			$sql .= "  FROM pic_result, award, pic, profile ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section != 'CONTEST' ";
		$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND pic.profile_id = pic_result.profile_id ";
		$sql .= "   AND pic.pic_id = pic_result.pic_id ";
		$sql .= "   AND profile.profile_id = pic.profile_id ";
		$sql .= "   AND profile.club_id = '$club_id' ";
		$sql .= " ORDER BY pic.profile_id, award.level ASC, pic.total_rating DESC ";
		$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
		$num_pics = 0;
		$profile_list = [];
		while ($rpic = mysqli_fetch_array($qpic)) {
			if ( ($rpic['level'] < 99) || ( $rpic['level'] == 99 && (! in_array($rpic['profile_id'], $profile_list)) ) ) {
				if ( ! in_array($rpic['profile_id'], $profile_list) )
					$profile_list[] = $rpic['profile_id'];
				$source_file = SALON_HOME . "/salons/" . $yearmonth . "/upload/" . $rpic['section'] . "/" . $rpic['picfile'];
				$profile_id = $rpic['profile_id'];
				$pic_id = $rpic['pic_id'];
				$profile_name = safe_name($rpic['profile_name']);
				$title = safe_name($rpic['title']);
				$award_name = safe_name($rpic['award_name']);

				// Copy Picture
				$target_file = $target_folder . "/" . sprintf("%04d-PIC-%02d-%s-%s-%s.jpg", $profile_id, $pic_id, $profile_name, $title, $award_name);
				if (! copy($source_file, $target_file))
					fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $profile_name . " under " . $row['club_name'] . chr(13) . chr(10));

				// Copy Avatar if present
				if ($rpic['avatar'] != "" && $rpic['avatar'] != "user.jpg" && file_exists($avatar_folder . "/" . $rpic['avatar'])) {
					$target_avatar = $target_folder . "/" . sprintf("%04d-AVATAR-%s.jpg", $profile_id, $profile_name);
					if (! copy($avatar_folder . "/" . $rpic['avatar'], $target_avatar))
						fprintf($output, "Error copying avatar : " . $rpic['avatar'] . " of " . $profile_name . " under Best Entrants " . chr(13) . chr(10));
				}
				++ $num_pics;
			}
		}
		printf("Copied %d awarded/accepted pictures under %s.\r\n", $num_pics, $row['club_name']);
	}

	//
	// Copy All Award Winning Pictures related to YPS
	//
	// $target_folder = "../../salons/" . $yearmonth . "/upload/YPS";
	$target_folder = GENFILES_FOLDER . "/YPS";
	if (! file_exists($target_folder))
		mkdir($target_folder);

	// Get a list of pictures awarded and copy them
	$sql  = "SELECT pic.profile_id, profile.profile_name, profile.yps_login_id, pic.pic_id, pic.title, pic.picfile, ";
	$sql .= "       award.award_name, award.level, pic.section, profile.avatar ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile ";
	else
		$sql .= "  FROM pic_result, award, pic, profile ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.level <= 9 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$sql .= "   AND profile.yps_login_id != '' ";
	$sql .= " ORDER BY pic.profile_id, award.level ASC, pic.total_rating DESC ";
	$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
	$num_pics = 0;
	$profile_list = [];
	while ($rpic = mysqli_fetch_array($qpic)) {
		$source_file = SALON_HOME . "/salons/" . $yearmonth . "/upload/" . $rpic['section'] . "/" . $rpic['picfile'];
		$profile_id = $rpic['profile_id'];
		$yps_login_id = $rpic['yps_login_id'];
		$pic_id = $rpic['pic_id'];
		$profile_name = safe_name($rpic['profile_name']);
		$title = safe_name($rpic['title']);
		$award_name = safe_name($rpic['award_name']);

		// Copy picture
		$target_file = $target_folder . "/" . sprintf("%s-%04d-%02d-PIC-%s-%s-%s.jpg", $yps_login_id, $profile_id, $pic_id, $profile_name, $title, $award_name);
		if (! copy($source_file, $target_file))
			fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $profile_name . " under " . $row['club_name'] . chr(13) . chr(10));
		++ $num_pics;
		// Copy Avatar if present
		if ($rpic['avatar'] != "" && $rpic['avatar'] != "user.jpg" && file_exists($avatar_folder . "/" . $rpic['avatar'])) {
			$target_avatar = $target_folder . "/" . sprintf("%s-%04d-%02d-AVATAR-%s.jpg", $yps_login_id, $profile_id, $pic_id, $profile_name);
			if (! copy($avatar_folder . "/" . $rpic['avatar'], $target_avatar))
				fprintf($output, "Error copying avatar : " . $rpic['avatar'] . " of " . $profile_name . " under " . $section . chr(13) . chr(10));
		}
	}
	printf("Copied %d awarded pictures under YPS\r\n", $num_pics);

	fclose($output);
}
else
    debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
?>

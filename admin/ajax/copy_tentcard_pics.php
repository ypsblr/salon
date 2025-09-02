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

	$output = fopen('copy_errors.txt', 'w');

	// Avatar Folder
	$avatar_folder = "../../res/avatar";

	// Copy Awarded Pictures under each Section
	$target_path = "../../salons/" . $yearmonth . "/upload/Tentcard";
	if (! file_exists($target_path))
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
		$stub = $srow['stub'];

		// Check if Directory exists
		$section_path = "../../salons/" . $yearmonth . "/upload/" . $section;
		if (file_exists($section_path)) {
			// Create an Acceptance Folder if it does not exist
			$target_folder = $target_path . "/" . $stub;
			if (! file_exists($target_folder))
				mkdir($target_folder);
			$target_pic_folder = $target_folder . "/pic";
			if (! file_exists($target_pic_folder))
				mkdir($target_pic_folder);
			$target_avatar_folder = $target_folder . "/avatar";
			if (! file_exists($target_avatar_folder))
				mkdir($target_avatar_folder);

			$csv = fopen($target_folder . "/" . $stub . "_data.csv", "w");
			fputcsv($csv, array("Author", "Honors", "Avatar", "State", "Country", "Section", "Picture", "Title", "Award") );

			// Get a list of pictures awarded and copy them
			$sql  = "SELECT pic.profile_id, profile.profile_name, profile.honors, profile.avatar, profile.state, country.country_name, ";
			$sql .= "       pic.pic_id, pic.title, pic.picfile, award.award_name, award.level, award.sequence ";
			if ($contest_archived)
				$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile, country ";
			else
				$sql .= "  FROM pic_result, award, pic, profile, country ";
			$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
			$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND award.award_id = pic_result.award_id ";
			$sql .= "   AND award.level <= 9 ";
			$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
			$sql .= "   AND pic.section = '$section' ";
			$sql .= "   AND pic.profile_id = pic_result.profile_id ";
			$sql .= "   AND pic.pic_id = pic_result.pic_id ";
			$sql .= "   AND profile.profile_id = pic.profile_id ";
			$sql .= "   AND country.country_id = profile.country_id ";
			$sql .= " ORDER BY award.level, award.sequence ";
			$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__, false);
			$num_pics = 0;
			while ($rpic = mysqli_fetch_array($qpic)) {
				$source_file = $section_path . "/" . $rpic['picfile'];
				$profile_id = $rpic['profile_id'];
				$profile_name = safe_name($rpic['profile_name']);
				$pic_id = $rpic['pic_id'];
				$title = safe_name($rpic['title']);
				$award_name = safe_name($rpic['award_name']);
				// Copy Picture
				$target_file = sprintf("%04d-%02d-%s-%s-%s.jpg", $profile_id, $pic_id, $profile_name, $title, $award_name);
				if (! copy($source_file, $target_pic_folder . "/" . $target_file))
					fprintf($output, "Error copying : " . $rpic['picfile'] . " of " . $profile_name . " under " . $section . chr(13) . chr(10));
				++ $num_pics;
				// Copy Avatar if present
				$target_avatar = "";
				if ($rpic['avatar'] != "" && $rpic['avatar'] != "user.jpg" && file_exists($avatar_folder . "/" . $rpic['avatar'])) {
					$target_avatar = sprintf("%04d-%s.jpg", $profile_id, $profile_name);
					if (! copy($avatar_folder . "/" . $rpic['avatar'], $target_avatar_folder . "/" . $target_avatar))
						fprintf($output, "Error copying avatar : " . $rpic['avatar'] . " of " . $profile_name . " under " . $section . chr(13) . chr(10));
				}

				$author = $rpic['profile_name'];
				$honors = $rpic['honors'];
				$avatar = $target_avatar;
				$state = $rpic['state'];
				$country = $rpic['country_name'];
				$picture = $target_file;
				$title = $rpic['title'];
				$award = $rpic['award_name'];
				fputcsv($csv, array($author, $honors, $avatar, $state, $country, $section, $picture, $title, $award) );

			}
			printf("Copied %d pictures under %s<br>", $num_pics, $section);
			fclose($csv);
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

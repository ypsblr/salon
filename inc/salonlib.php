<?php
// =====================================================================
// salonlib - Library of functions for use by salon.php on the home page
// provides functions to process data and display salon details
// ======================================================================

function get_contest ($yearmonth) {

	global $DBCON;

	// Fetch Contest Details
	$query = mysqli_query($DBCON, "SELECT * FROM contest WHERE yearmonth = '$yearmonth' AND results_ready = 1 ") or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return false;
	$salon['contest'] = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Cut-off Table
	$sql  = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ORDER BY section_sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$cut_off_table  = "<table class='table' style='margin-left: 30px; max-width: 60%;'>";
	$cut_off_table .= "<tr><th>Section</th><th>Cut-off Score</th></tr>";
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$cut_off_table .= "<tr><td>" . $row['section'] . "</td><td>" . $row['cut_off_score'] . "</td></tr>";
	}
	$cut_off_table .= "</table>";

	// Fetch Exhibition Data
	$query = mysqli_query($DBCON, "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ") or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0)
		$salon['exhibition'] = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Get details of the Team
	$sql = "SELECT * FROM team WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contestTeam = array();
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$contestTeam[$row['role']] = $row;
	$salon['team'] = $contestTeam;


	// Value Substitution Array used in merging blobs
	$contest_values = array(
						"contest-yearmonth" => $yearmonth,
						"contest-name" => $salon['contest']['contest_name'],
						"max-pic-width" => $salon['contest']['max_width'],
						"max-pic-height" => $salon['contest']['max_height'],
						"registration-last-date" => date(DATE_FORMAT, strtotime($salon['contest']['registration_last_date'])),		// DATE_FORMAT is defined in sessions.php as "D, M j, Y"
						"submission-timezone-name" => $salon['contest']['submission_timezone_name'],
						"judging-start-date" => date(DATE_FORMAT, strtotime($salon['contest']['judging_start_date'])),
						"judging-end-date" => date(DATE_FORMAT, strtotime($salon['contest']['judging_end_date'])),
						"result-date" => date(DATE_FORMAT, strtotime($salon['contest']['results_date'])),
						"cut-off-table" => $cut_off_table,
						"update-start-date" => date(DATE_FORMAT, strtotime($salon['contest']['update_start_date'])),
						"update-end-date" => date(DATE_FORMAT, strtotime($salon['contest']['update_end_date'])),
						"judging-venue" => $salon['contest']['judging_venue'],
						"judging-venue-address" => $salon['contest']['judging_venue_address'],
						"judging-venue-location-map" => $salon['contest']['judging_venue_location_map'],
						"exhibition-name" => $salon['contest']['exhibition_name'],
						"exhibition-start-date" => date(DATE_FORMAT, strtotime($salon['contest']['exhibition_start_date'])),
						"exhibition-end-date" => date(DATE_FORMAT, strtotime($salon['contest']['exhibition_end_date'])),
						"exhibition-venue" => $salon['contest']['exhibition_venue'],
						"exhibition-venue-address" => $salon['contest']['exhibition_venue_address'],
						"exhibition-venue-location-map" => $salon['contest']['exhibition_venue_location_map'],
						"catalog-release-date" => date(DATE_FORMAT, strtotime($salon['contest']['catalog_release_date'])),
						"catalog-file" => $salon['contest']['catalog'],
						"salon-chairman" => isset($contestTeam['Chairman']) ? $contestTeam['Chairman']['member_name'] : "",
						"chairman-role" => isset($contestTeam['Chairman']) ? $contestTeam['Chairman']['role_name'] : "",
						"salon-chairman-avatar" => isset($contestTeam['Chairman']) ? $contestTeam['Chairman']['avatar'] : "",
						"salon-chairman-honors" => isset($contestTeam['Chairman']) ? $contestTeam['Chairman']['honors'] : "",
						"salon-secretary" => isset($contestTeam['Secretary']) ? $contestTeam['Secretary']['member_name'] : "",
						"secretary-role" => isset($contestTeam['Secretary']) ? $contestTeam['Secretary']['role_name'] : "",
						"salon-secretary-avatar" => isset($contestTeam['Secretary']) ? $contestTeam['Secretary']['avatar'] : "",
						"salon-secretary-honors" => isset($contestTeam['Secretary']) ? $contestTeam['Secretary']['honors'] : ""
						);

	$salon['values'] = $contest_values;

	// Generate contestSectionList
	//
	$sql  = "SELECT * FROM section WHERE yearmonth = '$yearmonth' ORDER BY section_type, section";
	$query = mysqli_query($DBCON, $sql)or die(mysqli_error());

	$contestSectionList = array();	// Associative array with section name as the key
	$contestDigitalSections = 0;
	$contestPrintSections = 0;
	$submissionLastDates = array();

	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$contestDigitalSections += ($row['section_type'] == 'D' ? 1 : 0);
		$contestPrintSections += ($row['section_type'] == 'P' ? 1 : 0);
		$contestSectionList[$row["section"]] = $row;
		if (empty($submissionLastDates[$row['submission_last_date']]))
			$submissionLastDates[$row['submission_last_date']] = $row['section'];
		else
			$submissionLastDates[$row['submission_last_date']] .= "," . $row['section'];
	}

	$salon['sections'] = $contestSectionList;

	return $salon;
}
?>

<?php
function show_recognitions($yearmonth) {

	global $DBCON;
?>
	<div>
		<table class="table table-bordered" style="width: 60%; margin: 0 auto;">
		<?php
			$sql = "SELECT * FROM recognition WHERE yearmonth='$yearmonth' ";
			$rcgq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($rcgr = mysqli_fetch_array($rcgq)) {
		?>
			<tr>
				<td><img src="/salons/<?= $yearmonth;?>/img/recognition/<?=$rcgr['logo'];?>" style="max-width: 80px;"></td>
				<td>
					<b><?php echo $rcgr['short_code'];?> <?php echo $rcgr['recognition_id'];?></b>
					<br>
					<a href="<?php echo $rcgr['website'];?>" target="_blank"><?php echo $rcgr['organization_name'];?></a>
				</td>
			</tr>
		<?php
			}
		?>
		</table>
	</div>
<?php
}
?>

<?php
function show_jury($yearmonth, $sectionList) {

	global $DBCON;

	$index = 0;
	foreach ($sectionList as $section => $section_row) {
?>
	<div class="row">
		<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
			<p><b><?=$section;?></b></p>
		</div>
<?php
		$sql  = "SELECT * FROM assignment, user ";
		$sql .= " WHERE assignment.yearmonth='$yearmonth' ";
		$sql .= "   AND assignment.section = '$section' ";
		$sql .= "   AND user.user_id = assignment.user_id ";
		$sql .= " ORDER BY assignment.jurynumber";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)){
			$index ++;
?>
		<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
			<div class="containerBox">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 thumbnail" >
					<a title="Click to view the profile of <?=$row['user_name'];?>" data-toggle="modal" href="#jury-<?=$index;?>">
						<img src="/res/jury/<?=$row['avatar'];?>" >
						<div class="caption">
							<p><?=$row['user_name'];?></p>
						</div>
					</a>
				</div>
			</div>
		</div>
		<div id="jury-<?=$index;?>" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4">
								<img src="/res/jury/<?=$row['avatar'];?>" >
							</div>
							<div class="col-lg-8 col-md-8 col-sm-8">
								<p><big><?=$row['user_name'];?></big></p>
								<p><b><?=$row['honors'];?></b></p>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<p class="text text-justified">
							<?php include ("blob/jury/" . $row['profile_file']);?>
						</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
<?php
		}
?>
	</div>
<?php
	}
}
?>

<?php
function show_pic_awards_for_profile($yearmonth, $profile_id) {
	global $DBCON;

	if (CONTEST_ARCHIVED)
		$sql  = "SELECT * FROM ar_pic_result pic_result, ar_pic pic, award ";
	else
		$sql  = "SELECT * FROM pic_result, pic, award ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= " ORDER BY pic.section, pic.title ";
	$pic_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($pic_query) > 0) {
?>
	<h4 class='primary-font'>Award-winning Pictures</h4>
<?php
		$acceptances = 0;
		while ($pic = mysqli_fetch_array($pic_query)) {
			if ($pic['level'] == 99)
				++ $acceptances;
			else {
?>
	<div class="row">
		<div class="col-sm-1 col-md-1 col-lg-1"></div>
		<div class="col-sm-4 col-md-4 col-lg-4 thumbnail">
			<div style="max-width: 100%;">
				<a href="/salons/<?=$yearmonth;?>/upload/<?=$pic['section'];?>/<?=$pic['picfile'];?>"
						data-lightbox="EA-<?=$profile_id;?>"
						data-title="<?=$pic['title'];?>" >
					<img class="img-responsive lozad" style="margin:auto;" alt="loading..."
						data-src="/salons/<?=$yearmonth;?>/upload/<?=$pic['section'];?>/tn/<?=$pic['picfile'];?>" >
				</a>
			</div>
		</div>
		<div class="col-sm-7 col-md-7 col-lg-7">
			<p>Section: <b><?=$pic['section'];?> - <?=$pic['award_name'];?></b></p>
			<p>Title: <b><?=$pic['title']; ?></b></p>
		</div>
	</div>
<?php
			}		// else
		}	// while
?>
	<h4 class="primary-font">Other Pictures Accepted: <?=$acceptances;?></h5>
<?php
	}
}
?>

<?php
function show_award_notes($yearmonth, $award_id) {
	global $DBCON;

	$sql  = "SELECT * FROM stats_compiled ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND stat_segment = 'AWARD' ";
	$sql .= "   AND award_id = '$award_id' ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
?>
	<h4 class="text text-color">On the Award</h4>
<?php
		while ($row = mysqli_fetch_array($query)) {
?>
	<div class="row">
		<div class="col-sm-9">
			<p class="text-color"><b><?= $row['stat_heading'];?></b></p>
			<p><?= $row['stat_description'];?></p>
			<hr>
		</div>
	</div>
<?php
		}

	}

}
?>

<?php
function show_entry_results($yearmonth, $award_id, $profile_id, $award_name) {
	global $DBCON;

	$sql  = "SELECT profile_name, avatar, honors, yps_login_id, club.club_id AS club_id, club_name, city, state, country_name ";
	$sql .= "  FROM profile LEFT JOIN club ON profile.club_id = club.club_id, country ";
	$sql .= " WHERE profile_id = '$profile_id' ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$profile_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$profile = mysqli_fetch_array($profile_query);

?>
	<h4 class="text text-color"><?=$award_name;?></h4>
	<div class="row">
<?php
	if (trim($profile['avatar']) != "") {
?>
		<div class="col-sm-2 thumbnail">
			<div style="max-width:100%;" >
				<img class="img-responsive lozad" style="margin-left:auto; margin-right:auto;" data-src="/res/avatar/<?=$profile['avatar'];?>" >
			</div>
		</div>
<?php
	}
?>
		<div class="col-sm-10">
			<h4 class='primary-font'><?=$profile['profile_name'];?></h4>
			<div style="margin-left: 20px">
				<p class="text-muted"><?=$profile['city'];?>, <?=$profile['state'];?>, <?=$profile['country_name'];?></p>
<?php
	if ($profile['honors'] != "") {
?>
				<p class="text-muted"><?=$profile['honors'];?></p>
<?php
	}
	if ($profile['yps_login_id'] != "")
		$club_name = "Youth Photographic Society";
	else if ($profile['club_id'] != 0 && $profile['club_name'] != "")
		$club_name = $profile['club_name'];
	if (! empty($club_name)) {
?>
				<p class="text-muted"><?=$club_name;?></p>
<?php
	}
?>
			</div>
			<hr>
				<!-- List down Pictures winning awards if available -->
<?php
			show_pic_awards_for_profile($yearmonth, $profile_id);
?>
		</div>
	</div>
	<hr>
<?php
}
?>

<?php
function show_entry_awards($yearmonth) {
	global $DBCON;

	// Get a list of awards
	// $sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' AND award_type = 'entry' ORDER BY level, sequence";
	$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND award_type = 'entry' ORDER BY level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$award_id = $row['award_id'];
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="primary-font">
				<a data-toggle="collapse" data-parent="#awards-contest" href="#awards-entry-<?=$award_id;?>">
					<?php echo $row['award_name'];?>
				</a>
			</h3>
		</div>
		<div id="awards-entry-<?=$award_id;?>" class="panel-collapse collapse">
			<div class="panel-body">
				<?= show_award_notes($yearmonth, $award_id);?>
				<div class="row">
					<div class="col-sm-12">
<?php
		$sql = "SELECT * FROM entry_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ORDER BY ranking";
		$results_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($results_query) == 0) {
?>
					<p><b>Not awarded to anyone</b></p>
<?php
		}
		else {
			while ($results_row = mysqli_fetch_array($results_query)) {
				$sponsor_text = get_sponsor_text($yearmonth, $award_id, $results_row['sponsorship_no']);
				// Find Sponsor Details
				if ($sponsor_text != "") {
?>
			<p class="text-color"><?= $sponsor_text;?></p>
<?php
				}
				$profile_id = $results_row['profile_id'];
				show_entry_results($yearmonth, $award_id, $profile_id, $row['award_name']);
			}
		}
?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
	}
}
?>

<?php
function show_club_results($yearmonth, $award_id, $club_id) {
	global $DBCON;

	$sql  = "SELECT * FROM club, country WHERE club_id = '$club_id' AND club.club_country_id = country.country_id";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club = mysqli_fetch_array($query);

	// Get Statistics
	$sql  = "SELECT COUNT(*) AS num_participants, SUM(uploads) AS num_uploads, ";
	$sql .= "       SUM(awards) AS num_awards, SUM(hms) AS num_hms, SUM(acceptances) AS num_acceptances, ";
	$sql .= "       SUM(score) AS total_score ";
	if (CONTEST_ARCHIVED)
		$sql .= " FROM ar_entry entry, profile ";
	else
		$sql .= " FROM entry, profile ";
	$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
	$sql .= "   AND profile.profile_id = entry.profile_id ";
	$sql .= "   AND profile.club_id = '$club_id' ";
	$sql .= "   AND entry.uploads > 0";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$club_stat = mysqli_fetch_array($query);
?>

	<div class="row">
		<div class="col-sm-2">
			<img src="/res/club/<?=$club['club_logo'];?>" style="width: 100%"; />
		</div>
		<div class="col-sm-10">
			<h3 class="primary-font">
				<?=$club['club_name'];?>
			</h3>
			<p><a href="<?=$club['club_website'];?>" target="_blank"><?=$club['club_website'];?></a></p>
			<br>
			<p>Number of Participants : <b><?=$club_stat['num_participants'];?></b></p>
			<p>Number of Picture Uploads : <b><?=$club_stat['num_uploads'];?></b></p>
			<p>Number of Acceptances : <b><?=$club_stat['num_acceptances'] + $club_stat['num_awards'] + $club_stat['num_hms'];?></b></p>
			<p style='padding-left:15px;'>of which Medals : <b><?=$club_stat['num_awards'];?></b></p>
			<p style='padding-left:15px;'>and Honorable Mentions : <b><?=$club_stat['num_hms'];?></b></p>
			<br>
			<p><b>Hearty Congratulations to all the members!</b></p>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-12">
			<h4 class="primary-font">Participating Members</h4>
<?php
	if (CONTEST_ARCHIVED)
		$sql  = "SELECT * FROM ar_entry entry, profile ";
	else
		$sql  = "SELECT * FROM entry, profile ";
	$sql .= " WHERE entry.yearmonth = '$yearmonth' ";
	$sql .= "   AND profile.profile_id = entry.profile_id ";
	$sql .= "   AND profile.club_id = '$club_id' ";
	$sql .= "   AND entry.uploads > 0 ";
	$sql .= " ORDER BY profile_name ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$idx = 0;
	while ($profile = mysqli_fetch_array($query)) {
		++ $idx;
?>
			<div class="col-sm-6" style="padding-right: 8px;">
				<div class="row" >
					<div class="col-sm-4">
						<img data-src="/res/avatar/<?=$profile['avatar'];?>" style="width: 80%; padding-top: 2px; padding-bottom: 2px;" class="lozad">
					</div>
					<div class="col-sm-8" style="padding-bottom: 2px;">
						<br>
						<p><b><?=$profile['salutation'];?> <?=$profile['profile_name'];?></b></p>
						<p class="text-muted"><small><?=$profile['honors'];?></small></p>
					</div>
				</div>
			</div>
			<div class="<?php echo ($idx % 2) == 0 ? 'clearfix' : '';?>"></div>
<?php
	}
?>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-12">
			<h4 class="primary-font">Winners from Members</h4>
<?php
	$sql  = "SELECT award_name, pic.section, title, picfile, salutation, profile_name, city, state, country_name, honors ";
	if (CONTEST_ARCHIVED)
		$sql .= "  FROM ar_pic_result pic_result, award, ar_pic pic, profile, country ";
	else
		$sql .= "  FROM pic_result, award, pic, profile, country ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= "   AND profile.club_id = '$club_id' ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.level < 99 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= " ORDER BY level, sequence ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$idx = 0;
	while ($pic = mysqli_fetch_array($query)) {
		++ $idx;
?>
			<div class="col-sm-3 thumbnail">
				<div class="caption"><?=$pic['section'] . " - " . $pic['award_name'];?></div>
				<div style="max-width:100%;" >
					<a href="/salons/<?=$yearmonth;?>/upload/<?=$pic['section'];?>/<?=$pic['picfile'];?>"
							data-lightbox="CLUB-<?=$club_id;?>"
							data-title="<?=$pic['title'];?> by <?=$pic['salutation'];?> <?=$pic['profile_name'];?>, <small><?=$pic['city'];?>, <?=$pic['state'];?>, <?=$pic['country_name'];?>, <?=$pic['honors'];?>" >
						<img class="img-responsive lozad" style="margin-left:auto; margin-right:auto;" alt="loading..."
							 data-src="/salons/<?=$yearmonth;?>/upload/<?=$pic['section'];?>/tn/<?=$pic['picfile'];?>" >
					</a>
				</div>
				<div class="caption"><?=$pic['title'];?></div>
			</div>
<?php
		if ($idx % 4 == 0) {
?>
			<div class="clearfix"></div>
<?php
		}
	}
?>
		</div>
	</div>
<?php
}
?>

<?php
function show_club_awards($yearmonth, $section) {
	global $DBCON;

	// Get a list of awards
	$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' AND award_type = 'club' ORDER BY level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$award_id = $row['award_id'];
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="primary-font">
				<a data-toggle="collapse" data-parent="#awards-contest" href="#awards-club-<?=$award_id;?>">
					<?php echo $row['award_name'];?>
				</a>
			</h3>
		</div>
		<div id="awards-club-<?=$award_id;?>" class="panel-collapse collapse">
			<div class="panel-body">
				<h4 class="text text-color"><?=$row['award_name'];?></h4>
				<div class="row">
					<div class="col-sm-12">
<?php
		$sql = "SELECT * FROM club_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ORDER BY ranking";
		$results_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($results_query) == 0) {
?>
				<p><b>Not awarded to anyone</b></p>
<?php
		}
		else {
			while ($results_row = mysqli_fetch_array($results_query)) {
				$club_id = $results_row['club_id'];
				show_club_results($yearmonth, $award_id, $club_id);
			}
		}
?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
	}
}
?>

<?php
function video_file($profile_id, $pic_id) {
	global $video_folder;
	global $member_videos;

	foreach ($member_videos as $video) {
		if ($video['profile_id'] == $profile_id && $video['pic_id'] == $pic_id) {
			return $video_folder . $video['video'];
		}
	}
	return false;
}
function show_pic_results($yearmonth, $profile_id, $pic_id){
	global $DBCON;
	global $exhibitionSet;
	global $exhibitionVirtualTourReady;

	if (CONTEST_ARCHIVED)
		$sql  = "SELECT * FROM ar_pic pic, country, ";
	else
		$sql  = "SELECT * FROM pic, country, ";
	$sql .= "       profile LEFT JOIN club ON club.club_id = profile.club_id ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$pic = mysqli_fetch_array($query);
	$club_name = "";
	if ($pic['yps_login_id'] != "") {
		if (substr($pic['yps_login_id'], 0, 2) == "LM")
			$club_name = "Life Member, Youth Photographic Society";
		else if (substr($pic['yps_login_id'], 0, 2) == "IM")
			$club_name = "Individual Member, Youth Photographic Society";
		if (substr($pic['yps_login_id'], 0, 2) == "JA")
			$club_name = "Junior Associate, Youth Photographic Society";
	}
	else if ($pic['club_name'] != NULL)
		$club_name = $pic['club_name'];
	// if (file_exists("salons/" . $yearmonth . "/upload/" . $pic['section'] . "/tnl/" . $pic['picfile']))
	// 	$pic_path = "/salons/" . $yearmonth . "/upload/" . $pic['section'] . "/tnl/" . $pic['picfile'];
	// else
	$pic_path = "/salons/" . $yearmonth . "/upload/" . $pic['section'] . "/" . $pic['picfile'];
?>
	<div class="row">
<?php
	if (trim($pic['avatar']) != "" && $pic['avatar'] != "user.jpg") {
?>
		<div class="col-sm-2 col-md-2 col-lg-2">
			<img data-src="/res/avatar/<?=$pic['avatar'];?>" class="img-responsive lozad">
		</div>
<?php
	}
?>
		<div class="col-sm-8 col-md-8 col-lg-8">
			<span class="lead"><?=$pic['salutation'];?> <?=$pic['profile_name'];?></span><br>
<?php
	if ($pic['honors'] != "") {
?>
			<span class="small text-muted"><?=$pic['honors'];?></span><br>
<?php
	}
?>
			<span class="small text-muted"><?=$pic['city'];?>, <?=$pic['state'];?>, <?=$pic['country_name'];?></span><br>
<?php
	if ($club_name != "") {
?>
			<span class="small text-muted"><?=$club_name;?></span>
<?php
	}
?>
		</div>
		<div class="col-sm-2">
<?php
	// if ($exhibitionSet && $exhibitionVirtualTourReady && ($video = video_file($profile_id, $pic_id))) {
	if ( $video = video_file($profile_id, $pic_id) ) {
?>
			<button type="button" class="btn btn-color" data-toggle="modal" data-target="#video-<?= $profile_id;?>-<?= $pic_id;?>"  style="width: 100%;">
				<i class="fa fa-video-camera"></i> Listen to<br>the Author
			</button>
			<!-- Modal Video -->
			<div class="modal" id="video-<?= $profile_id;?>-<?= $pic_id;?>" tabindex="-1" role="dialog" aria-labelledby="video-header-label">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="pauseVideo('video-<?= $profile_id;?>-<?= $pic_id;?>')" >
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="video-header-label"><?= $pic['profile_name'];?> <i>on the picture</i> &quot;<?= $pic['title'];?>&quot;</h4>
						</div>
						<div class="modal-body">
							<div class="embed-responsive embed-responsive-16by9">
								<video controls>
									<source src="<?= $video;?>" type="video/mp4">
									<source src="<?= $video;?>" type="video/mov">
									Your browser does not support mp4 video
								</video>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
	}
?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<h4 class="primary-font"><?=$pic['title'];?></h4>
			<!-- <img class="img-responsive" src="/salons/<?=$yearmonth;?>/upload/<?=$pic['section'];?>/<?=$pic['picfile'];?>" alt="#" /> -->
			<img class="img-responsive lozad" data-src="<?=$pic_path;?>" alt="loading..." />
<?php
	if ($pic['location'] != "") {
?>
			<p><small><?=$pic['location'];?></small></p>
<?php
	}
?>
		</div>
	</div>
	<hr>
<?php
}
?>

<?php
function get_sponsor_text($yearmonth, $award_id, $sponsorship_no) {
	global $DBCON;

	if ($sponsorship_no == 0)
		return "";

	$sql  = "SELECT award_name_suffix, sponsor_name, sponsor_logo, sponsor_website ";
	$sql .= "  FROM sponsorship, sponsor ";
	$sql .= " WHERE sponsorship.yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsorship.sponsorship_type = 'AWARD' ";
	$sql .= "   AND sponsorship.link_id = '$award_id' ";
	$sql .= "   AND sponsorship.sponsorship_no = '" . $sponsorship_no . "' ";
	$sql .= "   AND sponsor.sponsor_id = sponsorship.sponsor_id ";
	$sponsor_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$sponsor_text = "";
	if (mysqli_num_rows($sponsor_query) > 0) {
		$sponsor_row = mysqli_fetch_array($sponsor_query);
		if ($sponsor_row["award_name_suffix"] != "")
			$sponsor_text .= "<i>&quot;" . $sponsor_row["award_name_suffix"] . "&quot;</i> - ";
		$sponsor_text .= "Sponsored by " . $sponsor_row["sponsor_name"];
		if ($sponsor_row["sponsor_website"])
			$sponsor_text .= " - <small><i><a href='" . $sponsor_row['sponsor_website'] . "' target='_blank'>" . $sponsor_row['sponsor_website'] . "</a></i></small>";
	}

	return $sponsor_text;
}
?>

<?php
function show_picture_awards($yearmonth, $section) {
	global $DBCON;

	$section_tag = str_replace(" ", "_", $section);
?>
	<div class="row">
		<div class="col-sm-12">
			<a href="#acceptances-for-section-<?= $section_tag;?>" class="pull-right">Go to Acceptances</a>
		</div>
	</div>
<?php
	// Get a list of awards
	$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' AND award_type = 'pic' AND level < 99 ORDER BY award_group, level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$award_id = $row['award_id'];
?>
	<h3 class="text text-color"><?=$row['award_name'];?></h3>
	<div class="row">
		<div class="col-sm-12">
<?php
		if (CONTEST_ARCHIVED)
			$sql  = "SELECT * FROM ar_pic_result pic_result ";
		else
			$sql  = "SELECT * FROM pic_result ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ORDER BY ranking";
		$results_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($results_query) == 0) {
?>
			<p><b>Not awarded to anyone</b></p>
<?php
		}
		else {
			while ($results_row = mysqli_fetch_array($results_query)) {
				$sponsor_text = get_sponsor_text($yearmonth, $award_id, $results_row['sponsorship_no']);
				// Find Sponsor Details
				if ($sponsor_text != "") {
?>
			<p class="text-color"><?= $sponsor_text;?></p>
<?php
				}
				$profile_id = $results_row['profile_id'];
				$pic_id = $results_row['pic_id'];
				show_pic_results($yearmonth, $profile_id, $pic_id);
			}
		}
?>
		</div>
	</div>
<?php
	}
}
?>

<?php
function show_special_picture_awards($yearmonth) {
	global $DBCON;

	// Get a list of awards
	$sql = "SELECT * FROM award WHERE yearmonth = '$yearmonth' AND section = 'CONTEST' AND award_type = 'pic' ORDER BY award_group, level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		$award_id = $row['award_id'];
?>
	<h3 class="text text-color"><?=$row['award_name'];?></h3>
	<div class="row">
		<div class="col-sm-12">
<?php
		if (CONTEST_ARCHIVED)
			$sql  = "SELECT * FROM ar_pic_result pic_result ";
		else
			$sql  = "SELECT * FROM pic_result ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ORDER BY ranking";
		$results_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($results_query) == 0) {
?>
			<p><b>Not awarded to anyone</b></p>
<?php
		}
		else {
			while ($results_row = mysqli_fetch_array($results_query)) {
				$sponsor_text = get_sponsor_text($yearmonth, $award_id, $results_row['sponsorship_no']);
				// Find Sponsor Details
				if ($sponsor_text != "") {
?>
			<p class="text-color"><?= $sponsor_text;?></p>
<?php
				}
				$profile_id = $results_row['profile_id'];
				$pic_id = $results_row['pic_id'];
				show_pic_results($yearmonth, $profile_id, $pic_id);
			}
		}
?>
		</div>
	</div>
<?php
	}
}
?>

<?php
function show_acceptance_results($yearmonth, $award_list, $full_house_list){
	global $DBCON;

	if (CONTEST_ARCHIVED)
		$sql  = "SELECT * FROM ar_pic_result pic_result, ar_pic pic, country, ";
	else
		$sql  = "SELECT * FROM pic_result, pic, country, ";
	$sql .= "       profile LEFT JOIN club ON club.club_id = profile.club_id ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic_result.award_id IN (" . implode(", ", array_keys($award_list)) . ") ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= " ORDER BY profile.profile_name, pic.pic_id ";

	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
?>
	<div class="row">
<?php
	$idx = 0;
	while($pic = mysqli_fetch_array($query)) {
		++ $idx;
		$club_name = "";
		if ($pic['yps_login_id'] != "") {
			if (substr($pic['yps_login_id'], 0, 2) == "LM")
				$club_name = "Life Member, Youth Photographic Society";
			else if (substr($pic['yps_login_id'], 0, 2) == "IM")
				$club_name = "Individual Member, Youth Photographic Society";
			if (substr($pic['yps_login_id'], 0, 2) == "JA")
				$club_name = "Junior Associate, Youth Photographic Society";
		}
		else if ($pic['club_name'] != NULL)
			$club_name = $pic['club_name'];

		$tn_path = "/salons/" . $yearmonth . "/upload/" . $pic['section'] . "/tn/" . $pic['picfile'];
		// if (file_exists("salons/" . $yearmonth . "/upload/" . $pic['section'] . "/tnl/" . $pic['picfile']))
		// 	$pic_path = "/salons/" . $yearmonth . "/upload/" . $pic['section'] . "/tnl/" . $pic['picfile'];
		// else
		$pic_path = "/salons/" . $yearmonth . "/upload/" . $pic['section'] . "/" . $pic['picfile'];
?>
		<div class="col-sm-3 thumbnail">
			<div class="caption">
				<a href="#" data-toggle="tooltip"
						title="<?=$pic['salutation'];?> <?=$pic['profile_name'];?>, <?=$pic['honors'];?>, <?=$pic['city'];?>, <?=$pic['state'];?>, <?=$pic['country_name'];?>, <?=$club_name;?>">
					<span class="flag-icon flag-icon-<?=strtolower($pic['sortname']);?>"></span> <?=$pic['salutation'];?> <?=$pic['profile_name'];?>
				</a>
			</div>
			<div style="max-width:100%;" >
				<a href="<?=$pic_path;?>"
						data-lightbox="<?=$pic['section'];?>"
						data-title="<?=$pic['title'];?> by <?=$pic['salutation'];?> <?=$pic['profile_name'];?>, <small><?=$pic['city'];?>, <?=$pic['state'];?>, <?=$pic['country_name'];?>, <?=$pic['honors'];?>" >
					<img class="img-responsive lozad" style="margin-left:auto; margin-right:auto;" src="/img/preview.png" data-src="<?= $tn_path;?>" >
				</a>
			</div>
			<div class="caption"><?=$pic['title'];?></div>
<?php
		$level = $award_list[$pic['award_id']]['level'];
		$award_name = $award_list[$pic['award_id']]['award_name'];
		$has_medal = ($award_list[$pic['award_id']]['has_medal'] == 1);
		$has_ribbon = ($award_list[$pic['award_id']]['has_ribbon'] == 1);
		$has_pin = ($award_list[$pic['award_id']]['has_pin'] == 1);
		$has_memento = ($award_list[$pic['award_id']]['has_memento'] == 1);
		$has_gift = ($award_list[$pic['award_id']]['has_gift'] == 1);
		$icon = "";
		if ($has_medal)
			$icon = "fa-certificate";
		if ($has_pin)
			$icon = "fa-map-pin";
		if ($has_ribbon)
			$icon = "fa-bookmark";
		if ($has_memento)
			$icon = "fa-memento";
		if ($has_gift)
			$icon = "fa-gift";
		if ($level < 99) {
?>
			<div style="width: 100%; padding-left: 9px; color: #fff; background-color: #5bc0de;">
<?php
			if ($icon != "") {
?>
				<i class="fa <?= $icon;?>" aria-hidden="true" style="margin-right: 8px;"></i>
<?php
			}
?>
				<?= $award_name;?>
			</div>
<?php
		}
		if (in_array($pic['profile_id'], $full_house_list)) {
?>
			<div style="width: 100%; padding-left: 9px; background-color: #ffffc4;"><img src="img/fullhousered.gif" style="width: 20px;"> <span class="text-danger"><b>FULL HOUSE !</b></span></div>
<?php
		}
?>
		</div>
<?php
		if ($idx % 4 == 0) {
?>
		<div class="clearfix"></div>
<?php
		}
	}
?>
	</div>
<?php
}
?>

<?php
// Modified to show all acceptances
function show_picture_acceptances($yearmonth, $section) {
	global $DBCON;

	$section_tag = str_replace(" ", "_", $section);

	// Get Full House List
	$sql  = "SELECT pic_result.profile_id, COUNT(*) AS num_acceptances, MAX(max_pics_per_entry) AS max_pics_per_entry ";
	if (CONTEST_ARCHIVED)
		$sql .= "  FROM ar_pic_result pic_result, ar_pic pic, section ";
	else
		$sql .= "  FROM pic_result, pic, section ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND pic.section = '$section' ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= " GROUP BY profile_id ";
	$sql .= "HAVING num_acceptances = max_pics_per_entry ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$full_house_list = [];
	while ($row = mysqli_fetch_array($query))
		$full_house_list[] = $row["profile_id"];

	// Get a list of award IDs
	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE award.yearmonth = '$yearmonth' ";
	$sql .= "   AND section = '$section' ";
	$sql .= "   AND award_type = 'pic' ";
	$sql .= "   AND award_group IN ( ";
	$sql .= "       SELECT award_group FROM entrant_category ";
	$sql .= "        WHERE entrant_category.yearmonth = award.yearmonth ";
	$sql .= "          AND acceptance_reported = '1' ";
	$sql .= "       ) ";
	$sql .= " ORDER BY level, sequence";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$award_list = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$award_list[$row['award_id']] = $row;
	}
	$acceptance_award_ids = implode(", ", array_keys($award_list));
?>
	<span id="acceptances-for-section-<?= $section_tag;?>"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="primary-font">
				<a data-toggle="collapse" data-parent="#awards-<?=$section_tag;?>" href="#acceptance-<?= $section_tag;?>">All Accepted Images</a>
			</h3>
		</div>
		<div id="acceptance-<?= $section_tag;?>" class="panel-collapse collapse">
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-12">
<?php
		if (CONTEST_ARCHIVED)
			$sql = "SELECT COUNT(*) AS num_acceptances FROM ar_pic_result pic_result ";
		else
			$sql  = "SELECT COUNT(*) AS num_acceptances FROM pic_result ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND award_id IN ($acceptance_award_ids)";
		$results_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$results_row = mysqli_fetch_array($results_query);

		if ($results_row['num_acceptances'] == 0) {
?>
			<p><b>Not awarded to anyone</b></p>
<?php
		}
		else
			show_acceptance_results($yearmonth, $award_list, $full_house_list);
?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>

<?php
// List Portfolios and show all acceptances & awards
// Global Variable to Hold Portfolio Print Definition
// Test Print List of Awards

function show_portfolios($yearmonth, $award_group) {
	global $DBCON;
	global $pdf_def;

	$award_tag = str_replace(" ", "-", $award_group);

	// Find a list of profiles with wins
	$sql  = "SELECT profile.profile_id, profile_name, avatar, honors, city, country_name, yps_login_id, IFNULL(club_name, '') AS author_club, acceptance_reported, ";
	$sql .= "       SUM(IF(award.level < 9, 1, 0)) AS num_medals, SUM(IF(award.level = 9, 1, 0)) AS num_hms, ";
	$sql .= "       SUM(IF(award.level = 99, 1, 0)) AS num_acceptances ";
	if (CONTEST_ARCHIVED)
		$sql .= "  FROM ar_pic_result pic_result, award, country, profile LEFT JOIN club ON club.club_id = profile.club_id, ar_entry entry, entrant_category ";
	else
		$sql .= "  FROM pic_result, award, country, profile LEFT JOIN club ON club.club_id = profile.club_id, entry, entrant_category ";
	$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_group = '$award_group' ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= "   AND country.country_id = profile.country_id ";
	$sql .= "   AND entry.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND entry.profile_id = pic_result.profile_id ";
	$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
	$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
	$sql .= " GROUP BY profile_id, profile_name, avatar, honors, city, country_name, yps_login_id, author_club, acceptance_reported ";
	$sql .= " ORDER BY profile_name ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
?>
	<div class="row">
<?php
	$counter = 0;
	$row_id = 0;
	while ($profile = mysqli_fetch_array($query)) {

		// If the acceptance is reported for this participant
		$acceptance_reported = ($profile['acceptance_reported'] == '1');

		$pdf_cell = [ "columnGap" => 4, "columns" => [] ];	// Provide for 2 columns - left: avatar, right: text
?>
		<div class="col-sm-4" style="height: 100px; border-width: 0.5px; border-color: #aaa; border-radius: 8px; border-style: solid;" >
			<div class="pull-right">
				<a onclick="revealWins(<?= $profile['profile_id'];?>, '<?= $award_tag;?>-row-<?= $row_id + 1;?>', <?= $counter % 3;?>)">
					<span class="text-color"><i class="fa fa-chevron-circle-down"></i></span>
				</a>
			</div>
			<table><tr>
<?php
		if ( ($profile['avatar'] != "") && ($profile['avatar'] != "user.jpg") && file_exists("res/avatar/" . $profile['avatar']) ) {
			//$avatar = "data:image/jpeg;base64," . base64_encode(file_get_contents("res/avatar/" . $profile['avatar']));
			$avatar = "avatar_" . $profile['profile_id'];
			array_push($pdf_cell["columns"], [ "width" => "auto", "stack" => [ [ "image" => $avatar, "width" => 36 ] ] ]);
?>
			<td width="60px" valign="top">
				<img data-src="/res/avatar/<?= $profile['avatar'];?>" style="width: 100%; padding-top: 4px; padding-right: 8px;"
				 		class="pdf-avatar lozad" data-avatar="<?= $avatar;?>" data-filter="pdf-<?= $award_tag;?>" >
			</td>
<?php
		}
		$pdf_texts = [];
		$pdf_texts[] = [ "text" => ucwords(strtolower($profile['profile_name'])), "style" => "name" ];		// Place the name first
?>
			<td>
				<div style="display: block;"><b><?= $profile['profile_name'];?></b></div>
<?php
		if ($profile['honors'] != "") {
			$honors = str_replace(",", ", ", $profile['honors']);
			$honors_stripped = strlen($honors) > 80 ? substr($honors, 0, 80) . "..." : $honors;
			$pdf_texts[] = [ "text" => $honors, "style" => "honors" ];
?>
				<div style="display: block; color: #888; font-size: 0.6em; line-height: normal; padding-top: 4px; word-wrap: break-word;" >
					<a data-toggle="tooltip" title="<?= $honors;?>"><?= $honors_stripped;?></a>
				</div>
<?php
		}
		$club_name = $profile['author_club'];
		if ($profile['yps_login_id'] != "") {
			switch(substr($profile['yps_login_id'], 0, 2)) {
				case "lm":
				case "LM": $club_name = "YOUTH PHOTOGRAPIC SOCIETY (LIFE MEMBER)"; break;
				case "im":
				case "IM": $club_name = "YOUTH PHOTOGRAPIC SOCIETY (INDIVIDUAL MEMBER)"; break;
				case "ja":
				case "JA": $club_name = "YOUTH PHOTOGRAPIC SOCIETY (JUNIOR ASSOCIATE)"; break;
			}
		}
		if ($club_name != "") {
			$pdf_texts[] = [ "text" => $club_name, "style" => "club" ];
?>
				<div style="display: block; color: #444; font-size: 0.8em; line-height: normal; padding-top: 4px;"><?= $club_name;?></div>
<?php
		}

		// Render screen
		$wins = "";
		if ($profile['num_medals'] > 0) {
			$wins .= "<span style='padding-right: 8px;'>";
			for ($i = 0; $i < $profile['num_medals']; ++ $i)
				$wins .= "<small><i class='fa fa-certificate'></i></small>";
			$wins .= "<span class='badge badge-success' title='" . $profile['num_medals'] . " Medal(s)'>" . $profile['num_medals'] . "</span>";
			$wins .= "</span>";
		}
		if ($profile['num_hms'] > 0) {
			$wins .= "<span style='padding-right: 8px;'>";
			for ($i = 0; $i < $profile['num_hms']; ++ $i)
				$wins .= "<small><i class='fa fa-tag'></i></small>";
			$wins .= "<span class='badge badge-info' title='" . $profile['num_hms'] . " Honorable Mention(s)'>" . $profile['num_hms'] . "</span>";
			$wins .= "</span>";
		}
		if ($profile['num_acceptances'] > 0) {
			$plus = ($profile['num_medals'] > 0 || $profile['num_hms'] > 0) ? " more" : "";
			$wins .= "<span style='padding-right: 8px;'>";
			for ($i = 0; $i < $profile['num_acceptances']; ++ $i)
				$wins .= "<small><i class='fa fa-thumbs-up'></i></small>";
			$wins .= "<span class='badge badge-warning' ";
			$wins .= "title='" . $profile['num_acceptances'] . $plus . " Acceptance(s)'>" . $profile['num_acceptances'] . "</span>";
			$wins .= "</span>";
		}

		// Render PDF
		$pdf_acceptances = ($profile['num_acceptances'] + $profile['num_hms'] + $profile['num_medals']);
		if ($acceptance_reported)
			$wins_pdf = $pdf_acceptances . " Acceptance" . (($pdf_acceptances > 1) ? "s" : "");
		else
			$wins_pdf = "";

		if (($profile['num_medals'] + $profile['num_hms']) > 0) {
		 	$wins_pdf .= $acceptance_reported ? " (" : "";
			$wins_pdf .= ($profile['num_medals'] > 0) ? $profile['num_medals'] . " Medal" . (($profile['num_medals'] > 1) ? "s" : "") . (($profile['num_hms'] > 0) ? ", " : "") : "";
			$wins_pdf .= ($profile['num_hms'] > 0) ? $profile['num_hms'] . " Honorable Mention" . (($profile['num_hms'] > 1) ? "s" : "") : "";
			$wins_pdf .= $acceptance_reported ? ")" : "";
		}
		$pdf_texts[] = [ "text" => $wins_pdf, "style" => "wins" ];
		array_push($pdf_cell["columns"], [ "width" => "*", "stack" => $pdf_texts ]);
		$pdf_val = json_encode($pdf_cell, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
		//if (json_last_error() != JSON_ERROR_NONE)
			//debug_dump(json_last_error_msg(), $pdf_cell, __FILE__, __LINE__);
?>
				<div class="text-color" style="display: block; padding-top: 4px;"><?= $wins;?></div>
			</td>
			</tr></table>
		</div>
		<input type="hidden" class="pdf-cell" data-filter="pdf-<?= $award_tag;?>" value='<?= $pdf_val;?>' >
<?php
		++ $counter;
		if (($counter % 3) == 0) {
			++ $row_id;
?>
		<div class="clearfix" id="<?= $award_tag;?>-row-<?= $row_id;?>"></div>
<?php
		}
	}	// while
	if (($counter % 3) != 0) {
?>
		<div class="clearfix" id="row-<?= $row_id;?>"></div>
<?php
	}
?>
	</div>
<?php
}
?>

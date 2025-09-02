<?php
/****** GLOBAL VALUES *******/
$stat_yearmonth = $yearmonth;

// Generate Statistics if not already there
// include "inc/generate_statistics.php";

// Helper functions
function no_spaces($string) {
	return str_replace(" ", "_", $string);
}

function render_profile($profile_id) {
	global $DBCON;

	$sql  = "SELECT * FROM profile, country ";
	$sql .= "WHERE profile.profile_id = '$profile_id' AND profile.country_id = country.country_id ";
	$enqry = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$profile = mysqli_fetch_array($enqry);
	$participant_avatar = $profile['avatar'];
	$participant_salutation = $profile['salutation'];
	$participant_name = $profile['profile_name'];
	$participant_country_name = $profile['country_name'];
	$participant_honors = $profile['honors'];

	return <<<HTML
<img src='res/avatar/$participant_avatar' style='width: 100px; margin-right: 8px; display: block; float: left;' >
<p><b>$participant_salutation $participant_name</b></p>
<p><small>$participant_honors</small></p>
<p>$participant_country_name</p>
HTML;
}

//
// Return a list of Award Groups
//
function get_award_group_list ($yearmonth, $stat_category) {
	global $DBCON;

	$sql = "SELECT DISTINCT award_group FROM stats_participation WHERE yearmonth = '$yearmonth' AND stat_category = '$stat_category' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$award_group_list = array();
	while ($row = mysqli_fetch_array($query))
		$award_group_list[] = $row['award_group'];

	return $award_group_list;
}

function get_stat_segment_list ($yearmonth, $stat_category, $award_group) {
	global $DBCON;

	$sql  = "SELECT DISTINCT stat_segment, stat_segment_sequence FROM stats_participation ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND stat_category = '$stat_category' AND award_group = '$award_group' ";
	$sql .= " ORDER BY stat_segment_sequence ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$stat_segment_list = array();
	while ($row = mysqli_fetch_array($query))
		$stat_segment_list[$row['stat_segment_sequence']] = $row['stat_segment'];

	return $stat_segment_list;
}

function get_participation_column_list ($yearmonth, $stat_category, $award_group, $stat_segment) {
	global $DBCON;

	$sql  = "SELECT SUM(stat_awards) AS stat_awards, SUM(stat_hms) AS stat_hms, SUM(stat_acceptances) AS stat_acceptances ";
	$sql .= "FROM stats_participation ";
	$sql .= "WHERE yearmonth = '$yearmonth' ";
	$sql .= "  AND stat_category = '$stat_category' ";
	$sql .= "  AND award_group = '$award_group' ";
	$sql .= "  AND stat_segment = '$stat_segment' ";
	$sql .= "  AND stat_row != 'TOTAL' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return array($row['stat_awards'] > 0, $row['stat_hms'] > 0, $row['stat_acceptances'] > 0);
}

//
// Generate HTML for a participation statistics
//
function render_participation_stat ($yearmonth, $stat_category, $award_group) {

	global $DBCON;

	$stat_segment_list = get_stat_segment_list($yearmonth, $stat_category, $award_group);

	if (sizeof($stat_segment_list) > 0) {
?>
		<!-- Generate Tabs -->
		<ul class="nav nav-tabs">
		<?php
				$first_tab = true;
				foreach($stat_segment_list as $stat_segment_sequence => $stat_segment) {
		?>
			<li class="<?php echo $first_tab ? 'active' : '';?>" >
				<a data-toggle="pill" href="#part-<?= no_spaces($stat_category);?>-<?= no_spaces($award_group);?>-<?= $stat_segment_sequence;?>">
					<h4 class="text-color small"><?= $stat_segment;?></h4>
				</a>
			</li>
		<?php
					$first_tab = false;
				}
		?>
		</ul>

		<!-- Generate Tab Contest -->
		<div class="tab-content">
			<!-- Generate DIV for each Stat Segment -->
		<?php
				$first_tab = true;
				foreach($stat_segment_list as $stat_segment_sequence => $stat_segment) {
					list ($has_awards, $has_hms, $has_acceptances) = get_participation_column_list($yearmonth, $stat_category, $award_group, $stat_segment);
					$header_rows = ($has_acceptances && ($has_awards || $has_hms)) ? 2 : 1;
					$header_cols = ($has_awards ? 1 : 0) + ($has_hms ? 1 : 0);
		?>
			<div id="part-<?= no_spaces($stat_category);?>-<?= no_spaces($award_group);?>-<?= $stat_segment_sequence;?>" class="tab-pane fade <?php echo $first_tab ? 'in active' : '';?>" >
				<!-- Participation Table -->
				<table class="table table-striped">
				<thead>
					<!-- Generate Heading Row -->
				<?php
					if ($header_rows == 1){
				?>
					<tr>
						<th>Name</th>
				<?php
						if ($stat_category == "PARTICIPATION") {
				?>
						<th style='text-align: center;'># Participated</th>
						<th style='text-align: center;'># Winners</th>
				<?php
						}
				?>
						<th style='text-align: center;'># Pictures</th>
				<?php
						if ($has_acceptances) {
				?>
						<th style='text-align: center;'># Accepted</th>
				<?php
						}
						if ($has_awards) {
				?>
						<th style='text-align: center;'># Medals</th>
				<?php
						}
						if ($has_hms) {
				?>
						<th style='text-align: center;'># Honorable<br>Mentions</th>
				<?php
						}
				?>
					</tr>
				<?php
					}
					else {
				?>
					<tr>
						<th rowspan="2">Name</th>
				<?php
						if ($stat_category == "PARTICIPATION") {
				?>
						<th style='text-align: center;' rowspan="2"># Participated</th>
						<th style='text-align: center;' rowspan="2"># Winners</th>
				<?php
						}
				?>
						<th style='text-align: center;' rowspan="2"># Pictures</th>
				<?php
						if ($has_acceptances) {
				?>
						<th style='text-align: center;' rowspan="2"># Accepted</th>
				<?php
						}
						if ($has_awards || $has_hms) {
				?>
						<th style='text-align: center;' colspan="<?= $header_cols;?>">Accepted Includes</th>
				<?php
						}
				?>
					</tr>
					<tr>
				<?php
						if ($has_awards) {
				?>
						<th style='text-align: center;'># Medals</th>
				<?php
						}
						if ($has_hms) {
				?>
						<th style='text-align: center;'># Honorable<br>Mentions</th>
				<?php
						}
				?>
					</tr>
				<?php
					}
				?>
				</thead>
				<tbody>
					<!-- Generate data for the table -->
				<?php
					$sql  = "SELECT * FROM stats_participation ";
					$sql .= "WHERE yearmonth = '$yearmonth' ";
					$sql .= "  AND stat_category = '$stat_category' ";
					$sql .= "  AND award_group = '$award_group' ";
					$sql .= "  AND stat_segment = '$stat_segment' ";
					$sql .= "ORDER BY stat_row_sequence ";
					$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					while ($stat_row = mysqli_fetch_array($statq)) {
						$is_total_row = $stat_row['stat_total_row'];
						$acceptances = $stat_row['stat_acceptances'] + $stat_row['stat_awards'] + $stat_row['stat_hms'];
				?>
					<tr>
						<!-- First Column - Name -->
				<?php
						if ($stat_row['stat_profile_id'] != 0) {
				?>
						<td><?= render_profile($stat_row['stat_profile_id']);?></td>
				<?php
						}
						else {
				?>
						<td><?= $is_total_row ? "<b>" . $stat_row['stat_row'] . "</b>" : $stat_row['stat_row'];?></td>
				<?php
						}
						if ($stat_category == 'PARTICIPATION') {
				?>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $stat_row['stat_participated'] . "</b>" : $stat_row['stat_participated'];?></td>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $stat_row['stat_winners'] . "</b>" : $stat_row['stat_winners'];?></td>
				<?php
						}
				?>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $stat_row['stat_pictures'] . "</b>" : $stat_row['stat_pictures'];?></td>
				<?php
						if ($has_acceptances) {
				?>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $acceptances . "</b>" : $acceptances;?></td>
				<?php
						}
						if ($has_awards) {
				?>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $stat_row['stat_awards'] . "</b>" : $stat_row['stat_awards'];?></td>
				<?php
						}
						if ($has_hms) {
				?>
						<td style='text-align: center;'><?= $is_total_row ? "<b>" . $stat_row['stat_hms'] . "</b>" : $stat_row['stat_hms'];?></td>
				<?php
						}
				?>
					</tr>
				<?php
					}
				?>
				</tbody>
				</table>
			</div>
		<?php
					$first_tab = false;
				}
		?>
		</div>
<?php
	}
}		// End of function render_participation_stat
?>

<?php
// Map section stub to color
function stub_color($stub) {
	switch (substr($stub, 0, 2)) {
		case "CO" : return "#17a2b8";
		case "MO" : return "#6c757d";
		case "ND" : return "#28a745";
		case "TD" : return "#ffc107";
		default : return "#007bff";
	}
}
?>


<!-- MAIN -->
<h2 class="headline text-color">
	<span class="border-color">SALON STATISTICS</span>
</h2>

<?php
	// Determine if contest is archived or not
	$sql = "SELECT * FROM contest WHERE yearmonth = '$stat_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$stat_contest = mysqli_fetch_array($query);
	$stat_contest_archived = ($stat_contest['archived'] == '1');

	//
	// Check if there is participation statistics available
	//
	$sql  = "SELECT COUNT(*) As num_rows FROM stats_participation ";
	$sql .= " WHERE yearmonth = '$stat_yearmonth' ";
	$sql .= "   AND stat_category = 'PARTICIPATION' ";
	$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$statr = mysqli_fetch_array($statq);
	if ($statr['num_rows'] > 0) {
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="text-color">PARTICIPATION STATISTICS</h3>
	</div>
	<div class="panel-body">
		<!-- Generate Tabs for each award_group -->
		<ul class="nav nav-tabs">
<?php
		$award_group_list = get_award_group_list($stat_yearmonth, 'PARTICIPATION');
		$first_tab = true;
		foreach ($award_group_list as $award_group) {
?>
			<li class="<?= $first_tab ? 'active' : '';?>" >
				<a data-toggle="pill" href="#ag-part-<?= no_spaces($award_group);?>">
					<h4 class="text-color"><?= $award_group;?></h4>
				</a>
			</li>
<?php
			$first_tab = false;
		}
?>
		</ul>
		<!-- Generate Tab Content for each Award Group-->
		<div class="tab-content">
			<!-- Generate DIV for each Award Group -->
<?php
		$first_tab = true;
		foreach($award_group_list as $award_group) {
?>
			<div id="ag-part-<?= no_spaces($award_group);?>" class="tab-pane fade <?php echo $first_tab ? 'in active' : '';?>" >
				<?php render_participation_stat($stat_yearmonth, 'PARTICIPATION', $award_group); ?>
			</div>
<?php
			$first_tab = false;
		}
?>
		</div>	<!-- tab-content -->
	</div>	<!-- panel-body -->
</div>
<?php
	}	// $statr['num_rows'] > 0

	// *********************************
	// Scoring patterns and other Graphs
	// *********************************
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="text-color">CHARTS</h3>
	</div>
	<div class="panel-body">

		<h4 class="text-color">Acceptance</h4>
		<div class="row">
<?php
	$sql = "SELECT * FROM section WHERE yearmonth = '$stat_yearmonth' ORDER BY section_sequence";
	$section_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$stat_acc_list = [];
	for ($idx = 0; $section_row = mysqli_fetch_array($section_query); ++ $idx) {
		$section = $section_row['section'];
		$stub = $section_row['stub'];
		$cut_off = $section_row['cut_off_score'];
		$stat_acc_list[$idx] = array("section" => $section, "stub" => $stub, "cut_off" => $cut_off);
		// Count Number of Pictures and acceptances
		$sql  = "SELECT COUNT(*) AS num_pics, COUNT(pic_result.yearmonth) AS num_accepted ";
		if ($stat_contest_archived)
			$sql .= "  FROM ar_entry entry, entrant_category, ar_pic pic LEFT JOIN (ar_pic_result pic_result INNER JOIN award) ";
		else
			$sql .= "  FROM entry, entrant_category, pic LEFT JOIN (pic_result INNER JOIN award) ";
		$sql .= "    ON pic_result.yearmonth = pic.yearmonth ";
		$sql .= "   AND pic_result.profile_id = pic.profile_id ";
		$sql .= "   AND pic_result.pic_id = pic.pic_id ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.section != 'CONTEST' ";
		$sql .= " WHERE pic.yearmonth = '$stat_yearmonth' ";
		$sql .= "   AND pic.section = '$section' ";
		$sql .= "   AND entry.yearmonth = pic.yearmonth ";
		$sql .= "   AND entry.profile_id = pic.profile_id ";
		$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
		$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
		$sql .= "   AND entrant_category.acceptance_reported = '1' ";
		$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$statr = mysqli_fetch_array($statq);
		$stat_acc_list[$idx]["num_pics"] = $statr["num_pics"];
		$stat_acc_list[$idx]["num_accepted"] = $statr["num_accepted"];
		$stat_acc_list[$idx]["percent_accepted"] = round($statr["num_accepted"] / $statr["num_pics"] * 100, 1);
		if ($idx > 0 && ($idx % 4) == 0) {
?>
			<div class="clearfix"></div>
<?php
		}
?>
			<div class="col-sm-3">
				<div id="stat-acc-<?= $stub;?>" style="width: 100%; height: 200px;"><div style="display: flex; height: 100%; align-items: center; justify-content: center;">Loading...</div></div>
				<div class="caption text-center"><b><?= $section;?></b></div>
				<div class="small text-center">Pics: <?= $statr["num_pics"];?>, Cut-off: <?= $cut_off;?><br>Accepted: <?= $stat_acc_list[$idx]["percent_accepted"];?>%</div>
				<div class="divider"></div>
			</div>
<?php
	}
?>
		</div>

		<h4 class="text-color">Scoring Pattern</h4>
		<div class="row">
<?php
	$sql = "SELECT * FROM section WHERE yearmonth = '$stat_yearmonth' ORDER BY section_sequence";
	$section_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$stat_sp_list = [];
	for ($idx = 0; $section_row = mysqli_fetch_array($section_query); ++ $idx) {
		$section = $section_row['section'];
		$stub = $section_row['stub'];
		$cut_off = $section_row['cut_off_score'];
		$stat_sp_list[$idx] = array("section" => $section, "stub" => $stub, "cut_off" => $cut_off);

		// Get histogram data
		$sql  = "SELECT total_rating, COUNT(*) AS num_pics ";
		if ($stat_contest_archived)
			$sql .= "  FROM ar_pic pic, ar_entry entry, entrant_category ";
		else
			$sql .= "  FROM pic, entry, entrant_category ";
		$sql .= " WHERE pic.yearmonth = '$stat_yearmonth' ";
		$sql .= "   AND section = '$section' ";
		$sql .= "   AND entry.yearmonth = pic.yearmonth ";
		$sql .= "   AND entry.profile_id = pic.profile_id ";
		$sql .= "   AND entrant_category.yearmonth = entry.yearmonth ";
		$sql .= "   AND entrant_category.entrant_category = entry.entrant_category ";
		$sql .= "   AND entrant_category.acceptance_reported = '1' ";
		$sql .= " GROUP BY total_rating ";
		$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$score_list = [];
		$total_pics = 0;
		$js_array = "['Score','# Pictures', {role : 'style'}]";
		while ($statr = mysqli_fetch_array($statq)) {
			$total_rating = $statr['total_rating'];
			$num_pics = $statr['num_pics'];
			$score_list[$total_rating] = $num_pics;
			$total_pics += $num_pics;
			$js_array .= ", [" . $total_rating . ", " . $num_pics . ", '" . ($total_rating < $cut_off ? "#d7d7d7" : stub_color($stub)) . "']";
		}
		$stat_sp_list[$idx]["score_list"] = $score_list;
		$stat_sp_list[$idx]["js_array"] = $js_array;
		$stat_sp_list[$idx]["total_pics"] = $total_pics;
?>
			<div class="col-sm-6">
				<div id="stat-sp-<?= $stub;?>" style="width: 100%; height: 300px;"><div style="display: flex; height: 100%; align-items: center; justify-content: center;">Loading...</div></div>
				<div class="caption text-center"><b><?= $section;?></b></div>
				<div class="small text-center">Pics: <?= $total_pics;?>, Cut-off: <?= $cut_off;?></div>
				<div class="divider"></div>
			</div>
<?php
	}
?>
		</div>
	</div>
</div>
<?php
	// **************************
	// Generate TOP category data
	// **************************
	// Check if there is participation statistics available
	//
	$sql  = "SELECT COUNT(*) As num_rows FROM stats_participation ";
	$sql .= " WHERE yearmonth = '$stat_yearmonth' ";
	$sql .= "   AND stat_category = 'TOP' ";
	$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$statr = mysqli_fetch_array($statq);
	if ($statr['num_rows'] > 0) {
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="text-color">TOP PERFORMANCES</h3>
	</div>
	<div class="panel-body">
		<!-- Generate Tabs for each award_group -->
		<ul class="nav nav-tabs">
<?php
		$award_group_list = get_award_group_list($stat_yearmonth, 'TOP');
		$first_tab = true;
		foreach ($award_group_list as $award_group) {
?>
			<li class="<?php echo $first_tab ? 'active' : '';?>" >
				<a data-toggle="pill" href="#ag-top-<?= no_spaces($award_group);?>">
					<h4 class="text-color"><?= $award_group;?></h4>
				</a>
			</li>
<?php
			$first_tab = false;
		}
?>
		</ul>
		<!-- Generate Tab Content for each Award Group-->
		<div class="tab-content">
			<!-- Generate DIV for each Award Group -->
<?php
		$first_tab = true;
		foreach($award_group_list as $award_group) {
?>
			<div id="ag-top-<?= no_spaces($award_group);?>" class="tab-pane fade <?php echo $first_tab ? 'in active' : '';?>" >
				<?php render_participation_stat($stat_yearmonth, 'TOP', $award_group); ?>
			</div>
<?php
			$first_tab = false;
		}
?>
		</div>	<!-- tab-content -->
	</div>	<!-- panel-body -->
</div>
<?php
	} // $statr['num_rows'] > 0

	/**** DISPLAY STATISTICAL TIT BITS *****/
	$sql = "SELECT COUNT(*) AS num_bits FROM stats_bits";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['num_bits'] > 0) {
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-color">STATISTICAL TIT-BITS</h4>
	</div>
	<div class="panel-body">
		<!-- Generate Tabs -->
		<ul class="nav nav-tabs">
		<?php
			$sql  = "SELECT DISTINCT stat_segment, stat_segment_sequence FROM stats_bits ";
			$sql .= "WHERE yearmonth = '$stat_yearmonth' ";
			$sql .= "ORDER BY stat_segment_sequence ";
			$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$first_tab = true;
			$stat_bits = array();
			while($statr = mysqli_fetch_array($statq)) {
				$stat_bits[$statr['stat_segment_sequence']] = $statr['stat_segment'];
		?>
			<li class="<?php echo $first_tab ? 'active' : '';?>" >
				<a data-toggle="pill" href="#bits-<?php echo $statr['stat_segment_sequence'];?>">
					<h4 class="text-color small"><?php echo $statr['stat_segment'];?></h4>
				</a>
			</li>
		<?php
				$first_tab = false;
			}
		?>
		</ul>
		<!-- Generate Tab Contest -->
		<div class="tab-content">
			<!-- Generate DIV for each Segment -->
		<?php
			$first_tab = true;
			foreach($stat_bits as $stat_segment_sequence => $stat_segment) {
		?>
			<div id="bits-<?php echo $stat_segment_sequence;?>" class="tab-pane fade <?php echo $first_tab ? 'in active' : '';?>" >
				<!-- Tit-Bits Table -->
				<table class="table table-striped">
				<thead>
					<!-- Generate Heading Row -->
					<tr>
						<th>Statistics</th>
						<th class="text-center">Value</th>
						<th style="width: 300px;">Participant</th>
					</tr>
				</thead>
				<tbody>
					<!-- Generate data for the table -->
				<?php
					$sql  = "SELECT * FROM stats_bits ";
					$sql .= "WHERE stats_bits.yearmonth = '$stat_yearmonth' ";
					$sql .= "  AND stat_segment = '$stat_segment' ";
					$sql .= "ORDER BY stat_row_sequence ";
					$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					while ($stat_row = mysqli_fetch_array($statq)) {
				?>
					<tr>
						<td><?php echo $stat_row['stat_row'];?></td>
						<td class="text-center"><?php echo $stat_row['stat_value'];?></td>
				<?php
						// If the stat_row contains profile_id, extract the same
						if ($stat_row['profile_id'] > 0) {
				?>
						<td><?php echo render_profile($stat_row['profile_id']);?></td>
				<?php
						}
						else {
				?>
						<td>-</td>
				<?php
						}
				?>
					</tr>
				<?php
					}
				?>
				</tbody>
				</table>
			</div>
		<?php
				$first_tab = false;
			}
		?>
		</div>
	</div>
</div>
<?php
	}

	/******* FINALLY DISPLAY ANY COMPILED INFORMATION *******/
	$sql  = "SELECT COUNT(*) AS num_stat FROM stats_compiled ";
	$sql .= " WHERE yearmonth = '$stat_yearmonth' ";
	$sql .= "  AND stat_segment NOT IN ('AWARD', 'PIC', 'PROFILE') ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['num_stat'] > 0) {
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="text-color">OTHER STATISTICS</h4>
	</div>
	<div class="panel-body">
		<!-- Generate Tabs -->
		<ul class="nav nav-tabs">
		<?php
			$sql  = "SELECT DISTINCT stat_segment, stat_segment_sequence FROM stats_compiled ";
			$sql .= "WHERE yearmonth = '$stat_yearmonth' ";
			$sql .= "  AND stat_segment NOT IN ('AWARD', 'PIC', 'PROFILE') ";
			$sql .= "ORDER BY stat_segment_sequence ";
			$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$first_tab = true;
			$stat_bits = array();
			while($statr = mysqli_fetch_array($statq)) {
				$stat_bits[$statr['stat_segment_sequence']] = $statr['stat_segment'];
		?>
			<li class="<?php echo $first_tab ? 'active' : '';?>" >
				<a data-toggle="pill" href="#comp-<?php echo $statr['stat_segment_sequence'];?>">
					<h4 class="text-color small"><?php echo $statr['stat_segment'];?></h4>
				</a>
			</li>
		<?php
				$first_tab = false;
			}
		?>
		</ul>
		<!-- Generate Tab Contest -->
		<div class="tab-content">
			<!-- Generate DIV for each Segment -->
		<?php
			$first_tab = true;
			foreach($stat_bits as $stat_segment_sequence => $stat_segment) {
		?>
			<div id="comp-<?php echo $stat_segment_sequence;?>" class="tab-pane fade <?php echo $first_tab ? 'in active' : '';?>" >
				<!-- Compiled Information -->
				<?php
					$sql  = "SELECT * FROM stats_compiled ";
					$sql .= "WHERE yearmonth = '$stat_yearmonth' ";
					$sql .= "  AND stat_segment = '$stat_segment' ";
					$sql .= "ORDER BY stat_row_sequence ";
					$statq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
					while ($stat_row = mysqli_fetch_array($statq)) {
				?>
				<div class="well">
					<h4 class="text-color"><?php echo $stat_row['stat_heading'];?></h4>
				<?php
						if ($stat_row['profile_id'] > 0) {
				?>
					<div class="row" style="margin-top: 8px;">
						<div class="col-sm-12">
							<?php echo render_profile($stat_row['profile_id']);?>
						</div>
					</div>
				<?php
						}
				?>

					<div class="row" style="margin-top: 8px;">
						<div class="col-sm-12">
							<?php echo $stat_row['stat_description'];?>
						</div>
					</div>
				</div>
				<?php
					}
				?>
			</div>
		<?php
				$first_tab = false;
			}
		?>
		</div>
	</div>
</div>
<?php
	}
?>

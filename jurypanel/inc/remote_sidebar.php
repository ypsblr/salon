<?php
	$self = basename($_SERVER['PHP_SELF']);

	// Set Active Section
	$active_section = "";
	$active_award_group = "";
	if (isset($_REQUEST['show'])) {
		$show_arg = explode("|", decode_string_array($_REQUEST['show']));
		if (sizeof($show_arg) == 1) {
			$active_section = $show_arg[0];
		}
		else if (sizeof($show_arg) == 2) {
			$active_section = $show_arg[0];
			$active_award_group = $show_arg[1];
		}
	}

	// Determine current contest eligible for judging
	$sql = "SELECT * FROM contest WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$jury_contest = mysqli_fetch_array($query);

	// Get User Record
	$sql = "SELECT * FROM user WHERE user_id = '" . $_SESSION['jury_id'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$jury_user = mysqli_fetch_array($query);

	// Get Award Groups
	$sql = "SELECT DISTINCT award_group FROM entrant_category WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rsb_award_group_list = array();
	while ($row = mysqli_fetch_array($query))
		$rsb_award_group_list[] = $row['award_group'];

	// Get Open assignments
	$sql  = "SELECT * FROM assignment ";
	$sql .= " WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
	$sql .= "   AND user_id = '" . $_SESSION['jury_id'] ."' ";
	$assignment_query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$assignment_list = array();
	while ($assignment_row = mysqli_fetch_array($assignment_query, MYSQLI_ASSOC))
		$assignment_list[$assignment_row['section']] = $assignment_row;
?>

	<!-- Navigation -->
	<aside id="menu" style="top: 0px; padding-top: 60px;">
		<div id="navigation">
			<div class="profile-picture">
				<div><span class="text-info text-center"><b><?= $jury_user['user_name'];?></b></span></div>
				<a href="#"><img src="/res/jury/<?= $jury_user['avatar'];?>" class="img-circle m-b" alt="logo" style="max-width:80%;"></a>
				<div class="stats-label text-color">
					<span class="font-extra-bold font-uppercase">Remote Judging</span>
					<div id="sparkline1" class="small-chart m-t-sm"></div>
				</div>
			</div>

			<ul class="nav" id="side-menu">
				<li class="<?php echo ($self == 'rating_remote_home.php') ? 'active' : '';?>">
					<a href="/jurypanel/rating_remote_home.php"> <span class="nav-label"><i class="fa fa-home"></i> Home </span></a>
				</li>

				<?php
					if ($jury_contest['results_ready'] == 0 && $jury_contest['judging_in_progress'] == '1') {
				?>
				<li class="<?php echo ($self == 'rating_remote.php') ? 'active' : '';?>" <?= mysqli_num_rows($assignment_query) == 0 ? "disabled" : ""; ?> >
					<a href="#"> <span class="nav-label"><i class="fa fa-star-half-o"></i> Score</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($assignment_list as $assignment_section => $assignment_row) {
							foreach ($rsb_award_group_list as $rsb_award_group) {
								if ($self == 'rating_remote.php' && $active_section == $assignment_section && $active_award_group == $rsb_award_group)
									$class = "active";
								else
									$class = "";
					?>
						<li class="<?= $class;?>" >
							<a href="rating_remote.php?show=<?= encode_string_array($assignment_section . "|" . $rsb_award_group);?>">
								<small><?= $assignment_section . " - " . $rsb_award_group;?></small>
							</a>
						</li>
					<?php
							}
						}
					?>
					</ul>
				</li>

				<li class="<?php echo ($self == 'award_remote.php') ? 'active' : '';?>" <?= mysqli_num_rows($assignment_query) == 0 ? "disabled" : ""; ?> >
					<a href="#"> <span class="nav-label"><i class="fa fa-graduation-cap"></i> Award</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($assignment_list as $assignment_section => $assignment_row) {
							foreach ($rsb_award_group_list as $rsb_award_group) {
								if ($self == 'award_remote.php' && $active_section == $assignment_section && $active_award_group == $rsb_award_group)
									$class = "active";
								else
									$class = "";
					?>
						<li class="<?= $class;?>" >
							<a href="award_remote.php?show=<?= encode_string_array($assignment_section . "|" . $rsb_award_group);?>">
								<small><?= $assignment_section . " - " . $rsb_award_group;?></small>
							</a>
						</li>
					<?php
							}
						}
					?>
					</ul>
				</li>
				<?php
					}
				?>

				<li class="<?php echo ($self == 'remote_results.php') ? 'active' : '';?>" <?= mysqli_num_rows($assignment_query) == 0 ? "disabled" : ""; ?> >
					<a href="#"> <span class="nav-label"><i class="fa fa-trophy"></i> Result</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($assignment_list as $assignment_section => $assignment_row) {
							if ($self == 'remote_results.php' && $active_section == $assignment_section)
								$class = "active";
							else
								$class = "";
					?>
						<li class="<?= $class;?>" >
							<a href="remote_results.php?show=<?= encode_string_array($assignment_section);?>">
								<small><?= $assignment_section;?></small>
							</a>
						</li>
					<?php
						}
					?>
					</ul>
				</li>

				<li>
					<a href="/jurypanel/index.php"> <span class="nav-label"><i class="fa fa-sign-out"></i> Logout </span></a>
				</li>
			</ul>
		</div>
	</aside>

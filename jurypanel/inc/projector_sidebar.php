<?php

	$self = basename($_SERVER['PHP_SELF']);

	if (isset($_REQUEST['sections'])) {
		$active_section = $_REQUEST['sections'];
		$active_section = str_replace(" ", "", $active_section);
		$active_section = decode_string_array($active_section);
	}
	else
		$active_section = "";

	// Determine current contest eligible for judging
	if (isset($_SESSION['jury_yearmonth'])) {
		$sql = "SELECT * FROM contest WHERE yearmonth = '" . $_SESSION['jury_yearmonth'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$_SESSION['err_msg'] = "Current contest not set ";
			unset($_SESSION['jury_yearmonth']);
		}
		else {
			$jury_yearmonth = $_SESSION['jury_yearmonth'];
			$jury_contest = mysqli_fetch_array($query);
		}
	}

	// Get list of contests for dropdown box
	$sql  = "SELECT * FROM contest ";
	//$sql .= " WHERE results_ready = 0 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_contests = mysqli_num_rows($query);
	if ($num_contests == 0) {
		$_SESSION['err_msg'] = "There are no contests to judge";
		header("Location: index.php");
		die();
	}
	$contest_list = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$contest_list[$row['yearmonth']] = $row['contest_name'];
		// If there is only one contest, set it as current contest
		if ($num_contests == 1) {
			$jury_contest = $row;
			$jury_yearmonth = $jury_contest['yearmonth'];
			$_SESSION['jury_yearmonth'] = $row['yearmonth'];
		}
	}

	if (isset($jury_yearmonth) && $jury_yearmonth != "") {
		// Prepare Award Group List for selection
		$sql = "SELECT DISTINCT award_group FROM entrant_category WHERE yearmonth = '$jury_yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$psbar_ag_list = array();
		while ($row = mysqli_fetch_array($query))
			$psbar_ag_list[] = $row['award_group'];

		// Set Entrant Category Filter
		if (isset($_SESSION['categories'])) {
			$categories = explode(",", $_SESSION['categories']);
			foreach ($categories AS $idx => $category)
				$categories[$idx] = "'" . $category . "'";		// Add Quotes
			$entrant_filter = " AND entrant_category IN (" . implode(",", $categories) . ") ";
		}
		else {
			$entrant_filter = "";
		}

		$slist = array();
		// $sql = "SELECT DISTINCT pic.section AS section FROM pic, entry WHERE pic.entry_id = entry.entry_id " . $entrant_filter . " ORDER BY section";
		$sql = "SELECT section FROM section WHERE yearmonth = '$jury_yearmonth' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query))
			$slist[] = $row['section'];
	}
?>

	<!-- Navigation -->
	<aside id="menu" style="top: 50px;">
		<div id="navigation">
			<div class="profile-picture">
				<a href="#"><img src="img/yps.jpg" class="img-circle m-b" alt="logo"></a>
				<div class="stats-label text-color">
					<span class="font-extra-bold font-uppercase">Projection Master</span>
					<div class="dropdown">
						<a class="dropdown-toggle" href="#" data-toggle="dropdown">
							<small class="text-muted">Projection Control<b class="caret"></b></small>
						</a>
						<ul class="dropdown-menu animated flipInX m-t-xs">
							<li><a href="#" id="sidebar" class="right-sidebar-toggle">Profile</a></li>
							<li class="divider"></li>
							<li><a href="op/projector_logout.php">Logout</a></li>
						</ul>
					</div>
					<div id="sparkline1" class="small-chart m-t-sm"></div>
				</div>
			</div>

			<!-- Global Filter Criteria -->
			<div class="panel panel-default">
				<div class="panel-heading text-center"><h4 class="text-color text-danger"><b>Global Filter</b></h4></div>
				<div class="panel-body">
					<form action="op/set_filter.php" id="filter_form" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label>Select Contest</label>
							<select name="jury_yearmonth" class="form-control" value="<?php echo isset($jury_yearmonth) ? $jury_yearmonth : '';?>" >
							<?php
								foreach($contest_list as $contest_yearmonth => $contest_name) {
							?>
								<option value="<?=$contest_yearmonth;?>" <?php echo (isset($jury_yearmonth) && $jury_yearmonth == $contest_yearmonth) ? "selected" : "";?> ><?=$contest_name;?></option>
							<?php
								}
							?>
							</select>
						</div>
						<?php
							if (isset($jury_yearmonth)) {
						?>
						<div class="form-group">
							<label>Award Group</label>
							<?php
								foreach($psbar_ag_list as $award_group) {
							?>
							<div class="radio" style="padding-left: 8px;">
								<label>
									<input type="radio" name="award_group" value="<?php echo $award_group;?>"
											<?php echo (isset($_SESSION['award_group']) && $award_group == $_SESSION['award_group']) ? "checked" : "";?> >
									<?php echo $award_group;?>
								</label>
							</div>
							<?php
								}
							?>
						</div>
						<?php
							}
						?>
						<button class="btn btn-success" style="width: 100%" type="submit" name="set_filter">SET SELECTION</button>
					</form>
				</div>
			</div>

			<ul class="nav" id="side-menu">
				<li class="<?php echo ($self == 'projector_home.php') ? 'active' : '';?>">
					<a href="projector_home.php"> <span class="nav-label"><i class="fa fa-home"></i> Dashboard </span></a>
				</li>
				<li class="<?php echo ($self == 'projector_view_remote.php') ? 'active' : '';?>" <?php echo isset($jury_yearmonth) ? "" : "disabled"; ?> >
					<a href="#"><span class="nav-label"><i class="fa fa-mortar-board"></i> Projector</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($slist as $section) {
							if ($self == 'projector_view_remote.php' && $active_section == $section)
								$class = "active";
							else
								$class = "";
					?>
						<li class="<?php echo $class;?>" >
							<a href="projector_view_remote.php?sections=<?php echo encode_string_array($section);?> &clear_filters"><?php echo $section;?></a>
						</li>
					<?php
						}
					?>
					</ul>
				</li>
				<li class="<?php echo ($self == 'projector_acc.php') ? 'active' : '';?>" <?php echo isset($jury_yearmonth) ? "" : "disabled"; ?> >
					<a href="#"><span class="nav-label"><i class="fa fa-mortar-board"></i> Acceptance</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($slist as $section) {
							if ($self == 'projector_acc.php' && $active_section == $section)
								$class = "active";
							else
								$class = "";
					?>
						<li class="<?php echo $class;?>" ><a href="projector_acc.php?sections=<?php echo encode_string_array($section);?>&next=0|0"><?php echo $section;?></a></li>
					<?php
						}
					?>
					</ul>
				</li>
				<li class="<?php echo ($self == 'projector_results.php') ? 'active' : '';?>" <?php echo isset($jury_yearmonth) ? "" : "disabled"; ?> >
					<a href="#"><span class="nav-label"><i class="fa fa-list"></i> Results</span><span class="fa arrow"></span> </a>
					<ul class="nav nav-second-level">
					<?php
						foreach ($slist as $section) {
							if ($self == 'projector_results.php' && $active_section == $section)
								$class = "active";
							else
								$class = "";
					?>
						<li class="<?php echo $class;?>" ><a href="projector_results.php?sections=<?php echo encode_string_array($section);?>"><?php echo $section;?></a></li>
					<?php
						}
					?>
					</ul>
				</li>
				<li class="<?php echo ($self == 'projector_admin.php') ? 'active' : '';?>">
					<a href="projector_admin.php"> <span class="nav-label"><i class="fa fa-home"></i> Admin </span></a>
				</li>
			</ul>
		</div>
	</aside>

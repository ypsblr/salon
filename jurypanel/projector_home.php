<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Convert a pipeline separate list into a quoted comma separated list for use in SQL IN condition
function in_list($pipe_str) {
	if ($pipe_str == "")
		return $pipe_str;
	else {
		$str_list = explode("|", $pipe_str);
		for ($i = 0; $i < sizeof($str_list); ++$i)
			$str_list[$i] = "'" . $str_list[$i] . "'";
		return implode(",", $str_list);
	}
}

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && $_SESSION['jury_type'] == "PROJECTOR") {
	if (isset($_SESSION['jury_yearmonth'])) {
		$jury_yearmonth = $_SESSION['jury_yearmonth'];
	}
	else {
		$sql = "SELECT MAX(yearmonth) AS yearmonth FROM contest";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$jury_yearmonth = $row['yearmonth'];
	}
	$_SESSION['jury_yearmonth'] = $jury_yearmonth;

	$sql = "SELECT * FROM assignment WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {
		$_SESSION['err_msg'] = "No jury has been assigned to this salon.";
		header("Location: /jurypanel/index.php" );
		printf("<script>location.href='/jurypanel/index.php'</script>");
		die();
	}
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	$sql = "SELECT entrant_category, award_group FROM entrant_category WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$category_list = array();
	$award_group_list = array();
	while ($row = mysqli_fetch_array($query)) {
		$category_list[] = $row['entrant_category'];
		if (isset($award_group_list[$row['award_group']]))
			$award_group_list[$row['award_group']] .= "|" . $row['entrant_category'];
		else
			$award_group_list[$row['award_group']] = $row['entrant_category'];
	}

	// If award_group is not set, set it to the first $award_group
	if (! isset($_SESSION['award_group'])) {
		foreach ($award_group_list as $award_group => $ec_list) {
			$_SESSION['award_group'] = $award_group;
			break;
		}
	}

	$sectionCount = array();
	if (isset($_SESSION['categories'])) {
		$session_filter = explode(",", $_SESSION['categories']);
		$categories = explode(",", $_SESSION['categories']);
		foreach ($categories AS $idx => $category)
			$categories[$idx] = "'" . $category . "'";		// Add Quotes
		$entrant_filter = " AND entrant_category IN (" . implode(",", $categories) . ") ";
	}
	else {
		$session_filter = explode(",", implode(",", $category_list));
		$entrant_filter = "";
	}

	$sql  = "SELECT pic.section, section.stub, COUNT(*) as count FROM pic, entry, section ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= "   AND entry.yearmonth = pic.yearmonth ";
	$sql .= "   AND entry.profile_id = pic.profile_id ";
	$sql .= $entrant_filter;
	$sql .= " GROUP BY pic.section, section.stub ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$sectionCount[$row['section']] = $row;
?>
<!DOCTYPE html>
<html>
<head>

    <!-- Page title -->
	<title>Youth Photographic Society | Projection Panel</title>

	<?php include("inc/header.php");?>
	<link href="plugin/bootstrap-toggle/css/bootstrap-toggle.min.css" rel="stylesheet">

</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | PROJECTION PANEL  </h1>
			<p>Please Wait. </p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>
	<!--[if lt IE 7]>
	<p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->

	<!-- Header -->
	<div id="header">
		<div class="color-line"></div>
		<div id="logo" class="light-version">
			<span>Youth Photographic Society</span>
		</div>
		<nav role="navigation">
			<div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
			<div class="small-logo">
				<span class="text-primary">PROJECTION APP</span>
			</div>
			<form role="search" class="navbar-form-custom" method="post" action="#">
				<div class="form-group">
					<input type="text" placeholder="Search something special" class="form-control" name="search">
				</div>
			</form>
			<div class="mobile-menu">
				<button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
					<i class="fa fa-edit"></i>
				</button>
				<div class="collapse mobile-navbar" id="mobile-collapse">
					<ul class="nav navbar-nav">
						<li>
							<a class="" href="index.php">Logout</a>
						</li>
						<li>
							<a data-toggle="modal" data-target="#myModal">Profile</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>
	</div>

	<!-- Navigation -->
	<?php include("inc/projector_sidebar.php");?>


	<div id="wrapper">
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>Welcome to Projection View</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="hpanel">
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12">
									<div class="well">
										<h2 class="text-center"><?= $contest['contest_name'];?></h2>
										<label>
											<span style="padding-right: 15px;" class="lead">Judging Status</span>
											<input type="checkbox" data-toggle="toggle" data-size="small"
												   data-on="In progress... " data-off="<?= $contest['results_ready'] == 1 ? "Completed" : "Not started";?>"
												   id="ckb-judging-status"
												   <?= $contest['results_ready'] == 0 ? "" : "disabled";?>
												   <?= $contest['judging_in_progress'] == 0 ? "" : "checked";?> >
										</label>

									</div>
								</div>
							</div>
							<div class="row">
							<?php
								$idx = 0;
								foreach($sectionCount as $section => $sc_row) {
									$section_stub = $sc_row['stub'];
							?>
								<div class="col-lg-3">
									<div class="hpanel">
										<div class="panel-body text-center">
											<i class="fa fa-4x fa-photo"></i>
											<h3><?php echo $section;?></h3>
											<?php
												foreach ($award_group_list as $award_group => $ag_categories) {
												    debug_to_console(1);
													// Get Jury Session Status
													$sql  = "SELECT * FROM jury_session ";
													$sql .= " WHERE yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND section = '$section' ";
													$sql .= "   AND award_group = '$award_group' ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													if (mysqli_num_rows($query) == 0) {
													    debug_to_console(2);
														$session_open = 0;
														$show_display = 0;
														$result_ready = 0;
													}
													else {
													    debug_to_console(3);
														$jury_session = mysqli_fetch_array($query);
														$session_open = $jury_session['session_open'];
														$show_display = $jury_session['show_display'];
														$result_ready = $jury_session['result_ready'];
													}

													// Count Number of Pictures for this award_group
													$sql  = "SELECT COUNT(*) AS num_uploads ";
													$sql .= "  FROM pic, entry ";
													$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND pic.section = '$section' ";
													$sql .= "   AND entry.yearmonth = pic.yearmonth ";
													$sql .= "   AND entry.profile_id = pic.profile_id ";
													$sql .= "   AND entry.entrant_category IN (" . in_list($ag_categories) . ") ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$uploads_row = mysqli_fetch_array($query);
													$num_uploads = $uploads_row['num_uploads'];

													// Check if all pictures have been scored
													$sql  = "SELECT COUNT(rating.rating) AS num_scores ";
													$sql .= "  FROM entry, pic LEFT JOIN rating ";
													$sql .= "    ON rating.yearmonth = pic.yearmonth ";
													$sql .= "   AND rating.profile_id = pic.profile_id ";
													$sql .= "   AND rating.pic_id = pic.pic_id ";
													$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND pic.section = '$section' ";
													$sql .= "   AND entry.yearmonth = pic.yearmonth ";
													$sql .= "   AND entry.profile_id = pic.profile_id ";
													$sql .= "   AND entry.entrant_category IN (" . in_list($ag_categories) . ") ";
													$sql .= " GROUP BY pic.yearmonth, pic.profile_id, pic.pic_id ";
													$sql .= "HAVING num_scores < ( ";
													$sql .= "           SELECT COUNT(*) FROM assignment ";
													$sql .= "            WHERE assignment.yearmonth = '$jury_yearmonth' ";
													$sql .= "              AND assignment.section = '$section' ";
													$sql .= "           ) ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$unscored = mysqli_num_rows($query);

													// Jury-wise number of scores
													$sql  = "SELECT user.user_id, user_name, rating, IFNULL(COUNT(rating.rating), 0) AS num_pics ";
													$sql .= "  FROM user, assignment LEFT JOIN (rating, entry, pic) ";
													$sql .= "    ON rating.yearmonth = assignment.yearmonth ";
													$sql .= "   AND rating.user_id = assignment.user_id ";
													$sql .= "   AND entry.yearmonth = rating.yearmonth ";
													$sql .= "   AND entry.profile_id = rating.profile_id ";
													$sql .= "   AND entry.entrant_category IN (" . in_list($ag_categories) . ") ";
													$sql .= "   AND pic.yearmonth = rating.yearmonth ";
													$sql .= "   AND pic.profile_id = rating.profile_id ";
													$sql .= "   AND pic.pic_id = rating.pic_id ";
													$sql .= "   AND pic.section = '$section' ";
													$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND assignment.section = '$section' ";
													$sql .= "   AND user.user_id = assignment.user_id ";
													$sql .= " GROUP BY user_id, rating ";
													$sql .= " ORDER BY user_name ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

													$total_rating_1 = 0;
													$total_rating_2 = 0;
													$total_rating_3 = 0;
													$total_rating_4 = 0;
													$total_rating_5 = 0;
													$total_scored = 0;
													$jury_scored = [];

													while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
													    debug_to_console(4);
														$user_id = $row['user_id'];
														// Create a Row if not found
														if ( ! isset($jury_scored[$user_id])) {
															$jury_scored[$user_id] = array("user_name" => $row['user_name'], "rating_1" => 0, "rating_2" => 0,
																										"rating_3" => 0, "rating_4" => 0, "rating_5" => 0, "num_scored" => 0);
														}
														// Update the rating bucket
														$total_scored += $row['num_pics'];
    												    debug_to_console("row rating is");
    												    debug_to_console($row['rating']);
														switch($row['rating']) {
															case 1 : {
															    debug_to_console("case1");
																$jury_scored[$user_id]["rating_1"] = $row['num_pics'];
																$jury_scored[$user_id]["num_scored"] += $row['num_pics'];
																$total_rating_1 += $row['num_pics'];
																break;
															}
															case 2 : {
															    debug_to_console("case2");
																$jury_scored[$user_id]["rating_2"] = $row['num_pics'];
																$jury_scored[$user_id]["num_scored"] += $row['num_pics'];
																$total_rating_2 += $row['num_pics'];
																break;
															}
															case 3 : {
															    debug_to_console("case3");
																$jury_scored[$user_id]["rating_3"] = $row['num_pics'];
																$jury_scored[$user_id]["num_scored"] += $row['num_pics'];
																$total_rating_3 += $row['num_pics'];
																break;
															}
															case 4 : {
															    debug_to_console("case4");
																$jury_scored[$user_id]["rating_4"] = $row['num_pics'];
																$jury_scored[$user_id]["num_scored"] += $row['num_pics'];
																$total_rating_4 += $row['num_pics'];
																break;
															}
															case 5 : {
															    debug_to_console("case5");
																$jury_scored[$user_id]["rating_5"] = $row['num_pics'];
																$jury_scored[$user_id]["num_scored"] += $row['num_pics'];
																$total_rating_5 += $row['num_pics'];
																break;
															}
														}
													}

                                                    debug_to_console(5);
													// Check if there are results
													$sql  = "SELECT COUNT(*) AS num_pic_results ";
													$sql .= "  FROM award, pic_result ";
													$sql .= " WHERE award.yearmonth = '$jury_yearmonth' ";
													$sql .= "   AND award.section = '$section' ";
													$sql .= "   AND award.award_group = '$award_group' ";
													$sql .= "   AND pic_result.yearmonth = award.yearmonth ";
													$sql .= "   AND pic_result.award_id = award.award_id ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$results = mysqli_fetch_array($query);
													$num_pic_results = $results['num_pic_results'];

													$hide_judging_controls = ($contest['results_ready'] == '1' || $contest['judging_in_progress'] == '0');
											?>
											<table class="table" style="background-color: #fffdd0;">
												<tr>
													<td valign="center">
														<span class="text-info lead"><?= $award_group;?></span>
														<a href="#" class="text-success" data-toggle="modal" data-target="<?= '#STAT-' . $section_stub . '-' . $award_group;?>">Show Distribution</a>
													</td>
													<td>
														<h3 class="m-xs font-extra-bold no-margins text-success"> <?= $num_uploads;?></h3>
														<p><small><b>Total</b></small></p>
													</td>
													<td>
														<h3 class="m-xs font-extra-bold no-margins text-success"> <?= $unscored;?></h3>
														<p><small><b>Unscored</b></small></p>
													</td>
												</tr>
												<?php
													foreach ($jury_scored as $user_id => $scored) {
													    debug_to_console(6);
												?>
												<tr>
													<td colspan="2">
														<p class="text-right"><?= $scored['user_name'];?></p>
													</td>
													<td><span class="text-center"><?= $num_uploads - $scored['num_scored'];?></td>
												</tr>
												<?php
													}
												?>
												<tr>
													<td colspan="3">
														<div class="row judging-controls" style="display: <?= $hide_judging_controls ? 'none' : 'block';?>;" >
															<div class="col-sm-4">
																<label>
																	<span class="text-info font-bold">Score ?</span><br>
																	<input type="checkbox" data-toggle="toggle" data-size="mini"
																		   data-on="Yes" data-off="No"
																		   class="ckb-open-session" id="ckb-open-session-<?= $section_stub . "-" . $award_group;?>"
																		   data-section="<?= $section;?>" data-award-group="<?= $award_group;?>"
																		   data-num-uploads="<?= $num_uploads;?>" data-unscored="<?= $unscored;?>"
																		   data-num-results="<?= $num_pic_results;?>"
																		   <?= $session_open == 0 ? "" : "checked";?> >
																</label>
															</div>
															<div class="col-sm-4">
																<label>
																	<span class="text-info font-bold">Display ?</span><br>
																	<input type="checkbox" data-toggle="toggle" data-size="mini"
																		   data-on="Yes" data-off="No"
																		   class="ckb-show-display" id="ckb-show-display-<?= $section_stub . "-" . $award_group;?>"
																		   data-section="<?= $section;?>" data-award-group="<?= $award_group;?>"
																		   data-num-uploads="<?= $num_uploads;?>" data-unscored="<?= $unscored;?>"
																		   data-num-results="<?= $num_pic_results;?>"
																		   <?= $show_display == 0 ? "" : "checked";?> >
																</label>
															</div>
															<div class="col-sm-4">
																<label>
																	<span class="text-info font-bold">Results ?</span><br>
																	<input type="checkbox" data-toggle="toggle" data-size="mini"
																		   data-on="Yes" data-off="No"
																		   class="ckb-reveal-result" id="ckb-reveal-result-<?= $section_stub . "-" . $award_group;?>"
																		   data-section="<?= $section;?>" data-award-group="<?= $award_group;?>"
																		   data-num-uploads="<?= $num_uploads;?>" data-unscored="<?= $unscored;?>"
																		   data-num-results="<?= $num_pic_results;?>"
																		   <?= $result_ready == 0 ? "" : "checked";?>
																		   <?= $num_pic_results > 0 ? "" : "disabled";?> >
																</label>
															</div>
														</div>
													</td>
												</tr>
											</table>
											<?php
											        debug_to_console(7);
													// Now create the Modal for the award group containing breakup of scores
											?>
											<div class="modal" tabindex="-1" role="dialog" id="<?= 'STAT-' . $section_stub . '-' . $award_group;?>" aria-labelledby="image-review-header-label">
												<div class="modal-dialog modal-lg" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
															<h5 class="modal-title">Rating Distribution for <?= $award_group;?> under <?= $section;?> section</h4>
														</div>
														<div class="modal-body">
															<table class="table">
																<tr>
																	<th>Jury Member</th>
																	<th style="text-align: center;">1</th>
																	<th style="text-align: center;">2</th>
																	<th style="text-align: center;">3</th>
																	<th style="text-align: center;">4</th>
																	<th style="text-align: center;">5</th>
																	<th style="text-align: center;">Scored</th>
																</tr>
															<?php
																foreach ($jury_scored as $user_id => $scored) {
																    debug_to_console(8);
																    debug_to_console($scored['user_name']);
															?>
																<tr>
																	<td><?= $scored['user_name'];?></td>
																	<td style="text-align: center;"><?= $scored['rating_1'];?></td>
																	<td style="text-align: center;"><?= $scored['rating_2'];?></td>
																	<td style="text-align: center;"><?= $scored['rating_3'];?></td>
																	<td style="text-align: center;"><?= $scored['rating_4'];?></td>
																	<td style="text-align: center;"><?= $scored['rating_5'];?></td>
																	<td style="text-align: center;"><?= $scored['num_scored'];?></td>
																</tr>
															<?php
																}
																if ($total_rating_1 > 0) 
																{
															?>
															<tr>
																<td><b>Rating Distribution</b></td>
																<td style="text-align: center; font-weight: bold;"><?= sprintf("%.1f%%", $total_rating_1 / $total_scored * 100);?></td>
																<td style="text-align: center; font-weight: bold;"><?= sprintf("%.1f%%", $total_rating_2 / $total_scored * 100);?></td>
																<td style="text-align: center; font-weight: bold;"><?= sprintf("%.1f%%", $total_rating_3 / $total_scored * 100);?></td>
																<td style="text-align: center; font-weight: bold;"><?= sprintf("%.1f%%", $total_rating_4 / $total_scored * 100);?></td>
																<td style="text-align: center; font-weight: bold;"><?= sprintf("%.1f%%", $total_rating_5 / $total_scored * 100);?></td>
																<td style="text-align: center; font-weight: bold;"></td>
															</tr>
															<?php
																}
															?>
															</table>
														</div>
													</div>
												</div>
											</div>
											<?php
											        debug_to_console(9);
												}
											?>
										</div>
										<div class="panel-footer text-center">
											<a href="projector_view_remote.php?sections=<?php echo encode_string_array("$section");?> &clear_filters" class="btn btn-info btn-sm">Project</a>
										</div>
									</div>
								</div>
							<?php
									++ $idx;
									if ($idx % 4 == 0) {
							?>
								<div class="clearfix"></div>
							<?php
									}
								}
							?>
							</div>
						</div>
						<div class="panel-footer">
							<p id="msg-line">Have a Good Day</p>
						</div>
					</div>
				</div>
			</div>
		</div>

<?php
      // include("inc/profile_modal.php");
?>

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Vendor scripts -->
<!--
<script src="plugin/jquery-flot/jquery.flot.js"></script>
<script src="plugin/jquery-flot/jquery.flot.resize.js"></script>
<script src="plugin/jquery-flot/jquery.flot.pie.js"></script>
<script src="plugin/flot.curvedlines/curvedLines.js"></script>
<script src="plugin/peity/jquery.peity.min.js"></script>
-->

<!-- App scripts -->
<script src="plugin/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>

<!-- Handle Toggle of Switches -->
<script>
	$("#ckb-judging-status").change(function(e) {
		let target = $(this);
		let judging_in_progress = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		$.ajax({
			url: "ajax/set_judging_status.php",
			type: "POST",
			data: { yearmonth, judging_in_progress },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (data.success) {
					if (data.judging_in_progress == '1') {
						$("div.judging-controls").show();
					}
					else {
						$("div.judging-controls").hide();
					}
				}
				else {
					target.prop("checked", ! target.prop("checked"));
					target.bootstrapToggle('destroy');
					target.bootstrapToggle();
					swal("Operation Failed", "Unable to change the Judging Status. Try again.", "error");
				}
			},
		});

	});

	$("input.ckb-open-session").change(function() {
		let target = $(this);
		let session_open = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let award_group = $(this).data("award-group");
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, award_group, section, session_open },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (! data.success) {
					target.prop("checked", ! target.prop("checked"));
					target.bootstrapToggle('destroy');
					target.bootstrapToggle();
					swal("Operation Failed", "Unable to change the Jury Session Status. Try again.", "error");
				}
			},
		});
	});

	$("input.ckb-show-display").change(function() {
		let target = $(this);
		let show_display = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let award_group = $(this).data("award-group");
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, award_group, section, show_display },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (data.success) {
					// Turn all other check boxes off when turning on one checkbox
					if (data.show_display == '1') {
						// $("input.ckb-show-display").prop("checked", false);
						target.prop("checked", true);
						$("input.ckb-show-display").bootstrapToggle('destroy');
						$("input.ckb-show-display").bootstrapToggle();
					}
				}
				else {
					target.prop("checked", ! target.prop("checked"));
					target.bootstrapToggle('destroy');
					target.bootstrapToggle();
					swal("Operation Failed", "Unable to change the Jury Session Status. Try again.", "error");
				}
			},
		});
	});

	$("input.ckb-reveal-result").change(function() {
		let target = $(this);
		let result_ready = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let award_group = $(this).data("award-group");
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, award_group, section, result_ready },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (! data.success) {
					target.prop("checked", ! target.prop("checked"));
					target.bootstrapToggle('destroy');
					target.bootstrapToggle();
					swal("Operation Failed", "Unable to change the Jury Session Status. Try again.", "error");
				}
			},
		});
	});

</script>


<!-- Simple Javascript to mediate results upload -->
<script>
// Global Variable
var server_url = "<?= http_method();?>salon.localhost/jurypanel/svc/put_table_data.php";

// Since $.post executes async queries, results may not arrive on the next line. Hence this function is called when $.post data is received
function send_data_to_server(tableref, table_data) {

	// As simple as this -> just send data to the server
	$("#msg-line").html("Uploading " + tableref + " Data");
	$.post (server_url,
			{ "data" : table_data, "user" : "projector", "auth" : "projector!@#" },
			function (data, status) {
				if (status == "success") alert(data);
				$("#msg-line").html("Finshed Uploading " + tableref + " Data");
				alert("Finshed Uploading " + tableref + " Data");
			}
	);


}

function isValidJSON(str)
{
	try {
		JSON.parse(str);
	}
	catch(e) {
		return false;
	}
	return true;
}

function upload_rating() {
	// Simple Results Uploader
	// Calls a local script to assemble data
	// Then calls a generic server script to load data into the target table

	// Retrieve & upload rating data
	$("#msg-line").html("Assembling Rating Data");
	$.post (
		"svc/get_table_data.php",
		{ "table" : "rating" },
		function (data, status) {
			if (status == "success") {
				if (isValidJSON(data))
					send_data_to_server("Rating", data);	// Send Data to Server when ready with valid data
				else
					alert(data);	// Probably error message
			}
			else
				alert(status);
		},
		"text"
	);
}

function upload_result() {
	// Simple Results Uploader
	// Calls a local script to assemble data
	// Then calls a generic server script to load data into the target table

	// Retrieve & upload result data
	$("#msg-line").html("Assembling Result Data");
	$.post (
		"svc/get_table_data.php",
		{ "table" : "result" },
		function (data, status) {
			if (status == "success") {
				if (isValidJSON(data))
					send_data_to_server("Result", data);	// Send Data to Server when ready with valid data
				else
					alert(data);	// Probably error message
			}
			else
				alert(status);
		},
		"text"
	);
}


</script>


</body>

</html>

<?php
}
else
{
	debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
	$_SESSION['err_msg'] = "Invalid Request";
	header("Location: /jurypanel/index.php" );
	printf("<script>location.href='/jurypanel/index.php'</script>");
}

?>

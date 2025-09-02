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

if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && isset($_SESSION['jury_yearmonth']) ) {

	$jury_id = $_SESSION['jury_id'];
	$jury_yearmonth = $_SESSION['jury_yearmonth'];

	// Get Contest
	$sql = "SELECT * FROM contest WHERE yearmonth = '$jury_yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Get Award Group list
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
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | REMOTE RATING PANEL  </h1>
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
	<?php include("inc/remote_sidebar.php");?>


	<div id="wrapper">
		<div class="content">
			<div class="row">
				<div class="col-lg-12 text-center m-t-md">
					<h2>Welcome to YPS Remote Rating</h2>
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
										<h3 class="text-center">Your Judging Assignments</h3>
									</div>
								</div>
							</div>
							<div class="row">
							<?php
								// Get the list of Assignments
								$sql  = "SELECT * FROM assignment, section ";
								$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
								$sql .= "   AND assignment.user_id = '$jury_id' ";
								$sql .= "   AND section.yearmonth = assignment.yearmonth ";
								$sql .= "   AND section.section = assignment.section ";
								$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								if (mysqli_num_rows($query) == 0)
									die_with_error("Not assigned to any section", __FILE__, __LINE__);
								while ($assignment = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
									$section_stub = $assignment['stub'];
									$assignment_completed = ($assignment['status'] == "COMPLETED");
									$all_award_groups_scored = true;
							?>
								<div class="col-lg-3">
									<div class="hpanel">
										<div class="panel-body text-center">
											<i class="fa fa-4x fa-photo"></i>
											<h3><?php echo $assignment['section'];?></h3>
							<?php
									foreach ($award_group_list as $award_group => $entrant_categories) {
										// Find Number of Pictures Uploaded
										$sql  = "SELECT COUNT(*) AS pics_uploaded FROM pic, entry ";
										$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
										$sql .= "   AND pic.section = '" . $assignment['section'] . "' ";
										$sql .= "   AND entry.yearmonth = pic.yearmonth ";
										$sql .= "   AND entry.profile_id = pic.profile_id ";
										$sql .= "   AND entry.entrant_category IN (" . in_list($entrant_categories) . ") ";
										$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$subr = mysqli_fetch_array($subq);
										$pics_uploaded = $subr['pics_uploaded'];

										// Find number of pictures yet to be scored
										$sql  = "SELECT COUNT(*) AS pics_scored ";
										$sql .= "  FROM pic, rating, entry ";
										$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
										$sql .= "   AND pic.section = '"  . $assignment['section'] . "' ";
										$sql .= "   AND rating.yearmonth = pic.yearmonth ";
										$sql .= "   AND rating.profile_id = pic.profile_id ";
										$sql .= "   AND rating.pic_id = pic.pic_id ";
										$sql .= "   AND rating.user_id = '$jury_id' ";
										$sql .= "   AND entry.yearmonth = pic.yearmonth ";
										$sql .= "   AND entry.profile_id = pic.profile_id ";
										$sql .= "   AND entry.entrant_category IN (" . in_list($entrant_categories) . ") ";
										$subq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$subr = mysqli_fetch_array($subq);
										$pics_scored = $subr['pics_scored'];
										$all_award_groups_scored = ($pics_scored != $pics_uploaded) ? false : $all_award_groups_scored;
										$score_param = encode_string_array($assignment['section'] . "|" . $award_group);
										$award_param = encode_string_array($assignment['section'] . "|" . $award_group);
										$result_param = encode_string_array($assignment['section']);
							?>
											<table class="table" style="background-color: #fffdd0">
												<tr>
													<td>
														<span class="text-info lead"><?= $award_group;?></span>
													</td>
													<td>
														<h3 class="m-xs font-extra-bold no-margins text-success"> <?= $pics_uploaded;?></h3>
														<p><small><b>Total</b></small></p>
													</td>
													<td>
														<h3 class="m-xs font-extra-bold no-margins text-warning"> <?= $pics_scored;?></h3>
														<p><small><b>Scored</b></small></p>
													</td>
												</tr>
												<tr>
													<td class="text-center" colspan="3">
														<div class="row">
															<?php
																if ($contest['results_ready'] == '0' && $contest['judging_in_progress'] == '1') {
															?>
															<div class="col-sm-4">
																<a href="rating_remote.php?show=<?= $score_param;?>"
														   			class="btn btn-info btn-sm"
														   			<?= $assignment_completed ? "disabled" : "";?> >Score</a>
															</div>
															<div class="col-sm-4">
																<a href="award_remote.php?show=<?= $award_param;?>"
														   			class="btn btn-info btn-sm"
														   			<?= $assignment_completed ? "disabled" : "";?> >Award</a>
															</div>
															<?php
																}
															?>
															<div class="col-sm-4">
																<a href="remote_results.php?show=<?= $result_param;?>"
														   			class="btn btn-info btn-sm" >Result</a>
															</div>
														</div>
													</td>
												</tr>
											</table>
							<?php
									}
							?>
											<div class="row">
												<div class="col-sm-12">
													<label>
														<span style="padding-right: 15px;" >Completed Scoring ?</span>
														<input type="checkbox" data-toggle="toggle" data-size="mini"
															   data-on="Yes" data-off="No"
															   class="ckb-assignment-status"
															   data-section="<?= $assignment['section'];?>"
															   <?= $assignment_completed ? "checked" : "";?>
															   <?= ($assignment_completed || (! $all_award_groups_scored)) ? "disabled" : "";?> >
													</label>
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
						<div class="panel-footer">
							<p id="msg-line">Have a Good Day</p>
						</div>
					</div>
				</div>
			</div>
		</div>

<?php
      include("inc/profile_modal.php");
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
	$("input.ckb-open-session").change(function() {
		let target = $(this);
		let session_open = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, section, session_open },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (! data.success) {
					//target.bootstrapToggle('toggle');	// revert action
					swal("Operation Failed", "Unable to change the Jury Session Status. Try again.", "error");
				}
			},
		});
	});

	$("input.ckb-show-display").change(function() {
		let target = $(this);
		let show_display = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, section, show_display },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (! data.success) {
					//target.bootstrapToggle('toggle');	// revert action
					swal("Operation Failed", "Unable to change the Jury Session Status. Try again.", "error");
				}
			},
		});
	});

	$("input.ckb-reveal-result").change(function() {
		let target = $(this);
		let result_ready = $(this).prop("checked") ? '1' : '0';
		let yearmonth = "<?= $jury_yearmonth;?>";
		let section = $(this).data("section");
		$.ajax({
			url: "ajax/set_jury_session.php",
			type: "POST",
			data: { yearmonth, section, result_ready },
			cache: false,
			success: function(response) {
				data = JSON.parse(response);
				if (! data.success) {
					//target.bootstrapToggle('toggle');	// revert action
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

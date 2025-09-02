<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Since there are many columns with Null value, here is a safe way to show null
function safe($str, $default = "") {
	if (is_null($str))
		return $default;
	else
		return $str;
}

function email_filter_from_data ($list) {
	$email_list = [];
	foreach ($list as $item) {
		list ($email, $items, $mailing_date, $tracking_no, $notes) = $item;
		$email_list[] = "'" . $email . "'";
	}
	return implode(",", $email_list);
}

function get_club_name($club_id) {
	global $DBCON;

	if ($club_id != 0 && $club_id != "") {
		$sql = "SELECT club_name FROM club WHERE club_id = '$club_id' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
			return $row['club_name'];
		}
	}
	return "";
}

function get_num_uploads($yearmonth, $profile_id) {
	global $DBCON;
	global $contest_archived;

	$sql  = "SELECT IFNULL(COUNT(*), 0) AS num_pics FROM " . ($contest_archived ? "ar_pic" : "pic") . " ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return $row['num_pics'];
}

function get_catalog_dues($yearmonth, $profile_id) {
	global $DBCON;

	$sql  = "SELECT IFNULL(SUM(number_of_copies), 0) AS number_of_copies, IFNULL(SUM(order_value), 0) AS order_value, ";
	$sql .= "       IFNULL(SUM(payment_received), 0) AS catalog_payment_received, MAX(currency) AS currency ";
	$sql .= "  FROM catalog_order WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return [$row['order_value'] - $row['catalog_payment_received'], $row['currency']];
}


if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

//	$session_id=$_SESSION['jury_id'];
//	$sql = "SELECT * FROM user WHERE user_id = '$session_id'";
//	$user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
//	$user_row = mysqli_fetch_array($user_query);
//	$jury_username = $user_row['login'];
//	$jury_name=$user_row['user_name'];
//	$jury_pic=$user_row['avatar'];
//	$user_type = $user_row['type'];

	$sectionList = array();
	$num_digital_sections = 0;
	$num_print_sections = 0;
	$digital_last_date = "";
	$print_last_date = "";
	// $jury_yearmonth = $_SESSION['jury_yearmonth'];
	$sql = "SELECT section_type, section, submission_last_date FROM section WHERE yearmonth = '$admin_yearmonth' ORDER BY section_type, section";
	$sqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($srow = mysqli_fetch_array($sqry)) {
		$sectionList[$srow['section']] = $srow['section_type'];
		if ($srow['section_type'] == "D") {
			$num_digital_sections ++;
			$digital_last_date = max($srow['submission_last_date'], $digital_last_date);
		}
		if ($srow['section_type'] == "P") {
			$num_print_sections ++;
			$print_last_date = max($srow['submission_last_date'], $print_last_date);
		}
	}

	$youth_category_list = array();
	$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$admin_yearmonth' AND age_within_range = '1' AND age_maximum = '18' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$youth_category_list[] = $row['entrant_category'];

	$cash_award_recipient_list = array();
	$sql  = "SELECT DISTINCT pic_result.profile_id ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award ";
	else
		$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.cash_award > 0 ";
	$sql .= "UNION ";
	$sql .= "SELECT DISTINCT entry_result.profile_id ";
	$sql .= "  FROM entry_result, award ";
	$sql .= " WHERE entry_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.cash_award > 0 ";
	$sql .= " ORDER BY profile_id ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query))
		$cash_award_recipient_list[] = $row['profile_id'];

	$contest_has_cash_awards = (sizeof($cash_award_recipient_list) > 0);

	// Is there Early Bird
	$sql  = "SELECT COUNT(*) AS has_early_bird, MAX(fee_end_date) AS last_date FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$admin_yearmonth' AND fee_code = 'EARLY BIRD' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$has_earlybird = ($row['has_early_bird'] != 0);
	$earlybird_last_date = $row['last_date'];
	$earlybird_open = ($has_earlybird && date_tz(date("Y-m-d"), $admin_contest['submission_timezone']) > $earlybird_last_date);
	// debug_dump("cash_award_recipient_list", $cash_award_recipient_list, __FILE__, __LINE__);

	$salon_reports = [
		"salon_opening" => array(
								"title" => "Salon Opening Mail",
								"valid" => $contest_open,
								"profiles" => "non-entrants",
								"include_indians" => true,
								"include_foreigners" => ($admin_contest['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "salon_open_mail.htm",
								"header_image" => "salon_header.jpg",
								"generator" => "salon_open.php",
								"custom_tags" => array(),
							),
		"salon_reminder" => array(
								"title" => "Salon Reminder",
								"valid" => $contest_open,
								"profiles" => "non-entrants",
								"include_indians" => true,
								"include_foreigners" => ($admin_contest['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "salon_reminder_mail.htm",
								"header_image" => "salon_header.jpg",
								"generator" => "salon_reminder.php",
								"custom_tags" => array(),
							),
		"early_bird_ending" => array(
								"title" => "Early Bird Ending",
								"valid" => $earlybird_open,
								"profiles" => "all",
								"include_indians" => true,
								"include_foreigners" => ($admin_contest['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "salon_opening.htm",
								"header_image" => "salon_header.jpg",
								"generator" => "salon_opening.php",
								"custom_tags" => array(),
							),
	];

	// Establisg Current Report
	$cr_name = "";
	$cr_details = [];
	$cr_include_indians = false;
	$cr_include_foreigners = false;
	$cr_include_yps_members = false;
	foreach ($salon_reports as $report_name => $report_details) {
		if (isset($_REQUEST[$report_name])) {		// based on button pressed
			$cr_name = $report_name;
			$cr_details = $report_details;
			$cr_include_indians = $report_details['include_indians'];
			$cr_include_foreigners = $report_details['include_foreigners'];
			$cr_include_yps_members = $report_details['include_yps_members'];
		}
	}

	// Build the main queries for the current report
	$entry_table = ($contest_archived ? "ar_entry entry" : "entry");
	$has_entry_details = false;
	if (isset($cr_details['profiles'])) {
		switch ($cr_details['profiles']) {
			case 'all' : {
				$main_sql  = "SELECT * FROM profile, country ";
				$main_sql .= " WHERE profile_disabled = '0' ";
				$main_sql .= "   AND country.country_id = profile.country_id ";
				$has_entry_details = false;
				break;
			}
			case 'non-entrants' : {
				$main_sql  = "SELECT * FROM profile, country ";
				$main_sql .= " WHERE profile.profile_id NOT IN ( ";
				$main_sql .= "       SELECT profile_id FROM $entry_table ";
				$main_sql .= "        WHERE yearmonth = '$admin_yearmonth' ) ";
				$main_sql .= "   AND country.country_id = profile.country_id ";
				$main_sql .= "   AND profile_disabled = '0' ";
				$has_entry_details = false;
				break;
			}
			default : {
				// Default to participants in the salon
				$main_sql  = "SELECT * FROM profile, entry, country ";
				$main_sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
				$main_sql .= "   AND entry.profile_id = profile.profile_id ";
				$main_sql .= "   AND country.country_id = profile.country_id ";
				$main_sql .= "   AND profile_disabled = '0' ";
				$has_entry_details = true;
			}
		}
	}
	else {
		// Default to participants in the salon
		$main_sql  = "SELECT * FROM profile, entry ";
		$main_sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
		$main_sql .= "   AND entry.profile_id = profile.profile_id ";
		$has_entry_details = true;
	}
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Participant Follow-up Panel</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

    <!-- App styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />

	<style>
		div.filter-button {
			display:inline-block;
			margin-right: 15px;
		}
	</style>
</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS ADMIN PANEL  </h1>
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

	<!-- Header -->
<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<a class="small-header-action" href="#">
						<div class="clip-header">
							<i class="fa fa-arrow-up"></i>
						</div>
					</a>
					<h3 class="font-light m-b-xs">
						Manage Salon Mails
					</h3>
					<form name="filter_form" method="post" action="salon_mail.php">
						<div class="row">
							<div class="col-sm-12 form-group" >
								<label>List of Mails</label>
								<!-- Participant Filter -->
								<div class="row form-group">
									<?php
										foreach ($salon_reports as $report_name => $report_details) {
											if ($report_details['valid']) {
									?>
									<div class="col-sm-3">
										<div class="input-group">
											<input type="text" class="form-control" value="<?= $report_details['title'];?>" readonly aria-label="...">
											<span class="input-group-btn">
												<button name="<?= $report_name;?>" class="btn btn-info" id="<?= $report_name;?>" ><i class="fa fa-play"></i> GO </button>
											</span>
									    </div>
									</div>
									<?php
											}
										}
									?>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<form method="post" action="op/create_mail_queue.php" onSubmit="return validate();">
							<!-- Hidden Inputs -->
							<input type="hidden" name="admin_email" value="<?= $member_email;?>" >

							<div class="panel-body">
								<div class="row">
									<div class="col-sm-12">
										<h3 class="text-info"><?= isset($cr_details['title']) ? $cr_details['title'] : "None Selected";?></h3>
										<div class="row">
											<div class="col-sm-4">
												<label>Show : </label>
												<span style="margin-left: 10px;">
													<label><input type="radio" name="filter_profiles" id="include_all" <?= ($cr_include_indians && $cr_include_foreigners) ? "checked" : "";?> > All</label>
												</span>
												<span style="margin-left: 10px;">
													<label><input type="radio" name="filter_profiles" id="include_indians" <?= ($cr_include_indians && (! $cr_include_foreigners)) ? "checked" : "";?> > Indians</label>
												</span>
												<span style="margin-left: 10px;">
													<label><input type="radio" name="filter_profiles" id="include_foreigners" <?= ($cr_include_foreigners && (! $cr_include_indians)) ? "checked" : "";?> > Foreigners</label>
												</span>
												<span style="margin-left: 10px;">
													<label><input type="checkbox" name="filter_yps_members" id="include_yps_members" <?= $cr_include_yps_members ? "checked" : "";?> > Include YPS Members</label>
												</span>
											</div>
											<div class="col-sm-4">
											<label>Select : </label>
											<span style="margin-left: 10px;">
												<label><input type="radio" name="select_profiles" id="select_none" checked > None</label>
											</span>
											<span style="margin-left: 10px;">
												<label><input type="radio" name="select_profiles" id="select_all"> All</label>
											</span>
											<span style="margin-left: 10px;"><label><input type="radio" name="select_profiles" id="select_all_on_page"> All on Page</label></span>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-4">
											<span><label>Send Options : </label></span>
											<span style="margin-left: 10px;"><label><input type="checkbox" name="send_test_email" checked > Send Test Mail</label></span>
											<span style="margin-left: 10px;"><label><input type="checkbox" name="no_cc" checked> Do not CC Salon Email</label></span>
										</div>
										<div class="col-sm-3">
											<div class="input-group">
												<input type="text" class="form-control" value="<?= $cr_details['template'];?>" readonly aria-label="...">
												<span class="input-group-btn">
													<button name="edit_template" class="btn btn-info btn-sm form-control" id="edit-templete" ><i class="fa fa-edit"></i> Edit </button>
												</span>
											</div>
										</div>
										<div class="col-sm-2">
											<button name="queue_report" class="btn btn-info btn-sm pull-right" id="queue-report"><i class="fa fa-plus"></i> Queue Report </button>
										</div>
									</div>
								</div>
								<!-- Show Details -->
								<table id="entry_table" class="table table-striped table-bordered table-hover" style="width: 100%;">
								<thead>
									<tr>
										<th>Select</th>
										<th>Category</th>
										<th>Name, Club, Email, Phone</th>
										<th>Sections</th>
										<th>Uploads</th>
										<th>Wins</th>
										<th>Salon Dues</th>
										<th>Catalog Dues</th>
									</tr>
								</thead>
								<tbody>
							<?php
								// Filter Flags
								$showing_pc_list = false;

								// Run the Main Loop
								$entry = mysqli_query($DBCON, $main_sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$row_no = 0;
								while($tr_entry = mysqli_fetch_array($entry)) {
									++ $row_no;
									$profile_id = $tr_entry['profile_id'];
									// Get Club Name
									$club_name = get_club_name($tr_entry['club_id']);

									// Non-profile fields
									$entrant_category = $has_entry_details ? safe($tr_entry['entrant_category']) : "Non-Participant";
									$fee_code = $has_entry_details ? safe($tr_entry['fee_code']) : "";
									$digital_sections = $has_entry_details ? safe($tr_entry['digital_sections'], 0) : 0;
									$print_sections = $has_entry_details ? safe($tr_entry['print_sections'], 0) : 0;
									$awards = $has_entry_details ? safe($tr_entry['awards'], 0) : 0;
									$hms = $has_entry_details ? safe($tr_entry['hms'], 0) : 0;
									$acceptances = $has_entry_details ? safe($tr_entry['acceptances'], 0) : 0;
									$currency = $has_entry_details ? safe($tr_entry['currency']) : "";
									$fees_payable = $has_entry_details ? safe($tr_entry['fees_payable'], 0.0) : 0.0;
									$discount_applicable = $has_entry_details ? safe($tr_entry['discount_applicable'], 0.0) : 0.0;
									$payment_received = $has_entry_details ? safe($tr_entry['payment_received'], 0.0) : 0.0;
									$date_of_birth = safe($tr_entry['date_of_birth']);
									$num_uploads = $has_entry_details ? get_num_uploads($admin_yearmonth, $profile_id) : 0;
									list($catalog_dues, $catalog_currency) = get_catalog_dues($admin_yearmonth, $profile_id);
									// Determine wheather to display the row or hide the row
									$row_visible = false;
									if ($cr_include_indians && $cr_include_foreigners)
										$row_visible = true;
									elseif ($cr_include_indians && $tr_entry['country_id'] == 101)
										$row_visible = true;
									elseif ($cr_include_foreigners && $tr_entry['country_id'] != 101)
										$row_visible = true;
									if ($tr_entry['yps_login_id'] != "" && (! $cr_include_yps_members))
										$row_visible = false;

							?>

									<tr <?= $row_visible ? "" : "style='display: none'";?> >
										<td>
											<input name="checkbox[]" type="checkbox"
													value="<?= $profile_id . "|" . $tr_entry['email'];?>"
													class="row-selector"
													data-row-no="<?= $row_no;?>"
													data-profile-id="<?= $profile_id;?>"
													data-email="<?= $tr_entry['email'];?>"
													data-country="<?= $tr_entry['country_id'];?>"
													data-yps-member-id="<?= $tr_entry['yps_login_id'];?>"
													data-row-visible="<?= $row_visible ? '1' : '0';?>" >
											&nbsp;<?php printf("%4d %s", $row_no, $tr_entry['sortname']);?>
										</td>
										<td><?= $entrant_category;?><br><?= $fee_code;?><br><?= $date_of_birth;?></td>
										<td>
											<?= $tr_entry['profile_name'];?>
											<?= ($tr_entry['yps_login_id'] == "") ? "" : "(" . $tr_entry['yps_login_id'] . ")";?>
											<?= $club_name == "" ? "" : "<br>" . $club_name;?>
											<br>
											<?= $tr_entry['email'] . ", " . $tr_entry['phone'];?>
										</td>
										<td><?= $has_entry_details ? "DG: " . $digital_sections . ", PR: " . $print_sections : "NA";?></td>
										<td class="text-center"><?= $num_uploads;?></td>
										<td><?= "AW:" . $awards . ", HM:" . $hms . ", AC:" . $acceptances;?></td>
										<td><?= sprintf("%.02f %s", $fees_payable - $discount_applicable - $payment_received, $currency);?></td>
										<td><?= sprintf("%.02f %s", $catalog_dues, $catalog_currency);?></td>
									</tr>
							<?php
								}
							?>
								</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
      include("inc/footer.php");
?>


<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">&laquo;</a>
    <a class="next">&raquo;</a>
    <a class="close">&#42;</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>

<!-- DataTables -->
<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

<!-- DataTables buttons scripts -->
<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
<script src="plugin/pdfmake/build/vfs_fonts.js"></script>
<script src="plugin/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="plugin/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="plugin/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugin/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>

<!-- App scripts -->
<script src="plugin/blueimp-gallery/js/jquery.blueimp-gallery.min.js"></script>

<script>
	$(document).ready(function() {
		// Create table
		var table = $('#entry_table').DataTable();

		// Select rows for queuing
		// Select None - Click handler is used to uncheck manually selected rows too
		$("#select_none").click(function(){
			let table = $("#entry_table").DataTable();
			table.$(".row-selector").prop("checked", false);
			console.log(table.$(".row-selector:checked").length + " selected");
		});
		// Select All
		$("#select_all").change(function(){
			// $(".row-selector").prop("checked", false);
			// if ($(this).is(":checked"))
			// $(".row-selector").prop("checked", true);
			let table = $("#entry_table").DataTable();
			table.$(".row-selector").filter("[data-row-visible='1']").prop("checked", true);
			console.log(table.$(".row-selector:checked").length + " selected");
		});
		// Select All on Page
		$("#select_all_on_page").change(function(){
			let table = $("#entry_table").DataTable();
			$(".row-selector").filter("[data-row-visible='1']").prop("checked", true);
			console.log(table.$(".row-selector:checked").length + " selected");
		});

		// Control row visibility - Works on the entire table
		// Common functions for call from multiple event handlers
		function include_all(table, include_yps_members) {
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").attr("data-row-visible", "1");

			}
			// Make checked rows visible
			table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			table.draw();
			console.log(table.$(".row-selector[data-row-visible='1']").length + " rows are visible");
		}
		function include_indians(table, include_yps_members) {
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").filter("[data-country='101']").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").filter("[data-country='101']").attr("data-row-visible", "1");

			}
			// Make checked rows visible
			table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			table.draw();
			console.log(table.$(".row-selector[data-row-visible='1']").length + " rows are visible");
		}
		function include_foreigners(table, include_yps_members) {
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").filter("[data-country!='101']").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").filter("[data-country!='101']").attr("data-row-visible", "1");

			}
			// Make checked rows visible
			table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			table.draw();
			console.log(table.$(".row-selector[data-row-visible='1']").length + " rows are visible");
		}
		// Include all (yps members are included only when checked)
		$("#include_all").change(function(){
			let table = $("#entry_table").DataTable();
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_all(table, include_yps_members);
		});

		// Include Indians
		$("#include_indians").change(function(){
			let table = $("#entry_table").DataTable();
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_indians(table, include_yps_members);
		});

		// Include Foreigners
		$("#include_foreigners").change(function(){
			let table = $("#entry_table").DataTable();
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_foreigners(table, include_yps_members);
		});

		// Handle toggling of YPS Member Inclusion
		$("#include_yps_members").change(function(){
			let table = $("#entry_table").DataTable();
			let include_yps_members = $("#include_yps_members").prop("checked");
			if ($("#include_indians").prop("checked"))
				include_indians(table, include_yps_members);
			else if($("#include_foreigners").prop("checked"))
				include_foreigners(table, include_yps_members);
			else
				include_all(table, include_yps_members);
		});

	});
</script>
<script language="javascript">
function validate() {
	var chks = document.getElementsByName('checkbox[]');
	var hasChecked = false;
	for (var i = 0; i < chks.length; i++){
		if (chks[i].checked){
			hasChecked = true;
			break;
		}
	}
	if (hasChecked == false){
		swal("Selection Required !", "Please select at least one participant to send mail.", "warning");
		return false;
	}
	return true;
}
</script>

<script>

    // $(document).ready(function () {

        // var table = $('#entry_table').DataTable();

		// $("#login_filter").on('keyup change', function() {
		// 	table.column(0).search(this.value)
		// 					.draw();
		// });
		//
		// $("#category_filter").on('keyup change', function() {
		// 	table.column(1).search(this.value)
		// 					.draw();
		// });
		//
		// $("#name_filter").on('keyup change', function() {
		// 	table.column(2).search(this.value)
		// 					.draw();
		// });
		//
		// $("#participation_code_filter").on('keyup change', function() {
		// 	table.column(3).search(this.value)
		// 					.draw();
		// });
	// });

</script>

</body>

</html>
<?php
}
else
{
	header("Location: " . $_SERVER['HTTP_REFERER']);
	print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	//header("Location: /jurypanel/index.php");
	//printf("<script>location.href='/jurypanel/index.php'</script>");
}

?>

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
	// $jury_yearmonth = $_SESSION['jury_yearmonth'];
	$sql = "SELECT section_type, section FROM section WHERE yearmonth = '$admin_yearmonth' ORDER BY section_type, section";
	$sqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($srow = mysqli_fetch_array($sqry)) {
		$sectionList[$srow['section']] = $srow['section_type'];
		if ($srow['section_type'] == "D")
			$num_digital_sections ++;
		if ($srow['section_type'] == "P")
			$num_print_sections ++;
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

	// debug_dump("cash_award_recipient_list", $cash_award_recipient_list, __FILE__, __LINE__);
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
						Manage Participants
					</h3>
					<form name="filter_form" method="post" action="all_participate.php">
						<div class="row">
							<!-- Participating Club Filter -->
							<div class="col-sm-2" style="padding: 4px; <?php echo (isset($_REQUEST['participating-club']) && $_REQUEST['club_id'] != 0) ? "background-color: yellow;" : "";?>" >
								<label>Participating Club</label>
								<div class="input-group">
									<select name="club_id" class="form-control">
										<option value='0'>All Clubs</option>
									<?php
										$sql  = "SELECT * FROM club_entry, club ";
										$sql .= " WHERE club_entry.yearmonth = '$admin_yearmonth' ";
										$sql .= "   AND club_entry.club_id = club.club_id ";
										$club_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($club = mysqli_fetch_array($club_query)) {
											if (isset($_REQUEST['participating-club']) && $club['club_id'] == $_REQUEST['club_id'])
												$selected = "selected";
											else
												$selected = "";

									?>
										<option value="<?=$club['club_id'];?>" <?=$selected;?>><?=$club['club_name'];?></option>
									<?php
										}
									?>
									</select>
									<span class="input-group-btn">
										<button class="btn btn-info" type="submit" name="participating-club"><i class="glyphicon glyphicon-filter small"></i></button>
									</span>
								</div>
							</div>
							<div style="display:inline-block; margin-left:15px;">
								<label>Select Target Group</label><br>
								<div>
									<!-- Payment & Upload Filter -->
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['pay-upload']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="pay-upload"><i class="glyphicon glyphicon-filter small"></i> Payment/Upload Pending</button>
									</div>
									<!-- Full Resolution Upload Filter -->
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['frf-upload']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="frf-upload"><i class="glyphicon glyphicon-filter small"></i> Exhibition Action Pending</button>
									</div>
									<!-- Salon Participants - Paid Filter -->
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['salon-entrants']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="salon-entrants"><i class="glyphicon glyphicon-filter small"></i> Salon Participants</button>
									</div>
									<!-- Non Participants - Marketing Filter -->
									<?php
										if ( file_exists("../salons/$admin_yearmonth/blob/salon_open_mail.htm") || file_exists("../salons/$admin_yearmonth/blob/salon_reminder_mail.htm") ) {
									?>
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['non-entrants']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="non-entrants"><i class="glyphicon glyphicon-filter small"></i> Non Participants</button>
									</div>
									<?php
										}
									?>
									<?php
										if ( file_exists("../salons/$admin_yearmonth/blob/award_winners_communication.htm") ) {
									?>
									<!-- Salon Award Winners -->
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['salon-recipients']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="salon-recipients"><i class="glyphicon glyphicon-filter small"></i> Award Recipients</button>
									</div>
									<?php
										}
									?>
									<!-- Custom Mailing List -->
									<?php
										if ( file_exists("../salons/$admin_yearmonth/blob/mailing_data.php") ) {
									?>
									<div class="filter-button" style="padding: 4px; <?php echo isset($_REQUEST['mailing-list']) ? "background-color: yellow;" : "";?>" >
										<button class="btn btn-info" type="submit" name="mailing-list"><i class="glyphicon glyphicon-filter small"></i> Mailing List</button>
									</div>
									<?php
										}
									?>
									<!-- Salon Participating Clubs -->
									<!-- <div class="col-sm-2" style="padding: 4px; <?php //echo isset($_REQUEST['salon-clubs']) ? "background-color: yellow;" : "";?>" >
										<label>Salon Participants</label><br>
										<button class="btn btn-info" type="submit" name="salon-clubs"><i class="glyphicon glyphicon-filter small"></i> Filter</button>
									</div> -->
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
						<form method="post" action="op/all_send_mail.php" onSubmit="return validate();">
							<!-- Hidden Inputs -->
							<input type="hidden" name="admin_email" value="<?= $member_email;?>" >

							<!-- Participating Club Actions -->
							<?php
								if (isset($_REQUEST['participating-club']) && $_REQUEST['club_id'] != 0) {
							?>
							<!-- <input type="hidden" name="club_id" value="<?=$_REQUEST['club_id'];?>" >
							<button type="submit" value="Club Member Reminder" name="club_reminder_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-upload"></i>
								Remind Club Members
							</button> -->
							<?php
								}
							?>

							<!-- Payment & Upload Actions -->
							<?php
								if (isset($_REQUEST['pay-upload'])) {
							?>
							<!-- <button type="submit" value="Early Bird Reminder" name="earlybird_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-upload"></i>
								Early Bird Payment
							</button>
							<button type="submit" value="Send Email" name="upload_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-upload"></i>
								Payment &amp; Upload
							</button> -->
							<?php
								}
							?>

							<!-- Full Resolution Upload Actions -->
							<?php
								if (isset($_REQUEST['frf-upload'])) {
							?>
							<!-- <button type="submit" value="Bank Details" name="bank_details_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Upload/Acct. Update Reminder
							</button> -->
							<?php
								}
							?>

							<!-- Salon Participants - Paid Actions -->
							<?php
								if (isset($_REQUEST['salon-entrants'])) {
							?>
							<!-- <button type="submit" value="Judging Invite" name="judging_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Judging Invite
							</button>
							<button type="submit" value="Mail Results" name="results_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Mail Results
							</button>
							<button type="submit" value="Invite_to_Exhibition" name="exhibition_invite" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Invite to Exhibition
							</button>
							<button type="submit" value="Mail Catalog" name="catalog_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Mail Catalog
							</button>
							<button type="submit" value="Remind Catalog" name="catalog_remind" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Remind Catalog Order
							</button> -->
							<?php
								}
							?>

							<!-- Non Participants - Marketing Actions -->
							<?php
								if (isset($_REQUEST['non-entrants'])) {
									if ( file_exists("../salons/$admin_yearmonth/blob/salon_open_mail.htm") ) {
							?>
							<!-- <button type="submit" value="Salon Open" name="salon_open_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Salon Open Mail
							</button> -->
							<?php
									}
									if ( file_exists("../salons/$admin_yearmonth/blob/salon_reminder_mail.htm") ) {
							?>
							<!-- <button type="submit" value="Salon Reminder" name="salon_reminder_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Salon Reminder Mail
							</button> -->
							<?php
									}
								}
							?>

							<!-- Salon Award Winners Actions -->
							<?php
								if (isset($_REQUEST['salon-recipients'])) {
							?>
							<!-- <button type="submit" value="Custom Email" name="custom_awardee_email" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Custom Email to Awardees
							</button> -->
							<?php
								}
								if (isset($_REQUEST['mailing-list'])) {
							?>
							<!-- <button type="submit" value="Custom Email" name="mailing_info_email" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Send Mailing Info
							</button> -->
							<?php
								}
							?>

							<?php
								if (isset($_REQUEST['salon-clubs'])) {
							?>
							<!-- <button type="submit" value="Mail Club Results" name="club_results_mail" class="btn btn-info" aria-label="icon note circle bordered">
								<i class="fa fa-mail-forward"></i>
								Mail Club Results
							</button> -->
							<?php
								}
							?>
							<div class="panel-body">
								<p>
									<span><label>Select on Page : [ </label></span>
									<span style="margin-left: 10px;"><label><input type="radio" name="select-profiles" id="select_none" checked > None</label></span>
									<span style="margin-left: 10px;"><label><input type="radio" name="select-profiles" id="select_all"> All</label></span>
									<span style="margin-left: 10px;"><label><input type="radio" name="select-profiles" id="select_indians" > Indians</label></span>
									<span style="margin-left: 10px;"><label><input type="radio" name="select-profiles" id="select_foreigners" > Foreigners</label></span>
									<span style="margin-left: 10px; margin-right: 10px;"><label> ] </label></span>
									<span style="margin-left: 10px;"><label><input type="checkbox" name="send_test_email"> Send Test Mail</label></span>
									<span style="margin-left: 10px;"><label><input type="checkbox" name="no_cc"> Do not CC Salon Email</label></span>
								</p>
								<table id="entry_table" class="table table-striped table-bordered table-hover" style="width: 100%;">
								<thead>
									<tr>
										<th rowspan="2">Select</th>
										<th rowspan="2">Category</th>
										<th rowspan="2">Name, Club, Email, Phone, Bank Acct</th>
										<th rowspan="2">Participation</th>
										<?php
											if ($num_digital_sections > 0) {
										?>
										<th colspan="<?php echo $num_digital_sections; ?>" class="text-center">Digital</th>
										<?php
											}
											if ($num_print_sections > 0) {
										?>
										<th colspan="<?php echo $num_print_sections; ?>" class="text-center">Print</th>
										<?php
											}
										?>
										<th colspan="3" class="text-center">Wins</th>
										<th rowspan="2" class="text-center">Age Proof</th>
										<th colspan="4" class="text-center">Salon Fees</th>
										<th colspan="3" class="text-center">Catalog</th>
									</tr>
									<tr>
									<?php
										foreach ($sectionList as $this_section => $this_type) {
											if ($this_type == "D") {
									?>
										<th class="text-center"><?php echo substr($this_section, 0, 2); ?></th>
									<?php
											}
										}
									?>
									<?php
										foreach ($sectionList as $this_section => $this_type) {
											if ($this_type == "P") {
									?>
										<th class="text-center"><?php echo substr($this_section, 0, 2); ?></th>
									<?php
											}
										}
									?>
									<th class="text-center">AW</th>
									<th class="text-center">AC</th>
									<th class="text-center">FF</th>
									<th class="text-right">Fees</th>
									<th class="text-right">Discount</th>
									<th class="text-right">Paid</th>
									<th class="text-right">Due</th>
									<th class="text-right">No.</th>
									<th class="text-right">Price</th>
									<th class="text-right">Paid</th>
									</tr>
								</thead>
								<tbody>
							<?php
								// Filter Flags
								$showing_pc_list = false;

								// Default Query
								if ($contest_archived)
									$sql  = "SELECT * FROM ar_entry entry, profile ";
								else
									$sql  = "SELECT * FROM entry, profile ";
								$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
								$sql .= "   AND entry.profile_id = profile.profile_id ";

								// Check if there are filters
								// Club Participants with Coupons
								if (isset($_REQUEST['participating-club']) && isset($_REQUEST['club_id']) && $_REQUEST['club_id'] != 0) {
									$showing_pc_list = true;
									$club_id = $_REQUEST['club_id'];
									$sql  = "SELECT profile.profile_id, profile.profile_name, profile.phone, profile.age_proof, profile.age_proof_file, profile.country_id, ";
									$sql .= "       profile.bank_account_number, profile.bank_account_name, profile.bank_name, profile.bank_ifsc_code, profile.bank_branch, ";
									$sql .= "       entry.awards, entry.hms, entry.acceptances, entry.entrant_category, entry.digital_sections, entry.print_sections, entry.fee_code, ";
									$sql .= "       entry.fees_payable, entry.discount_applicable, entry.payment_received, entry.currency, ";
									$sql .= "       coupon.club_id, coupon.email ";
									$sql .= "  FROM coupon ";
									$sql .= "  LEFT JOIN profile ON coupon.profile_id = profile.profile_id ";
									if ($contest_archived)
										$sql .= "  LEFT JOIN ar_entry entry ON (entry.yearmonth = '$admin_yearmonth' AND entry.profile_id = profile.profile_id) ";
									else
										$sql .= "  LEFT JOIN entry ON (entry.yearmonth = '$admin_yearmonth' AND entry.profile_id = profile.profile_id) ";
									$sql .= " WHERE coupon.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND coupon.club_id = '$club_id' ";
								}

								if (isset($_REQUEST['pay-upload'])) {
									if ($contest_archived)
										$sql  = "SELECT * FROM ar_entry entry, profile ";
									else
										$sql  = "SELECT * FROM entry, profile ";
									$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND entry.profile_id = profile.profile_id ";
									$sql .= "   AND (entry.uploads = 0 OR entry.payment_received < (entry.fees_payable - entry.discount_applicable) ) ";
								}

								if (isset($_REQUEST['frf-upload'])) {
									if ($contest_archived)
										$sql  = "SELECT * FROM ar_entry entry, profile ";
									else
										$sql  = "SELECT * FROM entry, profile ";
									$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND entry.profile_id = profile.profile_id ";
									$sql .= "   AND profile.profile_id IN ( ";
									$sql .= "           SELECT DISTINCT pic_result.profile_id ";
									if ($contest_archived)
										$sql .= "             FROM ar_pic_result pic_result, award, ar_pic pic, section ";
									else
										$sql .= "             FROM pic_result, award, pic, section ";
									$sql .= "            WHERE pic_result.yearmonth = '$admin_yearmonth' ";
									$sql .= "              AND award.yearmonth = pic_result.yearmonth ";
									$sql .= "              AND award.award_id = pic_result.award_id ";
									$sql .= "              AND award.section != 'CONTEST' ";
									$sql .= "              AND award.award_type = 'pic' ";
									$sql .= "              AND award.level <= 9 ";
									$sql .= "              AND pic.yearmonth = pic_result.yearmonth ";
									$sql .= "              AND pic.profile_id = pic_result.profile_id ";
									$sql .= "              AND pic.pic_id = pic_result.pic_id ";
									$sql .= "              AND (pic.full_picfile IS NULL OR pic.full_picfile = '') ";
									$sql .= "              AND section.section = pic.section ";
									$sql .= "              AND section.section_type = 'D' ";
									$sql .= "            ) ";
									if (sizeof($cash_award_recipient_list) > 0) {
										$sql .= "UNION ";
										if ($contest_archived)
											$sql .= "SELECT * FROM ar_entry entry, profile ";
										else
											$sql .= "SELECT * FROM entry, profile ";
										$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
										$sql .= "   AND profile.profile_id = entry.profile_id ";
										$sql .= "   AND profile.profile_id IN (" . implode(",", $cash_award_recipient_list) . ") ";
										$sql .= "   AND profile.bank_account_number = '' ";
									}
								}

								if (isset($_REQUEST['salon-entrants'])) {
									if ($contest_archived)
										$sql  = "SELECT * FROM ar_entry entry, profile ";
									else
										$sql  = "SELECT * FROM entry, profile ";
									$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND entry.profile_id = profile.profile_id ";
									$sql .= "   AND (entry.uploads > 0 OR entry.payment_received > 0) ";
								}

								$for_non_entrants = false;
								if (isset($_REQUEST['non-entrants'])) {
									$for_non_entrants = true;
									$sql  = "SELECT * FROM profile ";
									$sql .= " WHERE profile_id NOT IN ( ";
									if ($contest_archived)
										$sql .= "SELECT profile_id FROM ar_entry WHERE yearmonth = '$admin_yearmonth' ";
									else
										$sql .= "SELECT profile_id FROM entry WHERE yearmonth = '$admin_yearmonth' ";
									$sql .= "       ) ";
									$sql .= "   AND profile_disabled = '0' ";
								}

								if (isset($_REQUEST['salon-recipients'])) {
									if ($contest_archived)
										$sql  = "SELECT * FROM ar_entry entry, profile ";
									else
										$sql  = "SELECT * FROM entry, profile ";
									$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND entry.profile_id = profile.profile_id ";
									$sql .= "   AND profile.profile_id IN ( ";
									$sql .= "           SELECT DISTINCT pic_result.profile_id ";
									if ($contest_archived)
										$sql .= "             FROM ar_pic_result pic_result, award ";
									else
										$sql .= "             FROM pic_result, award ";
									$sql .= "            WHERE pic_result.yearmonth = '$admin_yearmonth' ";
									$sql .= "              AND award.yearmonth = pic_result.yearmonth ";
									$sql .= "              AND award.award_id = pic_result.award_id ";
									$sql .= "              AND award.section != 'CONTEST' ";
									$sql .= "              AND award.award_type = 'pic' ";
									$sql .= "              AND award.level <= 9 ";
									$sql .= "           UNION ";
									$sql .= "           SELECT DISTINCT profile_id FROM catalog_order ";
									$sql .= "            WHERE catalog_order.yearmonth = '$admin_yearmonth' ";
									$sql .= "              AND order_value = payment_received ";
									$sql .= "            ) ";
								}

								if ( isset($_REQUEST['mailing-list']) ) {
									include ("../salons/$admin_yearmonth/blob/mailing_data.php");
									$email_filter = email_filter_from_data($mailing_data);
									if ($contest_archived)
										$sql  = "SELECT * FROM ar_entry entry, profile ";
									else
										$sql  = "SELECT * FROM entry, profile ";
									$sql .= " WHERE entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND entry.profile_id = profile.profile_id ";
									$sql .= "   AND profile.email IN ('cantbeempty', " . $email_filter . ") ";
								}

								if (isset($_REQUEST['salon-clubs']) ) {
									$showing_pc_list = true;
									$sql  = "SELECT profile.profile_id, profile.profile_name, profile.phone, profile.age_proof, profile.age_proof_file, profile.country_id, ";
									$sql .= "       profile.bank_account_number, profile.bank_account_name, profile.bank_name, profile.bank_ifsc_code, profile.bank_branch, ";
									$sql .= "       entry.awards, entry.hms, entry.acceptances, entry.entrant_category, entry.digital_sections, entry.print_sections, entry.fee_code, ";
									$sql .= "       entry.fees_payable, entry.discount_applicable, entry.payment_received, entry.currency, ";
									$sql .= "       coupon.club_id, coupon.email ";
									if ($contest_archived)
										$sql .= "  FROM club_entry, ar_entry entry, profile ";
									else
										$sql .= "  FROM club_entry, entry, profile ";
									$sql .= " WHERE club_entry.yearmonth = '$admin_yearmonth' ";
									$sql .= "   AND coupon.club_id = '$club_id' ";
								}

								$entry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								$row_no = 0;
								while($tr_entry = mysqli_fetch_array($entry)) {
									++ $row_no;
									// Get Club Name
									$club_name = "";
									if ($tr_entry['club_id'] > 0) {
										$sql = "SELECT * FROM club WHERE club_id = '" . $tr_entry['club_id'] . "' ";
										$clubq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										if (mysqli_num_rows($clubq) > 0) {
											$club_row = mysqli_fetch_array($clubq, MYSQLI_ASSOC);
											$club_name = $club_row['club_name'];
										}
									}

									// Non-profile fields
									$entrant_category = $for_non_entrants ? "" : safe($tr_entry['entrant_category']);
									$fee_code = $for_non_entrants ? "" : safe($tr_entry['fee_code']);
									$digital_sections = $for_non_entrants ? 0 : safe($tr_entry['digital_sections'], 0);
									$print_sections = $for_non_entrants ? 0 : safe($tr_entry['print_sections'], 0);
									$awards = $for_non_entrants ? 0 : safe($tr_entry['awards'], 0);
									$hms = $for_non_entrants ? 0 : safe($tr_entry['hms'], 0);
									$acceptances = $for_non_entrants ? 0 : safe($tr_entry['acceptances'], 0);
									$currency = $for_non_entrants ? "" : safe($tr_entry['currency']);
									$fees_payable = $for_non_entrants ? 0.0 : safe($tr_entry['fees_payable'], 0.0);
									$discount_applicable = $for_non_entrants ? 0.0 : safe($tr_entry['discount_applicable'], 0.0);
									$payment_received = $for_non_entrants ? 0.0 : safe($tr_entry['payment_received'], 0.0);


									// Check Club Discount
									// $sql  = "SELECT * FROM coupon, club ";
									// $sql .= "WHERE coupon.club_id = club.club_id ";
									// $sql .= "  AND coupon.profile_id = '" . $tr_entry['profile_id'] . "' ";
									// $cq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									// $club_registered = (mysqli_num_rows($cq) > 0) ? "(Registered)" : "";

							?>
									<tr>
										<td>
											<input name="checkbox[]" type="checkbox"
													value="<?= safe($tr_entry['profile_id'], 0) . "|" . $tr_entry['email'];?>"
													class="row-selector"
													data-country="<?= $tr_entry['country_id'];?>" >
											&nbsp;<?php printf("%4d", $row_no);?>
										</td>
										<td><?=safe($entrant_category);?><br><?=safe($fee_code);?><br><?=safe($tr_entry['date_of_birth']);?></td>
										<td>
							<?php
									echo safe($tr_entry['profile_name'], "Not Registered");;
									if ($tr_entry['club_id'] != "")
										echo "<br>" . $club_name;
									echo "<br>" . $tr_entry['email'] . ", " . safe($tr_entry['phone']);
									// if ((safe($tr_entry['awards'], 0) + safe($tr_entry['hms'], 0)) > 0 && safe($tr_entry['entrant_category']) != "YOUTH") {
									if (in_array($tr_entry['profile_id'], $cash_award_recipient_list)) {
										if (safe($tr_entry['bank_account_number']) == "")
											echo "<br><b>Bank Account NOT UPDATED</b>";
										else {
											echo "<br>" . safe($tr_entry['bank_account_name']);
											echo "<br>Acct: " . safe($tr_entry['bank_account_number']);
											echo "<br>" . safe($tr_entry['bank_name']);
											echo "<br>IFSC: " . safe($tr_entry['bank_ifsc_code']);
											echo "<br>" . safe($tr_entry['bank_branch']);
										}
									}
							?>
										</td>
										<td><?php echo (($digital_sections > 0) ? "DG: " . $digital_sections : "") . (($print_sections > 0) ? " PR: " . $print_sections : "");?></td>
							<?php
									$uid = safe($tr_entry['profile_id'], 0);
									foreach ($sectionList as $this_section => $this_type) {
										if ($this_type == 'D') {
											if (! $for_non_entrants) {
												if ($contest_archived)
													$sql = "SELECT * FROM ar_pic pic WHERE yearmonth = '$admin_yearmonth' AND profile_id='$uid' AND section = '$this_section'";
												else
													$sql = "SELECT * FROM pic WHERE yearmonth = '$admin_yearmonth' AND profile_id='$uid' AND section = '$this_section'";
												$pics_m = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							?>
										<td class="text-center"><?php echo mysqli_num_rows($pics_m);?></td>
							<?php
											}
											else {
							?>
										<td class="text-center">0</td>
							<?php
											}
										}
									}
									$uid = safe($tr_entry['profile_id'], 0);
									foreach ($sectionList as $this_section => $this_type) {
										if ($this_type == 'P') {
											if (! $for_non_entrants) {
												if ($contest_archived)
													$sql = "SELECT * FROM ar_pic pic WHERE yearmonth = '$admin_yearmonth' AND profile_id='$uid' AND section = '$this_section'";
												else
													$sql = "SELECT * FROM pic WHERE yearmonth = '$admin_yearmonth' AND profile_id='$uid' AND section = '$this_section'";
												$pics_m = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							?>
										<td class="text-center"><?php echo mysqli_num_rows($pics_m);?></td>
							<?php
											}
											else {
							?>
										<td class="text-center">0</td>
							<?php
											}
										}
									}
							?>
										<td class="text-center"><?= $awards + $hms;?></td>
										<td class="text-center"><?= $acceptances;?></td>
							<?php
									// Number of Full-sized Picture Files Uploaded
									if (! $for_non_entrants) {
										$sql  = "SELECT COUNT(*) AS num_ff ";
										if ($contest_archived)
											$sql .= "  FROM ar_pic_result pic_result, ar_pic pic, award ";
										else
											$sql .= "  FROM pic_result, pic, award ";
										$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
										$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
										$sql .= "   AND award.award_id = pic_result.award_id ";
										$sql .= "   AND award.level < 99 ";
										$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
										$sql .= "   AND pic.profile_id = pic_result.profile_id ";
										$sql .= "   AND pic.pic_id = pic_result.pic_id ";
										$sql .= "   AND pic.profile_id = '$uid' ";
										$sql .= "   AND pic.full_picfile IS NOT NULL";
										$ffq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$ffr = mysqli_fetch_array($ffq);
							?>
										<td class="text-center"><?php echo $ffr['num_ff'];?></td>
							<?php
									}
									else {
							?>
										<td class="text-center">0</td>
							<?php
									}
									// Payments Alert
									$alert = "";
									if (! $for_non_entrants) {
										$sql = "SELECT * FROM payment WHERE yearmonth = '$admin_yearmonth' AND account = 'IND' AND link_id = '" . safe($tr_entry['profile_id'], 0) . "' ";
										$payq = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										if (mysqli_num_rows($payq) > 0) {
											$alert = "Payments Received : ";
											$count = 0;
											while ($payr = mysqli_fetch_array($payq)) {
												$alert .= sprintf("(%d) Rs. %7.2f on %s/%s", ++$count, $payr['amount'], substr($payr['datetime'], 6, 2), substr($payr['datetime'], 4, 2)) . "    ";
											}
										}
									}
							?>
							<?php
								if (safe($tr_entry['age_proof']) != 'none' && safe($tr_entry['age_proof']) != "" && safe($tr_entry['age_proof_file']) != "") {
							?>
										<td class="text-center"><a href="/res/age_proof/<?php echo $tr_entry['age_proof_file'];?>" target="_blank">View <?php echo $tr_entry['age_proof']; ?></a></td>
							<?php
								}
								else {
							?>
										<td class="text-center">-- NA --</td>
							<?php
								}
							?>
										<td class="text-right"><?= $fees_payable . " " . $currency;?></td>
										<td class="text-right"><?= $discount_applicable . " " . $currency; ?></td>
							<?php
									if ($alert == "") {
							?>
										<td class="text-right"><?= $payment_received . " " . $currency;?></td>
							<?php
									}
									else {
							?>
										<td class="text-right"><a href="javascript:alert('<?php echo $alert;?>')"><?= $payment_received . " " . $currency;?></a></td>
							<?php
									}
							?>
										<td class="text-right"><?= sprintf("%.02f %s", $fees_payable - $discount_applicable - $payment_received, $currency);?></td>
							<?php
									// Catalog Orders
									if (! $for_non_entrants) {
										$sql  = "SELECT IFNULL(SUM(number_of_copies), 0) AS number_of_copies, IFNULL(SUM(order_value), 0) AS order_value, ";
										$sql .= "       IFNULL(SUM(payment_received), 0) AS catalog_payment_received, MAX(currency) AS currency ";
										$sql .= "  FROM catalog_order WHERE yearmonth = '$admin_yearmonth' AND profile_id = '$uid' ";
										$catalog_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$catalog = mysqli_fetch_array($catalog_query);
							?>
										<td class="text-center"><?php echo safe($catalog['number_of_copies'], 0);?></td>
										<td class="text-right"><?php echo sprintf("%.02f %s", safe($catalog['order_value'], 0), $catalog['currency']);?></td>
										<td class="text-right"><?php echo sprintf("%.02f %s", safe($catalog['catalog_payment_received'], 0), $catalog['currency']);?></td>
							<?php
									}
									else {
							?>
										<td class="text-center">0</td>
										<td class="text-right">0.00</td>
										<td class="text-right">0.00</td>
							<?php
									}
							?>
									</tr>
							<?php
								}
							?>
								</tbody>
								<tfoot>
									<tr>
										<th><input type="text" id="login_filter" placeholder="Num.." size="5" ></th>
										<th><input type="text" id="category_filter" placeholder="Filter..." size="8" ></th>
										<th><input type="text" id="name_filter" placeholder="Name..." size="12" ></th>
										<th><input type="text" id="participation_code_filter" placeholder="Filter..." size="8" ></th>
										<th colspan="<?php echo $num_digital_sections + $num_print_sections;?>"></th>
										<th colspan="3"></th>
										<th></th>
										<th colspan="4"></th>
										<th colspan="3"></th>
									</tr>
								</tfoot>
								</table>
							</div>
						</form>
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
		// Select All
		$("#select_none").change(function(){
			$(".row-selector").prop("checked", false);
		});
		// Select All
		$("#select_all").change(function(){
			// $(".row-selector").prop("checked", false);
			// if ($(this).is(":checked"))
				$(".row-selector").prop("checked", true);
		});
		// Select Indians
		$("#select_indians").change(function(){
			$(".row-selector").prop("checked", false);
			// if ($(this).is(":checked"))
			$(".row-selector").filter("[data-country='101']").prop("checked", true);
		});
		// Select Indians
		$("#select_foreigners").change(function(){
			$(".row-selector").prop("checked", false);
			// if ($(this).is(":checked"))
			$(".row-selector").filter("[data-country!='101']").prop("checked", true);
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

    $(document).ready(function () {

        var table = $('#entry_table').DataTable();

		$("#login_filter").on('keyup change', function() {
			table.column(0).search(this.value)
							.draw();
		});

		$("#category_filter").on('keyup change', function() {
			table.column(1).search(this.value)
							.draw();
		});

		$("#name_filter").on('keyup change', function() {
			table.column(2).search(this.value)
							.draw();
		});

		$("#participation_code_filter").on('keyup change', function() {
			table.column(3).search(this.value)
							.draw();
		});
	});

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

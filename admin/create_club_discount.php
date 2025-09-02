<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

define ("DATE_IN_SUBMISSION_TIMEZONE", date("Y-m-d"));

// Minimal Validations
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	$session_id=$_SESSION['admin_id'];

	// Prepare Lists for selection
	// List of fee_codes open based on current date
	// --------------------------------------------
	$fee_code_list = array();
	$sql  = "SELECT DISTINCT fee_code FROM fee_structure ";
	$sql .= "WHERE yearmonth = '$admin_yearmonth' ";
	$sql .= "  AND fee_end_date >= '" . DATE_IN_SUBMISSION_TIMEZONE . "' ";
	$fc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($fc) == 0) {
		$contest_closed = true;
	}
	else {
		$contest_closed = false;
		while($rfc = mysqli_fetch_array($fc, MYSQLI_ASSOC))
			$fee_code_list[$rfc['fee_code']] = $rfc;;
	}

	// List of Clubs
	// $club_list = array();
	// $sql  = "SELECT * FROM club, club_entry LEFT JOIN discount ";
	// $sql .= "    ON discount.yearmonth = club_entry.yearmonth ";
	// $sql .= "   AND discount.discount_code = 'CLUB' ";
	// $sql .= "   AND discount.fee_code = club_entry.fee_code ";
	// $sql .= "   AND discount.group_code = club_entry.group_code ";
	// $sql .= " WHERE club_entry.yearmonth = '$admin_yearmonth' ";
	// $sql .= "   AND club_entry.club_id = club.club_id ";
	// $tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// while ($tmpr = mysqli_fetch_array($tmpq, MYSQLI_ASSOC))
	// 	$club_list[$tmpr['club_id']] = $tmpr;

	// List of Entrant Categories
	$ec_list = array();
	$sql  = "SELECT * FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND yps_membership_required = '0' ";
	$sql .= "   AND fee_waived = '0' ";
	$tmpq = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($tmpr = mysqli_fetch_array($tmpq, MYSQLI_ASSOC))
		$ec_list[$tmpr['entrant_category']] = $tmpr;

?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Group Discount Administration Panel</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />

    <!-- App styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />

</head>
<body class="fixed-navbar fixed-sidebar">
	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS MASTER PANEL  </h1>
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

<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

    <div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="row">
				<?php
					if (! $contest_closed) {
				?>
				<div class="col-sm-3">
					<div class="hpanel">
						<div class="panel-body">
							<a class="small-header-action" href="#">
								<div class="clip-header">
									<i class="fa fa-arrow-up"></i>
								</div>
							</a>
							<h3 class="font-light m-b-xs">
								Create Club Discount
							</h3>
							<hr>
							<!-- Left side Form -->
							<!-- Top Form for generating Discount Options -->
							<form role="form" method="post" action="op/create_club_entry.php" id="group-form" name="group-form" enctype="multipart/form-data">

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label for="club_id">Select the Club *</label>
											<select name="club_id" id="club_id" class="form-control" required ></select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label  for="profile_id">Select the co-ordinator using email *</label>
											<select name="profile_id" id="profile_id" class="form-control" required disabled ></select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label  for="entrant_category">Participation Category *</label>
											<select name="entrant_category" id="entrant_category" class="form-control" required disabled>
											<?php
												foreach($ec_list as $ec => $ec_row) {
											?>
												<option value="<?=$ec_row['entrant_category'];?>" id="<?=$ec_row['entrant_category'];?>"
														data-currency="<?=$ec_row['currency'];?>" >
													<?=$ec_row['entrant_category_name'];?>
												</option>
											<?php
												}
											?>
											</select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label  for="fee_code">Fee Code *</label>
											<select name="fee_code" id="fee_code" class="form-control" required disabled>
											<?php
												foreach($fee_code_list as $fee_code => $fc_row) {
											?>
												<option value="<?=$fee_code;?>" id="<?=$fee_code;?>"><?=$fee_code;?></option>
											<?php
												}
											?>
											</select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label for="promised_group_size">Promised Group Size *</label>
											<input name="promised_group_size" id="promised_group_size" type="number" class="form-control" required disabled >
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<label for="payment_mode">Payment *</label>
										</div>
										<div class="col-sm-4">
											<label>
												<input type="radio" name="payment_mode" value="SELF_PAYMENT" id="payment_mode_self" required disabled>
												&nbsp;&nbsp;By Members
											</label>
										</div>
										<div class="col-sm-8">
											<label>
												<input type="radio" name="payment_mode" value="GROUP_PAYMENT" id="payment_mode_group" required disabled>
												&nbsp;&nbsp;By Group Co-ordinator
											</label>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<?php
											if ($admin_contest['fee_model'] == "POLICY") {
										?>
										<!-- Standard options -->
										<div class="col-sm-12">
											<label  for="group_code">Standard Discount Options</label>
											<select name="group_code" id="group_code" class="form-control" disabled ></select>
										</div>
										<!-- Specific Discount -->
										<input type="hidden" name="allow_standard_discounts" value="NO" id="allow_standard_discounts" >
										<div class="col-sm-6">
											<label for="discount_percentage">Special Discount % *</label>
											<input name="discount_percentage" id="discount_percentage" type="number" min="0" max="50" value="0" class="form-control" disabled >
										</div>
										<?php
											}
											else {
										?>
										<input name="discount_percentage" type="hidden" value="0" >
										<input name="group_code" type="hidden" value="" >
										<label for="allow_standard_discounts">Discount *</label>
										<div class="col-sm-12" style="padding-top: 4px;">
											<label>
												<input type="radio" name="allow_standard_discounts" value="YES" id="allow_standard_discounts" disabled >
												&nbsp;&nbsp;Allow Standard Discounts
											</label>
										</div>
										<?php
											}
										?>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<!-- List of Participation Codes -->
										<div class="col-sm-12">
											<label  for="participation_codes">Restrict Participation to</label>
											<select name="participation_codes[]" id="participation_codes" class="form-control" required disabled ></select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-12">
											<br>
											<button type="submit" class="btn btn-info pull-right" name="create_club_entry" id="create_club_entry" disabled>Create Club Discount</button>
										</div>
									</div>
								</div>

							</form>

						</div>	<!-- / .panel-body -->
					</div>		<!-- / .panel -->
				</div>			<!-- Left Half -->
				<?php
					}
				?>
				<div class="col-sm-9">
					<div class="hpanel">
						<form method="post" action="op/group_send_mail.php" onSubmit="return validate()" >
							<div class="panel-body">
								<p>
									<span style="margin-left: 10px;"><label><input type="checkbox" id="select_all">&nbsp;&nbsp;Select All on Page</label></span>
								</p>
								<table id="group_table" class="table table-striped table-bordered table-hover" style="width: 100%">
									<thead>
										<tr>
											<th rowspan="2">Select</th>
											<th rowspan="2">Club</th>
											<th rowspan="2">Members</th>
											<th colspan="6" class="text-center">Participation</th>
										</tr>
										<tr>
											<th>DIG</th>
											<th>PRT</th>
											<th>FEES</th>
											<th>DISCOUNT</th>
											<th>PAID</th>
											<th>UPLOAD</th>
										</tr>
									</thead>
									<tbody>
									<?php
										$sql  = "SELECT * FROM club, profile, entrant_category, club_entry LEFT JOIN discount ";
										$sql .= "    ON discount.yearmonth = club_entry.yearmonth ";
										$sql .= "   AND discount.discount_code = 'CLUB' ";
										$sql .= "   AND discount.fee_code = club_entry.fee_code ";
										$sql .= "   AND discount.group_code = club_entry.group_code ";
										$sql .= "   AND discount.currency = club_entry.currency ";
										$sql .= " WHERE club_entry.yearmonth = '$admin_yearmonth' ";
										$sql .= "   AND club.club_id = club_entry.club_id ";
										$sql .= "   AND profile.profile_id = club_entry.club_entered_by ";
										$sql .= "   AND entrant_category.yearmonth = club_entry.yearmonth ";
										$sql .= "   AND entrant_category.entrant_category = club_entry.entrant_category ";
										$qclub_list = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										$row_no = 0;
										while ($club_list = mysqli_fetch_array($qclub_list)) {
											++ $row_no;
											$club_id = $club_list['club_id'];
											$grp_pay = true;
											// Compute Totals from Coupon if payment_mode is Individual Payment
											if ($club_list['payment_mode'] == 'SELF_PAYMENT') {
												$grp_pay = false;
												$sql  = "SELECT COUNT(*) AS num_paid, SUM(entry.fees_payable) AS total_fees, SUM(entry.discount_applicable) AS discount, ";
												$sql .= "       SUM(entry.payment_received) AS total_payment ";
												$sql .= "  FROM coupon, entry ";
												$sql .= " WHERE coupon.yearmonth = '$admin_yearmonth' ";
												$sql .= "   AND coupon.club_id = '$club_id' ";
												$sql .= "   AND entry.yearmonth = coupon.yearmonth ";
												$sql .= "   AND entry.profile_id = coupon.profile_id ";
												$qsums = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$sums = mysqli_fetch_array($qsums);
											}
											$sql = "SELECT * FROM coupon WHERE yearmonth = '$admin_yearmonth' AND club_id = '$club_id' ";
											$qcoupon = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											$num_coupons = mysqli_num_rows($qcoupon);
											$coupon = mysqli_fetch_array($qcoupon);		// for the first row
									?>
										<tr>
											<td rowspan="<?= ($num_coupons == 0) ? 1 : $num_coupons;?>">
												<input name="checkbox[]" type="checkbox" value="<?=$club_id;?>" class="row-selector">
												&nbsp;<?php printf("%4d", $row_no);?>
											</td>
											<td rowspan="<?= ($num_coupons == 0) ? 1 : $num_coupons;?>">
												<p><b><?=$club_list['club_name'];?></b></p>
												<table class="table table-condensed">
													<tr>
														<td>Entered By</td>
														<td><?=$club_list['profile_name'];?></td>
													</tr>
													<tr>
														<td>Email</td>
														<td><?=$club_list['email'];?></td>
													</tr>
													<tr>
														<td>Phone</td>
														<td><?=$club_list['phone'];?></td>
													</tr>
													<tr>
														<td>WhatsApp</td>
														<td><?=$club_list['whatsapp'];?></td>
													</tr>
													<tr>
														<td>Payment</td>
														<td><?php echo $club_list['payment_mode'] == "GROUP_PAYMENT" ? "Group Payment" : "Individual Payment";?></td>
													</tr>
													<tr>
														<td>Participation</td>
														<td><?=$club_list['entrant_category_name'];?></td>
													</tr>
													<tr>
														<td>Group Size</td>
														<td><?=$club_list['minimum_group_size'];?></td>
													</tr>
													<tr>
														<td>Club Discount</td>
														<td><?= ($admin_contest['fee_model'] == "POLICY") ? ($club_list['discount_percentage'] * 100) . "%" : "STANDARD";?></td>
													</tr>
													<tr>
														<td>Participation Codes</td>
														<td><?= implode(", ", explode("|", $club_list['participation_codes']));?></td>
													</tr>
													<tr>
														<td>Paid Participants</td>
														<td><?= $grp_pay ? $club_list['paid_participants'] : $sums['num_paid'];?></td>
													</tr>
													<tr>
														<td>Total Fees</td>
														<td><?= $grp_pay ? $club_list['total_fees'] : $sums['total_fees'];?></td>
													</tr>
													<tr>
														<td>Discount</td>
														<td><?= $grp_pay ? $club_list['total_discount'] : $sums['discount'];?></td>
													</tr>
													<tr>
														<td>Paid</td>
														<td><?= $grp_pay ? $club_list['total_payment_received'] : $sums['total_payment'];?></td>
													</tr>
												</table>
											</td>
											<td>
											<?php
												if ($coupon) {
													if ($coupon['profile_id'] == 0) {
														echo $coupon['email'];
													}
													else {
														$sql = "SELECT profile_name FROM profile WHERE profile_id = '" . $coupon['profile_id'] . "' ";
														$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
														$row = mysqli_fetch_array($query);
														echo $row['profile_name'];
													}
												}
												else
													echo "";
											?>
											</td>
											<td>
												<?= ($coupon) ? $coupon['digital_sections'] : "";?>
											</td>
											<td>
												<?= ($coupon) ? $coupon['print_sections'] : "";?>
											</td>
											<td>
												<?= ($coupon) ? $coupon['fees_payable'] : "";?>
											</td>
											<td>
												<?= ($coupon) ? $coupon['discount_applicable'] : "";?>
											</td>
											<td>
												<?= ($coupon) ? $coupon['payment_received'] : "";?>
											</td>
											<td>
											<?php
												if ($coupon) {
													$sql = "SELECT COUNT(*) AS num_uploads FROM pic WHERE yearmonth = '$admin_yearmonth' AND profile_id = '" . $coupon['profile_id'] . "' ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$row = mysqli_fetch_array($query);
													echo $row['num_uploads'];
												}
												else
													echo "";
											?>
											</td>
										</tr>
									<?php
											while ($coupon && $coupon = mysqli_fetch_array($qcoupon)) {
									?>
										<tr>
											<td>
											<?php
												if ($coupon['profile_id'] == 0) {
													echo $coupon['email'];
												}
												else {
													$sql = "SELECT profile_name FROM profile WHERE profile_id = '" . $coupon['profile_id'] . "' ";
													$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$row = mysqli_fetch_array($query);
													echo $row['profile_name'];
												}
											?>
											</td>
											<td>
												<?=$coupon['digital_sections'];?>
											</td>
											<td>
												<?=$coupon['print_sections'];?>
											</td>
											<td>
												<?=$coupon['fees_payable'];?>
											</td>
											<td>
												<?=$coupon['discount_applicable'];?>
											</td>
											<td>
												<?=$coupon['payment_received'];?>
											</td>
											<td>
											<?php
												$sql = "SELECT COUNT(*) AS num_uploads FROM pic WHERE yearmonth = '$admin_yearmonth' AND profile_id = '" . $coupon['profile_id'] . "' ";
												$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$row = mysqli_fetch_array($query);
												echo $row['num_uploads'];
											?>
											</td>
										</tr>
									<?php
											}
										}
									?>
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
			</div>				<!-- row -->
		</div> <!-- / header -->
		<!-- Footer -->
		<?php include_once("inc/profile_modal.php");?>
	</div>		<!-- / .wrapper -->
	<?php include("inc/footer.php");?>

	<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
	<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

	<script src="plugin/select2/js/select2.min.js"></script>

	<!-- Page specific scripts -->
	<!-- Initialize Tables -->
	<script>
	// Called when the Club and Requestor are selected
	function enable_inputs() {
		$("#entrant_category").prop("disabled", false);
		$("#fee_code").prop("disabled", false);
		$("#promised_group_size").prop("disabled", false);
		$("#payment_mode_self").prop("disabled", false);
		$("#payment_mode_group").prop("disabled", false);
		$("#group_code").prop("disabled", false);
		$("#discount_percentage").prop("disabled", false);
		$("#allow_standard_discounts").prop("disabled", false);
		$("#participation_codes").prop("disabled", false);
		$("#create_club_entry").prop("disabled", false);
	}

    $(document).ready(function(){
		// Dynamic Select settings for Club Names
		$("#club_id").select2({
			minimumInputLength : 1,
			ajax : {
				url : "ajax/get_club_data.php",
				dataType : "json",
				data : function (input) {
							return {
								source : 'club',
								yearmonth : '<?= $admin_yearmonth;?>',
								club_id : $("#club_id").val(),
								search : input.term,
							}
				},
			},
		});

		// Dynamic Select settings for Club Entered Profiles
		$("#profile_id").select2({
			ajax : {
				url : "ajax/get_club_data.php",
				dataType : "json",
				data : function (input) {
							return {
								source : 'profile',
								yearmonth : '<?= $admin_yearmonth;?>',
								club_id : $("#club_id").val(),
								profile_id : $("#profile_id").val(),
								search : input.term,
							}
				},
			},
		});

		// Dynamic Select settings for Standard Group Discount Codes
		$("#group_code").select2({
			ajax : {
				url : "ajax/get_club_data.php",
				dataType : "json",
				data : function (input) {
					return {
						source : 'group_code',
						yearmonth : '<?= $admin_yearmonth;?>',
						club_id : $("#club_id").val(),
						entrant_category : $("#entrant_category").val(),
						fee_code : $("#fee_code").val(),
						fee_model : '<?= $admin_contest['fee_model'];?>',
						search : input.term,
					}
				},
			},
		});

		// Dynamic Select settings for Participation Codes (Multiple Selection)
		$("#participation_codes").select2({
			multiple : true,
			ajax : {
				url : "ajax/get_club_data.php",
				dataType : "json",
				data : function (input) {
					return {
						source : 'participation_code',
						yearmonth : '<?= $admin_yearmonth;?>',
						entrant_category : $("#entrant_category").val(),
						fee_code : $("#fee_code").val(),
						search : input.term,
					}
				},
			},
		});

		// Enable Profile and other fields When Club ID has been selected
		$("#club_id").change(function() {
			//if ($("#club_id:selected").length > 0 && $("#club_id").val() != "" && $("#club_id").val() != 0) {
			if ($("#club_id option:selected").length > 0) {
				$("#profile_id").prop("disabled", false);
				//if ($("#profile_id:selected").length > 0 && $("#profile_id").val() != "" && $("#profile_id").val != 0)
				if ($("#profile_id option:selected").length > 0)
					enable_inputs();
			}
		});

		// Enable fields When Profile ID has been selected
		$("#profile_id").change(function() {
			//if ($("#profile_id:selected").length > 0 && $("#profile_id").val() != "" && $("#profile_id").val != 0 &&
			//	$("#club_id:selected").length > 0 && $("#club_id").val() != "" && $("#club_id").val() != 0)
			if ($("#profile_id option:selected").length > 0 && $("#club_id option:selected").length > 0)
				enable_inputs();
		});
	});
    </script>


</body>

</html>
<?php
}
else {
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}
?>

<?php
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

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "0",
			"registration_start_date" => NULL, "registration_last_date" => NULL,
	);
	$yearmonth = 0;

	// Load sections for the contest
	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		// Set up Salon
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
			$row = mysqli_fetch_array($query);
			foreach ($salon as $field => $value) {
				if (isset($row[$field]))
					$salon[$field] = $row[$field];
			}
		}

		// Load Sections
		$sql  = "SELECT * FROM entrant_category ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$ec_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$ec_list[$row['entrant_category']] = $row;
		}
		$salon["ec_list"] = $ec_list;

		// Build Country list
		$sql = "SELECT * FROM country ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$country_list = [];
		while ($row = mysqli_fetch_array($query))
			$country_list[$row['country_id']] = $row['country_name'];

		// Award Group
		$sql = "SELECT DISTINCT award_group FROM award WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$ag_list = [];
		while ($row = mysqli_fetch_array($query))
			$ag_list[] = $row['award_group'];

		// Fee Group
		$sql = "SELECT DISTINCT fee_group, currency FROM fee_structure WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$fg_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$fg_list[$row['fee_group']] = $row;

		// Discount Group
		$sql = "SELECT DISTINCT discount_group, currency FROM discount WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$dg_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$dg_list[] = $row;

		// participation_code
		$sql  = "SELECT participation_code, description, fee_group, currency FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND fee_code = 'REGULAR' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$pc_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
			$pc_list[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />

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
			<h1>    YPS SALON MANAGEMENT PANEL  </h1>
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
						MANAGE PARTICIPANT CATEGORIES - <?= $yearmonth == 0 ? "" : "FOR " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-ec-form" action="categories.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-8">
								<label for="yearmonth">Select Salon</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
									<?php
										$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
										$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($row = mysqli_fetch_array($query)) {
									?>
										<option value="<?= $row['yearmonth'];?>" <?= ($row['yearmonth'] == $yearmonth) ? "selected" : "";?>><?= $row['contest_name'];?></option>
									<?php
										}
									?>
									</select>
									<span class="input-group-btn">
										<button type="submit" class="btn btn-info pull-right" name="select-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">

			<h3 class="text-info">Category Add/Edit</h3>
			<form role="form" method="post" id="ec_details_form" name="ec_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="is_edit_ec" id="is_edit_ec" value="0">
				<input type="hidden" name="state_must_match" value="0">
				<input type="hidden" name="state_names" value="">

				<!-- Edited Fields -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="yearmonth">Salon ID</label>
						<input type="number" name="yearmonth" class="form-control" id="yearmonth" value="<?= $salon['yearmonth'];?>" readonly >
					</div>
					<div class="col-sm-8">
						<label for="contest_name">Contest Name</label>
						<input type="text" name="contest_name" class="form-control" id="contest_name" value="<?= $salon['contest_name'];?>" readonly >
					</div>
				</div>

				<!-- Collect Section Details to Add/Edit -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="entrant_category">Entrant Category Code (no spaces)</label>
						<input type="text" name="entrant_category" class="form-control text-uppercase" id="entrant_category" >
					</div>
					<div class="col-sm-8">
						<label for="entrant_category_name">Entrant Category Name</label>
						<input type="text" name="entrant_category_name" class="form-control" id="entrant_category_name" >
					</div>
				</div>

				<!-- YPS Membership Match -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label>Is YPS Membership Required ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="yps_membership_required" id="yps_membership_required" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Must be a YPS Member" >
						</div>
					</div>
					<div class="col-sm-6" id="div_yps_member_prefixes" style="display : none;">
						<label for="yps_member_prefixes">Membership Types to be included</label>
						<div class="row form-group">
							<div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon">
										<input type="checkbox" name="yps_member_prefixes[]" id="yps_member_prefix_lm" value="LM" >
									</span>
									<input type="text" class="form-control" readonly value="YPS Life Member" >
								</div>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon">
										<input type="checkbox" name="yps_member_prefixes[]" id="yps_member_prefix_im" value="IM" >
									</span>
									<input type="text" class="form-control" readonly value="YPS Individual Member" >
								</div>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon">
										<input type="checkbox" name="yps_member_prefixes[]" id="yps_member_prefix_ja" value="JA" >
									</span>
									<input type="text" class="form-control" readonly value="YPS Junior Associate" >
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Gender Match -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Restrict to specific Gender ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="gender_must_match" id="gender_must_match" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Gender must match" >
						</div>
					</div>
					<div class="col-sm-4" id="div_gender_match" style="display: none;">
						<label for="gender_match">Gender to Match</label>
						<select class="form-control" name="gender_match" id="gender_match" value="" >
							<option value="MALE">Male</option>
							<option value="FEMALE">Female</option>
							<option value="OTHER">Other</option>
						</select>
					</div>
				</div>

				<!-- Age within range -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Limit to Age range ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="age_within_range" id="age_within_range" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Age should be within range" >
						</div>
					</div>
					<div id="div_age_min_max" style="display: none;">
						<div class="col-sm-4">
							<label for="age_minimum">Minimum Age</label>
							<input type="number" name="age_minimum" class="form-control" id="age_minimum" value="8" >
						</div>
						<div class="col-sm-4">
							<label for="age_maximum">Maximum Age</label>
							<input type="number" name="age_maximum" class="form-control" id="age_maximum" value="120" >
						</div>
					</div>
				</div>

				<!-- Country Match -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Restrict to specific Countries ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="country_must_match" id="country_must_match" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Include/Exclude countries" >
						</div>
					</div>
					<div class="col-sm-4 div_country_selections" style="display: none;">
						<label for="country_codes">Include</label>
						<select class="form-control country-select" name="country_codes[]" id="country_codes" multiple="multiple" value="">
						<?php
							if (isset($country_list)) {
								foreach($country_list as $country_id => $country_name) {
						?>
							<option value="<?= $country_id;?>"><?= $country_name;?></option>
						<?php
								}
							}
						?>
						</select>
					</div>
					<div class="col-sm-4 div_country_selections" style="display: none;">
						<label for="country_excludes">Exclude</label>
						<select class="form-control country-select" name="country_excludes[]" id="country_excludes" multiple="multiple" value="">
						<?php
							if (isset($country_list)) {
								foreach($country_list as $country_id => $country_name) {
						?>
							<option value="<?= $country_id;?>"><?= $country_name;?></option>
						<?php
								}
							}
						?>
						</select>
					</div>
				</div>

				<!-- Switches -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Create Club</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="can_create_club" id="can_create_club" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Can Create Club/Group" >
						</div>
					</div>
					<div class="col-sm-4">
						<label>Report Acceptance</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="acceptance_reported" id="acceptance_reported" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Acceptance Reported" >
						</div>
					</div>
					<div class="col-sm-4">
						<label>Fee Payable</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="fee_waived" id="fee_waived" value="1" >
							</span>
							<input type="text" class="form-control" readonly value="Fee waived" >
						</div>
					</div>
				</div>

				<!-- Fees Related -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="currency">Currency</label>
						<select class="form-control" name="currency" id="currency" value="INR">
							<option value="INR" selected>Indian Rupees</option>
							<?php
								if (isset($salon['is_international']) && $salon['is_international'] == "1") {
							?>
							<option value="USD">US Dollars</option>
							<?php
								}
							?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="fee_group">Fee as per Fee Group</label>
						<select class="form-control" name="fee_group" id="fee_group" >
						<?php
							if (isset($ag_list)) {
								foreach($fg_list as $fg => $fr) {
									if ($fr['currency'] == 'INR') {
						?>
							<option value="<?= $fg;?>"><?= $fg;?></option>
						<?php
									}
								}
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="default_participation_code">Default Participation</label>
						<select class="form-control" name="default_participation_code" id="default_participation_code" value="" >
							<!-- to be dynamically generated -->
						</select>
					</div>
				</div>

				<!-- defaults -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="award_group">Award Group</label>
						<select class="form-control" name="award_group" id="award_groups" >
						<?php
							if (isset($ag_list)) {
								foreach($ag_list as $ar) {
						?>
							<option value="<?= $ar;?>"><?= $ar;?></option>
						<?php
								}
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="discount_group">Discount Group</label>
						<select class="form-control" name="discount_group" id="discount_group" >
						<?php
							if (isset($dg_list)) {
								foreach($dg_list as $dr) {
									if ($dr['currency'] == 'INR') { // default set in the form
						?>
							<option value="<?= $dr['discount_group'];?>"><?= $dr['discount_group'];?></option>
						<?php
									}
								}
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="default_digital_sections">Default Digital Sections</label>
						<input type="number" name="default_digital_sections" class="form-control" id="default_digital_sections" value="0" >
					</div>
					<div class="col-sm-3">
						<label for="default_print_sections">Default Print Sections</label>
						<input type="number" name="default_print_sections" class="form-control" id="default_print_sections" value="0" >
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_ec" name="update_ec">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>

			<!-- Section List -->
			<div id="section_list">
				<h3 class="text-info">List of Entrant Categories</h3>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>YPS Membership</th>
									<th>Gender Match</th>
									<th>Age Match</th>
									<th>Country Match</th>
									<th>Award Group</th>
									<th>Fee Group</th>
									<th>Discount Group</th>
									<th>EDIT</th>
									<th>DEL</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($ec_list)) {
									foreach ($ec_list as $entrant_category => $ec_data) {
										$include_country_list = "INCLUDE(NONE)";
										$exclude_country_list = "EXCLUDE(NONE)";
										if ($ec_data['country_must_match'] == '1') {
											// Include list
											if (! empty($ec_data['country_codes'])) {
												$sql  = "SELECT GROUP_CONCAT(country_name SEPARATOR ', ') AS country_list FROM country ";
												$sql .= " WHERE country_id IN (" . $ec_data['country_codes'] . ") ";
												$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$row = mysqli_fetch_array($query);
												$include_country_list = "INCLUDE (" . $row['country_list'] . ")";
											}
											// Exclude List
											if (! empty($ec_data['country_excludes'])) {
												$sql  = "SELECT GROUP_CONCAT(country_name SEPARATOR ', ') AS country_list FROM country ";
												$sql .= " WHERE country_id IN (" . $ec_data['country_excludes'] . ") ";
												$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												$row = mysqli_fetch_array($query);
												$exclude_country_list = "EXCLUDE (" . $row['country_list'] . ")";
											}
										}
							?>
								<tr id="<?= $entrant_category;?>-row">
									<td><?= $ec_data['entrant_category_name'];?></td>
									<td><?= ($ec_data['yps_membership_required'] == '1') ? "Must be " . $ec_data['yps_member_prefixes'] : "Not Reqd";?></td>
									<td><?= ($ec_data['gender_must_match'] == '1') ? "Must be " . $ec_data['gender_match'] : "Any";?></td>
									<td><?= ($ec_data['age_within_range'] == '1') ? "Between " . $ec_data['age_minimum'] . " and " . $ec_data['age_maximum'] : "Any age";?></td>
									<td><?= $include_country_list;?><br><?= $exclude_country_list;?></td>
									<td><?= $ec_data['award_group'];?></td>
									<td><?= ($ec_data['fee_waived'] == '0' ? $ec_data['fee_group'] : "FREE");?></td>
									<td><?= $ec_data['discount_group'];?></td>
									<td>
										<button class="btn btn-info ec-edit-button"
												data-category='<?= $entrant_category;?>'
												data-ec='<?= json_encode($ec_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-edit"></i>
										</button>
									</td>
									<td>
										<button class="btn btn-danger ec-delete-button"
												data-category='<?= $entrant_category;?>'
												data-ec='<?= json_encode($ec_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_ec_list">
									<th><?= isset($ec_list) ? sizeof($ec_list) : 0;?></th>
									<th>End of List</th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Select 2 for Country Codes -->
<script src="plugin/select2/js/select2.min.js"></script>

<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Initt select 2
		// $(".country-select").select2({theme: 'bootstrap'});

		// Other event handlers
		$("#yps_membership_required").click(function(){
			if ($("#yps_membership_required").prop("checked")) {
				$("#yps_member_prefix_im").prop("checked", true);
				$("#yps_member_prefix_ja").prop("checked", true);
				$("#yps_member_prefix_lm").prop("checked", true);
				$("#div_yps_member_prefixes").show();
			}
			else {
				$("#yps_member_prefix_im").prop("checked", false);
				$("#yps_member_prefix_ja").prop("checked", false);
				$("#yps_member_prefix_lm").prop("checked", false);
				$("#div_yps_member_prefixes").hide();
			}
		});

		$("#gender_must_match").click(function(){
			if ($("#gender_must_match").prop("checked"))
				$("#div_gender_match").show();
			else
				$("#div_gender_match").hide();
		});

		$("#age_within_range").click(function(){
			if ($("#age_within_range").prop("checked"))
				$("#div_age_min_max").show();
			else
				$("#div_age_min_max").hide();
		});

		$("#country_must_match").click(function(){
			if ($("#country_must_match").prop("checked")) {
				$(".div_country_selections").show();
				$(".country-select").select2({theme: 'bootstrap'});
			}
			else
				$(".div_country_selections").hide();
		});
	});
</script>

<!-- Custom Validation Functions -->
<script>
	jQuery.validator.addMethod(
		"yearmonth",
		function(value, element, param) {
			let year = value.substr(0, 4);
			let month = value.substr(4);
			if (year >= "1980" && year <= "2099" && month >= "01" && month <= "12")
				return true;
			else
				return this.optional(element);
		},
		"Must have valid value in YYYYMM format"
	);
	jQuery.validator.addMethod(
		"nospace",
		function(value, element, param) {
			let pattern = /^[a-zA-z_]+$/;
			if (pattern.test(value))
				return true;
			else
				return this.optional(element);
		},
		"Only alphabets and underscore are allowed"
	);
</script>

<!-- Add / Update section -->
<script>
	$(document).ready(function(){
		let vaidator = $('#ec_details_form').validate({
			rules:{
				entrant_category : {
					required : true,
					nospace : true,
				},
				entrant_category_name : { required : true, },
				age_minimum : { min : 8, },
				age_maximum : { max : 120, },
			},
			messages:{
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {
				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_ec.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Details Saved",
										text: "Participant Category details have been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								$("#" + $("#entrant_category").val() + "-row").remove();
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_ec_list");
								// Re-install handlers
								$(".ec-edit-button").click(function(){
									edit_ec(this);
								});
								$(".ec-delete-button").click(function(){
									delete_ec(this);
								});
								// Reset Form fields to default
								$("#is_edit_ec").val("0");
								$("#entrant_category").val("");
								$("#entrant_category").removeAttr("readonly");
								$("#entrant_category_name").val("");
								$("#yps_membership_required").attr("checked", false);
								$("#yps_member_prefix_im").attr("checked", false);
								$("#yps_member_prefix_ja").attr("checked", false);
								$("#yps_member_prefix_lm").attr("checked", false);
								$("#gender_must_match").attr("checked", false);
								$("#gender_match").val("");
								$("#age_within_range").attr("checked", false);
								$("#age_minimum").val("8");
								$("#age_maximum").val("120");
								$("#country_must_match").attr("checked", false);
								$("#country_codes").val("");
								$("#country_codes option:selected").each(function(){
									$(this).removeAttr("selected");
								})
								$("#country_excludes").val("");
								$("#country_excludes option:selected").each(function(){
									$(this).removeAttr("selected");
								})
								$("#currency").val("INR");
								$("#can_create_club").attr("checked", false);
								$("#fee_waived").attr("checked", false);
								$("#acceptance_reported").attr("checked", false);
								$("#award_group").val("");
								$("#fee_group").val("");
								$("#discount_group").val("");
								$("#default_participation_code").val("");
								$("#default_digital_sections").val("0");
								$("#default_print_sections").val("0");

								$(".country-select").select2({theme: 'bootstrap'});

								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Participant Category could not be saved: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
				return false;
			},
		});
	});
</script>

<!-- Handle Delete Section -->
<script>
	// Handle delete button request
	function delete_ec(button) {
		let entrant_category = $(button).attr("data-category");
		let ec_data = JSON.parse($(button).attr("data-ec"));
		let yearmonth = $("#yearmonth").val();
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the Participant Category " + entrant_category + " ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_ec) {
			if (delete_ec) {
				$('#loader_img').show();
				$.post("ajax/delete_ec.php", {yearmonth, entrant_category}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + entrant_category + "-row").remove();
						swal({
								title: "Removed",
								text: "Participant Category " + entrant_category + " has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove participant category: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

	// Register Click Handler
	$(document).ready(function(){
		$(".ec-delete-button").click(function(){
			delete_ec(this);
		});
	});
</script>

<!-- Handle Edit Section -->
<script>
	// Fee Group
	<?php
		if (isset($fg_list)) {
	?>
	var fg_list = JSON.parse('<?= json_encode($fg_list, JSON_FORCE_OBJECT);?>');
	function fg_options(currency) {
		let html = "";
		for (let fg in fg_list) {
			if (fg_list[fg].currency == currency)
				html += "<option value='" + fg_list[fg].fee_group + "'>" + fg_list[fg].fee_group + "</option> ";
		}
		return html;
	}
	<?php
		}
	?>

	// Discount Group
	<?php
		if (isset($dg_list)) {
	?>
	var dg_list = JSON.parse('<?= json_encode($dg_list);?>');
	function dg_options(currency) {
		let html = "";
		for (let i = 0; i < dg_list.length; ++i) {
			if (dg_list[i].currency == currency)
				html += "<option value='" + dg_list[i].discount_group + "'>" + dg_list[i].discount_group + "</option> ";
		}
		return html;
	}
	<?php
		}
	?>

	// Participation Code
	<?php
		if (isset($pc_list)) {
	?>
	var pc_list = JSON.parse('<?= json_encode($pc_list, JSON_FORCE_OBJECT);?>');
	function pc_options(currency, fee_group) {
		let html = "<option value=''>Any</option>";
		for (let pc in pc_list) {
			if (pc_list[pc].currency == currency && pc_list[pc].fee_group == fee_group)
				html += "<option value='" + pc_list[pc].participation_code + "'>" + pc_list[pc].description + "</option> ";
		}
		return html;
	}
	<?php
		}
	?>

	$(document).ready(function(){
		$("#currency").on("change", function(){
			$("#fee_group").html(fg_options($("#currency").val()));
			$("#discount_group").html(dg_options($("#currency").val()));
		});
		$("#fee_group").on("change", function(){
			$("#default_participation_code").html(pc_options($("#currency").val(), $("#fee_group").val()));
		});
	});

	function edit_ec(button) {
		let entrant_category = $(button).attr("data-category");
		let ec_data = JSON.parse($(button).attr("data-ec"));
		// Fill the form with Details
		$("#is_edit_ec").val("1");
		$("#entrant_category").val(ec_data.entrant_category);
		$("#entrant_category").attr("readonly", "readonly");
		$("#entrant_category_name").val(ec_data.entrant_category_name);
		$("#yps_membership_required").prop("checked", ec_data.yps_membership_required == "1");
		$("#yps_member_prefix_im").prop("checked", ec_data.yps_member_prefixes.includes("IM"));
		$("#yps_member_prefix_ja").prop("checked", ec_data.yps_member_prefixes.includes("JA"));
		$("#yps_member_prefix_lm").prop("checked", ec_data.yps_member_prefixes.includes("LM"));
		if ($("#yps_membership_required").prop("checked"))
			$("#div_yps_member_prefixes").show();
		else
			$("#div_yps_member_prefixes").hide();
		$("#gender_must_match").prop("checked", ec_data.gender_must_match == "1");
		$("#gender_match").val(ec_data.gender_match);
		if ($("#gender_must_match").prop("checked"))
			$("#div_gender_match").show();
		else
			$("#div_gender_match").hide();
		$("#age_within_range").prop("checked", ec_data.age_within_range == "1");
		$("#age_minimum").val(ec_data.age_minimum);
		$("#age_maximum").val(ec_data.age_maximum);
		if ($("#age_within_range").prop("checked"))
			$("#div_age_min_max").show();
		else
			$("#div_age_min_max").hide();
		$("#country_must_match").prop("checked", ec_data.country_must_match == "1");
		$("#country_codes").val(ec_data.country_codes.split(","));
		$("#country_excludes").val(ec_data.country_excludes.split(","));
		if ($("#country_must_match").prop("checked"))
			$(".div_country_selections").show();
		else
			$(".div_country_selections").hide();
		$("#currency").val(ec_data.currency);
		$("#can_create_club").prop("checked", ec_data.can_create_club == "1");
		$("#fee_waived").prop("checked", ec_data.fee_waived == "1");
		$("#acceptance_reported").prop("checked", ec_data.acceptance_reported == "1");
		$("#award_group").val(ec_data.award_group);
		$("#fee_group").val(ec_data.fee_group);
		$("#discount_group").val(ec_data.discount_group);
		$("#default_participation_code").val(ec_data.default_participation_code);
		$("#default_digital_sections").val(ec_data.default_digital_sections);
		$("#default_print_sections").val(ec_data.default_print_sections);

		$(".country-select").select2({theme: 'bootstrap'});

		$("#update_button").html("<i class='fa fa-edit'></i> Update");
		swal({
				title: "Edit",
				text: "Participant Category " + entrant_category + " details have been copied to the form. Edit the details and click on the Update button.",
				icon: "success",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Great'
		});
	}

	$(document).ready(function(){
		$(".ec-edit-button").click(function(){
			edit_ec(this);
		});
	});
</script>



</body>

</html>
<?php
}
else
{
	if (basename($_SERVER['HTTP_REFERER']) == THIS) {
		header("Location: manage_home.php");
		print("<script>location.href='manage_home.php'</script>");

	}
	else {
		header("Location: " . $_SERVER['HTTP_REFERER']);
		print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	}
}

?>

<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

function currency_accumulate(& $currency_list, $currency, $property, $value) {
	if (isset($currency_list[$currency][$property]))
		$currency_list[$currency][$property] += $value;
	else
		$currency_list[$currency][$property] = $value;
}

if (isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");


?>
<!DOCTYPE html>
<html>
<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Manage Dashboard</title>

	<?php include "inc/header.php"; ?>

	<style>
		table.table th, table.table th.right {
			text-align: right;
		}
		table.table th.left {
			text-align: left;
		}
		table.table th.center {
			text-align: center;
		}
		table.table td, table.table td.right {
			text-align : right;
		}
		table.table td.left {
			text-align : left;
		}
		table.table td.center {
			text-align : center;
		}
	</style>

</head>
<body class="fixed-navbar fixed-sidebar">

<!-- Simple splash screen-->
<div class="splash">
	<div class="color-line"></div>
	<div class="splash-title">
		<h1>   YPS SALON MANAGEMENT PANEL  </h1>
		<p>Please Wait.</p>
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
    <div class="content">
        <div class="row">
            <div class="col-lg-12 text-center m-t-md">
                <h2>
                    Management Panel for Salons
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
				<!-- Participants -->
				<div class="hpanel">
					<div class="panel-heading">
						<span class="lead">Contests</span>
						<a class="showhide"><i class="fa fa-chevron-up"></i></a>
					</div>
					<div class="panel-body">
						<div class="text text-centered" style="padding-top: 20px; padding-bottom: 20px;">
							<span style="text-align: center;"><big><big>Welcome to Salon Management Panel</big></big></span>
						</div>
						<h3 class="text-info">ACTIONS</h3>
						<div class="row form-group">
							<div class="col-sm-12">
								<table class="table" width="100%">
									<tbody>
										<tr>
											<th class="left">ACTION</th><th class="center">INPUTS</th>
										</tr>

										<!-- New Salon -->
										<tr>
											<td class="left lead">Create New Salon</td>
											<td class="right">
												<form role="form" method="post" id="create-salon-form" name="create-salon-form" action="edit_salon.php" enctype="multipart/form-data" >
													<button type="submit" class="btn btn-info" name="create-salon-action" ><i class="fa fa-star"></i> New</button>
												</form>
											</td>
										</tr>

										<!-- Clone Salon -->
										<tr>
											<td class="left lead">Clone New Salon</td>
											<td class="left">
												<form role="form" method="post" id="clone-salon-form" name="clone-salon-form" enctype="multipart/form-data" >
													<div class="row form-group">
														<div class="col-sm-12">
															<label for="yearmonth">Select Salon</label>
															<div class="input-group">
																<select class="form-control" name="yearmonth" id="clone-yearmonth" >
																<?php
																	$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
																	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($row = mysqli_fetch_array($query)) {
																?>
																	<option value="<?= $row['yearmonth'];?>"><?= $row['contest_name'];?></option>
																<?php
																	}
																?>
																</select>
																<span class="input-group-btn">
																	<a class="btn btn-info pull-right" name="clone-get-contest-button" id="clone-get-contest-button" > GET </a>
																</span>
															</div>
														</div>
													</div>
													<div class="row form-group">
														<div class="col-sm-4">
															<label for="yaermonth">Contest ID/Yearmonth (YYYYMM)</label>
															<input type="number" name="cln_yearmonth" id="cln_yearmonth" class="form-control" placeholder="YYYYMM..." >
														</div>
														<div class="col-sm-8">
															<label for="contest_name">Contest Name</label>
															<input type="text" name="cln_contest_name" id="cln_contest_name" class="form-control" placeholder="Contest name..." >
														</div>
													</div>
													<br><br>
													<div class="row form-group">
														<div class="col-sm-12">
															<button class="btn btn-info pull-right" name="clone-salon-action" ><i class="fa fa-clone"></i> Clone</button>
														</div>
													</div>
												</form>
											</td>
										</tr>

										<!-- Edit Salon -->
										<tr>
											<td class="left lead">Edit Salon</td>
											<td class="left">
												<form role="form" method="post" id="edit-salon-form" name="edit-salon-form" action="edit_salon.php" enctype="multipart/form-data" >
													<div class="row form-group">
														<div class="col-sm-12">
															<label for="yearmonth">Select Salon</label>
															<div class="input-group">
																<select class="form-control" name="yearmonth" >
																<?php
																	$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
																	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($row = mysqli_fetch_array($query)) {
																?>
																	<option value="<?= $row['yearmonth'];?>"><?= $row['contest_name'];?></option>
																<?php
																	}
																?>
																</select>
																<span class="input-group-btn">
																	<button type="submit" class="btn btn-info pull-right" name="edit-salon-action" ><i class="fa fa-edit"></i> Edit</button>
																</span>
															</div>
														</div>
														<br><br>
													</div>
												</form>
											</td>
										</tr>

										<!-- Delete Empty Salon -->
										<tr>
											<td class="left lead">Delete Empty Salon</td>
											<td class="left">
												<form role="form" method="post" id="del-salon-form" name="del-salon-form" enctype="multipart/form-data" >
													<div class="row form-group">
														<div class="col-sm-12">
															<label for="yearmonth">Select Salon</label>
															<div class="input-group">
																<select class="form-control" name="yearmonth" >
																<?php
																	$sql  = "SELECT yearmonth, contest_name ";
																	$sql .= "  FROM contest ";
																	$sql .= " WHERE yearmonth NOT IN ( ";
																	$sql .= "         SELECT DISTINCT yearmonth FROM entry ";
																	$sql .= "       ) ";
																	$sql .= "   AND yearmonth NOT IN ( ";
																	$sql .= "         SELECT DISTINCT yearmonth FROM ar_entry ";
																	$sql .= "       ) ";
																	$sql .= " ORDER BY yearmonth DESC ";
																	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($row = mysqli_fetch_array($query)) {
																?>
																	<option value="<?= $row['yearmonth'];?>"><?= $row['contest_name'];?></option>
																<?php
																	}
																?>
																</select>
																<span class="input-group-btn">
																	<button type="submit" class="btn btn-info pull-right" name="del-salon-action" ><i class="fa fa-trash"></i> Delete</button>
																</span>
															</div>
														</div>
														<br><br>
													</div>
												</form>
											</td>
										</tr>

										<!-- Edit Dates Salon -->
										<tr>
											<td class="left lead">Edit Salon Dates</td>
											<td class="left">
												<form role="form" method="post" id="dates-salon-form" name="dates-salon-form" enctype="multipart/form-data" >
													<div class="row form-group">
														<div class="col-sm-12">
															<label for="yearmonth">Select Salon</label>
															<div class="input-group">
																<select class="form-control" name="yearmonth" id="dates-yearmonth" >
																<?php
																	$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
																	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																	while ($row = mysqli_fetch_array($query)) {
																?>
																	<option value="<?= $row['yearmonth'];?>"><?= $row['contest_name'];?></option>
																<?php
																	}
																?>
																</select>
																<span class="input-group-btn">
																	<a class="btn btn-info" name="dates-get-contest-button" id="dates-get-contest-button" > GET </a>
																</span>
															</div>
														</div>
													</div>
													<!-- Timezone -->
													<br>
													<h5 class="text-info">Upload Dates</h5>
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="submission_timezone">Submission Timezone</label>
															<select class="form-control" name="submission_timezone" id="dates-submission-timezone" >
																<option value="Asia/Kolkata">India</option>
																<option value="America/Anchorage">Alaska</option>
																<option value="America/Los_Angeles">PST</option>
															</select>
															<input type="hidden" name="submission_timezone_name" id="dates-submission-timezone-name" >
														</div>
														<div class="col-sm-6">
														</div>
													</div>
													<!-- Registration Dates -->
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="registration_start_date">Registration Start Date</label>
															<input type="date" class="form-control" name="registration_start_date" id='dates-registration-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="registration_last_date">Registration Last Date</label>
															<input type="date" class="form-control" name="registration_last_date" id='dates-registration-last-date' placeholder="Last Date..." >
														</div>
													</div>
													<!-- Upload Last Dates -->
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="submission_last_date_digital">Digital Upload Last Date</label>
															<input type="date" class="form-control" name="submission_last_date_digital" id='dates-submission-last-date-digital' placeholder="Last Date..." >
														</div>
														<input type="hidden" name="has_print_sections" id="#dates-has-print-sections" value="0" >
														<div class="col-sm-6" id="dates-print-dates">
															<label for="registration_last_date">Print Upload Last Date</label>
															<input type="date" class="form-control" name="submission_last_date_print" id='dates-submission-last-date-print' placeholder="Start Date..." >
														</div>
													</div>
													<!-- Early Bird Fee Dates -->
													<br>
													<h5 class="text-info">Early Bird Fee Dates</h5>
													<input type="hidden" name="has_earlybird_dates" id="dates-has-earlybird-dates" value="0" >
													<div class="row form-group" id="dates-earlybird-dates">
														<div class="col-sm-6">
															<label for="earlybird_fee_start_date">Early Bird Fee Start Date</label>
															<input type="date" class="form-control" name="earlybird_fee_start_date" id='dates-earlybird-fee-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="earlybird_fee_end_date">Early Bird Fee Last Date</label>
															<input type="date" class="form-control" name="earlybird_fee_end_date" id='dates-earlybird-fee-end-date' placeholder="Last Date..." >
														</div>
														<div class="col-sm-6">
															<label for="earlybird_discount_start_date">Early Bird Discount Start Date</label>
															<input type="date" class="form-control" name="earlybird_discount_start_date" id='dates-earlybird-discount-start-date' readonly >
														</div>
														<div class="col-sm-6">
															<label for="earlybird_discount_end_date">Early Bird Discount Last Date</label>
															<input type="date" class="form-control" name="earlybird_discount_end_date" id='dates-earlybird-discount-end-date' readonly >
														</div>
													</div>
													<!-- Regular Fee Dates -->
													<br>
													<h5 class="text-info">Regular Fee Dates</h5>
													<input type="hidden" name="has_regular_dates" id="dates-has-regular-dates" value="0" >
													<div class="row form-group" id="dates-regular-dates">
														<div class="col-sm-6">
															<label for="regular_fee_start_date">Regular Fee Start Date</label>
															<input type="date" class="form-control" name="regular_fee_start_date" id='dates-regular-fee-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="regular_fee_end_date">Regular Bird Fee Last Date</label>
															<input type="date" class="form-control" name="regular_fee_end_date" id='dates-regular-fee-end-date' placeholder="Last Date..." >
														</div>
														<div class="col-sm-6">
															<label for="regular_discount_start_date">Regular Discount Start Date</label>
															<input type="date" class="form-control" name="regular_discount_start_date" id='dates-regular-discount-start-date' readonly >
														</div>
														<div class="col-sm-6">
															<label for="regular_discount_end_date">Regular Discount Last Date</label>
															<input type="date" class="form-control" name="regular_discount_end_date" id='dates-regular-discount-end-date' readonly >
														</div>
													</div>
													<!-- Judging Dates -->
													<br>
													<h5 class="text-info">Judging, Results, Updates</h5>
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="judging_start_date">Judging Start Date</label>
															<input type="date" class="form-control" name="judging_start_date" id='dates-judging-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="judging_end_date">Judging Last Date</label>
															<input type="date" class="form-control" name="judging_end_date" id='dates-judging-end-date' placeholder="Last Date..." >
														</div>
													</div>
													<!-- Results Date -->
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="results_date">Result Publish Date</label>
															<input type="date" class="form-control" name="results_date" id='dates-results-date' placeholder="Results Date..." >
														</div>
														<div class="col-sm-6">
														</div>
													</div>
													<!-- Data Update Dates -->
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="update_start_date">Update Start Date</label>
															<input type="date" class="form-control" name="update_start_date" id='dates-update-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="update_end_date">Update Last Date</label>
															<input type="date" class="form-control" name="update_end_date" id='dates-update-end-date' placeholder="Last Date..." >
														</div>
													</div>
													<!-- Exhibition Dates -->
													<br>
													<h5 class="text-info">Exhibition, Catalog</h5>
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="exhibition_start_date">Exhibition Start Date</label>
															<input type="date" class="form-control" name="exhibition_start_date" id='dates-exhibition-start-date' placeholder="Start Date..." >
														</div>
														<div class="col-sm-6">
															<label for="exhibition_end_date">Exhibition Last Date</label>
															<input type="date" class="form-control" name="exhibition_end_date" id='dates-exhibition-end-date' placeholder="Last Date..." >
														</div>
													</div>
													<!-- Catalog Dates -->
													<div class="row form-group">
														<div class="col-sm-6">
															<label for="catalog_release_date">Catalog Release Date</label>
															<input type="date" class="form-control" name="catalog_release_date" id='dates-catalog-release-date' placeholder="Release Date..." >
														</div>
														<div class="col-sm-6">
															<label for="catalog_order_last_date">Catalog Order Last Date</label>
															<input type="date" class="form-control" name="catalog_order_last_date" id='dates-catalog-order-last-date' placeholder="Last Date..." >
														</div>
													</div>
													<!-- Sponsorship Dates -->
													<br>
													<h5 class="text-info">Sponsorship</h5>
													<input type="hidden" name="has_sponsorship_dates" id="dates-has-sponsorship-dates" value="0">
													<div class="row form-group" id="dates-sponsorship-dates">
														<div class="col-sm-6">
															<label for="sponsorship_last_date">Sponsorship Last Date</label>
															<input type="date" class="form-control" name="sponsorship_last_date" id='dates-sponsorship-last-date' placeholder="Release Date..." >
														</div>
														<div class="col-sm-6">
														</div>
													</div>
													<br><br>
													<div class="row form-group">
														<div class="col-sm-12">
															<button type="submit" class="btn btn-info pull-right" name="dates-salon-action" ><i class="fa fa-save"></i> Update</button>
														</div>
													</div>
													<br><br>
												</form>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="panel-footer">
					</div>
				</div>

            </div>
        </div>
    </div>

</div>

<?php
include("inc/footer.php");
?>

<!-- Vendor scripts -->
<script src="plugin/peity/jquery.peity.min.js"></script>
<script src="plugin/swal/js/sweetalert.min.js"></script>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		$("#clone-get-contest-button").click(function(e){
			e.preventDefault();
			$("#cln_yearmonth").val($("#clone-yearmonth").val());
			$("#cln_contest_name").val($("#clone-yearmonth option:selected").text());
		});

		$("#dates-get-contest-button").click(function(e){
			// e.preventDefault();
			$('#loader_img').show();
			let yearmonth = $("#dates-yearmonth").val();
			$.post("ajax/get_salon_dates.php", {yearmonth}, function(response){
				$('#loader_img').hide();
				response = JSON.parse(response);
				if(response.success){
					$("#dates-submission-timezone").val(response.salon.submission_timezone);
					$("#dates-submission-timezone-name").val(response.salon.submission_timezone_name);
					$("#dates-registration-start-date").val(response.salon.registration_start_date);
					$("#dates-registration-last-date").val(response.salon.registration_last_date);

					if(response.salon.has_earlybird_fee) {
						$("#dates-has-earybird-dates").val(1);
						$("#dates-earlybird-fee-start-date").val(response.salon.earlybird_fee_start_date);
						$("#dates-earlybird-fee-end-date").val(response.salon.earlybird_fee_end_date);
						$("#dates-earlybird-discount-start-date").val(response.salon.earlybird_discount_start_date);
						$("#dates-earlybird-discount-end-date").val(response.salon.earlybird_discount_end_date);
						$("#dates-earlybird-dates").show();
					}
					else {
						$("#dates-has-earybird-dates").val(0);
						$("#dates-earlybird-fee-start-date").val(null);
						$("#dates-earlybird-fee-end-date").val(null);
						$("#dates-earlybird-discount-start-date").val(null);
						$("#dates-earlybird-discount-end-date").val(null);
						$("#dates-earlybird-dates").hide();
					}

					if(response.salon.has_regular_fee) {
						$("#dates-has-regular-dates").val(1);
						$("#dates-regular-fee-start-date").val(response.salon.regular_fee_start_date);
						$("#dates-regular-fee-end-date").val(response.salon.regular_fee_end_date);
						$("#dates-regular-discount-start-date").val(response.salon.regular_discount_start_date);
						$("#dates-regular-discount-end-date").val(response.salon.regular_discount_end_date);
						$("#dates-regular-dates").show();
					}
					else {
						$("#dates-has-regular-dates").val(0);
						$("#dates-regular-fee-start-date").val(null);
						$("#dates-regular-fee-end-date").val(null);
						$("#dates-regular-discount-start-date").val(null);
						$("#dates-regular-discount-end-date").val(null);
						$("#dates-regular-dates").hide();
					}

					$("#dates-submission-last-date-digital").val(response.salon.submission_last_date_digital);
					if(response.salon.has_print_sections) {
						$("#dates-has-print-sections").val(1);
						$("#dates-submission-last-date-print").val(response.salon.submission_last_date_print);
						$("#dates-print-dates").show();
					}
					else {
						$("#dates-has-print-sections").val(0);
						$("#dates-submission-last-date-print").val(null);
						$("#dates-print-dates").hide();
					}

					$("#dates-judging-start-date").val(response.salon.judging_start_date);
					$("#dates-judging-end-date").val(response.salon.judging_end_date);

					$("#dates-results-date").val(response.salon.results_date);
					$("#dates-update-start-date").val(response.salon.update_start_date);
					$("#dates-update-end-date").val(response.salon.update_end_date);

					$("#dates-exhibition-start-date").val(response.salon.exhibition_start_date);
					$("#dates-exhibition-end-date").val(response.salon.exhibition_end_date);

					$("#dates-catalog-release-date").val(response.salon.catalog_release_date);
					$("#dates-catalog-order-last-date").val(response.salon.catalog_order_last_date);

					if (response.salon.has_sponsorship) {
						$("#dates-has-sponsorship-dates").val(1);
						$("#dates-sponsorship-last-date").val(response.salon.sponsorship_last_date);
						$("#dates-sponsorship-dates").show();
					}
					else {
						$("#dates-has-sponsorship-dates").val(0);
						$("#dates-sponsorship-last-date").val(null);
						$("#dates-sponsorship-dates").hide();
					}

					$("#dates-salon-form").valid();		// Validate form
				}
				else{
					swal({
							title: "Load Failed",
							text: "Unable to load salon dates: " + response.msg,
							icon: "warning",
							confirmButtonClass: 'btn-warning',
							confirmButtonText: 'OK'
					});
				}
			});

			// Set Submission Timezone Name
			$("#dates-submission-timezone").on("change", function(){
				$("#dates-submission-timezone-name").val($("#dates-submission-timezone option:selected").text());
			});

			// Toggle Early Bird Dates
			$("#dates-has-earlybird-dates").on("change", function(){
				if ($("#dates-has-earlybird-dates").prop("checked"))
					$("#dates-earlybird-dates").show();
				else
					$("#dates-earlybird-dates").hide();
			});

			// Toggle Regular Dates
			$("#dates-has-regular-dates").on("change", function(){
				if ($("#dates-has-regular-dates").prop("checked"))
					$("#dates-regular-dates").show();
				else
					$("#dates-regular-dates").hide();
			});

			// Set Discount Dates to same value as associated fee dates
			$("#dates-earlybird-fee-start-date").on("change", function(){
				$("#dates-earlybird-discount-start-date").val($("#dates-earlybird-fee-start-date").val());
			});
			$("#dates-earlybird-fee-end-date").on("change", function(){
				$("#dates-earlybird-discount-end-date").val($("#dates-earlybird-fee-end-date").val());
			});
			$("#dates-regular-fee-start-date").on("change", function(){
				$("#dates-regular-discount-start-date").val($("#dates-regular-fee-start-date").val());
			});
			$("#dates-regular-fee-end-date").on("change", function(){
				$("#dates-regular-discount-end-date").val($("#dates-regular-fee-end-date").val());
			});

		});
	});
</script>

<!-- Update Handlers -->
<!-- Delete Salon -->
<script>
	$(document).ready(function(){
		let validator = $('#del-salon-form').validate({
			rules:{
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

				var formData = new FormData(form);

				// Send to Server
				$('#loader_img').show();
				$.ajax({
						url: "ajax/delete_empty_salon.php",
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
										title: "Deleted",
										text: "Empty Salon has been deleted.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Delete Failed",
										text: "Deletion of Empty Salon failed: " + response.msg,
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

<!-- Clone Salon -->
<script>
	$(document).ready(function(){
		let validator = $('#clone-salon-form').validate({
			rules:{
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

				var formData = new FormData(form);

				// Send to Server
				$('#loader_img').show();
				$.ajax({
						url: "ajax/clone_salon.php",
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
										title: "Salon Cloned",
										text: "A new salon has been created by cloning.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Clone Failed",
										text: "Cloning of Salon failed: " + response.msg,
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

<!-- Update Dates -->
<script>
	$(document).ready(function(){
		let validator = $('#dates-salon-form').validate({
			rules:{
				registration_start_date : {
					required : true,
					date_max : "#dates-registration-last-date",
				},
				registration_last_date : {
					required : true,
					date_min : "#dates-registration-start-date",
				},
				earlybird_fee_start_date : {
					required : true,
					date_max : "#dates-earlybird-fee-end-date",
				},
				earlybird_fee_end_date : {
					required : true,
					date_min : "#dates-earlybird-fee-start-date",
				},
				regular_fee_start_date : {
					required : true,
					date_max : "#dates-regular-fee-end-date",
				},
				regular_fee_end_date : {
					required : true,
					date_min : "#dates-regular-fee-start-date",
				},
				submission_last_date_digital : {
					required : true,
					date_max : "#dates-registration-last-date",
				},
				submission_last_date_print : {
					required : true,
					date_max : "#dates-registration-last-date",
				},
				judging_start_date : {
					required : true,
					date_min : "#dates-registration-last-date",
					date_max : "#dates-judging-end-date",
				},
				judging_end_date : {
					required : true,
					date_min : "#dates-judging-start-date",
				},
				results_date : {
					required : true,
					date_min : "#dates-judging-end-date",
				},
				update_start_date : {
					required : true,
					date_min : "#dates-results-date",
					date_max : "#dates-update-end-date",
				},
				update_end_date : {
					required : true,
					date_min : "#dates-update-start-date",
				},
				exhibition_start_date : {
					required : true,
					date_min : "#dates-update-end-date",
					date_max : "#dates-exhibition-end-date",
				},
				exhibition_end_date : {
					required : true,
					date_min : "#dates-exhibition-start-date",
				},
				catalog_release_date : {
					required : true,
					date_min : "#dates-exhibition-end-date",
					date_max : "#dates-catalog-order-last-date",
				},
				catalog_order_last_date : {
					required : true,
					date_min : "#dates-catalog-release-date",
				},
				sponsorship_last_date : {
					required : true,
					date_min : "#dates-registration-start-date",
				},
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

				var formData = new FormData(form);

				// Send to Server
				$('#loader_img').show();
				$.ajax({
						url: "ajax/update_salon_dates.php",
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
										text: "Salon dates have been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Save Failed",
										text: "Salon dates could not be saved: " + response.msg,
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


</body>

</html>

<?php
}
else
{
	$_SESSION['signin_msg'] = "Use ID with require permission not found !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>

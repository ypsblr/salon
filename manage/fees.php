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

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "0", "registration_start_date" => "", "registration_last_date" => "",
	);
	$yearmonth = 0;

	// Load details for the contest
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

		// Get last dates for Digital and print sections
		$sql  = "SELECT section_type, COUNT(*) AS number_of_sections, MAX(submission_last_date) AS last_date ";
		$sql .= "  FROM section ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " GROUP BY section_type ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if ($row['section_type'] == "P") {
				$salon['print_sections'] = $row['number_of_sections'];
				$salon["print_last_date"] = $row['last_date'];
			}
			else {
				$salon['digital_sections'] = $row['number_of_sections'];
				$salon["digital_last_date"] = $row['last_date'];
			}
		}

		// Do not Copy Rules
		// Set submission_last_date to registration_last_date of target salon
		if (isset($_REQUEST['clonefrom'])) {
			$clonefrom = $_REQUEST['clonefrom'];
			$yearmonth = $_REQUEST['yearmonth'];
			if ($salon['is_international'] == "1")
				$sql = "SELECT * FROM fee_structure WHERE yearmonth = '$clonefrom' ";
			else
				$sql = "SELECT * FROM fee_structure WHERE yearmonth = '$clonefrom' AND currency = 'INR' ";

			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				$fee_code = $row['fee_code'];
				$fee_group = $row['fee_group'];
				$participation_code = $row['participation_code'];
				$currency = $row['currency'];	// Cannot copy from previous year
				$description = $row['description'];		// Not used any more
				$digital_sections = $row['digital_sections'];		// Set to last date of registration of current salon
				$print_sections = $row['print_sections'];
				$fee_start_date = $salon['registration_start_date'];		// Not used for now
				if ($print_sections > 0)
					$fee_end_date = $salon['print_last_date'];		// Not used for now
				else
					$fee_end_date = $salon['digital_last_date'];
				$fees = $row['fees'];
				$exclusive = "1";

				// Insert data into current salon
				$sql  = "INSERT INTO fee_structure (yearmonth, fee_code, fee_group, participation_code, currency, description, ";
				$sql .= "             digital_sections, print_sections, fee_start_date, fee_end_date, fees, exclusive) ";
				$sql .= "VALUES ('$yearmonth', '$fee_code', '$fee_group', '$participation_code', '$currency', '$description', ";
				$sql .= "       '$digital_sections', '$print_sections', '$fee_start_date', '$fee_end_date', '$fees', '$exclusive' ) ";
				mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}

		// Load Fees
		$sql  = "SELECT * FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " ORDER BY currency, fee_code, fee_group, fees ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$fee_structure_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$fee_structure_list[] = $row;
		}
		$salon["fee_structure_list"] = $fee_structure_list;
	}

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

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
						MANAGE FEE STRUCTURE - <?= $yearmonth == 0 ? "" : "FOR " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-salon-form" action="fees.php" enctype="multipart/form-data" >
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
			<?php
				if ($yearmonth != 0 && sizeof($fee_structure_list) == 0) {
			?>
			<!-- Select a Salon to clone from if current list of Fee Structure is empty -->
			<h3 class="text-info">Copy Fee Structure from another Salon</h3>
			<form role="form" method="post" name="copy-fees-form" id="copy-fees-form" action="fees.php" enctype="multipart/form-data" >
				<input type="hidden" name="yearmonth" id="clone_yearmonth" value="<?= $yearmonth;?>" >
				<div class="row form-group">
					<div class="col-sm-8">
						<label for="yearmonth">Copy Fee Structure from selected Salon</label>
						<div class="input-group">
							<select class="form-control" name="clonefrom">
							<?php
								$sql  = "SELECT contest.yearmonth, contest_name, COUNT(*) AS num_fees ";
								$sql .= "  FROM contest, fee_structure ";
								$sql .= " WHERE fee_structure.yearmonth = contest.yearmonth ";
								$sql .= " GROUP BY contest.yearmonth ";
								$sql .= " ORDER BY yearmonth DESC ";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
								<option value="<?= $row['yearmonth'];?>" ><?= $row['contest_name'] . " (" . $row['num_fees'] . " fee definitions)";?></option>
							<?php
								}
							?>
							</select>
							<span class="input-group-btn">
								<button type="submit" class="btn btn-info pull-right" name="clone-fees-button" ><i class="fa fa-copy"></i> COPY </a>
							</span>
						</div>
					</div>
				</div>
			</form>
			<?php
				}
			?>

			<h3 class="text-info">Fees Add/Edit</h3>
			<form role="form" method="post" id="fees_details_form" name="fees_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="is_international" id="is_international" value="<?= $salon['is_international'];?>">
				<input type="hidden" name="registration_last_date" id="registration_last_date" value="<?= $salon['is_international'];?>">
				<input type="hidden" name="row_id" id="row_id" value="0">
				<input type="hidden" name="last_row_id" id="last_row_id" value="0">
				<input type="hidden" name="is_edit_fees" id="is_edit_fees" value="0">
				<input type="hidden" name="exclusive" id="exclusive" value="1">
				<!-- Key Values -->
				<input type="hidden" name="key_fee_code" id="key_fee_code" value="">
				<input type="hidden" name="key_fee_group" id="key_fee_group" value="">
				<input type="hidden" name="key_participation_code" id="key_participation_code" value="">
				<input type="hidden" name="key_currency" id="key_currency" value="">

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

				<!-- Collect Fee Details to Add/Edit -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="fee_code">Fee Code</label>
						<select class="form-control" name="fee_code" id="fee_code">
							<option value="EARLYBIRD">Early Bird</option>
							<option value="REGULAR">Regular</option>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="fee_group">Fee Group</label>
						<input type="text" name="fee_group" class="form-control text-uppercase" id="fee_group" >
					</div>
					<div class="col-sm-4">
						<label for="currency">Currency</label>
						<select class="form-control" name="currency" id="currency">
							<option value="INR">Indian Rupee</option>
							<?php
								if ($salon['is_international'] == "1") {
							?>
							<option value="USD">US Dollars</option>
							<?php
								}
							?>
						</select>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-3">
						<label for="participation_code">Participation Option</label>
						<input type="text" name="participation_code" class="form-control" id="participation_code" >
					</div>
					<div class="col-sm-3">
						<label for="description">Display Text</label>
						<input type="text" name="description" class="form-control" id="description" >
					</div>
					<div class="col-sm-3">
						<label for="digital_sections">Digital Sections</label>
						<input type="number" name="digital_sections" class="form-control" id="digital_sections" >
					</div>
					<div class="col-sm-3">
						<label for="print_sections">Print Sections</label>
						<input type="number" name="print_sections" class="form-control" id="print_sections" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-3">
						<label for="fees">Fees</label>
						<input type="number" name="fees" class="form-control" id="fees" >
					</div>
					<div class="col-sm-3">
						<label for="fee_start_date">From</label>
						<input type="date" name="fee_start_date" class="form-control" id="fee_start_date" >
					</div>
					<div class="col-sm-3">
						<label for="fee_end_date">To</label>
						<input type="date" name="fee_end_date" class="form-control" id="fee_end_date" >
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_fees" name="update_fees">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>

			<!-- Fee List -->
			<div id="fees_list">
				<h3 class="text-info">Fee Structure</h3>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>Fee Code</th>
									<th>Fee Group</th>
									<th>participation_code</th>
									<th>Digital</th>
									<th>Print</th>
									<th>Currency</th>
									<th>Fees</th>
									<th>From</th>
									<th>To</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($fee_structure_list)) {
									for ( $idx = 0; $idx < sizeof($fee_structure_list); ++ $idx) {
										$fee = $fee_structure_list[$idx];
							?>
								<tr id="<?= $idx;?>-row" class="fees-row">
									<td><?= $fee['fee_code'];?></td>
									<td><?= $fee['fee_group'];?></td>
									<td><?= $fee['participation_code'];?></td>
									<td><?= $fee['digital_sections'];?></td>
									<td><?= $fee['print_sections'];?></td>
									<td><?= $fee['currency'];?></td>
									<td><?= $fee['fees'];?></td>
									<td><?= $fee['fee_start_date'];?></td>
									<td><?= $fee['fee_end_date'];?></td>
									<td>
										<button class="btn btn-info fees-edit-button"
												data-row-id='<?= $idx;?>'
												data-fees='<?= json_encode($fee, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-edit"></i>
										</button>
									</td>
									<td>
										<button class="btn btn-danger fees-delete-button"
												data-row-id='<?= $idx;?>'
												data-fees='<?= json_encode($fee, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_fees_list" data-last-row-id="<?= isset($idx) ? $idx : 0;?>">
									<th><span id="number_of_rows"><?= isset($fee_structure_list) ? sizeof($fee_structure_list) : 0;?></span></th>
									<th>End of List</th>
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

<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();
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
</script>

<!-- Add / Update Fee -->
<script>
	$(document).ready(function(){
		let vaidator = $('#fees_details_form').validate({
			rules:{
				fee_code : { required : true, },
				fee_group : { required : true, },
				currency : { required : true, },
				participation_code : { required : true, },
				description : { required : true, },
				fees: {
					required : true,
					min : 1,
				},
				fee_start_date : { required : true, },
				fee_end_date : { required : true, },
				digital_sections : {
					min : 0,
					max : <?= isset($salon['digital_sections']) ? $salon['digital_sections'] : 0;?>,
				},
				print_sections : {
					min : 0,
					max : <?= isset($salon['print_sections']) ? $salon['print_sections'] : 0;?>,
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
				// Validation of dates
				if ($("#fee_end_date") > $("#registration_last_date")) {
					swal({
							title: "Invalid Date",
							text: "Fee last date cannot be more than " + $("#registration_last_date"),
							icon: "warning",
							confirmButtonClass: 'btn-warning',
							confirmButtonText: 'OK'
					});
					return false;
				}
				if ($("#print_sections").val() > 0 && $("#fee_end_date").val() > "<?= isset($salon['print_last_date']) ? $salon['print_last_date'] : '';?>") {
					swal({
							title: "Invalid Date",
							text: "Fee last date for print section cannot be more than <?= isset($salon['print_last_date']) ? $salon['print_last_date'] : '';?>",
							icon: "warning",
							confirmButtonClass: 'btn-warning',
							confirmButtonText: 'OK'
					});
					return false;
				}

				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_fees.php",
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
										text: "Fee data has been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								if ($("#is_edit_fees").val() == "1")
									$("#" + $("#row_id").val() + "-row").remove();
								$("#end_of_fees_list").attr("data-last-row-id", response.last_row_id);
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_fees_list");
								$("#number_of_rows").html($(".fees-row").length);
								// Re-install handlers
								$(".fees-edit-button").click(function(){
									edit_fees(this);
								});
								$(".fees-delete-button").click(function(){
									delete_fees(this);
								});
								// Reset Form fields to default
								$("#row_id").val("0");
								$("#last_row_id").val("0");
								$("#is_edit_fees").val("0");
								// Keys
								$("#key_fee_code").val("");
								$("#key_fee_group").val("");
								$("#key_participation_code").val("");
								$("#key_currency").val("INR");
								// Data
								$("#fee_code").val("");
								$("#fee_group").val("");
								$("#participation_code").val("");
								$("#currency").val("INR");
								$("#description").val("");
								$("#digital_sections").val("0");
								$("#print_sections").val("0");
								$("#fee_start_date").val("<?= $salon['registration_start_date'];?>");
								$("#fee_end_date").val("<?= $salon['registration_last_date'];?>");
								$("#fees").val("0");

								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Fees could not be saved: " + response.msg,
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

<!-- Handle Delete Fees -->
<script>
	// Handle delete button request
	function delete_fees(button) {
		let row_id = $(button).attr("data-row-id");
		let fee = JSON.parse($(button).attr("data-fees"));
		let yearmonth = $("#yearmonth").val();
		let fee_code = fee.fee_code;
		let fee_group = fee.fee_group;
		let participation_code = fee.participation_code;
		let currency = fee.currency;
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete this Fee ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_fees) {
			if (delete_fees) {
				$('#loader_img').show();
				$.post("ajax/delete_fees.php", {yearmonth, fee_code, fee_group, participation_code, currency}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + row_id + "-row").remove();
						swal({
								title: "Removed",
								text: "Fee has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove the fee: " + response.msg,
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
		$(".fees-delete-button").click(function(){
			delete_fees(this);
		});
	});
</script>

<!-- Handle Edit Fees -->
<script>
	function edit_fees(button) {
		let row_id = $(button).attr("data-row-id");
		let fee = JSON.parse($(button).attr("data-fees"));
		// Fill the form with Details
		$("#is_edit_fees").val("1");
		$("#row_id").val(row_id);
		$("#last_row_id").val($("#end_of_fees_list").attr("data-last-row-id"));
		// Keys
		$("#key_fee_code").val(fee.fee_code);
		$("#key_fee_group").val(fee.fee_group);
		$("#key_participation_code").val(fee.participation_code);
		$("#key_currency").val(fee.currency);
		// Data
		$("#fee_code").val(fee.fee_code);
		$("#fee_group").val(fee.fee_group);
		$("#participation_code").val(fee.participation_code);
		$("#currency").val(fee.currency);
		$("#description").val(fee.description);
		$("#digital_sections").val(fee.digital_sections);
		$("#print_sections").val(fee.print_sections);
		$("#fee_start_date").val(fee.fee_start_date);
		$("#fee_end_date").val(fee.fee_end_date);
		$("#fees").val(fee.fees);

		$("#update_button").html("<i class='fa fa-edit'></i> Update");
		swal({
				title: "Edit",
				text: "Details of fees have been copied to the form. Edit the details and click on the Update button.",
				icon: "success",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Great'
		});
	}

	$(document).ready(function(){
		$(".fees-edit-button").click(function(){
			edit_fees(this);
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

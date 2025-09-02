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

		// Get Participation Codes list
		$sql  = "SELECT DISTINCT participation_code, currency, description ";
		$sql .= "  FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " ORDER BY currency, (digital_sections + print_sections) DESC ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$participation_code_list = [];
		while ($row = mysqli_fetch_array($query)) {
			$participation_code_list[$row['currency']][] = ["participation_code" => $row['participation_code'], "description" => $row['description']];
		}
		$salon["participation_code_list"] = $participation_code_list;

		// Load Discount
		$sql  = "SELECT * FROM discount ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$discount_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$discount_list[] = $row;
		}
		$salon["discount_list"] = $discount_list;
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
						MANAGE DISCOUNT POLICIES - <?= $yearmonth == 0 ? "" : "FOR " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-salon-form" action="discount.php" enctype="multipart/form-data" >
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
			<h3 class="text-info">Discount Add/Edit</h3>
			<form role="form" method="post" id="discount_details_form" name="discount_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="registration_last_date" id="registration_last_date" value="<?= $salon['registration_last_date'];?>">
				<input type="hidden" name="row_id" id="row_id" value="0">
				<input type="hidden" name="last_row_id" id="last_row_id" value="0">
				<input type="hidden" name="is_edit_discount" id="is_edit_discount" value="0">
				<!-- Key Values -->
				<input type="hidden" name="key_discount_code" id="key_discount_code" value="">
				<input type="hidden" name="key_fee_code" id="key_fee_code" value="">
				<input type="hidden" name="key_discount_group" id="key_discount_group" value="">
				<input type="hidden" name="key_participation_code" id="key_participation_code" value="">
				<input type="hidden" name="key_currency" id="key_currency" value="">
				<input type="hidden" name="key_group_code" id="key_group_code" value="">

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
						<label for="discount_code">Discount for</label>
						<select class="form-control" name="discount_code" id="discount_code">
							<option value="CLUB">Club/Group Discount</option>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="fee_code">Fee Code</label>
						<select class="form-control" name="fee_code" id="fee_code">
							<option value="EARLYBIRD">Early Bird</option>
							<option value="REGULAR" selected>Regular</option>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="discount_group">Discount Group</label>
						<select class="form-control" name="discount_group" id="discount_group">
							<option value="GENERAL" selected>General</option>
							<option value="YPS">YPS Members</option>
						</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="currency">Currency</label>
						<select class="form-control" name="currency" id="currency">
							<option value="INR" selected>Indian Rupee</option>
							<?php
								if ($salon['is_international'] == "1") {
							?>
							<option value="USD">US Dollars</option>
							<?php
								}
							?>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="participation_code">Participation Code</label>
						<select class="form-control" name="participation_code" id="participation_code">
						<?php
							foreach ($participation_code_list['INR'] as $code) {
						?>
							<option value='<?= $code['participation_code'];?>'><?= $code['description'];?></option>
						<?php
							}
						?>
							<option value='POLICY' selected>Any Participation</option>
						</select>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="group_code">Group Code</label>
						<input type="text" name="group_code" class="form-control text-uppercase" id="group_code" value="GRP_10_PLUS" >
					</div>
					<div class="col-sm-4">
						<label for="minimum_group_size">Minimum Entrants</label>
						<input type="number" name="minimum_group_size" class="form-control" id="minimum_group_size" value="10" >
					</div>
					<div class="col-sm-4">
						<label for="maximum_group_size">Maximum Entrants</label>
						<input type="number" name="maximum_group_size" class="form-control" id="maximum_group_size" value="999" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="discount">Discount Amount</label>
						<input type="number" name="discount" class="form-control" id="discount" value="0.00" >
					</div>
					<div class="col-sm-4">
						<label for="discount_percentage">Discount Percentage</label>
						<input type="number" name="discount_percentage" class="form-control" id="discount_percentage" value="0" >
					</div>
					<div class="col-sm-4">
						<label for="discount_round_digits">Digits to round</label>
						<input type="number" name="discount_round_digits" class="form-control" id="discount_round_digits" value="0" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="discount_start_date">From</label>
						<input type="date" name="discount_start_date" class="form-control" id="discount_start_date" value='<?= $salon['registration_start_date'];?>'>
					</div>
					<div class="col-sm-3">
						<label for="discount_end_date">To</label>
						<input type="date" name="discount_end_date" class="form-control" id="discount_end_date" value='<?= $salon['registration_last_date'];?>'>
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_fees" name="update_discount">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>

			<!-- Fee List -->
			<div id="discount_list">
				<h3 class="text-info">Discount</h3>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>For</th>
									<th>Fee Code</th>
									<th>Disc Group</th>
									<th>Participation Code</th>
									<th>Currency</th>
									<th>Group ID</th>
									<th>Min sz</th>
									<th>Max sz</th>
									<th>Discount</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($discount_list)) {
									for ( $idx = 0; $idx < sizeof($discount_list); ++ $idx) {
										$discount = $discount_list[$idx];
							?>
								<tr id="<?= $idx;?>-row" class="discount-row">
									<td><?= $discount['discount_code'];?></td>
									<td><?= $discount['fee_code'];?></td>
									<td><?= $discount['discount_group'];?></td>
									<td><?= $discount['participation_code'];?></td>
									<td><?= $discount['currency'];?></td>
									<td><?= $discount['group_code'];?></td>
									<td><?= $discount['minimum_group_size'];?></td>
									<td><?= $discount['maximum_group_size'];?></td>
									<td><?= ($discount['discount'] > 0) ? $discount['discount'] : ($discount['discount_percentage'] * 100) . " %";?></td>
									<td>
										<button class="btn btn-info discount-edit-button"
												data-row-id='<?= $idx;?>'
												data-discount='<?= json_encode($discount, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-edit"></i>
										</button>
									</td>
									<td>
										<button class="btn btn-danger discount-delete-button"
												data-row-id='<?= $idx;?>'
												data-discount='<?= json_encode($discount, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_discount_list" data-last-row-id="<?= isset($idx) ? $idx : 0;?>">
									<th><span id="number_of_rows"><?= isset($discount_list) ? sizeof($discount_list) : 0;?></span></th>
									<th>End of List</th>
									<th></th>
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
		let vaidator = $('#discount_details_form').validate({
			rules:{
				discount_code : { required : true, },
				fee_code : { required : true, },
				discount_group : { required : true, },
				participation_code : { required : true, },
				currency : { required : true, },
				group_code : { required : true, },
				minimum_group_size : {
					required : true,
					min : 1,
				},
				maximum_group_size : {
					required : true,
					min : 1,
				},
				discount: {
					required : true,
					min : 0,
				},
				discount_percentage : {
					required : true,
					min : 0,
					max : 100,
				},
				discount_round_digits : {
					required : true,
					min : 0,
					max : 2,			// 2 digits is the maximum
				},
				discount_start_date : { required : true, },
				discount_end_date : {
					required : true,
					date_max : "#registration_last_date",
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
				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_discount.php",
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
										text: "Discount data has been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								if ($("#is_edit_discount").val() == "1")
									$("#" + $("#row_id").val() + "-row").remove();
								$("#end_of_discount_list").attr("data-last-row-id", response.last_row_id);
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_discount_list");
								$("#number_of_rows").html($(".discount-row").length);
								// Re-install handlers
								$(".discount-edit-button").click(function(){
									edit_discount(this);
								});
								$(".discount-delete-button").click(function(){
									delete_discount(this);
								});
								// Reset Form fields to default
								$("#row_id").val("0");
								$("#last_row_id").val("0");
								$("#is_edit_discount").val("0");
								// Keys
								$("#key_discount_code").val("");
								$("#key_fee_code").val("");
								$("#key_discount_group").val("");
								$("#key_participation_code").val("");
								$("#key_currency").val("INR");
								$("#group_code").val("");
								// Data
								$("#discount_code").val("");
								$("#fee_code").val("");
								$("#discount_group").val("");
								$("#participation_code").val("");
								$("#currency").val("INR");
								$("#group_code").val("");
								$("#minimum_group_size").val("0");
								$("#maximum_group_size").val("0");
								$("#discount_start_date").val("<?= $salon['registration_start_date'];?>");
								$("#discount_end_date").val("<?= $salon['registration_last_date'];?>");
								$("#discount").val("0");
								$("#discount_percentage").val("0");
								$("#discount_round_digits").val("0");

								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Discount could not be saved: " + response.msg,
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
	function delete_discount(button) {
		let row_id = $(button).attr("data-row-id");
		let discount = JSON.parse($(button).attr("data-discount"));
		let yearmonth = $("#yearmonth").val();
		let discount_code = discount.discount_code;
		let fee_code = discount.fee_code;
		let discount_group = discount.discount_group;
		let participation_code = discount.participation_code;
		let currency = discount.currency;
		let group_code = discount.group_code;
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete this Discount ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_fees) {
			if (delete_fees) {
				$('#loader_img').show();
				$.post("ajax/delete_discount.php", {yearmonth, discount_code, fee_code, discount_group, participation_code, currency, group_code}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + row_id + "-row").remove();
						swal({
								title: "Removed",
								text: "Discount has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove the discount : " + response.msg,
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
		$(".discount-delete-button").click(function(){
			delete_discount(this);
		});
	});
</script>

<!-- Handle Edit Fees -->
<script>
	// Global Variables
	<?php
		if (isset($salon['participation_code_list'])) {
	?>
	var participation_codes = JSON.parse('<?= json_encode($salon['participation_code_list'], JSON_FORCE_OBJECT);?>');

	function pc_options(currency, code) {
		html = "";
		if ( ! (participation_codes[currency] == undefined) ) {
			for (let pc in participation_codes[currency]) {
				let participation_code = participation_codes[currency][pc].participation_code;
				let description = participation_codes[currency][pc].description;
				html += "<option value='" + participation_code + "'" + (code == participation_code ? "selected" : "") + " >" + description + "</option>";
			}
			html += "<option value='POLICY'" + (code == 'POLICY' ? 'selected' : '') + " >Any Participation</option>";
			return html;
		}
		else {
			return "<option value=''>No Participation Codes Defined</option>";
		}
	}
	<?php
		}
	?>

	function edit_discount(button) {
		let row_id = $(button).attr("data-row-id");
		let discount = JSON.parse($(button).attr("data-discount"));
		// Fill the form with Details
		$("#is_edit_discount").val("1");
		$("#row_id").val(row_id);
		$("#last_row_id").val($("#end_of_discount_list").attr("data-last-row-id"));
		// Keys
		$("#key_discount_code").val(discount.discount_code);
		$("#key_fee_code").val(discount.fee_code);
		$("#key_discount_group").val(discount.discount_group);
		$("#key_participation_code").val(discount.participation_code);
		$("#key_currency").val(discount.currency);
		$("#key_group_code").val(discount.group_code);
		// Data
		$("#discount_code").val(discount.discount_code);
		$("#fee_code").val(discount.fee_code);
		$("#discount_group").val(discount.discount_group);
		$("#participation_code").val(discount.participation_code);
		$("#participation_code").html(pc_options(discount.currency, discount.participation_code));
		$("#currency").val(discount.currency);
		$("#group_code").val(discount.group_code);
		$("#minimum_group_size").val(discount.minimum_group_size);
		$("#maximum_group_size").val(discount.maximum_group_size);
		$("#discount_start_date").val(discount.discount_start_date);
		$("#discount_end_date").val(discount.discount_end_date);
		$("#discount").val(discount.discount);
		$("#discount_percentage").val(discount.discount_percentage * 100);
		$("#discount_round_digits").val(discount.discount_round_digits);

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
		$(".discount-edit-button").click(function(){
			edit_discount(this);
		});
		$("#currency").on("change", function(){
			$("#participation_code").html(pc_options($("#currency").val(), $("#participation_code").val()));
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

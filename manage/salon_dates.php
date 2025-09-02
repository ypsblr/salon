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
			"yearmonth" => "", "contest_name" => "",
			"registration_start_date" => NULL, "registration_last_date" => NULL, "submission_timezone" => "", "submission_timezone_name" => "",
			"judging_start_date" => NULL, "judging_end_date" => NULL, "results_date" => NULL,
			"update_start_date" => NULL, "update_end_date" => NULL,
			"exhibition_start_date" => NULL, "exhibition_end_date" => NULL,
			"catalog_release_date" => NULL, "catalog_order_last_date" => NULL,
			// 'has_earlybird_dates' => false, 'has_regular_dates' => false,
			// 'earlybird_fee_start_date' => NULL, 'earlybird_fee_end_date' => NULL,
			// 'regular_fee_start_date' => NULL, 'regular_fee_end_date' => NULL,
			// 'has_earlybird_discount' => false, 'has_regular_discount' => false,
			// 'earlybird_discount_start_date' => NULL, 'earlybird_discount_end_date' => NULL,
			// 'regular_discount_start_date' => NULL, 'regular_discount_end_date' => NULL,
			'has_print_sections' => false, 'has_digital_sections' => false,
			'submission_last_date_print' => NULL, 'submission_last_date_digital' => NULL,
			'has_sponsorship_dates' => false, 'sponsorship_last_date' => NULL,
	);
	$yearmonth = 0;

	// Clone Salon, if yearmonth passed
	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		// Contest
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


		// Submission Last Date
		$sql  = "SELECT section_type, MAX(submission_last_date) AS submission_last_date ";
		$sql .= "  FROM section ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " GROUP BY section_type ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if ($row['section_type'] == "P") {
				$salon['has_print_sections'] = true;
				$salon['submission_last_date_print'] = $row['submission_last_date'];
			}
			else {
				$salon['has_digital_sections'] = true;
				$salon['submission_last_date_digital'] = $row['submission_last_date'];
			}
		}

		// Sponsorship Dates
		$sql  = "SELECT MAX(sponsorship_last_date) AS sponsorship_last_date ";
		$sql .= "  FROM award ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND sponsored_awards > 0 ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query);
			if ($row['sponsorship_last_date'] != NULL) {
				$salon['sponsorship_last_date'] = $row['sponsorship_last_date'];
				$salon['has_sponsorship_dates'] = true;
			}
		}

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
						<?= $yearmonth == 0 ? "Select a Salon" : "Editing Details of " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="salon_dates.php" enctype="multipart/form-data" >
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
										<button type="submit" class="btn btn-info pull-right" name="edit-contest-button" id="edit-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<form role="form" method="post" id="edit_dates_form" name="edit_dates_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $salon['yearmonth'];?>">
				<input type="hidden" name="submission_timezone_name" id="submission_timezone_name" value="<?= $salon['submission_timezone_name'];?>" >
				<input type="hidden" name="has_digital_sections" id="has_digital_sections" value="<?= $salon['has_digital_sections'] ? '1' : '0';?>" >
				<input type="hidden" name="has_print_sections" id="has_print_sections" value="<?= $salon['has_print_sections'] ? '1' : '0';?>" >
				<input type="hidden" name="has_sponsorship_dates" id="has_sponsorship_dates" value="<?= $salon['has_sponsorship_dates'] ? '1' : '0';?>">

				<!-- Edited Fields -->
				<!-- Registration Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="registration_start_date">Registration Start Date</label>
						<input type="date" class="form-control" name="registration_start_date" id='registration_start_date' value="<?= $salon['registration_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="registration_last_date">Registration Last Date</label>
						<input type="date" class="form-control" name="registration_last_date" id='registration_last_date' value="<?= $salon['registration_last_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="submission_timezone">Submission Timezone</label>
						<select class="form-control" name="submission_timezone" id="submission_timezone" value="<?= $salon['submission_timezone'];?>"  >
							<option value="Asia/Kolkata">India</option>
							<option value="America/Anchorage">Alaska</option>
							<option value="America/Los_Angeles">PST</option>
						</select>
					</div>
				</div>
				<!-- Upload Last Dates -->
				<div class="row form-group">
					<?php
						if ($salon['has_digital_sections']) {
					?>
					<div class="col-sm-4">
						<label for="submission_last_date_digital">Digital Upload Last Date</label>
						<input type="date" class="form-control" name="submission_last_date_digital" id='submission_last_date_digital' value="<?= $salon['submission_last_date_digital'];?>" >
					</div>
					<?php
				}
						if ($salon['has_print_sections']) {
					?>
					<div class="col-sm-4">
						<label for="registration_last_date">Print Upload Last Date</label>
						<input type="date" class="form-control" name="submission_last_date_print" id='submission_last_date_print' value="<?= $salon['submission_last_date_print'];?>" >
					</div>
					<?php
						}
					?>
				</div>
				<!-- Judging Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="judging_start_date">Judging Start Date</label>
						<input type="date" class="form-control" name="judging_start_date" id='judging_start_date' value="<?= $salon['judging_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="judging_end_date">Judging Last Date</label>
						<input type="date" class="form-control" name="judging_end_date" id='judging_end_date' value="<?= $salon['judging_end_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="results_date">Result Publish Date</label>
						<input type="date" class="form-control" name="results_date" id='results_date' value="<?= $salon['results_date'];?>" >
					</div>
				</div>
				<!-- Data Update Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="update_start_date">Update Start Date</label>
						<input type="date" class="form-control" name="update_start_date" id='update_start_date' value="<?= $salon['update_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="update_end_date">Update Last Date</label>
						<input type="date" class="form-control" name="update_end_date" id='update_end_date' value="<?= $salon['update_end_date'];?>" >
					</div>
				</div>
				<!-- Exhibition Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="exhibition_start_date">Exhibition Start Date</label>
						<input type="date" class="form-control" name="exhibition_start_date" id='exhibition_start_date' value="<?= $salon['exhibition_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="exhibition_end_date">Exhibition Last Date</label>
						<input type="date" class="form-control" name="exhibition_end_date" id='exhibition_end_date' value="<?= $salon['exhibition_end_date'];?>" >
					</div>
				</div>
				<!-- Catalog Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="catalog_release_date">Catalog Release Date</label>
						<input type="date" class="form-control" name="catalog_release_date" id='catalog_release_date' value="<?= $salon['catalog_release_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="catalog_order_last_date">Catalog Order Last Date</label>
						<input type="date" class="form-control" name="catalog_order_last_date" id='catalog_order_last_date' value="<?= $salon['catalog_order_last_date'];?>" >
					</div>
				</div>
				<!-- Sponsorship Dates -->
				<?php
					if ($salon['has_sponsorship_dates']) {
				?>
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="sponsorship_last_date">Sponsorship Last Date</label>
						<input type="date" class="form-control" name="sponsorship_last_date" id='sponsorship_last_date' value="<?= $salon['sponsorship_last_date'];?>" >
					</div>
				</div>
				<?php
					}
				?>

				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-9">
						<input class="btn btn-primary pull-right" type="submit" id="edit-dates" name="edit-dates" value="Update">
					</div>
				</div>
			</form>
		</div>

		<!-- MODAL Forms -->
		<?php include("inc/blob_modal_html.php");?>
		<!-- END OF MODAL FORMS -->

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<?php include("inc/blob_modal_script.php");?>

<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		// Hide Form till a salon is loaded
		if($("#yearmonth").val() == 0)
			$(".content").hide();
		// Set submission_timezone_name based on selection
		$("#submission_timezone").change(function(){
			$("#submission_timezone_name").val($("#submission_timezone option:selected").text());
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

</script>

<!-- Update Dates -->
<script>
	$(document).ready(function(){
		let validator = $('#edit_dates_form').validate({
			rules:{
				registration_start_date : {
					required : true,
					date_max : "#registration_last_date",
				},
				registration_last_date : {
					required : true,
					date_min : "#registration_start_date",
				},
				earlybird_fee_start_date : {
					required : true,
					date_min : "#registration_start_date",
					date_max : "#earlybird_fee_end_date",
				},
				earlybird_fee_end_date : {
					required : true,
					date_min : "#earlybird_fee_start_date",
				},
				regular_fee_start_date : {
					required : true,
					date_min : "#registration_start_date",
					date_max : "#regular_fee_end_date",
				},
				regular_fee_end_date : {
					required : true,
					date_min : "#regular_fee_start_date",
				},
				submission_last_date_digital : {
					required : true,
					date_max : "#registration_last_date",
				},
				submission_last_date_print : {
					required : function () { return $("#has_print_sections").val() != 0;},
					date_max : "#registration_last_date",
				},
				judging_start_date : {
					required : true,
					date_min : "#registration_last_date",
					date_max : "#judging_end_date",
				},
				judging_end_date : {
					required : true,
					date_min : "#judging_start_date",
				},
				results_date : {
					required : true,
					date_min : "#judging_end_date",
				},
				update_start_date : {
					required : true,
					date_min : "#results_date",
					date_max : "#update_end_date",
				},
				update_end_date : {
					required : true,
					date_min : "#update_start_date",
				},
				exhibition_start_date : {
					required : true,
					date_min : "#update_end_date",
					date_max : "#exhibition_end_date",
				},
				exhibition_end_date : {
					required : true,
					date_min : "#exhibition_start_date",
				},
				catalog_release_date : {
					required : true,
					date_min : "#exhibition_end_date",
					date_max : "#catalog_order_last_date",
				},
				catalog_order_last_date : {
					required : true,
					date_min : "#catalog_release_date",
				},
				sponsorship_last_date : {
					required : true,
					date_min : "#registration_start_date",
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

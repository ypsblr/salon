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
			"is_salon" => "0", "is_international" => "0", "is_no_to_past_acceptance" => "0",
			"registration_start_date" => NULL, "registration_last_date" => NULL, "submission_timezone" => "", "submission_timezone_name" => "",
			"judging_start_date" => NULL, "judging_end_date" => NULL, "results_date" => NULL,
			"has_exhibition" => "0", "exhibition_start_date" => NULL, "exhibition_end_date" => NULL,
			"max_pics_per_entry" => "0", "max_width" => "1920", "max_height" => "1080", "max_file_size_in_mb" => "4",
	);
	$yearmonth = 0;

	// Clone Salon, if yearmonth passed
	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
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
						NEW SALON - <?= $yearmonth == 0 ? "" : "CLONED FROM " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="clone-salon-form" name="clone-salon-form" action="new_salon.php" enctype="multipart/form-data" >
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
										<button type="submit" class="btn btn-info pull-right" name="clone-contest-button" id="clone-contest-button" ><i class="fa fa-copy"></i> CLONE </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<form role="form" method="post" id="new_contest_form" name="new_contest_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="submission_timezone_name" id="submission_timezone_name" value="<?= $salon['submission_timezone_name'];?>" >
				<input type="hidden" name="max_pics_per_entry" value="<?= $salon['max_pics_per_entry'];?>" >

				<!-- Edited Fields -->
				<!-- Contest Name -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="yearmonth">Salon ID (yyyymm format of month)</label>
						<input type="number" name="yearmonth" class="form-control" id="yearmonth" value="<?= $salon['yearmonth'];?>" >
					</div>
					<div class="col-sm-8">
						<label for="contest_name">Contest Name</label>
						<input type="text" name="contest_name" class="form-control" id="contest_name" value="<?= $salon['contest_name'];?>" >
					</div>
				</div>

				<!-- Some switches -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Is this a Salon ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="is_salon" id="is_salon" value="1" <?= $salon['is_salon'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="This is a Salon" >
						</div>
					</div>
					<div class="col-sm-4">
						<label>Is this International Salon ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="is_international" id="is_international" value="1" <?= $salon['is_international'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="International Salon" >
						</div>
					</div>
					<div class="col-sm-4">
						<label>Are images accepted in the past not allowed?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="is_no_to_past_acceptance" id="is_no_to_past_acceptance" value="1" <?= $salon['is_no_to_past_acceptance'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="No past acceptances" >
						</div>
					</div>
				</div>

				<!-- Registration date -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="registration_start_date">Registration Start</label>
						<input type="date" name="registration_start_date" class="form-control" id="registration_start_date" value="<?= $salon['registration_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="registration_last_date">Registration End</label>
						<input type="date" name="registration_last_date" class="form-control" id="registration_last_date" value="<?= $salon['registration_last_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="submission_timezone">Timezone</label>
						<select class="form-control" name="submission_timezone" id="submission_timezone" value="<?= $salon['submission_timezone'];?>" >
							<option value="Asia/Kolkata">India</option>
							<option value="America/Anchorage">Alaska</option>
							<option value="America/Los_Angeles">PST</option>
						</select>
					</div>
				</div>

				<!-- Picture dimensions etc. -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="max_width">Max Picture Width (px)</label>
						<input type="number" name="max_width" class="form-control" id="max_width" value="<?= $salon['max_width'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="max_height">Max Picture Height (px)</label>
						<input type="number" name="max_height" class="form-control" id="max_height" value="<?= $salon['max_height'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="max_file_size_in_mb">Max File Size (MB)</label>
						<input type="number" name="max_file_size_in_mb" class="form-control" id="max_file_size_in_mb" value="<?= $salon['max_file_size_in_mb'];?>" >
					</div>
				</div>

				<!-- Judging & Results dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="judging_start_date">Judging Start</label>
						<input type="date" name="judging_start_date" class="form-control" id="judging_start_date" value="<?= $salon['judging_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="judging_end_date">Judging End</label>
						<input type="date" name="judging_end_date" class="form-control" id="judging_end_date" value="<?= $salon['judging_end_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="results_date">Results Date</label>
						<input type="date" name="results_date" class="form-control" id="results_date" value="<?= $salon['results_date'];?>" >
					</div>
				</div>

				<!-- Exhibition Dates -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Is there an Exhibition?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="has_exhibition" id="has_exhibition" value="1" <?= $salon['has_exhibition'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="Has Exhibition" >
						</div>
					</div>
					<div class="col-sm-4">
						<label for="exhibition_start_date">Exhibition Opens</label>
						<input type="date" name="exhibition_start_date" class="form-control" id="exhibition_start_date" value="<?= $salon['exhibition_start_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="exhibition_end_date">Exhibition Closes</label>
						<input type="date" name="exhibition_end_date" class="form-control" id="exhibition_end_date" value="<?= $salon['exhibition_end_date'];?>" >
					</div>
				</div>

				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-9">
						<input class="btn btn-primary pull-right" type="submit" id="create-salon" name="create-salon" value="Create">
					</div>
				</div>
			</form>
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

<!-- Edit Contest -->
<script>
	$(document).ready(function(){
		let vaidator = $('#new_contest_form').validate({
			rules:{
				yearmonth : {
					minlength : 6,
					maxlength : 6,
					required : true,
					yearmonth : true,
				},
				contest_name : { required : true, },
				registration_start_date : { required : true, },
				registration_last_date : {
					required : true,
					date_min : "#registration_start_date",
				},
				submission_timezone : { required : true, },
				max_width : {
					required : true,
					range : [1080, 1920],
				},
				max_height : {
					required : true,
					range: [ 640, 1200],
				},
				max_file_size_in_mb : {
					required : true,
					range : [ 1, 8],
				},
				judging_start_date : {
					required : true,
					date_min : "#registration_last_date",
				},
				judging_end_date : {
					required : true,
					date_min : "#judging_start_date",
				},
				results_date : {
					required : true,
					date_min : "#judging_end_date",
				},
				exhibition_start_date : {
					required : "#has_exhibition:checked",
				},
				exhibition_end_date : {
					required : "#has_exhibition:checked",
					date_min : "#exhibition_start_date",
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
						url: "ajax/create_salon.php",
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
										text: "Contest has been created successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								$("#edit-contest-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Contest could not be created: " + response.msg,
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

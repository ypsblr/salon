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
			"contest_description_blob" => "contest_description.htm", "terms_conditions_blob" => "terms_conditions.htm",
			"contest_announcement_blob" => "contest_announcement.htm", "fee_structure_blob" => "", "discount_structure_blob" => "",
			"judging_description_blob" => "judging_description.htm", "judging_report_blob" => "judging_report.htm",
			"results_description_blob" => "results_description.htm",
			"exhibition_description_blob" => "", "exhibition_description.htm",
			"exhibition_report_blob" => "exhibition_report.htm",
			"chairman_message_blob" => "chairman_message.htm",
			"section_rules" => [],
	);
	$yearmonth = 0;

	// Load Salon, if yearmonth passed
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

		// Section Rules
		$sql = "SELECT section, stub, rules, rules_blob FROM section WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$section_rules = [];
		while ($row = mysqli_fetch_array($query)) {
			$section_rules[$row['stub']] = array("section" => $row['section'], "text" => $row['rules'], "blob" => $row['rules_blob']);
		}
		$salon['section_rules'] = $section_rules;
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
						EDIT SALON - <?= $yearmonth == 0 ? "Select Salon" : $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="clone-salon-form" name="clone-salon-form" action="salon_blobs.php" enctype="multipart/form-data" >
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
										<button type="submit" class="btn btn-info pull-right" name="edit-blobs-button" id="edit-blobs-button" ><i class="fa fa-edit"></i> EDIT </button>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<form role="form" method="post" id="edit_blobs_form" name="edit_blobs_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $salon['yearmonth'];?>" >

				<!-- Edited Fields -->
				<!-- Salon Blobs -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="contest_description_blob">Salon Description</label>
						<div class="input-group">
							<input type="text" class="form-control" name="contest_description_blob" readonly value="<?= $salon['contest_description_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="contest_description"
									data-blob="<?= $salon['contest_description_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="terms_conditions_blob">Salon Rules</label>
						<div class="input-group">
							<input type="text" class="form-control" name="terms_conditions_blob" readonly value="<?= $salon['terms_conditions_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="terms_conditions"
									data-blob="<?= $salon['terms_conditions_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="terms_conditions_blob">Salon Announcement</label>
						<div class="input-group">
							<input type="text" class="form-control" name="contest_announcement_blob" readonly value="<?= $salon['contest_announcement_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="contest_announcement"
									data-blob="<?= $salon['contest_announcement_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Judging -->
				<!-- Judging Event -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="judging_description_blob">Judging Description</label>
						<div class="input-group">
							<input type="text" class="form-control" name="judging_description_blob" readonly value="<?= $salon['judging_description_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="judging_description"
									data-blob="<?= $salon['judging_description_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-6">
						<label for="judging_report_blob">Judging Report</label>
						<div class="input-group">
							<input type="text" class="form-control" name="judging_report_blob" readonly value="<?= $salon['judging_report_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="judging_report"
									data-blob="<?= $salon['judging_report_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Results -->
				<!-- Results Blobs -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="results_description_blob">Results Description</label>
						<div class="input-group">
							<input type="text" class="form-control" name="results_description_blob" readonly value="<?= $salon['results_description_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="results_description"
									data-blob="<?= $salon['results_description_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-6">
						<label for="chairman_message_blob">Chairman&apos;s Message</label>
						<div class="input-group">
							<input type="text" class="form-control" name="chairman_message_blob" readonly value="<?= $salon['chairman_message_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="chairman_message"
									data-blob="<?= $salon['chairman_message_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- EXHIBITION -->
				<!-- Description -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="exhibition_description_blob">Exhibition Details</label>
						<div class="input-group">
							<input type="text" class="form-control" name="exhibition_description_blob" readonly value="<?= $salon['exhibition_description_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="exhibition_description"
									data-blob="<?= $salon['exhibition_description_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-6">
						<label for="exhibition_report_blob">Exhibition Report</label>
						<div class="input-group">
							<input type="text" class="form-control" name="exhibition_report_blob" readonly value="<?= $salon['exhibition_report_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="exhibition_report"
									data-blob="<?= $salon['exhibition_report_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Section Rules -->
				<?php
					if (sizeof($salon['section_rules']) > 0) {
				?>
				<div class="row form-group">
				<?php
						foreach ($salon['section_rules'] as $stub => $rules) {
				?>
					<div class="col-sm-6">
						<label>Rules for <?= $rules['section'];?></label>
					<?php
							if ($rules['text'] != "") {
					?>
						<div class="input-group">
							<textarea name="<?= $stub . '-rules';?>" id="<?= $stub . '-rules';?>" class="form-control"><?= $rules['text'];?></textarea>
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="textarea"
									data-blob="<?= "section|" . $rules['section'] . "|" . $stub . "-rules";?>"><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					<?php
							}
							else {
					?>
						<div class="input-group">
							<input type="text" class="form-control" name="rules_blob" readonly value="<?= $rules['blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="section_rules"
									data-blob="<?= $rules['blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					<?php
							}
					?>
					</div>
				<?php
						}
				?>
				</div>
				<?php
					}
				?>
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

<script>
	$(document).ready(function(){
		if($("#yearmonth").val() == 0)
			$(".content").hide();
	});
</script>

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
		let vaidator = $('#edit_contest_form').validate({
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
					range : [ 640, 1200],
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
				update_start_date : {
					required : true,
					date_min : "#results_date",
				},
				update_end_date : {
					required : true,
					date_min : "#update_start_date",
				},
				exhibition_start_date : {
					required : "#has_exhibition:checked",
					date_min : "#update_end_date",
				},
				exhibition_end_date : {
					required : "#has_exhibition:checked",
					date_min : "#exhibition_start_date",
				},
				exhibition_name : { required : "#has_exhibition:checked", },
				catalog_release_date : {
					required : "#has_catalog:checked",
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
						url: "ajax/contest_update.php",
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
										text: "Contest details have been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								$("#edit-contest-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Contest details could not be saved: " + response.msg,
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

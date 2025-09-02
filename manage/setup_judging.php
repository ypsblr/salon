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
	$salon = array (
			"yearmonth" => "", "contest_name" => "",
			"registration_last_date" => NULL,
			"judging_start_date" => NULL, "judging_end_date" => NULL, "results_date" => NULL,
			"has_judging_event" => "0", "judging_mode" => "VENUE", "judging_description_blob" => "judging_description.htm",
			"judging_venue" => "", "judging_venue_address" => "", "judging_venue_location_map" => "",
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
						<?= $yearmonth == 0 ? "Select a Salon" : "Judging Details for " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="setup_judging.php" enctype="multipart/form-data" >
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
			<form role="form" method="post" id="edit_contest_form" name="edit_contest_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="registration_last_date" id="registration_last_date" value="<?= $salon['registration_last_date'];?>" >

				<!-- Edited Fields -->
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

				<!-- Salon Blobs -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="contest_description_blob">Judging Description</label>
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
				</div>

				<!-- Some switches -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label>Is there a judging_event ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="has_judging_event" id="has_judging_event" value="1" <?= $salon['has_judging_event'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="There is a Judging Event" >
						</div>
					</div>
					<div class="col-sm-4">
						<label for="judging_mode">Type of Judging</label>
						<select class="form-control" name="judging_mode" id="judging_mode" value="<?= $salon['judging_mode'];?>" >
							<option value="VENUE">Offline Judging in Hall</option>
							<option value="REMOTE">Remote Online Judging</option>
						</select>
					</div>
				</div>

				<!-- Judging Venue -->
				<div class="row form-group">
					<div class="col-sm-8">
						<label for="judging_venue">Judging Venue (for Judging in Hall)</label>
						<input type="text" name="judging_venue" class="form-control" id="judging_venue" value="<?= $salon['judging_venue'];?>" >
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-12">
						<label for="judging_venue_address">Judging Venue Address</label>
						<input type="text" name="judging_venue_address" class="form-control" id="judging_venue_address" value="<?= $salon['judging_venue_address'];?>" >
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-12">
						<label for="judging_venue">Google Map link for Judging Venue</label>
						<input type="text" name="judging_venue_location_map" class="form-control" id="judging_venue_location_map" value="<?= $salon['judging_venue_location_map'];?>" >
					</div>
				</div>

				<!-- Judging Schedule by Section


				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-9">
						<input class="btn btn-primary pull-right" type="submit" id="edit-judging" name="edit-judging" value="Update">
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
	// Global variables to save judging venue details
	let judging_venue = "<?= $salon['judging_venue'];?>";
	let judging_venue_address = "<?= $salon['judging_venue_address'];?>";
	let judging_venue_location_map = "<?= $salon['judging_venue_location_map'];?>";

	$(document).ready(function(){
		// Hide Form till a salon is loaded
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Set Judging Venue details based on judging_mode
		$("#judging_mode").change(function(){
			if ($("#judging_mode").val() == "REMOTE") {
				// save existing values
				judging_venue = $("#judging_venue").val();
				judging_venue_address = $("#judging_venue_address").val();
				judging_venue_location_map = $("#judging_venue_location_map").val();
				$("#judging_venue").val("Online remote judging");
				$("#judging_venue_address").val("Will be webcast live through Youtube and Facebook");
				$("#judging_venue_location_map").val("");
			}
			else {
				// Restore saved values
				$("#judging_venue").val(judging_venue);
				$("#judging_venue_address").val(judging_venue_address);
				$("#judging_venue_location_map").val(judging_venue_location_map);
			}
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
				judging_venue : {
					required : function() { return $("#judging_mode").val() == "VENUE"; },
				},
				judging_venue_address : {
					required : function() { return $("#judging_mode").val() == "VENUE"; },
				},
				judging_venue_location_map : {
					required : function() { return $("#judging_mode").val() == "VENUE"; },
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
						url: "ajax/update_judging_details.php",
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

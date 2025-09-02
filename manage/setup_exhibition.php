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
			"yearmonth" => "", "contest_name" => "", "update_end_date" => NULL, "archived" => 0,
			"exhibition_start_date" => NULL, "exhibition_end_date" => NULL, "exhibition_name" => "",
			"exhibition_venue" => "", "exhibition_venue_address" => "", "exhibition_venue_location_map" => "",
			"exhibition_report_blob" => "",
			"schedule_blob" => "", "invitation_img" => "", "email_header_img" => "", "email_message_blob" => "",
			"dignitory_roles" => "", "dignitory_names" => "", "dignitory_positions" => "", "dignitory_avatars" => "",
			"chair_role" => "", "chair_name" => "", "chair_position" => "", "chair_avatar" => "", "chair_blob" => "",
			"guest_role" => "", "guest_name" => "", "guest_position" => "", "guest_avatar" => "", "guest_blob" => "",
			"other_role" => "", "other_name" => "", "other_position" => "", "other_avatar" => "", "other_blob" => "",
			"dignitory_profile_blobs" => "", "is_virtual" => 0, "virtual_tour_ready" => 0,
	);
	$yearmonth = 0;
	$is_contest_archived = false;

	// Fill $salon, if yearmonth passed
	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		$sql = "SELECT * FROM contest LEFT JOIN exhibition ON exhibition.yearmonth = contest.yearmonth WHERE contest.yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
			$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
			foreach ($salon as $field => $value) {
				if (isset($row[$field])) {			// Handle NULLs if exhibition has not yet been setup
					if (is_null($row[$field])) {
						if (is_string($salon[$field]))
							$salon[$field] = "";
						else if (is_numeric($salon[$field]))
							$salon[$field] = 0;
						else if (substr($field, -4) == "date")
							$salon[$field] = NULL;
					}
					else {
						$salon[$field] = $row[$field];
					}
				}
				// Set full path for Invitation Image and Email Header Image
				if ( ! empty($row['invitation_img'])) {
					if ($row['invitation_img'] != "exhibition-invite-card.jpg" && file_exists("../../salons/$yearmonth/img/" . $row['invitation_img'])) {
						copy("../../salons/$yearmonth/img/" . $row['invitation_img'], "../../salons/$yearmonth/img/exhibition-invite-card.jpg");
					}
					$salon['invitation_img'] = "/salons/$yearmonth/img/exhibition-invite-card.jpg";
				}
				if ( ! empty($row['email_header_img'])) {
					if ($row['email_header_img'] != "exhibition-banner.jpg" && file_exists("../../salons/$yearmonth/img/" . $row['email_header_img'])) {
						copy("../../salons/$yearmonth/img/" . $row['email_header_img'], "../../salons/$yearmonth/img/exhibition-banner.jpg");
					}
					$salon['email_header_img'] = "/salons/$yearmonth/img/exhibition-banner.jpg";
				}
				// Process Dignitory Details
				if (! empty($row["dignitory_roles"])) {
					$dignitory_roles = explode("|", $row['dignitory_roles']);
					$salon['chair_role'] = isset($dignitory_roles[0]) ? $dignitory_roles[0] : "";
					$salon['guest_role'] = isset($dignitory_roles[1]) ? $dignitory_roles[1] : "";
					$salon['other_role'] = isset($dignitory_roles[2]) ? $dignitory_roles[2] : "";
				}
				if (! empty($row["dignitory_names"])) {
					$dignitory_names = explode("|", $row['dignitory_names']);
					$salon['chair_name'] = isset($dignitory_names[0]) ? $dignitory_names[0] : "";
					$salon['guest_name'] = isset($dignitory_names[1]) ? $dignitory_names[1] : "";
					$salon['other_name'] = isset($dignitory_names[2]) ? $dignitory_names[2] : "";
				}
				if (! empty($row["dignitory_positions"])) {
					$dignitory_positions = explode("|", $row['dignitory_positions']);
					$salon['chair_position'] = isset($dignitory_positions[0]) ? $dignitory_positions[0] : "";
					$salon['guest_position'] = isset($dignitory_positions[1]) ? $dignitory_positions[1] : "";
					$salon['other_position'] = isset($dignitory_positions[2]) ? $dignitory_positions[2] : "";
				}
				if (! empty($row["dignitory_avatars"])) {
					$target_dir = "/salons/$yearmonth/img/";
					$dignitory_avatars = explode("|", $row['dignitory_avatars']);
					$salon['chair_avatar'] = (! empty($dignitory_avatars[0])) ? $target_dir . $dignitory_avatars[0] : "/res/user.jpg";
					$salon['guest_avatar'] = (! empty($dignitory_avatars[1])) ? $target_dir . $dignitory_avatars[1] : "/res/user.jpg";
					$salon['other_avatar'] = (! empty($dignitory_avatars[2])) ? $target_dir . $dignitory_avatars[2] : "/res/user.jpg";
				}
				if (! empty($row["dignitory_blobs"])) {
					$dignitory_blobs = explode("|", $row['dignitory_blobs']);
					$salon['chair_blob'] = isset($dignitory_blobs[0]) ? $dignitory_blobs[0] : "";
					$salon['guest_blob'] = isset($dignitory_blobs[1]) ? $dignitory_blobs[1] : "";
					$salon['other_blob'] = isset($dignitory_blobs[2]) ? $dignitory_blobs[2] : "";
				}
			}
		}
		$is_contest_archived = ($salon['archived'] == '1' );
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
						<?= $yearmonth == 0 ? "Select a Salon" : "Exhibirion Details for " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="setup_exhibition.php" enctype="multipart/form-data" >
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
			<?php
				if ($yearmonth != 0) {
			?>
			<form role="form" method="post" id="edit_contest_form" name="edit_contest_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="update_end_date" id="update_end_date" value="<?= $salon['update_end_date'];?>" >

				<!-- Edited Fields -->
				<!-- Exhibition dates -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="exhibition_start_date">Exhibition Start Date</label>
						<input type="date" name="exhibition_start_date" class="form-control" id="exhibition_start_date" value="<?= $salon['exhibition_start_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label for="exhibition_end_date">Exhibition End Date</label>
						<input type="date" name="exhibition_end_date" class="form-control" id="exhibition_end_date" value="<?= $salon['exhibition_end_date'];?>" >
					</div>
					<div class="col-sm-3">
						<label>Is Exhibition Virtual ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="is_virtual" id="is_virtual" value="1" <?= $salon['is_virtual'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="Virtual Exhibition" >
						</div>
					</div>
					<div class="col-sm-3">
						<label>Virtual Rooms Ready ?</label>
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="virtual_tour_ready" id="virtual_tour_ready" value="1" <?= $salon['virtual_tour_ready'] == "0" ? "" : "checked";?> >
							</span>
							<input type="text" class="form-control" readonly value="Virtual Rooms Ready" >
						</div>
					</div>
				</div>

				<!-- Exhibition Blobs -->
				<div class="row form-group">
					<div class="col-sm-4">
						<label for="exhibition_report_blob">Exhibition Report</label>
						<div class="input-group">
							<input type="text" class="form-control" name="exhibition_report_blob" id="exhibition_report_blob" readonly
									value="<?= $salon['exhibition_report_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="exhibition_report"
									data-blob-input="exhibition_report_blob"
									data-blob="<?= $salon['exhibition_report_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="judging_description_blob">Exhibition Schedule</label>
						<div class="input-group">
							<input type="text" class="form-control" name="schedule_blob" id="schedule_blob" readonly value="<?= $salon['schedule_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="exhibition_schedule"
									data-blob-input="schedule_blob"
									data-blob="<?= $salon['schedule_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-4">
						<label for="chairman_message_blob">Email Communication</label>
						<div class="input-group">
							<input type="text" class="form-control" name="email_message_blob" id="email_message_blob" readonly
									value="<?= $salon['email_message_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="<?= ((! empty($salon['is_virtual'])) && $salon['is_virtual'] == '1') ? 'exhibition_invite_webinar' : 'exhibition_invite_venue';?>"
									data-blob-input="email_message_blob"
									data-blob="<?= $salon['email_message_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Exhibition Location Info -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="exhibition_name">Exhibition Name</label>
						<input type="text" name="exhibition_name" class="form-control" id="exhibition_name" value="<?= $salon['exhibition_name'];?>" >
					</div>
					<div class="col-sm-6">
						<label for="exhibition_venue">Exhibition Venue</label>
						<input type="text" name="exhibition_venue" class="form-control" id="exhibition_venue" value="<?= $salon['exhibition_venue'];?>" >
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-12">
						<label for="exhibition_venue_address">Exhibition Venue Address</label>
						<input type="text" name="exhibition_venue_address" class="form-control" id="exhibition_venue_address" value="<?= $salon['exhibition_venue_address'];?>" >
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-12">
						<label for="exhibition_venue_location_map">Exhibition Venue Location Map</label>
						<input type="text" name="exhibition_venue_location_map" class="form-control" id="exhibition_venue_location_map" value="<?= $salon['exhibition_venue_location_map'];?>" >
					</div>
				</div>

				<!-- Images -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="invitation_img">Invitation Card Image</label>
						<p><img src="<?= $salon['invitation_img'];?>" class="text-center" id="img_invitation" style="max-width: 120px" ></p>
						<input type="file" name="invitation_img" class="form-control img-file" id="invitation_img" data-img="img_invitation" >
					</div>
					<div class="col-sm-3">
						<label for="email_header_img">Invitation Email Header Image</label>
						<p><img src="<?= $salon['email_header_img'];?>" class="text-center" id="img_email_header" style="max-width: 120px" ></p>
						<input type="file" name="email_header_img" class="form-control img-file" id="email_header_img" data-img="img_email_header" >
					</div>
				</div>

				<!-- Dignitory Information -->
				<h3 class="text-info">Dignitory Details</h3>
				<div class="row form-group">
					<div class="col-sm-3"></div>
					<div class="col-sm-3">
						<label>Chief Guest</label>
						<a class="delete-dignitory" id="delete_chair"
								data-index="0"
								style='<?= ($salon['chair_name'] == "") ? "display: none;" : "";?>' >
							<i class="fa fa-trash" style="color: red;" ></i>
						</a>
					</div>
					<div class="col-sm-3">
						<label>Guest of Honor</label>
						<a class="delete-dignitory" id="delete_guest"
								data-index="1"
								style='<?= ($salon['guest_name'] == "") ? "display: none;" : "";?>' >
							<i class="fa fa-trash" style="color: red;" ></i>
						</a>
					</div>
					<div class="col-sm-3">
						<label>Other Dignitory</label>
						<a class="delete-dignitory" id="delete_other"
								data-index="2"
								style='<?= ($salon['other_name'] == "") ? "display: none;" : "";?>' >
							<i class="fa fa-trash" style="color: red;" ></i>
						</a>
					</div>
				</div>
				<div class="row form-group" style="padding-bottom: 4px; border-bottom: 1px solid #aaa;">
					<div class="col-sm-3"><label>Role Name</label></div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_roles[0]" class="form-control" id="chair_role" value="<?= $salon['chair_role'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_roles[1]" class="form-control" id="guest_role" value="<?= $salon['guest_role'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_roles[2]" class="form-control" id="other_role" value="<?= $salon['other_role'];?>" >
					</div>
				</div>
				<div class="row form-group" style="padding-bottom: 4px; border-bottom: 1px solid #aaa;">
					<div class="col-sm-3"><label>Dignitory Name</label></div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_names[0]" class="form-control" id="chair_name" value="<?= $salon['chair_name'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_names[1]" class="form-control" id="guest_name" value="<?= $salon['guest_name'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_names[2]" class="form-control" id="other_name" value="<?= $salon['other_name'];?>" >
					</div>
				</div>
				<div class="row form-group" style="padding-bottom: 4px; border-bottom: 1px solid #aaa;">
					<div class="col-sm-3"><label>Dignitory Position</label></div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_positions[0]" class="form-control" id="chair_position" value="<?= $salon['chair_position'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_positions[1]" class="form-control" id="guest_position" value="<?= $salon['guest_position'];?>" >
					</div>
					<div class="col-sm-3">
						<input type="text" name="dignitory_positions[2]" class="form-control" id="other_position" value="<?= $salon['other_position'];?>" >
					</div>
				</div>
				<div class="row form-group" style="padding-bottom: 4px; border-bottom: 1px solid #aaa;">
					<div class="col-sm-3"><label>Dignitory Avatar</label></div>
					<div class="col-sm-3">
						<img src="<?= $salon['chair_avatar'];?>" class="text-center" id="img_chair_avatar" style="max-width: 120px" >
						<input type="file" name="dignitory_avatars[0]" class="form-control img-file" id="chair_avatar" data-img="img_chair_avatar" >
					</div>
					<div class="col-sm-3">
						<img src="<?= $salon['guest_avatar'];?>" class="text-center" id="img_guest_avatar" style="max-width: 120px" >
						<input type="file" name="dignitory_avatars[1]" class="form-control img-file" id="guest_avatar" data-img="img_guest_avatar" >
					</div>
					<div class="col-sm-3">
						<img src="<?= $salon['other_avatar'];?>" class="text-center" id="img_other_avatar" style="max-width: 120px" >
						<input type="file" name="dignitory_avatars[2]" class="form-control img-file" id="other_avatar" data-img="img_other_avatar" >
					</div>
				</div>
				<div class="row form-group" style="padding-bottom: 4px; border-bottom: 1px solid #aaa;">
					<div class="col-sm-3"><label>Dignitory Profile Blob</label></div>
					<div class="col-sm-3">
						<div class="input-group">
							<input type="text" class="form-control" name="dignitory_blobs[0]" id="dignitory_blobs_0" readonly
									value="<?= $salon['chair_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="chair_blob"
									data-blob-input="dignitory_blobs_0"
									data-blob="<?= $salon['chair_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="input-group">
							<input type="text" class="form-control" name="dignitory_blobs[1]" id="dignitory_blobs_1" readonly
									value="<?= $salon['guest_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="guest_blob"
									data-blob-input="dignitory_blobs_1"
									data-blob="<?= $salon['guest_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="input-group">
							<input type="text" class="form-control" name="dignitory_blobs[2]" id="dignitory_blobs_2" readonly
									value="<?= $salon['other_blob'];?>" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="other_blob"
									data-blob-input="dignitory_blobs_2"
									data-blob="<?= $salon['other_blob'];?>" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>


				<!-- Update -->
				<br><br>
				<div class="row form-group">
					<div class="col-sm-9">
						<input class="btn btn-primary pull-right" type="submit" id="setup_exhibition" name="setup_exhibition" value="Save Exhibition Details">
					</div>
				</div>
			</form>
			<?php
				}
			?>
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

	$(document).ready(function(){
		// Hide Form till a salon is loaded
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

<!-- Load Picture into view when selected -->
<script>
	$(document).ready(function(){
		// Load picture into view
		$(".img-file").on("change", function(){
			let input = $(this).get(0);
			let target = $(this).attr("data-img");
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#" + target).attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});
	});
</script>

<!-- Edit Contest -->
<script>
	function show_dignitory_delete() {
		if ($("#chair_name").val() == "")
			$("#delete_chair").hide();
		else
			$("#delete_chair").show();

		if ($("#guest_name").val() == "")
			$("#delete_guest").hide();
		else
			$("#delete_guest").show();

		if ($("#other_name").val() == "")
			$("#delete_other").hide();
		else
			$("#delete_other").show();
	}

	$(document).ready(function(){
		let vaidator = $('#edit_contest_form').validate({
			rules:{
				results_date : {
					required : true,
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
						url: "ajax/setup_exhibition.php",
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
										text: "Contest Exhibition has been created successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								$("#edit-contest-modal").modal('hide');
								show_dignitory_delete();
							}
							else{
								swal({
										title: "Save Failed",
										text: "Contest Exhibition details could not be created: " + response.msg,
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

<!-- Delete Dignitory Function -->
<script>
	function remove_dignitory_from_display(index) {
		let prefix = "";
		prefix = (index == 0) ? "chair" : prefix;
		prefix = (index == 1) ? "guest" : prefix;
		prefix = (index == 2) ? "other" : prefix;

		// Empty Fields
		$("#" + prefix + "_role").val("");
		$("#" + prefix + "_name").val("");
		$("#" + prefix + "_position").val("");
		let img_id = $("#" + prefix + "_avatar").attr("data-img");
		$("#" + img_id).attr("src", "/res/user.jpg");
		$("#" + prefix + "_blob").val("");

		// Hide delete button
		$("#delete_" + prefix).hide();
	}
	$(document).ready(function(){
		$(".delete-dignitory").click(function(){
			let index = $(this).attr("data-index");
			swal({
				title: 'DELETE Confirmation',
				text:  "Do you want to delete the dignitory ?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then(function (delete_confirmed) {
				if (delete_confirmed) {
					// Assemble Data
					let formData = new FormData();
					formData.append("yearmonth", <?= $yearmonth;?>);
					formData.append("dignitory_index", index);

					$('#loader_img').show();
					$.ajax({
							url: "ajax/delete_dignitory.php",
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
											title: "Dignitory Deleted",
											text: "Dignitory has been deleted from Contest Exhibition.",
											icon: "success",
											confirmButtonClass: 'btn-success',
											confirmButtonText: 'Great'
									});
									$("#edit-contest-modal").modal('hide');
									// Update Display
									remove_dignitory_from_display(index);
								}
								else{
									swal({
											title: "Save Failed",
											text: "Unable to delete the Dignitory from the Contest Exhibition: " + response.msg,
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
				}
			});
		})
	})

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

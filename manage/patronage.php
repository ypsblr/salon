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
			"yearmonth" => "", "contest_name" => "", "is_international" => "0",
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

		// Do not Copy Rules
		// Set submission_last_date to registration_last_date of target salon
		if (isset($_REQUEST['clonefrom'])) {
			$clonefrom = $_REQUEST['clonefrom'];
			$yearmonth = $_REQUEST['yearmonth'];
			$sql = "SELECT * FROM recognition WHERE yearmonth = '$clonefrom' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				$short_code = $row['short_code'];
				$organization_name = mysqli_real_escape_string($DBCON, $row['organization_name']);
				$website = mysqli_real_escape_string($DBCON, $row['website']);
				$recognition_id = "APPLIED";	// Cannot copy from previous year
				$small_logo = "";		// Not used any more
				$logo = $row['logo'];		// Set to last date of registration of current salon
				$notification = $row['notification'];
				$description = "";		// Not used for now
				$notice = mysqli_real_escape_string($DBCON, $row['notice']);

				// Copy Logo and Notification Images
				// Copy Logo
				$src_logo = "../salons/$clonefrom/img/recognition/$logo";
				$tgt_logo = "../salons/$yearmonth/img/recognition/$logo";
				if (file_exists($src_logo)) {
					if (! is_dir("../salons/$yearmonth/img/recognition"))
						mkdir("../salons/$yearmonth/img/recognition");
					copy($src_logo, $tgt_logo);
				}
				else {
					$logo = "";			// Logo does not exist
				}
				// Copy Notification
				$src_notification = "../salons/$clonefrom/img/recognition/$notification";
				$tgt_notification = "../salons/$yearmonth/img/recognition/$notification";
				if (file_exists($src_notification)) {
					if (! is_dir("../salons/$yearmonth/img/recognition"))
						mkdir("../salons/$yearmonth/img/recognition");
					copy($src_notification, $tgt_notification);
				}
				else {
					$notification = "";			// Logo does not exist
				}

				// Insert data into current salon
				$sql  = "INSERT INTO recognition (yearmonth, short_code, organization_name, website, recognition_id, small_logo, logo, ";
				$sql .= "             notification, description, notice, rules) ";
				$sql .= "VALUES ('$yearmonth', '$short_code', '$organization_name', '$website', '$recognition_id', '$small_logo', '$logo', ";
				$sql .= "       '$notification', '$description', '$notice', NULL) ";
				mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}

		// Load Recognitions
		$sql  = "SELECT * FROM recognition ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$recognition_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$recognition_list[$row['short_code']] = $row;
		}
		$salon["recognition_list"] = $recognition_list;
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
						MANAGE RECOGNITIONS - <?= $yearmonth == 0 ? "" : "FOR " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-salon-form" action="patronage.php" enctype="multipart/form-data" >
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
				if ($yearmonth != 0 && sizeof($recognition_list) == 0) {
			?>
			<!-- Select a Salon to clone from if current list of recognitions is empty -->
			<h3 class="text-info">Copy Recognitions from another Salon</h3>
			<form role="form" method="post" name="copy-recognitions-form" id="copy-recognitions-form" action="patronage.php" enctype="multipart/form-data" >
				<input type="hidden" name="yearmonth" id="clone_yearmonth" value="<?= $yearmonth;?>" >
				<div class="row form-group">
					<div class="col-sm-8">
						<label for="yearmonth">Copy Recognitions from selected Salon</label>
						<div class="input-group">
							<select class="form-control" name="clonefrom">
							<?php
								$sql  = "SELECT contest.yearmonth, contest_name, COUNT(*) AS num_recognitions ";
								$sql .= "  FROM contest, recognition ";
								$sql .= " WHERE recognition.yearmonth = contest.yearmonth ";
								$sql .= " GROUP BY contest.yearmonth ";
								$sql .= " ORDER BY yearmonth DESC ";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
								<option value="<?= $row['yearmonth'];?>" ><?= $row['contest_name'] . " (" . $row['num_recognitions'] . " recognitions)";?></option>
							<?php
								}
							?>
							</select>
							<span class="input-group-btn">
								<button type="submit" class="btn btn-info pull-right" name="clone-recognitions-button" ><i class="fa fa-copy"></i> COPY </a>
							</span>
						</div>
					</div>
				</div>
			</form>
			<?php
				}
			?>

			<h3 class="text-info">Recognition Add/Edit</h3>
			<form role="form" method="post" id="recognition_details_form" name="recognition_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="is_international" id="is_international" value="<?= $salon['is_international'];?>">
				<input type="hidden" name="logo" id="prev-logo" value="">
				<input type="hidden" name="notification" id="prev-notification" value="">
				<input type="hidden" name="is_edit_recognition" id="is_edit_recognition" value="0">

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

				<!-- Collect Recognition Details to Add/Edit -->
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="organization_name">Organization Name</label>
						<input type="text" name="organization_name" class="form-control" id="organization_name" >
					</div>
					<div class="col-sm-4">
						<label for="short_code">Unique short code</label>
						<input type="text" name="short_code" class="form-control text-uppercase" id="short_code" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="recognition_id">Recognition Code</label>
						<input type="text" name="recognition_id" class="form-control" id="recognition_id" >
					</div>
					<div class="col-sm-4">
						<label for="website">Website</label>
						<input type="url" name="website" class="form-control" id="website" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-6">
						<label for="edit-logo">Logo</label>
						<div class="row">
							<div class="col-sm-12">
								<img id="edit-logo" style="max-width: 120px; max-height: 120px;">
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<input type="file" name="logo_file" id="edit-logo-file" >
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<label for="edit-notification">Notification</label>
						<div class="row">
							<div class="col-sm-12">
								<img id="edit-notification" style="max-width: 180px; max-height: 180px;">
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<input type="file" name="notification_file" id="edit-notification-file" >
							</div>
						</div>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-8">
						<label>Notices to the participant</label>
						<textarea name="notice" id="notice" rows="4"></textarea>
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_recognition" name="update_recognition">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>

			<!-- Recognition List -->
			<div id="recognition_list">
				<h3 class="text-info">List of Recognitions</h3>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>Code</th>
									<th>Organization</th>
									<th>Recognition Code</th>
									<th>Website</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($recognition_list)) {
									foreach ($recognition_list as $short_code => $recognition_data) {
							?>
								<tr id="<?= $short_code;?>-row">
									<td><?= $short_code;?></td>
									<td><?= $recognition_data['organization_name'];?></td>
									<td><?= $recognition_data['recognition_id'];?></td>
									<td><?= $recognition_data['website'];?></td>
									<td>
										<button class="btn btn-info recognition-edit-button"
												data-short-code='<?= $short_code;?>'
												data-recognition='<?= json_encode($recognition_data, JSON_FORCE_OBJECT | JSON_HEX_APOS | JSON_HEX_QUOT);?>' >
											<i class="fa fa-edit"></i>
										</button>
									</td>
									<td>
										<button class="btn btn-danger recognition-delete-button"
												data-short-code='<?= $short_code;?>'
												data-recognition='<?= json_encode($recognition_data, JSON_FORCE_OBJECT | JSON_HEX_APOS | JSON_HEX_QUOT);?>' >
											<i class="fa fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_recognition_list">
									<th><?= isset($recognition_list) ? sizeof($recognition_list) : 0;?></th>
									<th>End of List</th>
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

<!-- Include Tiny MCE Editor for notices -->
<!-- tinymce editor -->
<script src='plugin/tinymce/tinymce.min.js'></script>
<script src='plugin/tinymce/plugins/link/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/lists/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/image/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/table/plugin.min.js'></script>


<!-- Action Handlers -->
<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Init Tinymce
		tinymce.init({
			selector: '#notice',
			height: 400,
			plugins : 'link lists image table',
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

<!-- Add / Update Recognition -->
<script>
	$(document).ready(function(){
		let vaidator = $('#recognition_details_form').validate({
			rules:{
				organization_name : { required : true, },
				short_code : {
					required : true,
					maxlength : 8,
				},
				recognition_id : { required : true, },
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
				// Set ext area with value from tinymce
				$("#notice").text(tinymce.get("notice").getContent());

				// Assemble Data
				var formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_recognition.php",
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
										text: "Recognition data has been saved successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								$("#" + $("#short_code").val() + "-row").remove();
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_recognition_list");
								// Re-install handlers
								$(".recognition-edit-button").click(function(){
									edit_recognition(this);
								});
								$(".recognition-delete-button").click(function(){
									delete_recognition(this);
								});
								// Reset Form fields to default
								$("#is_edit_recognition").val("0");
								$("#organization_name").val("");
								$("#short_code").val("");
								$("#short_code").removeAttr("readonly");
								$("#recognition_id").val("");
								$("#website").val("");
								$("#notice").text("");
								tinymce.get("notice").setContent("");
								$("#prev-logo").val("");
								$("#edit-logo").removeAttr("src");
								$("#edit-logo-file").val("");
								$("#prev-notification").val("");
								$("#edit-notification").removeAttr("src");
								$("#edit-notification-file").val("");
								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Patronage could not be saved: " + response.msg,
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

<!-- Handle Delete Recognition -->
<script>
	// Handle delete button request
	function delete_recognition(button) {
		let short_code = $(button).attr("data-short-code");
		let recognition = JSON.parse($(button).attr("data-recognition"));
		let yearmonth = $("#yearmonth").val();
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the patronage from " + recognition.organization_name + " ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_recognition) {
			if (delete_recognition) {
				$('#loader_img').show();
				$.post("ajax/delete_recognition.php", {yearmonth, short_code}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + short_code + "-row").remove();
						swal({
								title: "Removed",
								text: "Patronage from " + recognition.organization_name + " has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove patronage: " + response.msg,
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
		$(".recognition-delete-button").click(function(){
			delete_recognition(this);
		});
	});
</script>

<!-- Handle Edit Recognition -->
<script>
	function edit_recognition(button) {
		let short_code = $(button).attr("data-short-code");
		let recognition = JSON.parse($(button).attr("data-recognition"));
		// Fill the form with Details
		$("#is_edit_recognition").val("1");
		$("#short_code").val(recognition.short_code);
		$("#short_code").attr("readonly", true);
		$("#organization_name").val(recognition.organization_name);
		$("#recognition_id").val(recognition.recognition_id);
		$("#website").val(recognition.website);
		$("#notice").val(recognition.notice);
		tinymce.get("notice").setContent(recognition.notice);
		$("#prev-logo").val(recognition.logo);
		$("#prev-notification").val(recognition.notification);
		$("#edit-logo").attr("src", "/salons/" + $("#yearmonth").val() + "/img/recognition/" + recognition.logo);
		$("#edit-notification").attr("src", "/salons/" + $("#yearmonth").val() + "/img/recognition/" + recognition.notification);
		$("#update_button").html("<i class='fa fa-edit'></i> Update");
		swal({
				title: "Edit",
				text: "Details of recognition from " + recognition.organization_name + " have been copied to the form. Edit the details and click on the Update button.",
				icon: "success",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Great'
		});
	}

	$(document).ready(function(){
		$(".recognition-edit-button").click(function(){
			edit_recognition(this);
		});
	});

	// Load picture into view
	$("#edit-logo-file").on("change", function(){
		let input = $(this).get(0);
		if (input.files && input.files.length > 0 && input.files[0] != "") {
			// Phase 1 Check Size & Dimensions
			var file_size = input.files[0].size;
			var reader = new FileReader();
			// Handler for file read completion
			reader.onload = function (e) {
				$("#edit-logo").attr("src", e.target.result);
			}
			// Handler for File Read Error
			reader.onerror = function(e) {
				alert("Unable to open selected picture");
			}

			// Perform File Read
			reader.readAsDataURL(input.files[0]);

		}
	});
	// Load picture into view
	$("#edit-notification-file").on("change", function(){
		let input = $(this).get(0);
		if (input.files && input.files.length > 0 && input.files[0] != "") {
			// Phase 1 Check Size & Dimensions
			var file_size = input.files[0].size;
			var reader = new FileReader();
			// Handler for file read completion
			reader.onload = function (e) {
				$("#edit-notification").attr("src", e.target.result);
			}
			// Handler for File Read Error
			reader.onerror = function(e) {
				alert("Unable to open selected picture");
			}

			// Perform File Read
			reader.readAsDataURL(input.files[0]);

		}
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

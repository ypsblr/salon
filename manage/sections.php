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

		// Clone sections, if clonefrom passed
		// Do not Copy Rules
		// Set submission_last_date to registration_last_date of target salon
		if (isset($_REQUEST['clonefrom'])) {
			$clonefrom = $_REQUEST['clonefrom'];
			$yearmonth = $_REQUEST['yearmonth'];
			$sql = "SELECT * FROM section WHERE yearmonth = '$clonefrom' ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			while ($row = mysqli_fetch_array($query)) {
				$section = $row['section'];
				$section_type = $row['section_type'];
				$section_sequence = $row['section_sequence'];
				$stub = $row['stub'];
				$description = "";		// Not used any more
				$submission_last_date = $salon['registration_last_date'];		// Set to last date of registration of current salon
				$max_pics_per_entry = $row['max_pics_per_entry'];
				$rules = "";
				$rules_blob = "section_" . $row['stub'] . "_rules.htm";
				// Creates Rules Blobs in the target folder
				// Copy existing blob if specified
				if ($row['rules_blob'] != "") {
					$src_blob = "../salons/$clonefrom/blob/" . $row['rules_blob'];
					$tgt_blob = "../salons/$yearmonth/blob/$rules_blob";
					copy($src_blob, $tgt_blob);
				}
				elseif ($row['rules'] != "") {
					// copy rules into blob file
					$tgt_blob = "../salons/$yearmonth/blob/$rules_blob";
					file_put_contents($tgt_blob, $row['rules']);
				}
				else {
					// Set rules_blob blank
					$rules_blob = "";
				}

				// Insert section into current salon
				$sql  = "INSERT INTO section (yearmonth, section, section_type, section_sequence, stub, description, rules, rules_blob, ";
				$sql .= "             submission_last_date, max_pics_per_entry) ";
				$sql .= "VALUES ('$yearmonth', '$section', '$section_type', '$section_sequence', '$stub', '$description', '$rules', '$rules_blob', ";
				$sql .= "       '$submission_last_date', '$max_pics_per_entry') ";
				mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			}
		}

		// Load Sections
		$sql  = "SELECT yearmonth, section, section_type, section_sequence, stub, description, rules, rules_blob, ";
		$sql .= "       submission_last_date, max_pics_per_entry ";
		$sql .= "  FROM section ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " ORDER BY section_type, section_sequence ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$section_list = [];
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			// Force a blob file name if empty
			if ($row['rules_blob'] == "")
				$row['rules_blob'] = "section_" . $row['stub'] . "_rules.htm";
			$section_list[$row['section']] = $row;
		}
		$salon["section_list"] = $section_list;
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
						MANAGE SECTIONS - <?= $yearmonth == 0 ? "" : "FOR " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" name="select-salon-form" action="sections.php" enctype="multipart/form-data" >
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
				if ($yearmonth != 0 && sizeof($section_list) == 0) {
			?>
			<!-- Select a section to clone from if current list of sections is empty -->
			<h3 class="text-info">Copy Sections from another Salon</h3>
			<form role="form" method="post" name="copy-sections-form" id="copy-sections-form" action="sections.php" enctype="multipart/form-data" >
				<input type="hidden" name="yearmonth" id="clone_yearmonth" value="<?= $yearmonth;?>" >
				<div class="row form-group">
					<div class="col-sm-8">
						<label for="yearmonth">Copy Sections from selected Salon</label>
						<div class="input-group">
							<select class="form-control" name="clonefrom">
							<?php
								$sql  = "SELECT contest.yearmonth, contest_name, COUNT(*) AS num_sections ";
								$sql .= "  FROM contest, section ";
								$sql .= " WHERE section.yearmonth = contest.yearmonth ";
								$sql .= " GROUP BY contest.yearmonth ";
								$sql .= " ORDER BY yearmonth DESC ";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
								<option value="<?= $row['yearmonth'];?>" ><?= $row['contest_name'] . " (" . $row['num_sections'] . " sections)";?></option>
							<?php
								}
							?>
							</select>
							<span class="input-group-btn">
								<button type="submit" class="btn btn-info pull-right" name="clone-sections-button" ><i class="fa fa-copy"></i> COPY </a>
							</span>
						</div>
					</div>
				</div>
			</form>
			<?php
				}
			?>

			<h3 class="text-info">Section Add/Edit</h3>
			<form role="form" method="post" id="section_details_form" name="section_details_form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="registration_last_date" id="registration_last_date" value="<?= $salon['registration_last_date'];?>">
				<input type="hidden" name="is_edit_section" id="is_edit_section" value="0">

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
					<div class="col-sm-6">
						<label for="section">Section Name</label>
						<input type="text" name="section" class="form-control text-uppercase" id="section" >
					</div>
					<div class="col-sm-4">
						<label for="stub">Unique short Stub</label>
						<input type="text" name="stub" class="form-control text-uppercase" id="stub" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="section_type">Section Type</label>
						<select class="form-control" name="section_type" id="section_type" >
							<option value="D">Digital</option>
							<option value="P">Print</option>
						</select>
					</div>
					<div class="col-sm-4">
						<label for="section_sequence">Sequence No for display</label>
						<input type="number" name="section_sequence" class="form-control" id="section_sequence" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label for="submission_last_date">Submission Last Date</label>
						<input type="date" name="submission_last_date" class="form-control" id="submission_last_date" value="<?= $salon['registration_last_date'];?>" >
					</div>
					<div class="col-sm-4">
						<label for="max_pics_per_entry">Maximum Uploads to Section</label>
						<input type="number" name="max_pics_per_entry" class="form-control" id="max_pics_per_entry" >
					</div>
				</div>

				<div class="row form-group">
					<div class="col-sm-4">
						<label>Rules for the section</label>
						<div class="input-group">
							<input type="text" class="form-control" name="rules_blob" id="rules_blob" readonly value="" >
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob" id="blob_button"
									data-yearmonth="<?= $yearmonth;?>"
									data-blob-type="section_rules"
									data-blob="" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>

				<!-- Update -->
				<br>
				<div class="row form-group">
					<div class="col-sm-9">
						<button type="submit" class="btn btn-info" id="update_section" name="update_section">
							<span id="update_button"><i class="fa fa-plus"></i> Add</span>
						</button>
					</div>
				</div>
			</form>
			<hr>

			<!-- Section List -->
			<div id="section_list">
				<h3 class="text-info">List of Sections</h3>
				<div class="row">
					<div class="col-sm-12">
						<table class="table">
							<thead>
								<tr>
									<th>Seq</th>
									<th>Section</th>
									<th>Type</th>
									<th>Last Date</th>
									<th>Max Uploads</th>
									<th>EDIT</th>
									<th>DEL</th>
								</tr>
							</thead>
							<tbody>
							<?php
								if (isset($section_list)) {
									foreach ($section_list as $section => $section_data) {
							?>
								<tr id="<?= $section_data['stub'];?>-row">
									<td><?= $section_data['section_sequence'];?></td>
									<td><?= $section;?></td>
									<td><?= ($section_data['section_type'] == "P") ? "Print" : "Digital";?></td>
									<td><?= $section_data['submission_last_date'];?></td>
									<td><?= $section_data['max_pics_per_entry'];?></td>
									<td>
										<button class="btn btn-info section-edit-button"
												data-stub='<?= $section_data['stub'];?>'
												data-section='<?= json_encode($section_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-edit"></i>
										</button>
									</td>
									<td>
										<button class="btn btn-danger section-delete-button"
												data-stub='<?= $section_data['stub'];?>'
												data-section='<?= json_encode($section_data, JSON_FORCE_OBJECT);?>' >
											<i class="fa fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php
									}
								}
							?>
								<tr id="end_of_section_list">
									<th><?= isset($section_list) ? sizeof($section_list) : 0;?></th>
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
			<?php include("inc/blob_modal_html.php"); ?>
		</div>

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
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		// Set blob_file_name to section_BLOB_rules.htm when not editing the section
		$("#stub").on("change", function(){
			if ($("#is_edit_section").val() == "0") {
				$("#rules_blob").val("section_" + $("#stub").val().toUpperCase() + "_rules.htm");
				$("#blob_button").attr("data-blob", $("#rules_blob").val());
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

<!-- Add / Update section -->
<script>
	$(document).ready(function(){
		let vaidator = $('#section_details_form').validate({
			rules:{
				section : { required : true, },
				stub : {
					required : true,
					maxlength : 4,
				},
				section_type : { required : true, },
				section_sequence : { required : true, min : 1, max : 20, },
				submission_last_date : {
					required : true,
					date_max : "#registration_last_date",
				},
				max_pics_per_entry : { required : true, min : 1, max : 16, },
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
						url: "ajax/create_section.php",
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
										text: "Section has been created successfully.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Remove existing table row
								$("#" + $("#stub").val() + "-row").remove();
								// Add to table at the end
								$(response.row_html).insertBefore("#end_of_section_list");
								// Re-install handlers
								$(".section-edit-button").click(function(){
									edit_section(this);
								});
								$(".section-delete-button").click(function(){
									delete_section(this);
								});
								// Reset Form fields to default
								$("#is_edit_section").val("0");
								$("#section").val("");
								$("#section").removeAttr("readonly");
								$("#stub").val("");
								$("#section_type").val("D");
								$("#section_sequence").val("0");
								$("#submission_last_date").val($("#registration_last_date").val());
								$("#max_pics_per_entry").val("0");
								// $("#rules").text("");
								$("#rules_blob").val("");
								$("#blob_button").attr("data-blob", "");
								$("#update_button").html("<i class='fa fa-plus'></i> Add");
							}
							else{
								swal({
										title: "Save Failed",
										text: "Salon could not be saved: " + response.msg,
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
	function delete_section(button) {
		let stub = $(button).attr("data-stub");
		let section_data = JSON.parse($(button).attr("data-section"));
		let section = section_data.section;
		let yearmonth = $("#yearmonth").val();
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the section " + section + " ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_section) {
			if (delete_section) {
				$('#loader_img').show();
				$.post("ajax/delete_section.php", {yearmonth, section}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#" + stub + "-row").remove();
						swal({
								title: "Removed",
								text: "Section " + section + " has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove section: " + response.msg,
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
		$(".section-delete-button").click(function(){
			delete_section(this);
		});
	});
</script>

<!-- Handle Edit Section -->
<script>
	function edit_section(button) {
		let stub = $(button).attr("data-stub");
		let section_data = JSON.parse($(button).attr("data-section"));
		// Fill the form with Details
		$("#is_edit_section").val("1");
		$("#section").val(section_data.section);
		$("#section").attr("readonly", true);
		$("#stub").val(section_data.stub);
		$("#section_type").val(section_data.section_type);
		$("#section_sequence").val(section_data.section_sequence);
		$("#submission_last_date").val(section_data.submission_last_date);
		$("#max_pics_per_entry").val(section_data.max_pics_per_entry);
		// $("#rules").text(section_data.rules);
		$("#rules_blob").val(section_data.rules_blob);
		$("#blob_button").attr("data-blob", section_data.rules_blob);
		$("#update_button").html("<i class='fa fa-edit'></i> Update");
		swal({
				title: "Edit",
				text: "Section " + section_data.section + " details have been copied to the form. Edit the details and click on the Update button.",
				icon: "success",
				confirmButtonClass: 'btn-success',
				confirmButtonText: 'Great'
		});
	}

	$(document).ready(function(){
		$(".section-edit-button").click(function(){
			edit_section(this);
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

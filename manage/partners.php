<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");
include("../inc/partnerlib.php");

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


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "",
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
	}

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />

	<style>
		div.valid-error {
			font-size: 10px;
			color : red;
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
						Manage Salon partners
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="partners.php" enctype="multipart/form-data" >
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
										<button type="submit" class="btn btn-info pull-right" name="select-contest-button" ><i class="fa fa-play"></i> GO </a>
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
			<div class="row">
				<!-- Provision for Filters -->
				<div style="display:inline-block; margin-left:15px;">
					<div>
						<div style="padding: 4px;" >
							<button class="btn btn-info" name="add-partner" id="add-partner"><i class="fa fa-plus-circle"></i> Add Partner</button>
						</div>
					</div>
				</div>
			</div>
			<?php
				}
			?>
			<hr>
			<div class="row">
				<div class="col-sm-12">
					<table id="partner_table" class="table" >
						<thead>
							<tr>
								<th>Logo</th>
								<th>Seq#</th>
								<th>Partner Name</th>
								<th>Tagline</th>
								<th>Type</th>
								<th>Phone</th>
								<th>Email</th>
								<th>Website</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php
							// Get Partner Data
							$partner_data = get_partner_data($yearmonth);
							if ( isset($partner_data['partners']) ) {
								foreach ($partner_data['partners'] as $partner) {
									$partner_idx = $partner['idx'];
									$partner_sequence = $partner['sequence'];
									$partner_name = $partner['name'];
									$partner_text = $partner['text'];
									$partner_tagline = isset($partner['tagline']) ? $partner['tagline'] : "";
									$partner_website = isset($partner['website']) ? $partner['website'] : "";
									$partner_phone = isset($partner['phone']) ? $partner['phone'] : "";
									$partner_email = isset($partner['email']) ? $partner['email'] : "";
									// Image
									if (file_exists(__DIR__ . "/../salons/$yearmonth/img/sponsor/" . $partner['img']))
										$partner_img = "/salons/$yearmonth/img/sponsor/" . $partner['img'];
									else
										$partner_img = "/img/preview.png";
									// Logo
									if ((! empty($partner['logo'])) && file_exists(__DIR__ . "/../salons/$yearmonth/img/sponsor/" . $partner['logo']))
										$partner_logo = "/salons/$yearmonth/img/sponsor/" . $partner['logo'];
									else
										$partner_logo = "/img/user.jpg";
							?>
							<tr id="partner-<?= $partner_idx;?>-row">
								<td><img id="logo-<?= $partner_idx;?>" style="max-width: 80px;" src="<?= $partner_logo;?>"></td>
								<td><span id="sequence-<?= $partner_idx;?>"><?= $partner_sequence;?></span></td>
								<td><span id="name-<?= $partner_idx;?>"><?= $partner_name;?></span></td>
								<td><span id="tagline-<?= $partner_idx;?>"><?= $partner_tagline;?></span></td>
								<td><span id="text-<?= $partner_idx;?>"><?= $partner_text;?></span></td>
								<td><span id="phone-<?= $partner_idx;?>"><?= $partner_phone;?></span></td>
								<td><span id="email-<?= $partner_idx;?>"><?= $partner_email;?></span></td>
								<td><span id="website-<?= $partner_idx;?>"><?= $partner_website;?></span></td>
								<td>
									<button id="edit-partner-<?= $partner_idx;?>" class="btn btn-info edit-partner" name="edit-partner"
										    data-idx='<?= $partner_idx;?>'
											data-partner='<?= json_encode($partner, JSON_FORCE_OBJECT);?>'>
										<i class="fa fa-edit"></i> Edit
									</button>
								</td>
								<td>
									<button id="delete-partner-<?= $partner_idx;?>" class="btn btn-danger delete-partner" name="delete-partner"
											data-idx='<?= $partner_idx;?>'
										<i class="fa fa-trash"></i> Delete
									</button>
								</td>
							</tr>
						<?php
								}
							}
						?>
							<tr id="end-of-partner-list" data-idx="<?= ++ $partner_idx;?>">
								<td></td>
								<td>End of List</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- MODAL FORMS -->
		<!-- Edit Partner -->
		<div class="modal" id="edit-partner-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Partner</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-partner-form" name="edit-partner-form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="is-edit-partner" value="0" >
							<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="prev_logo" id="edit-prev-logo" >
							<input type="hidden" name="prev_img" id="edit-prev-img" >
							<input type="hidden" name="partner_idx" id="edit-idx" value="0" >

							<!-- Edited Fields -->
							<!-- Logo -->
							<div class="row form-group">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12"><b>Logo</b></div>
										<div class="col-sm-3"><img src="/img/user.jpg" id="edit-logo" style="max-width: 120px; max-height: 120px;"></div>
										<div class="col-sm-9">
											<label for="edit-logo-file">Select Logo</label>
											<input type="file" name="partner_logo" id="edit-logo-file" >
										</div>
									</div>
								</div>
							</div>
							<!-- Logo -->
							<div class="row form-group">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12"><b>Graphics *</b></div>
										<div class="col-sm-3"><img src="/img/preview.png" id="edit-img" style="max-width: 120px; max-height: 120px;"></div>
										<div class="col-sm-9">
											<label for="edit-img-file">Select Graphics</label>
											<input type="file" name="partner_img" id="edit-img-file" >
										</div>
									</div>
								</div>
							</div>
							<!-- Name -->
							<div class="row form-group">
								<div class="col-sm-4">
									<label for="edit-sequence">Sequence *</label>
									<input type="text" name="partner_sequence" class="form-control" id="edit-sequence" placeholder="Seq..." required >
								</div>
								<div class="col-sm-8">
									<label for="edit-name">Partner Name *</label>
									<input type="text" name="partner_name" class="form-control" id="edit-name" placeholder="Partner name..." required >
								</div>
							</div>
							<!-- Name -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-tagline">Tagline</label>
									<input type="text" name="partner_tagline" class="form-control" id="edit-tagline" placeholder="Tagline..." >
								</div>
								<div class="col-sm-6">
									<label for="edit-text">Partner Role *</label>
									<input type="text" name="partner_text" class="form-control" id="edit-text" placeholder="Salon Partner..." required >
								</div>
							</div>
							<!-- URL -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-website">Website URL</label>
									<input type="url" name="partner_website" class="form-control" id="edit-website" placeholder="https://..." >
								</div>
							</div>
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-phone">Phone</label>
									<input type="phone" name="partner_phone" class="form-control" id="edit-phone" placeholder="+91-..." >
								</div>
								<div class="col-sm-6">
									<label for="edit-email">Email</label>
									<input type="email" name="partner_email" class="form-control" id="edit-email" placeholder="contactus@..." >
								</div>
							</div>
							<!-- Update -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="edit-update" name="edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- END OF MODAL FORM -->

	</div>
<?php
      include("inc/footer.php");
?>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>

<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<!-- Select 2 for Country Codes -->
<script src="plugin/select2/js/select2.min.js"></script>


<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

	});
</script>

<!-- Action Handlers -->
<script>

	// Fetch Partner data and launch edit dialog
	function launch_edit_modal(partner, mode, idx = 0) {
		$("#is-edit-partner").val(mode == "edit" ? "1" : "0");
		$("#edit-idx").val(idx);

		if (mode == "edit") {
			// Logo
			$("#edit-prev-logo").val(partner.logo);
			if (partner.logo == "")
				$("#edit-logo").attr("src", "/img/user.jpg");
			else
				$("#edit-logo").attr("src", "/salons/" + <?= $yearmonth;?> + "/img/sponsor/" + partner.logo);

			// Graphics
			$("#edit-prev-img").val(partner.img);
			if (partner.img == "")
				$("#edit-img").attr("src", "/img/preview.png");
			else
				$("#edit-img").attr("src", "/salons/" + <?= $yearmonth;?> + "/img/sponsor/" + partner.img);
		}
		else {
			$("#edit-prev-logo").val("");
			$("#edit-logo").attr("src", "/img/user.jpg");
			$("#edit-prev-img").val("");
			$("#edit-img").attr("src", "/img/preview.png");
		}

		$("#edit-logo-file").val("");
		$("#edit-img-file").val("");

		$("#edit-idx").val(partner.idx);
		$("#edit-sequence").val(partner.sequence);
		$("#edit-name").val(partner.name);
		$("#edit-tagline").val(partner.tagline);
		$("#edit-text").val(partner.text);
		$("#edit-website").val(partner.website);
		$("#edit-phone").val(partner.phone);
		$("#edit-email").val(partner.email);

		if (mode == "edit")
			$("#edit-update").val("Update");
		else
			$("#edit-update").val("Add");

		$("#edit-partner-modal").modal('show');
	}

	// Delete Partner
	// Handle delete button request
	function delete_partner(button) {
		let partner_idx = $(button).attr("data-idx");
		let yearmonth = '<?= $yearmonth;?>';
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the partner ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$('#loader_img').show();
				$.post("ajax/delete_partner.php", {yearmonth, partner_idx}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#partner-" + partner_idx + "-row").remove();
						swal({
								title: "Removed",
								text: "Partner has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove partner: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

	$(document).ready(function(){
		// $("#edit-partner-modal").on("shown.bs.modal", function(){
		// 	$("#edit-sections").select2({theme: 'bootstrap'});
		// 	$("#edit-role").select2({theme: 'bootstrap'});
		// });
		// Edit button
		$(".edit-partner").click(function(){
			let partner = JSON.parse($(this).attr("data-partner"));
			let idx = $(this).attr("data-idx");
			launch_edit_modal(partner, "edit", idx);
		});

		// Add button
		$("#add-partner").click(function(){
			let partner = {
					idx : 0,
					sequence : 0,
					name : "",
					logo : "",
					img : "",
					tagline : "",
					text : "",
					website : "",
					phone : "",
					email : "",
			};

			launch_edit_modal(partner, "add");
		});

		// Delete Button
		$(".delete-partner").click(function(){
			delete_partner(this);
		});
	});
</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load logo into view
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

		// Load graphics into view
		$("#edit-img-file").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#edit-img").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		function update_partner_row(partner, idx) {
			if (partner.logo == "")
				$("#logo-" + idx).attr("src", "/img/user.jpg");
			else
				$("#logo-" + idx).attr("src", "/salons/" + <?= $yearmonth;?> + "/img/sponsor/" + partner.logo);

			$("#sequence-" + idx).html(partner.sequence);
			$("#name-" + idx).html(partner.name);
			$("#tagline-" + idx).html(partner.tagline);
			$("#text-" + idx).html(partner.text);
			$("#phone-" + idx).html(partner.phone);
			$("#email-" + idx).html(partner.email);
			$("#website-" + idx).html(partner.website);
			$("#edit-partner-" + idx).attr("data-partner", JSON.stringify(partner));
			$("#delete-partner-" + idx).attr("data-idx", idx);
			$("#delete-partner-" + idx).attr("data-partner-name", partner.name);
		}

		function add_partner_row(partner) {
			let idx = $("#end-of-partner-list").attr("data-idx");
			let row = "<tr id='partner-" + idx + "-row'>";
			if (partner.logo == "")
				row += "  <td><img id='logo-" + idx + "' style='max-width: 80px;' src='/img/user.jpg' ></td>";
			else
				row += "  <td><img id='logo-" + idx + "' style='max-width: 80px;' src='/salons/" + <?= $yearmonth;?> + "/img/sponsor/" + partner.logo + "' ></td>";
			row += "  <td><span id='sequence-" + idx + "'>" + partner.sequence + "</span></td>";
			row += "  <td><span id='name-" + idx + "'>" + partner.name + "</span></td>";
			row += "  <td><span id='tagline-" + idx + "' >" + partner.tagline + "</span></td>";
			row += "  <td><span id='text-" + idx + "' >" + partner.text + "</span></td>";
			row += "  <td><span id='email-" + idx + "' >" + partner.email + "</span></td>";
			row += "  <td><span id='phone-" + idx + "' >" + partner.phone + "</span></td>";
			row += "  <td><span id='website-" + idx + "' >" + partner.website + "</span></td>";
			row += "  <td>";
			row += "    <button id='edit-partner-" + idx + "' class='btn btn-info edit-partner' name='edit-partner' ";
			row += "            data-partner='" + JSON.stringify(partner) + "'>" ;
			row += "       <i class='fa fa-edit'></i> Edit ";
			row += "    </button> ";
			row += "  </td>";
			row += "  <td>";
			row += "    <button id='delete-partner-" + idx + "' class='btn btn-danger delete-partner' name='delete-partner' ";
			row += "            data-idx='" + idx + "' data-partner-name='" + partner.name + "' >";
			row += "       <i class='fa fa-trash'></i> Delete ";
			row += "    </button>";
			row += "  </td>";
			row += "</tr>";

			$(row).insertBefore("#end-of-partner-list");
			++ idx;
			$("#end-of-partner-list").attr("data-idx", idx);

			// Edit button
			$(".edit-partner").click(function(){
				let partner = JSON.parse($(this).attr("data-partner"));
				launch_edit_modal(partner, "edit");
			});
			// Delete Button
			$(".delete-partner").click(function(){
				delete_partner(this);
			});

		}

		// Update rules back to server
		let vaidator = $('#edit-partner-form').validate({
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
				// Enforce Graphics
				if ($("#edit-img-file").val() == "" && $("#edit-prev-img").val() == "") {
					swal({
							title: "Error",
							text: "Graphics File must be uploaded",
							icon: "warning",
							confirmButtonClass: 'btn-warning',
							confirmButtonText: 'OK'
					});
					return false;
				}
				let idx = $("#edit-idx").val();
				// Assemble Data
				let formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/update_partner.php",
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
										text: "Partner details have been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Update changes to the table
								if ($("#is-edit-partner").val() == "0")
									add_partner_row(response.partner);
								else
									update_partner_row(response.partner, idx);

								$("#edit-partner-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Partner details could not be saved: " + response.msg,
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

<?php
// session_start();
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

	$role_table = [
		"Chairman" => array("role_name" => "Salon Chairman", "level" => "1", "sequence" => "1", "member_group" => "Salon Committee", "permission" => "chairman", "flags" => "allow_downloads"),
		"Secretary" => array("role_name" => "Salon Secretary", "level" => "1", "sequence" => "2", "member_group" => "Salon Committee", "permission" => "secretary", "flags" => "allow_downloads"),
		"Treasurer" => array("role_name" => "Treasurer", "level" => "5", "sequence" => "1", "member_group" => "Salon Committee", "permission" => "treasurer", "flags" => ""),
		"Reviewer" => array("role_name" => "Reviewer", "level" => "5", "sequence" => "2", "member_group" => "Salon Committee", "permission" => "reviewer", "flags" => "is_reviewer"),
		"Web Master" => array("role_name" => "Web Master", "level" => "5", "sequence" => "9", "member_group" => "Salon Committee", "permission" => "admin", "flags" => "is_reviewer,allow_downloads"),
		"Sponsorship" => array("role_name" => "Sponsorship", "level" => "5", "sequence" => "3", "member_group" => "Salon Committee", "permission" => "manager", "flags" => ""),
		"Creatives" => array("role_name" => "Creative Designs", "level" => "9", "sequence" => "2", "member_group" => "External Support", "permission" => "", "flags" => ""),
		"Catalog" => array("role_name" => "Catalog Design", "level" => "9", "sequence" => "3", "member_group" => "External Support", "permission" => "", "flags" => ""),
		"Exhibition" => array("role_name" => "Exhibition In-charge", "level" => "9", "sequence" => "5", "member_group" => "External Support", "permission" => "", "flags" => ""),
		"Media" => array("role_name" => "Media and Publicity", "level" => "9", "sequence" => "7", "member_group" => "External Support", "permission" => "", "flags" => ""),
		"Other" => array("role_name" => "Salon Support", "level" => "9", "sequence" => "11", "member_group" => "External Support", "permission" => "", "flags" => ""),
		"Mentor" => array("role_name" => "Salon Mentor", "level" => "9", "sequence" => "99", "member_group" => "External Support", "permission" => "", "flags" => ""),
	];

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
						Manage Salon Committee
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="team.php" enctype="multipart/form-data" >
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
				if ($yearmonth != 0) {
			?>
			<div class="row">
				<!-- Provision for Filters -->
				<div style="display:inline-block; margin-left:15px;">
					<div>
						<div style="padding: 4px;" >
							<button class="btn btn-info" name="add-member" id="add-member"><i class="fa fa-plus-circle"></i> Add Committee Member</button>
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
					<table id="member_table" class="table" >
						<thead>
							<tr>
								<th>Avatar</th>
								<th>Member Name</th>
								<th>Login</th>
								<th>Role</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Honors</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php
							// Default Query
							$sql  = "SELECT * FROM team ";
							$sql .= "  WHERE yearmonth = '$yearmonth' ORDER BY level, sequence ";
							$mquery = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while($member = mysqli_fetch_array($mquery, MYSQLI_ASSOC)) {
								$id = $member['member_id'];
							?>
							<tr id="member-<?= $id;?>-row">
								<td><img id="avatar-<?= $id;?>" style="max-width: 80px;" src="/salons/<?= $yearmonth;?>/img/com/<?= $member['avatar'];?>"></td>
								<td><span id="member-name-<?= $id;?>"><?= $member['member_name'];?></span></td>
								<td><span id="login-<?= $id;?>" class="member-login" data-member-id="<?= $member['member_id'];?>" data-member-login="<?= $member['member_login_id'];?>" ><?= $member['member_login_id'];?></span></td>
								<td><span id="role-<?= $id;?>" ><?= $member['role_name'];?></span></td>
								<td><span id="email-<?= $id;?>"><?= $member['email'];?><span></td>
								<td><span id="phone-<?= $id;?>"><?= $member['phone'];?><span></td>
								<td><span id="honors-<?= $id;?>"><?= $member['honors'];?></span></td>
								<td>
									<button id="edit-member-<?= $id;?>" class="btn btn-info edit-member" name="edit-member"
											data-member='<?= json_encode($member, JSON_FORCE_OBJECT);?>'>
										<i class="fa fa-edit"></i> Edit
									</button>
								</td>
								<td>
									<button id="delete-member-<?= $id;?>" class="btn btn-danger delete-member" name="delete-member"
											data-member-id='<?= $id;?>' data-yearmonth="<?= $member['yearmonth'];?>">
										<i class="fa fa-trash"></i> Delete
									</button>
								</td>
							</tr>
						<?php
							}
						?>
							<tr id="end-of-member-list">
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
		<!-- Edit Member -->
		<div class="modal" id="edit-member-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Member</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-member-form" name="edit-member-form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="member_id" id="edit-member-id" >
							<input type="hidden" name="prev_avatar" id="edit-prev-avatar" >
							<input type="hidden" name="level" id="edit-level">
							<input type="hidden" name="sequence" id='edit-sequence'>
							<input type="hidden" name="member_group" id="edit-member-group">
							<input type="hidden" name="permissions" id="edit-permissions">
							<input type="hidden" name="address" id="edit-address" >
							<input type="hidden" name="profile" value="" >
							<input type="hidden" name="is_edit_member" id="is-edit-member" value="0" >

							<!-- Edited Fields -->
							<!-- Avatar -->
							<div class="row form-group">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12"><b>Avatar</b></div>
										<div class="col-sm-3"><img src="/res/user.jpg" id="edit-member-avatar" style="max-width: 120px; max-height: 120px;"></div>
										<div class="col-sm-9">
											<label for="edit-avatar-file">Select Avatar</label>
											<input type="file" name="avatar" id="edit-avatar-file" >
										</div>
									</div>
								</div>
							</div>
							<!-- Name -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-member-name">Member Name</label>
									<input type="text" name="member_name" class="form-control" id="edit-member-name" placeholder="Member name..." required >
								</div>
							</div>
							<!-- Login -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-login">Login</label>
									<input type="text" name="member_login_id" class="form-control" id="edit-member-login-id" placeholder="Unique Login..." >
								</div>
								<div class="col-sm-6">
									<label for="edit-password">Password</label>
									<div class="input-group">
										<input type="text" name="member_password" class="form-control" id="edit-member-password" placeholder="Password..." >
										<span class="input-group-btn">
											<button class="btn btn-info" type="button" id="edit-generate" > Generate </button>
										</span>
									</div>
								</div>
							</div>
							<!-- Email -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-email">Email</label>
									<input type="email" name="email" class="form-control" id="edit-email" placeholder="Email..." required>
								</div>
							</div>
							<!-- Honors -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-honors">Honors</label>
									<input type="text" name="honors" class="form-control" id="edit-honors" placeholder="Honors..." >
								</div>
							</div>
							<!-- Honors -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-honors">Phone</label>
									<input type="phone" name="phone" class="form-control" id="edit-phone" placeholder="+91-..." >
								</div>
							</div>
							<!-- Internal Fields -->
							<!-- Role -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-role">Role</label>
									<select class="form-control" name="role" id="edit-role" required >
									<?php
										foreach($role_table as $role => $props) {
											$role_name = ($props['member_group'] == "Salon Committee" ? "*" : "&gt;") . $props['role_name'];
									?>
										<option value="<?= $role;?>"><?= $role_name;?></option>
									<?php
										}
									?>
										<!-- <option value="Chairman">*Salon Chairman</option>
										<option value="Secretary">*Salon Secretary</option>
										<option value="Treasurer">*Treasurer</option>
										<option value="Reviewer">*Reviewer</option>
										<option value="Web Master">*Web Master</option>
										<option value="Sponsorship">&gt;Sponsorship</option>
										<option value="Creatives">&gt;Creatives</option>
										<option value="Catalog">&gt;Catalog</option>
										<option value="Exhibition">&gt;Exhibition</option>
										<option value="Media">&gt;Media</option>
										<option value="Other">&gt;Other</option>
										<option value="Mentor">&gt;Mentor</option> -->
									</select>
									<p><small><span style="padding-right: 30px;">* - Salon Committee</span>    &gt; - External Support</small></p>
								</div>
							</div>
							<!-- Role Name -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-role-name">Role Name</label>
									<input type="text" name="role_name" class="form-control" id="edit-role-name" required >
								</div>
							</div>
							<!-- Sections assigned to Reviewers -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-sections">Review Sections</label>
									<select class="form-control" name="sections[]" id="edit-sections" multiple="multiple" >
									<?php
										$sql  = "SELECT section FROM section WHERE yearmonth = '$yearmonth' ORDER BY section_type, section_sequence ";
										$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($row = mysqli_fetch_array($query)) {
									?>
										<option value="<?= $row['section'];?>" ><?= $row['section'];?></option>
									<?php
										}
									?>
									</select>
								</div>
							</div>
							<!-- Flags -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-flags">Flags</label>
								</div>
								<div class="col-sm-6">
									<div class="input-group form-control">
										<span class="input-group-addon">
											<input type="checkbox" name="is_reviewer" id="edit-is-reviewer" value="1">
										</span>
										<input type="text" class="form-control" value="Reviewer" readonly>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="input-group form-control">
										<span class="input-group-addon">
											<input type="checkbox" name="is_print_coordinator" id="edit-is-print-coordinator" value="1">
										</span>
										<input type="text" class="form-control" value="Print Coordinator" readonly>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="input-group form-control">
										<span class="input-group-addon">
											<input type="checkbox" name="allow_downloads" id="edit-allow-downloads" value="1">
										</span>
										<input type="text" class="form-control" value="Allow Downloads" readonly>
									</div>
								</div>
							</div>
							<!-- Print Coordinator Address -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-role-name">Print Co-ordinator Address</label>
									<input type="text" class="form-control" name="address[]" id="edit-address-1" >
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" name="address[]" id="edit-address-2" >
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" name="address[]" id="edit-address-3" >
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" name="address[]" id="edit-address-4" >
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" name="address[]" id="edit-address-5" >
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
	var role_table = JSON.parse('<?= json_encode($role_table, JSON_FORCE_OBJECT);?>');

	function handle_role_change(role_select) {
		let role = $(role_select).val();
		if (! (role_table[role] == undefined)) {
			$("#edit-level").val(role_table[role].level);
			$("#edit-sequence").val(role_table[role].sequence);
			$("#edit-member-group").val(role_table[role].member_group);
			$("#edit-permissions").val(role_table[role].permission);
			$("#edit-role-name").val(role_table[role].role_name);
			if (role_table[role].flags != "") {
				let flags = role_table[role].flags.split(",");
				for (let flag of flags) {
					switch(flag) {
						case "is_reviewer" : {
							$("#edit-is-reviewer").prop("checked", true);
							break;
						}
						case "allow_downloads" : {
							$("#edit-allow-downloads").prop("checked", true);
							break;
						}
					}
				}
			}
		}
	}
	// Fetch Member data and launch edit dialog
	function launch_edit_modal(member, mode) {
		$("#is-edit-member").val(mode == "edit" ? "1" : "0");
		$("#edit-member-id").val(member.member_id);
		$("#edit-prev-avatar").val(member.avatar);
		if (member.avatar == "")
			$("#edit-member-avatar").attr("src", "/res/jury/user.jpg");
		else
			$("#edit-member-avatar").attr("src", "/salons/" + member.yearmonth + "/img/com/" + member.avatar);
		$("#edit-avatar-file").val("");
		$("#edit-member-login-id").val(member.member_login_id);
		$("#edit-member-password").val(member.member_password);
		$("#edit-level").val(member.level);
		$("#edit-sequence").val(member.sequence);
		$("#edit-role").val(member.role);
		$("#edit-role-name").val(member.role_name);
		$("#edit-member-group").val(member.member_group);
		$("#edit-member-name").val(member.member_name);
		$("#edit-honors").val(member.honors);
		$("#edit-phone").val(member.phone);
		$("#edit-email").val(member.email);
		$("#edit-permissions").val(member.permissions);
		$("#edit-sections").val(member.sections.split("|"));
		$("#edit-is-reviewer").attr("checked", member.is_reviewer == "1");
		$("#edit-is-print-coordinator").attr("checked", member.is_print_coordinator == "1");
		$("#edit-allow-downloads").attr("checked", member.allow_downloads == "1");
		let address = member.address.split("|");
		for (let i = 0; i < address.length; ++ i) {
			$("#edit-address-" + (i + 1)).val(address[i]);
		}

		if (mode == "edit")
			$("#edit-update").val("Update");
		else
			$("#edit-update").val("Add");

		// Set up role handler
		$("#edit-role").on("change", function(){
			handle_role_change(this);
		});

		$("#edit-member-modal").modal('show');
	}

	// Delete Member
	// Handle delete button request
	function delete_member(button) {
		let member_id = $(button).attr("data-member-id");
		let yearmonth = $(button).attr("data-yearmonth");
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the member ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$('#loader_img').show();
				$.post("ajax/delete_team.php", {yearmonth, member_id}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						$("#member-" + member_id + "-row").remove();
						swal({
								title: "Removed",
								text: "Member has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove member: " + response.msg,
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
		$("#edit-member-modal").on("shown.bs.modal", function(){
			$("#edit-sections").select2({theme: 'bootstrap'});
			$("#edit-role").select2({theme: 'bootstrap'});
		});
		// Edit button
		$(".edit-member").click(function(){
			let member = JSON.parse($(this).attr("data-member"));
			launch_edit_modal(member, "edit");
		});

		// Add button
		$("#add-member").click(function(){
			let member = {
					member_id : 0,
					avatar : "",
					member_login_id : "",
					member_password : "",
					level : "0",
					sequence : "0",
					role : "",
					role_name : "",
					member_group : "",
					member_name : "",
					honors : "",
					phone : "",
					email : "",
					permissions : "",
					sections : "",
					is_reviewer : "0",
					is_print_coordinator : "0",
					allow_downloads : "",
					address : "",
			};

			launch_edit_modal(member, "add");
		});

		// Delete Button
		$(".delete-member").click(function(){
			delete_member(this);
		});
	});
</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load picture into view
		$("#edit-avatar-file").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#edit-member-avatar").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		// Generate Password
		$("#edit-generate").click(function(){
			let name = $("#edit-member-name").val();
			// Remove spaces, numbers and vowels from the name and assemble in reverse order
			let password = name.match(/[^ aeiou0-9]/g).reduce(function(pwd, match){ return match + pwd;}).toLowerCase();
			// Reduce to 5 chars
			if (password.length > 5)
				password = password.substring(0, 5);
			// Add 3 digit random number
			let digits = (Math.random() * 1000).toFixed(0);
			while (digits.length < 3)
				digits = "0" + digits;
			password += digits;
			// Assign to Password
			$("#edit-member-password").val(password);
		});

		function update_member_row(member) {
			let id = member.member_id;
			if (member.avatar == "")
				$("#avatar-" + id).attr("src", "/res/jury/user.jpg");
			else
				$("#avatar-" + id).attr("src", "/salons/" + member.yearmonth + "/img/com/" + member.avatar);
			$("#member-name-" + id).html(member.member_name);
			$("#login-" + id).html(member.member_login_id);
			$("#role-" + id).html(member.role_name);
			$("#email-" + id).html(member.email);
			$("#phone-" + id).html(member.phone);
			$("#honors-" + id).html(member.honors);
			$("#edit-member-" + id).attr("data-member", JSON.stringify(member));
			$("#delete-member-" + id).attr("data-member", JSON.stringify(member));
		}

		function add_member_row(member) {
			let id = member.member_id;
			let row = "<tr id='member-" + id + "-row'>";
			if (member.avatar == "")
				row += "  <td><img id='avatar-" + id + "' style='max-width: 80px;' src='/res/jury/user.jpg' ></td>";
			else
				row += "  <td><img id='avatar-" + id + "' style='max-width: 80px;' src='/salons/" + member.yearmonth + "/img/com/" + member.avatar + "' ></td>";
			row += "  <td><span id='member-name-" + id + "'>" + member.member_name + "</span></td>";
			row += "  <td><span id='login-" + id + "' class='member-login' data-member-id='" + member.member_id + " data-member-login='" + member.member_login_id + "' >" + member.member_login_id + "</span></td>";
			row += "  <td><span id='role-" + id + "' >" + member.role_name + "</span></td>";
			row += "  <td><span id='email-" + id + "' >" + member.email + "</span></td>";
			row += "  <td><span id='phone-" + id + "' >" + member.phone + "</span></td>";
			row += "  <td><span id='honors-" + id + "' >" + member.honors + "</span></td>";
			row += "  <td>";
			row += "    <button id='edit-member-" + id + "' class='btn btn-info edit-member' name='edit-member' ";
			row += "            data-member='" + JSON.stringify(member) + "'>" ;
			row += "       <i class='fa fa-edit'></i> Edit ";
			row += "    </button> ";
			row += "  </td>";
			row += "  <td>";
			row += "    <button id='delete-member-" + id + "' class='btn btn-danger delete-member' name='delete-member' ";
			row += "            data-member-id='" + id + "' data-yearmonth='" + member.yearmonth + "' >";
			row += "       <i class='fa fa-trash'></i> Delete ";
			row += "    </button>";
			row += "  </td>";
			row += "</tr>";

			$(row).insertBefore("#end-of-member-list");

			$("#edit-sections").select2({theme: 'bootstrap'});
			$("#edit-role").select2({theme: 'bootstrap'});

			// Edit button
			$(".edit-member").click(function(){
				let member = JSON.parse($(this).attr("data-member"));
				launch_edit_modal(member, "edit");
			});
			// Delete Button
			$(".delete-member").click(function(){
				delete_member(this);
			});

		}

		// Update rules back to server
		let vaidator = $('#edit-member-form').validate({
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
				// Validate Login ID for uniqueness
				if ($("#edit-login").val() != "") {
					let member_id = $("#edit-member-id").val();
					let login = $("#edit-login").val();
					let other_logins = $(".member-login[data-member-id!='" + member_id + "'][data-member-login='" + login + "']").length;
					if (other_logins > 0) {
						swal({
								title: "Error",
								text: "The login is not unique",
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
						return false;
					}
				}
				// Assemble Data
				let formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/update_team.php",
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
										text: "Member details have been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Update changes to the table
								if ($("#is-edit-member").val() == "0")
									add_member_row(response.member);
								else
									update_member_row(response.member);

								$("#edit-member-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Member details could not be saved: " + response.msg,
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

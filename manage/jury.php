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
						Manage Jury
					</h3>
					<div class="row">
						<!-- Provision for Filters -->
						<div style="display:inline-block; margin-left:15px;">
							<div>
								<div style="padding: 4px;" >
									<button class="btn btn-info" name="add-jury" id="add-jury"><i class="glyphicon glyphicon-plus small"></i> Add Jury</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="content">
			<div class="row">
				<div class="col-sm-4">
					<label for="user_id">Select Jury</label>
					<div class="input-group form-control">
						<select class="form-control" name="user_id" id="select-jury" >
						<?php
							$sql  = "SELECT user_id, user_name FROM user WHERE type = 'JURY' ORDER BY user_id ";
							$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while ($row = mysqli_fetch_array($query)) {
						?>
							<option value="<?= $row['user_id'];?>" ><?= $row['user_name'];?></option>
						<?php
							}
						?>
						</select>
						<span class="input-group-btn">
							<button class="btn btn-info" id="find-jury" ><i class="fa fa-search"></i> FIND </a>
						</span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<table id="user_table" class="table" >
						<thead>
							<tr>
								<th>Avatar</th>
								<th>Jury Name</th>
								<th>Login</th>
								<th>Email</th>
								<th>Honors</th>
								<th>Status</th>
								<th>Edit</th>
								<th>Profile</th>
							</tr>
						</thead>
						<tbody>
						<?php
							// Default Query
							$sql  = "SELECT user_id, user_name, login, password, type, avatar, title, honors, email, profile_file, status ";
							$sql .= "  FROM user WHERE type = 'JURY' ORDER BY user_id ";
							$jquery = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
							while($jury = mysqli_fetch_array($jquery, MYSQLI_ASSOC)) {
								$id = $jury['user_id'];
							?>
							<tr>
								<td><img id="avatar-<?= $id;?>" style="max-width: 80px;" src="/res/jury/<?= $jury['avatar'];?>"></td>
								<td><span id="user-name-<?= $id;?>"><?= $jury['user_name'];?></span></td>
								<td><span id="login-<?= $id;?>" class="jury-login" data-user-id="<?= $jury['user_id'];?>" data-user-login="<?= $jury['login'];?>" ><?= $jury['login'];?></span></td>
								<td><span id="email-<?= $id;?>"><?= $jury['email'];?><span></td>
								<td><span id="honors-<?= $id;?>"><?= $jury['honors'];?></span></td>
								<td><span id="status-<?= $id;?>"><?= $jury['status'];?></span></td>
								<td>
									<button id="edit-user-<?= $id;?>" class="btn btn-info edit-user" name="edit-user"
											data-user='<?= json_encode($jury, JSON_FORCE_OBJECT);?>'>
										<i class="fa fa-edit"></i> Edit
									</button>
								</td>
								<td>
									<button id="edit-profile-<?= $id;?>" class="btn btn-info edit-profile" name="edit-profile"
											data-blob="<?= $jury['profile_file'] == '' ? $jury['login'] . '.htm' : $jury['profile_file'];?>"
											data-user='<?= json_encode($jury, JSON_FORCE_OBJECT);?>'>
										<i class="fa fa-file"></i> Profile
									</button>
								</td>
							</tr>
						<?php
							}
						?>
							<tr id="end-of-user-list">
								<td></td>
								<td>End of List</td>
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
		<!-- Edit Jury -->
		<div class="modal" id="edit-user-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit User</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-user-form" name="edit-user-form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" name="user_id" id="edit-user-id" >
							<input type="hidden" name="prev_avatar" id="edit-prev-avatar" >

							<!-- Edited Fields -->
							<!-- Avatar -->
							<div class="row form-group">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12"><b>Avatar</b></div>
										<div class="col-sm-3"><img src="/res/jury/user.jpg" id="edit-user-avatar" style="max-width: 120px; max-height: 120px;"></div>
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
									<label for="edit-user-name">Jury Name</label>
									<input type="text" name="user_name" class="form-control text-uppercase" id="edit-user-name" placeholder="Jury name..." required >
								</div>
							</div>
							<!-- Login -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-login">Login</label>
									<input type="text" name="login" class="form-control" id="edit-login" placeholder="Unique Login..." required >
								</div>
								<div class="col-sm-6">
									<label for="edit-password">Password</label>
									<div class="input-group">
										<input type="text" name="password" class="form-control" id="edit-password" placeholder="Password..." required>
										<span class="input-group-btn">
											<button class="btn btn-info" type="button" id="edit-generate" > Generate </button>
										</span>
									</div>
								</div>
							</div>
							<!-- Email -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-title">Title</label>
									<input type="text" name="title" class="form-control" id="edit-title" placeholder="Title..." >
								</div>
							</div>
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-email">Email</label>
									<input type="email" name="email" class="form-control" id="edit-email" placeholder="Email..." required>
								</div>
							</div>
							<!-- Email -->
							<div class="row form-group">
								<div class="col-sm-12">
									<label for="edit-honors">Honors</label>
									<input type="text" name="honors" class="form-control" id="edit-honors" placeholder="Honors..." >
								</div>
							</div>
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-profile-file">Profile HTML</label>
									<input type="text" name="profile_file" class="form-control" id="edit-profile-file" placeholder="xxxx.htm" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-status">Status</label>
									<div class="input-group">
										<span class="input-group-addon">
											<input type="checkbox" name="status" id="edit-status" value="ACTIVE" >
										</span>
										<input type="text" class="form-control" readonly value="ACTIVE" >
									</div>
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
		<!-- Edit Contest Rules -->
		<div class="modal" id="edit-blob-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header" style="padding-top: 15px; padding-bottom: 15px;">
						<div class="row form-group">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Editing <span id="blob_file_name">Blob</span></small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-blob-form" name="edit_blob_form" action="#" enctype="multipart/form-data" >
							<input type="hidden" name="blob_file" id="blob-file" value="">
							<!-- Rules text area -->
							<div class="row form-group">
								<div class="col-sm-12">
									<textarea name="blob_content" class="form-control" id="blob-content" >Loading Content...</textarea>
								</div>
							</div>
							<br><br>
							<div class="row form-group">
								<div class="col-sm-9">
									<input class="btn btn-primary pull-right" type="submit" id="blob-save" name="blob_save" value="Save">
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
		$("#select-jury").select2({theme: 'bootstrap'});

		$("#find-jury").click(function(){
			document.getElementById("avatar-" + $("#select-jury").val()).scrollIntoView(false);
		})
	});
</script>

<!-- Action Handlers -->
<script>
	// Fetch jury data and launch edit dialog
	function launch_edit_modal(user) {
		$("#edit-user-id").val(user.user_id);
		$("#edit-prev-avatar").val(user.avatar);
		$("#edit-user-avatar").attr("src", "/res/jury/" + user.avatar);
		$("#edit-avatar-file").val("");
		$("#edit-user-name").val(user.user_name);
		$("#edit-login").val(user.login);
		$("#edit-password").val(user.password);
		$("#edit-email").val(user.email);
		$("#edit-title").val(user.title);
		$("#edit-honors").val(user.honors);
		$("#edit-profile-file").val(user.profile_file);
		$("#edit-status").prop("checked", user.jury_id == 0 || user.status == "ACTIVE");

		if (user.jury_id == 0)
			$("#edit-update").val("Add");
		else
			$("#edit-update").val("Update");

		$("#edit-user-modal").modal('show');
	}

	$(document).ready(function(){
		// Edit button
		$(".edit-user").click(function(){
			let user = JSON.parse($(this).attr("data-user"));
			launch_edit_modal(user);
		});
		// Add button
		$("#add-jury").click(function(){
			let jury = {
					jury_id : 0,
					name : "",
					login : "",
					password : "",
					avatar : "user.png",
					email : "",
					status : 1,
			};
			launch_edit_modal(jury);
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
					$("#edit-user-avatar").attr("src", e.target.result);
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
			let name = $("#edit-user-name").val();
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
			$("#edit-password").val(password);
		});

		function update_user_row(user) {
			$("#avatar-" + user.user_id).attr("src", "/res/jury/" + user.avatar);
			$("#user-name-" + user.user_id).html(user.user_name);
			$("#login-" + user.user_id).html(user.login);
			$("#login-" + user.user_id).attr("data-user-login", user.login);
			$("#email-" + user.user_id).html(user.email);
			$("#honors-" + user.user_id).html(user.honors);
			$("#status-" + user.user_id).html(user.status);
			$("#edit-user-" + user.user_id).attr("data-user", JSON.stringify(user));
			$("#edit-profile-" + user.user_id).attr("data-user", JSON.stringify(user));
			$("#edit-profile-" + user.user_id).attr("data-blob", (user.profile_file == "" ? user.login + ".htm" : user.profile ) );
		}

		function add_user_row(user) {
			let row = "<tr>";
			row += "<td><img id='avatar-" + user.user_id + "' style='max-width: 80px;' src='/res/jury/" + user.avatar +"' ></td> ";
			row += "<td><span id='user-name-" + user.user_id + "'>" + user.user_name + "</span></td> ";
			row += "<td><span id='login-" + user.user_id + "' class='jury-login' data-user-id='" + user.user_id + "' data-user-login='" + user.login + "' >" + user.login + "</span></td> ";
			row += "<td><span id='email-" + user.user_id + "'>" + user.email + "<span></td> ";
			row += "<td><span id='honors-" + user.user_id + "'>" + user.honors + "</span></td> ";
			row += "<td><span id='status-" + user.user_id + "'>" + user.status + "</span></td> ";
			row += "<td>";
			row += "  <button id='edit-user-" + user.user_id + "' class='btn btn-info edit-user' name='edit-user' ";
			row += "    data-user='" + JSON.stringify(user) + "' > ";
			row += "    <i class='fa fa-edit'></i> Edit ";
			row += "  </button> ";
			row += "</td>";
			row += "<td>";
			row += "  <button id='edit-profile-" + user.user_id + "' class='btn btn-info edit-profile' name='edit-profile' ";
			row += "    data-blob='" + (user.profile_file == "" ? user.login + ".htm" : user.profile_file ) + "'";
			row += "    data-user='" + JSON.stringify(user) + "'> ";
			row += "    <i class='fa fa-file'></i> Profile ";
			row += "  </button> ";
			row += "</td>";
			row += "</tr>"

			$(row).insertBefore("#end-of-user-list");

			// Set handler again
			$(".edit-profile").click(function(){ edit_blob(this);})
			// Edit button
			$(".edit-user").click(function(){
				let user = JSON.parse($(this).attr("data-user"));
				launch_edit_modal(user);
			});

		}

		// Update rules back to server
		let vaidator = $('#edit-user-form').validate({
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
				let user_id = $("#edit-user-id").val();
				let login = $("#edit-login").val();
				let other_logins = $(".user-login[data-user-id!='" + user_id + "'][data-user-login='" + login + "']").length;
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
				// Assemble Data
				let formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/update_jury.php",
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
										text: "Jury details have been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Update changes to the table
								if (user_id == 0)
									add_user_row(response.user);
								else
									update_user_row(response.user);

								$("#edit-user-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Jury details could not be saved: " + response.msg,
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

<!-- BLOB EDIT SUPPORT -->
<!-- tinymce editor -->
<script src='plugin/tinymce/tinymce.min.js'></script>
<script src='plugin/tinymce/plugins/link/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/lists/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/image/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/table/plugin.min.js'></script>

<script>
	function edit_blob(btn) {
		$("#blob-file").val($(btn).attr("data-blob"));
		$("#blob-file-name").html($(btn).attr("data-blob"));
		tinymce.get("blob-content").setContent("Loading content...");
		$("#edit-blob-modal").modal("show");
	}

	$(document).ready(function(){
		// Init
		tinymce.init({
			selector: '#blob-content',
			height: 600,
			plugins : 'link lists image table',
		});

		// Event handle to load Blob Modal
		$(".edit-profile").click(function(){ edit_blob(this);})
		// $(".edit-profile").click(function(e){
		// 	$("#blob-file").val($(this).attr("data-blob"));
		// 	$("#blob-file-name").html($(this).attr("data-blob"));
		// 	tinymce.get("blob-content").setContent("Loading content...");
		// 	// }
		// 	$("#edit-blob-modal").modal("show");
		// });
	});
</script>

<!-- Edit Rules Action Handlers -->
<script>
	// Get Rules Text from server
	$(document).ready(function(){
		$("#edit-blob-modal").on("shown.bs.modal", function(){
			// Load Rules from file on server
			let login = $("#")
			let blob_file = $("#blob-file").val();
			$('#loader_img').show();
			$.post("ajax/get_jury_blob.php", {blob_file}, function(response){
				$('#loader_img').hide();
				response = JSON.parse(response);
				if(response.success){
					// $("#er_contest_rules").val(response.rules);
					tinymce.get("blob-content").setContent(response.blob_content);
				}
				else{
					swal({
							title: "Load Failed",
							text: "Unable to load blob: " + response.msg,
							icon: "warning",
							confirmButtonClass: 'btn-warning',
							confirmButtonText: 'OK'
					});
				}
			});
		});

		// Update rules back to server
		let vaidator = $('#edit-blob-form').validate({
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
				// Assemble Data
				let formData = new FormData();
				formData.append("blob_file", $("#blob-file").val());
				formData.append("blob_content", tinymce.get("blob-content").getContent());

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_jury_blob.php",
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
										title: "Blob Saved",
										text: $("#blob-file").val() + " has been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								$("#edit-blob-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: $("#blob-file").val() + " could not be saved: " + response.msg,
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
	header("Location: " . $_SERVER['HTTP_REFERER']);
	print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	//header("Location: /jurypanel/index.php");
	//printf("<script>location.href='/jurypanel/index.php'</script>");
}

?>

<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");


// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
// if(isset($_SESSION['jury_id']) && ($_SESSION['jury_type'] == "MASTER" || $_SESSION['jury_type'] == "ADMIN")) {
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Determine current contest eligible for jury assignment
	if ($admin_contest['results_ready'] != '0') {
		$_SESSION['err_msg'] = "There are no open contests to which Jury can be assigned !";
		header("Location: " . $_SERVER['HTTP_REFERER']);
		print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	}

	// Set up user fields
	if(isset($_REQUEST['edit_id'])) {
		$editUserId = $_REQUEST['edit_id'];
		$sql = "SELECT * FROM user WHERE user_id = '$editUserId'";
		$sql_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($sql_query);
		$editLogin = $row["login"];
		$editName = $row["user_name"];
		$editType = $row["type"];
		$editPassword = $row["password"];
		$editTitle = $row["title"];
		$editHonors = $row["honors"];
		$editProfileFile = $row["profile_file"];
		$editProfile = file_get_contents("../blob/jury/". $editProfileFile);
		$editStatus = $row["status"];
		$editAvatar = $row['avatar'];
	}
	else {
		$editUserId = "";
		$editLogin = "";
		$editName = "";
		$editType = "";
		$editPassword = "";
		$editTitle = "";
		$editHonors = "";
		$editProfile = "";
		$editProfileFile = "";
		$editStatus = "";
		$editAvatar = "user.jpg";
	}


?>
<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Jury Administration Panel</title>

	<?php include "inc/header.php"; ?>

	<!-- Scripts for TinyMCE -->
	<script src='plugin/tinymce/tinymce.min.js'></script>
	<script src='plugin/tinymce/plugins/link/plugin.min.js'></script>
	<script src='plugin/tinymce/plugins/lists/plugin.min.js'></script>

	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />

</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YOUTH PHOTOGRAPHIC SOCIETY | ADMIN PANEL  </h1>
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
		<div class="content">
			<div class="row">
				<!-- JURY ADD/EDIT SCREEN -->
				<div class="col-md-6 col-lg-6">
					<div class="hpanel">
						<div class="panel-body">
							<div class="text-center m-b-md">
								<h3>Add Jury</h3>
							</div>
							<form action="op/add_user.php" id="loginForm" method="post" enctype="multipart/form-data">
								<input type="hidden" name="avatar" value="$editAvatar" />
								<input type="hidden" name="profile_file" value="<?=$editProfileFile;?>" />
								<div class="row">
								<?php
									if(isset($_REQUEST['edit_id'])) {
								?>
									<div class="form-group col-lg-12">
										<label>ID</label>
										<input type="text" class="form-control" name="user_id" value="<?php echo $editUserId;?>" readonly >
									</div>
								<?php
									}
								?>
									<div class="form-group col-lg-12">
										<label>User Type</label>
										<select class="form-control" name="type" required>
											<option value="ADMIN" <?php echo ($editType == "ADMIN") ? "selected" : "";?>>ADMIN</option>
											<option value="JURY" <?php echo ($editType == "JURY") ? "selected" : "";?>>JURY</option>
										</select>
									</div>
									<div class="form-group col-lg-12">
										<label>Name</label>
										<input type="text" class="form-control" name="name" value="<?php echo $editName;?>"required>
									</div>
									<div class="form-group col-lg-12">
										<label>Login Name</label>
										<input type="text"  class="form-control" name="login" value="<?php echo $editLogin;?>" required>
									</div>
									<div class="form-group col-lg-12">
										<label>Password</label>
										<input type="password"  class="form-control" name="password" <?php echo (isset($_REQUEST['edit_id'])) ? '' : 'required'; ?> >
									</div>
									<div class="form-group col-lg-12">
										<label>Title</label>
										<input type="text"  class="form-control" name="title" value="<?php echo $editTitle;?>">
									</div>
									<div class="form-group col-lg-12">
										<label>Honors</label>
										<input type="text"  class="form-control" name="honors" value="<?php echo $editHonors;?>">
									</div>
									<div class="form-group col-lg-12">
										<label>Profile</label>
										<textarea rows="8" class="form-control html-editor" name="profile" ><?php echo $editProfile;?></textarea>
									</div>
									<div class="clearfix"></div>
									<div class="form-group col-lg-10">
										<label>Upload Profile Picture (180 pixels)</label>
										<input type="file" class="form-control" name="picture" onchange="loadPhoto(this);" >
									</div>
								<?php
									// if(isset($_REQUEST['edit_id'])) {
								?>
									<div class="form-group col-lg-2">
										<img src="../res/jury/<?php echo $editAvatar;?>" id="avatar_preview" style="height: 75px; border: 1px solid #CCC;">
									</div>
								<?php
									// }
								?>
									<div class="clearfix"></div>
									<!--
									<div class="form-group col-lg-12">
										<label>Upload Avatar (75 pixels)</label>
										<input type="file" class="form-control" name="avatar">
									</div>
									-->
								</div>
								<div class="text-center">
								<?php
									if(isset($_REQUEST['edit_id'])) {
								?>
									<button class="btn btn-success" type="submit" name="update_user">Update</button>
								<?php
									}
									else {
								?>
									<button class="btn btn-success" type="submit" name="new_user">Add User</button>
								<?php
									}
								?>
									<a href="./user_admin.php"><button class="btn btn-default" type="button">Cancel</button></a>
								</div>
							</form>
						</div>
					</div>
				</div>		<!-- END of JURY ADD/EDIT Screen -->

				<!-- JURY LIST -->
				<div class="col-md-6 col-lg-6">
					<div class="hpanel">
						<div class="panel-body">
							<div class="text-center m-b-md">
								<h3>Jury/Admin Accounts</h3>
							</div>
							<table id="example2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Jury ID</th><th>Type</th><th>Name</th><th>Login</th><th>Password</th><th>Status</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$sql = "SELECT * FROM user WHERE type = 'ADMIN' OR type = 'JURY'";
								$users = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while($tr_users = mysqli_fetch_array($users)) {
							?>
								<tr>
									<td>
										<a href="user_admin.php?edit_id=<?php echo $tr_users['user_id'];?>" method="post">
											<?php echo $tr_users['user_id'] . " ";?><i class="ace-icon fa fa-edit"></i>
										</a>
									</td>
									<td><?php echo $tr_users['type'];?></td>
									<td><?php echo $tr_users['user_name'];?></td>
									<td><?php echo $tr_users['login'];?></td>
									<td><?php echo $tr_users['password'];?></td>
									<td>
										<a href="op/delete_user.php?delete_id=<?php echo $tr_users['user_id'];?>" method="post"
												onclick="return confirm('Are you sure to DELETE this Jury/Admin');"
												title="Delete">
											<?php echo $tr_users['status'] . " "; ?><i class="ace-icon fa fa-trash"></i>
										</a>
									</td>
								</tr>
							<?php
								}
							?>
							</tbody>
							</table>
						</div>
					</div>
				</div>		<!-- END OF JURY LIST -->

				<!-- JURY ASSIGH -->
				<div class="col-md-6 col-lg-6">
					<div class="hpanel">
						<div class="panel-body">
							<div class="text-center m-b-md">
								<h3>Assign Jury to Section</h3>
							</div>
							<form action="op/jury_assign.php" id="assignment_form" method="post" onsubmit="return validate_form()" enctype="multipart/form-data">
								<div class="form-group col-lg-12 col-md-12" id="assignments">
									<label>Jury Assignments</label>
									<div class="row">
										<div class="form-group col-lg-5 col-md-5 col-sm-5 col-xs-5"><b>Section</b></div>
										<div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-2"><b>#</b></div>
										<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-4"><b>Jury</b></div>
										<div class="form-group col-lg-1 col-md-1 col-sm-1 col-xs-1">-</div>
									</div>
							<?php
								$idx = 0;
								// Load Current Assignments
								$sql  = "SELECT * FROM assignment, section, user ";
								$sql .= " WHERE assignment.yearmonth = '$admin_yearmonth' ";
								$sql .= "   AND section.yearmonth = assignment.yearmonth ";
								$sql .= "   AND section.section = assignment.section ";
								$sql .= "   AND user.user_id = assignment.user_id ";
								$sql .= " ORDER BY assignment.section, jurynumber";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
									<div class="row" data-row-type="delete" id="assignment[<?php echo $idx;?>]">
										<!-- Hidden field to hold the value of the hidden SELECT -->
										<input type="hidden" data-hidden="section" name="section[<?php echo $idx;?>]" value="<?php echo $row['section'];?>" >
										<input type="hidden" data-hidden="user_id" name="user_id[<?php echo $idx;?>]" value="<?php echo $row['user_id'];?>" >
										<div class="form-group col-lg-5 col-md-5 col-sm-5 col-xs-5">
											<select class="form-control" data-visible="section" name="section[<?php echo $idx;?>]" id="section[<?php echo $idx;?>]" disabled >
												<option value="<?php echo $row['section'];?>"><?php echo $row['section'];?></option>
											</select>
										</div>
										<div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-2">
											<input type="number" class="form-control" data-visible="jurynumber" name="jurynumber[<?php echo $idx;?>]" id="jurynumber[<?php echo $idx;?>]" value="<?php echo $row['jurynumber'];?>" readonly>
										</div>
										<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-4" >
											<select class="form-control" data-visible="user_id" name="user_id[<?php echo $idx;?>]" id="user_id[<?php echo $idx;?>]" disabled >
												<option value="<?php echo $row['user_id'];?>"><?php echo $row['user_name'];?></option>
											</select>
										</div>
										<div class="form-group col-lg-1 col-md-1 col-sm-1 col-xs-1">
											<a href="javascript:delete_assignment(<?php echo $idx;?>)" class="btn btn-danger" id="delete_assignment"><i class="glyphicon glyphicon-trash small"></i></a>
										</div>
									</div>
							<?php
									$idx++;
								}
							?>
									<!-- Add New Assignment Row -->
									<div class="row" data-row-type="add" id="assignment[<?php echo $idx;?>]">
										<!-- Hidden field to hold the value of the hidden SELECT -->
										<input type="hidden" data-hidden="section" name="section[<?php echo $idx;?>]" value="<?php echo $row['section'];?>" >
										<input type="hidden" data-hidden="user_id" name="user_id[<?php echo $idx;?>]" value="<?php echo $row['user_id'];?>" >
										<div class="form-group col-lg-5 col-md-5 col-sm-5 col-xs-5">
											<select class="form-control" data-visible="section" name="section[<?php echo $idx;?>]" id="section[<?php echo $idx;?>]" >
												<option value="" selected>Pick Section...</option>
							<?php
								$sql = "SELECT section FROM section WHERE yearmonth = '$admin_yearmonth' ORDER BY section";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
												<option value="<?php echo $row['section'];?>"><?php echo $row['section'];?></option>
							<?php
								}
							?>
											</select>
										</div>
										<div class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-2">
											<input type="number" class="form-control" data-visible="jurynumber" name="jurynumber[<?php echo $idx;?>]" id="jurynumber[<?php echo $idx;?>]" value="0" >
										</div>
										<div class="form-group col-lg-4 col-md-4 col-sm-4 col-xs-4" >
											<select class="form-control user-id-select" data-visible="user_id" name="user_id[<?php echo $idx;?>]" id="user_id[<?php echo $idx;?>]" required>
												<option value="0" selected>Pick Jury...</option>
							<?php
								$sql = "SELECT * FROM user WHERE type = 'JURY' ORDER BY user_name";
								$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
								while ($row = mysqli_fetch_array($query)) {
							?>
												<option value="<?php echo $row['user_id'];?>"><?php echo $row['user_name'];?></option>
							<?php
								}
							?>
											</select>
										</div>
										<div class="form-group col-lg-1 col-md-1 col-sm-1 col-xs-1">
											<a href="javascript:add_assignment(<?php echo $idx;?>)" id="add_assignment" class="btn btn-primary"><i class="glyphicon glyphicon-plus small" ></i></a>
										</div>
									</div>
								</div>
								<div class="row"><br></div>
								<div class="row">
									<div class="col-lg-8 col-md-8 col-sm-6 col-xs-6"></div>
									<div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-6">
										<input class="form-control btn btn-primary" type="submit" name="submit" value="Submit">
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>		<!-- END OF JURY ASSIGN -->

			</div>
		</div>

	<?php
		//include("inc/footer.php");
		include("inc/profile_modal.php");
	?>

	</div>

	<?php include("inc/footer.php"); ?>

	<!-- Vendor scripts -->
	<!-- DataTables -->
	<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
	<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>
	<!-- DataTables buttons scripts -->
	<script src="plugin/pdfmake/build/pdfmake.min.js"></script>
	<script src="plugin/pdfmake/build/vfs_fonts.js"></script>
	<script src="plugin/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="plugin/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="plugin/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="plugin/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>

	<script src="plugin/select2/js/select2.min.js"></script>

<script>
	$(document).ready(function(){
		$(".user-id-select").select2();
	});

</script>

<script>

    $(function () {

        // Initialize Example 1
        $('#example1').dataTable( {
            "ajax": 'api/datatables.json',
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            buttons: [
                {extend: 'copy',className: 'btn-sm'},
                {extend: 'csv',title: 'ExampleFile', className: 'btn-sm'},
                {extend: 'pdf', title: 'ExampleFile', className: 'btn-sm'},
                {extend: 'print',className: 'btn-sm'}
            ]
        });

        // Initialize Example 2
        $('#example2').dataTable();

    });

</script>

<script>
	// TinyMCE
	$(document).ready(function(){
		tinymce.init({
			selector: '.html-editor',
			plugins : 'link lists',
			menubar: false,
			toolbar: 'formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link '
		});
	});
</script>


<script>
	// Reload form when jury yearmonth is changed
	$(document).ready(function() {
		$("#jury_yearmonth").change(function() {
			$post("op/set_yearmonth.php", {jury_yearmonth : $("#jury_yearmonth").val() });
			location.href = "user_admin.php";
		});
	});
</script>

<script>
// Validate Assignments Form
function validate_form() {

	var assign = [];
	var assignments = document.getElementById("assignments");

	// Gather all values
	var sectionsHidden = getElementsByAttributeValue(assignments, "data-hidden", "section");
	var useridsHidden = getElementsByAttributeValue(assignments, "data-hidden", "user_id");
	var sectionsVisible = getElementsByAttributeValue(assignments, "data-visible", "section");
	var jurynumbersVisible = getElementsByAttributeValue(assignments, "data-visible", "jurynumber");
	var useridsVisible = getElementsByAttributeValue(assignments, "data-visible", "user_id");

	var valid_form = true;

	// Assign Hidden Inputs with values from Visible Selects
	var i;
	var idx;
	var prev;

	for (i = 0; i < sectionsVisible.length; ++i) {
		sectionsHidden[i].value = sectionsVisible[i].value;
		useridsHidden[i].value = useridsVisible[i].value;
	}

	// 1. Remove last line if empty. Otherwise flag error if any field is missing
	var last_row = sectionsVisible.length - 1;
	if (sectionsVisible[last_row].value == "" && jurynumbersVisible[last_row].value == "0" && useridsVisible[last_row].value == "0")  // Empty Row. Remove from validation
		-- last_row;
	else {
		// Flag Error if any fields are missing
		if (sectionsVisible[last_row].value == "") {
			swal("Error!", "Section not selected in the last row", "error");
			valid_form = false;
		}
		if (jurynumbersVisible[last_row].value == "") {
			swal("Error!", "Jury Number not entered in the last row", "error");
			valid_form = false;
		}
		if (useridsVisible[last_row].value == "") {
			swal("Error!", "Jury not selected in the last row", "error");
			valid_form = false;
		}
	}


	// 2. Validate Jury Number Sequence
	// Assign Values to an array and sort as they could be in any order
	for (var idx = 0; idx < jurynumbersVisible.length; ++idx)
		assign[idx] = sectionsVisible[idx].value + "|" + jurynumbersVisible[idx].value;

	assign.sort();

	var prevSection = "";
	var prevNumber = 0;
	for (var i = 0; i < assign.length && valid_form; ++i) {
		var curSection = assign[i].split("|")[0];
		var curNumber = Number(assign[i].split("|")[1]);
		if (curSection == prevSection) {
			if(curNumber == (prevNumber + 1))
				prevNumber = curNumber;
			else {
				swal("Error!", "Jury Numbers not in sequence for " + curSection + " section", "error");
				valid_form = false;
			}
		}
		else {
			prevSection = curSection;
			prevNumber = curNumber;
		}
	}

	// 3. Validate Duplicate Assignments
	assign = [];
	for (var idx = 0; idx < sectionsVisible.length; ++idx)
		assign[idx] = sectionsVisible[idx].value + "|" + useridsVisible[idx].value;

	assign.sort();

	var prevSection = "";
	var prevUserid = 0;
	for (var i = 0; i < assign.length && valid_form; ++i) {
		var curSection = assign[i].split("|")[0];
		var curUserid = Number(assign[i].split("|")[1]);
		if (curSection == prevSection) {
			if(curUserid == prevUserid) {
				swal("Error!", "Jury assigned twice under " + curSection + " section", "error");
				valid_form = false;
			}
			else
				prevUserid = curUserid;
		}
		else {
			prevSection = curSection;
			prevUserid = curUserid;
		}
	}

	return valid_form;
}
</script>

<!-- Scripts to handle assignment rows -->
<script>
// Return an Array of Elements that match attribute-value pair
function getElementsByAttributeValue(groupElement, attribute, attributeValue) {
	var i;
	var childElements = [];
	var childElement;

	for (i = 0; i < groupElement.children.length; ++i) {
		childElement = groupElement.children[i];
		if (childElement.getAttribute(attribute) == attributeValue)
			childElements.push(childElement);
		else if (childElement.children.length > 0)   	// Recursively check at the next level
			childElements = childElements.concat(getElementsByAttributeValue(childElement, attribute, attributeValue));
	}
	return childElements;
}

// Returns the first Element that matches attribute-value pair
function getElementByAttributeValue(groupElement, attribute, attributeValue) {
	var i;
	var childElement;
	for (i = 0; i < groupElement.children.length; ++i) {
		childElement = groupElement.children[i];
		if (childElement.getAttribute(attribute) == attributeValue)
			return childElement;
		else if (childElement.children.length > 0)  	// Recursively check at the next level
			if (childElement = getElementByAttributeValue(childElement, attribute, attributeValue))
				return childElement;
	}
	return null;
}

function add_assignment(idx) {
	var row = document.getElementById("assignment[" + idx.toString() + "]");

	var section = document.getElementById("section[" + idx.toString() + "]");
	var user_id = document.getElementById("user_id[" + idx.toString() + "]");
	var jurynumber = document.getElementById("jurynumber[" + idx.toString() + "]");

	var valid_row = true;
	if (section.value > "" && user_id.value > 0 && jurynumber.value > 0) {
		// Validate if Jury Number is not assigned for this section
		var i;
		for (i = 0; i < idx; i++) {			// Check all previous assignments i < idx
			if (document.getElementById("section[" + i.toString() + "]")) {
				var compare_section = document.getElementById("section[" + i.toString() + "]");
				var compare_user_id = document.getElementById("user_id[" + i.toString() + "]");
				var compare_jurynumber = document.getElementById("jurynumber[" + i.toString() + "]");
				if (section.value == compare_section.value) {
					// Check if Jury Number has already been assigned
					if (compare_jurynumber.value == jurynumber.value) {
						swal("Error!", 'Jury Number ' + compare_jurynumber.value.toString() + ' has already been assigned to another Jury', "error");
						valid_row = false;
					}
					// Check if Jury has already been assigned
					if (compare_user_id.value == user_id.value) {
						swal("Error!", "This Jury has already been assigned to this Section", "error");
						valid_row = false;
					}
				}
			}
		}
	}
	else {
		swal("Error!", "Please fill all the fields", "error");
		valid_row = false;
	}

	if (valid_row) { 							// Add only if the data is valid

		var new_row = row.cloneNode(true);		// Clone the assignment

		// Change the Old Row and Replace the Plus icon with Trash Icon
		row.setAttribute("data-row-type", "delete");									// Change row-type to delete after cloning for use by initialize function
		var old_hidden_section = getElementByAttributeValue(row, "data-hidden", "section");			// Handle to the hidden input for field section
		var old_hidden_user_id = getElementByAttributeValue(row, "data-hidden", "user_id");			// Handle to the hidden input for field user_id

		old_hidden_section.value = section.value;											// Assign the SELECTED section to hidden field before disabling SELECT
		old_hidden_user_id.value = user_id.value;											// Assign the SELECTED user_id to hidden field before disabling SELECT

		section.setAttribute("disabled", "");												// Disable the select fields
		user_id.setAttribute("disabled", "");
		jurynumber.setAttribute("readonly", "");											// Make Jury Number field Readonly

		var old_button = row.getElementsByTagName("A")[0];
		old_button.innerHTML = '<i class="glyphicon glyphicon-trash small">';			// Change Old Button to Delete
		old_button.setAttribute("class", "btn btn-danger");								// Change Old Button Delete to Red
		old_button.setAttribute("href", "javascript:delete_assignment(" + idx.toString() + ")");	// Change Processing to Delete

		// Prepare the New Row Added
		new_idx = idx + 1;
		new_row.id = "assignment[" + new_idx.toString() + "]";							// change row id

		var new_hidden_section = getElementByAttributeValue(new_row, "data-hidden", "section");						// Hidden Field for changing name. It will have a value of 0
		var new_hidden_user_id = getElementByAttributeValue(new_row, "data-hidden", "user_id");
		new_hidden_section.setAttribute("name", "section[" + new_idx.toString() + "]");
		new_hidden_user_id.setAttribute("name", "user_id[" + new_idx.toString() + "]");

		var new_visible_section = getElementByAttributeValue(new_row, "data-visible", "section");
		new_visible_section.value = "";															// Set to Default
		new_visible_section.setAttribute("name", "section[" + new_idx.toString() + "]");		// change the id of the select element
		new_visible_section.setAttribute("id", "section[" + new_idx.toString() + "]");		// change the id of the select element

		var new_visible_user_id = getElementByAttributeValue(new_row, "data-visible", "user_id");
		new_visible_user_id.value = 0;															// Set to Default
		new_visible_user_id.setAttribute("name", "user_id[" + new_idx.toString() + "]");		// change the id of the select element
		new_visible_user_id.setAttribute("id", "user_id[" + new_idx.toString() + "]");			// change the id of the select element

		var new_visible_jurynumber = getElementByAttributeValue(new_row, "data-visible", "jurynumber");
		new_visible_jurynumber.value = 0;															// Set to Default
		new_visible_jurynumber.setAttribute("name", "jurynumber[" + new_idx.toString() + "]");		// change the id of the select element
		new_visible_jurynumber.setAttribute("id", "jurynumber[" + new_idx.toString() + "]");			// change the id of the select element

		var new_button = new_row.getElementsByTagName("A")[0];
		new_button.setAttribute("href", "javascript:add_assignment(" + new_idx.toString() + ")");	// Set processing for add button

		row.parentNode.appendChild(new_row);		// Add Child below
	}
}

function delete_assignment(idx) {
	var row = document.getElementById("assignment["+idx.toString()+"]");
	var parent = row.parentNode;
	parent.removeChild(row);
}

</script>

<script>

	function loadPhoto(input) {
		if (input.files && input.files[0] != "") {
			// Meta values checking
			var reader = new FileReader();

			reader.onload = function (e) {
				$('#avatar_preview').attr('src', e.target.result);
			};
			reader.readAsDataURL(input.files[0]);
		}
	}

</script>


</body>

</html>
<?php
}
else
{
	header("Location: " . $_SERVER['HTTP_REFERER']);
	print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}

?>

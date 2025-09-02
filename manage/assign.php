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

function nospace($str) {
	return str_replace(" ", "_", $str);
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Prepare list of jury
	$sql = "SELECT * FROM user WHERE type='JURY' AND status = 'ACTIVE' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$jury_list = [];
	while ($row = mysqli_fetch_array($query))
		$jury_list[$row['user_id']] = $row['user_name'];

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />

	<style>
		div.filter-button {
			display:inline-block;
			margin-right: 15px;
		}
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
						Assign Jury to Sections
					</h3>
					<div class="row">
						<div class="col-sm-4">
							<form role="form" method="post" name="select-contest-form" action="#" enctype="multipart/form-data" >
								<label for="yearmonth">Select a Contest</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" >
										<?php
											$sql  = "SELECT yearmonth, contest_name FROM contest ";
											$sql .= " WHERE results_ready = '0' ";
											$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
											while ($row = mysqli_fetch_array($query)) {
										?>
										<option value="<?= $row['yearmonth'];?>"><?= $row['contest_name'];?></option>
										<?php
											}
										?>
									</select>
									<span class="input-group-btn">
										<button class="btn btn-info" type="submit" > GET </button>
									</span>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="content">
			<div class="row">
				<div class="col-lg-12">
					<div class="hpanel">
						<div class="panel-body">
						<?php
							if (isset($_REQUEST['yearmonth'])) {
									$yearmonth = $_REQUEST['yearmonth'];
									$sql  = "SELECT section, stub, section_type ";
									$sql .= "  FROM section ";
									$sql .= " WHERE yearmonth = '$yearmonth' ";
									$sql .= " ORDER BY section_type, section_sequence ";
									$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
									if (mysqli_num_rows($query) == 0)
										return_error("Contest ($yearmonth) not set up for judging", __FILE__, __LINE__);

									$section_list = [];
									while ($row = mysqli_fetch_array($query)) {
										$section_list[$row['stub']] = $row;
									}
							?>
							<h3 class="text-info">Assign Jury</h3>
							<div class="row">
							<?php
								$count = 0;
								foreach ($section_list as $stub => $section_data) {
									$section = $section_data['section'];
									$section_nospace = nospace($section);
							?>
								<div class="col-sm-4 form-group" style="border: 1px #aaa solid;">
									<h4 class="text-info">Jury Assignments for <?= $section;?> section</h4>
									<form role="form" method="post" class="jury-assign-form" id="jury-assign-form-<?= $stub;?>" action="#" enctype="multipart/form-data" >
										<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>">
										<input type="hidden" name="section" value="<?= $section;?>">
										<input type="hidden" name="stub" value="<?= $stub;?>">
										<!-- Pick Jury and add -->
										<div class="row form-group">
											<div class="col-sm-12">
												<label for="user_id">Select and Assign Jury</label>
												<div class="input-group">
													<select class="jury-select form-control" name="user_id"
															id="jury-select-<?= $stub;?>" style="width: 100%;" >
													<?php
														foreach ($jury_list as $select_jury_id => $select_jury_name) {
													?>
														<option value="<?= $select_jury_id;?>"><?= $select_jury_name;?></option>
													<?php
														}
													?>
													</select>
													<span class="input-group-btn">
														<button class="btn btn-info add-selected-jury" type="button"
																data-select2-open="single-append-text"
																data-section="<?= $section;?>"
																data-section-nospace="<?= $section_nospace;?>"
																data-stub="<?= $stub;?>"
														  		data-jury-select="#jury-select-<?= $stub;?>"
															> <i class="fa fa-plus"></i> </button>
													</span>
												</div>
											</div>
										</div>
										<!-- Jury List -->
										<br><br>
										<p><b>Jury presently assigned</b></p>
										<br>
										<div class="row form-group">
											<div class="col-sm-12">
											<?php
												$sql  = "SELECT user.user_id, user_name FROM assignment, user ";
												$sql .= " WHERE assignment.yearmonth = '$yearmonth' ";
												$sql .= "   AND assignment.section = '$section' ";
												$sql .= "   AND user.user_id = assignment.user_id ";
												$sql .= " ORDER BY jurynumber ";
												$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
												while ($row = mysqli_fetch_array($query)) {
													$row_id = "jury-" . $row['user_id'] . "-" . $stub;
											?>
												<div class="input-group" id="<?= $row_id;?>">
													<input type="hidden" name="selected_jury_list[]" value="<?= $row['user_id'];?>" >
													<input type="text" value="<?= $row['user_name'];?>" class="form-control" readonly>
													<span class="input-group-btn">
														<button class="btn btn-info delete-jury-assignment" type="button"
																onclick="delete_jury_assignment('#<?= $row_id;?>')"
															> <i class="fa fa-minus"></i> </button>
													</span>
												</div>
											<?php
												}
											?>
												<!-- Anchor marking end of jury assignment list -->
												<span id="eol-<?= $stub;?>"><span>
											</div>
										</div>
										<br>
										<!-- Update button -->
										<div class="row form-group">
											<div class="col-sm-12">
												<button class="btn btn-info pull-right" name="assign-jury-to-section" type="submit"> Update </button>
											</div>
										</div>
									</form>
								</div>	<!-- col-sm-4 -->
							<?php
									++ $count;
									if ($count % 3 == 0) {
							?>
								<div class="clearfix"></div>
							<?php
									}
								}
							?>
							</div>
							<?php
								}	// If contest_id set
							?>
						</div>	<!-- panel body -->
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

<script src="plugin/select2/js/select2.min.js"></script>


<script>
	$(document).ready(function(){
		// Select 2
		$(".jury-select").select2({theme: 'bootstrap'});
	});
</script>

<!-- Action Handlers -->
<script>
	// Delete Button
	function delete_jury_assignment(jury_div) {
		$(jury_div).remove();
	}

	$(document).ready(function(){

		// Add button
		$(".add-selected-jury").click(function(){
			let stub = $(this).attr("data-stub");
			let section = $(this).attr("data-section");
			let section_nospace = $(this).attr("data-section-nospace");
			let select_id = $(this).attr("data-jury-select");
			let user_id = $(select_id).val();
			let user_name = $(select_id + " option:selected").text();
			let jury_delete_div = "jury-" + user_id + "-" + stub;
			let jury_delete_div_ref = "'#" + jury_delete_div + "'";
			let div = '';
			div += '<div class="input-group " id="' + jury_delete_div + '" > ';
			div += '   <input type="hidden" name="selected_jury_list[]" value="' + user_id + '" > ';
			div += '   <input type="text" value="' + user_name + '" class="form-control" readonly > ';
			div += '   <span class="input-group-btn"> ';
			div += '      <button class="btn btn-info delete-jury-assignment" type="button" ';
			div += '          onclick="delete_jury_assignment(' + jury_delete_div_ref + ')" ';
			div += '      > <i class="fa fa-minus"></i> </button> ';
			div += '   </span> ';
			div += '</div> ';

			// Insert the jury at the end
			$(div).insertBefore("#eol-" + stub);
		});

		// Ajax Call Handling
		function save_jury_assignment(formData) {
			$('#loader_img').show();
			$.ajax({
					url: "ajax/assign_jury.php",
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
									text: "Jury assignments have been saved.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Save Failed",
									text: "Jury assignments could not be saved: " + response.msg,
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

		// Update Elimination Assignment
		$(".jury-assign-form").submit(function(){
			let formData = new FormData(this);
			save_jury_assignment(formData);
			return false;
		})
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

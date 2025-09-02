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

function sort_by_sequence ($a, $b) {
	if ($a["sequence"] == $b["sequence"])
		return 0;
	return ($a["sequence"] < $b["sequence"]) ? -1 : 1;	// ascending order
}

function max_sequence ($a, $b) {
	if ($a == null || empty($a))
		return $b['sequence'];
	if ($a['sequence'] > $b['sequence'])
		return $a['sequence'];
	else
		return $b['sequence'];
}

function event_photos($yearmonth, $event) {
	$csvpath = "../salons/$yearmonth/blob/photos.csv";
	$photospath = "../photos/$yearmonth/$event";
	$event_photos = [];
	if (file_exists($csvpath) && is_dir($photospath)) {
		// CSV format - event, sequence, file_name, description
		$csvfile = fopen($csvpath, "r");
		while ($row = fgetcsv($csvfile)) {
			if ($row[0] == $event && file_exists($photospath . "/" . $row[2])) {
				$event_photos[] = array("sequence" => $row[1], "photo" => $row[2], "caption" => $row[3]);
			}
		}
	}
	usort($event_photos, "sort_by_sequence");

	return $event_photos;
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "",
	);

	$yearmonth = 0;
	$salon_event = "";

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

	// Load Photos
	$photos = [];
	// $max_photo_sequence = 1;
	if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['salon_event'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		$salon_event = $_REQUEST['salon_event'];
		$photo_path = "../photos/$yearmonth/$salon_event";
		$photo_tn_path = $photo_path;
		$photo_full_path = "../photos/$yearmonth/$salon_event/download";

		$photos = event_photos($yearmonth, $salon_event);
		// Find the maximum Sequence
		// if (sizeof($photos) == 0)
		// 	$max_photo_sequence = 1;
		// else
		// 	$max_photo_sequence = array_reduce($photos, "max_sequence") + 1;
	}

?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

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
						Upload <?= ucfirst($salon_event);?> Photos
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="photos.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-4">
								<label for="yearmonth">Select Salon</label>
								<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>" required >
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
							</div>
							<div class="col-sm-2">
								<label for="salon_event">Select the Event</label>
								<select class="form-control" name="salon_event" id="select-salon-event" value="<?= $salon_event;?>" required >
									<option value="judging" <?= $salon_event == 'judging' ? 'selected' : '';?> >Judging Photos</option>
									<option value="exhibition" <?= $salon_event == 'exhibition' ? 'selected' : '';?> >Exhibition Photos</option>
									<option value="inauguration" <?= $salon_event == 'inauguration' ? 'selected' : '';?> >Award Function Photos</option>
								</select>
							</div>
							<div class="col-sm-1">
								<br>
								<button type="submit" class="btn btn-info pull-right" name="select-contest-button" ><i class="fa fa-edit"></i> EDIT </a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>


		<div class="content">
			<form role="form" method="post" name="add-photo-form" id="add-photo-form" enctype="multipart/form-data" >
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="salon_event" id="salon-event" value="<?= $salon_event;?>" >

				<div class="row form-group">
					<div class="col-sm-3">
						<label for="event_photo">Event Photo</label>
						<p><img src="/img/preview.png" class="text-center" style="max-width: 200px" id="photo-display" ></p>
						<input type="file" name="event_photo" class="form-control img-file" id="event-photo" data-img="photo-display" required >
					</div>
					<div class="col-sm-9">
						<div class="row form-group">
							<div class="col-sm-12">
								<label for="photo_caption">Caption</label>
								<input type="text" class="form-control" name="photo_caption" id="photo-caption" value="" required >
							</div>
						</div>
						<div class="row form-group">
							<div class="col-sm-6">
								<label for="photo_sequence">Sequence <small><small>(use numbering with gaps to allow insertion between photos)</small></small></label>
								<div class="row">
									<div class="col-sm-4">
										<input type="number" class="form-control" name="photo_sequence" id="photo-sequence" min="1" required >
									</div>
									<div class="col-sm-8">
										<p class="text-info" style="padding-top: 8px;">Last Seq # <span id="disp-last-sequence"></span></p>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<button class="btn btn-info" name="add_photo" id="add-photo"><i class="fa fa-plus-circle"></i> Add Photo</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			<hr>
			<div class="row">
				<div class="col-sm-12">
					<table id="photo-table" class="table" >
						<thead>
							<tr>
								<th>Sequence</th>
								<th>Photo</th>
								<th>Caption</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
						<?php
							for ($idx = 0; $idx < sizeof($photos); ++ $idx) {
								$photo = $photos[$idx];
								$seq = $photo['sequence'];
						?>
							<tr id="photo-<?= $seq;?>-row" data-seq="<?= $seq;?>" class="photo-row">
								<td><?= $seq;?></td>
								<td><img id="photo-<?= $seq;?>" style="max-width: 120px;" src="/photos/<?= $yearmonth;?>/<?= $salon_event;?>/<?= $photo['photo'];?>"></td>
								<td><span id="caption-<?= $seq;?>"><?= $photo['caption'];?></span></td>
								<td>
									<button id="delete-photo-<?= $seq;?>" class="btn btn-danger delete-photo" onclick="delete_photo(<?= $seq;?>)" >
										<i class="fa fa-trash"></i> Delete
									</button>
								</td>
							</tr>
						<?php
							}
						?>
							<tr id="end-of-photo-list">
								<td></td>
								<td></td>
								<td><b>That&apos;s all we have</b></td>
								<td></td>
							</tr>
						</tbody>
					</table>
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


<script>
	function update_last_seq() {
		let last_seq = 0;
		$(".photo-row").each(function(){
			if (Number($(this).attr("data-seq")) > last_seq)
				last_seq = $(this).attr("data-seq");
		});
		$("#disp-last-sequence").html(last_seq);
	}

	$(document).ready(function(){
		// Hide content till a salon is selected
		let yearmonth = "<?= isset($_REQUEST['yearmonth']) ? $_REQUEST['yearmonth'] : 0;?>";
		let salon_event = "<?= isset($_REQUEST['salon_event']) ? $_REQUEST['salon_event'] : '';?>"
		if(yearmonth == 0 || salon_event == "")
			$(".content").hide();
	});
</script>

<!-- Action Handlers -->
<script>

	// Delete Photo
	// Handle delete button request
	function delete_photo(photo_sequence) {
		let yearmonth = "<?= $yearmonth;?>";
		let salon_event = "<?= $salon_event;?>";
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete this Photo ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$('#loader_img').show();
				$.post("ajax/delete_photo.php", {yearmonth, salon_event, photo_sequence}, function(response) {
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						// Remove the table row
						$("#photo-" + photo_sequence + "-row").remove();
						update_last_seq();
						swal({
								title: "Removed",
								text: "The Photo has been removed successfully.",
								icon: "success",
								confirmButtonClass: 'btn-success',
								confirmButtonText: 'Great'
						});
					}
					else{
						swal({
								title: "Remove Failed",
								text: "Unable to remove photo: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				});
			}
		});
	}

</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Update first time
		update_last_seq();

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

		function add_photo_row(photo) {
			let photo_display = photo.photo;
			let caption = photo.caption;
			let photo_sequence = photo.sequence;
			let yearmonth = $("#yearmonth").val();
			let salon_event = $("#salon-event").val();
			let row = '<tr id="photo-' + photo_sequence + '-row" data-seq="' + photo_sequence + '" class="photo-row" >';
			row += '<td>' + photo_sequence + '</td>';
			row += '<td><img id="photo-' + photo_sequence + '" style="max-width: 120px;" src="/photos/' + yearmonth + '/' + salon_event + '/' + photo_display + '"></td>';
			row += '<td><span id="caption-' + photo_sequence + '">' + caption + '</span></td>';
			row += '<td>';
			row += '	<button id="delete-photo-' + photo_sequence + '" class="btn btn-danger delete-photo"';
			row += '            onclick="delete_photo(' + photo_sequence + ')" >';
			row += '		<i class="fa fa-trash"></i> Delete ';
			row += '	</button>';
			row += '</td>';
			row += '</tr>';

			let filtered_rows = [], insert_point;
			filtered_rows = $("tr.photo-row").filter(function() {
				return (Number($(this).attr("data-seq")) >= photo_sequence);
			});
			if (filtered_rows.length > 0)
				insert_point = filtered_rows[0];
			else
				insert_point = $("#end-of-photo-list");

			// Insert at the first instance
			$(row).insertBefore($(insert_point));

			// Update last sequence number
			update_last_seq();
		}

		// Update rules back to server
		let vaidator = $('#add-photo-form').validate({
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
				let formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/add_photo.php",
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
										text: "Photo details have been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Update changes to the table
								add_photo_row(response.photo);
								$("#photo-display").attr("src", "/img/preview.png");
								$("#event-photo").val(null);
								$("#photo-caption").val("");
								$("#photo-sequence").val($("#max-photo-sequence").val());
							}
							else{
								swal({
										title: "Save Failed",
										text: "Photo details could not be saved: " + response.msg,
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

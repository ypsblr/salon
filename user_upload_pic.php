<?php
include_once("inc/session.php");
include_once("inc/categories.php");
include_once("inc/user_lib.php");

// Add single quotes at the start and end of a string
function quote_string($str) {
	return "'" . $str . "'";
}

function rejection_text($notifications) {
	global $rejection_reasons;

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			$rejected = false;
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

function generate_edit_form ($yearmonth, $profile_id, $section, $section_row, $idx, $pic_list) {

	// Display Edit Form only if submissions are still open
	if ($section_row['submission_last_date'] >= DATE_IN_SUBMISSION_TIMEZONE) {
		if (isset($pic_list[$idx])) {
			$rpic = $pic_list[$idx];
			$pic_id = $rpic['pic_id'];
			$pic_title = $rpic['title'];
			$pic_file = $rpic['picfile'];
			$pic_link = "/salons/" . $yearmonth . "/upload/" . $section . "/" . $pic_file;
			$pic_tn_link = "/salons/" . $yearmonth . "/upload/" . $section . "/tn/" . $pic_file;
			$pic_file_size = $rpic['file_size'];
			$pic_width = $rpic['width'];
			$pic_height = $rpic['height'];
			$pic_exif = $rpic['exif'];
			$rejection_text = rejection_text($rpic['notifications']);
		}
		else {
			$pic_id = 0;
			$pic_title = "";
			$pic_file = "preview.png";
			$pic_link = "/img/" . $pic_file;
			$pic_file_size = 0;
			$pic_width = 0;
			$pic_height = 0;
			$pic_exif = "";
			$rejection_text = "";
		}
		$form_id = "UF_" . $section_row['stub'] . "_" . $idx;
		$delete_link = "DELPIC_" . $section_row['stub'] . "_" . $idx;
		$upload_link = "UPLDPIC_" . $section_row['stub'] . "_" . $idx;
?>
	<!-- Controls -->
	<div class="row">
		<div class="col-sm-12">
			<a href="javascript:void(0)" onclick="deletePicture(this)" id="<?= $delete_link;?>"
			   class="text-danger" style="display: <?= $pic_id == 0 ? 'none' : 'inline-block';?>"
			   data-yearmonth="<?= $yearmonth;?>" data-profile-id="<?= $profile_id;?>"
			   data-stub="<?= $section_row['stub'];?>" data-idx="<?= $idx;?>" data-pic-id="<?= $pic_id;?>" >
				Delete Picture
			</a>
			<a href="javascript:void(0)" onclick="showUploadForm('<?= $form_id;?>', '<?= $upload_link;?>')"
			   class="text-danger pull-right" id="<?= $upload_link;?>">
				<?= ($pic_id == 0) ? "Upload Picture" : "Edit Picture";?>
			</a>
		</div>
	</div>
	<!-- UPLOAD FORM FOR EACH IMAGE -->
	<form role="form" name="upload-form" id="<?= $form_id;?>" method="post"
		  action="#" enctype="multipart/form-data" class="imageUpload" style="display: none;"
		  data-stub="<?= $section_row['stub'];?>" data-idx="<?= $idx;?>"
		  data-section="<?= $section;?>" data-yearmonth="<?= $yearmonth;?>"
		  data-profile-id="<?= $profile_id;?>" data-pic-id="<?= $pic_id;?>" >
		<!-- Hidden Form Variables -->
		<input type="hidden" name="yearmonth" value="<?=$yearmonth;?>" >
		<input type="hidden" name="profile_id" value="<?=$profile_id;?>" >
		<input type="hidden" name="upload_mode" value="<?= ($pic_id == 0) ? "new" : "edit";?>" >
		<input type="hidden" name="upload_pic_id" value="<?= $pic_id;?>" >
		<input type="hidden" name="upload_section" value="<?=$section;?>" >
		<input type="hidden" name="upload_picfile" value="<?= ($pic_id == 0) ? "" : $rpic['picfile'];?>" >
		<input type="hidden" name="exif" value="<?= ($pic_id == 0) ? "" : $pic_exif;?>" >
		<input type="hidden" name="dropped-file-tmp-name" value="" >
		<input type="hidden" name='dropped-file-name' value="" >
		<input type="hidden" name="dropped-file-upload-error" value="" >
		<hr>
		<!-- DISPLAY PROGRESS BAR WHEN DISPLAYING UPLOAD FORM -->
		<div class="row upload-progress-bar" style="display: none;">
			<div class="col-sm-2"></div>
			<div class="col-sm-8">
				<div class="progress">
					<div class="progress-bar" role="progressbar" id="PRG_<?= $section_row['stub'];?>_<?= $idx;?>"
						 style="width: 0%; background-color: orange; color:#fff;"
					 	 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
					</div>
				</div>
			</div>
			<div class="col-sm-2"></div>
		</div>
		<!-- THE UPLOAD FORM -->
		<div class="row" >
			<div class="col-sm-12">
				<a href="javascript: void(0)" onclick="hideUploadForm('<?= $form_id;?>', '<?= $upload_link;?>')" class="text-danger small pull-right">Hide</a>
			</div>
			<div class="col-sm-12">
				<div class="form-group">
					<label for="img_title">Image Title (max 35 chars) *</label>
					<input type="text" class="form-control" name="img_title" placeholder="Image Title" maxlength="35" value="<?= $pic_title;?>" required>
				</div>
				<div class="form-group" id="UPLD_FILE_<?= $section_row['stub'];?>_<?= $idx;?>">
					<label class="control-label">Select Picture...*</label>
					<input type="file" name="submittedfile" onchange="loadPhoto(this);" extension="jpg,jpeg" class="form-control file" >
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4">
							<label class="control-label">File Size <br><small><small>(Max <?= MAX_PIC_FILE_SIZE_IN_MB;?>MB)</small></small></label>
							<input type="text" class="form-control" name="file_size_disp" value="<?= round($pic_file_size / 1024 / 1024, 2) . ' MB';?>" readonly />
							<input type="hidden" name="file_size" value="<?= $pic_file_size;?>" >
							<span class="file_size_warning" style="display:none;">
								<p class="text text-danger">File Size Exceeds <?= MAX_PIC_FILE_SIZE_IN_MB; ?>MB</p>
							</span>
						</div>
						<div class="col-sm-4">
							<label class="control-label">Width <br><small><small>(Max <?=MAX_PIC_WIDTH;?> px)</small></small></label>
							<input type="text" class="form-control" name="width" value="<?= $pic_width;?>" readonly/>
						</div>
						<div class="col-sm-4">
							<label class="control-label">Height <br><small><small>(Max <?=MAX_PIC_HEIGHT;?> px)</small></small></label>
							<input type="text" class="form-control" name="height" value="<?= $pic_height;?>" readonly/>
						</div>
						<div class="col-sm-12 dimension_warning" style="display: none;">
							<span class="text text-danger resize_dimension"></span>
						</div>
						<div class="col-sm-12 pic-info" style="display: none;">
							<span class="text text-info pic-info"></span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-6">
							<button class="btn btn-default" name="cancel_upload" onclick="cancelUpload(event, this)" style="display: none;">Cancel</button>
						</div>
						<div class="col-sm-6">
							<button type="submit" class="btn btn-color pull-right" name="upload_img"><?= ($pic_id == 0) ? "Submit" : "Update";?></button>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<span class="text-danger small upload-error"></span>
							<span class="text-info small upload-success"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php
	}
	else {
?>
		<div class="row" id="pic-upload-closed" style="display: <?php echo ($section_row['submission_last_date'] < DATE_IN_SUBMISSION_TIMEZONE) ? 'block' : 'none'; ?>;">
			<div class="col-sm-12">
				<p class="text text-danger lead">Submission of entries under this section is CLOSED</p>
			</div>
		</div>
<?php
	}
}

if(isset($_SESSION['USER_ID']) && ($tr_user['fee_waived'] ||
		($tr_user['fees_payable'] > 0 && ($tr_user['fees_payable'] - $tr_user['discount_applicable'] - $tr_user['payment_received']) <= 0))) {

	// Check for blacklist
	if ($tr_user['blacklist_match'] != "" && $tr_user['blacklist_exception'] == 0)
		handle_error("Not permitted due to match with Blacklisted profile.", __FILE__, __LINE__);

	$profile_id = $uid = $_SESSION['USER_ID'];
	$entrant_category = $tr_user['entrant_category'];
	$participation_code = $tr_user['participation_code'];
	$user_digital_sections = $tr_user['digital_sections'];
	$user_print_sections =  $tr_user['print_sections'];
	$digital_sections_state = "";
	$print_sections_state = "";

	// Read session variables to be able to open the same section again
	// debug_dump("SESSSION", $_SESSION, __FILE__, __LINE__);
	if (isset($_SESSION['hide_upload_instructions']))
		$hide_upload_instructions = ($_SESSION['hide_upload_instructions'] == "true");
	else
		$hide_upload_instructions = false;

	if (isset($_SESSION['hide_past_acceptances']))
		$hide_past_acceptances = ($_SESSION['hide_past_acceptances'] == "true");
	else
		$hide_past_acceptances = false;

	// Set the selectability of digital & print section tabs
	if ($user_digital_sections == 0)
		$digital_sections_state = "disabled";
	if ($user_print_sections == 0)
		$print_sections_state = "disabled";

	$active_section_type = "D";		// Default section type as it appears as the first tab

	// Restore active section type
	if (isset($_SESSION['active_section_type']))
		$active_section_type = $_SESSION['active_section_type'];

	// Validate active_section_type against its state based on current participation options
	if ($active_section_type == "D" && $digital_sections_state == "disabled")
		$active_section_type = "P";
	if ($active_section_type == "P" && $print_sections_state == "disabled")
		$active_section_type = "D";

	$_SESSION['active_section_type'] = $active_section_type;		// save active section type

	if (isset($_SESSION['active_digital_section']))
		$active_digital_section = $_SESSION['active_digital_section'];
	else
		$active_digital_section = "";

	if (isset($_SESSION['active_print_section']))
		$active_print_section = $_SESSION['active_print_section'];
	else
		$active_print_section = "";


	// Gather section statistics
	$num_digital_sections = 0;
	$num_print_sections = 0;
	$uploaded_digital_sections = 0;
	$uploaded_print_sections = 0;
	$section_list = array();

	$sql = "SELECT * FROM section WHERE yearmonth = '$contest_yearmonth' ";
	$qs = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$digital_first = true;
	$print_first = true;
	while ($qs_row = mysqli_fetch_array($qs, MYSQLI_ASSOC)) {
		$this_section = $qs_row['section'];
		$sql  = "SELECT section, COUNT(*) AS num_uploads FROM pic ";
		$sql .= "WHERE yearmonth = '$contest_yearmonth' ";
		$sql .= "  AND profile_id = '" . $tr_user['profile_id'] . "' ";
		$sql .= "  AND section = '$this_section' ";
		$qup = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$num_uploads = ($rup = mysqli_fetch_array($qup)) ? $rup['num_uploads'] : 0;
		$active = false;
		if ($qs_row['section_type'] == "D") {
			++ $num_digital_sections;
			$uploaded_digital_sections += ($num_uploads == 0) ? 0 : 1;
			if ($active_digital_section == "" && $digital_first) {
				$active = true;
				$_SESSION['active_digital_section'] = $this_section;
				$digital_first = false;
			}
			else {
				if ($active_digital_section == $this_section)
					$active = true;
			}
		}
		if ($qs_row['section_type'] == "P") {
			++ $num_print_sections;
			$uploaded_print_sections += ($num_uploads == 0) ? 0 : 1;
			if ($active_print_section == "" && $print_first) {
				$active = true;
				$_SESSION['active_print_section'] = $this_section;
				$print_first = false;
			}
			else {
				if ($active_print_section == $this_section)
					$active = true;
			}
		}
		$section_list[$qs_row['section']] = array ( "stub" => $qs_row['stub'], "section_type" => $qs_row['section_type'], "active" => $active, "state" => "",
													"submission_last_date" => $qs_row['submission_last_date'], "max_pics_per_entry" => $qs_row['max_pics_per_entry'],
													"num_uploads" => $num_uploads, "move_options" => "" );

	}	// while (sections)
	// scan through and disable sections that are not required based on selection and number of uploads
	$first_active_digital_section = "";
	$first_active_print_section = "";
	$digital_move_options = array();
	$print_move_options = array();
	foreach ($section_list as $section => $section_row) {
		if ($section_row['section_type'] == "D" && $uploaded_digital_sections >= $user_digital_sections && $section_row["num_uploads"] == 0) {
			$section_list[$section]["state"] = "disabled";
			$section_list[$section]['active'] = false;
		}
		if ($section_row['section_type'] == "P" && $uploaded_print_sections >= $user_print_sections && $section_row["num_uploads"] == 0) {
			$section_list[$section]["state"] = "disabled";
			$section_list[$section]['active'] = false;
		}
		if ($section_list[$section]['state'] == "") {
			if ($section_row['section_type'] == "D" && $first_active_digital_section == "")
				$first_active_digital_section = $section;
			if ($section_row['section_type'] == "P" && $first_active_print_section == "")
				$first_active_print_section = $section;
		}
		if ($section_list[$section]["state"] == "" && $section_row["num_uploads"] < $section_row['max_pics_per_entry']) {
			if ($section_row['section_type'] == 'D')
				$digital_move_options[] = $section;
			if ($section_row['section_type'] == 'P')
				$print_move_options[] = $section;
		}
	}
	if ($digital_sections_state != "disabled" && ($active_digital_section == "" || $section_list[$active_digital_section]['state'] == "disabled")) {
		$active_digital_section = $first_active_digital_section;
		$section_list[$active_digital_section]['active'] = true;
	}
	if ($print_sections_state != "disabled" && ($active_print_section == "" || $section_list[$active_print_section]['state'] == "disabled")) {
		$active_print_section = $first_active_print_section;
		$section_list[$active_print_section]['active'] = true;
	}
	$_SESSION['active_digital_section'] = $active_digital_section;
	$_SESSION['active_print_section'] = $active_print_section;

	// debug_dump("section_list", $section_list, __FILE__, __LINE__);
	// Attach move options to each section to be saved as data for each picture thumbnail
	foreach ($section_list as $section => $section_row) {
//	 	debug_dump("section_row", $section_row, __FILE__, __LINE__);
//	 	debug_dump("section", $section, __FILE__, __LINE__);
		if ($section_row['section_type'] == 'D' && $section_row['state'] == '')
			$section_list[$section]['move_options'] = implode(",", array_diff($digital_move_options, array($section)));
		if ($section_row['section_type'] == 'P' && $section_row['state'] == '')
			$section_list[$section]['move_options'] = implode(",", array_diff($print_move_options, array($section)));
	}

	// debug_dump("uploaded_digital_sections", $uploaded_digital_sections, __FILE__, __LINE__);
	// debug_dump("user_digital_sections", $user_digital_sections, __FILE__, __LINE__);
	// debug_dump("digital_move_options", $digital_move_options, __FILE__, __LINE__);
	// debug_dump("print_move_options", $print_move_options, __FILE__, __LINE__);
	// debug_dump("section_list", $section_list, __FILE__, __LINE__);

	if ($active_section_type == "D")
		$upload_section = $active_digital_section;
	if ($active_section_type == "P")
		$upload_section = $active_print_section;

	// Trigger all_pictures_uploaded when maximum number of uploads under all non-disabled sections have been uploaded_digital_sections
	$all_digital_pictures_uploaded = true;
	$all_print_pictures_uploaded = true;
	foreach ($section_list as $section => $section_row) {
		if ($section_row['section_type'] == 'D' && $section_row['state'] == '' && $section_row['num_uploads'] < $section_row['max_pics_per_entry'])
			$all_digital_pictures_uploaded = false;
		if ($section_row['section_type'] == 'P' && $section_row['state'] == '' && $section_row['num_uploads'] < $section_row['max_pics_per_entry'])
			$all_print_pictures_uploaded = false;
	}

	// Gather List of Rejection Reasons
		// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($rntf = mysqli_fetch_array($qntf))
		$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once("inc/header.php"); ?>
	<style>
		div.dragarea {
			width: 100%;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 15px;
			background-color: #fff;">
		}
		div.dragarea.hover {
			color: red;
			border-style: dashed;
			border-color: red;
		}
		img.dragarea.hover {
			border: 1px dashed red;
		}
	</style>

</head>

<body class="<?php echo THEME;?>">
	<?php include_once("inc/navbar.php") ;?>
    <div class="wrapper">
    <?php  include_once("inc/Slideshow.php") ;?>

		<div class="container">
			<div class="row">
				<!-- Left Menu -->
				<div class="col-sm-3">
					<?php include("inc/user_sidemenu.php");?>
				</div>
				<!-- Right Side -->
				<div class="col-sm-9">
					<div class="row">
						<br>
						<div class="col-sm-12 user-cart">
							<div class="signin-form" id="myTab">
								<!-- Loading image made visible during processing -->
								<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
									<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
								</div>
								<!-- Maintains status of session variables -->
								<form id="tracking-form">
									<input type="hidden" name="upload_section_type" id="upload_section_type" value="<?= $active_section_type;?>" >
									<input type="hidden" name="upload_digital_section" id="upload_digital_section" value="<?= $active_digital_section;?>" >
									<input type="hidden" name="upload_print_section" id="upload_print_section" value="<?= $active_print_section;?>" >
								</form>
								<div class="well">
									<!-- Collapsible Instructions Panel -->
									<div class="panel-group">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h5 class="panel-title" style="color:red;">
													<a data-toggle="collapse" href="#upload_instructions"><b>READ BEFORE UPLOADING</b></a>
												</h5>
											</div>
											<div class="panel-collapse <?php echo ($hide_upload_instructions ? 'collapse' : 'in');?>" id="upload_instructions">
												<div class="panel-body">
													<div class="col-sm-9">
														<ul>
															<li>Images must be in JPG/JPEG format. Width should not exceed <?= MAX_PIC_WIDTH;?> pixels and Height should not exceed <?= MAX_PIC_HEIGHT;?> pixels.
																All pictures uploaded will be projected in 100% resolution. If the uploaded picture is smaller, the projected
																image will also look small. The File Size should not exceed <?= MAX_PIC_FILE_SIZE_IN_MB; ?>MB.</li>
															<li>Uploads with your name visible on the Picture or your name appearing as part of Title will not be considered
																for awards.</li>
															<?php
																if ($contestIsNoToPastAcceptance > 0) {
															?>
															<li>Pictures that have won acceptances in the previous Salons of Youth Photographic Society are not eligible for upload
																in this salon. The list of pictures that have won in acceptances in the past salons of YPS shown below is for reference only.</li>
															<?php
																}
															?>
															<li>Please read all the <a href="term_condition.php">Salon Rules</a> before uploading pictures</li>
														</ul>
													</div>
													<div class="col-sm-3">
														<h5 style="color:red;"><b>SIZING GUIDE</b></h5>
														<a href="#" data-toggle="modal" data-target="#dimensions"><img src="/salons/<?= $contest_yearmonth;?>/img/dimensions.jpg" style="max-width: 200px;"></a>
														<p style="color:red">Click to view larger image</p>
														<!-- Modal -->
														<div id="dimensions" class="modal fade" role="dialog">
															<div class="modal-dialog">
																<!-- Modal content-->
																<div class="modal-content" style="width: 1000px">
																	<div class="modal-header">
																		<button type="button" class="close" data-dismiss="modal">&times;</button>
																		<h4 class="modal-title">Sizing Guide</h4>
																	</div>
																	<div class="modal-body">
																		<img src="/salons/<?= $contest_yearmonth;?>/img/dimensions.jpg">
																	</div>
																	<div class="modal-footer">
																		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="panel-footer">
													<!-- do not show this again -->
													<label>
														<input type="checkbox" name="hide_upload_instructions" id="hide_upload_instructions" value="yes" <?php echo ($hide_upload_instructions == true ? "checked" : "");?> >
														Keep Instructions hidden
													</label>
												</div>
											</div>
										</div>
									</div>
									<!-- end of collapsible instructions panel -->

									<!-- UPLOAD COMPLETED INFO PANEL -->
									<?php
										if ($all_digital_pictures_uploaded && $all_print_pictures_uploaded) {
									?>
									<div class="well">
										<h5 style="color:red;"><b>YOU HAVE COMPLETED UPLOADING</b></h5>
										<ul>
											<li>You have successfully uploaded the maximum number of pictures under all the Digital &amp; Print sections available as per the Participation options chosen.</li>
											<li>If you want to substitute any picture with another, delete that picture and upload another.</li>
											<li>If you want to drop a section and upload pictures under another section, delete all the pictures in the section you want to drop
												&amp; select another section and upload pictures under that section.</li>
									<?php
											if ($tr_user['digital_sections'] < $num_digital_sections || $tr_user['print_sections'] < $num_print_sections) {
									?>
											<li>If you want to add more sections, please go back to <a href="/user_payment.php">Payment</a> option and change the
												participation choices and pay the incremental amount.</li>
									<?php
											}
									?>
										</ul>
									</div>
									<?php
										}
									?>
									<!-- END OF - UPLOAD COMPLETED INFO PANEL -->

									<!-- TABS FOR DIGITAL & PRINT PANES -->
									<div class="well">
										<!-- TABS -->
										<ul class="nav nav-tabs">
										<?php
											if ($num_digital_sections > 0) {
										?>
											<li id="digital-pill" data-section-type="D"
												class="track-type <?php echo $digital_sections_state;?> <?php echo ($active_section_type == "D") ? "active" : "";?> "  >
												<a data-toggle="pill" href="#digital-tc"  ><h3>DIGITAL SUBMISSIONS</h3><small>Number of Sections: <?php echo $user_digital_sections;?></small></a>
											</li>
										<?php
											}
											if ($num_print_sections > 0) {
										?>
											<li id="print-pill" data-section-type="P"
												class="track-type <?php echo $print_sections_state;?> <?php echo ($active_section_type == "P") ? "active" : "";?> "  >
												<a data-toggle="pill" href="#print-tc" ><h3>PRINT SUBMISSIONS</h3><small>Number of Sections: <?php echo $user_print_sections;?></small></a>
											</li>
										<?php
											}
										?>
										</ul>
										<!-- CONTENT FOR DIGITAL & PRINT PANES -->
										<div class="tab-content">
										<?php
											if ($num_digital_sections > 0) {
										?>
											<!-- DIGITAL TAB CONTENT -->
											<div id="digital-tc"
												 class="tab-pane fade <?php echo ($user_digital_sections == 0) ? "disabled" : "";?> <?php echo ($active_section_type == "D") ? "active in" : "";?> ">
												<!-- TABS FROR DIGITAL SECTIONS -->
												<ul class="nav nav-pills">
													<?php
														foreach($section_list AS $section => $section_row) {
															if ($section_row['section_type'] == "D") {
																if ($section_row['active'])
																	$class = "active";
																else
																	$class = "";
													?>
													<li id="NP_<?php echo $section_row['stub'];?>"
																class="track-digital-section <?php echo $section_row['state'] . ' ' . $class;?>" data-section="<?php echo $section;?>"
																data-submission="<?php echo ($section_row['submission_last_date'] >= DATE_IN_SUBMISSION_TIMEZONE) ? 'open' : 'closed';?>" >
														<a data-toggle="pill" href="#TC_<?php echo $section_row['stub'];?>" ><?= $section;?></a>
													</li>
													<?php
															}
														}
													?>
												</ul>
												<div class="tab-content">
													<?php
														foreach($section_list AS $section => $section_row) {
															if ($section_row['section_type'] == "D") {
																if ($section_row['active']) {
																	$class = "active in";
																}
																else
																	$class = "";
																// Create a list of pictures to display
																$sql = "SELECT * FROM pic WHERE yearmonth = '$contest_yearmonth' AND profile_id = '" . $tr_user['profile_id'] . "' AND section = '$section' ";
																$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																$num_drops = $section_row['max_pics_per_entry'] - mysqli_num_rows($qpic);
																$pic_list = [];
																while ($rpic = mysqli_fetch_array($qpic, MYSQLI_ASSOC)) {
																	// list($width, $height) = getimagesize("salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $rpic['picfile']);
																	// $rpic['pic_file_size'] = filesize("salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $rpic['picfile']);
																	// $rpic['pic_width'] = $width;
																	// $rpic['pic_height'] = $height;
																	$pic_list[] = $rpic;
																}
													?>
													<div  class="tab-pane fade <?php echo $section_row['state'] . ' ' . $class;?>" id="TC_<?php echo $section_row['stub'];?>" >
														<!-- Show thumbnails of pictures uploaded under this section -->
														<!-- <h4 class="text text-color"><?php echo $section;?></h4> -->
														<!-- DRAG & DROP AREA -->
														<div class="row">
															<div class="col-sm-12">
																<div class="text-info text-center dragarea" id="DDA_<?= $section_row['stub'];?>"
																	 style="display: <?= $num_drops == 0 ? 'none' : 'block';?>"
																	 data-section="<?= $section;?>"
																	 data-maxuploads="<?= $section_row['max_pics_per_entry'];?>"
																	 data-stub="<?= $section_row['stub'];?>"
																	 data-available-drops="<?= $num_drops;?>" >
																	<span class="lead">Drag and Drop multiple JPEG Files HERE (max <span class="available-drops-display"><?= $num_drops;?></span>)</span>
																	<br>
																	Wait for the box to turn <span class="text-danger">Red</span> in Color before dropping the files.
																	<br>
																	Or Drag and Drop a single JPEG file on any vacant thumbnail below
																</div>
															</div>
														</div>
														<div class="row">
															<h4 class="text text-info">Pictures Uploaded under '<?php echo $section;?>' section:</h4>
															<?php
																// Generate Forms
																for ($idx = 0; $idx < $section_row['max_pics_per_entry']; ++ $idx ) {

																	$pic_box_id = "PIC_" . $section_row['stub'] . "_" . $idx;
																	if (isset($pic_list[$idx])) {
																		$rpic = $pic_list[$idx];
																		$pic_id = $rpic['pic_id'];
																		$pic_title = $rpic['title'];
																		$pic_file = $rpic['picfile'];
																		$pic_link = "/salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $pic_file;
																		$pic_tn_link = "/salons/" . $contest_yearmonth . "/upload/" . $section . "/tn/" . $pic_file;
																		$rejection_text = rejection_text($rpic['notifications']);
																	}
																	else {
																		$pic_id = 0;
																		$pic_title = "";
																		//$pic_file = "preview.png";
																		//$pic_link = "/img/" . $pic_file;
																		//$pic_link = "javascript:void(0)";
																		$pic_tn_link = "/img/preview.png";
																		$rejection_text = "";
																	}
															?>
															<div class="col-sm-6 col-md-6 col-lg-6 well" id="<?= $pic_box_id;?>">
																<!-- Thumbnail is always displayed -->
																<div class="row">
																	<div class="caption text-center">
																		<b><span class="pic-title"><?=$pic_title;?></span></b>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-2"></div>
																	<div class="col-sm-8 thumbnail">
																		<img class="img-responsive pic-link dragarea"
																			 data-has-pic="<?= $pic_id == 0 ? 'no' : 'yes';?>"
																			 data-stub="<?= $section_row['stub'];?>"
																			 data-idx="<?= $idx;?>"
																			 style="margin-left:auto; margin-right:auto; max-height: 200px;"
																			 src="<?=$pic_tn_link;?>" >
																	</div>
																	<div class="col-sm-2"></div>
																</div>
																<div class="row">
																	<div class="small rejection-text" style="display: <?= $rejection_text == "" ? "none" : "block";?>">
																		<span class="text-danger notifications">Notifications : </span><span class="rejection-text"><?=$rejection_text;?></span>
																	</div>
																</div>
																<?= generate_edit_form($contest_yearmonth, $tr_user['profile_id'], $section, $section_row, $idx, $pic_list);?>
															</div>
															<?php
																	if ($idx % 2 == 1) {
															?>
															<div class="clearfix"></div>
															<?php
																	}
																}
															?>
														</div>
													</div>
													<?php
															}
														}
													?>
												</div>
											</div>
											<!-- END OF DIGITAL TAB CONTENT -->
											<?php
												}
												if ($num_print_sections > 0) {
											?>
											<!-- PRINT TAB CONTENT -->
											<div id="print-tc"
												 class="tab-pane fade <?php echo ($user_print_sections == 0) ? "disabled" : "";?> <?php echo ($active_section_type == "P") ? "active in" : "";?> ">
												<!-- Mailing Instructions for Print Sections -->
												<div class="well">
													<div class="row">
														<div class="col-sm-8">
															<p><b><span style="color: red;">Instructions for submitting prints:</span></b></p>
															<ol>
																<li>Upload a digital version of the picture conforming to the maximum dimensions similar to the Digital submission.</li>
																<li>Note down the code displayed below the thumbnail of the uploaded picture and write it on the back of the picture.</li>
																<li><span style="color:red;"><b>Do not write Title of the picture or your name or any other information on the back of the picture !</b></span></li>
																<li>Allowed print sizes - <b>Long Side :</b> <i>15 - 18 inches (max 18 inches)</i> & <b>Short Side : </b> <i> 10 - 12 inches (max 12 inches).</li>
															</ol>
														</div>
														<div class="col-sm-4">
															<b><span style="color: red;">Mail Prints to:</span></b><br>
															<div style="margin-left: 0px; font-weight: bold;">
															<?php
																$sql = "SELECT * FROM team WHERE yearmonth = '$contest_yearmonth' AND is_print_coordinator = '1' ";
																$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																if (mysqli_num_rows($query) == 0) {
															?>
																	TO BE ANNONCED<br>Get in touch with YPS
															<?php
																}
																else {
																	$row = mysqli_fetch_array($query);
																	$address_html = str_replace("|", "<br>", $row['address']);
															?>
																<?= $row['member_name'];?><br>
																<?= $address_html;?><br>
																Phone : <?= $row['phone'];?><br>
																Email : <?= $row['email'];?>
															<?php
																}
															?>
															</div>
														</div>
													</div>
												</div>
												<!-- TABS FROR PRINT SECTIONS -->
												<ul class="nav nav-pills">
													<?php
														foreach($section_list AS $section => $section_row) {
															if ($section_row['section_type'] == "P") {
																if ($section_row['active'])
																	$class = "active";
																else
																	$class = "";
													?>
													<li id="NP_<?php echo $section_row['stub'];?>"
																class="track-print-section <?php echo $section_row['state'] . ' ' . $class;?>" data-section="<?php echo $section;?>"
																data-submission="<?php echo ($section_row['submission_last_date'] >= DATE_IN_SUBMISSION_TIMEZONE) ? 'open' : 'closed';?>" >
														<a data-toggle="pill" href="#TC_<?php echo $section_row['stub'];?>" ><?= $section;?></a>
													</li>
													<?php
															}
														}
													?>
												</ul>
												<div class="tab-content">
													<?php
														foreach($section_list AS $section => $section_row) {
															if ($section_row['section_type'] == "P") {
																if ($section_row['active']) {
																	$class = "active in";
																}
																else
																	$class = "";
																// Create a list of pictures to display
																$sql = "SELECT * FROM pic WHERE yearmonth = '$contest_yearmonth' AND profile_id = '" . $tr_user['profile_id'] . "' AND section = '$section' ";
																$qpic = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
																$num_drops = $section_row['max_pics_per_entry'] - mysqli_num_rows($qpic);
																$pic_list = [];
																while ($rpic = mysqli_fetch_array($qpic, MYSQLI_ASSOC)) {
																	// list($width, $height) = getimagesize("salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $rpic['picfile']);
																	// $rpic['pic_file_size'] = filesize("salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $rpic['picfile']);
																	// $rpic['pic_width'] = $width;
																	// $rpic['pic_height'] = $height;
																	$pic_list[] = $rpic;
																}
													?>
													<div  class="tab-pane fade <?php echo $section_row['state'] . ' ' . $class;?>" id="TC_<?php echo $section_row['stub'];?>" >
														<!-- Show thumbnails of pictures uploaded under this section -->
														<!-- <h4 class="text text-color"><?php echo $section;?></h4> -->
														<!-- DRAG & DROP AREA -->
														<div class="row">
															<div class="col-sm-12">
																<div class="text-info text-center dragarea" id="DDA_<?= $section_row['stub'];?>"
																	 style="display: <?= $num_drops == 0 ? 'none' : 'block';?>"
																	 data-section="<?= $section;?>"
																	 data-maxuploads="<?= $section_row['max_pics_per_entry'];?>"
																	 data-stub="<?= $section_row['stub'];?>"
																	 data-available-drops="<?= $num_drops;?>" >
																	<span class="lead">Drag and Drop multiple JPEG Files HERE (max <span class="available-drops-display"><?= $num_drops;?></span>)</span>
																	<br>
																	Wait for the box to turn <span class="text-danger">Red</span> in Color before dropping the files.
																	<br>
																	Or Drag and Drop a single JPEG file on an vacant thumbnail below
																</div>
															</div>
														</div>
														<div class="row">
															<h4 class="text text-info">Pictures Uploaded under '<?php echo $section;?>' section:</h4>
															<?php
																// Generate Forms
																for ($idx = 0; $idx < $section_row['max_pics_per_entry']; ++ $idx ) {

																	$pic_box_id = "PIC_" . $section_row['stub'] . "_" . $idx;
																	if (isset($pic_list[$idx])) {
																		$rpic = $pic_list[$idx];
																		$pic_id = $rpic['pic_id'];
																		$pic_title = $rpic['title'];
																		$pic_file = $rpic['picfile'];
																		$pic_link = "/salons/" . $contest_yearmonth . "/upload/" . $section . "/" . $pic_file;
																		$pic_tn_link = "/salons/" . $contest_yearmonth . "/upload/" . $section . "/tn/" . $pic_file;
																		$rejection_text = rejection_text($rpic['notifications']);
																	}
																	else {
																		$pic_id = 0;
																		$pic_title = "";
																		// $pic_file = "preview.png";
																		// $pic_link = "/img/" . $pic_file;
																		$pic_tn_link = "/img/preview.png";
																		$rejection_text = "";
																	}

															?>
															<div class="col-sm-6 col-md-6 col-lg-6 well" id="<?= $pic_box_id;?>">
																<!-- Thumbnail is always displayed -->
																<div class="row">
																	<div class="caption text-center">
																		<b><span class="pic-title"><?=$pic_title;?></span></b>
																		<br>
																		<span style="color:red; <?= $pic_id == 0 ? 'display: none;' : '';?>" class="pic-eseq">Code: <b><?=$rpic['eseq'];?></b></span>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-2"></div>
																	<div class="col-sm-8 thumbnail">
																		<img class="img-responsive pic-link dragarea"
																			 data-has-pic="<?= $pic_id == 0 ? 'no' : 'yes';?>"
																			 data-stub="<?= $section_row['stub'];?>"
																			 data-idx="<?= $idx;?>"
																			 style="margin-left:auto; margin-right:auto; max-height: 200px;"
																			 src="<?=$pic_tn_link;?>" >
																	</div>
																	<div class="col-sm-2"></div>
																</div>
																<div>
																	<div class="small rejection-text" style="display: <?= $rejection_text == "" ? "none" : "block";?>">
																		<span class="text-danger notifications">Notifications : </span><span class="rejection-text"><?=$rejection_text;?></span>
																	</div>
																</div>
																<?= generate_edit_form($contest_yearmonth, $tr_user['profile_id'], $section, $section_row, $idx, $pic_list);?>
															</div>
															<?php
																	if ($idx % 2 == 1) {
															?>
															<div class="clearfix"></div>
															<?php
																	}
																}
															?>
														</div>
													</div>
													<?php
															}
														}
													?>
												</div>
											</div>
											<!-- END OF PRINT TAB CONTENT -->
											<?php
												}
											?>
										</div>
									</div>
									<!-- END OF DISPLAY OF CURRENT UPLOADS BY SECTION -->

									<?php
										if ($contestIsNoToPastAcceptance > 0) {
									?>
									<!-- Show accepted pictures from the previous Salons -->
									<div class="panel-group">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h5 class="panel-title" style="color:red;">
													<a data-toggle="collapse" href="#past_acceptances"><b>Pictures accepted in recent YPS Digital Salons</b></a>
												</h5>
											</div>
											<div class="panel-collapse <?php echo ($hide_past_acceptances ? 'collapse' : 'in');?>" id="past_acceptances">
												<div class="panel-body">
													<p class="text text-justified">As per Salon Terms and Conditions and as per generally accepted practices of the organizations
															providing patronage to this Salon, your pictures that have won Acceptances (including awards / certificates of merits /
															honorable mentions) in the past Salons of YPS are not eligible to be uploaded in this Salon. For your convenience, we
															have listed below the pictures submitted by you in the salons of YPS since 2016.
															These are meant to serve you as reference and are by no means exhaustive.</p>
													<p class="text text-justify">You have the ultimate responsibility to adhere to the Terms and Conditions of the Salon and that of the
															organizations providing patronage, to whom you may apply for honors. Please verify the submissions in the past before
															uploading pictures under this salon.</p>
													<!-- Pictures from archives go here -->
													<?php
														// Get contest details
														$sql  = "SELECT yearmonth, contest_name, archived FROM contest ";
														$sql .= " WHERE yearmonth != '$contest_yearmonth' ";
														$sql .= " ORDER BY yearmonth DESC ";
														$qpc = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
														while ($rpc = mysqli_fetch_array($qpc)) {
															$pc_yearmonth = $rpc['yearmonth'];
															$pc_contest_name = $rpc['contest_name'];
															$pc_archived = ($rpc['archived'] == '1');
															// Check for accepted pictures
															$sql  = "SELECT pic.section, pic.title, pic.picfile, ";
															$sql .= "       award.level AS award_level, award_name ";
															if ($pc_archived)
																$sql .= "FROM ar_pic pic, ar_pic_result pic_result, award ";
															else
																$sql .= "FROM pic, pic_result, award ";
															$sql .= "WHERE pic_result.yearmonth = '$pc_yearmonth' ";
															$sql .= "  AND pic_result.profile_id = '" . $tr_user['profile_id'] . "' ";
															$sql .= "  AND award.yearmonth = pic_result.yearmonth ";
															$sql .= "  AND award.award_id = pic_result.award_id ";
															$sql .= "  AND award.award_type = 'pic' ";
															$sql .= "  AND pic.yearmonth = pic_result.yearmonth ";
															$sql .= "  AND pic.profile_id = pic_result.profile_id ";
															$sql .= "  AND pic.pic_id = pic_result.pic_id ";
															$sql .= "  AND award.award_group IN ( ";
															$sql .= "            SELECT entrant_category.award_group FROM entrant_category ";
															$sql .= "             WHERE entrant_category.yearmonth = award.yearmonth ";
															$sql .= "               AND entrant_category.acceptance_reported = '1' ) ";
															$sql .= "ORDER BY pic.section ASC, award_level ASC ";

															$qpa = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
															if (mysqli_num_rows($qpa) > 0) {
													?>
													<div class="row">
														<h4 class="text text-color" style="padding-left: 10px;">Salon: <?php echo $pc_contest_name . " (" . substr($pc_yearmonth, 0, 4) . ")";?></h4>
													<?php
																$pics_in_row = 0;
																while ($rpa = mysqli_fetch_array($qpa)) {
																	$section = $rpa['section'];
																	$pic_title = $rpa['title'];
																	$pic_file = $rpa['picfile'];
																	$award_level = $rpa['award_level'];
																	$award_name = $rpa['award_name'];
													?>
														<div class="col-sm-3 col-md-3 col-lg-3 thumbnail">
															<div class="caption">
																<b><?=$section;?></b>
															</div>
															<div style="max-width:100%;" >
																<a href="/salons/<?=$pc_yearmonth;?>/upload/<?=$section;?>/<?=$pic_file;?>"
																		data-lightbox="past-acceptances"
																		data-title="<?=$pic_title;?>" >
																	<img class="img-responsive" style="margin-left:auto; margin-right:auto;"
																			src="/salons/<?=$pc_yearmonth;?>/upload/<?=$section;?>/tn/<?=$pic_file;?>" >
																</a>
															</div>
															<div class="caption text-center"><b><?=$pic_title;?></b></div>
														</div>
													<?php
																	++ $pics_in_row;
																	if ($pics_in_row % 4 == 0) {
																		$pics_in_row = 0;
													?>
														<div class="clearfix"></div>
													<?php
																	}
																}		// while there are past acceptances in this contest
													?>
														<div class="clearfix"></div>
													</div>		<!-- row -->
													<?php
															}	// if contest has acceptances
														}		// for each past contest
													?>
												</div>
												<div class="panel-footer">
													<!-- do not show this again -->
													<label>
														<input type="checkbox" name="hide_past_acceptances" id="hide_past_acceptances" value="yes" <?php echo ($hide_past_acceptances == true ? "checked" : "");?> >
														Keep Past Acceptances hidden
													</label>
												</div>
											</div>
										</div>
									</div>	<!-- end of collapsible previous acceptances panel -->
									<?php
										}
									?>
								</div>
							</div>		<!-- / #myTab -->
						</div>			<!-- / #userCart -->
					</div>
				</div>
			</div>						<!-- / .row -->
		</div> <!-- / .container -->
		<?php include_once("inc/footer.php");?>
	</div>		<!-- / .wrapper -->


    <!-- Style Toggle -->

	<?php include_once("inc/settingToggle.php") ;?>
    <!-- JavaScript
    ================================================== -->
	<?php include_once("inc/scripts.php"); ?>


	<!-- Form specific scripts -->
    <script src="plugin/lightbox/js/lightbox.min.js"></script>
    <script>
    $(document).ready(function(){
		$('html, body').animate({ scrollTop: $("#myTab").offset().top-100 }, 500);
	});
    </script>

    <!-- JS Custom -->
	<script>
	// Keep Track of selection of Section Tabs. Remembers across pages
	// Common routine to fire ajax call to set session variables
	function setActiveSession() {
		$.post("ajax/set_active_session.php",
			   {
					hide_upload_instructions : $("#hide_upload_instructions").is(":checked"),
					active_section_type : $("#upload_section_type").val(),
					active_digital_section : $("#upload_digital_section").val(),
					active_print_section : $("#upload_print_section").val(),
					hide_past_acceptances : $("#hide_past_acceptances").is(":checked"),
				}
		);

	}
	$(document).ready(function() {

		// Set listener to keep track of hide_upload_instruction checkbox
		$("#hide_upload_instructions").click(function() {
			let hide_upload_instructions = $("#hide_upload_instructions").is(":checked");
			if (hide_upload_instructions)
				$("#upload_instructions").collapse({toggle: true});
			setActiveSession();
		});

		// Set listener to keep track of hide_past_acceptances checkbox
		$("#hide_past_acceptances").click(function() {
			let hide_past_acceptances = $("#hide_past_acceptances").is(":checked");
			if (hide_past_acceptances)
				$("#past_acceptances").collapse({toggle: true});
			setActiveSession();
		});

		// Set Listeners to keep the section values updated
		$(".track-type").each(function(index, item) {
			$(this).click(function(e) {
				// Do not allow the tab to be clicked open when a "disabled" class is set
				if ($(this).hasClass("disabled")) {
					e.preventDefault();
					return false;
				}
				// Handle Tab change and set _SESSION variables
				let upload_section_type = $(this).attr("data-section-type");
				$("#upload_section_type").val(upload_section_type);
				setActiveSession();
			});
		});

		$(".track-digital-section").each(function(index, item) {
			$(this).click(function(e) {
				// Do not allow the tab to be clicked open when a "disabled" class is set
				if ($(this).hasClass("disabled")) {
					e.preventDefault();
					return false;
				}
				// Handle Tab change and set _SESSION variables
				let upload_digital_section = $(this).attr("data-section");
				$("#upload_digital_section").val(upload_digital_section);
				setActiveSession();
			});
		});

		$(".track-print-section").each(function(index, item) {
			$(this).click(function(e) {
				// Do not allow the tab to be clicked open when a "disabled" class is set
				if ($(this).hasClass("disabled")) {
					e.preventDefault();
					return false;
				}
				// Handle Tab change and set _SESSION variables
				let upload_print_section = $(this).attr("data-section");
				$("#upload_print_section").val(upload_print_section);
				setActiveSession();
			});
		});
	});
	</script>

	<!-- Picture Upload Handling Functions -->
	<script>
	function setToPreview(thumbnail, form) {
		// Set Thumnail to Preview
		//thumbnail.find("a.pic-link").removeAttr("href");
		//thumbnail.find("a.pic-link").removeData("lightbox");
		//thumbnail.find("a.pic-link").removeData("title");
		thumbnail.find("img.pic-link").attr("src", "img/preview.png");
		thumbnail.find(".pic-title").html("");
		thumbnail.find(".pic-eseq").hide();
		thumbnail.find("span.rejection-text").html("");
		thumbnail.find("div.rejection-text").hide();

		// Adjust visibility of Delete Picture and Upload Picture links
		let stub = form.data("stub");
		let idx = form.data("idx");
		$("#DELPIC_" + stub + "_" + idx).data("pic-id", "0");
		$("#DELPIC_" + stub + "_" +idx).hide();
		$("#UPLDPIC_" + stub + "_" +idx).html("Upload Picture");
		$("#UPLDPIC_" + stub + "_" +idx).show();

		// Set Form to Preview
		form.data("pic-id", "0");
		form.find("[name='upload_mode']").val("new");
		form.find("[name='upload_pic_id']").val("0");
		form.find("[name='upload_picfile']").val("");
		form.find("[name='exif']").val("");
		form.find("[name='img_title']").val("");
		form.find("[name='submittedfile']").val(null);
		form.find("[name='file_size_disp']").val("");
		form.find("[name='file_size']").val("0");
		form.find("[name='width']").val("");
		form.find("[name='height']").val("");
		form.find("span.resize_dimension").html("");
		form.find("div.dimension_warning").hide();
		form.find("[name='cancel_upload']").hide();
		form.find("[name='upload_img']").html("Submit");
		form.find("div.upload-progress-bar").hide();	// Hide progress bar when showing preview

		// Empty Drag & Drop Fields
		form.find("[name='dropped-file-name']").val("");
		form.find("[name='dropped-file-tmp-name']").val("");
		form.find("[name='dropped-file-upload-error']").val("");
	}
	</script>

	<script>
	function setToPic(thumbnail, form, pic) {
		// Set Thumnail to Preview
		//thumbnail.find("a.pic-link").attr("href", "/salons/" + pic.yearmonth + "/upload/" + pic.section + "/" + pic.picfile);
		//thumbnail.find("a.pic-link").data("lightbox", pic.section);
		//thumbnail.find("a.pic-link").data("title", pic.title);

		thumbnail.find("img.pic-link").attr("src", "/salons/" + pic.yearmonth + "/upload/" + pic.section + "/tn/" + pic.picfile);
		thumbnail.find(".pic-title").html(pic.title);
		thumbnail.find(".pic-eseq").html("Code : <b>" + pic.eseq + "</b>");
		thumbnail.find(".pic-eseq").show();
		thumbnail.find("span.rejection-text").html(pic.rejection_text);
		if (pic.rejection_text && pic.rejection_text != "")
			thumbnail.find("div.rejection-text").show();
		else
			thumbnail.find("div.rejection-text").hide();

		// Adjust visibility of Delete Picture and Upload Picture links
		let stub = form.data("stub");
		let idx = form.data("idx");
		$("#DELPIC_" + stub + "_" + idx).data("pic-id", pic.pic_id);
		$("#DELPIC_" + stub + "_" +idx).show();
		$("#UPLDPIC_" + stub + "_" +idx).html("Edit Picture");
		$("#UPLDPIC_" + stub + "_" +idx).show();

		$("#ESEQ_" + stub + "_" + idx).html(pic.eseq);

		// Set Form to Preview
		form.data("pic-id", pic.pic_id);
		form.find("[name='upload_mode']").val("edit");
		form.find("[name='upload_pic_id']").val(pic.pic_id);
		form.find("[name='upload_picfile']").val(pic.picfile);
		form.find("[name='exif']").val(pic.exif);
		form.find("[name='img_title']").val(pic.title);
		form.find("[name='submittedfile']").val(null);
		form.find("[name='file_size_disp']").val((pic.file_size / 1024 / 1024).toFixed(2) + " MB");
		form.find("[name='file_size']").val(pic.file_size);
		form.find("[name='width']").val(pic.width);
		form.find("[name='height']").val(pic.height);
		form.find("span.resize_dimension").html("");
		form.find("div.dimension_warning").hide();
		form.find("[name='cancel_upload']").hide();

		// Empty Drag & Drop Fields
		form.find("[name='dropped-file-name']").val("");
		form.find("[name='dropped-file-tmp-name']").val("");
		form.find("[name='dropped-file-upload-error']").val("");
	}
	</script>

	<script>
	// Delete a Picture for the user
	function deletePicture(elem)	{
		let yearmonth = $(elem).data("yearmonth");
		let profile_id = $(elem).data("profile-id");
		let stub = $(elem).data("stub");
		let idx = $(elem).data("idx");
		let pic_id = $(elem).data("pic-id");
		let thumbnail = $("#PIC_" + stub + "_" + idx);
		let form = $("#UF_" + stub + "_" + idx);

        if(pic_id > 0 && pic_id != '') {
        	swal({
            	title: 'Delete Image',
            	text:  'Are you sure you want to DELETE this Image!',
				icon: "warning",
				buttons: true,
				dangerMode: true,
        	})
			.then(function (deletePic) {
				if (deletePic) {
					// Clear Messages
					form.find("div.pic-info").hide();
					form.find("span.upload-error").html("");
					form.find("span.upload-success").html("");

					$('#loader_img').show();
					$.ajax({
						url: "ajax/delete_pic.php",
						type: "POST",
						data: { yearmonth, profile_id, pic_id, },
						success: function(response) {
							$('#loader_img').hide();
							let data = JSON.parse(response);
							if(data.success){
								// Replace thumbnail and form with preview
								setToPreview(thumbnail, form);
								enableDD(stub, idx);
								swal("Success", "The Picture has been successfully deleted. You can upload another picture in its place", "success");
							}
							else{
								swal("Upload Failed!", response.msg, "error");
							}
						},
						error : function(xHr, status, error) {
							$('#loader_img').hide();
							swal("Updation Failed!", "Unable to complete the operation (" + status + ") . Try again!", "error");
						}
					});
				}
			});
        }
	}
    </script>

	<script>
	function cancelUpload(event, btn) {

		event.preventDefault();

		let form = $(btn.form);
		let stub = form.data("stub");
		let idx = form.data("idx");
		let yearmonth = form.data("yearmonth");
		let profile_id = form.data("profile-id");
		let pic_id = form.data("pic-id");
		let thumbnail = $("#PIC_" + stub + "_" + idx);
		$("#UPLD_FILE_" + stub + "_" + idx).show();			// Show File selection again
		// Restore Picture Details from the server
		if(pic_id == 0 ) {
			setToPreview(thumbnail, form);
			form.hide();
			$("#UPLDPIC_" + stub + "_" + idx).show();
			enableDD(stub, idx);
		}
		else {
			$.post("ajax/get_pic_details.php", { yearmonth, profile_id, pic_id }, function(response){
				result = JSON.parse(response);
				if (result.success) {
					// Update Picture Display
					setToPic(thumbnail, form, result.pic);
					form.hide();
					$("#UPLDPIC_" + stub + "_" + idx).show();
					// Cannot Enable Drg and Drop - enableDD(stub, idx);
				}
				else
					form.find("span.upload-error").html("Unable to retrieve details of previously uploaded picture! Try again!");
			});
		}

	}
	</script>

	<script>
	// Show Upload Form
	function showUploadForm(form_id, field_id) {
		$("#" + form_id).show();
		$("#" + field_id).hide();
	}
	// Hide Upload Form
	function hideUploadForm(form_id, field_id) {
		$("#" + form_id).hide();
		$("#" + field_id).show();
	}
	</script>


	<!-- EXIF Data Extraction -->
	<script src="plugin/lightbox/js/lightbox.min.js"></script>

	<script>
	function showPhoto(picfile, form) {
		/* Import JPEG Meta File */
		var $j = this.JpegMeta.JpegFile;
		let stub = form.data("stub");
		let idx = form.data("idx");

		form.find("div.progress-bar").css("width", "0%");
		form.find("div.upload-progress-bar").css("display", "none");

		// File Size checking
		form.find("[name='file_size']").val(picfile.size);
		form.find("[name='file_size_disp']").val((picfile.size / 1024 / 1024).toFixed(2) + " MB");
		if (picfile.size > <?php echo (MAX_PIC_FILE_SIZE_IN_MB * 1024 * 1024);?>)
			form.find(".file_size_warning").css("display", "block");
		else
			form.find(".file_size_warning").css("display", "none");

		// Meta values checking
		var reader = new FileReader();

		reader.onload = function (e) {
			$("#PIC_" + stub + "_" + idx).find("img").attr('src', e.target.result);
			form.find("[name='cancel_upload']").css("display", "block");
			// Read and Extract EXIF
			var jpeg = new $j(atob(this.result.replace(/^.*?,/,'')), picfile);
			// Height & Width
			var pic_width = jpeg.general.pixelWidth.value;
			var pic_height = jpeg.general.pixelHeight.value;
			form.find("[name='width']").val(pic_width);
			form.find("[name='height']").val(pic_height);
			var exif = getExifData(jpeg);
			form.find("[name='exif']").val(JSON.stringify(exif));		// exif.error contains error message where applicable

			// Resize Suggestion
			var max_height = <?php echo MAX_PIC_HEIGHT;?>;
			var max_width = <?php echo MAX_PIC_WIDTH;?>;
			var resize_height = pic_height;
			var resize_width = pic_width;
			if (pic_width > max_width || pic_height > max_height) {
				// First, adjust height to make resize_width = max_width;
				resize_height = Math.floor(resize_height * max_width / resize_width);
				resize_width = max_width;
				// Second, if height is still larger than allowed max_height, reduce dimensions so that height = max_height
				if (resize_height > max_height) {
					resize_width = Math.floor(resize_width * max_height / resize_height);
					resize_height = max_height;
				}
				form.find("span.resize_dimension").html("Too Large ! Resize to " + resize_width + "x" + resize_height);
				form.find("div.dimension_warning").css("display", "block");
			}
			else {
				form.find("div.dimension_warning").css("display", "none");
				if (pic_width < max_width && pic_height < max_height) {
					let html = "The picture is smaller than the maximum permitted dimensions of <?php echo MAX_PIC_WIDTH;?>px width ";
					html += " & <?php echo MAX_PIC_HEIGHT;?>px height. You will still be able to upload the picture. For best impact, ";
					html += " please upload a larger picture.";
					form.find("span.pic-info").html(html);
					form.find("div.pic-info").show();
				}
			}
		};
		reader.readAsDataURL(picfile);
	}

	function loadPhoto(input) {
		if (input.files && input.files[0] != "") {
			let form = $(input).parents("[name='upload-form']").filter("form");
			showPhoto(input.files[0], form);
			disableDD(form.data("stub"), form.data("idx"));
		}
	}

	</script>


	<!-- Form Validation -->
	<script src="plugin/validate/js/jquery.validate.min.js"></script>

	<!-- Custom Validation Functions -->
	<script src="custom/js/validate.js"></script>

	<!-- Form Validation & Submission -->
	<script>
	// Attach validation to an upload form
	function validate_form(index, elem){
		$(this).validate({
			rules:{
				img_title: {
					required:true,
					maxlength: 35,		// as per PSA requirement
				},
				submittedfile: {
					required: {
						param: true,
						depends: function() { return $(this).find("[name='upload_mode']").val() == "new"; },
					},
				},
			},
			messages:{
				img_title:{
					required:'Image Title is required',
				},
				submittedfile:{
					required:'Select a picture to upload',
				},
			},
			errorElement: "div",
			errorClass: "valid-error",
			submitHandler: function(form) {

				// Perform validations on readiness of hidden fields
				var pic_width = $(form).find("[name='width']").val();
				var pic_height = $(form).find("[name='height']").val();
				var file_size = $(form).find("[name='file_size']").val();
				var formData = new FormData(form);

				if (formData.get("upload_picfile") == "" && formData.get("submittedfile").name == "" && formData.get("dropped-file-tmp-name") == "") {
					swal("Picture Warning", 'No picture selected or dropped for upload, or dropped file not accepted by server. Select a file and try upload', "error");
				}
				else if (file_size > <?php echo (MAX_PIC_FILE_SIZE_IN_MB * 1024 * 1024);?> ||
						pic_width > <?php echo MAX_PIC_WIDTH;?> ||
						pic_height > <?php echo MAX_PIC_HEIGHT;?> ) {
					swal("Picture Warning", 'Picture file does not conform to required size and dimensions. Please check the warnings on the screen and resize accordingly.', "error");
				}
				else {
					//form.submit();
					// Disable Upload Button
					$(form).find("[name='upload_img']").attr("disabled", true);

					// Show Progress Bar
					let progress_bar = $(form).find("div.progress-bar");
					progress_bar.attr("aria-valuenow", "0");
					progress_bar.css("width", "0%");
					progress_bar.css("background-color", "orange");
					progress_bar.html("Submitting...")
					$(form).find("div.upload-progress-bar").show();

					// Clear Messages
					$(form).find("div.pic-info").hide();
					$(form).find("span.upload-error").html("");
					$(form).find("span.upload-success").html("");

					// Create Thumbnail and add as attachment to formData
					// Create a new canvas if one does not exist
					let rzstub = $(form).data("stub");
					let rzidx = $(form).data("idx");
					let canvas_id = "canvas_" + rzstub + "_" + rzidx;
					let canvas;
					if (document.getElementById(canvas_id))
						canvas = document.getElementById(canvas_id);
					else {
						canvas = document.createElement('canvas');
						canvas.id = canvas_id;
					}
					let ctx = canvas.getContext('2d');
					canvas.width = 480;		// width = 480 pixels
					canvas.height = 480 / pic_width * pic_height;
					// Find the preview image
					let img = $("#PIC_" + rzstub + "_" +rzidx).find("img.pic-link").get(0);
					ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

					// Get BLOB from Canvas and append to form data
					canvas.toBlob(function(imgBlob) {
							formData.append("thumbnail", imgBlob);
							saveFormData(form, formData);			// Save on the server
						},
						"image/jpeg"
					);
				}
			},
		});
	}

	function saveFormData(form, formData) {

		let progress_bar = $(form).find("div.progress-bar");
        
		// Gather Data - Add dropped file if present
		$.ajax({
				url: "ajax/upload_img_new.php",
				type: "POST",
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				xhr : function() {
						// Add a hook to monitor upload progress
						let xhr = $.ajaxSettings.xhr();

						if (xhr.upload) {
							xhr.upload.onprogress = function(e) {
								// Update Progress Bar
								if (e.lengthComputable) {
									var percentComplete = Math.round((e.loaded / e.total) * 100);
									progress_bar.attr("aria-valuenow", percentComplete.toFixed(0));
									progress_bar.css("width", percentComplete.toFixed(0) + "%");
									console.log(percentComplete + '% uploaded');
								}
							}
						}

						// return xhr with custom hook to show progress
						return xhr;
				},
				success: function(response) {
					$(form).find("[name='upload_img']").removeAttr("disabled");
					let data = JSON.parse(response);
				    console.log('data is', data);
					if(data.success){
						// Change progress-bar to mark completion
						progress_bar.css("background-color", "green");
						progress_bar.html("Picture submitted");
						// Set all required values from returned values
						// Prepare form and thumbnail jquery objects
						// Gather Info
						let stub = $(form).data("stub");
						let idx = $(form).data("idx");
						setToPic($("#PIC_" + stub + "_" + idx), $(form), data.pic);
						$(form).find("[name='cancel_upload']").hide();
						$(form).find("span.upload-success").html("Picture successfully uploaded/updated!");
						// Warn potential Rejection
						if (data.pic.rejection_text && data.pic.rejection_text != "") {
							let notification = document.createElement("div");
							notification.innerHTML = "<p>The following potential issues have been found. Please double check and replace picture if necessary:</p>" + data.pic.rejection_text;
							swal({title: "CHECK", content: notification, icon: "warning",});
						}
					}
					else{
						progress_bar.css("background-color", "red");
						progress_bar.html("Submission Failed");
			 			$(form).find("span.upload-error").html("Upload failed (" + data.msg + ")");
					}
				},
				error : function(xHr, status, error) {
				    alert('failed');
					progress_bar.css("background-color", "red");
					progress_bar.html("Failed, try again");
					$(form).find("[name='upload_img']").removeAttr("disabled");
					$(form).find("span.upload-error").html("Unable to complete (" + status + "). Try again!");
				}
		});
	}

	$(document).ready(function() {
		// Validator for Picture Upload Form
		$("form").filter("[name='upload-form']").each(validate_form);
	});

	</script>

	<!-- Drag and Drop Functions -->
	<script>
	// Keep Track of Open Slots and Number of Open Slots for Drag and Drop -
	// Enable an Image for Drag and Drop
	function enableDD(stub, idx) {
		// Set img.has-pic = "no" so that drag and drop is allowed
		$("#PIC_" + stub + "_" +idx).find("img.dragarea").data("has-pic", "no");

		// Update Drag and Drop DIV with number of pics with has-pic = "no"
		dragarea = $("#DDA_" + stub);
		let has_pics = 0;
		for (let i = 0; i < dragarea.data("maxuploads"); ++i) {
			if ($("#PIC_" + stub + "_" + i).find("img.dragarea").data("has-pic") == "yes")
				++ has_pics;
		}
		let drops = dragarea.data("maxuploads") - has_pics;
		dragarea.data("available-drops", drops);
		dragarea.find("span.available-drops-display").html(drops);
		if (drops != 0)
			dragarea.show();
	}

	// Disable an image for Drag and Drop
	function disableDD(stub, idx) {
		// Set img.has-pic = "yes" so that further drag and drop is prevented
		$("#PIC_" + stub + "_" +idx).find("img.dragarea").data("has-pic", "yes");

		// Update Drag and Drop DIV with number of pics with has-pic = "no"
		let dragarea = $("#DDA_" + stub);
		let has_pics = 0;
		for (let i = 0; i < dragarea.data("maxuploads"); ++i) {
			if ($("#PIC_" + stub + "_" + i).find("img.dragarea").data("has-pic") == "yes")
				++ has_pics;
		}
		let drops = dragarea.data("maxuploads") - has_pics;
		dragarea.data("available-drops", drops);
		dragarea.find("span.available-drops-display").html(drops);
		if (drops == 0)
			dragarea.hide();
	}

	// Handle dropping of image on the thumbnail
	function dropOnImage(imgTarget, file) {
		if (file.type == "image/jpeg") {
			let stub = imgTarget.data("stub");
			let idx = imgTarget.data("idx");
			if (stub != undefined && idx != undefined) {
				let form = $("#UF_" + stub + "_" +idx);

				// Populate the Form
				$("#UPLDPIC_" + stub + "_" + idx).hide();		// Hide the Upload Link
				form.find("[name='img_title']").val(file.name.replace(/\.jpe*g/, ""));	// File name as title
				form.find("[name='submittedfile']").val(null);		// Remove any file selected
				$("#UPLD_FILE_" + stub + "_" + idx).hide();			// Hide File Selection
				showPhoto(file, form);	// Load thumbnail
				form.show();	// Display upload form
				disableDD(stub, idx);

				// Upload Dropped File to server
				form.find("[name='dropped-file-name']").val(file.name);
				let fd = new FormData();
				fd.append("yearmonth", form.data("yearmonth"));
				fd.append("profile_id", form.data("profile-id"));
				fd.append("stub", stub);
				fd.append("droppedfile", file);

				// Show Progress Bar
				let progress_bar = form.find("div.progress-bar");
				progress_bar.attr("aria-valuenow", "0");
				progress_bar.css("width", "0%");
				progress_bar.css("background-color", "orange");
				progress_bar.html("Sending...")
				form.find("div.upload-progress-bar").show();

				// Invoke uploader
				form.find("[name='dropped-file-tmp-name']").val("");
				form.find("[name='dropped-file-upload-error']").val("");
				$.ajax({
						url: "ajax/upload_dropped_photo.php",
						type: "POST",
						data: fd,
						cache: false,
						processData: false,
						contentType: false,
						xhr : function() {
								// Add a hook to monitor upload progress
								let xhr = $.ajaxSettings.xhr();

								if (xhr.upload) {
									xhr.upload.onprogress = function(e) {
										// Update Progress Bar
										if (e.lengthComputable) {
											var percentComplete = Math.round((e.loaded / e.total) * 100);
											progress_bar.attr("aria-valuenow", percentComplete.toFixed(0));
											progress_bar.css("width", percentComplete.toFixed(0) + "%");
											console.log(percentComplete + '% uploaded');
										}
									}
								}

								// return xhr with custom hook to show progress
								return xhr;
						},
						success: function(response) {
							let data = JSON.parse(response);
							if(data.success){
								form.find("[name='dropped-file-tmp-name']").val(data.tmpfile);
								// progress_bar.css("background-color", "green");
								progress_bar.html("Click Submit button");
							}
							else{
								progress_bar.css("background-color", "red");
								progress_bar.html("Sending Failed");
								form.find("[name='dropped-file-upload-error']").val("Upload failed (" + data.msg + ")");
							}
						},
						error : function(xHr, status, error) {
							progress_bar.css("background-color", "red");
							progress_bar.html("Sending Failed");
							form.find("[name='dropped-file-upload-error']").val("Unable to complete (" + status + "). Try again!");
						}
				});
			}
		}
	}

	// Find next open image slot under this section
	function findDropSlot(divTarget) {
		let div = divTarget.data();
		let pic;
		if (div.stub && div.maxuploads) {
			for (let i = 0; i < div.maxuploads; ++i) {
				imgTarget = $("#PIC_" + div.stub + "_" +i).find("img.dragarea");
				if (imgTarget.data("has-pic") == "no")
					return imgTarget;
			}
		}
	}

	// Install Handlers
	$(document).ready(function() {
		// Handle Hover with Drop
		$(".dragarea").on("dragover dragleave", function(e){
			if ( (e.target.nodeName.toLowerCase() == "div" && $(e.target).data("available-drops") > 0) ||
				 (e.target.nodeName.toLowerCase() == "img" && $(e.target).data("has-pic") == "no") ) {
				e.stopPropagation();
				e.preventDefault();
				if (e.type == "dragover")
					$(e.target).addClass("hover");
				else
					$(e.target).removeClass("hover");
			}
		});

		// Handle Drop
		$(".dragarea").on("drop", function(e) {
			if ( (e.target.nodeName.toLowerCase() == "div" && $(e.target).data("available-drops") > 0) ||
				 (e.target.nodeName.toLowerCase() == "img" && $(e.target).data("has-pic") == "no") ) {
				e.stopPropagation();
				e.preventDefault();
				$(e.target).removeClass("hover");
				let files = [];
				if (e.dataTransfer && e.dataTransfer.files)
					files = e.dataTransfer.files;
				else if (e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files)
					files = e.originalEvent.dataTransfer.files;
				if (files.length > 0) {
					if (e.target.nodeName == "img" || e.target.nodeName == "IMG") {
						dropOnImage($(e.target), files[0]);
					}
					else {
						let available_drops = $(e.target).data("available-drops");
						for(let i = 0; i < files.length && i < available_drops; ++i) {
							dropOnImage(findDropSlot($(e.target)), files[i]);
						}
					}
				}
			}

		});
	});

	</script>


</body>

</html>

<?php
}
else{
	$_SESSION['err_msg'] = "Invalid Request !";
	header("Location: index.php");
	printf("<script>location.href='index.php'</script>");
}

?>

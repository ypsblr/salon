<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Constants
define("CONST_SS_WIDTH", 1920);
define("CONST_SS_HEIGHT", 1080);

function get_slideshow_config($yearmonth) {

	if (file_exists(__DIR__ . "/../salons/$yearmonth/blob/slideshow.json")) {
		// Load Partner Data
		$slideshow_config = json_decode(file_get_contents(__DIR__ . "/../salons/$yearmonth/blob/slideshow.json"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "Slideshow Definition garbled";
			return false;
		}

		return $slideshow_config;
	}
	else {
		return false;
	}

}

// Labels & Values
// Lists
$field_types = ["Image" => "image", "Text" => "text"];
$pic_image_fields = ["Picture" => "picfile", "Avatar" => "avatar", "Flag" => "flag"];
$pic_text_fields = ["Author" => "profile_name", "City" => "city", "Honors" => "honors", "Pic Title" => "title", "Section" => "section" ];
$section_image_fields = ["Jury 1 Avatar" => "jury_1_avatar", "Jury 2 Avatar" => "jury_2_avatar", "Jury 3 Avatar" => "jury_3_avatar"];
$section_text_fields = ["Section" => "section", "Jury 1 Name" => "jury_1_name", "Jury 2 Name" => "jury_2_name", "Jury 3 Name" => "jury_3_name"];


function get_label($array, $val) {
	foreach ($array as $key => $text) {
		if ($text == $val)
			return $key;
	}
	return "";
}

function get_val($array, $label) {
	if (isset($array[$label]))
		return $array[$label];
	return "";
}

function get_options($array, $selection = "") {
	$html = "";
	foreach ($array as $key => $value) {
		$html .= "<option value= '" . $value . "' " . ($value == $selection ? 'selected' : '') . ">" . $key . "</option>";
	}
	return $html;
}

function get_field($array, $field, $value) {
	if ($value == "")
		return get_label($array, $field);
	else
		return $field;
}

// Loads list of ttf font files
function filter_font_files($filename) {
	return preg_match("/ttf$/", $filename);
}

function ttf_list() {
	$font_folder = "../PHPImageWorkshop/font";
	return array_filter(scandir($font_folder), "filter_font_files");
}


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

	// Clone previous conf
	if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['copyfrom']) && file_exists("../salons/" . $_REQUEST['copyfrom'] . "/blob/slideshow.json"))
		copy("../salons/" . $_REQUEST['copyfrom'] . "/blob/slideshow.json", "../salons/" . $_REQUEST['yearmonth'] . "/blob/slideshow.json");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "0"
	);

	$yearmonth = 0;

	// Load sections for the contest
	$has_ss_conf = false;
	$has_ss_design = false;
	$is_international = false;
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
		$is_international = ($salon['is_international'] == '1');

		// Load Configuration - Clone from Previous similar contest if not found
		if (! file_exists("../salons/$yearmonth/blob/slideshow.json")) {
			$sql  = "SELECT yearmonth, contest_name FROM contest WHERE yearmonth != '$yearmonth' ";
			$sql .= "   AND is_international = '" . ($is_international ? '1' : '0') . "' ";
			$sql .= " ORDER BY yearmonth DESC ";
			$sql .= " LIMIT 1 ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) > 0) {
				$prev_salon = mysqli_fetch_array($query);
				$prev_yearmonth = $prev_salon['yearmonth'];
				$prev_contest_name = $prev_salon['contest_name'];
				if (file_exists("../salons/$prev_yearmonth/blob/slideshow.json")) {
					copy("../salons/$prev_yearmonth/blob/slideshow.json", "../salons/$yearmonth/blob/slideshow.json");
					$_SESSION['info_msg'] = "Copied Certificate Definition from $prev_contest_name.";
				}
			}
		}
		// Get Slideshow Config
		if ($ss_conf = get_slideshow_config($yearmonth))
			$has_ss_conf = true;
		// Check for backdrop design file
		$ss_design_file = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_slideshow.png";
		if (file_exists("../salons/$yearmonth/img/$ss_design_file")) {
			list($ss_width, $ss_height) = getimagesize("../salons/$yearmonth/img/$ss_design_file");
			if ($ss_width == CONST_SS_WIDTH && $ss_height == CONST_SS_HEIGHT)
				$has_ss_design = true;
		}
		// Previous year Conf
		$prev_conf = [];
		$sql = "SELECT yearmonth, contest_name FROM contest WHERE yearmonth != $yearmonth";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if (file_exists("../salons/" . $row['yearmonth'] . "/blob/slideshow.json"))
				$prev_conf[$row['yearmonth']] = $row['contest_name'];
		}
	}

	// Fonts
	$font_files = ttf_list();



?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Salon Management</title>

	<?php include "inc/header.php"; ?>

	<link rel="stylesheet" href="plugin/select2/css/select2.min.css" />
	<link rel="stylesheet" href="plugin/select2/css/select2-bootstrap.min.css" />
	<link rel="stylesheet" href="plugin/bootstrap-colorpicker/css/bootstrap-colorpicker.css" >

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
						Configure Slideshow
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="slideshow.php" enctype="multipart/form-data" >
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
			<form role="form" method="post" id="edit-slideshow-form" name="edit-slideshow-form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="is_international" value="<?= $is_international ? '1' : '0';?>" >
				<input type="hidden" name="slideshow_design" value="<?= $ss_design_file;?>" >
				<input type="hidden" name="color_none" value="" >
				<input type="hidden" name="color_black" value="#000000" >
				<input type="hidden" name="color_white" value="#FFFFFF" >
				<input type="hidden" name="color_gold" value="#A48438" >
				<input type="hidden" name="color_subdued" value="#808080" >
				<input type="hidden" name="color_highlight" value="#C0C0C0" >
				<input type="hidden" name="width" value="<?= CONST_SS_WIDTH;?>" >
				<input type="hidden" name="height" value="<?= CONST_SS_HEIGHT;?>" >

				<!-- Copy from earlier year -->
				<?php
					if (isset($prev_conf) && sizeof($prev_conf) > 0){
				?>
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="yearmonth">Select Salon from which to copy Slideshow configuration</label>
						<div class="input-group">
							<select class="form-control" name="yearmonth" id="select-prev-json" >
							<?php
								foreach($prev_conf as $prev_yearmonth => $prev_contest_name) {
							?>
								<option value="<?= $prev_yearmonth;?>" ><?= $prev_contest_name;?></option>
							<?php
								}
							?>
							</select>
							<span class="input-group-btn">
								<button type="submit" class="btn btn-info pull-right" id="select-prev-json-button" ><i class="fa fa-play"></i> Copy From </a>
							</span>
						</div>
					</div>
				</div>
				<?php
					}
				?>

				<!-- Slideshow Design -->
				<div class="row form-group">
					<div class="col-sm-12">
						<div class="row">
							<div class="col-sm-12"><b>Slideshow Design *</b></div>
							<div class="col-sm-2">
								<img src="<?= $has_ss_design ? "/salons/$yearmonth/img/$ss_design_file" : "/img/preview.png";?>"
										id="disp-ss-design" style="max-width: 120px; max-height: 180px;" >
							</div>
							<div class="col-sm-4">
								<label for="edit-ss-design">Select Graphics (<?= CONST_SS_WIDTH;?> x <?=CONST_SS_HEIGHT;?>)</label>
								<input type="file" name="ss_design" id="edit-ss-design" >
								<p id="error-ss-design" class="text-danger"></p>
								<button id="upload-ss-design" class="btn btn-info" disabled
										data-file="<?= ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_slideshow.png";?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				</div>
				<!-- Slideshow Parameters -->
				<div class="row form-group">
					<!-- File Name Stub -->
					<div class="col-sm-3">
						<label for="edit-ss-stub">Slideshow File Stub *</label>
						<input type="text" name="stub" id="edit-ss-stub"
								value="<?= $has_ss_conf ? $ss_conf['file_name_stub'] : ($is_international ? "IS" : "AIS") . substr($yearmonth, 0, 4) . "-SS";?>" >
					</div>
					<!-- Regular Font -->
					<div class="col-sm-3">
						<label for="edit-ss-font-regular">Regular Font *</label>
						<select class="form-control select-2" name="font_regular" id="edit-ss-font-regular" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_ss_conf && $ss_conf['font_regular'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
					<!-- Bold Font -->
					<div class="col-sm-3">
						<label for="edit-ss-font-bold">Bold Font *</label>
						<select class="form-control select-2" name="font_bold" id="edit-ss-font-bold" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_ss_conf && $ss_conf['font_bold'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
					<!-- Italic Font -->
					<div class="col-sm-3">
						<label for="edit-ss-font-italic">Italic Font *</label>
						<select class="form-control select-2" name="font_italic" id="edit-ss-font-italic" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_ss_conf && $ss_conf['font_italic'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
				</div>
				<!-- Custom Colors -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="edit-ss-custom-color-1">Color 1</label>
						<div id="custom-color-1" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-ss-custom-color-1" name="color_custom_1"
									value="<?= ($has_ss_conf && isset($ss_conf['color_custom_1'])) ? "#" . $ss_conf['color_custom_1'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-ss-custom-color-2">Color 2</label>
						<div id="custom-color-2" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-ss-custom-color-2" name="color_custom_2"
									value="<?= ($has_ss_conf && isset($ss_conf['color_custom_2'])) ? "#" . $ss_conf['color_custom_2'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-ss-custom-color-3">Color 3</label>
						<div id="custom-color-3" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-ss-custom-color-3" name="color_custom_3"
									value="<?= ($has_ss_conf && isset($ss_conf['color_custom_3'])) ? "#" . $ss_conf['color_custom_3'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-ss-custom-color-1">Color 4</label>
						<div id="custom-color-4" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-ss-custom-color-4" name="color_custom_4"
									value="<?= ($has_ss_conf && isset($ss_conf['color_custom_4'])) ? "#" . $ss_conf['color_custom_4'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
				</div>

				<!-- FIELD TABLES for Section, Opening, Secction Closing & Picture pages -->
				<!-- Seection Opening Page Fields -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Section Opening Page Definition</h3>
						<button class="btn btn-info add-field" data-table="opening" data-context="section"><i class="fa fa-plus-circle"></i> Add Field</button>
						<table id="opening-table" class="table">
							<thead>
								<tr>
									<th>Type</th>
									<th>Field</th>
									<th>Value</th>
									<th>Left</th>
									<th>Top</th>
									<th>Width</th>
									<th>Height</th>
									<th>Align</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody>
						<?php
							if ($has_ss_conf) {
								for ($idx = 0; $idx < sizeof($ss_conf['section_opening']); ++ $idx) {
									$row = $ss_conf['section_opening'][$idx];
						?>
								<tr id="ss-opening-field-<?= $idx;?>-row">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="opening_data[]" id="opening-data-<?= $idx;?>" value='<?= json_encode($row);?>' >
									<td><?= get_label($field_types, $row['type']);?></td>
									<td><?= get_field($row['type'] == "image" ? $section_image_fields : $section_text_fields, $row['field'], $row['value']);?></td>
									<td><?= $row['value'];?></td>
									<td><?= $row['x'];?></td>
									<td><?= $row['y'];?></td>
									<td><?= $row['width'];?></td>
									<td><?= $row['height'];?></td>
									<td><?= $row['position'];?></td>
									<td>
										<button id="edit-opening-fields-<?= $idx;?>" class="btn btn-info edit-field"
											    data-idx='<?= $idx;?>' data-table='opening' data-context="section" >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-opening-fields-<?= $idx;?>" class="btn btn-danger delete-field"
												data-idx='<?= $idx;?>' data-table='opening' data-context="section" >
											<i class="fa fa-trash"></i> Delete
										</button>
									</td>
								</tr>
						<?php
								}
							}
						?>
								<tr id="end-of-opening-table" data-idx="<?= isset($idx) ? $idx : 0;?>">
									<td>-- End of Section Opening</td>
									<td></td>
									<td></td>
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

				<!-- Section Closing Page Fields Definition -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Section Closing Page Definition</h3>
						<button class="btn btn-info add-field" data-table="closing" data-context="section"><i class="fa fa-plus-circle"></i> Add Field</button>
						<table id="closing-table" class="table">
							<thead>
								<tr>
									<th>Type</th>
									<th>Field</th>
									<th>Value</th>
									<th>Left</th>
									<th>Top</th>
									<th>Width</th>
									<th>Height</th>
									<th>Align</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody>
						<?php
							if ($has_ss_conf) {
								for ($idx = 0; $idx < sizeof($ss_conf['section_closing']); ++ $idx) {
									$row = $ss_conf['section_closing'][$idx];
						?>
								<tr id="ss-closing-field-<?= $idx;?>-row">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="closing_data[]" id="closing-data-<?= $idx;?>" value='<?= json_encode($row);?>' >
									<td><?= get_label($field_types, $row['type']);?></td>
									<td><?= get_field($row['type'] == "image" ? $section_image_fields : $section_text_fields, $row['field'], $row['value']);?></td>
									<td><?= $row['value'];?></td>
									<td><?= $row['x'];?></td>
									<td><?= $row['y'];?></td>
									<td><?= $row['width'];?></td>
									<td><?= $row['height'];?></td>
									<td><?= $row['position'];?></td>
									<td>
										<button id="edit-closing-fields-<?= $idx;?>" class="btn btn-info edit-field"
											    data-idx='<?= $idx;?>' data-table='closing' data-context="section" >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-closing-fields-<?= $idx;?>" class="btn btn-danger delete-field"
												data-idx='<?= $idx;?>' data-table='closing' data-context="section" >
											<i class="fa fa-trash"></i> Delete
										</button>
									</td>
								</tr>
						<?php
								}
							}
						?>
								<tr id="end-of-closing-table" data-idx="<?= isset($idx) ? $idx : 0;?>">
									<td>-- End of Section Closing</td>
									<td></td>
									<td></td>
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

				<!-- Picture Page Fields -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Picture Page Definition</h3>
						<button class="btn btn-info add-field" data-table="picture" data-context="picture"><i class="fa fa-plus-circle"></i> Add Field</button>
						<table id="picture-table" class="table">
							<thead>
								<tr>
									<th>Type</th>
									<th>Field</th>
									<th>Value</th>
									<th>Left</th>
									<th>Top</th>
									<th>Width</th>
									<th>Height</th>
									<th>Align</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody>
						<?php
							if ($has_ss_conf) {
								for ($idx = 0; $idx < sizeof($ss_conf['picture']); ++ $idx) {
									$row = $ss_conf['picture'][$idx];
						?>
								<tr id="ss-picture-field-<?= $idx;?>-row">
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="picture_data[]" id="picture-data-<?= $idx;?>" value='<?= json_encode($row);?>' >
									<td><?= get_label($field_types, $row['type']);?></td>
									<td><?= get_field($row['type'] == "image" ? $pic_image_fields : $pic_text_fields, $row['field'], $row['value']);?></td>
									<td><?= $row['value'];?></td>
									<td><?= $row['x'];?></td>
									<td><?= $row['y'];?></td>
									<td><?= $row['width'];?></td>
									<td><?= $row['height'];?></td>
									<td><?= $row['position'];?></td>
									<td>
										<button id="edit-picture-fields-<?= $idx;?>" class="btn btn-info edit-field"
											    data-idx='<?= $idx;?>' data-table='picture' data-context="picture" >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-picture-fields-<?= $idx;?>" class="btn btn-danger delete-field"
												data-idx='<?= $idx;?>' data-table='picture' data-context="picture" >
											<i class="fa fa-trash"></i> Delete
										</button>
									</td>
								</tr>
						<?php
								}
							}
						?>
								<tr id="end-of-picture-table" data-idx="<?= isset($idx) ? $idx : 0;?>">
									<td>-- End of Picture Fields</td>
									<td></td>
									<td></td>
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

				<!-- Update -->
				<div class="row" style="padding-top: 20px;">
					<div class="col-sm-12">
						<input class="btn btn-primary pull-right" type="submit" name="save-json" value="Save">
					</div>
				</div>
			</form>

		</div>



		<!-- MODAL FORMS -->
		<!-- Edit Partner -->
		<div class="modal" id="edit-field-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Slideshow Field</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-field-form" name="edit_field_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="is-edit-field" value="0" >
							<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>" >
							<input type="hidden" name="edit_context" id="edit-context" value="">
							<input type="hidden" name="edit_table" id="edit-table" value="">
							<input type="hidden" name="edit_idx" id="edit-idx" value="0" >

							<!-- Edited Fields -->
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- Type -->
								<div class="col-sm-6">
									<label for="edit-ss-type">Select Type of Field</label>
									<select id="edit-ss-type" name="type" class="form-control" required></select>
								</div>
								<!-- Field -->
								<div class="col-sm-6">
									<label for="edit-ss-field">Select a Field</label>
									<select id="edit-ss-field" name="field" class="form-control"></select>
								</div>
								<!-- Field -->
								<div class="col-sm-6">
									<label for="edit-ss-label">(OR) Label</label>
									<input type="text" id="edit-ss-label" name="label" class="form-control" value="">
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-value">& Value</label>
									<input type="text" id="edit-ss-value" name="value" class="form-control" value="" disabled >
								</div>
							</div>
							<!-- Font -->
							<div class="row form-group" id="edit-text-attributes">
								<div class="col-sm-4">
									<label for="edit-ss-font">Font</label>
									<select id="edit-ss-font" name="font" class="form-control" required>
										<option value="regular">Regular</option>
										<option value="bold">Bold</option>
										<option value="italic">Italic</option>
									</select>
								</div>
								<div class="col-sm-4">
									<label for="edit-ss-font-size">Font Size</label>
									<input type="number" id="edit-ss-font-size" class="form-control" name="font_size" required>
								</div>
								<div class="col-sm-4">
									<label for="edit-ss-font-color">Color</label>
									<select id="edit-ss-font-color" name="font_color" class="form-control" required>
										<option value="color_black">Black</option>
										<option value="color_white">White</option>
										<option value="color_gold">Gold</option>
										<option value="color_subdued">Subdued</option>
										<option value="color_highlight">Highlight</option>
										<option value="color_custom_1">Custom 1</option>
										<option value="color_custom_2">Custom 2</option>
										<option value="color_custom_3">Custom 3</option>
										<option value="color_custom_4">Custom 4</option>
										<option value="color_none">No Color</option>
									</select>
								</div>
							</div>
							<!-- Position -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-ss-x">Left (px)</label>
									<input type="number" id="edit-ss-x" name="x" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-y">Top (px)</label>
									<input type="number" id="edit-ss-y" name="y" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-width">Width (px)</label>
									<input type="number" id="edit-ss-width" name="width" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-height">Height (px)</label>
									<input type="number" id="edit-ss-height" name="height" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-halign">Horz Align</label>
									<select id="edit-ss-halign" name="halign" class="form-control" required>
										<option value="L">Left</option>
										<option value="M">Center</option>
										<option value="R">Right</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-valign">Vert Align</label>
									<select id="edit-ss-valign" name="valign" class="form-control" required>
										<option value="T">Top</option>
										<option value="M">Middle</option>
										<option value="B">Bottom</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-rotate">Rotate (degrees)</label>
									<input type="number" id="edit-ss-rotate" name="rotate" class="form-control" value="0" required>
								</div>
							</div>
							<!-- Fill & Border -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-ss-border-size">Border Sz (px)</label>
									<input type="number" id="edit-ss-border-size" name="border_size" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-border-gap">Border Gap (px)</label>
									<input type="number" id="edit-ss-border-gap" name="border_gap" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-border-color">Border Color</label>
									<select id="edit-ss-border-color" name="border_color" class="form-control" required>
										<option value="color_black">Black</option>
										<option value="color_white">White</option>
										<option value="color_gold">Gold</option>
										<option value="color_subdued">Subdued</option>
										<option value="color_highlight">Highlight</option>
										<option value="color_custom_1">Custom 1</option>
										<option value="color_custom_2">Custom 2</option>
										<option value="color_custom_3">Custom 3</option>
										<option value="color_custom_4">Custom 4</option>
										<option value="color_none">No Color</option>
									</select>
									<!-- <div id="border-color" class="input-group color-picker-component">
										<input type="text" class="form-control" id="edit-ss-border-color" name="border_color" value="#fff" >
										<span class="input-group-addon"><i></i></span>
									</div> -->
								</div>
								<div class="col-sm-6">
									<label for="edit-ss-fill-color">Fill Color</label>
									<select id="edit-ss-fill-color" name="fill_color" class="form-control" required>
										<option value="color_black">Black</option>
										<option value="color_white">White</option>
										<option value="color_gold">Gold</option>
										<option value="color_subdued">Subdued</option>
										<option value="color_highlight">Highlight</option>
										<option value="color_custom_1">Custom 1</option>
										<option value="color_custom_2">Custom 2</option>
										<option value="color_custom_3">Custom 3</option>
										<option value="color_custom_4">Custom 4</option>
										<option value="color_none">No Color</option>
									</select>
									<!-- <div id="fill-color" class="input-group color-picker-component">
										<input type="text" class="form-control" id="edit-ss-fill-color" name="fill_color" value="#fff" >
										<span class="input-group-addon"><i></i></span>
									</div> -->
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

<!-- Color Picker -->
<script src="plugin/bootstrap-colorpicker/js/bootstrap-colorpicker.js"></script>


<script>
	$(document).ready(function(){
		// Hide content till a salon is selected
		if($("#yearmonth").val() == 0)
			$(".content").hide();

		$(".select-2").select2();

		// Attach Color Picker
		$('#custom-color-1').colorpicker();
		$('#custom-color-2').colorpicker();
		$('#custom-color-3').colorpicker();
        $('#custom-color-4').colorpicker();

	});
</script>

<!-- Javascript Dropdown Handlers -->
<script>
	var field_types = <?= json_encode($field_types);?>;
	var pic_image_fields = <?= json_encode($pic_image_fields);?>;
	var pic_text_fields = <?= json_encode($pic_text_fields);?>;
	var section_image_fields = <?= json_encode($section_image_fields);?>;
	var section_text_fields = <?= json_encode($section_text_fields);?>;

	function get_label(array, val) {
		for ( const [label, field] of Object.entries(array) ) {
			if (field == val)
				return label;
		}
		return "";
	}

	function get_val(array, val) {
		for ( const [label, field] of Object.entries(array) ) {
			if (label == val)
				return field;
		}
		return "";
	}

	function get_options(array, selection = "") {
		let html = "";
		for ( const [label, field] of Object.entries(array) ) {
			html += "<option value= '" + field + "' " + (field == selection ? 'selected' : '') + ">" + label + "</option>";
		}
		return html;
	}

</script>

<!-- Action Handlers -->
<script>

	// Fetch Partner data and launch edit dialog
	function launch_edit_modal(table, context, mode, field = {}, idx = 0) {
		$("#edit-table").val(table);
		$("#edit-context").val(context);
		$("#edit-idx").val(idx);
		$("#is-edit-field").val(mode == "edit" ? "1" : "0");

		// Set Defaults
		$("#edit-ss-type").val("text");
		$("#edit-ss-field").val("");
		$("#edit-ss-field").attr("required", true);
		$("#edit-ss-label").val("");
		$("#edit-ss-label").removeAttr("required");
		$("#edit-ss-value").val("");
		$("#edit-ss-value").attr("disabled", true);
		$("#edit-ss-value").removeAttr("required");

		if (mode == "edit") {
			// Set values passed
			// Type
			$("#edit-ss-type").html(get_options(field_types, field.type));
			$("#edit-ss-type").val(field.type);
			$("#edit-ss-type option[value='" + field.type + "']").attr("selected", true);
			// Field
			if (field.value == "") {
				if (field.type == "image") {
					if (context == "picture")
						$("#edit-ss-field").html(get_options(pic_image_fields, field.field));
					else
						$("#edit-ss-field").html(get_options(section_image_fields, field.field));
				}
				else {
					if (context == "picture")
						$("#edit-ss-field").html(get_options(pic_text_fields, field.field));
					else
						$("#edit-ss-field").html(get_options(section_text_fields, field.field));
				}
				$("#edit-ss-field").val(field.field);
			}
			else {
				$("#edit-ss-label").val(field.field);
				$("#edit-ss-value").val(field.value);
				if (field.type == "image") {
					if (context == "picture")
						$("#edit-ss-field").html(get_options(pic_image_fields));
					else
						$("#edit-ss-field").html(get_options(section_image_fields));
				}
				else {
					if (context == "picture")
						$("#edit-ss-field").html(get_options(pic_text_fields));
					else
						$("#edit-ss-field").html(get_options(section_text_fields));
				}
			}
			// Font
			$("#edit-ss-font").val(field.font);
			$("#edit-ss-font option[value='" + field.font + "']").attr("selected", true);
			$("#edit-ss-font-size").val(field.font_size);
			$("#edit-ss-font-color").val(field.font_color);
			$("#edit-ss-font-color option[value='" + field.font_color + "']").attr("selected", true);
			// Position
			$("#edit-ss-x").val(field.x);
			$("#edit-ss-y").val(field.y);
			$("#edit-ss-width").val(field.width);
			$("#edit-ss-height").val(field.height);
			$("#edit-ss-halign").val(field.position.substr(0,1));
			$("#edit-ss-halign option[value='" + field.position.substr(0,1) + "']").attr("selected", true);
			$("#edit-ss-valign").val(field.position.substr(1,1));
			$("#edit-ss-valign option[value='" + field.position.substr(1,1) + "']").attr("selected", true);
			$("#edit-ss-rotate").val(field.rotate);
			// Border & Fill
			$("#edit-ss-border-size").val(field.border_size);
			$("#edit-ss-border-gap").val(field.border_gap);
			$("#edit-ss-border-color").val(field.border_color);
			$("#edit-ss-border-color option[value='" + field.border_color + "']").attr("selected", true);
			$("#edit-ss-fill-color").val(field.fill_color);
			$("#edit-ss-fill-color option[value='" + field.fill_color + "']").attr("selected", true);
		}
		else {
			// Type
			$("#edit-ss-type").html(get_options(field_types));
			$("#edit-ss-type").val("text");		// Set default to Text field
			if (context == "picture")
				$("#edit-ss-field").html(get_options(pic_text_fields));
			else
				$("#edit-ss-field").html(get_options(section_text_fields));
			$("#edit-ss-field").val("");
			$("#edit-ss-label").val("");
			$("#edit-ss-value").val("");
			// Font
			$("#edit-ss-font").val("regular");
			$("#edit-ss-font option[value='regular']").attr("selected", true);
			$("#edit-ss-font-size").val("32");
			$("#edit-ss-font-color").val("color_white");
			$("#edit-ss-font-color option[value='color_white']").attr("selected", true);
			// Position
			$("#edit-ss-x").val("0");
			$("#edit-ss-y").val("0");
			$("#edit-ss-width").val("0");
			$("#edit-ss-height").val("0");
			$("#edit-ss-halign").val("L");
			$("#edit-ss-halign option[value='L']").attr("selected", true);
			$("#edit-ss-valign").val("T");
			$("#edit-ss-valign option[value='T']").attr("selected", true);
			$("#edit-ss-rotate").val("0");
			// Border & Fill
			$("#edit-ss-border-size").val("0");
			$("#edit-ss-border-gap").val("0");
			$("#edit-ss-border-color").val("color_none");
			$("#edit-ss-fill-color").val("color_none");
		}

		// Hide Font fields for image
		if (field.type == "image")
			$("#edit-text-attributes").hide();
		else
			$("#edit-text-attributes").show();


		if (mode == "edit")
			$("#edit-update").val("Update");
		else
			$("#edit-update").val("Add");

		// Attach Color Picker
		// $('#border-color').colorpicker();
		// $('#fill-color').colorpicker();

		// Install Type Change Handler
		$("#edit-ss-type").change(function(){
			if ($(this).val() == "image") {
				$("#edit-text-attributes").hide();
				if (context == "picture")
					$("#edit-ss-field").html(get_options(pic_image_fields));
				else
					$("#edit-ss-field").html(get_options(section_image_fields));
			}
			else {
				$("#edit-text-attributes").show();
				if (context == "picture")
					$("#edit-ss-field").html(get_options(pic_text_fields));
				else
					$("#edit-ss-field").html(get_options(section_text_fields));
			}
			// Clear label and value
			$("#edit-ss-label").val("");
			$("#edit-ss-value").val("");
			$("#edit-ss-value").attr("disabled", true);
		});

		// Install Handler for Field
		$("#edit-ss-field").change(function(){
			if ($(this).val() == "") {
				$("#edit-ss-value").removeAttr("disabled");
				$("#edit-ss-value").attr("required", true);
			}
			else {
				$("#edit-ss-label").val("");
				$("#edit-ss-value").attr("disabled", true);
				$("#edit-ss-value").removeAttr("required");
			}
		});

		// Install handler for label edit
		$("#edit-ss-label").change(function(){
			if ($(this).val() == "") {
				$("#edit-ss-field").attr("required", true);
				$("#edit-ss-value").attr("disabled", true);
				$("#edit-ss-value").removeAttr("required");
			}
			else {
				$("#edit-ss-field").val("");
				$("#edit-ss-field").removeAttr("required");
				$("#edit-ss-value").removeAttr("disabled");
				$("#edit-ss-value").attr("required", true);
			}
		});

		$("#edit-field-modal").modal('show');
	}

	// Delete Partner
	// Handle delete button request
	function delete_field(context, idx) {
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the field ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$("#ss-" + context + "-field-" + idx + "-row").remove();
			}
		});
	}

	$(document).ready(function() {
		// $("#edit-partner-modal").on("shown.bs.modal", function(){
		// 	$("#edit-sections").select2({theme: 'bootstrap'});
		// 	$("#edit-role").select2({theme: 'bootstrap'});
		// });
		// Copy Previous configuration
		$("#select-prev-json-button").click(function(){
			let yearmonth = '<?= $yearmonth;?>';
			let prev_contest = $("#select-prev-json").val();
			window.location.href="slideshow.php?yearmonth=" + yearmonth + "&copyfrom=" + prev_contest;
		});
		// Edit button
		$(".edit-field").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let table = $(this).attr("data-table");
			let context = $(this).attr("data-context");
			let field = JSON.parse($("#" + table + "-data-" + idx).val());
			launch_edit_modal(table, context, "edit", field, idx);
		});

		// Add button
		$(".add-field").click(function(e) {
			e.preventDefault();
			let table = $(this).attr("data-table");
			let context = $(this).attr("data-context");
			launch_edit_modal(table, context, "add");
		});

		// Delete Button
		$(".delete-field").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let context = $(this).attr("data-context");
			delete_field(context, idx);
		});
	});
</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>

	function build_field_row(data, idx) {

		let table = data.get("edit_table");
		let context = data.get("edit_context");
		// let idx = $("#end-of-" + table + "-table").attr("data-idx");

		let field = data.get("field") == null ? "" : data.get("field");
		let label = data.get("label") == null ? "" : data.get("label");
		let value = data.get("value") == null ? "" : data.get("value");

		let disp_field = "";

		if (value == "") {
			if (context == 'section')
				disp_field = get_label(data.get('type') == "image" ? section_image_fields : section_text_fields, field);
			else
				disp_field = get_label(data.get('type') == "image" ? pic_image_fields : pic_text_fields, field);
		}
		else {
			disp_field = label;
		}

		// Assemble Data for the Row
		let field_data = {
				"type" : data.get("type"),
				"field" : (value != "" && label != "") ? label : field,
				"value" : value,
				"x" : data.get("x"),
				"y" : data.get("y"),
				"width" : data.get("width"),
				"height" : data.get("height"),
				"rotate" : data.get("rotate"),
				"position" : data.get("halign") + data.get("valign"),
				"font" : data.get("font"),
				"font_size" : data.get("font_size"),
				"font_color" : data.get("font_color"),
				"fill_color" : data.get("fill_color"),
				"border_size" : data.get("border_size"),
				"border_color" : data.get("border_color"),
				"border_gap" : data.get("border_gap"),
		};

		// Build the row to insert
		html  =	"<input type='hidden' name='" + table + "_data[]' id='" + table + "-data-" + idx + "' ";
		html += " value='" + JSON.stringify(field_data) + "' > ";
		html += "<td>" + get_label(field_types, data.get('type')) + "</td> ";
		html += "<td>" + disp_field + "</td> ";
		html += "<td>" + value + "</td> ";
		html += "<td>" + data.get("x") + "</td> ";
		html += "<td>" + data.get("y") + "</td> ";
		html += "<td>" + data.get("width") + "</td> ";
		html += "<td>" + data.get("height") + "</td> ";
		html += "<td>" + data.get("halign") + data.get("valign") + "</td>";
		html += "<td> ";
		html += "<button id='edit-" + table + "-fields-" + idx + "' class='btn btn-info edit-field' name='edit-" + table + "-field' ";
		html += "data-idx='" + idx + "' data-table='" + table + "' data-context='" + context + "' > ";
		html += "<i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='delete-" + table + "-fields-" + idx + "' class='btn btn-danger delete-field' name='delete-" + table + "-field' ";
		html += "data-idx='" + idx + "' data-table='" + table + "' data-context='" + context + "' > ";
		html += "<i class='fa fa-trash'></i> Delete ";
		html += "</button> ";
		html += "</td> ";

		return html;
	}

	function update_field_row(data, idx) {
		let table = data.get("edit_table");
		let context = data.get("edit_context");
		$("#ss-" + table + "-field-" + idx + "-row").html(build_field_row(data, idx));
	}

	function add_field_row(data) {
		let table = data.get("edit_table");
		let context = data.get("edit_context");
		let idx = $("#end-of-" + table + "-table").attr("data-idx");

		let html = "<tr id='ss-" + table + "-field-" + idx + "-row'> ";
		html += build_field_row(data, idx);
		html += "</tr> ";

		$(html).insertBefore("#end-of-" + table + "-table");
		++ idx;
		$("#end-of-" + table + "-table").attr("data-idx", idx);

		// Edit button
		$(".edit-field").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_table = $(this).attr("data-table");
			let fn_context = $(this).attr("data-context");
			let fn_field = JSON.parse($("#" + fn_table + "-data-" + fn_idx).val());
			launch_edit_modal(fn_table, fn_context, "edit", fn_field, fn_idx);
		});

		// Delete Button
		$(".delete-field").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_context = $(this).attr("data-context");
			delete_field(fn_context, fn_idx);
		});
	}
</script>

<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load logo into view
		$("#edit-ss-design").on("change", function(){
			$("#error-ss-design").html("");
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#disp-ss-design").attr("src", e.target.result);
					$("#disp-ss-design").one("load", function() {
						if (this.naturalWidth == "<?= CONST_SS_WIDTH;?>" && this.naturalHeight == "<?= CONST_SS_HEIGHT;?>" )
							$("#upload-ss-design").removeAttr("disabled");
						else
							$("#error-ss-design").html("width x height must be <?= CONST_SS_WIDTH;?> x <?= CONST_SS_HEIGHT;?>");
					});
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		// Ajax Upload of Slideshow Design PNG
		$("#upload-ss-design").click(function(e){
			e.preventDefault();
			if ($("#edit-ss-design").val() == "") {
				swal("Select a Slideshow Design Image !", "Please select a slideshow design image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = $(this).attr("data-file");
				let file = $("#edit-ss-design")[0].files[0];
				let stub = $("#edit-ss-design").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								// Set display to server image
								$("#disp-ss-design").attr("src", "/salons/" + yearmonth + "/img/" + file_name);
								swal({
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
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


		// Update rules back to the table
		$('#edit-field-form').validate({
			rules:{
				"edit-ss-x" : {
					min : 0,
					max : <?= CONST_SS_WIDTH;?>,
					required : true,
				},
				"edit-ss-y" : {
					min : 0,
					max : <?= CONST_SS_HEIGHT;?>,
					required : true,
				},
				"edit-ss-width" : {
					min : 0,
					max : (<?= CONST_SS_WIDTH;?> - $("#edit-ss-x").val()),
				},
				"edit-ss-height" : {
					min : 0,
					max : (<?= CONST_SS_HEIGHT;?> - $("#edit-ss-y").val()),
				},
				"edit-ss-rotate" : {
					min : 0,
					max : 360,
				},
				"edit-ss-border" : {
					min : 0,
					max : 10,
				},
				"edit-ss-border-gap" : {
					min : 0,
					max : 10,
				}
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

				let idx = $("#edit-idx").val();

				// Assemble Data
				let formData = new FormData(form);

				// Update changes to the table
				if ($("#is-edit-field").val() == "0")
					add_field_row(formData);
				else
					update_field_row(formData, idx);

				$("#edit-field-modal").modal('hide');
			},
		});

	// Save JSON
	$('#edit-slideshow-form').validate({
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
			// Validations
			// Desig File exists / uploaded
			if (! $("#disp-ss-design").attr("src").endsWith("slideshow.png")) {
				swal("No Slideshow Design Image !", "Please select a slideshow design image and upload.", "warning");
				return false;
			}

			// Check if section opening, closing and picture fields have been defined
			if ($("[name='opening_data[]']").length == 0) {
				swal("Section Opening Page Not Defined !", "There are no field definitions for section opening page", "warning");
				return false;
			}
			if ($("[name='closing_data[]']").length == 0) {
				swal("Section Closing Page Not Defined !", "There are no field definitions for section closing page", "warning");
				return false;
			}
			if ($("[name='picture_data[]']").length == 0) {
				swal("Picture Page Not Defined !", "There are no field definitions for picture page", "warning");
				return false;
			}

			// Assemble Data
			let formData = new FormData(form);

			$('#loader_img').show();
			$.ajax({
					url: "ajax/save_slideshow_json.php",
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
									title: "Slideshow Definition Saved",
									text: "The slideshow definition has been saved. Run slideshow generation from Admin system.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Save Failed",
									text: "Slideshow Definition could not be saved: " + response.msg,
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

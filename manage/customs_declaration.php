<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");


function get_config($yearmonth) {

	if (file_exists(__DIR__ . "/../salons/$yearmonth/blob/customs_declaration.json")) {
		// Load Partner Data
		$config = json_decode(file_get_contents(__DIR__ . "/../salons/$yearmonth/blob/customs_declaration.json"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "_customs_declaration Declaration Definition garbled";
			return false;
		}

		return $config;
	}
	else {
		return false;
	}

}

// Labels & Values
// Lists
$field_types = ["Text" => "text"];
$pic_image_fields = [];
$pic_text_fields = ["From Name" => "from_name", "From Street Address" => "from_street", "From Phone" => "from_phone",
					"From PIN" => "from_pin", "From City" => "from_city",
 					"Author Name" => "profile_name", "Author Address" => "address", "Author Phone" => "phone", "Author PIN" => "pin",
					"Author City" => "city", "Author Country" => "country_name", "Certificates" => "certificates", "Medals" => "awards",
					"Ribbons" => "hms" ];


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
	if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['copyfrom']) && file_exists("../salons/" . $_REQUEST['copyfrom'] . "/blob/customs_declaration.json"))
		copy("../salons/" . $_REQUEST['copyfrom'] . "/blob/customs_declaration.json", "../salons/" . $_REQUEST['yearmonth'] . "/blob/customs_declaration.json");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "is_international" => "0"
	);

	$yearmonth = 0;

	// Load sections for the contest
	$has_conf = false;
	$has_design = false;
	$is_international = false;
	$design_file = "";
	$df_width = 0;
	$df_height = 0;
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
		if (! $is_international) {
			$_SESSION['err_msg'] = "Customs Declarations are required only for International Salons";
			header("Location: " . $_SERVER['HTTP_REFERER']);
			print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}

		// Get Config
		if ($conf = get_config($yearmonth))
			$has_conf = true;

		// Check for backdrop design file
		$design_file = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_customs_declaration.png";
		if (file_exists("../salons/$yearmonth/img/$design_file")) {
			list($df_width, $df_height) = getimagesize("../salons/$yearmonth/img/$design_file");
			$has_design = true;
		}
		// Previous year Conf
		$prev_conf = [];
		$sql = "SELECT yearmonth, contest_name FROM contest WHERE yearmonth != $yearmonth";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if (file_exists("../salons/" . $row['yearmonth'] . "/blob/customs_declaration.json"))
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
						Configure Customs Declaration
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="customs_declaration.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-8">
								<label for="yearmonth">Select Salon</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
									<?php
										$sql = "SELECT yearmonth, contest_name FROM contest WHERE is_international = '1' ORDER BY yearmonth DESC ";
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
			<form role="form" method="post" id="edit-customs-form" name="edit-customs-form" action="#" enctype="multipart/form-data" >
				<!-- Hidden non-edited fields -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="is_international" value="<?= $is_international ? '1' : '0';?>" >
				<input type="hidden" name="design" value="<?= $design_file;?>" >
				<input type="hidden" name="color_none" value="" >
				<input type="hidden" name="color_black" value="#000000" >
				<input type="hidden" name="color_white" value="#FFFFFF" >
				<input type="hidden" name="color_gold" value="#A48438" >
				<input type="hidden" name="color_subdued" value="#808080" >
				<input type="hidden" name="color_highlight" value="#F02020" >
				<input type="hidden" name="width" id="width" value="<?= $df_width;?>" >
				<input type="hidden" name="height" id="height" value="<?= $df_height;?>" >

				<!-- Copy from earlier year -->
				<?php
					if (isset($prev_conf) && sizeof($prev_conf) > 0){
				?>
				<div class="row form-group">
					<div class="col-sm-6">
						<label for="yearmonth">Select Salon from which to copy Customs Declaration configuration</label>
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

				<!-- Customs Declaration Design -->
				<div class="row form-group">
					<div class="col-sm-9">
						<div class="row">
							<div class="col-sm-12"><b>Customs Declaration Design *</b></div>
							<div class="col-sm-2">
								<img src="<?= $has_design ? "/salons/$yearmonth/img/$design_file" : "/img/preview.png";?>"
										id="disp-design" style="max-width: 120px; max-height: 180px;" >
							</div>
							<div class="col-sm-4">
								<label for="edit-design">Select Graphics</label>
								<input type="file" name="design" id="edit-design" >
								<p id="error-design" class="text-danger"></p>
								<button id="upload-design" class="btn btn-info" disabled
										data-file="<?= $design_file;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				</div>
				<!-- Customs Declaration Parameters -->
				<div class="row form-group">
					<!-- File Name Stub -->
					<div class="col-sm-3">
						<label for="edit-stub">Customs Declaration File Stub *</label>
						<input type="text" name="stub" id="edit-stub"
								value="<?= $has_conf ? $conf['file_name_stub'] : "IS" . substr($yearmonth, 0, 4) . "-CD";?>" >
					</div>
					<!-- Regular Font -->
					<div class="col-sm-3">
						<label for="edit-font-regular">Regular Font *</label>
						<select class="form-control select-2" name="font_regular" id="edit-font-regular" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_conf && $conf['font_regular'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
					<!-- Bold Font -->
					<div class="col-sm-3">
						<label for="edit-font-bold">Bold Font *</label>
						<select class="form-control select-2" name="font_bold" id="edit-font-bold" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_conf && $conf['font_bold'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
					<!-- Italic Font -->
					<div class="col-sm-3">
						<label for="edit-font-italic">Italic Font *</label>
						<select class="form-control select-2" name="font_italic" id="edit-font-italic" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= ($has_conf && $conf['font_italic'] == $font_file) ? "selected" : "";?> ><?= basename($font_file, ".ttf");?></option>
						<?php
							}
						?>
						</select>
					</div>
				</div>

				<!-- Custom Colors -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="edit-custom-color-1">Color 1</label>
						<div id="custom-color-1" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-1" name="color_custom_1"
									value="<?= ($has_conf && isset($conf['color_custom_1'])) ? "#" . $conf['color_custom_1'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-2">Color 2</label>
						<div id="custom-color-2" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-2" name="color_custom_2"
									value="<?= ($has_conf && isset($conf['color_custom_2'])) ? "#" . $conf['color_custom_2'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-3">Color 3</label>
						<div id="custom-color-3" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-3" name="color_custom_3"
									value="<?= ($has_conf && isset($conf['color_custom_3'])) ? "#" . $conf['color_custom_3'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-1">Color 4</label>
						<div id="custom-color-4" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-4" name="color_custom_4"
									value="<?= ($has_conf && isset($conf['color_custom_4'])) ? "#" . $conf['color_custom_4'] : '#FFFFFF';?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
				</div>

				<!-- Custom Fields Required for Cusoms Declaration -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="edit-from-name">Sender Name</label>
						<input type="text" class="form-control" id="edit-from-name" name="from_name"
									value="<?= ($has_conf && isset($conf['from_name'])) ? $conf['from_name'] : '';?>" >
					</div>
					<div class="col-sm-9">
						<label for="edit-from-street">Sender Street Address</label>
						<input type="text" class="form-control" id="edit-from-street" name="from_street"
									value="<?= ($has_conf && isset($conf['from_street'])) ? $conf['from_street'] : '';?>" >
					</div>
					<div class="col-sm-3">
						<label for="edit-from-phone">Sender Phone</label>
						<input type="text" class="form-control" id="edit-from-phone" name="from_phone"
									value="<?= ($has_conf && isset($conf['from_phone'])) ? $conf['from_phone'] : '';?>" >
					</div>
					<div class="col-sm-3">
						<label for="edit-from-city">Sender City</label>
						<input type="text" class="form-control" id="edit-from-city" name="from_city"
									value="<?= ($has_conf && isset($conf['from_city'])) ? $conf['from_city'] : '';?>" >
					</div>
					<div class="col-sm-3">
						<label for="edit-from-pin">Sender PIN</label>
						<input type="text" class="form-control" id="edit-from-pin" name="from_pin"
									value="<?= ($has_conf && isset($conf['from_pin'])) ? $conf['from_pin'] : '';?>" >
					</div>
				</div>

				<!-- FIELD TABLES for Picture pages -->
				<!-- Picture Page Fields -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Page Definition</h3>
						<button class="btn btn-info add-field-btn" ><i class="fa fa-plus-circle"></i> Add Field</button>
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
							if ($has_conf) {
								for ($idx = 0; $idx < sizeof($conf['picture']); ++ $idx) {
									$row = $conf['picture'][$idx];
						?>
								<tr id="picture-field-<?= $idx;?>-row">
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
										<button id="edit-picture-fields-<?= $idx;?>" class="btn btn-info edit-field-btn"
											    data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-opening-fields-<?= $idx;?>" class="btn btn-danger delete-field-btn"
												data-idx='<?= $idx;?>' >
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
								<h4 class="modal-title"><small>Edit Fields</small></h4>
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
							<input type="hidden" name="edit_idx" id="edit-idx" value="0" >

							<!-- Edited Fields -->
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- Type -->
								<div class="col-sm-6">
									<label for="edit-type">Select Type of Field</label>
									<select id="edit-type" name="type" class="form-control" required></select>
								</div>
								<!-- Field -->
								<div class="col-sm-6">
									<label for="edit-field">Select a Field</label>
									<select id="edit-field" name="field" class="form-control"></select>
								</div>
								<!-- Field -->
								<div class="col-sm-6">
									<label for="edit-label">(OR) Label</label>
									<input type="text" id="edit-label" name="label" class="form-control" value="">
								</div>
								<div class="col-sm-6">
									<label for="edit-value">& Value</label>
									<input type="text" id="edit-value" name="value" class="form-control" value="" disabled >
								</div>
							</div>
							<!-- Font -->
							<div class="row form-group" id="edit-text-attributes">
								<div class="col-sm-4">
									<label for="edit-font">Font</label>
									<select id="edit-font" name="font" class="form-control" required>
										<option value="regular">Regular</option>
										<option value="bold">Bold</option>
										<option value="italic">Italic</option>
									</select>
								</div>
								<div class="col-sm-4">
									<label for="edit-font-size">Font Size</label>
									<input type="number" id="edit-font-size" class="form-control" name="font_size" required>
								</div>
								<div class="col-sm-4">
									<label for="edit-font-color">Color</label>
									<select id="edit-font-color" name="font_color" class="form-control" required>
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
									<label for="edit-x">Left (px)</label>
									<input type="number" id="edit-x" name="x" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-y">Top (px)</label>
									<input type="number" id="edit-y" name="y" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-width">Width (px)</label>
									<input type="number" id="edit-width" name="width" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-height">Height (px)</label>
									<input type="number" id="edit-height" name="height" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-halign">Horz Align</label>
									<select id="edit-halign" name="halign" class="form-control" required>
										<option value="L">Left</option>
										<option value="M">Center</option>
										<option value="R">Right</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="edit-valign">Vert Align</label>
									<select id="edit-valign" name="valign" class="form-control" required>
										<option value="T">Top</option>
										<option value="M">Middle</option>
										<option value="B">Bottom</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="edit-rotate">Rotate (degrees)</label>
									<input type="number" id="edit-rotate" name="rotate" class="form-control" value="0" required>
								</div>
							</div>
							<!-- Fill & Border -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-border-size">Border Sz (px)</label>
									<input type="number" id="edit-border-size" name="border_size" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-border-gap">Border Gap (px)</label>
									<input type="number" id="edit-border-gap" name="border_gap" class="form-control" required>
								</div>
								<div class="col-sm-6">
									<label for="edit-border-color">Border Color</label>
									<select id="edit-border-color" name="border_color" class="form-control" required>
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
								<div class="col-sm-6">
									<label for="edit-fill-color">Fill Color</label>
									<select id="edit-fill-color" name="fill_color" class="form-control" required>
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
	function launch_edit_modal(mode, field = {}, idx = 0) {
		$("#edit-idx").val(idx);
		$("#is-edit-field").val(mode == "edit" ? "1" : "0");

		// Set Defaults
		$("#edit-type").val("text");
		$("#edit-field").val("");
		$("#edit-field").attr("required", true);
		$("#edit-label").val("");
		$("#edit-label").removeAttr("required");
		$("#edit-value").val("");
		$("#edit-value").attr("disabled", true);
		$("#edit-value").removeAttr("required");

		if (mode == "edit") {
			// Set values passed
			// Type
			$("#edit-type").html(get_options(field_types, field.type));
			$("#edit-type").val(field.type);
			$("#edit-type option[value='" + field.type + "']").attr("selected", true);
			// Field
			if (field.value == "") {
				if (field.type == "image") {
					$("#edit-field").html(get_options(pic_image_fields, field.field));
				}
				else {
					$("#edit-field").html(get_options(pic_text_fields, field.field));
				}
				$("#edit-field").val(field.field);
			}
			else {
				$("#edit-label").val(field.field);
				$("#edit-value").val(field.value);
				if (field.type == "image") {
					$("#edit-field").html(get_options(pic_image_fields));
				}
				else {
					$("#edit-field").html(get_options(pic_text_fields));
				}
			}
			// Font
			$("#edit-font").val(field.font);
			$("#edit-font option[value='" + field.font + "']").attr("selected", true);
			$("#edit-font-size").val(field.font_size);
			$("#edit-font-color").val(field.font_color);
			$("#edit-font-color option[value='" + field.font_color + "']").attr("selected", true);
			// Position
			$("#edit-x").val(field.x);
			$("#edit-y").val(field.y);
			$("#edit-width").val(field.width);
			$("#edit-height").val(field.height);
			$("#edit-halign").val(field.position.substr(0,1));
			$("#edit-halign option[value='" + field.position.substr(0,1) + "']").attr("selected", true);
			$("#edit-valign").val(field.position.substr(1,1));
			$("#edit-valign option[value='" + field.position.substr(1,1) + "']").attr("selected", true);
			$("#edit-rotate").val(field.rotate);
			// Border & Fill
			$("#edit-border-size").val(field.border_size);
			$("#edit-border-gap").val(field.border_gap);
			$("#edit-border-color").val(field.border_color);
			$("#edit-border-color option[value='" + field.border_color + "']").attr("selected", true);
			$("#edit-fill-color").val(field.fill_color);
			$("#edit-fill-color option[value='" + field.fill_color + "']").attr("selected", true);
		}
		else {
			// Type
			$("#edit-type").html(get_options(field_types));
			$("#edit-type").val("text");		// Set default to Text field
			$("#edit-field").html(get_options(pic_text_fields));
			$("#edit-field").val("");
			$("#edit-label").val("");
			$("#edit-value").val("");
			// Font
			$("#edit-font").val("regular");
			$("#edit-font option[value='regular']").attr("selected", true);
			$("#edit-font-size").val("32");
			$("#edit-font-color").val("color_white");
			$("#edit-font-color option[value='color_white']").attr("selected", true);
			// Position
			$("#edit-x").val("0");
			$("#edit-y").val("0");
			$("#edit-width").val("0");
			$("#edit-height").val("0");
			$("#edit-halign").val("L");
			$("#edit-halign option[value='L']").attr("selected", true);
			$("#edit-valign").val("T");
			$("#edit-valign option[value='T']").attr("selected", true);
			$("#edit-rotate").val("0");
			// Border & Fill
			$("#edit-border-size").val("0");
			$("#edit-border-gap").val("0");
			$("#edit-border-color").val("color_none");
			$("#edit-fill-color").val("color_none");
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
		$("#edit-type").change(function(){
			if ($(this).val() == "image") {
				$("#edit-text-attributes").hide();
				$("#edit-field").html(get_options(pic_image_fields));
			}
			else {
				$("#edit-text-attributes").show();
				$("#edit-field").html(get_options(pic_text_fields));
			}
			// Clear label and value
			$("#edit-label").val("");
			$("#edit-value").val("");
			$("#edit-value").attr("disabled", true);
		});

		// Install Handler for Field
		$("#edit-field").change(function(){
			if ($(this).val() == "") {
				$("#edit-value").removeAttr("disabled");
				$("#edit-value").attr("required", true);
			}
			else {
				$("#edit-label").val("");
				$("#edit-value").attr("disabled", true);
				$("#edit-value").removeAttr("required");
			}
		});

		// Install handler for label edit
		$("#edit-label").change(function(){
			if ($(this).val() == "") {
				$("#edit-field").attr("required", true);
				$("#edit-value").attr("disabled", true);
				$("#edit-value").removeAttr("required");
			}
			else {
				$("#edit-field").val("");
				$("#edit-field").removeAttr("required");
				$("#edit-value").removeAttr("disabled");
				$("#edit-value").attr("required", true);
			}
		});

		$("#edit-field-modal").modal('show');
	}

	// Delete Partner
	// Handle delete button request
	function delete_field(idx) {
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the field ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$("#picture-field-" + idx + "-row").remove();
			}
		});
	}

	$(document).ready(function() {
		// Copy Previous configuration
		$("#select-prev-json-button").click() {
			let yearmonth = '<?= $yearmonth;?>';
			let prev_contest = $("#select-prev-json").val();
			location.href="customs_declaration.php?yearmonth=" . yearmonth . "&copyfrom=" . prev_contest;
		}
		// Edit button
		$(".edit-field-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let field = JSON.parse($("#picture-data-" + idx).val());
			launch_edit_modal("edit", field, idx);
		});

		// Add button
		$(".add-field-btn").click(function(e) {
			e.preventDefault();
			launch_edit_modal("add");
		});

		// Delete Button
		$(".delete-field-btn").click(function(e){
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			delete_field(idx);
		});
	});
</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>

	function build_field_row(data, idx) {

		// let idx = $("#end-of-picture-table").attr("data-idx");

		let field = data.get("field") == null ? "" : data.get("field");
		let label = data.get("label") == null ? "" : data.get("label");
		let value = data.get("value") == null ? "" : data.get("value");

		let disp_field = "";

		if (value == "") {
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
		html  =	"<input type='hidden' name='picture_data[]' id='picture-data-" + idx + "' ";
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
		html += "<button id='edit-picture-fields-" + idx + "' class='btn btn-info edit-field-btn' name='edit-picture-field' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='delete-picture-fields-" + idx + "' class='btn btn-danger delete-field-btn' name='delete-picture-field' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-trash'></i> Delete ";
		html += "</button> ";
		html += "</td> ";

		return html;
	}

	function update_field_row(data, idx) {
		$("#picture-field-" + idx + "-row").html(build_field_row(data, idx));
	}

	function add_field_row(data) {
		let idx = $("#end-of-picture-table").attr("data-idx");

		let html = "<tr id='picture-field-" + idx + "-row'> ";
		html += build_field_row(data, idx);
		html += "</tr> ";

		$(html).insertBefore("#end-of-picture-table");
		++ idx;
		$("#end-of-picture-table").attr("data-idx", idx);

		// Edit button
		$(".edit-field-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_field = JSON.parse($("#" + fn_table + "-data-" + fn_idx).val());
			launch_edit_modal("edit", fn_field, fn_idx);
		});

		// Delete Button
		$(".delete-field-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_field(fn_idx);
		});
	}
</script>

<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load logo into view
		$("#edit-design").on("change", function(){
			$("#error-design").html("");
			$("#upload-design").attr("disabled", true);
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#disp-design").attr("src", e.target.result);
					$("#disp-design").one("load", function() {
						$("#width").val(this.naturalWidth);
						$("#height").val(this.naturalHeight);
						$("#upload-design").removeAttr("disabled");
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

		// Ajax Upload of Ribbon Holder Design PNG
		$("#upload-design").click(function(e){
			e.preventDefault();
			if ($("#edit-design").val() == "") {
				swal("Select a Design Image !", "Please select a Customs Declaration design image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = $(this).attr("data-file");
				let file = $("#edit-design")[0].files[0];
				let stub = $("#edit-design").attr("name");
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
								$("#disp-design").attr("src", "/salons/" + yearmonth + "/img/" + file_name);
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
				"edit-x" : {
					min : 0,
					max : $("#width").val(),
					required : true,
				},
				"edit-y" : {
					min : 0,
					max : $("#height").val(),
					required : true,
				},
				"edit-width" : {
					min : 0,
					max : ($("#width").val() - $("#edit-x").val()),
				},
				"edit-height" : {
					min : 0,
					max : ($("#height").val() - $("#edit-y").val()),
				},
				"edit-rotate" : {
					min : 0,
					max : 360,
				},
				"edit-border" : {
					min : 0,
					max : 10,
				},
				"edit-border-gap" : {
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
	$('#edit-customs-form').validate({
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
			// Design File exists / uploaded
			if (! $("#disp-design").attr("src").endsWith("customs_declaration.png")) {
				swal("No Design Image !", "Please select a Customs Declaration design image and upload.", "warning");
				return false;
			}

			// Check if section opening, closing and picture fields have been defined
			if ($("[name='picture_data[]']").length == 0) {
				swal("Picture Page Not Defined !", "There are no field definitions for picture page", "warning");
				return false;
			}

			// Assemble Data
			let formData = new FormData(form);

			$('#loader_img').show();
			$.ajax({
					url: "ajax/save_customs_declaration_json.php",
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
									title: "Customs Declaration Definition Saved",
									text: "The Customs Declaration definition has been saved. Run Customs Declaration generation from Admin system.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Save Failed",
									text: "Customs Declaration Definition could not be saved: " + response.msg,
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

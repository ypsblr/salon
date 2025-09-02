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

// Loads list of ttf font files
function filter_font_files($filename) {
	return preg_match("/ttf$/", $filename);
}

function ttf_list() {
	$font_folder = "../pdf/font/unifont";
	return array_filter(scandir($font_folder), "filter_font_files");
}

function get_config($yearmonth) {

	if (file_exists(__DIR__ . "/../salons/$yearmonth/blob/certdef.json")) {
		// Load Partner Data
		$config = json_decode(file_get_contents(__DIR__ . "/../salons/$yearmonth/blob/certdef.json"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "Certificate Definition garbled";
			return false;
		}

		return $config;
	}
	else {
		return false;
	}
}

$default_colors = [
			"color_label" => array("name" => "color_label", "value" => "#927C4D", "label" => "Label"),
			"color_field" => array("name" => "color_field", "value" => "#A93C3A", "label" => "Field"),
			"color_subdued" => array("name" => "color_subdued", "value" => "#808080", "label" => "Subdued"),
			"color_highlight" => array("name" => "color_highlight", "value" => "#F02020", "label" => "Highlight"),
			"color_black" => array("name" => "color_black", "value" => "#000000", "label" => "Black"),
			"color_white" => array("name" => "color_white", "value" => "#FFFFFF", "label" => "White"),
			"color_gray" => array("name" => "color_gray", "value" => "#A0A0A0", "label" => "Gray"),
			"color_gold" => array("name" => "color_gold", "value" => "#A48438", "label" => "Gold"),
			"color_red" => array("name" => "color_red", "value" => "#FF0000", "label" => "Red"),
			"color_blue" => array("name" => "color_blue", "value" => "#5D82C1", "label" => "Blue"),
			"color_green" => array("name" => "color_green", "value" => "#44AC5B", "label" => "Green"),
			"color_yellow" => array("name" => "color_yellow", "value" => "#FDBE30", "label" => "Yellow"),
			"color_custom_1" => array("name" => "color_custom_1", "value" => "#FFFFFF", "label" => "Custom 1"),
			"color_custom_2" => array("name" => "color_custom_2", "value" => "#FFFFFF", "label" => "Custom 2"),
			"color_custom_3" => array("name" => "color_custom_3", "value" => "#FFFFFF", "label" => "Custom 3"),
			"color_custom_4" => array("name" => "color_custom_4", "value" => "#FFFFFF", "label" => "Custom 4"),
			"color_none" => array("name" => "color_none", "value" => "", "label" => "No Color")
		];

function get_color($color) {
	global $has_conf;
	global $cert;
	global $default_colors;

	if ($has_conf && isset($cert['doc']['colors'][$color]))
		return str_replace("0x", "#", $cert['doc']['colors'][$color]);
	else
		return $default_colors[$color][ 'value'];
}
// Generate options for selecting color
function color_options() {
	global $default_colors;

	$options = "";
	foreach ($default_colors as $color_name => $color) {
		$options .= "<option value='" . $color_name . "'>" . $color['label'] . "</option>";
	}
	return $options;
}

$block_types = ["Tiled as Columns" => "tile", "List as Rows" => "list"];
$node_nodetypes = ["Image" => "image", "Text" => "text"];
$image_nodes = ["Picture" => "picfile", "Author Avatar" => "author_avatar", "Sponsor Logo" => "sponsor_logo",
				"Chairman Signature" => "chairman_sig", "Secretary Signature" => "secretary_sig"];
$text_node_types = ["Field" => "field", "Label" => "label"];
$text_nodes = ["Author Name" => "author_name", "Honors" => "honors", "Author Country" => "country_name",
					"Award Name" => "award_name", "Picture Title" => "pic_title", "Award Section" => "award_section",
					"Jury Section" => "jury_section", "Jury 1 Name" => "jury_name_1", "Jury 2 Name" => "jury_name_2",
					"Jury 3 Name" => "jury_name_3", "Sponsor Name" => "sponsor_name", "Custom Award Name" => "custom_award_name",
					"Sponsor Website" => "sponsor_website"];
$node_columns = ["Award ID" => "award_id", "Award Level" => "level", "Award Sequence" => "sequence", "Award Section" => "section",
					"Award Type" => "award_type", "Award Name" => "award_name", "Recognition Code" => "recognition_code",
					"Awards Sponsored" => "sponsored_awards", "Author ID" => "profile_id", "Author Name" => "profile_name",
				 	"Author Honors" => "honors", "Author Avatar" => "avatar", "Sponsorship Number" => "sponsorship_no",
				 	"Picture Title" => "pic_title", "Picture Section" => "pic_section", "Picture Image" => "picfile", "No Field" => "" ];

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

$award_levels = [ "1" => "Gold Medal", "2" => "Silver Medal", "3" => "Bronze Medal",
			"4" => "Custom", "5" => "Custom", "6" => "Custom", "7" => "Custom", "8" => "Custom",
			"9" => "Honorable Mention",
			"99" => "Acceptance",
			"999" => "Overall Award" ];

function get_level_legend($level) {
	global $award_levels;

	foreach ($award_levels as $key => $legend) {
		if ($key == $level)
			return $legend;
	}
	return "Unknown";
}

function get_level_design($levels, $level) {
	foreach ($levels as $data) {
		if ($data['level'] == $level)
			return $data['design'];
	}
	return "";
}

function get_level_border_img($levels, $level) {
	foreach ($levels as $data) {
		if ($data['level'] == $level)
			return $data['border_img'];
	}
	return "";
}


if ( isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "", "certificates_ready" => "0", "is_international" => "0"
	);

	$yearmonth = 0;
	$cert = [ "page" => [], "blocks" => [], "nodes" => [] ];

	// Load sections for the contest
	$has_conf = false;
	$has_design = false;
	$design_file = "";
	$has_border_img = false;
	$border_img = "";
	$has_chairman_signature = false;
	$chairman_signature_file = "";
	$has_secretary_signature = false;
	$secretary_signature_file = "";
	$is_international = false;
	$width = 0;
	$height = 0;
	$orientation = 'P';
	$level_list = [];
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

		$is_international = ($salon["is_international"] == "1");

		// Load Certificate Configuration - Clone from Previous similar contest if not found
		if (! file_exists("../salons/$yearmonth/blob/certdef.json")) {
			$sql  = "SELECT yearmonth, contest_name FROM contest WHERE yearmonth != '$yearmonth' ";
			$sql .= "   AND is_international = '" . ($is_international ? '1' : '0') . "' ";
			$sql .= " ORDER BY yearmonth DESC ";
			$sql .= " LIMIT 1 ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) > 0) {
				$prev_salon = mysqli_fetch_array($query);
				$prev_yearmonth = $prev_salon['yearmonth'];
				$prev_contest_name = $prev_salon['contest_name'];
				if (file_exists("../salons/$prev_yearmonth/blob/certdef.json")) {
					copy("../salons/$prev_yearmonth/blob/certdef.json", "../salons/$yearmonth/blob/certdef.json");
					$_SESSION['info_msg'] = "Copied Certificate Definition from $prev_contest_name.";
				}
			}
		}
		if ($cert = get_config($yearmonth))
			$has_conf = true;

		// Check for Certificate Design
		$design_file = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_cert.png";
		if (file_exists("../salons/$yearmonth/img/$design_file")) {
			$has_design = true;
			list($design_width_px, $design_height_px) = getimagesize("../salons/$yearmonth/img/$design_file");
			$width = ($design_width_px / 300 * 72);
			$height = ($design_height_px / 300 * 72);
			$orientation = ($width < $height ? "P" : "L");
		}

		// Border Images
		$border_img = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_frame.png";
		if (file_exists("../salons/$yearmonth/img/$border_img"))
			$has_border_img = true;

		// Signatures
		$chairman_signature_file = isset($cert['doc']['files']['chairman_sig']) && $cert['doc']['files']['chairman_sig'] != "" ? $cert['doc']['files']['chairman_sig'] : "chairman_sig.png";
		$has_chairman_signature = file_exists("../salons/$yearmonth/img/com/$chairman_signature_file");
		$secretary_signature_file = isset($cert['doc']['files']['secretary_sig']) && $cert['doc']['files']['secretary_sig'] != "" ? $cert['doc']['files']['secretary_sig'] : "secretary_sig.png";
		$has_secretary_signature = file_exists("../salons/$yearmonth/img/com/$secretary_signature_file");

		$sql = "SELECT DISTINCT level FROM award WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			$level = $row['level'];
			$level_legend = get_level_legend($level);
			$level_design_file = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_cert_$level.png";
			if (file_exists("../salons/$yearmonth/img/$level_design_file"))
				$has_level_design = true;
			else
				$has_level_design = false;
			$level_border_img = ($is_international ? "is" : "ais") . substr($yearmonth, 0, 4) . "_frame_$level.png";
			if (file_exists("../salons/$yearmonth/img/$level_border_img"))
				$has_level_border = true;
			else
				$has_level_border = false;

			$level_list[$level] = array("legend" => $level_legend, "design" => $level_design_file, "has_design" => $has_level_design,
										"border_img" => $level_border_img, "has_border_img" => $has_level_border);
		}
	}

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
						Design Certificate
					</h3>
					<br>
					<form role="form" method="post" name="select-contest-form" action="certificate.php" enctype="multipart/form-data" >
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
			<form role="form" method="post" name="certificate-form" id="edit-certificate-form" enctype="multipart/form-data" >
				<!-- Hidden Variables -->
				<input type="hidden" name="yearmonth" id="yearmonth" value="<?= $yearmonth;?>" >
				<input type="hidden" name="is_international" value="<?= $salon['is_international'];?>" >
				<input type="hidden" name="unit" id="unit" value="pt" >
				<input type="hidden" name="page_width" id="width" value="<?= $width;?>" >
				<input type="hidden" name="page_height" id="height" value="<?= $height;?>" >
				<input type="hidden" name="page_orientation" id="orientation" value="<?= $orientation;?>" >

				<?php
					foreach ($default_colors as $color) {
						if ($color['name'] != 'color_custom_1' && $color['name'] != 'color_custom_2' &&
							$color['name'] != 'color_custom_3' && $color['name'] != 'color_custom_4' ) {
				?>
				<input type="hidden" name="<?= $color['name'];?>" value="<?= get_color($color['name']);?>" >
				<?php
						}
					}
				?>

				<input type="hidden" name="design" id="update-design" value="<?= $has_conf && isset($cert['doc']['files']['design']) ? $cert['doc']['files']['design'] : '';?>" >
				<input type="hidden" name="border_img" id="update-border-img" value="<?= $has_conf && isset($cert['doc']['files']['border_img']) ? $cert['doc']['files']['border_img'] : '';?>" >
				<?php
					foreach ($level_list as $level => $data) {
				?>
				<input type="hidden" name="level_design[<?= $level;?>]" id="update-level-design-<?= $level;?>"
						value="<?= ($has_conf && isset($cert['doc']['files']['levels'])) ? get_level_design($cert['doc']['files']['levels'], $level)  : '';?>" >
				<input type="hidden" name="level_border_img[<?= $level;?>]" id="update-level-border-img-<?= $level;?>"
						value="<?= ($has_conf && isset($cert['doc']['files']['levels'])) ? get_level_border_img($cert['doc']['files']['levels'], $level) : '';?>" >
				<?php
					}
				?>
				<input type="hidden" name="chairman_sig" id="update-chairman-signature"
						value="<?= ($has_conf && isset($cert['doc']['files']['chairman_sig'])) ? $cert['doc']['files']['chairman_sig'] : '';?>" >
				<input type="hidden" name="secretary_sig" id="update-secretary-signature"
						value="<?= ($has_conf && isset($cert['doc']['files']['secretary_sig'])) ? $cert['doc']['files']['secretary_sig'] : '';?>" >

				<!-- DOCUMENT -->
				<!-- Images -->
				<div class="row form-group">
					<div class="col-sm-4">
						<div class="row">
							<!-- TO DO : *** Separate designs for each Level *** -->
							<div class="col-sm-12"><b>Certificate Design - General*</b></div>
							<div class="col-sm-12">
								<img src="<?= $has_design ? "/salons/$yearmonth/img/$design_file" : "/img/preview.png";?>"
											id="disp-design" style="max-width: 100%;" >
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
					<div class="col-sm-4">
						<div class="row">
							<div class="col-sm-12"><b>Border Frame - General</b></div>
							<div class="col-sm-12">
								<img src="<?= $has_border_img ? "/salons/$yearmonth/img/$border_img" : "/img/preview.png";?>"
										id="disp-border-img" style="max-width: 100%;" >
							</div>
							<div class="col-sm-12">
								<label for="edit-border-img">Select Graphics</label>
								<input type="file" name="border_img" id="edit-border-img" >
								<p id="error-border-img" class="text-danger"></p>
								<button id="upload-border-img" class="btn btn-info" disabled
										data-file="<?= $border_img;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="row form-group">
							<div class="col-sm-12">
								<label for="page_unit">Unit used</label>
								<input type="text" class="form-control" value="Point (1/72 inch)" readonly>
							</div>
							<div class="col-sm-12">
								<label for="page_unit">Page Size (WxH) in Points</label>
								<input type="text" class="form-control" id="page-size" value="<?= $width . ' x ' . $height . ' pts';?>" readonly>
							</div>
							<div class="col-sm-12">
								<label for="page_orientation">Orientation</label>
								<input type="text" class="form-control" id="page-orientation" value="<?= $orientation == 'P' ? 'Portrait' : 'Landscape';?>" readonly>
							</div>
							<div class="col-sm-12">
								<label for="cutting-bleed">Cutting Mark Size</label>
								<div class="input-group">
									<input type="number" id="cutting-bleed" name="cutting_bleed" class="form-control"
											value="<?= $has_conf && isset($cert['doc']['page']['cutting_bleed']) ? $cert['doc']['page']['cutting_bleed'] : 0;?>" required>
									<span class="input-group-btn">
										<button class="btn btn-info convert-unit" data-input="cutting-bleed" ><i class="fa fa-calculator"></i></a>
									</span>
								</div>
							</div>
							<div class="col-sm-12">
								<?php
									if ($has_conf)
										$file_name_stub = $cert['doc']['files']['file_name_stub'];
									else
									 	$file_name_stub = ($is_international ? "IS" : "AIS") . substr($yearmonth, 0, 4) . "_CERT";
								?>
								<label for="file-name-stub">Certificate File Name Stub</label>
								<input type="text" class="form-control" name="file_name_stub" id="file-name-stub" value="<?= $file_name_stub;?>" required>
							</div>
							<div class="col-sm-12">
								<label for="same-design-for-all-levels">Use same design & frame for ALL Award Levels ?</label>
								<div class="input-group">
									<span class="input-group-addon">
										<input type="checkbox" name="same_design_for_all_levels" id="same-design-for-all-levels" value="1"
												<?php
												 	if (isset($cert['files']['same_design_for_all_levels']))
													 	if ($cert['files']['same_design_for_all_levels'] == '1')
														 	echo "checked";
														else
														 	echo "";
													else
													 	echo "checked";
												?> >
									</span>
									<input type="text" class="form-control" value="Use for all levels" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row" id="designs-by-level" style="display: none;">
				<?php
					foreach ($level_list as $level => $data) {
						$level_legend = get_level_legend($level);
						$level_design_file = $data['design'];
						$has_level_design = $data['has_design'];
						$level_border_img = $data['border_img'];
						$has_level_border = $data['has_border_img'];
				?>
					<div class="col-sm-3">
						<div class="row">
							<!-- TO DO : *** Separate designs for each Level *** -->
							<div class="col-sm-12"><b>Certificate Design for <?= $level_legend;?>*</b></div>
							<div class="col-sm-12">
								<img src="<?= $has_level_design ? "/salons/$yearmonth/img/$level_design_file" : "/img/preview.png";?>"
										id="disp-design-<?= $level;?>" style="max-width: 100%;" >
								<label for="edit-design-<?= $level;?>">Select Graphics</label>
								<input type="file" name="level_design[]" id="edit-design-<?= $level;?>"
										class="edit_level_cert_design" data-level="<?= $level;?>" >
								<p id="error-design-<?= $level;?>" class="text-danger"></p>
								<button class="upload_level_cert_design btn btn-info" id="upload-design-<?= $level;?>" disabled
										data-level="<?= $level;?>"
										data-file="<?= $level_design_file;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="row">
							<div class="col-sm-12"><b>Picture Frame for <?= $level_legend;?></b></div>
							<div class="col-sm-12">
								<img src="<?= $has_level_border ? "/salons/$yearmonth/img/$level_border_img" : "/img/preview.png";?>"
										id="disp-border-img-gen-<?= $level;?>" style="max-width: 100%;" >
							</div>
							<div class="col-sm-12">
								<label for="edit-border-img-<?= $level;?>">Select Graphics</label>
								<input type="file" name="level_border_img[]" id="edit-border-img-<?= $level;?>"
								 		class="edit_level_border_img" data-level="<?= $level;?>">
								<p id="error-border-img-<?= $level;?>" class="text-danger"></p>
								<button id="upload-border-img-<?= $level;?>" class="upload_border_img btn btn-info" disabled
										data-level="<?= $level;?>"
										data-file="<?= $level_border_img;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				<?php
					}
				?>
				</div>

				<!-- Signature Files -->
				<!-- Images -->
				<div class="row form-group">
					<div class="col-sm-3">
						<div class="row">
							<!-- TO DO : *** Separate designs for each Level *** -->
							<div class="col-sm-12"><b>Chairman Signature for Embedding</b></div>
							<div class="col-sm-12">
								<img src="<?= $has_chairman_signature ? "/salons/$yearmonth/img/com/$chairman_signature_file" : "/img/preview.png";?>"
										id="disp-chairman-signature" style="max-width: 100%;" >
								<label for="edit-chairman-signature">Select Graphics</label>
								<input type="file" name="chairman_signature" id="edit-chairman-signature" >
								<p id="error-chairman-signature" class="text-danger"></p>
								<button id="upload-chairman-signature" class="btn btn-info" disabled
										data-file="<?= $chairman_signature_file;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="row">
							<div class="col-sm-12"><b>Secretary Signature for Embedding</b></div>
							<div class="col-sm-12">
								<img src="<?= $has_secretary_signature ? "/salons/$yearmonth/img/com/$secretary_signature_file" : "/img/preview.png";?>"
										id="disp-secretary-signature" style="max-width: 100%;" >
							</div>
							<div class="col-sm-12">
								<label for="edit-secretary-signature">Select Graphics</label>
								<input type="file" name="secretary_signature" id="edit-secretary-signature" >
								<p id="error-secretary-signature" class="text-danger"></p>
								<button id="upload-secretary-signature" class="btn btn-info" disabled
										data-file="<?= $secretary_signature_file;?>">
									<i class="fa fa-upload"></i> Upload
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Font -->
				<div class="row form-group">
					<?php
						$font_family = $has_conf ? $cert['doc']['font']['font_family'] : '';
						$font_regular = $has_conf ? $cert['doc']['font']['font_regular'] : '';
						$font_bold = $has_conf ? $cert['doc']['font']['font_bold'] : '';
						$font_italic = $has_conf ? $cert['doc']['font']['font_italic'] : '';
					?>
					<div class="col-sm-3">
						<label for="edit_font_family_name">Font Family Name</label>
						<input type="text" name="font_family" class="form-control" id="edit-font-family-name"
								value="<?= $font_family;?>"
								placeholder="Font Family name..." required >
					</div>
					<div class="col-sm-3">
						<label for="edit_font_regular">Regular Font</label>
						<select class="form-control" name="font_regular" id="edit_font_regular"
								value="<?= $font_regular;?>" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= $font_file == $font_regular ? 'selected' : '';?> ><?= basename($font_file);?></option>
						<?php
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="edit_font_bold">Bold Font</label>
						<select class="form-control" name="font_bold" id="edit_font_bold" value="<?= $font_bold;?>" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= $font_file == $font_bold ? 'selected' : '';?> ><?= basename($font_file);?></option>
						<?php
							}
						?>
						</select>
					</div>
					<div class="col-sm-3">
						<label for="edit_font_italic">Italic Font</label>
						<select class="form-control" name="font_italic" id="edit_font_italic" value="<?= $font_italic;?>" required >
						<?php
							foreach($font_files as $font_file) {
						?>
							<option value="<?= $font_file;?>" <?= $font_file == $font_italic ? 'selected' : '';?> ><?= basename($font_file);?></option>
						<?php
							}
						?>
						</select>
					</div>
				</div>

				<!-- Custom Colors -->
				<div class="row form-group">
					<div class="col-sm-3">
						<label for="edit-custom-color-1">Custom Color 1</label>
						<div id="custom-color-1" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-1" name="color_custom_1"
									value="<?= get_color('color_custom_1');?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-2">Custom Color 2</label>
						<div id="custom-color-2" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-2" name="color_custom_2"
									value="<?= get_color('color_custom_2');?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-3">Custom Color 3</label>
						<div id="custom-color-3" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-3" name="color_custom_3"
									value="<?= get_color('color_custom_3');?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
					<div class="col-sm-3">
						<label for="edit-custom-color-1">Custom Color 4</label>
						<div id="custom-color-4" class="input-group color-picker-component">
							<input type="text" class="form-control" id="edit-custom-color-4" name="color_custom_4"
									value="<?= get_color('color_custom_4');?>" >
							<span class="input-group-addon"><i></i></span>
						</div>
					</div>
				</div>

				<!-- BLOCKS -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Page Definition - BLOCKS</h3>
						<button class="btn btn-info" id="add-block-btn" ><i class="fa fa-plus-circle"></i> Add Block</button>
						<table id="block-table" class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Type</th>
									<th>Left</th>
									<th>Top</th>
									<th>Width</th>
									<th>Height</th>
									<th>Orientation</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody>
						<?php
							if ($has_conf) {
								for ($idx = 0; $idx < sizeof($cert['blocks']); ++ $idx) {
									$row = $cert['blocks'][$idx];
						?>
								<tr id="block-<?= $idx;?>-row" class="block-row"
										data-name="<?= $row['name'];?>" data-x="<?= $row['x'];?>" data-y="<?= $row['y'];?>" >
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="block_data[]" id="block-data-<?= $idx;?>" value='<?= json_encode($row);?>' >
									<td><?= $row['name']; ?>
									<td><?= get_label($block_types, $row['type']);?></td>
									<td><?= $row['x'];?></td>
									<td><?= $row['y'];?></td>
									<td><?= $row['width'];?></td>
									<td><?= $row['height'];?></td>
									<td><?= $row['orientation'] == "F" ? "Flipped" : "Normal";?></td>
									<td>
										<button id="edit-block-<?= $idx;?>" class="btn btn-info edit-block-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-block-<?= $idx;?>" class="btn btn-danger delete-block-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-trash"></i> Delete
										</button>
									</td>
								</tr>
						<?php
								}
							}
						?>
								<tr id="end-of-block-table" data-idx="<?= isset($idx) ? $idx : 0;?>">
									<td>-- End of Blocks</td>
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

				<!-- NODES -->
				<div class="row">
					<div class="col-sm-12">
						<h3 class="text-info">Page Definition - NODES</h3>
						<button class="btn btn-info" id="add-image-node-btn" ><i class="fa fa-plus-circle"></i> Add Image Node</button>
						<button style="padding-left : 30px;" class="btn btn-info" id="add-text-node-btn" ><i class="fa fa-plus-circle"></i> Add text Node</button>
						<table id="node-table" class="table">
							<thead>
								<tr>
									<th>Block</th>
									<th>Sequence</th>
									<th>Field Name/Label</th>
									<th>Field Type</th>
									<th>Content Type</th>
									<th>Edit</th>
									<th>Delete</th>
							</thead>
							<tbody>
						<?php
							if ($has_conf) {
								for ($idx = 0; $idx < sizeof($cert['nodes']); ++ $idx) {
									$row = $cert['nodes'][$idx];
						?>
								<tr id="node-<?= $idx;?>-row" class="node-row"
									data-name="<?= $row['name'];?>"
									data-type="<?= $row['type'];?>"
									data-nodetype="<?= $row['nodetype'];?>"
								>
									<!-- Placeholder for Input Fields - will contain json Encoded Text -->
									<input type="hidden" name="node_data[]" id="node-data-<?= $idx;?>" value='<?= json_encode($row);?>' >
									<td><?= $row['block'];?></td>
									<td><?= $row['sequence'];?></td>
									<?php
										if ($row['nodetype'] == 'image') {
									?>
									<td><?= get_label($image_nodes, $row['name']);?></td>
									<?php
										}
										else {
											if ($row['type'] == 'field') {
									?>
									<td><?= get_label($text_nodes, $row['name']);?></td>
									<?php
											}
											else {
									?>
									<td><?= $row['name'] . "='" . $row['value'] . "'";?></td>
									<?php
											}
										}
									?>
									<td><?= $row['nodetype'] == "text" ? get_label($text_node_types, $row['type']) : "Field";?></td>
									<td><?= get_label($node_nodetypes, $row['nodetype']);?></td>
									<td>
										<button id="edit-node-<?= $idx;?>" class="btn btn-info edit-<?= $row['nodetype'];?>-node-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-edit"></i> Edit
										</button>
									</td>
									<td>
										<button id="delete-node-<?= $idx;?>" class="btn btn-danger delete-<?= $row['nodetype'];?>-node-btn"
												data-idx='<?= $idx;?>' >
											<i class="fa fa-trash"></i> Delete
										</button>
									</td>
								</tr>
						<?php
								}
							}
						?>
								<tr id="end-of-node-table" data-idx="<?= isset($idx) ? $idx : 0;?>">
									<td>-- End of Nodes</td>
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
		<!-- Edit Block -->
		<div class="modal" id="edit-block-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Block</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-block-form" name="edit_block_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="is-edit-block" value="0" >
							<input type="hidden" id="block-edit-idx" value="0" >
							<input type="hidden" name="print" value="no" >

							<!-- Edited Fields -->
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- name -->
								<div class="col-sm-6">
									<label for="edit-name">Block Name (no spaces between words)</label>
									<input id="edit-name" name="name" class="form-control" required>
								</div>
								<!-- Type -->
								<div class="col-sm-6">
									<label for="edit-type">Select Block Type</label>
									<select id="edit-type" name="type" class="form-control"></select>
								</div>
							</div>
							<!-- Position -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-x">Left</label>
									<div class="input-group">
										<input type="number" id="edit-x" name="x" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-x" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="edit-y">Top</label>
									<div class="input-group">
										<input type="number" id="edit-y" name="y" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-y" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="edit-width">Width</label>
									<div class="input-group">
										<input type="number" id="edit-width" name="width" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-width" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="edit-height">Height</label>
									<div class="input-group">
										<input type="number" id="edit-height" name="height" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-height" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="edit-orientation">Orientation</label>
									<select id="edit-orientation" name="orientation" class="form-control" required>
										<option value="N">Normal</option>
										<option value="F">Flip Vertical</option>
									</select>
								</div>
							</div>
							<!-- Fill & Border -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="edit-border-width">Border Width</label>
									<div class="input-group">
										<input type="number" id="edit-border-width" name="border_width" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-border-width" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="edit-border-color">Border Color</label>
									<select id="edit-border-color" name="border_color" class="form-control" required>
										<?= color_options(); ?>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="edit-fill-color">Fill Color</label>
									<select id="edit-fill-color" name="fill_color" class="form-control" required>
										<?= color_options(); ?>
									</select>
								</div>
							</div>

							<!-- Print only when field value matches a value or does not match a value -->
							<div class="row">
								<div class="col-sm-12"><label>Add Condition for Printing this Block</label></div>
							</div>
							<div class="row form-group">
								<!-- Field 1 -->
								<div class="col-sm-5">
									<label for="cond-field-1">Field</label>
									<select id="cond-field-1" name="cond_field[]" class="form-control"></select>
								</div>
								<div class="col-sm-3">
									<label for="cond-match-1">Match</label>
									<select name="cond_match[]" id="cond-match-1" class="form-control" >
										<option value="EQ">Matches</option>
										<option value="NE">Not matches</option>
									</select>
								</div>
								<div class="col-sm-4">
									<label for="cond-value-1">Value</label>
									<input type="text" id="cond-value-1" name="cond_value[]" class="form-control">
								</div>
							</div>
							<div class="row form-group">
								<!-- Field 2 -->
								<div class="col-sm-5">
									<label for="cond-field-2">Field</label>
									<select id="cond-field-2" name="cond_field[]" class="form-control"></select>
								</div>
								<div class="col-sm-3">
									<label for="cond-match-2">Match</label>
									<select name="cond_match[]" id="cond-match-2" class="form-control" >
										<option value="EQ">Matches</option>
										<option value="NE">Not matches</option>
									</select>
								</div>
								<div class="col-sm-4">
									<label for="cond-value-2">Value</label>
									<input type="text" id="cond-value-2" name="cond_value[]" class="form-control">
								</div>
							</div>
							<div class="row form-group">
								<!-- Field 3 -->
								<div class="col-sm-5">
									<label for="cond-field-3">Field</label>
									<select id="cond-field-3" name="cond_field[]" class="form-control"></select>
								</div>
								<div class="col-sm-3">
									<label for="cond-match-3">Match</label>
									<select name="cond_match[]" id="cond-match-3" class="form-control" >
										<option value="EQ">Matches</option>
										<option value="NE">Not matches</option>
									</select>
								</div>
								<div class="col-sm-4">
									<label for="cond-value-3">Value</label>
									<input type="text" id="cond-value-3" name="cond_value[]" class="form-control">
								</div>
							</div>

							<!-- Print position when some field is missing -->
							<div class="row form-group">
								<div class="col=sm-12"><label>Shift Position when related block is omitted from certificate.</label></div>
								<div class="col-sm-6">
									<label for="edit-omitted">Omitted Block</label>
									<select id="edit-omitted" class="form-control" name="print_at_when_omitted"></select>
								</div>
								<div class="col-sm-3">
									<label for="edit-omitted-x">@ Left</label>
									<div class="input-group">
										<input type="number" class="form-control" id="edit-omitted-x" name="x_at">
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-omitted-x" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-3">
									<label for="edit-omitted-y">@ Top</label>
									<div class="input-group">
										<input type="number" class="form-control" id="edit-omitted-y" name="y_at">
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="edit-omitted-y" ><i class="fa fa-calculator"></i></a>
										</span>
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

		<!-- Edit Image Node -->
		<div class="modal" id="edit-image-node-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Image Node</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-image-node-form" name="edit_image_node_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="in-is-edit-node" value="0" >
							<input type="hidden" id="in-edit-idx" value="0" >
							<input type="hidden" name="nodetype" value="image" >
							<input type="hidden" name="type" value="field" >
							<input type="hidden" name="height" value="0" >

							<!-- Edited Fields -->
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- Name -->
								<div class="col-sm-6">
									<label for="in-edit-name">Node Name</label>
									<select id="in-edit-name" name="name" class="form-control" required></select>
								</div>
								<!-- Block -->
								<div class="col-sm-6">
									<label for="in-edit-block">Select Block</label>
									<select id="in-edit-block" name="block" class="form-control" required></select>
								</div>
								<!-- Sequence -->
								<div class="col-sm-6">
									<label for="in-edit-sequence">Sequence</label>
									<input type="number" id="in-edit-sequence" name="sequence" class="form-control">
								</div>
								<!-- Omit if Empty -->
								<div class="col-sm-6">
									<label>Omit Empty Block</label>
									<div class="input-group">
										<span class="input-group-addon">
											<input type="checkbox" name="omit_if_empty" id="in-edit-omit-empty" value="yes">
										</span>
										<input type="text" class="form-control" value="Omit if Empty" readonly>
									</div>
								</div>
							</div>
							<!-- Position -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="in-edit-float">Position</label>
									<select class="form-control" name="float" id="in-edit-float">
										<option value="left">Left</option>
										<option value="right">Right</option>
										<option value="fill">Fill</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="in-edit-spacing">Spacing</label>
									<div class="input-group">
										<input type="number" id="in-edit-spacing" name="spacing" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="in-edit-spacing" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
							</div>
							<!-- Fill & Border -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="in-edit-border">Border Type</label>
									<select class="form-control" name="bordertype" id="in-edit-border-type">
										<option value="">No Border</option>
										<option value="line">Line Border</option>
										<option value="frame">Frame Border</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="in-edit-border-width">Border Width</label>
									<div class="input-group">
										<input type="number" id="in-edit-border-width" name="borderwidth" class="form-control" required>
										<span class="input-group-btn">
											<button class="btn btn-info convert-unit" data-input="in-edit-border-width" ><i class="fa fa-calculator"></i></a>
										</span>
									</div>
								</div>
								<div class="col-sm-6">
									<label for="in-edit-border-color">Border Color</label>
									<select id="in-edit-border-color" name="bordercolor" class="form-control" required>
										<?= color_options(); ?>
									</select>
								</div>
								<!-- Border Image -->
								<div class="col-sm-12">
									<label for="in-edit-border-image">Frame Image</label>
									<select name="borderimage" id="in-edit-border-image" class="form-control">
										<option value="">No Border Frame</option>
										<option value="frame">Use Border Frame</option>
									</select>
								</div>
							</div>

							<!-- Update -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="in-edit-update" name="image-edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Text Field Form -->
		<div class="modal" id="edit-text-node-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Edit Text Node</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="edit-text-node-form" name="edit_image_node_form" action="#" enctype="multipart/form-data" >
							<!-- Hidden non-edited fields -->
							<input type="hidden" id="tn-is-edit-node" value="0" >
							<input type="hidden" id="tn-edit-idx" value="0" >
							<input type="hidden" name="font_family" id="tn-edit-font-family" value="Font Family">
							<input type="hidden" name="nodetype" value="text" >
							<input type="hidden" name="height" value="0" >
							<input type="hidden" name="group" value="" >

							<!-- Edited Fields -->
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- Node Type -->
								<div class="col-sm-6">
									<label for="tn-edit-type">Type</label>
									<select id="tn-edit-type" name="type" class="form-control" required></select>
								</div>
								<!-- Field -->
								<div class="col-sm-6">
									<label for="tn-edit-name">Field</label>
									<select id="tn-edit-name" name="name" class="form-control"></select>
								</div>
								<!-- Label -->
								<div class="col-sm-6">
									<label for="tn-edit-label">Label</label>
									<input type"text" id="tn-edit-label" name="label" class="form-control">
								</div>
								<!-- Label -->
								<div class="col-sm-6">
									<label for="tn-edit-template">Template (place [value] in text to merge)</label>
									<input type"text" id="tn-edit-template" name="template" class="form-control">
								</div>
								<!-- Omit if Empty -->
								<div class="col-sm-6">
									<label for="tn-edit-omit-empty">Omit from print if empty</label>
									<div class="input-group">
										<span class="input-group-addon">
											<input type="checkbox" name="omit_if_empty" id="tn-edit-omit-empty" value="yes">
										</span>
										<input type="text" class="form-control" value="Omit if Empty" readonly>
									</div>
								</div>
							</div>
							<div class="row form-group">
								<!-- Block -->
								<div class="col-sm-6">
									<label for="tn-edit-block">Select Block</label>
									<select id="tn-edit-block" name="block" class="form-control" required></select>
								</div>
								<!-- Sequence -->
								<div class="col-sm-6">
									<label for="tn-edit-sequence">Sequence</label>
									<input type="number" id="tn-edit-sequence" name="sequence" class="form-control">
								</div>
							</div>
							<!-- Font -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for='tn-edit-font-style'>Font Style</label>
									<select id="tn-edit-font-style" name="font_style" class="form-control">
										<option value="">Regular</option>
										<option value="B"><b>Bold</b></option>
										<option value="I"><i>Italic</i></option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="tn-edit-font-size">Size Pts</label>
									<input type="number" name="font_size" id="tn-edit-font-size" class="form-control">
								</div>
								<div class="col-sm-6">
									<label for="tn-edit-font-color">Color</label>
									<select id="tn-edit-font-color" name="font_color" class="form-control" required>
										<?= color_options(); ?>
									</select>
								</div>
							</div>
							<!-- Position -->
							<div class="row form-group">
								<div class="col-sm-6">
									<label for="tn-edit-align">Text Alignment</label>
									<select name="align" id="tn-edit-align" class="form-control">
										<option value="L">Left</option>
										<option value="C">Center</option>
										<option value="R">Right</option>
									</select>
								</div>
								<div class="col-sm-6">
									<label for="tn-edit-line-spacing">Line spacing</label>
									<select id="tn-edit-line-spacing" name="line_spacing" class="form-control">
										<option value="1.0">Line Spacing 1.0</option>
										<option value="1.1">Line Spacing 1.1</option>
										<option value="1.2">Line Spacing 1.2</option>
										<option value="1.3">Line Spacing 1.3</option>
										<option value="1.4">Line Spacing 1.4</option>
										<option value="1.5">Line Spacing 1.5</option>
										<option value="1.6">Line Spacing 1.6</option>
										<option value="1.7">Line Spacing 1.7</option>
										<option value="1.8">Line Spacing 1.8</option>
										<option value="1.9">Line Spacing 1.9</option>
										<option value="2.0">Line Spacing 2.0</option>
									</select>
								</div>
							</div>
							<!-- Update Button -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="tn-edit-update" name="text-edit-update" value="Update">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Unit Conversion Form -->
		<div class="modal" id="convert-unit-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row">
							<div class="col-sm-10">
								<h4 class="modal-title"><small>Convert units to Pts</small></h4>
							</div>
							<div class="col-sm-2">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<form role="form" method="post" id="convert-unit-form" name="convert_unit_form" action="#" >
							<!-- Type, Field, Value -->
							<div class="row form-group">
								<!-- Pixels -->
								<div class="col-sm-6">
									<label for="cv-px">Pixels</label>
									<input type="number" class="form-control" name="cv-px" id="cv-px" >
								</div>
								<!-- DPI -->
								<div class="col-sm-6">
									<label for="cv-dpi">@ DPI</label>
									<input type="number" class="form-control" name="cv-dpi" id="cv-dpi" >
								</div>
								<!-- Millimeters -->
								<div class="col-sm-12">
									<label for="cv-mm">Millimeters</label>
									<input type="number" class="form-control" name="cv-mm" id="cv-mm" >
								</div>
								<!-- Inches -->
								<div class="col-sm-12">
									<label for="cv-in">Inches</label>
									<input type="number" class="form-control" name="cv-in" id="cv-in" >
								</div>
								<!-- Points -->
								<div class="col-sm-12">
									<label for="cv-pt">Points</label>
									<input type="number" class="form-control" name="cv-pt" id="cv-pt" >
								</div>
							</div>
							<!-- Update Button -->
							<div class="row" style="padding-top: 20px;">
								<div class="col-sm-12">
									<input class="btn btn-primary pull-right" type="submit" id="cv-update" name="cv-update" value="Update">
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
		// Attach Color Picker
		$('#custom-color-1').colorpicker();
		$('#custom-color-2').colorpicker();
		$('#custom-color-3').colorpicker();
        $('#custom-color-4').colorpicker();
	});
</script>

<!-- Handle Lists -->
<script>
	var font_files = JSON.parse('<?= json_encode($font_files);?>');

	var block_types = <?= json_encode($block_types);?>;
	var node_nodetypes = <?= json_encode($node_nodetypes);?>;
	var text_node_types = <?= json_encode($text_node_types);?>;
	var image_nodes = <?= json_encode($image_nodes);?>;
	var text_nodes = <?= json_encode($text_nodes);?>;
	var node_columns = <?= json_encode($node_columns);?>;

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
<!-- Convert Unit -->
<script>

	function open_converter_modal(input) {
		// compute units and update fields
		let points = $("#" + input).val();
		let inches = Number((points / 72).toFixed(2));
		let millimeters = Number((inches * 25.4).toFixed(2));
		let dpi = 300;
		let pixels = Number((inches * dpi).toFixed(0));

		$("#cv-px").val(pixels);
		$("#cv-dpi").val(dpi);
		$("#cv-mm").val(millimeters);
		$("#cv-in").val(inches);
		$("#cv-pt").val(points);

		// Install Handlers
		$("#cv-dpi").change(function(){
			let dpi = Number($(this).val().toFixed(0));
			let pixels = $("#cv-px").val();
			let inches =Number((pixels / dpi).toFixed(2));
			let points = Number((inches * 72).toFixed(2));
			let millimeters = Number((inches * 25.4).toFixed(2));
			$("#cv-in").val(inches);
			$("#cv-pt").val(points);
			$("#cv-mm").val(millimeters);
		});
		$("#cv-px").change(function(){
			let pixels = $(this).val();
			let inches =Number((pixels / $("#cv-dpi").val()).toFixed(2));
			let points = Number((inches * 72).toFixed(2));
			let millimeters = Number((inches * 25.4).toFixed(2));
			$("#cv-in").val(inches);
			$("#cv-pt").val(points);
			$("#cv-px").val(pixels);
		});
		$("#cv-mm").change(function(){
			let millimeters = $(this).val();
			let inches =Number((millimeters / 25.4).toFixed(2));
			let points = Number((inches * 72).toFixed(2));
			let pixels = Number((inches * $("#cv-dpi").val()).toFixed(0));
			$("#cv-in").val(inches);
			$("#cv-pt").val(points);
			$("#cv-px").val(pixels);
		});
		$("#cv-in").change(function(){
			let inches = $(this).val();
			let millimeters =Number((inches * 25.4).toFixed(2));
			let points = Number((inches * 72).toFixed(2));
			let pixels = Number((inches * $("#cv-dpi").val()).toFixed(0));
			$("#cv-mm").val(millimeters);
			$("#cv-pt").val(points);
			$("#cv-px").val(pixels);
		});
		$("#cv-pt").change(function(){
			let points = $(this).val();
			let inches = Number((points / 72).toFixed(2));
			let millimeters = Number((inches * 25.4).toFixed(2));
			let pixels = Number((inches * $("#cv-dpi").val()).toFixed(0));
			$("#cv-mm").val(millimeters);
			$("#cv-in").val(inches);
			$("#cv-px").val(pixels);
		});
		$("#cv-update").click(function(e){
			e.preventDefault();
			$("#" + input).val($("#cv-pt").val());
			$("#convert-unit-modal").modal('hide');
		});

		// Show Modal
		$("#convert-unit-modal").modal('show');
	}

	$(document).ready(function(){
		$(".convert-unit").click(function(e){
			e.preventDefault();
			let input = $(this).attr("data-input");
			open_converter_modal(input);
		});
	});
</script>

<!-- Block Modal -->
<script>
	// Fetch Block data and launch edit dialog
	function launch_block_modal(mode, idx, block = {}) {
		$("#is-edit-block").val(mode == "edit" ? "1" : "0");
		$("#block-edit-idx").val(idx);

		// Populate Lists
		// Type
		$("#edit-type").html(get_options(block_types));

		// Block List
		let block_list = "";
		$(".block-row").each(function(index, elem) {
			block_list += "<option value='" + $(elem).attr("data-name") + "' data-x='" + $(elem).attr('data-x') + "' data-y='" + $(elem).attr("data-y") + "' >" + $(elem).attr("data-name") + "</option>";
		});
		block_list += "<option value='' data-x='0' data-y='0'>No Block</option>";

		// Field List
		// let field_list = "";
		// $(".node-row[data-nodetype='text'][data-type='field']").each(function(index, elem) {
		// 	field_list += "<option value='" + $(elem).attr("data-name") + "' >" + get_label(text_nodes, $(elem).attr("data-name")) + "</option>";
		// });
		// field_list += "<option value=''>No Field</option>";

		field_list = get_options(node_columns);


		if (mode == "edit") {
			// Mandatory Fields
			$("#edit-name").val(block.name);
			$("#edit-type").val(block.type);
			$("#edit-type option[value='" + block.type + "']").attr("selected", true);
			$("#edit-x").val(block.x);
			$("#edit-y").val(block.y);
			$("#edit-width").val(block.width);
			$("#edit-height").val(block.height);
			$("#edit-orientation").val(block.orientation);
			$("#edit-orientation option[value='" + block.orientation + "']").attr("selected", true);
			$("#edit-border-width").val(block.border_width);
			$("#edit-border-color").val(block.border_color == '' ? 'color_none' : block.border_color);
			$("#edit-border-color option[value='" + (block.border_color == '' ? 'color_none' : block.border_color) + "']").attr("selected", true);
			$("#edit-fill-color").val(block.fill_color == '' ? 'color_none' : block.fill_color);
			$("#edit-fill-color option[value='" + (block.fill_color == '' ? 'color_none' : block.fill_color) + "']").attr("selected", true);
			// Optional Fields
			for (let i = 0; i < 3; ++i) {
				// Field
				$("#cond-field-" + (i+1)).html(field_list);
				if (block.if && block.if.field[i]) {
					$("#cond-field-" + (i+1)).val(block.if.field[i]);
					$("#cond-field-" + (i+1) + " option[value='" + block.if.field[i] + "']").attr("selected", true);
					// Match
					$("#cond-match-" + (i+1)).val(block.if.match[i]);
					$("#cond-match-" + (i+1) + " option[value='" + block.if.match[i] + "']").attr("selected", true);
					// Value
					$("#cond-value-" + (i+1)).val(block.if.value[i]);
				}
				else {
					$("#cond-field-" + (i+1)).val("");
					$("#cond-match-" + (i+1)).val("EQ");
					$("#cond-match-" + (i+1) + " option[value='EQ']").attr("selected", true);
					$("#cond-value-" + (i+1)).val("");
				}
			}

			// Print at when a related field is omitted
			let print_at_when_omitted = block.print_at_when_omitted ? block.print_at_when_omitted : '';
			let x_at = block.x_at ? block.x_at : 0;
			let y_at = block.y_at ? block.y_at : 0;
			$("#edit-omitted").html(block_list);
			$("#edit-omitted").val(print_at_when_omitted);
			$("#edit-omitted option[value='" + print_at_when_omitted + "']").attr("selected", true);
			$("#edit-omitted-x").val(x_at);
			$("#edit-omitted-y").val(y_at);
		}
		else {
			$("#edit-name").val("");
			$("#edit-type").val("list");
			$("#edit-type option[value='list']").attr("selected", true);
			$("#edit-x").val(0);
			$("#edit-y").val(0);
			$("#edit-width").val(0);
			$("#edit-height").val(0);
			$("#edit-orientation").val("N");
			$("#edit-orientation option[value='N']").attr("selected", true);
			$("#edit-border-width").val(0);
			$("#edit-border-color").val("color_none");
			$("#edit-border-color option[value='color_none']").attr("selected", true);
			$("#edit-fill-color").val("color_none");
			$("#edit-fill-color option[value='color_none']").attr("selected", true);
			// conditional field 1
			$("#cond-field-1").html(field_list);
			$("#cond-field-1").val("");
			$("#cond-match-1").val("EQ");
			$("#cond-match-1 option[value='EQ']").attr("selected", true);
			$("#cond-value-1").val("");
			// conditional field 2
			$("#cond-field-2").html(field_list);
			$("#cond-field-2").val("");
			$("#cond-match-1").val("EQ");
			$("#cond-match-1 option[value='EQ']").attr("selected", true);
			$("#cond-value-2").val("");
			// conditional field 3
			$("#cond-field-3").html(field_list);
			$("#cond-field-3").val("");
			$("#cond-match-1").val("EQ");
			$("#cond-match-1 option[value='EQ']").attr("selected", true);
			$("#cond-value-3").val("");
			// Print at omitted block
			$("#edit-omitted").html(block_list);
			$("#edit-omitted").val("");
			$("#edit-omitted-x").val(0);
			$("#edit-omitted-y").val(0);
		}

		// Handlers
		$("#edit-omitted").on("change", function(){
			let option = $("#edit-omitted option:selected");
			$("#edit-omitted-x").val(option.attr("data-x"));
			$("#edit-omitted-y").val(option.attr("data-y"));
		});


		if (mode == "edit")
			$("#edit-update").val("Update");
		else
			$("#edit-update").val("Add");

		$("#edit-block-modal").modal('show');
	}

	function delete_block(idx) {
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the block ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$("#block-" + idx + "-row").remove();
			}
		});
	}

	// Block Modal Button Click Handling Functions
	$(document).ready(function(){
		// Add button
		$("#add-block-btn").click(function(e) {
			e.preventDefault();
			let idx = $("#end-of-block-table").attr("data-idx");
			launch_block_modal("add", idx);
		});

		// Edit button
		$(".edit-block-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let block = JSON.parse($("#block-data-" + idx).val());
			launch_block_modal("edit", idx, block);
		});

		// Delete Button
		$(".delete-block-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			delete_block(idx);
		});

		// Handle check/uncheck of same-design-for-all-levels
		$("#same-design-for-all-levels").on("change", function(){
			if($("#same-design-for-all-levels:checked").length > 0)
				$("#designs-by-level").hide();
			else
				$("#designs-by-level").show();
		});
	});


	// Functions to Update Block Table
	function build_block_row(data, idx) {

		// Assemble Data for the Row
		// Assemble Condition array
		let block_data = {
				"name" : data.get("name"),
				"type" : data.get("type"),
				"x" : data.get("x"),
				"y" : data.get("y"),
				"width" : data.get("width"),
				"height" : data.get("height"),
				"orientation" : data.get("orientation"),
				"border_width" : data.get("border_width"),
				"border_color" : data.get("border_color"),
				"fill_color" : data.get("fill_color"),
				"if" : {
					"field" : data.getAll("cond_field[]"),
					"match" : data.getAll("cond_match[]"),
					"value" : data.getAll("cond_value[]"),
				},
				"print_at_when_omitted" : data.get("print_at_when_omitted"),
				"x_at" : data.get("x_at"),
				"y_at" : data.get("y_at"),
				"print" : data.get("print"),
		};

		// Build the row to insert
		html  =	"<input type='hidden' name='block_data[]' id='block-data-" + idx + "' ";
		html += " value='" + JSON.stringify(block_data) + "' > ";
		html += "<td>" + data.get("name") + "</td> ";
		html += "<td>" + get_label(block_types, data.get('type')) + "</td> ";
		html += "<td>" + data.get("x") + "</td> ";
		html += "<td>" + data.get("y") + "</td> ";
		html += "<td>" + data.get("width") + "</td> ";
		html += "<td>" + data.get("height") + "</td> ";
		if (data.get("orientation") == "F")
			html += "<td>Flipped</td>";
		else
			html += "<td>Normal</td>";
		html += "<td> ";
		html += "<button id='edit-block-" + idx + "' class='btn btn-info edit-block-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='delete-block-" + idx + "' class='btn btn-danger delete-block-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-trash'></i> Delete ";
		html += "</button> ";
		html += "</td> ";

		return html;
	}

	function update_block_row(data, idx) {
		$("#block-" + idx + "-row").html(build_block_row(data, idx));

		// Install new handlers as updated buttons are treated as new buttons
		// Edit button
		$(".edit-block-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#block-data-" + fn_idx).val());
			launch_block_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-block-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_block(fn_idx);
		});
	}

	function add_block_row(data) {
		let idx = $("#end-of-block-table").attr("data-idx");

		let html = "<tr id='block-" + idx + "-row'> ";
		html += build_block_row(data, idx);
		html += "</tr> ";

		$(html).insertBefore("#end-of-block-table");
		++ idx;
		$("#end-of-block-table").attr("data-idx", idx);

		// Edit button
		$(".edit-block-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#block-data-" + fn_idx).val());
			launch_block_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-block-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_block(fn_idx);
		});
	}

	// Update rules back to the table
	$('#edit-block-form').validate({
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
			"edit-border-width" : {
				min : 0,
				max : 36,		// Half Inch max
			},
			"cond-value-1" : {
				required : ($("cond-field-1").val() != ""),
			},
			"cond-value-2" : {
				required : ($("cond-field-2").val() != ""),
			},
			"cond-value-3" : {
				required : ($("cond-field-3").val() != ""),
			},
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

			let idx = $("#block-edit-idx").val();

			// Assemble Data
			let formData = new FormData(form);

			// Update changes to the table
			if ($("#is-edit-block").val() == "0")
				add_block_row(formData);
			else
				update_block_row(formData, idx);

			$("#edit-block-modal").modal('hide');
		},
	});

</script>

<!-- Image Node Modal -->
<script>
	// Fetch Image Node data and launch edit dialog
	function launch_image_node_modal(mode, idx, node = {}) {
		$("#in-is-edit-node").val(mode == "edit" ? "1" : "0");
		$("#in-edit-idx").val(idx);

		// Populate Lists
		// Name
		$("#in-edit-name").html(get_options(image_nodes));

		// Block List
		let block_list = "";
		$(".block-row").each(function(index, elem) {
			block_list += "<option value='" + $(elem).attr("data-name") + "' >" + $(elem).attr("data-name") + "</option>";
		});
		$("#in-edit-block").html(block_list);

		if (mode == "edit") {
			$("#in-edit-name").val(node.name);
			$("#in-edit-name option[value='" + node.name + "']").attr("selected", true);
			$("#in-edit-block").val(node.block);
			$("#in-edit-block option[value='" + node.block + "']").attr("selected", true);
			$("#in-edit-sequence").val(node.sequence);

			if (node.omit_if_empty == "yes")
				$("#in-edit-omit-empty").attr("checked", true);
			else
				$("#in-edit-omit-empty").removeAttr("checked");

			$("#in-edit-float").val(node.float);
			$("#in-edit-float option[value='" + node.float + "']").attr("selected", true);

			$("#in-edit-spacing").val(node.spacing);

			let bordertype = (node.bordertype && node.bordertype != null) ? node.bordertype : '';
			$("#in-edit-border-type").val(bordertype);
			$("#in-edit-border-type option[value='" + bordertype + "']").attr("selected", true);

			if (bordertype == "line") {
				$("#in-edit-border-width").val(node.borderwidth);
				let border_color = node.bordercolor ? node.bordercolor : '';
				$("#in-edit-border-color").val(border_color == '' ? 'color_none' : border_color);
				$("#in-edit-border-color option[value='" + (border_color == '' ? 'color_none' : border_color) + "']").attr("selected", true);
				$("#in-edit-border-width").removeAttr("disabled");
				$("#in-edit-border-color").removeAttr("disabled");
				$("#in-edit-border-image").val("");
				$("#in-edit-border-image option[value='']").attr("selected", true);
				$("#in-edit-border-image").attr("disabled", true);
			}
			else if (bordertype == "frame") {
				$("#in-edit-border-width").val(0);
				$("#in-edit-border-width").attr("disabled", true);
				$("#in-edit-border-color").val('color_none');
				$("#in-edit-border-color option[value='color_none']").attr("selected", true);
				$("#in-edit-border-color").attr("disabled", true);
				let border_image = node.borderimage ? node.borderimage : "";
				$("#in-edit-border-image").val(border_image);
				$("#in-edit-border-image option[value='" + border_image + "']").attr("selected", true);
				$("#in-edit-border-image").removeAttr("disabled");
			}
			else {
				$("#in-edit-border-width").val(0);
				$("#in-edit-border-width").attr("disabled", true);
				$("#in-edit-border-color").val('color_none');
				$("#in-edit-border-color option[value='color_none']").attr("selected", true);
				$("#in-edit-border-color").attr("disabled", true);
				$("#in-edit-border-image").val("");
				$("#in-edit-border-image option[value='']").attr("selected", true);
				$("#in-edit-border-image").attr("disabled", true);
			}
		}
		else {
			$("#in-edit-name").val("");
			$("#in-edit-block").val("");
			$("#in-edit-sequence").val(0);
			$("#in-edit-omit-empty").removeAttr("checked");
			$("#in-edit-float").val("");
			$("#in-edit-spacing").val(0);
			$("#in-edit-float option[value='1.0']").attr("selected", true);
			$("#in-edit-border-type").val("");
			$("#in-edit-border-type option[value='']").attr("selected", true);
			$("#in-edit-border-width").val(0);
			$("#in-edit-border-width").attr("disabled", true);
			$("#in-edit-border-color").val("color_none");
			$("#in-edit-border-color option[value='color_none']").attr("selected", true);
			$("#in-edit-border-color").attr("disabled", true);
			$("#in-edit-border-image").val("");
			$("#in-edit-border-image option[value='']").attr("selected", true);
			$("#in-edit-border-image").attr("disabled", true);
		}

		if (mode == "edit")
			$("#in-edit-update").val("Update");
		else
			$("#in-edit-update").val("Add");

		$("#in-edit-border-type").on("change", function(e){
			if ($(this).val() == "line") {
				$("#in-edit-border-width").removeAttr("disabled");
				$("#in-edit-border-color").removeAttr("disabled");
				$("#in-edit-border-image").attr("disabled", true);
			}
			else if ($(this).val() == "frame") {
				$("#in-edit-border-width").attr("disabled", true);
				$("#in-edit-border-color").attr("disabled", true);
				$("#in-edit-border-image").removeAttr("disabled");
			}
			else {
				$("#in-edit-border-width").attr("disabled", true);
				$("#in-edit-border-color").attr("disabled", true);
				$("#in-edit-border-image").attr("disabled", true);
			}
		});

		$("#edit-image-node-modal").modal('show');
	}

	function delete_image_node(idx) {
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the Image Node ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$("#node-" + idx + "-row").remove();
			}
		});
	}

	// Image Node Modal Button Click Handling Functions
	$(document).ready(function(){
		// Add button
		$("#add-image-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $("#end-of-node-table").attr("data-idx");
			launch_image_node_modal("add", idx);
		});

		// Edit button
		$(".edit-image-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let node = JSON.parse($("#node-data-" + idx).val());
			launch_image_node_modal("edit", idx, node);
		});

		// Delete Button
		$(".delete-image-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			delete_image_node(idx);
		});
	});


	// Functions to Update Block Table
	function build_image_node_row(data, idx) {

		// Assemble Data for the Row
		let node_data = {
				"name" : data.get("name"),
				"nodetype" : data.get("nodetype"),
				"type" : data.get("type"),
				"block" : data.get("block"),
				"sequence" : data.get("sequence"),
				"omit_if_empty" : data.has("omit_if_empty") ? data.get("omit_if_empty") : "",
				"float" : data.get("float"),
				"spacing" : data.get("spacing"),
				"bordertype" : data.get("bordertype"),
				"borderwidth" : data.has("borderwidth") ? data.get("borderwidth") : 0,
				"bordercolor" : data.has("bordercolor") ? data.get("bordercolor") : "color_none",
				"borderimage" : data.has("borderimage") ? data.get("borderimage") : "",
				"height" : data.get("height"),
		};

		// Build the row to insert
		html  =	"<input type='hidden' name='node_data[]' id='node-data-" + idx + "' ";
		html += " value='" + JSON.stringify(node_data) + "' > ";
		html += "<td>" + data.get("block") + "</td> ";
		html += "<td>" + data.get("sequence") + "</td> ";
		html += "<td>" + get_label(image_nodes, data.get("name")) + "</td> ";
		html += "<td>Field</td> ";
		html += "<td>" + get_label(node_nodetypes, data.get('nodetype')) + "</td> ";
		html += "<td> ";
		html += "<button id='edit-node-" + idx + "' class='btn btn-info edit-image-node-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='delete-node-" + idx + "' class='btn btn-danger delete-image-node-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-trash'></i> Delete ";
		html += "</button> ";
		html += "</td> ";

		return html;
	}

	function update_image_node_row(data, idx) {
		$("#node-" + idx + "-row").html(build_image_node_row(data, idx));

		// Edit button
		$(".edit-image-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#node-data-" + fn_idx).val());
			launch_image_node_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-image-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_image_node(fn_idx);
		});
	}

	function add_image_node_row(data) {
		let idx = $("#end-of-node-table").attr("data-idx");

		let html = "<tr id='node-" + idx + "-row'> ";
		html += build_image_node_row(data, idx);
		html += "</tr> ";

		$(html).insertBefore("#end-of-node-table");
		++ idx;
		$("#end-of-node-table").attr("data-idx", idx);

		// Edit button
		$(".edit-image-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#node-data-" + fn_idx).val());
			launch_image_node_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-image-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_image_node(fn_idx);
		});
	}

	// Update rules back to the table
	$('#edit-image-node-form').validate({
		rules:{
			"in-edit-border-width" : {
				min : 0,
				max : 36,		// Half inch
			},
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

			let idx = $("#in-edit-idx").val();

			// Assemble Data
			let formData = new FormData(form);

			// Update changes to the table
			if ($("#in-is-edit-node").val() == "0")
				add_image_node_row(formData);
			else
				update_image_node_row(formData, idx);

			$("#edit-image-node-modal").modal('hide');
		},
	});

</script>

<!-- Text Node Modal -->
<script>
	// Fetch Text Node data and launch edit dialog
	function launch_text_node_modal(mode, idx, node = {}) {
		$("#tn-is-edit-node").val(mode == "edit" ? "1" : "0");
		$("#tn-edit-idx").val(idx);
		$("#tn-edit-font-family").val($("#edit-font-family-name").val());

		// Populate Lists
		// Type
		$("#tn-edit-type").html(get_options(text_node_types));
		// Name
		$("#tn-edit-name").html(get_options(text_nodes));

		// Block List
		let block_list = "";
		$(".block-row").each(function(index, elem) {
			block_list += "<option value='" + $(elem).attr("data-name") + "' >" + $(elem).attr("data-name") + "</option>";
		});
		$("#tn-edit-block").html(block_list);

		if (mode == "edit") {
			$("#tn-edit-type").val(node.type);
			$("#tn-edit-type option[value='" + node.type + "']").attr("selected", true);
			if (node.type == "field") {
				$("#tn-edit-name").val(node.name);
				$("#tn-edit-name option[value='" + node.name + "']").attr("selected", true);
				$("#tn-edit-name").removeAttr("disabled");
				$("#tn-edit-label").val("");
				$("#tn-edit-label").attr("disabled", true);
			}
			else {
				$("#tn-edit-name").val("");
				$("#tn-edit-name").attr("disabled", true);
				$("#tn-edit-label").val(node.value);
				$("#tn-edit-label").removeAttr("disabled");
			}
			if (node.template)
				$("#tn-edit-template").val(node.template);
			else
				$("#tn-edit-template").val("");
			$("#tn-edit-block").val(node.block);
			$("#tn-edit-block option[value='" + node.block + "']").attr("selected", true);
			$("#tn-edit-sequence").val(node.sequence);

			if (node.omit_if_empty == "yes")
				$("#tn-edit-omit-empty").attr("checked", true);
			else
				$("#tn-edit-omit-empty").removeAttr("checked");

			let font_style = (node.font_style && node.font_style != null) ? node.font_style : '';
			$("#tn-edit-font-style").val(font_style);
			$("#tn-edit-font-style option[value='" + font_style + "']").attr("selected", true);
			$("#tn-edit-font-size").val(node.font_size);
			$("#tn-edit-font-color").val(node.font_color == '' ? 'color_none' : node.font_color);
			$("#tn-edit-font-color option[value='" + (node.font_color == '' ? 'color_none' : node.font_color) + "']").attr("selected", true);
			$("#tn-edit-align").val(node.align);
			$("#tn-edit-align option[value='" + node.align + "']").attr("selected", true);
			let spacing = Number(node.line_spacing).toFixed(1);
			$("#tn-edit-line-spacing").val(spacing);
			$("#tn-edit-line-spacing option[value='" + spacing + "']").attr("selected", true);
		}
		else {
			$("#tn-edit-type").val("field");
			$("#tn-edit-type option[value='field']").attr("selected", true);
			$("#tn-edit-name").val("");
			$("#tn-edit-name").removeAttr("disabled");
			$("#tn-edit-label").val("");
			$("#tn-edit-label").attr("diabled", true);
			$("#tn-edit-template").val("");
			$("#tn-edit-block").val("");
			$("#tn-edit-sequence").val(0);
			$("#tn-edit-omit-empty").removeAttr("checked");
			$("#tn-edit-font-style").val("");
			$("#tn-edit-font-style option[value='']").attr("selected", true);
			$("#tn-edit-font-size").val(32);
			$("#tn-edit-font-color").val("color_field");
			$("#tn-edit-font-color option[value='color_field']").attr("selected", true);
			$("#tn-edit-align").val("L");
			$("#tn-edit-align option[value='L']").attr("selected", true);
			$("#tn-edit-line-spacing").val("1.0");
			$("#tn-edit-line-spacing option[value='1.0']").attr("selected", true);
		}

		if (mode == "edit")
			$("#tn-edit-update").val("Update");
		else
			$("#tn-edit-update").val("Add");

		// Install handler for field type change
		$("#tn-edit-type").on("change", function(){
			if ($(this).val() == "field") {
				$("#tn-edit-name").removeAttr("disabled");
				$("#tn-edit-label").val("");
				$("#tn-edit-label").attr("disabled", true);
			}
			else {
				$("#tn-edit-name").val("");
				$("#tn-edit-name").attr("disabled", true);
				$("#tn-edit-label").removeAttr("disabled");
			}
		});

		$("#edit-text-node-modal").modal('show');
	}

	function delete_text_node(idx) {
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the Text Node ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				$("#node-" + idx + "-row").remove();
			}
		});
	}

	// Image Node Modal Button Click Handling Functions
	$(document).ready(function(){
		// Add button
		$("#add-text-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $("#end-of-node-table").attr("data-idx");
			launch_text_node_modal("add", idx);
		});

		// Edit button
		$(".edit-text-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			let node = JSON.parse($("#node-data-" + idx).val());
			launch_text_node_modal("edit", idx, node);
		});

		// Delete Button
		$(".delete-text-node-btn").click(function(e) {
			e.preventDefault();
			let idx = $(this).attr("data-idx");
			delete_text_node(idx);
		});
	});


	// Functions to Update Block Table
	function new_label() {
		while (true) {
			let label_name = "label_" + (Math.random() * 1000).toFixed(0);
			let label_exists = false;
			$("[name='node_data[]']").each(function(){
				let node_data = JSON.parse($(this).val());
				if (node_data.name == label_name)
					label_exists = true;
			});
			if (! label_exists)
				return label_name;
		}
	}
	function build_text_node_row(data, idx) {

		// Assemble Data for the Row
		// let label_name = "label_" + (Math.random() * 1000).toFixed(0);
		let label_name = new_label();
		let node_data = {
				"name" : data.has("name") ? data.get("name") : label_name,
				"value" : (data.get("type") == 'field' ? '' : data.get("label")),
				"nodetype" : data.get("nodetype"),
				"type" : data.get("type"),
				"template" : data.get("template"),
				"block" : data.get("block"),
				"sequence" : data.get("sequence"),
				"omit_if_empty" : data.has("omit_if_empty") ? data.get("omit_if_empty") : "",
				"font_family" : data.get("font_family"),
				"font_style" : data.get("font_style"),
				"font_size" : data.get("font_size"),
				"font_color" : data.get("font_color"),
				"align" : data.get("align"),
				"line_spacing" : Number(data.get("line_spacing")).toFixed(1),
				"height" : data.get("height"),
				"group" : data.get("group"),
		};

		// Build the row to insert
		html  =	"<input type='hidden' name='node_data[]' id='node-data-" + idx + "' ";
		html += " value='" + JSON.stringify(node_data) + "' > ";
		html += "<td>" + data.get("block") + "</td> ";
		html += "<td>" + data.get("sequence") + "</td> ";
		html += "<td>" + (data.get('type') == 'field' ? get_label(text_nodes, data.get("name")) : label_name + "='" + data.get('label') + "'") + "</td> ";
		html += "<td>" + get_label(text_node_types, data.get('type')) + "</td> ";
		html += "<td>" + get_label(node_nodetypes, data.get('nodetype')) + "</td> ";
		html += "<td> ";
		html += "<button id='edit-node-" + idx + "' class='btn btn-info edit-text-node-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-edit'></i> Edit ";
		html += "</button> ";
		html += "</td> ";
		html += "<td> ";
		html += "<button id='delete-node-" + idx + "' class='btn btn-danger delete-text-node-btn' ";
		html += "data-idx='" + idx + "' > ";
		html += "<i class='fa fa-trash'></i> Delete ";
		html += "</button> ";
		html += "</td> ";

		return html;
	}

	function update_text_node_row(data, idx) {
		$("#node-" + idx + "-row").html(build_text_node_row(data, idx));

		// Edit button
		$(".edit-text-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#node-data-" + fn_idx).val());
			launch_text_node_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-text-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_text_node(fn_idx);
		});
	}

	function add_text_node_row(data) {
		let idx = $("#end-of-node-table").attr("data-idx");

		let html = "<tr id='node-" + idx + "-row'> ";
		html += build_text_node_row(data, idx);
		html += "</tr> ";

		$(html).insertBefore("#end-of-node-table");
		++ idx;
		$("#end-of-node-table").attr("data-idx", idx);

		// Edit button
		$(".edit-text-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			let fn_block = JSON.parse($("#node-data-" + fn_idx).val());
			launch_text_node_modal("edit", fn_idx, fn_block);
		});

		// Delete Button
		$(".delete-text-node-btn").click(function(e){
			e.preventDefault();
			let fn_idx = $(this).attr("data-idx");
			delete_text_node(fn_idx);
		});
	}

	// Update rules back to the table
	$('#edit-text-node-form').validate({
		rules:{
			"tn-edit-name" : {
				required : ($("tn-edit-type").val() == "field"),
			},
			"tn-edit-label" : {
				required : ($("tn-edit-type").val() == "label"),
			},
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

			let idx = $("#tn-edit-idx").val();

			// Assemble Data
			let formData = new FormData(form);

			// Update changes to the table
			if ($("#tn-is-edit-node").val() == "0")
				add_text_node_row(formData);
			else
				update_text_node_row(formData, idx);

			$("#edit-text-node-modal").modal('hide');
		},
	});

</script>

<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>
	function display_cert_design(input, level = 0) {
		if (input.files && input.files.length > 0 && input.files[0] != "") {
			// Phase 1 Check Size & Dimensions
			var file_size = input.files[0].size;
			var reader = new FileReader();
			// Handler for file read completion
			reader.onload = function (e) {
				if (level == 0) {
					$("#disp-design").attr("src", e.target.result);
					$("#upload-design").removeAttr("disabled");
				}
				else {
					$("#disp-design-" + level).attr("src", e.target.result);
					$("#upload-design-" + level).removeAttr("disabled");
				}
			}
			// Handler for File Read Error
			reader.onerror = function(e) {
				if (level == 0)
					$("#error-design").html("Unable to open selected picture");
				else
					$("#error-design-" + level).html("Unable to open selected picture");
			}
			// Perform File Read
			reader.readAsDataURL(input.files[0]);
		}
	}

	function display_border_img(input, level = 0) {
		if (input.files && input.files.length > 0 && input.files[0] != "") {
			// Phase 1 Check Size & Dimensions
			var file_size = input.files[0].size;
			var reader = new FileReader();
			// Handler for file read completion
			reader.onload = function (e) {
				if (level == 0) {
					$("#disp-border-img").attr("src", e.target.result);
					$("#upload-border-img").removeAttr("disabled");
				}
				else {
					$("#disp-border-img-" + level).attr("src", e.target.result);
					$("#upload-border-img-" + level).removeAttr("disabled");
				}
			}
			// Handler for File Read Error
			reader.onerror = function(e) {
				if (level == 0)
					$("#error-border-img").html("Unable to open selected picture");
				else
					$("#error-border-img-" + level).html("Unable to open selected picture");
			}
			// Perform File Read
			reader.readAsDataURL(input.files[0]);

		}
	}

	function display_signature(input, signature) {
		if (input.files && input.files.length > 0 && input.files[0] != "") {
			// Phase 1 Check Size & Dimensions
			var file_size = input.files[0].size;
			var reader = new FileReader();
			// Handler for file read completion
			reader.onload = function (e) {
				$("#disp-" + signature).attr("src", e.target.result);
				$("#upload-" + signature).removeAttr("disabled");
			}
			// Handler for File Read Error
			reader.onerror = function(e) {
				$("#error-" + signature).html("Unable to open selected picture");
			}
			// Perform File Read
			reader.readAsDataURL(input.files[0]);
		}
	}

	function upload_cert_design(btn) {
		let level = $(btn).attr("data-level") ? $(btn).attr("data-level") : 0;
		let input = "#edit-design";
		let display = "#disp-design";
		let update = "#update-design";
		if (level != 0) {
			input = "#edit-design-" + level;
			display = "#disp-design-" + level;
			update = "#update-design-" + level;
		}
		if ($(input).val() == "") {
			swal("Select a Design Image !", "Please select a Certificate design image to upload.", "warning");
		}
		else {
			let yearmonth = "<?= $yearmonth;?>";
			let file_name = $(btn).attr("data-file");
			let file = $(input)[0].files[0];
			let stub = $(input).attr("name");
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
							$(display).attr("src", "/salons/" + yearmonth + "/img/" + file_name);
							$(update).val(file_name);
							swal({
									title: "Image Saved",
									text: "Certificate Design has been uploaded and saved.",
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
	}

	function upload_border_img(btn) {
		let level = $(btn).attr("data-level") ? $(btn).attr("data-level") : 0;
		let input = "#edit-border-img";
		let display = "#disp-border-img";
		let update = "#update-border-img";
		if (level != 0) {
			input = "#edit-border-img-" + level;
			display = "#disp-border-img-" + level;
			update = "#update-border-img-" + level;
		}
		if ($(input).val() == "") {
			swal("Select a Frame Image !", "Please select a Frame image to upload.", "warning");
		}
		else {
			let yearmonth = "<?= $yearmonth;?>";
			let file_name = $(btn).attr("data-file");
			let file = $(input)[0].files[0];
			let stub = $(input).attr("name");
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
							if (level == 0)
								$("#disp-border-img").attr("src", "/salons/" + yearmonth + "/img/" + file_name);
							else
								$("#disp-border-img-" + level).attr("src", "/salons/" + yearmonth + "/img/" + file_name);
							swal({
									title: "Image Saved",
									text: "Frame Design has been uploaded and saved.",
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
	}

	function upload_signature(btn, signature) {
		let input = "#edit-" + signature;
		let display = "#disp-" + signature;
		let update = "#update-" + signature;
		if ($(input).val() == "") {
			swal("Select a Design Image !", "Please select a Certificate design image to upload.", "warning");
		}
		else {
			let yearmonth = "<?= $yearmonth;?>";
			let file_name = $(btn).attr("data-file");
			let file = $(input)[0].files[0];
			let stub = $(input).attr("name");
			let formData = new FormData();
			formData.append("yearmonth", yearmonth);
			formData.append("file_name", file_name);
			formData.append("sub_folder", "com");
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
							$(display).attr("src", "/salons/" + yearmonth + "/img/" + file_name);
							$(update).val(file_name);
							swal({
									title: "Image Saved",
									text: "Signature has been uploaded and saved.",
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
	}

	// Handle Image Uploads
	$(document).ready(function(){

		// Certificate Design
		// Load picture into view
		$("#edit-design").on("change", function(){
			let input = $(this).get(0);
			display_cert_design(input);
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$("#upload-design").click(function(e){
			e.preventDefault();
			upload_cert_design(this);
		});

		// Picture Frame General
		// Load picture into view
		$("#edit-border-img").on("change", function(){
			let input = $(this).get(0);
			display_border_img(input);
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$("#upload-border-img").click(function(e){
			e.preventDefault();
			upload_border_img(this);
		});

		// Levels based designs
		// Load picture into view
		$(".edit_level_cert_design").on("change", function(){
			let level = $(this).attr("data-level");
			let input = $(this).get(0);
			display_cert_design(input, level);
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$(".upload_level_cert_design").click(function(e){
			e.preventDefault();
			upload_cert_design(this);
		});

		// Levels based border image
		// Load picture into view
		$(".edit_level_border_img").on("change", function(){
			let level = $(this).attr("data-level");
			let input = $(this).get(0);
			display_cert_design(input, level);
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$(".upload_level_border_img").click(function(e){
			e.preventDefault();
			upload_cert_design(this);
		});

		// Chairman Signature
		// Load picture into view
		$("#edit-chairman-signature").on("change", function(){
			let input = $(this).get(0);
			display_signature(input, "chairman-signature");
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$("#upload-chairman-signature").click(function(e){
			e.preventDefault();
			upload_signature(this, "chairman-signature");
		});

		// Secretary Signature
		// Load picture into view
		$("#edit-secretary-signature").on("change", function(){
			let input = $(this).get(0);
			display_signature(input, "secretary-signature");
		});

		// Ajax Upload of Ribbon Holder Design PNG
		$("#upload-secretary-signature").click(function(e){
			e.preventDefault();
			upload_signature(this, "secretary-signature");
		});
	});
</script>

<script>
	// Save certdef.json
	// Save JSON
	$('#edit-certificate-form').validate({
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
			if (! $("#disp-design").attr("src").endsWith("cert.png")) {
				swal("No Design Image !", "Please select a Certificte design image and upload.", "warning");
				return false;
			}

			// Check if blocks are defined
			if ($("[name='block_data[]']").length == 0) {
				swal("Blocks Not Defined !", "There are no Block definitions for the certificate", "warning");
				return false;
			}

			// Check if nodes are defined
			if ($("[name='node_data[]']").length == 0) {
				swal("Blocks Not Defined !", "There are no Block definitions for the certificate", "warning");
				return false;
			}

			// Assemble Data
			let formData = new FormData(form);

			$('#loader_img').show();
			$.ajax({
					url: "ajax/save_certificate_json.php",
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
									title: "Certificate Definition Saved",
									text: "The Certificate definition has been saved. Run Certificate generation from Admin system.",
									icon: "success",
									confirmButtonClass: 'btn-success',
									confirmButtonText: 'Great'
							});
						}
						else{
							swal({
									title: "Save Failed",
									text: "Certificate Definition could not be saved: " + response.msg,
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

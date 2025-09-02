<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

if( isset($_SESSION['admin_id']) && isset($_REQUEST['save-json']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

	$target_folder = "../../salons/$yearmonth/blob";
	$json_file = $target_folder . "/" . "customs_declaration.json";

	// Assemble Picture Field Definitions
	if (isset($_REQUEST['picture_data']) && sizeof($_REQUEST['picture_data']) > 0) {
		$picture = [];
		foreach($_REQUEST['picture_data'] as $data_json) {
			$picture[] = json_decode($data_json, true);
		}
	}
	else {
		return_error("Section Opening Field Definitions Empty", __FILE__, __LINE__, true);
	}

	$ribbon_holder = array(
		"yearmonth" => $_REQUEST['yearmonth'],
		"is_international" => $_REQUEST['is_international'],
		"design" => $_REQUEST['design'],
		"width" => $_REQUEST['width'],
		"height" => $_REQUEST['height'],
		"file_name_stub" => $_REQUEST['stub'],
		"from_name" => $_REQUEST['from_name'],
		"from_street" => $_REQUEST['from_street'],
		"from_phone" => $_REQUEST['from_phone'],
		"from_city" => $_REQUEST['from_city'],
		"from_pin" => $_REQUEST['from_pin'],
		"color_black" => strtoupper(substr($_REQUEST['color_black'], 1)),
		"color_white" => strtoupper(substr($_REQUEST['color_white'], 1)),
		"color_gold" => strtoupper(substr($_REQUEST['color_gold'], 1)),
		"color_subdued" => strtoupper(substr($_REQUEST['color_subdued'], 1)),
		"color_highlight" => strtoupper(substr($_REQUEST['color_highlight'], 1)),
		"color_custom_1" => strtoupper(substr($_REQUEST['color_custom_1'], 1)),
		"color_custom_2" => strtoupper(substr($_REQUEST['color_custom_2'], 1)),
		"color_custom_3" => strtoupper(substr($_REQUEST['color_custom_3'], 1)),
		"color_custom_4" => strtoupper(substr($_REQUEST['color_custom_4'], 1)),
		"color_none" => $_REQUEST['color_none'],
		"font_regular" => $_REQUEST['font_regular'],
		"font_bold" => $_REQUEST['font_bold'],
		"font_italic" => $_REQUEST['font_italic'],
		"picture" => $picture
	);

	// Save JSON File
	if (file_exists($json_file)) {
		$info = pathinfo($json_file);
		rename($json_file, $target_folder . "/" . $info['filename'] . "_" . date("YmdHi") . "." . $info['extension']);
	}
	file_put_contents($json_file, json_encode($ribbon_holder));

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['slideshow'] = $ribbon_holder;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

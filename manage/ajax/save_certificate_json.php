<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

if( isset($_SESSION['admin_id']) && isset($_REQUEST['save-json']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

	$target_folder = "../../salons/$yearmonth/blob";
	$json_file = $target_folder . "/" . "certdef.json";

	// Assemble Block Definitions
	if (isset($_REQUEST['block_data']) && sizeof($_REQUEST['block_data']) > 0) {
		$blocks = [];
		foreach($_REQUEST['block_data'] as $data_json) {
			$blocks[] = json_decode($data_json, true);
		}
	}
	else {
		return_error("Unable to find definitions of Blocks", __FILE__, __LINE__, true);
	}

	// Assemble Node Definitions
	if (isset($_REQUEST['node_data']) && sizeof($_REQUEST['node_data']) > 0) {
		$nodes = [];
		foreach($_REQUEST['node_data'] as $data_json) {
			$nodes[] = json_decode($data_json, true);
		}
	}
	else {
		return_error("Unable to find definitions of Nodes", __FILE__, __LINE__, true);
	}

	// Add Level based design and border images
	$levels = [];
	if (! isset($_REQUEST['same_design_for_all_levels'])){
		$sql = "SELECT DISTINCT level FROM award WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			$level = $row['level'];
			$levels[] = array("level" => $level,
								"design" => isset($_REQUEST["level_design[$level]"]) ? $_REQUEST["level_design[$level]"] : "",
							 	"border_img" => isset($_REQUEST["level_border_img[$level]"]) ? $_REQUEST["level_border_img[$level]"] : ""
							);
		}
	}


	$cert = array(
		"doc" => [
				"page" => [
					"unit" => $_REQUEST['unit'],
					"page_width" => $_REQUEST['page_width'],
					"page_height" => $_REQUEST['page_height'],
					"page_orientation" => $_REQUEST['page_orientation'],
					"cutting_bleed" => $_REQUEST['cutting_bleed']
					],
				"files" => [
					"file_name_stub" => $_REQUEST['file_name_stub'],
					"design" => $_REQUEST['design'],
					"border_img" => $_REQUEST['border_img'],
					"same_design_for_all_levels" => isset($_REQUEST['same_design_for_all_levels']) ? 1 : 0,
					"levels" => $levels,
					"chairman_sig" => $_REQUEST['chairman_sig'],
					"secretary_sig" => $_REQUEST['secretary_sig']
					],
				"font" => [
					"font_family" => $_REQUEST['font_family'],
					"font_regular" => $_REQUEST['font_regular'],
					"font_bold" => $_REQUEST['font_bold'],
					"font_italic" => $_REQUEST['font_italic'],
					],
				"colors" => [
					"color_label" => str_replace("#", "0x", strtoupper($_REQUEST["color_label"])),
					"color_field" => str_replace("#", "0x", strtoupper($_REQUEST['color_field'])),
					"color_subdued" => str_replace("#", "0x", strtoupper($_REQUEST["color_subdued"])),
					"color_highlight" => str_replace("#", "0x", strtoupper($_REQUEST["color_highlight"])),
					"color_black" => str_replace("#", "0x", strtoupper($_REQUEST["color_black"])),
					"color_white" => str_replace("#", "0x", strtoupper($_REQUEST["color_white"])),
					"color_gray" => str_replace("#", "0x", strtoupper($_REQUEST["color_gray"])),
					"color_gold" => str_replace("#", "0x", strtoupper($_REQUEST["color_gold"])),
					"color_red" => str_replace("#", "0x", strtoupper($_REQUEST["color_red"])),
					"color_blue" => str_replace("#", "0x", strtoupper($_REQUEST["color_blue"])),
					"color_green" => str_replace("#", "0x", strtoupper($_REQUEST["color_green"])),
					"color_yellow" => str_replace("#", "0x", strtoupper($_REQUEST["color_yellow"])),
					"color_custom_1" => str_replace("#", "0x", strtoupper($_REQUEST["color_custom_1"])),
					"color_custom_2" => str_replace("#", "0x", strtoupper($_REQUEST["color_custom_2"])),
					"color_custom_3" => str_replace("#", "0x", strtoupper($_REQUEST["color_custom_3"])),
					"color_custom_4" => str_replace("#", "0x", strtoupper($_REQUEST["color_custom_4"])),
					"color_none" => ""
					]
			],
		"blocks" => $blocks,
		"nodes" => $nodes
	);

	// Save JSON File
	if (file_exists($json_file)) {
		$info = pathinfo($json_file);
		rename($json_file, $target_folder . "/" . $info['filename'] . "_" . date("YmdHi") . "." . $info['extension']);
	}
	file_put_contents($json_file, json_encode($cert));

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['cert'] = $cert;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>

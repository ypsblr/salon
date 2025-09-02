<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2018
*/
class Certificate {
	// Constants
	// Text Colors
	const COLOR_LABEL = 0xB09400;
	const COLOR_FIELD = 0x0;
	const COLOR_SUBDUED = 0x808080;
	const COLOR_HIGHLIGHT = 0xF02020;

	// Dimensions
	const UNIT = "mm";
	const WIDTH = 297;		// mm
	const HEIGHT = 210;		// mm
	const ORIENTATION = (self::WIDTH >= self::HEIGHT) ? "L" : "P";

	// Fonts
	const FONT_FAMILY = "Petita";
	const FONT_REGULAR = "PetitaMedium.ttf";
	const FONT_BOLD = "PetitaBold.ttf";
	const FONT_ITALIC = "PetitaLight.ttf";

	// const FONT_FAMILY = "DejaVu Sans";
	// const FONT_REGULAR = "DejaVuSans.ttf";
	// const FONT_BOLD = "DejaVuSans-Bold.ttf";
	// const FONT_ITALIC = "DejaVuSans-Oblique.ttf";


	// Other Constants
	const FILE_NAME_STUB = "YPS-ALL-INDIA-SALON-2018";

	// Protected
	protected $templates = array ("1" => "YPS_AIS_2018_CERTIFICATE.jpg", "2" => "YPS_AIS_2018_CERTIFICATE.jpg", "3" => "YPS_AIS_2018_CERTIFICATE.jpg",
									"4" => "YPS_AIS_2018_CERTIFICATE.jpg", "5" => "YPS_AIS_2018_CERTIFICATE.jpg", "9" => "YPS_AIS_2018_CERTIFICATE.jpg",
									"99" => "YPS_AIS_2018_CERTIFICATE.jpg");
	protected $templates_cm = array ("1" => "YPS_AIS_2018_CERTIFICATE.jpg", "2" => "YPS_AIS_2018_CERTIFICATE.jpg", "3" => "YPS_AIS_2018_CERTIFICATE.jpg",
									"4" => "YPS_AIS_2018_CERTIFICATE.jpg", "5" => "YPS_AIS_2018_CERTIFICATE.jpg", "9" => "YPS_AIS_2018_CERTIFICATE.jpg",
									"99" => "YPS_AIS_2018_CERTIFICATE.jpg");

	protected $blocks = array (
							"thumbnail_block" => array("type" => "tile", "x" => 200, "y" => 70, "width" => 80, "height" => 80, "fill_color" => "", "border_width" => "0", "border_color" => "", ),
							"award_block" => array("type" => "list", "x" => 11, "y" => 72, "width" => 180, "height" => 96, "fill_color" => "", "border_width" => "0", "border_color" => "", )
							);

	protected $nodes = array (
						// Text Nodes
						// For Award Block
						// "label_1" => array("nodetype" => "text", "type" => "label", "value" => "Hearty Congratulations",
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 18, "font_color" => self::COLOR_LABEL,
						// 			    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 1),

						"author_name" => array("nodetype" => "text", "type" => "field",
										"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 32, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 2),

						"honors" => array("nodetype" => "text", "type" => "field",
										"font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 10, "font_color" => self::COLOR_SUBDUED,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 2),

						// "honors" => array("nodetype" => "text", "type" => "field",
						// 				  "font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 10, "font_color" => self::COLOR_SUBDUED,
						// 			      "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 3),

						"blank_line_1" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 20, "group" => "award", "block" => "award_block", "sequence" => 4),

						// "label_2" => array("nodetype" => "text", "type" => "label", "value" => "for winning",
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 14, "font_color" => self::COLOR_LABEL,
						// 			    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 5),

						"award_name" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 24, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 6),

						"blank_line_2" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 22, "group" => "award", "block" => "award_block", "sequence" => 7),

						// "label_3" => array("nodetype" => "text", "type" => "label", "value" => "for the picture",
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 14, "font_color" => self::COLOR_LABEL,
						// 			    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 8),

						"title" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 24, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 9),

						// For Sponsor Block
						// "sponsor_logo" => array("nodetype" => "image", "type" => "field",
						// 					   "align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_SUBDUED,
						// 					   "float" => "right", "spacing" => 2, "block" => "sponsor_logo_block", "optional" => "yes", "sequence" => 1),
						//
						// "label_4" => array("nodetype" => "text", "type" => "label", "value" => "Award Sponsor",
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_FIELD,
						// 			    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 2),
						//
						// "custom_award_name" => array("nodetype" => "text", "type" => "field",
						// 				  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 10, "font_color" => self::COLOR_LABEL,
						// 			    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 3),
						//
						// "sponsor_name" => array("nodetype" => "text", "type" => "field",
						// 				  "font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_FIELD,
						// 			    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 4),
						//
						// "sponsor_website" => array("nodetype" => "text", "type" => "field",
						// 				  "font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LABEL,
						// 			    "align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 5),

						// Image Nodes
						"picfile" => array("nodetype" => "image", "type" => "field",
											 "align" => "center", "bordertype" => "border", "borderwidth" => 0.25, "bordercolor" => self::COLOR_SUBDUED,
											 "float" => "fill", "spacing" => 0, "block" => "thumbnail_block", "optional" => "no")

						);


	// Methods
	function getTemplate($level, $cutting_marks = false) {
		if ($cutting_marks) {
			if (! empty($this->templates_cm[$level]))
				return $this->templates_cm[$level];
			else
				return false;
		}
		else {
			if (! empty($this->templates[$level]))
				return $this->templates[$level];
			else
				return false;
		}
	}

	function getNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["value"]))
			return $this->nodes[$node_name]["value"];
		else
			return false;
	}

	function setNodeValue($node_name, $value) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field") {
			$this->nodes[$node_name]["value"] = $value;
			return true;
		}
		else
			return false;
	}

	function deleteNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field" &&
		   										isset($this->nodes[$node_name]["value"]) ) {
			unset($this->nodes[$node_name]["value"]);
			return true;
		}
		else
			return false;

	}

	function getBlock($block_name) {
		if (isset($this->blocks[$block_name]))
			return (object) $this->blocks[$block_name];
		else
			return false;
	}

	function getListOfBlocks() {
		$block_list = [];
		foreach($this->blocks as $block_name => $block) {
			$block_list[$block_name] = $block;
		}
		return $block_list;
	}

	function isBlockPrintable($block_name, $data = []) {
		return true;
	}

	function getNode($node_name) {
		if (isset($this->nodes[$node_name]))
			return $this->nodes[$node_name];
		else
			return false;
	}

	function getNodesForBlock($block_name) {
		if (isset($this->blocks[$block_name])) {
			$node_list = [];
			foreach ($this->nodes as $nodename => $node) {
				if (isset($node['block']) && $node['block'] == $block_name)
					$node_list[$nodename] = $node;
			}
			return $node_list;
		}
		else
			return false;
	}
}

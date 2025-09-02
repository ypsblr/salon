<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2020
*/
class Certificate {
	// Constants
	// Text Colors
	const COLOR_LABEL = 0x927C4D;
	const COLOR_FIELD = 0xA93C3A;
	const COLOR_SUBDUED = 0x808080;
	const COLOR_HIGHLIGHT = 0xF02020;

	// Dimensions
	const UNIT = "pt";
	const WIDTH = 9 * 72;	// 9 inches
	const HEIGHT = 6 * 72;	// 6 inches
	const ORIENTATION = (self::WIDTH >= self::HEIGHT) ? "L" : "P";

	// Fonts
	const FONT_FAMILY = "Gotham Condensed";
	const FONT_REGULAR = "GothamCond-Book.ttf";
	const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Other Constants
	const FILE_NAME_STUB = "YPS-ALL-INDIA-SALON-2020";

	// Protected
	protected $templates = array ("1" => "AIS_2020_CERT_FIP_GOLD.jpg", "2" => "AIS_2020_CERT_YPS_GOLD.jpg", "3" => "AIS_2020_CERT_YPS_SILVER.jpg",
									"4" => "AIS_2020_CERT_YPS_BRONZE.jpg", "5" => "AIS_2020_CERT_YPS_YOUTH.jpg", "9" => "AIS_2020_CERT_FIP_HM.jpg",
									"99" => "AIS_2020_CERT_ACCEPTANCE.jpg");
	protected $templates_cm = array ("1" => "AIS_2020_CERT_FIP_GOLD_CM.jpg", "2" => "AIS_2020_CERT_YPS_GOLD_CM.jpg", "3" => "AIS_2020_CERT_YPS_SILVER_CM.jpg",
									"4" => "AIS_2020_CERT_YPS_BRONZE_CM.jpg", "5" => "AIS_2020_CERT_YPS_YOUTH_CM.jpg", "9" => "AIS_2020_CERT_FIP_HM_CM.jpg",
									"99" => "AIS_2020_CERT_ACCEPTANCE_CM.jpg");

	protected $blocks = array (
							"thumbnail_block" => array("type" => "tile", "x" => 55, "y" => 190, "width" => 150, "height" => 150, "fill_color" => "", "border_width" => "0", "border_color" => "", ),
							"award_block" => array("type" => "list", "x" => 255, "y" => 215, "width" => 345, "height" => 125, "fill_color" => "", "border_width" => "0", "border_color" => "", ),
							"sponsor_logo_block" => array("type" => "tile", "x" => 140, "y" => 373, "width" => 80, "height" => 43, "fill_color" => "", "border_width" => "0", "border_color" => ""),
							"sponsor_block" => array("type" => "list", "x" => 220, "y" => 373, "width" => 140, "height" => 43, "fill_color" => "", "border_width" => "0.5", "border_color" => self::COLOR_SUBDUED)
							);

	protected $nodes = array (
						// Text Nodes
						// For Award Block
						"author_name" => array("nodetype" => "text", "type" => "field",
										"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 18, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 1),

						"honors" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_SUBDUED,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 2),

						"blank_line_1" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 3),

						"label_1" => array("nodetype" => "text", "type" => "label", "value" => "for winning",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 4),

						"award_name" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 5),

						"blank_line_2" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 6),

						"label_2" => array("nodetype" => "text", "type" => "label", "value" => "for the image",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 7),

						"title" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 8),

						// For Sponsor Block
						"sponsor_logo" => array("nodetype" => "image", "type" => "field",
											   "align" => "left", "bordertype" => "border", "borderwidth" => 0.5, "bordercolor" => self::COLOR_SUBDUED,
											   "float" => "right", "spacing" => 2, "block" => "sponsor_logo_block", "optional" => "yes", "sequence" => 1),

						"label_3" => array("nodetype" => "text", "type" => "label", "value" => "Award Sponsor",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_FIELD,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 2),

						"custom_award_name" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 10, "font_color" => self::COLOR_LABEL,
									    "align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 3),

						"sponsor_name" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 4),

						"sponsor_website" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LABEL,
									    "align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", "block" => "sponsor_block", "sequence" => 5),

						// Image Nodes
						"picfile" => array("nodetype" => "image", "type" => "field",
											 "align" => "center", "bordertype" => "frame", "borderwidth" => 4, "float" => "fill", "spacing" => 0,
											 "borderimage" => "AIS_2020_FRAME_LB.png", "block" => "thumbnail_block", "optional" => "no", "sequence" => 1)

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

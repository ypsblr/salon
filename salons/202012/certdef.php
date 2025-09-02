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
	const COLOR_LABEL = 0xD54F27;
	const COLOR_FIELD = 0x353535;
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
	const FILE_NAME_STUB = "YPS-INTLSALON2020";

	// Protected
	protected $templates = array ("1" => "IS2020_CERT.jpg", "2" => "IS2020_CERT.jpg", "3" => "IS2020_CERT.jpg",
									"4" => "IS2020_CERT.jpg", "5" => "IS2020_CERT.jpg", "9" => "IS2020_CERT.jpg",
									"99" => "IS2020_CERT.jpg");
	protected $templates_cm = array ("1" => "IS2020_CERT_PRINT.jpg", "2" => "IS2020_CERT_PRINT.jpg", "3" => "IS2020_CERT_PRINT.jpg",
									"4" => "IS2020_CERT_PRINT.jpg", "5" => "IS2020_CERT_PRINT.jpg", "9" => "IS2020_CERT_PRINT.jpg",
									"99" => "IS2020_CERT_PRINT.jpg");

	protected $blocks = array (
							"thumbnail_block" => array("type" => "tile", "x" => 190, "y" => 190, "width" => 165, "height" => 165, "fill_color" => "", "border_width" => "0", "boder_color" => "", ),
							"award_block" => array("type" => "list", "x" => 400, "y" => 190, "width" => 220, "height" => 135, "fill_color" => "", "border_width" => "0", "boder_color" => "", )
							);

	protected $nodes = array (
						// Text Nodes
						// For Award Block
						"congratulations" => array("nodetype" => "text", "type" => "label", "value" => "Hearty Congratulations",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 18, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 1),

						"trim_1" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/IS2020_CERT_TRIM.png",
											 "align" => "L", "bordertype" => "none", "borderwidth" => 0, "float" => "left", "spacing" => 0,
											 "borderimage" => "", "block" => "award_block", "optional" => "no", "sequence" => 2),

						"author_name" => array("nodetype" => "text", "type" => "field",
										"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 18, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 3),

						"honors" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "I", "font_size" => 8, "font_color" => self::COLOR_SUBDUED,
									      "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "name", "block" => "award_block", "sequence" => 4),

						"trim_2" => array("nodetype" => "image", "type" => "label", "value" => __DIR__ . "/img/IS2020_CERT_TRIM.png",
											 "align" => "L", "bordertype" => "none", "borderwidth" => 0, "float" => "left", "spacing" => 0,
											 "borderimage" => "", "block" => "award_block", "optional" => "no", "sequence" => 5),

						"blank_line_1" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 6),

						"label_1" => array("nodetype" => "text", "type" => "label", "value" => "for winning",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 7),

						"award_name" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 8),

						"blank_line_2" => array("nodetype" => "text", "type" => "label", "value" => "",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0, "group" => "award", "block" => "award_block", "sequence" => 9),

						"label_2" => array("nodetype" => "text", "type" => "label", "value" => "for the image",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 10),

						"title" => array("nodetype" => "text", "type" => "field",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_FIELD,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "title", "block" => "award_block", "sequence" => 11),

						// Image Nodes
						"picfile" => array("nodetype" => "image", "type" => "field",
											 "align" => "RT", "bordertype" => "none", "borderwidth" => 0, "float" => "right", "spacing" => 0,
											 "borderimage" => "", "block" => "thumbnail_block", "optional" => "no")

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

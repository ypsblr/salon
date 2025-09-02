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

	const COLOR_LOGO_RED = 0xFF0000;
	// const COLOR_LOGO_GRAY = 0x96999C;
	// const COLOR_LOGO_BLUE = 0x4285F4;
	// const COLOR_LOGO_GREEN = 0x34A853;
	// const COLOR_LOGO_YELLOW = 0xFEBC11;
	// const COLOR_LOGO_RED = 0xEE3537;
	const COLOR_LOGO_GRAY = 0x96999C;
	const COLOR_LOGO_BLUE = 0x5D82C1;
	const COLOR_LOGO_GREEN = 0x44AC5B;
	const COLOR_LOGO_YELLOW = 0xFDBE30;
	const COLOR_GOLD = 0xA48438;
	const COLOR_BLACK = 0x404040;
	// const COLOR_THEME = 0x0098DA;
	const COLOR_THEME = 0x4285F4;

	// Dimensions
	const UNIT = "pt";
	// const WIDTH = 595;	// A4
	// const HEIGHT = 842;	// A4
	const WIDTH = 599;	// A4
	const HEIGHT = 871;	// A4
	const ORIENTATION = (self::WIDTH >= self::HEIGHT) ? "L" : "P";

	// Fonts
	const FONT_FAMILY = "Gotham Condensed";
	const FONT_REGULAR = "GothamCond-Light.ttf";
	const FONT_BOLD = "GothamCond-Book.ttf";
	// const FONT_BOLD = "GothamCond-Medium.ttf";
	const FONT_ITALIC = "GothamCond-LightItalic.ttf";

	// Other Constants
	const FILE_NAME_STUB = "YPS-INTL-SALON-2021";

	const LOG_ERROR = "no";

	// Protected
	// protected $templates = array ("1" => "IS_2020_CERT.jpg", "2" => "IS_2020_CERT.jpg", "3" => "IS_2020_CERT.jpg",
	// 								"4" => "IS_2020_CERT.jpg", "5" => "IS_2020_CERT.jpg", "9" => "IS_2020_CERT.jpg",
	// 								"99" => "IS_2020_CERT.jpg");
	// protected $templates_cm = array ("1" => "AIS_2020_CERT_FIP_GOLD_CM.jpg", "2" => "AIS_2020_CERT_YPS_GOLD_CM.jpg", "3" => "AIS_2020_CERT_YPS_SILVER_CM.jpg",
	// 								"4" => "AIS_2020_CERT_YPS_BRONZE_CM.jpg", "5" => "AIS_2020_CERT_YPS_YOUTH_CM.jpg", "9" => "AIS_2020_CERT_FIP_HM_CM.jpg",
	// 								"99" => "AIS_2020_CERT_ACCEPTANCE_CM.jpg");
	protected $template_award = "is2021_cert.png";
	protected $template_acceptance = "is2021_cert.png";
	protected $template_cm = "is2021_cert.png";

	protected $blocks = array (
							// Picture & Title
							"pic_block" => array("type" => "list", "x" => 322, "y" => 480, "width" => 230, "height" => 154, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							// "section_block" => array("type" => "list", "x" => 322, "y" => 640, "width" => 230, "height" => 20, "orientation" => "N",
							// 							"fill_color" => "", "border_width" => "0", "border_color" => "",
							// 							"print" => "no" ),
							// Avatar, Author Name, Award, Section
							"avatar_block" => array("type" => "tile", "x" => 37, "y" => 480, "width" => 48, "height" => 48, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							"award_block" => array("type" => "list", "x" => 100, "y" => 480, "width" => 192, "height" => 150, "orientation" => "N",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),

							// Jury List
							"jury_block" => array("type" => "list", "x" => 246, "y" => 144, "width" => 312, "height" => 80, "orientation" => "F",
														"fill_color" => "", "border_width" => "0", "border_color" => "",
														"print" => "no" ),
							);

	protected $nodes = array (
						// Picture Node
						"picfile" => array("nodetype" => "image", "type" => "field", "block" => "pic_block", "sequence" => 1,
											 "float" => "fill", "bordertype" => "none", "borderwidth" => 4, "spacing" => 0,
											 "borderimage" => "" ),
						// Picture Title
						// "title" => array("nodetype" => "text", "type" => "field", "block" => "pic_title_block", "sequence" => 2,
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_LOGO_GRAY,
						// 			    	"align" => "C", "line_spacing" => 1.2, "height" => 0 ),
						// Section
						// "award_section" => array("nodetype" => "text", "type" => "field", "template" => "[value] SECTION", "block" => "section_block", "sequence" => 1,
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_THEME,
						// 			    	"align" => "C", "line_spacing" => 1.2, "height" => 0, "group" => "title", ),
						// Avatar
						"avatar" => array("nodetype" => "image", "type" => "field", "block" => "avatar_block", "sequence" => 1,
											 "float" => "fill", "bordertype" => "none", "borderwidth" => 4, "spacing" => 0,
											 "borderimage" => "" ),

						// For Award Block
						"author_name" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 1, "function" => "format_name",
										"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 18, "font_color" => self::COLOR_THEME,
									    "align" => "L", "line_spacing" => 1.0, "height" => 0 ),

						"honors" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 2, "omit_if_empty" => "yes",
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 10, "font_color" => self::COLOR_LOGO_GRAY,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0 ),

						"award_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "award_block", "sequence" => 3,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 6, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0 ),

						"award_label_1" => array("nodetype" => "text", "type" => "label", "value" => "on winning", "block" => "award_block", "sequence" => 4,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "award" ),

						"award_name_alone" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 5,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_THEME,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award" ),

						// "award_section" => array("nodetype" => "text", "type" => "field", "template" => "in [value] section", "block" => "award_block", "sequence" => 6,
						// 				  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_THEME,
						// 			    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", ),

						"award_blank_2" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "award_block", "sequence" => 7,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LABEL,
									    	"align" => "L", "line_spacing" => 1, "height" => 0 ),

						"award_label_2" => array("nodetype" => "text", "type" => "label", "value" => "for the picture", "block" => "award_block", "sequence" => 8,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "award" ),

						"title" => array("nodetype" => "text", "type" => "field", "block" => "award_block", "sequence" => 9, "function" => "format_title",
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_THEME,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "award" ),

						"award_label_3" => array("nodetype" => "text", "type" => "label", "value" => "under", "block" => "award_block", "sequence" => 10,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 12, "font_color" => self::COLOR_LOGO_GRAY,
									    	"align" => "L", "line_spacing" => 1.1, "height" => 0, "group" => "award" ),

						"award_section" => array("nodetype" => "text", "type" => "field", "template" => "[value] section", "block" => "award_block", "sequence" => 11,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_THEME,
									    	"align" => "L", "line_spacing" => 1.2, "height" => 0, "group" => "title", ),


						// Jury Block
						"jury_section" => array("nodetype" => "text", "type" => "field", "template" => "JURY - [value]", "block" => "jury_block", "sequence" => 1,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 14, "font_color" => self::COLOR_THEME,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0 ),

						"jury_blank_1" => array("nodetype" => "text", "type" => "label", "value" => "", "block" => "jury_block", "sequence" => 2,
										  	"font_family" => self::FONT_FAMILY, "font_style" => "", "font_size" => 8, "font_color" => self::COLOR_LOGO_RED,
									    	"align" => "L", "line_spacing" => 1.0, "height" => 0, "group" => "", ),

						"jury_name_1" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 3,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0 ),

						"jury_name_2" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 4,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0 ),

						"jury_name_3" => array("nodetype" => "text", "type" => "field", "block" => "jury_block", "sequence" => 5,
										  "font_family" => self::FONT_FAMILY, "font_style" => "B", "font_size" => 12, "font_color" => self::COLOR_BLACK,
									    "align" => "L", "line_spacing" => 1.1, "height" => 0 ),

						);


	// Protected Methods
	protected function log_error($errmsg, $phpfile, $phpline, $context = NULL) {
		if (self::LOG_ERROR == "yes") {
			$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
			file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
			if ($context != NULL) {
		        file_put_contents($log_file, "Context:" . chr(13) . chr(10), FILE_APPEND);
				file_put_contents($log_file, print_r($context, true) . chr(13) . chr(10), FILE_APPEND);
		    }
		}
	}

	//
	// Function to format name
	// Split into words and if the word has more than 3 letters or has a vowel after the first letter then capitalize
	//
	protected function format_name($name) {
	    $parts = preg_split("/\s+/", $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	    for ($i = 0; $i < sizeof($parts); ++ $i) {
	        if (strlen($parts[$i]) == 1 || strlen($parts[$i]) > 3)
	            $parts[$i] = ucfirst(strtolower($parts[$i]));
	        else {
	            // If the name comtains vowel after the first letter, it could be a proper name
	            if (preg_match("/[aeiouy]/i", $parts[$i]))
	                $parts[$i] = ucfirst(strtolower($parts[$i]));
	            else
	                $parts[$i] = strtoupper($parts[$i]);
	        }
	    }
	    return implode(" ", $parts);
	}

	protected function format_title($title) {
	    return ucwords(strtolower($title));
	}

	protected function format_place($place) {
	    return ucwords(strtolower($place));
	}


	// Methods
	function getTemplate($level, $cutting_marks = false) {
		if ($level == 99)
			return $this->template_acceptance;
		else
			return $this->template_award;
		// if ($cutting_marks) {
		// 	if (! empty($this->templates_cm[$level]))
		// 		return $this->templates_cm[$level];
		// 	else
		// 		return false;
		// }
		// else {
		// 	if (! empty($this->templates[$level]))
		// 		return $this->templates[$level];
		// 	else
		// 		return false;
		// }
	}

	function getNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["value"]))
			return $this->nodes[$node_name]["value"];
		else {
			$this->log_error("getNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function setNodeValue($node_name, $value) {

		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field") {

			if (isset($this->nodes[$node_name]['template']))
				$this->nodes[$node_name]["value"] = str_replace("[value]", $value, $this->nodes[$node_name]['template']);
			else
				$this->nodes[$node_name]["value"] = $value;

			// Apply transformation if defined
			if (isset($this->nodes[$node_name]["function"])) {
				$function = $this->nodes[$node_name]["function"];
				$tval = $this->$function($this->nodes[$node_name]["value"]);
				if (isset($tval))
					$this->nodes[$node_name]["value"] = $tval;
			}

			if ( isset($this->nodes[$node_name]["block"]) ) {

				if ( trim($this->nodes[$node_name]["value"]) == "" ) {

					if ( isset($this->nodes[$node_name]["omit_if_empty"]) && $this->nodes[$node_name]["omit_if_empty"] == "yes" ) {
						$this->log_error("setNodeValue omitting $node_name with empty value ($value)", __FILE__, __LINE__, $this->nodes[$node_name]);
						return false;
					}
					else {
						$this->printEnable($this->nodes[$node_name]["block"]);
						return true;
					}

				}
				else {
					$this->printEnable($this->nodes[$node_name]["block"]);
					return true;
				}

			}
			else {
				$this->log_error("setNodeValue missing block in $node_name", __FILE__, __LINE__, $this->nodes[$node_name]);
				return false;
			}
		}
		else {
			$this->log_error("setNodeValue failed to set $value for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function deleteNodeValue($node_name) {
		if (isset($this->nodes[$node_name]) && isset($this->nodes[$node_name]["type"]) && $this->nodes[$node_name]["type"] == "field" &&
		   										isset($this->nodes[$node_name]["value"]) ) {
			unset($this->nodes[$node_name]["value"]);
			return true;
		}
		else {
			$this->log_error("deleteNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getBlock($block_name) {
		if (isset($this->blocks[$block_name]))
			return (object) $this->blocks[$block_name];
		else {
			$this->log_error("getBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getListOfBlocks() {
		$block_list = [];
		foreach($this->blocks as $block_name => $block) {
			$block_list[$block_name] = $block;
		}
		return $block_list;
	}

	function printEnable($block_name) {
		if (isset($this->blocks[$block_name]))
			$this->blocks[$block_name]["print"] = "yes";
		else
			$this->log_error("printEnable unable to find $block_name", __FILE__, __LINE__);
	}

	// $data is an associate array of database values
	function isBlockPrintable($block_name, $data = []) {
		// Check if there is a condition associated with the block
		if (isset($this->blocks[$block_name]["if"])) {
			if ( isset($this->blocks[$block_name]["if"]["field"]) && sizeof($this->blocks[$block_name]["if"]["field"]) > 0 ) {
				// Try to match each field with a match or no match
				for ($i = 0; $i < sizeof($this->blocks[$block_name]["if"]["field"]); ++$i) {

					$field = $this->blocks[$block_name]["if"]["field"][$i];

					// If the data does not contain the field, conditions cannot be verified and hence block cannot be printed
					if (isset($data[$field])) {
						$value = $data[$field];
						$filename = basename($data[$field]);		// If the data contains path
					}
					else {
						$this->log_error("isBlockPrintable omitting $block_name as field [$field] has no data to match", __FILE__, __LINE__, $this->blocks[$block_name]);
						return false;
					}

					// Positive Match value
					if (isset($this->blocks[$block_name]["if"]["match"][$i]))
						$match = $this->blocks[$block_name]["if"]["match"][$i];
					else
						$match = NULL;
					// Negative Match value
					if (isset($this->blocks[$block_name]["if"]["notmatch"][$i]))
						$nomatch = $this->blocks[$block_name]["if"]["notmatch"][$i];
					else
						$nomatch = NULL;

					// If there is a positive match or a negative match, continue matching next field
					if ( ($match != NULL && ($match == $value || $match == $filename)) || ($nomatch != NULL && ($nomatch != $value && $nomatch != $filename)) )
						continue;
					else {
						$this->log_error("isBlockPrintable omitting $block_name value [$value] of [$field] does not have a match with [$match] or non-match with [$nomatch]", __FILE__, __LINE__, $this->blocks[$block_name]);
						return false;
					}
				}
			}
			else {
				$this->log_error("isBlockPrintable has incorrect condition config for $block_name", __FILE__, __LINE__, $this->blocks[$block_name]);
			}
		}

		// Conditions did not exist or conditions matched
		// Return if print flag is set to yes
		if (isset($this->blocks[$block_name]["print"]) && $this->blocks[$block_name]["print"] == "yes" )
			return true;
		else {
			$this->log_error("isBlockPrintable omitting $block_name based on value or 'print' setting", __FILE__, __LINE__, $this->blocks[$block_name]);
			return false;
		}
	}

	function getNode($node_name) {
		if (isset($this->nodes[$node_name]))
			return $this->nodes[$node_name];
		else {
			$this->log_error("getNode unable to find $node_name", __FILE__, __LINE__);
			return false;
		}
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
		else {
			$this->log_error("getNodesForBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}
}

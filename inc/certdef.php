<?php
/*
** certdef.php - Contains Print Definitions for the Certificate
** A separate file is created for each Salon depending upon Certificate Design
**
** Certificate Definition for All India Digital Salon 2020
*/
class Certificate {

	const LOG_ERROR = "no";

	protected $page = [];
	protected $fonts = [];
	protected $colors = [];
	protected $files = [];
	protected $blocks = [];
	protected $nodes = [];

	// constructor
	function __construct($json) {
		if (isset($json['doc']['page']))
			$this->page = $json['doc']['page'];
		if (isset($json['doc']['fonts']))
			$this->font = $json['doc']['font'];
		if (isset($json['doc']['colors']))
			$this->colors = $json['doc']['colors'];
		if (isset($json['doc']['files']))
			$this->files = $json['doc']['files'];
		if (isset($json['blocks']))
			$this->blocks = $json['blocks'];
		if (isset($json['nodes']))
			$this->nodes = $json['nodes'];

		// Process Substitutions
		// Blocks
		for ($i = 0; $i < sizeof($this->blocks); ++ $i) {
			foreach ($this->blocks[$i] as $key => $value) {
				if (in_array($key, ['border_color', 'fill_color'])) {
					// Replace with color
					if (isset($this->colors[$value]))
						$this->blocks[$i][$key] = $this->colors[$value];
				}
			}
		}
		// Nodes
		for ($i = 0; $i < sizeof($this->nodes); ++ $i) {
			foreach ($this->nodes[$i] as $key => $value) {
				if (in_array($key, ['bordercolor', 'font_color'])) {
					// Replace with color
					if (isset($this->colors[$value]))
						$this->nodes[$i][$key] = $this->colors[$value];
				}
			}
		}
	}

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
		return $this->files['design'];
		// if ($level == 99)
		// 	return $this->files['design_acceptance'];
		// elseif ($level == 9)
		// 	return $this->files['design_hm'];
		// elseif ($level == 8 && isset($this->files['design_award_8']))
		// 	return $this->files['design_award_8'];
		// elseif ($level == 7 && isset($this->files['design_award_7']))
		// 	return $this->files['design_award_7'];
		// elseif ($level == 6 && isset($this->files['design_award_6']))
		// 	return $this->files['design_award_6'];
		// elseif ($level == 5 && isset($this->files['design_award_5']))
		// 	return $this->files['design_award_5'];
		// elseif ($level == 4 && isset($this->files['design_award_4']))
		// 	return $this->files['design_award_4'];
		// elseif ($level == 3 && isset($this->files['design_award_3']))
		// 	return $this->files['design_award_3'];
		// elseif ($level == 2 && isset($this->files['design_award_2']))
		// 	return $this->files['design_award_2'];
		// elseif ($level == 1 && isset($this->files['design_award_1']))
		// 	return $this->files['design_award_1'];
		// else
		// 	return $this->files['design_award'];
	}

	// function getSaveFileName() {
	// 	return $this->files['file_name_stub'];
	// }
	//
	function getNodeIndex($node_name) {
		for ($i = 0; $i < sizeof($this->nodes); ++ $i) {
			if ($this->nodes[$i]['name'] == $node_name)
				return $i;
		}
		return -1;
	}

	function getBlockIndex($block_name) {
		for ($i = 0; $i < sizeof($this->blocks); ++ $i) {
			if ($this->blocks[$i]['name'] == $block_name)
				return $i;
		}
		return -1;
	}

	function getNodeValue($node_name) {
		if ( ($node_index = $this->getNodeIndex($node_name)) >= 0 && isset($this->nodes[$node_index]["value"]))
			return $this->nodes[$node_index]["value"];
		else {
			$this->log_error("getNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function setNodeValue($node_name, $value) {

		if ( ($node_index = $this->getNodeIndex($node_name)) >= 0 && isset($this->nodes[$node_index]["type"]) && $this->nodes[$node_index]["type"] == "field") {

			if (isset($this->nodes[$node_index]['template']) && $this->nodes[$node_index]['template'] != "")
				$this->nodes[$node_index]["value"] = str_replace("[value]", $value, $this->nodes[$node_index]['template']);
			else
				$this->nodes[$node_index]["value"] = $value;

			// Apply transformation if defined
			// if (isset($this->nodes[$node_index]["function"])) {
			// 	$function = $this->nodes[$node_index]["function"];
			// 	$tval = $this->$function($this->nodes[$node_index]["value"]);
			// 	if (isset($tval))
			// 		$this->nodes[$node_index]["value"] = $tval;
			// }

			if ( isset($this->nodes[$node_index]["block"]) ) {

				if ( trim($this->nodes[$node_index]["value"]) == "" ) {

					if ( isset($this->nodes[$node_index]["omit_if_empty"]) && $this->nodes[$node_index]["omit_if_empty"] == "yes" ) {
						$this->log_error("setNodeValue omitting $node_name with empty value ($value)", __FILE__, __LINE__, $this->nodes[$node_index]);
						return false;
					}
					else {
						$this->printEnable($this->nodes[$node_index]["block"]);
						return true;
					}

				}
				else {
					$this->printEnable($this->nodes[$node_index]["block"]);
					return true;
				}

			}
			else {
				$this->log_error("setNodeValue missing block in $node_name", __FILE__, __LINE__, $this->nodes[$node_index]);
				return false;
			}
		}
		else {
			$this->log_error("setNodeValue failed to set $value for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function deleteNodeValue($node_name) {
		if ( ($node_index = $this->getNodeIndex($node_name)) >= 0 && isset($this->nodes[$node_index]["type"]) && $this->nodes[$node_index]["type"] == "field" &&
		   										isset($this->nodes[$node_index]["value"]) ) {
			unset($this->nodes[$node_index]["value"]);
			return true;
		}
		else {
			$this->log_error("deleteNodeValue failed for $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getBlock($block_name) {
		if (($block_index = $this->getBlockIndex($block_name)) >= 0) {
			// Check if this block needs to be moved and set co-ordinates
			$block = $this->blocks[$block_index];
			if (isset($block['print_at_when_omitted']) && ($ob_index = $this->getBlockIndex($block['print_at_when_omitted'])) >= 0 ) {
				// if (! $this->isBlockPrintable($block['print_at_when_omitted'])) {
				if ($this->blocks[$ob_index]['print'] == 'no') {
					// $omitted_block = $this->blocks[$omitted_block_index];
					$block["x"] = $block["x_at"];
					$block["y"] = $block["y_at"];
				}
			}
			return (object) $block;
		}
		else {
			$this->log_error("getBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getListOfBlocks() {
		$block_list = [];
		for ($i = 0; $i < sizeof($this->blocks); ++ $i) {
			$block_name = $this->blocks[$i]['name'];
			$block_list[$block_name] = $this->getBlock($block_name);
		}
		return $block_list;
	}

	function printEnable($block_name) {
		if (($block_index = $this->getBlockIndex($block_name)) >= 0)
			$this->blocks[$block_index]["print"] = "yes";
		else
			$this->log_error("printEnable unable to find $block_name", __FILE__, __LINE__);
	}

	// $data is an associate array of database values
	function isBlockPrintable($block_name, $data = []) {
		if (($block_index = $this->getBlockIndex($block_name)) >= 0) {
			// Check if there is a condition associated with the block
			if (isset($this->blocks[$block_index]["if"])) {
				if ( isset($this->blocks[$block_index]["if"]["field"]) && sizeof($this->blocks[$block_index]["if"]["field"]) > 0 ) {
					// Try to match each field with a match or no match
					for ($i = 0; $i < sizeof($this->blocks[$block_index]["if"]["field"]); ++$i) {

						$match_field = $this->blocks[$block_index]["if"]["field"][$i];
						$match_type = $this->blocks[$block_index]["if"]["match"][$i];
						$match_value = $this->blocks[$block_index]["if"]["value"][$i];

						if ($match_field != "") {

							// If the data does not contain the field, conditions cannot be verified and hence block cannot be printed
							if (isset($data[$match_field])) {
								$value = $data[$field];
								$filename = basename($data[$field]);		// If the data contains path
							}
							else {
								$this->log_error("isBlockPrintable omitting $block_name as field [$field] has no data to match", __FILE__, __LINE__, $this->blocks[$block_index]);
								return false;
							}

							if ( ($match_type == 'EQ' && ($match_value == $value || $match_value == $filename)) ||
								 ($match_tyle != 'EQ' && $match_value != $value && $match_value != $filename) )
								 	continue;
							 else {
 								$this->log_error("isBlockPrintable omitting $block_name value [$value] of [$field] does not have a match with [$match] or non-match with [$nomatch]", __FILE__, __LINE__, $this->blocks[$block_index]);
 								return false;
 							}

							// Positive Match value
							// if (isset($this->blocks[$block_index]["if"]["match"][$i]))
							// 	$match = $this->blocks[$block_index]["if"]["match"][$i];
							// else
							// 	$match = NULL;
							// Negative Match value
							// if (isset($this->blocks[$block_index]["if"]["notmatch"][$i]))
							// 	$nomatch = $this->blocks[$block_index]["if"]["notmatch"][$i];
							// else
							// 	$nomatch = NULL;


							// If there is a positive match or a negative match, continue matching next field
							// if ( ($match != NULL && ($match == $value || $match == $filename)) || ($nomatch != NULL && ($nomatch != $value && $nomatch != $filename)) )
							// 	continue;
							// else {
							// 	$this->log_error("isBlockPrintable omitting $block_name value [$value] of [$field] does not have a match with [$match] or non-match with [$nomatch]", __FILE__, __LINE__, $this->blocks[$block_index]);
							// 	return false;
							// }
						}
					}
				}
				else {
					$this->log_error("isBlockPrintable has incorrect condition config for $block_name", __FILE__, __LINE__, $this->blocks[$block_index]);
					return false;
				}
			}
			// Conditions did not exist or conditions matched
			// Return if print flag is set to yes
			if (isset($this->blocks[$block_index]["print"]) && $this->blocks[$block_index]["print"] == "yes" )
				return true;
			else {
				$this->log_error("isBlockPrintable omitting $block_name based on value or 'print' setting", __FILE__, __LINE__, $this->blocks[$block_index]);
				return false;
			}
		}
		else {
			$this->log_error("isBlockPrintable could not find $block_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getNode($node_name) {
		if (($node_index = $this->getNodeIndex($node_name)) >= 0)
			return $this->nodes[$node_index];
		else {
			$this->log_error("getNode unable to find $node_name", __FILE__, __LINE__);
			return false;
		}
	}

	function getNodesForBlock($block_name) {
		if (($block_index = $this->getBlockIndex($block_name)) >= 0) {
			$node_list = [];
			for ( $node_index = 0; $node_index < sizeof($this->nodes); ++ $node_index) {
				$node = $this->nodes[$node_index];
				if (isset($node['block']) && $node['block'] == $block_name)
					$node_list[$node['name']] = $node;
			}
			return $node_list;
		}
		else {
			$this->log_error("getNodesForBlock unable to find $block_name", __FILE__, __LINE__);
			return false;
		}
	}
}

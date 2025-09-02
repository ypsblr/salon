<?php
require($_SERVER['DOCUMENT_ROOT'] . '/pdf/tfpdf.php');
// require('pdf/tfpdf.php');

//
// UTILITY FUNCTIONS
//

//
// Paper Dimensions
//
function get_paper_dimensions($size, $unit) {
	$paper_dimensions = array (
				"A0" => [array("mm" => 841, "cm" => 84.1, "in" => 33.1, "pt" => 2383.2),
							array("mm" => 1189, "cm" => 118.9, "in" => 46.8, "pt" => 3369.6)],
				"A1" => [array("mm" => 594, "cm" => 59.4, "in" => 23.4, "pt" => 1684.8),
							array("mm" => 841, "cm" => 84.1, "in" => 33.1, "pt" => 2383.2)],
				"A2" => [array("mm" => 420, "cm" => 42, "in" => 16.5, "pt" => 1188),
							array("mm" => 594, "cm" => 59.4, "in" => 23.4, "pt" => 1684.8)],
				"A3" => [array("mm" => 297, "cm" => 29.7, "in" => 11.7, "pt" => 842.4),
							array("mm" => 420, "cm" => 42, "in" => 16.5, "pt" => 1188)],
				"A4" => [array("mm" => 210, "cm" => 21, "in" => 8.3, "pt" => 597.6),
							array("mm" => 297, "cm" => 29.7, "in" => 11.7, "pt" => 842.4)],
				"A5" => [array("mm" => 148, "cm" => 14.8, "in" => 5.8, "pt" => 417.6),
							array("mm" => 210, "cm" => 21, "in" => 8.3, "pt" => 597.6)],
				"Letter" => [array("mm" => 216, "cm" => 21.6, "in" => 8.5, "pt" => 612),
							array("mm" => 279, "cm" => 27.9, "in" => 11, "pt" => 792)],
				"Legal" => [array("mm" => 216, "cm" => 21.6, "in" => 8.5, "pt" => 612),
							array("mm" => 356, "cm" => 35.6, "in" => 14, "pt" => 1008)],
				"Cert" => [array("mm" => 152, "cm" => 15.2, "in" => 6, "pt" => 432),
							array("mm" => 229, "cm" => 22.9, "in" => 9, "pt" => 648)]
			);

	if (is_array($size)) {
		if (sizeof($size) == 2) {
			list($width, $height) = $size;
			switch($unit) {
				case "mm" : return [array("mm" => $width, "cm" => round($width / 10, 1), "in" => round($width / 25.4, 1), "pt" => round($width / 25.4 * 72, 0)),
										array("mm" => $height, "cm" => round($height / 10, 1), "in" => round($height / 25.4, 1), "pt" => round($height / 25.4 * 72, 0)) ];
				case "cm" : return [array("mm" => round($width * 10, 0), "cm" => $width, "in" => round($width / 2.54, 1), "pt" => round($width / 2.54 * 72, 0)),
										array("mm" => round($height * 10, 0), "cm" => $height, "in" => round($height / 2.54, 1), "pt" => round($height / 2.54 * 72, 0)) ];
				case "in" : return [array("mm" => round($width * 25.4, 0), "cm" => round($width * 2.54, 1), "in" => $width, "pt" => round($width * 72, 0)),
										array("mm" => round($height * 25.4, 0), "cm" => round($height * 2.54, 1), "in" => $height, "pt" => round($height * 72, 0)) ];
				case "pt" : return [array("mm" => round($width / 72 * 25.4, 0), "cm" => round($width / 72 * 2.54, 1), "in" => round($width / 72, 1), "pt" => $width),
										array("mm" => round($height / 72 * 25.4, 0), "cm" => round($height / 72 * 2.54, 1), "in" => round($height / 72, 1), "pt" => $height) ];
				default : return array([], []);	// Empty Array
			}
		}
		else {
			return array([], []);
		}
	}
	else {
		if (isset($paper_dimensions[$size]))
			return [$paper_dimensions[$size]["width"], $paper_dimensions[$size]["height"]];
		else {
			return array([], []);
		}
	}
}

function get_cutting_mark_units($unit) {
	$cutting_marks = array("mm" => 3, "cm" => 0.3, "in" => 0.125, "pt" => 9);
	$cutting_mark_line_width = array("mm" => 25.4/144, "cm" => 2.54/144, "in" => 1/144, "pt" => 0.5);

	return array($cutting_marks[$unit], $cutting_mark_line_width[$unit]);
}

// General Functions
// Return Red color from hex color value
function get_red($hex) {
	return ($hex & 0xff0000) >> 16;
}

// Set Red color in hex color value and return modified hex color
function set_red($red, $hex = 0x0) {
	return ($hex & 0x00ffff) | ($red << 16);
}

// Return Green color from hex color value
function get_green($hex) {
	return ($hex & 0x00ff00) >> 8;
}

// Set Green color in hex color value and return modified hex color
function set_green($green, $hex = 0x0) {
	return ($hex & 0xff00ff) | ($green << 8);
}

// Return Blue color from hex color value
function get_blue($hex) {
	return ($hex & 0x0000ff);
}

// Set Blue color in hex color value and return modified hex color
function set_blue($blue, $hex = 0x0) {
	return ($hex & 0xffff00) | $blue;
}

// Change HEX Color Value to RGB
function hex2rgb($hex) {
	return (array(get_red($hex), get_green($hex), get_blue($hex)));
}

// Combine r g b colors in to hex color
function rgb2hex($r, $g, $b) {
	return set_red($r) | set_green($g) | set_blue($b);
}


// class PDF extends FPDF {
class cPDF extends tFPDF {
	// Custom Fields
	protected $paper_size = "Cert";
	protected $dimension_unit = "pt";
	protected $paper_width = array("mm" => 152, "cm" => 15.2, "in" => 6, "pt" => 432);
	protected $paper_height = array("mm" => 229, "cm" => 22.9, "in" => 9, "pt" => 648);
	protected $margin = 0;

	protected $cutting_mark_margin = 0;
	protected $cutting_mark_line_width = 0;
	protected $add_cutting_marks = false;

	protected $blocks;			// Ability to assemble blocks of content and then render in one shot. Helps validate size.
	protected $last_block_id;

	protected $angle;			// Current Rotation Setting


	// Custom Constructor that sets paper size and margins
	function __construct($orientation='L', $unit='pt', $size='Cert', $margin=0, $cutting_marks = false) {
		list ($width, $height) = get_paper_dimensions($size, $unit);
		if ($width != -1 && $height != -1) {
			$paper_width = $width[$unit];
			$paper_height = $height[$unit];
			if ($cutting_marks) {
				list($cm_margin, $cm_line_width) = get_cutting_mark_units($unit);
				$paper_width += ($cm_margin * 2);
				$paper_height += ($cm_margin * 2);
				$size = array($paper_width, $paper_height);
			}
			parent::__construct($orientation, $unit, $size);
			$this->paper_size = is_array($size) ? "Custom" : $size;
			$this->dimension_unit = $unit;
			$this->paper_width = $width;		// width dimensions
			$this->paper_height = $height;		// height dimensions
			$this->margin = $margin;
			if ($cutting_marks) {
				$this->cutting_mark_margin = $cm_margin;
				$this->cutting_mark_line_width = $cm_line_width;
				$this->margin += $cm_margin;
			}
			$this->add_cutting_marks = $cutting_marks;
		}
		else
			die("Unknown dimensions for " . $size . " in " . $unit . " units !");
	}


	function GetPageWidth() {
		if (isset($this->paper_width[$this->dimension_unit]))
			return $this->paper_width[$this->dimension_unit] + ($this->cutting_mark_margin * 2);
		else
			return 432;		// Default Cert width in pt
	}

	function GetPageHeight() {
		if (isset($this->paper_height[$this->dimension_unit]))
			return $this->paper_height[$this->dimension_unit] + ($this->cutting_mark_margin * 2);
		else
			return 648;		// Default Cert height in pt
	}

	function GetPrintAreaWidth() {
		return $this->GetPageWidth() - (2 * $this->margin);
	}

	function GetPrintAreaHeight() {
		return $this->GetPageHeight() - (2 * $this->margin);
	}


	function SetHexFillColor($hex) {
		if (is_string($hex) && substr($hex, 0, 2) == "0x")
			$color = hexdec($hex);
		else
			$color = $hex;
		list($red, $green, $blue) = hex2rgb($color);
		$this->SetFillColor($red, $green, $blue);
	}

	function SetHexTextColor($hex) {
		if (is_string($hex) && substr($hex, 0, 2) == "0x")
			$color = hexdec($hex);
		else
			$color = $hex;
		list($red, $green, $blue) = hex2rgb($color);
		$this->SetTextColor($red, $green, $blue);
	}

	function SetHexDrawColor($hex) {
		if (is_string($hex) && substr($hex, 0, 2) == "0x")
			$color = hexdec($hex);
		else
			$color = $hex;
		list($red, $green, $blue) = hex2rgb($color);
		$this->SetDrawColor($red, $green, $blue);
	}

	// Create a new page
	function CreatePage($background_img) {
		$this->SetMargins($this->margin, $this->margin);
		$this->AddPage();
		$this->SetBackground($background_img);
		if ($this->add_cutting_marks)
			$this->PrintCuttingMarks();
	}

	// Add Cutting Marks
	function PrintCuttingMarks() {
		if ($this->add_cutting_marks) {
			$page_width = $this->GetPageWidth();
			$page_height = $this->GetPageHeight();

			$cm_width = $this->cutting_mark_margin;

			$this->SetDrawColor(0, 0, 0);		// Black
			$this->SetLineWidth($this->cutting_mark_line_width);

			// Left Top Corners
			$this->Line(0, $cm_width, $cm_width / 2, $cm_width);	// Horizontal
			$this->Line($cm_width, 0, $cm_width, $cm_width / 2);	// Vertical

			// Left Bottom Corners
			$this->Line(0, $page_height - $cm_width, $cm_width / 2, $page_height - $cm_width);	// Horizonal
			$this->Line($cm_width, $page_height, $cm_width, $page_height - ($cm_width / 2));	// Vertical

			// Top Right Corners
			$this->Line($page_width, $cm_width, $page_width - ($cm_width / 2), $cm_width);	// Horizonal
			$this->Line($page_width - $cm_width, 0, $page_width - $cm_width, $cm_width / 2);	// Vertical

			// Bottom Right Corners
			$this->Line($page_width, $page_height - $cm_width, $page_width - ($cm_width / 2), $page_height - $cm_width);	// Horizonal
			$this->Line($page_width - $cm_width, $page_height, $page_width - $cm_width, $page_height - ($cm_width / 2));	// Vertical
		}
	}

	// Covert Pixels to unit used in this instance assuming a 300 DPI resolution
	function PixelsToUnits($pixels) {
		switch($this->dimension_unit) {
			case "mm" : return $pixels / 300 * 25.4;
			case "cm" : return $pixels / 300 * 2.54;
			case "in" : return $pixels / 300;
			case "pt" : return $pixels / 300 * 72;
			default : return $pixels / 300 * 72;
		}
	}

	// Covert document unit to points for text handling
	// Handle a single input or an array
	function UnitsToPts($vals) {
		if ( is_array($vals) )
			$dimension_vals = $vals;
		else
			$dimension_vals = array($vals);

		$ret_vals = [];

		foreach ($dimension_vals as $dimension) {
			switch($this->dimension_unit) {
				case "mm" : $ret_vals[] = $dimension / 25.4 * 72; break;
				case "cm" : $ret_vals[] = $dimension / 2.54 * 72; break;
				case "in" : $ret_vals[] = $dimension * 72; break;
				case "pt" : $ret_vals[] = $dimension; break;
				default : $ret_vals[] = $dimension;	break;	// assume points
			}
		}
		if (is_array($vals))
			return $ret_vals;
		else
			return $ret_vals[0];
	}

	// Covert points to document unit for text purposes
	// Handle a single input or an array
	function PtsToUnits($vals) {
		if ( is_array($vals) )
			$pt_vals = $vals;
		else
			$pt_vals = array($vals);

		$ret_vals = [];

		foreach ($pt_vals as $pts) {
			switch($this->dimension_unit) {
				case "mm" : $ret_vals[] = $pts / 72 * 25.4; break;
				case "cm" : $ret_vals[] = $pts / 72 * 2.54; break;
				case "in" : $ret_vals[] = $pts / 72; break;
				case "pt" : $ret_vals[] = $pts; break;
				default : $ret_vals[] = $pts; break;		// assume points
			}
		}
		if (is_array($vals))
			return $ret_vals;
		else
			return $ret_vals[0];
	}

	// Word Wrap function
	// Uses current font settings to calculate the width and wrap the words
	// text is passed by reference and is modified by inserting newlines
	// Returns Number of Lines
	// Adds carriage Returns to original text to print on multiple lines
	function WordWrap(&$text, $maxwidth) {
		$text = trim($text);
		if ($text==='')
			return 0;
		$space = $this->GetStringWidth(' ');
		$lines = explode("\n", $text);
		$text = '';
		$count = 0;

		foreach ($lines as $line)
		{
			$words = preg_split('/ +/', $line);
			$width = 0;

			foreach ($words as $word)
			{
				$wordwidth = $this->GetStringWidth($word);
				if ($wordwidth > $maxwidth)
				{
					// Word is too long, we cut it
					for($i = 0; $i < strlen($word); $i++)
					{
						$wordwidth = $this->GetStringWidth(substr($word, $i, 1));
						if($width + $wordwidth <= $maxwidth)
						{
							$width += $wordwidth;
							$text .= substr($word, $i, 1);
						}
						else
						{
							$width = $wordwidth;
							$text = rtrim($text)."\n".substr($word, $i, 1);
							$count++;
						}
					}
				}
				elseif(($width + $wordwidth + $space) <= $maxwidth)
				{
					$width += $wordwidth + $space;
					$text .= $word . ' ';
				}
				else
				{
					$width = $wordwidth + $space;
					$text = rtrim($text) . "\n" . $word . ' ';
					$count++;
				}
			}
			$text = rtrim($text) . "\n";
			$count++;
		}
		$text = rtrim($text);
		return $count;
	}

	// Set Page background image
	// Make sure that the image conforms to the paper size to avoid clipping and partial background
	function SetBackground($background_img, $dpi = 300) {
		$x = $this->add_cutting_marks ? $this->cutting_mark_margin : 0;
		$y = $this->add_cutting_marks ? $this->cutting_mark_margin : 0;
		$this->Image($background_img, $x, $y, -1 * $dpi);	// 300 DPI set
		// $this->Image($background_img, 0, 0, -1 * $dpi);	// 300 DPI set
	}

	function SetDocumentFonts($font_family, $regular_font_file, $bold_font_file = "", $italics_font_file = "", $bold_italics_font_file = "") {
		$this->default_font = $font_family;
		$this->AddFont($font_family, '', $regular_font_file);
		if ($bold_font_file != "")
			$this->AddFont($font_family, 'B', $bold_font_file);
		if ($italics_font_file != "")
			$this->AddFont($font_family, 'I', $italics_font_file);
		if ($bold_italics_font_file != "")
			$this->AddFont($font_family, 'BI', $bold_italics_font_file);
	}

	function PrintFiller($font, $font_size, $color, $x, $y, $width, $height, $align, $text, $angle = 0) {
		if (strpos($font, "|") == false) {
			$font_name = $font;
			$font_style = "";
		}
		else {
			list($font_name, $font_style) = explode("|", $font);
		}
		// Set Font
		$this->SetFont($font_name, $font_style, $font_size);
		// Set Color
		if (! is_numeric($color))
			$color = hexdec($color);
		$this->SetTextColor($color / (256*256), ($color / 256) % 256, $color % 256);
		// Set Location
		$this->SetXY($x, $y);
		$this->Rotate($angle, $x, $y);
		$this->MultiCell($width, $height, $text, 0, $align);
		$this->Rotate(0);
	}


	function PrintThumbnail($tn_x, $tn_y, $thumbnail_file, $thumbnail_size = 60, $border = false, $align = "MM", $link = "") {

		list($width, $height) = getimagesize($thumbnail_file);

		// Readjust to the square of 60mm x 60mm starting at 12mm,145mm co-ordinate
		if ($width < $height) {
			// Portrait orientation
			$img_height = $thumbnail_size;		// Set height to the maximum
			$img_width = $width / $height * $thumbnail_size;
		}
		else {
			$img_width = $thumbnail_size;
			$img_height = $height / $width * $thumbnail_size;
		}

		// Alignment within the frame
		$halign = substr($align, 0, 1);
		switch($halign) {
			case "L" : $x = $tn_x; break;
			case "R" : $x = $tn_x + $thumbnail_size - $img_width; break;
			case "M" : $x = $tn_x + (($thumbnail_size - $img_width) / 2); break;
			default : $x = $tn_x + (($thumbnail_size - $img_width) / 2); break;
		}

		$valign = substr($align, 1, 1);
		switch($valign) {
			case "T" : $y = $tn_y; break;
			case "B" : $y = $tn_y + $thumbnail_size - $img_height; break;
			case "M" : $y = $tn_y + (($thumbnail_size - $img_height) / 2); break;
			default : $y = $tn_y + (($thumbnail_size - $img_height) / 2); break;
		}

		if ($frame != "") {
			// Suitable Frame File must be passed on
			list($frame_file, $frame_location) = explode("|", $frame);
			switch($frame_location) {
				case "LB" : $this->Image($frame_file, $x - 2, $y + 2, $img_width, $img_height); break;
				case "LT" : $this->Image($frame_file, $x - 2, $y - 2, $img_width, $img_height); break;
				case "RT" : $this->Image($frame_file, $x + 2, $y - 2, $img_width, $img_height); break;
				case "RB" : $this->Image($frame_file, $x + 2, $y + 2, $img_width, $img_height); break;
				default : $this->Image($frame_file, $x - 2, $y + 2, $img_width, $img_height); break;
			}
		}

		if ($link == "")
			$this->Image($thumbnail_file, $x, $y, $img_width, $img_height);
		else
			$this->Image($thumbnail_file, $x, $y, $img_width, $img_height, NULL, $link);

		// Draw a Light Gray Border
		if (gettype($border) == "boolean" && $border)  {
			$this->SetDrawColor(192, 192, 192);
			$line_width = 0.5;
			$this->SetLineWidth($line_width);
			$this->Rect($x - $line_width, $y - $line_width, $img_width + ($line_width * 2), $img_height + ($line_width * 2), "D");
		}
		else if (gettype($border) == "string" && $border != "") {
			list($width, $color) = explode("|", $border);
			$this->SetDrawColor(get_red($color), get_green($color), gget_blue($color));
			$this->SetLineWidth($width);
			$this->Rect($x - $line_width, $y - $line_width, $img_width + ($line_width * 2), $img_height + ($line_width * 2), "D");
		}
	}

	//
	// Generates a Horizontal Line
	//
	function PrintHR($width = 0.5, $length = 0, $align = "L", $color = 0x7f7f7f, $margin_top = 0, $margin_bottom = 0) {

		$page_width = $this->GetPageWidth() - ($this->margin * 2);

		// Fix the length of the line
		if ($length == 0 || $length > $page_width)
			$length = $page_width;

		$saved_y = $this->GetY();
		$yf = $this->GetY() + $margin_top;
		$yt = $yf;

		switch($align) {
			case "C" : {	// Centered
				$xf = $this->margin + (($page_width - $length) / 2);
				$xt = $xf + $length;
				break;
			}
			case "L" : {
				$xf = $this->margin;
				$xt = $xf + $length;
				break;
			}
			case "R": {
				$xf = $this->margin + $page_width - $length;
				$xt = $xf + $length;
			}
		}

		$this->SetLineWidth($width);
		$this->SetDrawColor(get_red($color), get_green($color), get_blue($color));
		$this->Line($xf, $yf, $xt, $yt);

		$this->SetXY($this->margin, $saved_y + $margin_top + $width + $margin_bottom);
	}

	//
	// *** BLOCK FUNCTIONS - Allow a block of content to be assembled before rendering the same
	//
	function RemoveAllBlocks () {
		$this->blocks = [];		// Initialize Array
		$this->last_block_id = 0;
	}

	// Creates a Block that can contain multiple Child Nodes
	// $box should be of the format FILL_COLOR|BORDER_WIDTH|BORDER_COLOR
	// $margin = space inside the block not used for printing
	// $height = if 0, it means it can grow to the required height
	function CreateBlock ($type, $width, $height, $x, $y, $margin = 4, $box = "", $orientation = "N") {
		// Check to see if the block will fit within page
		$page_width = $this->GetPrintAreaWidth();
		$page_height = $this->GetPrintAreaHeight();
		if (($x + $width) > $page_width || ($y + $height) > $page_height)
			return false;

		$child_x = $x + $this->margin;	// Adjust x with margins
		$child_y = $y + $this->margin;	// Adjust y with margins
		++ $this->last_block_id;
		$this->blocks[$this->last_block_id] = array("type" => $type, "width" => $width, "height" => $height, "offset_x" => $x, "offset_y" => $y,
													"orientation" => $orientation,
													"child_width" => $width - (2 * $margin), "child_height" => ($height == 0 ? 0 : $height - (2 * $margin)),
													"used_width" => 0, "used_height" => 0,
													"child_x" => $x + $margin, "child_y" => $y + $margin,
													"margin" => $margin, "box" => $box, "nodes" => []);
		// debug_dump("BLOCK", $this->blocks, __FILE__, __LINE__);
		return $this->last_block_id;
	}

	// $for = "list" or "tile" - in document unit
	function GetNodeArea($block_id) {

		$unit = $this->dimension_unit;

		if ($this->blocks[$block_id]["type"] == "list") {
			// List is stacked vertically
			$available_width = $this->blocks[$block_id]["child_width"];
			if ($this->blocks[$block_id]["height"] == 0) {
				// Flexible Height - Extend up to the page height
				$available_height = $this->paper_height[$unit] - $this->margin - $this->blocks[$block_id]['used_height'];
			}
			else {
				// Fixed Height - Extend up to the page height
				$available_height = $this->blocks[$block_id]["child_height"] - $this->blocks[$block_id]['used_height'];
			}
		}
		else {
			// Tile is stacked horizontally
			$available_width = $this->blocks[$block_id]["child_width"] - $this->blocks[$block_id]["used_width"];
			if ($this->blocks[$block_id]["height"] == 0) {
				// Flexible Height - Extend up to the page height
				$available_height = $this->paper_height[$unit] - $this->margin;
			}
			else {
				// Fixed Height - Extend up to the page height
				$available_height = $this->blocks[$block_id]["child_height"];
			}
		}
		$x = $this->blocks[$block_id]["child_x"];
		$y = $this->blocks[$block_id]["child_y"];
		return array($available_width, $available_height, $x, $y);
	}

	// all dimensions in document unit
	function UpdateNodeArea($block_id, $width, $height, $float = "left", $spacing = 0) {
		if ($this->blocks[$block_id]["type"] == "list") {
			// Place each block below another - adjust "child_y"
			$this->blocks[$block_id]["used_height"] += $height;
			$this->blocks[$block_id]["child_y"] += $height;
		}
		else if($this->blocks[$block_id]["type"] == "tile") {
			// type = "tile"
			// Place each block next to each other
			// adjust "child_x"
			$this->blocks[$block_id]["used_width"] += $width + $spacing;
			if ($float == "left")
				$this->blocks[$block_id]["child_x"] += $width + $spacing;
		}
		else {
			// type = "canvas"
			// not keeping track of available space or current dignitory_positions
			// Nodes are added at specified offsets
		}
	}

	// Get Text Node Height
	function GetTextNodeHeight($text, $width, $font_family, $font_style, $font_size, $line_spacing = 1.2) {
		$this->SetFont($font_family, $font_style, $font_size);
		$wrapped_text = $text;
		//$num_lines = $this->WordWrap($wrapped_text, $width);
		$num_lines = $this->WordWrap($wrapped_text, $width - 8);	// Add some safety margin to avoid unwanted wraps
		$height = $this->PtsToUnits(($num_lines == 0 ? 1 : $num_lines) * $font_size * $line_spacing);
		return array($height, $wrapped_text);
	}

	// Add a Text Node to the bottom of the Block
	function AddTextNode ($block_id, $font, $align, $text, $line_spacing = 1.2, $height = 0, $posx = 0, $posy = 0 ) {
		// Compute Inherited Values
		list ($available_width, $available_height, $x, $y) = $this->GetNodeArea($block_id);
		$x = ($posx == 0) ? $x : $x + $posx;
		$y = ($posy == 0) ? $y : $y + $posy;

		$width = $available_width;

		// Determine the height required - Value passed is in the unit of the page and not in pts
		list($font_family, $font_style, $font_size, $font_color) = explode("|", $font);
		if ($height == 0)
			list($height, $wrapped_text) = $this->GetTextNodeHeight($text, $width, $font_family, $font_style, $font_size, $line_spacing);
		else
			$wrapped_text = $text;

		// Check if the block can be accommodated
		if ($height <= $available_height) {
			$this->blocks[$block_id]["nodes"][] = array("block_id" => $block_id, "type" => "TXT", "width" => $width, "height" => $height,
														"offset_x" => $x, "offset_y" => $y, "font" => $font, "align" => $align,
														"text" => $wrapped_text, "line_spacing" => $line_spacing );

			// Update the block
			$this->UpdateNodeArea($block_id, $width, $height);
		}
	}

	// $box - "width|height"
	// $border - "frame|width|frame_file"  or "border|width|color""
	// $float - "left"  or "right"
	function AddImageNode ($block_id, $file, $box = "", $border = "", $float = "fill", $spacing = 0, $link = "" ) {
		// Compute Inherited Values
		list ($available_width, $available_height, $x, $y) = $this->GetNodeArea($block_id);

		$max_width = $available_width;
		$max_height = $available_height;

		if ($box != "") {
			list($box_width, $box_height) = explode("|", $box);
			$max_width = ($box_width < $max_width) ? $box_width : $max_width;
			$max_height = ($box_height < $max_height) ? $box_height : $max_height;
		}

		// Assume the entire area as available for the picture
		$max_pic_width = $max_width;
		$max_pic_height = $max_height;

		// Provide space for frame
		if ($border != "") {
			list($border_type) = explode("|", $border);
			if ($border_type == "frame")
				list($border_type, $border_width, $border_pic_file) = explode("|", $border);
			else
				list($border_type, $border_width, $border_color) = explode("|", $border);
			$max_pic_width -= ($border_width * 2);
			$max_pic_height -= ($border_width * 2);
		}
		else {
			$border_width = 0;
		}

		// Get Image Size in Points
		// debug_dump("getimagesize", getimagesize($file), __FILE__, __LINE__);
		list($img_width, $img_height) = getimagesize($file);
		// $img_width = $this->PixelsToUnits($img_width);
		// $img_height = $this->PixelsToUnits($img_height);

		// Fit to max_width and max_height
		if ($img_width > $max_pic_width || $img_height > $max_pic_height) {
			// Determine the dimension with largest deficit
			if (($img_width / $max_pic_width) > ($img_height / $max_pic_height)) {
				// Need to constrain width to max_width
				$pic_width = $max_pic_width;
				$pic_height = $img_height * $max_pic_width / $img_width;
			}
			else {
				// Constrain the height
				$pic_height = $max_pic_height;
				$pic_width = $img_width * $max_pic_height / $img_height;
			}
		}
		else {
			$pic_width = $img_width;
			$pic_height = $img_height;
		}

		// Create a Rectangle node with fill color equal to border
		// or
		// Create a Image node for the frame file scaling it to the required size
		$used_width = 0;
		$used_height = 0;
		if ($border != "") {
			$bg_width = $pic_width + (2 * $border_width);
			$bg_height = $pic_height + (2 * $border_width);
			switch($float) {
				case "left" : {
					$bg_x = 0;
					$bg_y = 0;
					$used_width = $bg_width;
					$used_height = $bg_height;
					break;
				}
				case "right" : {
					$bg_x = $available_width - $bg_width;
					$bg_y = 0;
					$used_width = $bg_width;
					$used_height = $bg_height;
					break;
				}
				case "center" : {
					$bg_x = ($available_width - $bg_width) / 2;
					$bg_y = 0;
					$used_width = $bg_width;
					$used_height = $bg_height;
					break;
				}
				case "fill" : {
					$bg_x = ($available_width - $bg_width) / 2;
					$bg_y = ($available_height - $bg_height) / 2;
					$used_width = $available_width;
					$used_height = $available_height;
					break;
				}
				default : {
					// same as "fill"
					$bg_x = ($available_width - $bg_width) / 2;
					$bg_y = ($available_height - $bg_height) / 2;
					$used_width = $available_width;
					$used_height = $available_height;
					break;
				}
			}
			// Add frame
			if ($border_type == "frame") {
				// Create an Image Node
				$this->blocks[$block_id]["nodes"][] = array("block_id" => $block_id, "type" => "IMG", "width" => $bg_width, "height" => $bg_height,
															"offset_x" => $x + $bg_x, "offset_y" => $y + $bg_y, "file" => $border_pic_file,
															"align" => "LT", "link" => "");
			}
			else {
				// Create a Rect Node
				$this->blocks[$block_id]["nodes"][] = array("block_id" => $block_id, "type" => "RECT", "width" => $bg_width, "height" => $bg_height,
															"offset_x" => $x + $bg_x, "offset_y" => $y + $bg_y, "fill_color" => $border_color,
															"border_width" => $border_width, "border_color" => $border_color);
			}
			// Add Image
			$pic_x = $bg_x + $border_width;
			$pic_y = $bg_y + $border_width;
			$this->blocks[$block_id]["nodes"][] = array("block_id" => $block_id, "type" => "IMG", "width" => $pic_width, "height" => $pic_height,
														"offset_x" => $x + $pic_x, "offset_y" => $y + $pic_y, "file" => $file,
														"align" => "LT", "link" => $link);
		}
		else {
			// No Border
			switch($float) {
				case "left" : {
					$pic_x = 0;
					$pic_y = 0;
					$used_width = $pic_width;
					$used_height = $pic_height;
					break;
				}
				case "right" : {
					$pic_x = $available_width - $pic_width;
					$pic_y = 0;
					$used_width = $pic_width;
					$used_height = $pic_height;
					break;
				}
				case "center" : {
					$pic_x = ($available_width - $pic_width) / 2;
					$pic_y = 0;
					$used_width = $pic_width;
					$used_height = $pic_height;
					break;

				}
				case "fill" : {
					$pic_x = ($available_width - $pic_width) / 2;
					$pic_y = ($available_height - $pic_height) / 2;
					$used_width = $available_width;
					$used_height = $available_height;
					break;
				}
				default : {
					// same as "fill"
					$pic_x = ($available_width - $pic_width) / 2;
					$pic_y = ($available_height - $pic_height) / 2;
					$used_width = $available_width;
					$used_height = $available_height;
					break;
				}
			}
			$this->blocks[$block_id]["nodes"][] = array("block_id" => $block_id, "type" => "IMG", "width" => $pic_width, "height" => $pic_height,
														"offset_x" => $x + $pic_x, "offset_y" => $y + $pic_y, "file" => $file,
														"align" => "LT", "link" => $link);
		}

		$this->UpdateNodeArea($block_id, $used_width, $used_height, $float, $spacing);
	}

	// Rotate Function from fpdf Rotations code by Olivier
	function Rotate($angle, $x = -1, $y = -1) {
		if ($x == -1)
			$x = $this->x;
		if ($y == -1)
			$y = $this->y;
	    if ($this->angle != 0 )
	        $this->_out('Q');
	    $this->angle = $angle;
	    if ($angle != 0) {
	        $angle *= M_PI/180;
	        $c = cos($angle);
	        $s = sin($angle);
	        $cx = $x * $this->k;
	        $cy = ($this->h - $y) * $this->k;
	        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
	    }
	}

	function _endpage() {
	    if ($this->angle != 0) {
	        $this->angle = 0;
	        $this->_out('Q');
	    }
	    parent::_endpage();
	}

	// Adjust the Node X, Y co-ordinates and set set Cos and Sin for use by the Rotate and Print Function
	// All the functions assume that the block will be printed from the same x, y co-ordinates after setting the angle
	function mapXYD($block, $x, $y) {
		// No orientation flag set. Nothing to do
		if ( ! isset($block['orientation']))
			return [$x, $y, 0];

		switch ($block['orientation']) {
			case 'F' : {
				// Flip - Rotate 180 degree
				// The Block's X & Y are shifted to the diagonally opposite corner
				// 		and the contest is printed with that x,y as the pivot point with 180 degree rotation
				$rotated_block_x = $block['offset_x'] + $block['width'];
				$rotated_block_y = $block['offset_y'] + $block['height'];
				// Place the node at the same co-ordinate from the corner of the bloack as it was before
				$node_x = $rotated_block_x - ($x - $block['offset_x']);
				$node_y = $rotated_block_y - ($y - $block['offset_y']);
				return [$node_x, $node_y, 180];
				// return [$x, $y, 180];
			}
			case 'L' : {	// Rotate Left 90 degrees - To be implemented
				return [$x, $y, 0];
			}
			case 'R' : {	// Rotate Right 90 degrees - To be implemented
				return [$x, $y, 0];
			}
			default : {		// For everything else do nothing
				return [$x, $y, 0];
			}
		}
	}

	function PrintAllBlocks() {
		foreach($this->blocks as $block_id => $block)
			$this->PrintBlock($block_id);
	}

	function PrintBlock($block_id) {
		$block = $this->blocks[$block_id];

		// debug_dump("BLOCK", $block, __FILE__, __LINE__);
		// Adjust Block co-ordinates based on orientation
		// $this->RotateBlock($block_id);

		// Print Rectangle if required
		if ($block["box"] != "") {
			list ($fill_color, $border_width, $border_color) = explode("|", $block['box']);
			$rect_style = "";
			if ($fill_color != "") {
				$this->SetHexFillColor($fill_color);
				$rect_style .= "F";
			}
			if ($border_width != "") {
				$this->SetLineWidth($border_width);
				$this->SetHexDrawColor($border_color);
				$rect_style .= "D";
			}
			list ($offset_x, $offset_y, $angle) = $this->mapXYD($block, $block['offset_x'], $block['offset_y']);
			$this->Rotate($angle, $offset_x, $offset_y);
			$this->Rect($offset_x, $offset_y, $block["width"], $block["height"], $rect_style );
			$this->Rotate(0);
		}

		// Print each node, types = IMG, TXT, RECT
		foreach ($block["nodes"] as $node) {
			switch ($node['type']) {
				case 'RECT': { $this->PrintRectNode($node); break; }
				case 'IMG' : { $this->PrintImgNode($node); break; }
				case 'TXT' : { $this->PrintTextNode($node); break; }
			}
		}
	}

	function PrintRectNode ($node) {
		$block = $this->blocks[$node['block_id']];

		// Re-map co-ordinates based on rotation settings

		if ($node["fill_color"] != "" || $node["border_width"] > 0) {
			$rect_style = "";
			if ($node["fill_color"] != "") {
				$this->SetHexFillColor($node["fill_color"]);
				$rect_style .= "F";
			}
			if ($node["border_width"] > 0) {
				$this->SetLineWidth($node['border_width']);
				$this->SetHexDrawColor($node['border_color']);
				$rect_style .= "D";
			}

			list ($offset_x, $offset_y, $angle) = $this->mapXYD($block, $node["offset_x"], $node["offset_y"]);
			$this->Rotate($angle, $offset_x, $offset_y);
			$this->Rect($offset_x, $offset_y, $node["width"], $node["height"], $rect_style );
			$this->Rotate(0);
		}

	}

	function PrintImgNode($node) {
		$block = $this->blocks[$node['block_id']];
		list ($offset_x, $offset_y, $angle) = $this->mapXYD($block, $node["offset_x"], $node["offset_y"]);
		$this->Rotate($angle, $offset_x, $offset_y);

		// debug_dump("block", $block, __FILE__, __LINE__);
		// debug_dump("rotated_x", $offset_x, __FILE__, __LINE__);
		// debug_dump("rotated_y", $offset_y, __FILE__, __LINE__);

		//$this->PrintThumbnail($node['offset_x'], $node['offset_y'], $node['file'], $node["width"], $border, $node['align'], $node['link'] );
		if (isset($node['link']) && $node['link'] != "")
			$this->Image($node['file'], $offset_x, $offset_y, $node['width'], $node['height'], NULL, $node['link']);
		else
			$this->Image($node['file'], $offset_x, $offset_y, $node['width'], $node['height']);

		$this->Rotate(0);
	}

	function PrintTextNode($node) {
		$block = $this->blocks[$node['block_id']];
		$orientation = (isset($block['orientation']) ? $block['orientation'] : "N");
		list ($offset_x, $offset_y, $angle) = $this->mapXYD($block, $node["offset_x"], $node["offset_y"]);
		// $this->Rotate($angle, $offset_x, $offset_y);

		// debug_dump("block", $block, __FILE__, __LINE__);
		// debug_dump("rotated_x", $offset_x, __FILE__, __LINE__);
		// debug_dump("rotated_y", $offset_y, __FILE__, __LINE__);

		list($font_family, $font_style, $font_size, $font_color) = explode("|", $node['font']);
		$this->PrintFiller(implode("|", array($font_family, $font_style)), $font_size, $font_color,
							$offset_x, $offset_y, $node['width'], $this->PtsToUnits($font_size * $node['line_spacing']), $node['align'], $node['text'], $angle);
		// $this->PrintFiller(implode("|", array($font_family, $font_style)), $font_size, $font_color,
		// 					$node['offset_x'], $node['offset_y'], $node['width'], $font_size * $node['line_spacing'], $node['align'], $node['text']);

		// $this->Rotate(0);
	}

}


?>

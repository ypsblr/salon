<?php
//
// share_img_lib - Functions to generate  social share image
//
include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/lib.php");

include_once($_SERVER['DOCUMENT_ROOT'] . '/PHPImageWorkshop/ImageWorkshop.php');

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;

class ShareImageException {
	function __construct($errmsg, $file, $line, $die = false) {
		// parent::__contruct($errmsg);
		debug_dump("ShareImage Exception", $errmsg, $file, $line);
		if ($die)
			die($errmsg);
	}
}

class ShareImage {
    // Configuration Variables
    protected $poster_background = "";
	protected $poster_background_color = "000000";
    protected $poster_width = 1080;
    protected $poster_height = 1080;

	protected $poster;

	// All areas will hav x,y position and width and height
	protected $thumbnail_area = [];		// Canvas to fill with thumbnails
	protected $thumbnail_gap = 2;

	protected $avatar_area = [];

	protected $profile_area = [];
	protected $profile_name_font = array("font" => "PetitaBold.ttf", "size" => 36, "color" => "FFFFFF" );
	protected $profile_honors_font = array("font" => "PetitaMedium.ttf", "size" => 16, "color" => "FFFFFF" );

	protected $award_area = [];
	protected $pic_award_font = array("font" => "PetitaBold.ttf", "size" => 36, "color" => "FFFFFF" );
	protected $spl_award_font = array("font" => "PetitaBold.ttf", "size" => 36, "color" => "1F8544" );

	protected $custom_areas = [];

	protected $current_font = array("font" => "PetitaMedium.ttf", "size" => 16, "color" => "FFFFFF" );

	protected $lines = [];

	protected $last_error = "";
	protected $last_warning = "";

    function __construct($base_img, $width = 0, $height = 0) {
		if ($width == 0 || $height == 0) {
			$this->last_error = "Attempt to create a ShareImage with incorrect paramaters";
			throw new ShareImageException($this->last_error, __FILE__, __LINE__, true);
		}
		// Determine width and height of base image
		if (file_exists($base_img)) {
			list($img_width, $img_height, $img_type) = getimagesize($base_img);
			if ($img_type != "2" && $img_type != "3") {  // 2 - JPG,  3 - PNG
				$this->last_error = "Unsupported image type for " . $base_img;
				throw new ShareImageException($this->last_error, __FILE__, __LINE__, true);
			}
			// Save
	        $this->poster_background = $base_img;
			$this->poster_width = ($width == 0 ? $img_width : $width);
			$this->poster_height = ($height == 0 ? $img_height : $height);
		}
		else {
			$this->poster_background_color = $base_img;
			$this->poster_width = $width;
			$this->poster_height = $height;
		}

		// Initiate ImageWorkshop
		if ($this->poster_background != "") {
			$this->poster = ImageWorkshop::InitFromPath($this->poster_background);
			// Resize image to height and width if parameters are different
			if ( ($width != 0 && $width != $img_width) || ($height != 0 && $height != $img_height) ) {
				$this->$last_warning = "Resizing the background image";
				$this->poster->resizeInPixel($this->poster_width, $this->poster_height, true);
				throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			}
		}
		else {
			$this->poster = ImageWorkshop::initVirginLayer($this->poster_width, $this->poster_height, $this->poster_background_color);
		}
    }

	// Create Areas
	function createThumbnailArea ($x, $y, $width, $height, $thumbnail_gap = 2) {
		$has_warning = false;
		if ($x < 0 || $x > $this->poster_width || $y < 0 || $y > $this->poster_height ||
				($x + $width) > $this->poster_width || ($y + $height) > $this->poster_height ) {
			$this->last_warning = "Thumbnail container extends beyond the poster edges";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			$has_warning = true;
		}
		$this->thumbnail_area = array("x" => $x, "y" => $y, "width" => $width, "height" => $height);
		$this->$thumbnail_gap = $thumbnail_gap;
		return (! $has_warning);
	}

	function createAvatarArea ($x, $y, $width, $height) {
		$has_warning = false;
		if ($x < 0 || $x > $this->poster_width || $y < 0 || $y > $this->poster_height ||
				($x + $width) > $this->poster_width || ($y + $height) > $this->poster_height ) {
			$this->last_warning = "Avatar container extends beyond the poster edges";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			$has_warning = true;
		}
		$this->avatar_area = array("x" => $x, "y" => $y, "width" => $width, "height" => $height);
		return (! $has_warning);
	}

	function createProfileArea ($x, $y, $width, $height, $name_font, $honors_font) {
		$has_warning = false;
		if ($x < 0 || $x > $this->poster_width || $y < 0 || $y > $this->poster_height ||
				($x + $width) > $this->poster_width || ($y + $height) > $this->poster_height ) {
			$this->last_warning = "Profile container extends beyond the poster edges";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			$has_warning = true;
		}
		$this->profile_area = array("x" => $x, "y" => $y, "width" => $width, "height" => $height);
		$this->profile_name_font = $name_font;
		$this->profile_honors_font = $honors_font;
		return (! $has_warning);
	}

	function createAwardArea ($x, $y, $width, $height, $pic_font, $spl_font) {
		$has_warning = false;
		if ($x < 0 || $x > $this->poster_width || $y < 0 || $y > $this->poster_height ||
				($x + $width) > $this->poster_width || ($y + $height) > $this->poster_height ) {
			$this->last_warning = "Award container extends beyond the poster edges";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			$has_warning = true;
		}
		$this->award_area = array("x" => $x, "y" => $y, "width" => $width, "height" => $height);
		$this->pic_award_font = $pic_font;
		$this->spl_award_font = $spl_font;
		return (! $has_warning);
	}

	function addCustomArea ($x, $y, $width, $height, $font = []) {
		$has_warning = false;
		if ($x < 0 || $x > $this->poster_width || $y < 0 || $y > $this->poster_height ||
				($x + $width) > $this->poster_width || ($y + $height) > $this->poster_height ) {
			$this->last_warning = "Custom container extends beyond the poster edges";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			$has_warning = true;
		}
		$this->custom_areas[] = array("x" => $x, "y" => $y, "width" => $width, "height" => $height, "font" => $font);
		return (sizeof($this->custom_areas) - 1);	// Return Index
	}

	// Set current font
	function setCurrentFont($font, $font_size, $font_color) {
		if (empy($font) || empty($font_color) || $font_size <= 0) {
			$this->last_warning = "Incorrect font settings";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
			return;
		}

		$this->currentFont = array("font" => $font, "size" => $font_size, "color" => $font_color);
	}

	//
	//
	// Create and Return a Text Layer
	//
	private function createTextLayer($text, $area, $size = 0, $color = "", $font = "", $position = "MM" ) {
		if ($font == "")
			$fontPath = $_SERVER['DOCUMENT_ROOT'] . "/PHPImageWorkshop/font/" . $this->current_font["font"];
		else
			$fontPath = $_SERVER['DOCUMENT_ROOT'] . "/PHPImageWorkshop/font/" . $font;
		$fontColor = ($color == "" ? $this->current_font["color"] : $color);
		$textRotation = 0;
		$fontSize = ($size == 0 ? $this->current_font["size"] : $size);
		$text_layer = ImageWorkshop::initTextLayer($text, $fontPath, $fontSize, $fontColor, $textRotation);
		if ($text_layer->getWidth() > $area['width'])
			$text_layer->resizeByLargestSideInPixel($area['width'], true);

		return $text_layer;
	}

	//
	// Create and return an image Layer for a specific width
	//
	private function createImageLayerByWidth($file, $width, $border = 0, $border_color = "A0A0A0") {
		// Get Image Parameters
		if (file_exists($file)) {
			list($img_width, $img_height, $img_type) = getimagesize($file);
			if ($img_type != "2" && $img_type != "3")  // 2 - JPG,  3 - PNG
				return ImageWorkshop::initVirginLayer($width, 10);	// return empty layer
		}

		// Determine the height of the image by the width and create an empty layer
		$height = $width / $img_width * $img_height;
		$layer = ImageWorkshop::initVirginLayer($width, $height, ($border > 0 ? $border_color : null));

		// Create an Image Layer
		$img_layer = ImageWorkshop::initFromPath($file);

		// Resize Image Layer to fit the dimensions of the box
		if ($img_width > ($width - ($border * 2)) || $img_height > ($height - ($border * 2)) ) {
			$img_layer->resizeInPixel($width - ($border * 2), $height - ($border * 2), true, 0, 0, 'MM');
		}

		// Place the Image Layer on Empty Layer
		$layer->addLayerOnTop($img_layer, 0, 0, "MM");
		$layer->mergeAll();

		return $layer;
	}

	//
	// Create and return an image Layer
	//
	private function createImageLayer($file, $box, $border = 0, $border_color = "A0A0A0") {
		// Create an empty layer
		$layer = ImageWorkshop::initVirginLayer($box['width'], $box['height'], ($border > 0 ? $border_color : null));	// return empty layer

		// Get Image Parameters
		if (file_exists($file)) {
			list($img_width, $img_height, $img_type) = getimagesize($file);
			if ($img_type != "2" && $img_type != "3")  // 2 - JPG,  3 - PNG
				return $layer;	// return empty layer
		}

		// Create an Image Layer
		$img_layer = ImageWorkshop::initFromPath($file);

		// Resize Image Layer to fit the dimensions of the box
		if ($img_width > ($box['width'] - ($border * 2)) || $img_height > ($box['height'] - ($border * 2)) ) {
			$img_layer->resizeInPixel($box['width'] - ($border * 2), $box['height'] - ($border * 2), true, 0, 0, 'MM');
		}

		// Place the Image Layer on Empty Layer
		$layer->addLayerOnTop($img_layer, 0, 0, "MM");
		$layer->mergeAll();

		return $layer;
	}

	// Normalized Width is computed for each picture assuming the height of each picture to be the same
	// This function list totals that value
	private function normalizedTotalWidth($pics) {
		$total_normalized_width = 0;
		foreach($pics as $pic) {
			$total_normalized_width += $pic['normalized_width'];
		}
		return $total_normalized_width;
	}

	// Normalie all pictures on lines to match the thumbnail area height
	private function createLines($picfiles) {
		if (sizeof($picfiles) == 0) {
			$this->$last_warning = "No pictures provided for creating thumbnail area";
			throw new ShareImageException($this->last_warning, __FILE__, __LINE__);
		}
		// Determine number of lines that the picture will contain
		// Present logic assumes a square or near square canvas space
		// Need to come up with a logic to handle different types of areas
		$num_lines = round(sqrt(sizeof($picfiles)), 0);
		$num_pics_per_line = ceil(sizeof($picfiles) / $num_lines);			// since square root of number of pictures
	    $this->lines = [];

		// Distribute pictures across lines starting from the last picture
		$pic_index = $num_pics_per_line;
		$line = [];		// current line
		$line['pics'] = [];
		for ($index = sizeof($picfiles) - 1; $index >= 0 ; -- $index) {
			// Calculate dimensions and create an array to be prepended to current line
			$pic = [];
			$pic['picfile'] = $picfiles[$index];

			// add to the picture at the start of the array
			array_splice($line['pics'], 0, 0, [$pic]);
			// $line['pics'][] = $pic;

			// Start a next line when it is full
			-- $pic_index;
			if ($pic_index == 0) {
				// Add current line at the start of the lines array
				array_splice($this->lines, 0, 0, [$line]);
				// $this->lines[] = $line;
				$line = [];
				$line['pics'] = [];
				$pic_index = $num_pics_per_line;
			}
		}
		// Prepend the last assembled line
		if (sizeof($line['pics']) > 0) {
			array_splice($this->lines, 0, 0, [$line]);
			// $this->lines[] = $line;
		}
		// debug_dump("createLines::num_lines", $num_lines, __FILE__, __LINE__);
		// debug_dump("createLines::num_pics_per_line", $num_pics_per_line, __FILE__, __LINE__);
		// debug_dump("createLines::ines", $this->lines, __FILE__, __LINE__);
	}

	// Determine picture heioghts and widths; Calculate widths against a uniform height
	private function computeDimensions() {
		for ($ldx = 0; $ldx < sizeof($this->lines); ++$ldx) {
			// Calculate dimensions for each picture
			for ($pdx = 0; $pdx < sizeof($this->lines[$ldx]['pics']); ++$pdx) {
				$pic = $this->lines[$ldx]['pics'][$pdx];
				// Get the actual width and height
				list($img_width, $img_height) = getimagesize($pic['picfile']);
				$pic['img_width'] = $img_width;
				$pic['img_height'] = $img_height;

				// Compute arbitray width assuming the height is uniformly equal to the height of the thumbnail area
				$pic['normalized_height'] = $this->thumbnail_area['height'];
				$pic['normalized_width'] = floor($img_width * $this->thumbnail_area['height'] / $img_height);

				// Placeholder for calculating actual width and height
				$pic['print_width'] = 0;
				$pic['print_height'] = 0;

				$this->lines[$ldx]['pics'][$pdx] = $pic;
			}
		}
	}

	// Fit all lines to a maximum height and widths permissible
	private function fitLineWidths() {
		// Scale lines so that they all fit within the allocted height and width for each line
		//          Determine the adjusted height for each line
		$available_canvas_height = $this->thumbnail_area['height'] - ($this->thumbnail_gap * (sizeof($this->lines) - 1));	// gap between lines
		$available_line_height = floor($available_canvas_height / sizeof($this->lines));	// assume to be of same height
		$height_resize_factor = $available_line_height / $this->thumbnail_area['height'];		// factor by which lines should be scaled down

		for ($idx = 0; $idx < sizeof($this->lines); ++ $idx) {
			if (sizeof($this->lines[$idx]['pics']) > 0) {
				// Determine the width required for each line based on width of each picture adjusted to a common height
				$total_img_width = $this->normalizedTotalWidth($this->lines[$idx]['pics']);

				// Find out the width availale after subtracting gaps required between the pictures
				$available_line_width = $this->thumbnail_area['width'] - ($this->thumbnail_gap * (sizeof($this->lines[$idx]['pics']) - 1));

				// Determine scaling factor to fit the line within the available height and width
				$width_resize_factor = $available_line_width / $total_img_width;
				$resize_factor = min($width_resize_factor, $height_resize_factor);		// Fit within height and width
				$used_width = 0;
				$used_height = 0;
				for ($pic_idx = 0; $pic_idx < sizeof($this->lines[$idx]['pics']); ++$pic_idx) {
					// Width
					$print_width = floor($this->lines[$idx]['pics'][$pic_idx]['normalized_width'] * $resize_factor);
					$this->lines[$idx]['pics'][$pic_idx]['print_width'] = $print_width;
					$used_width += $print_width;
					// Height - Should be of uniform height
					$print_height = floor($this->lines[$idx]['pics'][$pic_idx]['normalized_height'] * $resize_factor);
					$this->lines[$idx]['pics'][$pic_idx]['print_height'] = $print_height;
					$used_height = max($used_height, $print_height);
				}
				$this->lines[$idx]['width'] = $used_width;
				$this->lines[$idx]['height'] = $used_height;
			}
		}
	}

	// Expand lines that have width to expand
	private function maximizeLineDimensions() {
		// Usable height after removing inter-line gaps
		$available_canvas_height = $this->thumbnail_area['height'] - ($this->thumbnail_gap * (sizeof($this->lines) - 1));	// gap between lines

		// How much of height has been used
		$used_canvas_height = 0;
		foreach ($this->lines as $line)
			$used_canvas_height += $line['height'];

		// If the lines are not occupying the entire height of the thumbnail area,
		//		Expand lines which are having width left perhaps because of less number of pictures or presence of vertical pictures
		if ($used_canvas_height < $available_canvas_height) {
			// How much of height can we grow
			$free_canvas_height = $available_canvas_height - $used_canvas_height;

			// Start expanding lines from top
			// Usually the top row does not have all the required number of pictures
			for ($idx = 0; $idx < sizeof($this->lines); ++ $idx) {
				$available_line_width = $this->thumbnail_area['width'] - ($this->thumbnail_gap * (sizeof($this->lines[$idx]['pics']) - 1));
				// Expand if there is space left to grow
				if ($this->lines[$idx]['width'] < $available_line_width) {
					// Determine how much we can grow on width and height and use the least of the two
					$width_resize_factor = $available_line_width / $this->lines[$idx]['width'];
					$height_resize_factor = ($this->lines[$idx]['height'] + $free_canvas_height) / $this->lines[$idx]['height'];
					$resize_factor = min($width_resize_factor, $height_resize_factor);

					// Recompute the line height and width and picture height and width
					$used_width = 0;
					$used_height = 0;
					for ($pic_idx = 0; $pic_idx < sizeof($this->lines[$idx]['pics']); ++$pic_idx) {
						// Width
						$print_width = floor($this->lines[$idx]['pics'][$pic_idx]['print_width'] * $resize_factor);
						$this->lines[$idx]['pics'][$pic_idx]['print_width'] = $print_width;
						$used_width += $print_width;
						// Height
						$print_height = floor($this->lines[$idx]['pics'][$pic_idx]['print_height'] * $resize_factor);
						$this->lines[$idx]['pics'][$pic_idx]['print_height'] = $print_height;
						$used_height = max($used_height, $print_height);
					}
					$this->lines[$idx]['width'] = $used_width;
					$free_canvas_height -= ($used_height - $this->lines[$idx]['height']);	// Take away additional height added
					$this->lines[$idx]['height'] = $used_height;
				}
			}
		}

	}

	private function renderThumbnailArea() {
		// Render thumbnails
	    // Add each line as a separate layer and place the resized thumbnails on it.
	    // Then add the line layer to thumbnail group layer
	    // First determine the total width and height required to place all thumbnails
	    $thumbnail_area_width = 0;		// Maximum Width required
	    $thumbnail_area_height = 0;		// Total Height required
	    foreach ($this->lines as $line) {
	        $thumbnail_area_width = max($thumbnail_area_width, $line['width'] + (sizeof($line['pics']) -1) * $this->thumbnail_gap);
	        $thumbnail_area_height += $line['height'];
	    }
	    $thumbnail_area_height += ($this->thumbnail_gap * (sizeof($this->lines) - 1));

		// Create a Layer for the overall area
	    $thumbnail_area_layer = ImageWorkshop::initVirginLayer($thumbnail_area_width, $thumbnail_area_height);

		// Place thumbnails on the image
	    $thumbnail_y = 0;
	    for ($idx = 0; $idx < sizeof($this->lines); ++ $idx) {
	        // Create a layer for the size of
	        $line_layer = ImageWorkshop::initVirginLayer($this->lines[$idx]['width'], $this->lines[$idx]['height']);
	        $line_x = 0;
	        $line_y = 0;
	        foreach ($this->lines[$idx]['pics'] as $pic) {
				if (is_array($pic) && sizeof($pic) > 0) {
		            $img_file = $pic['picfile'];
		            $pic_layer = $this->createImageLayerByWidth($img_file, $pic['print_width']);
		            $line_layer->addLayerOnTop($pic_layer, $line_x, $line_y, 'LT');
		            $line_layer->mergeAll();   // Merge and free up pic_layer
		            $line_x += ($pic['print_width'] + $this->thumbnail_gap);
				}
	        }
	        // Add the line Layer to thumbnail_area_layer and merge
	        // Find x position to center the layer
			// $thumbnail_x = ($this->thumbnail_area['width'] - $this->lines[$idx]['width']) / 2;
	        $thumbnail_x = ($thumbnail_area_width - $this->lines[$idx]['width']) / 2;
	        $thumbnail_area_layer->addLayerOnTop($line_layer, $thumbnail_x, $thumbnail_y, 'LT');
	        $thumbnail_area_layer->mergeAll();
	        $thumbnail_y += ($this->lines[$idx]['height'] + $this->thumbnail_gap);
	    }
		$canvas_layer = ImageWorkshop::initVirginLayer($this->thumbnail_area['width'], $this->thumbnail_area['height']);
	    $canvas_layer->addLayerOnTop($thumbnail_area_layer, 0, 0, "MM");
	    $canvas_layer->mergeAll();

		$this->poster->addLayerOnTop($canvas_layer, $this->thumbnail_area['x'], $this->thumbnail_area['y'], "LT");
		$this->poster->mergeAll();
	}

	// Add Partner Logo and website
	function addPartnerLogo($img_file, $img_width, $url, $x, $y, $width, $height) {
		$canvas_layer = ImageWorkshop::initVirginLayer($width, $height);
		$partner_logo_layer = $this->createImageLayerByWidth($img_file, $img_width);
		$url_layer =  $this->createTextLayer($url, ["width" => $width, "height" => 20], 0, "", "", "RB" );

		$canvas_layer->addLayerOnTop($partner_logo_layer, 0, 0, "RT");
		$canvas_layer->addLayerOnTop($url_layer, 0, 0, "RB");

		$this->poster->addLayerOnTop($canvas_layer, $x, $y, "LT");
		$this->poster->mergeAll();
	}

	// Create Thumbnail Area
	function addThumbnails($picfiles) {

		// Step 1 - Distribute Pictures across lines
		$this->createLines($picfiles);

		// Step 2 - Determine heights and caculate widths assuming uniform height - thumbnail area height
		$this->computeDimensions();
		// debug_dump("Compute Dimensions", $this->lines, __FILE__, __LINE__);

		// Step 3 - Fit lines within maximum widths and heights
		$this->fitLineWidths();
		// debug_dump("Fit Width", $this->lines, __FILE__, __LINE__);

		// Step 4 - Expand lines that have free width
		$this->maximizeLineDimensions();
		// debug_dump("Maximize Dimensions", $this->lines, __FILE__, __LINE__);

		// Step 5 - Render thumbnails on the thumbnail area
		$this->renderThumbnailArea();

	}

	// Create Profile
	function addProfile($profile_name, $honors) {
		//
		// FILL PROFILE AREA
		//
		$profile_area_height = 0;

		// Add Name & Honors Text Layer right in the center
		$name_layer = $this->createTextLayer($profile_name, $this->profile_area, $this->profile_name_font['size'],
										$this->profile_name_font['color'], $this->profile_name_font['font'], "MM");
		$profile_area_height += ($name_layer->getHeight() * 1.2);

		if ($honors != "") {
			$honors_layer = $this->createTextLayer($honors, $this->profile_area, $this->profile_honors_font['size'],
											$this->profile_honors_font['color'], $this->profile_honors_font['font'], "MM");
			$profile_area_height += $honors_layer->getHeight();
		}

		// Resize to fit height
		if ($profile_area_height > $this->profile_area['height']) {
			$resize_percent = round($this->profile_area['height'] / $profile_area_height * 100, 0);
			$name_layer->resizeInPercent(null, $resize_percent, true);
			if (isset($honors_layer))
				$honors_layer->resizeInPercent(null, $resize_percent, true);
			$profile_area_height = $this->profile_area['height'];
		}

		$profile_area_layer = ImageWorkshop::initVirginLayer($this->profile_area['width'], $this->profile_area['height']);

		$y = ($this->profile_area['height'] - $profile_area_height) / 2;     // Center

		$profile_area_layer->addLayerOnTop($name_layer, 0, $y, "MT");
		$y += ($name_layer->getHeight() * 1.2);

		if (isset($honors_layer))
			$profile_area_layer->addLayerOnTop($honors_layer, 0, $y, "MT");

		$profile_area_layer->mergeAll();

		// Add Name Group to the image
		$this->poster->addLayerOnTop($profile_area_layer, $this->profile_area['x'], $this->profile_area['y'], "LT");
		$this->poster->mergeAll();
	}

	// Create Awards
	function addAwards($pic_award_text, $spl_award_text = "") {

		$award_area_height = 0;
		// Special Award
		if ($spl_award_text != "") {
			$spl_award_layer = $this->createTextLayer($spl_award_text, $this->award_area, $this->spl_award_font['size'],
											$this->spl_award_font['color'], $this->spl_award_font['font'], "MM");
			$award_area_height += ($spl_award_layer->getHeight() * 1.2);

		}

		// Picture Award
		$pic_award_layer = $this->createTextLayer($pic_award_text, $this->award_area, $this->pic_award_font['size'],
										$this->pic_award_font['color'], $this->pic_award_font['font'], "MM");
		$award_area_height += $pic_award_layer->getHeight();

		// Resize to fit height
		if ($award_area_height > $this->award_area['height']) {
			$resize_percent = round($this->award_area['height'] / $award_area_height * 100, 0);
			if (isset($spl_award_layer))
				$spl_award_layer->resizeInPercent(null, $resize_percent, true);
			$pic_award_layer->resizeInPercent(null, $resize_percent, true);
			$award_area_height = $this->award_area['height'];
		}

		$award_group_layer = ImageWorkshop::initVirginLayer($this->award_area['width'], $this->award_area['height']);
		$y = ($this->award_area['height'] - $award_area_height) / 2;     // Center
		if (isset($spl_award_layer)) {
			$award_group_layer->addLayerOnTop($spl_award_layer, 0, $y, "MT");
			$y += ($spl_award_layer->getHeight() * 1.2);
		}
		$award_group_layer->addLayerOnTop($pic_award_layer, 0, $y, "MT");

		$award_group_layer->mergeAll();

		// Add Name Group to the image
		$this->poster->addLayerOnTop($award_group_layer, $this->award_area['x'], $this->award_area['y'], "LT");
		$this->poster->mergeAll();
	}

	function saveShareImg($save_folder, $savefile) {
		// Write second image
		$this->poster->save($save_folder, $savefile, true, null, $imageQuality = 85, $interlace = false);
	}

	// END of ShareImage class
}

?>

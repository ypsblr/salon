<?php
//
// image_merge - Generic utility to merge a background image with text from SQL
//
include_once($_SERVER['DOCUMENT_ROOT'] . '/admin/inc/connect.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/admin/inc/lib.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/PHPImageWorkshop/ImageWorkshop.php');

// Include support for cleaned up honors list
include_once($_SERVER['DOCUMENT_ROOT'] . '/admin/inc/honors.php');

define ("FONT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/PHPImageWorkshop/font");

ini_set("memory_limit", "256M");

use PHPImageWorkshop\ImageWorkshop as ImageWorkshop;

function make_path($path) {
	if (is_dir(dirname($path))) {
		if (! is_dir($path))
			mkdir($path);
	}
	else
		make_path(dirname($path));
}

function drawColor(&$canvas, $color) {
	if ($color == "")
		return imagecolorallocatealpha($canvas, 0, 0, 0, 127);	// set transparent background (first call to imagecolorallocate)
	else {
		sscanf($color, "%02x%02x%02x%02x", $red, $green, $blue, $opacity);
		// If no opacity os specified, then it is opaque
		if (empty($opacity))
			$alpha = 0;
		else
			$alpha = round(127 * ((100 - $opacity) / 100), 0);
		return imagecolorallocatealpha($canvas, $red, $green, $blue, $alpha);
	}
}

//
// Add Border Layer
//
function addRectangle(&$layer, $x, $y, $width, $height, $line_thickness, $line_color, $fill_color = "") {
	// Create an empty canvas
	$canvas = imagecreate($width, $height);
	// Create Background based on fill color
	$bgcolor = drawColor($canvas, $fill_color);

	// Draw series of rectangles to match border size
	$line = drawColor($canvas, $line_color);
	for ($i = 0; $i < $line_thickness; ++ $i) {
		imagerectangle($canvas, $i, $i, $width - $i - 1, $height - $i - 1, $line);
	}
	$rect_layer = ImageWorkshop::initFromResourceVar($canvas);
	$layer->addLayerOnTop($rect_layer, $x, $y, "LT");
	$layer->mergeAll();
}

//
// Add Fill Layer
//
function addFill(&$layer, $width, $height, $x, $y, $fill_color) {
	$fill_layer = ImageWorkshop::initVirginLayer($width, $height, $fill_color);
	$layer->addLayerOnTop($fill_layer, $x, $y, "LT");
	$layer->mergeAll();
}

// Return calculated co-ordinates based on position parameters
// The returned co-ordinates is based on x = left to Right and y = Top to Bottom
// Must be used with "LT" for proper positioning
function calculatePrintPosition($layer, $position, $x, $y, $width, $height) {
	// Adjust position parameters to be able to position using "LT"
	// Adjust 'x' position
	switch(substr($position, 0, 1)) {
		case 'M' : {
			$x += (($width - $layer->getWidth()) / 2);
			break;
		}
		case 'R' : {
			$x += ($width - $layer->getWidth());
			break;
		}
	}
	// Adjust 'y' position
	switch(substr($position, 1, 1)) {
		case 'M' : {
			$y += (($height - $layer->getHeight()) / 2);
			break;
		}
		case 'B' : {
			$y += ($height - $layer->getHeight());
			break;
		}
	}
	return [$x, $y];
}

// Calculate resize width and height to fit within a box
function fitLayerInRect(&$layer, $width, $height, $grow = 1) {
	$layer_width = $layer->getWidth() * $grow;
	$layer_height = $layer->getHeight() * $grow;
	if ( $layer_width > $width || $layer_height > $height ) {
		// Determine the dimension with largest deficit
		if (($layer_width / $width) > ($layer_height / $height)) {
			// Need to constrain img_width to width of the box - order of computation is important
			$layer_height *= ($width / $layer_width);
			$layer_width = $width;
		}
		else {
			// Constrain the height - order of computation is important
			$layer_width *= ($height / $layer_height);
			$layer_height = $height;
		}
		// // Fit Width
		// if ($resize_width > $width) {
		// 	$resize_width = $width;
		// 	$resize_height = round($resize_height * $resize_width / $layer->getWidth(), 0);
		// }
		// // Fit Height
		// if ($resize_height > $height) {
		// 	$resize_height = $height;
		// 	$resize_width = round($resize_width * $resize_height / $layer->getHeight(), 0);
		// }
		// debug_dump("layer", implode(", ", [$layer->getWidth(), $layer->getHeight()]), __FILE__, __LINE__);
		// debug_dump("print", implode(", ", [$print_width, $print_height]), __FILE__, __LINE__);
		// debug_dump("resize", implode(", ", [$resize_width, $resize_height]), __FILE__, __LINE__);
	}
	// Resize to the new dimensions
	$layer->resizeInPixel($layer_width, $layer_height, true);
}

//
// Create and Return a Text Layer
//
function createTextLayer(&$layer, $field) {
	// Create a transparent base layer
	$canvas = ImageWorkshop::initVirginLayer($field['width'], $field['height'], null);

	// Computations
	$position = (isset($field['position']) ? $field['position'] : "LT");
	$border_size = (isset($field['border_size']) ? $field['border_size'] : 0);
	$border_gap = (isset($field['border_gap']) ? $field['border_gap'] : 0);
	$border_space = $border_size + $border_gap;
	$print_x = $border_space;
	$print_y = $border_space;
	$print_width = ($field['width'] - (2 * $border_space));
	$print_height = ($field['height'] - (2 * $border_space));
	$grow = isset($field['grow']) ? $field['grow'] : 1;

	// Fill
	if (isset($field['fill_color']) && $field['fill_color'] != "")
		addFill($canvas, $print_width, $print_height, $print_x, $print_y, $field['fill_color']);

	// Create Text Layer
	$fontPath = FONT_PATH . "/" . $field['font'];
	$fontColor = $field['font_color'];
	$fontSize = $field['font_size'];
	$textRotation = $field['rotate'];
	$text_layer = ImageWorkshop::initTextLayer($field['value'], $fontPath, $fontSize, $fontColor, $textRotation);
	fitLayerInRect($text_layer, $print_width, $print_height, $grow);

	// Adjust position parameters to be able to position using "LT"
	list($print_x, $print_y) = calculatePrintPosition($text_layer, $position, $print_x, $print_y, $print_width, $print_height);

	// For image draw border around the image
	if ($border_space > 0) {
		$border_x = $print_x - $border_space;
		$border_y = $print_y - $border_space;
		$area_width = $text_layer->getWidth() + (2 * $border_space);
		$area_height = $text_layer->getHeight() + (2 * $border_space);
		// debug_dump("border", implode(", ", [$border_x, $border_y, $border_width, $border_height]), __FILE__, __LINE__);
		addRectangle($canvas, $border_x, $border_y, $area_width, $area_height, $border_size, $field['border_color']);
	}

	$canvas->addLayerOnTop($text_layer, $print_x, $print_y, "LT");
	$canvas->mergeAll();

	// Add canvas at the appropriate position
	$layer->addLayerOnTop($canvas, $field['x'], $field['y'], "LT");
	$layer->mergeAll();
}

//
// Create and return an image Layer
//
function createImageLayer(&$layer, $field) {
	// Get Image Parameters
	if (isset($field['value']) && file_exists($field['value'])) {
		list($img_width, $img_height, $img_type) = getimagesize($field['value']);
		if ($img_type != "2" && $img_type != "3") {  // 2 - JPG,  3 - PNG - Don't add other types of files
			return;
		}

		// Create an empty layer for positioning the image
		$canvas = ImageWorkshop::initVirginLayer($field['width'], $field['height'], null);

		// Computations
		$position = (isset($field['position']) ? $field['position'] : "LT");
		$border_size = (isset($field['border_size']) ? $field['border_size'] : 0);
		$border_gap = (isset($field['border_gap']) ? $field['border_gap'] : 0);
		$border_space = $border_size + $border_gap;
		$print_x = $border_space;
		$print_y = $border_space;
		$print_width = ($field['width'] - (2 * $border_space));
		$print_height = ($field['height'] - (2 * $border_space));
		$grow = isset($field['grow']) ? $field['grow'] : 1;

		// Fill
		if (isset($field['fill_color']) && $field['fill_color'] != "")
			addFill($canvas, $print_width, $print_height, $print_x, $print_y, $field['fill_color']);

		// Create an Image Layer
		$img_layer = ImageWorkshop::initFromPath($field['value']);

		// Rotate Layer if specified
		if (isset($field['rotate']) && $field['rotate'] != 0) {
			$img_layer->rotate($field['rotate']);
		}

		// Adjust image to fit withing the area specified
		fitLayerInRect($img_layer, $print_width, $print_height, $grow);

		// Adjust position parameters to be able to position using "LT"
		list($print_x, $print_y) = calculatePrintPosition($img_layer, $position, $print_x, $print_y, $print_width, $print_height);

		// For image draw border around the image
		if ($border_space > 0) {
			$border_x = $print_x - $border_space;
			$border_y = $print_y - $border_space;
			$area_width = $img_layer->getWidth() + (2 * $border_space);
			$area_height = $img_layer->getHeight() + (2 * $border_space);
			// debug_dump("border", implode(", ", [$border_x, $border_y, $border_width, $border_height]), __FILE__, __LINE__);
			addRectangle($canvas, $border_x, $border_y, $area_width, $area_height, $border_size, $field['border_color']);
		}

		$canvas->addLayerOnTop($img_layer, $print_x, $print_y, "LT");
		$canvas->mergeAll();

		// Place the Image Layer on the base layer
		$layer->addLayerOnTop($canvas, $field['x'], $field['y'], "LT");
		$layer->mergeAll();
	}
}

// Draw rectangle and add border and fill
function createRectLayer(&$layer, $field) {
	// Ignore any value set
	// Computations
	$border_size = (isset($field['border_size']) ? $field['border_size'] : 0);
	$fill_color = isset($field['fill_color']) ? $field['fill_color'] : "";
	$border_color = isset($field['border_color']) ? $field['border_color'] : "";

	// Create an empty layer for creating the rectangle
	$canvas = ImageWorkshop::initVirginLayer($field['width'], $field['height'], null);

	// Add a rectangle
	addRectangle($canvas, 0, 0, $field['width'], $field['height'], $border_size, $border_color, $fill_color);

	// Rotate Layer if specified
	if (isset($field['rotate']) && $field['rotate'] != 0) {
		$canvas->rotate($field['rotate']);
	}

	// Adjust rectangle to fit withing the area specified
	fitLayerInRect($canvas, $field['width'], $field['height']);

	// Place the Rectangle Layer on the base layer
	$layer->addLayerOnTop($canvas, $field['x'], $field['y'], "LT");
	$layer->mergeAll();

}

// Create an image from a Vector (.svg) file
function createVectorLayer(&$layer, $field) {
	if (isset($field['value']) && file_exists($field['value'])) {
		// Create a PNG from vector
		$vector = new Imagick($field['value']);
		// $vector = new Imagick();
		// $vector->setImageResolution($field['width'], $field['height']);
		// $vector->setBackgroundColor(new ImagickPixel("transparent"));
		// $vector->readImage($field['value']);
		$vector->setImageFormat("png");
		// $vector->setImageResolution($field['width'], $field['height']);
		$vector_image = imagecreatefromstring($vector->getImageBlob());

		// Create a Layer from the created image
		$vector_layer = ImageWorkshop::initFromResourceVar($vector_image);
		$vector_layer->resizeInPixel($field['width'], null, true);
		// Place the Image Layer on the base layer
		$layer->addLayerOnTop($vector_layer, $field['x'], $field['y'], "LT");
		$layer->mergeAll();
	}
}

function renderField(&$layer, $field) {

	switch ($field['type']) {
		case 'text' : {
			createTextLayer($layer, $field);
			break;
		}
		case 'image' : {
			createImageLayer($layer, $field);
			break;
		}
		case 'box' : {
			createRectLayer($layer, $field);
			break;
		}
		case 'vector' : {
			createVectorLayer($layer, $field);
			break;
		}
		default : {
			echo "Unknown field type : " . $field['type'];
			exit;
		}
	}

}

if ( (! empty($_REQUEST['yearmonth'])) && (! empty($_REQUEST['merge'])) ) {

	$admin_yearmonth = $_REQUEST['yearmonth'];
	$merge_script = $_REQUEST['merge'] . ".php";

	include "../inc/$merge_script";
	// if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$admin_yearmonth/$merge_script"))
	// 	include ($_SERVER['DOCUMENT_ROOT'] . "/salons/$admin_yearmonth/$merge_script");
	// else {
	// 	echo "Missing Configuration File !";
	// 	die();
	// }

	// Load Merge Definitions
	$doc = new ImageMerge($DBCON, $_REQUEST);

	$count = 0;
	while ($fields = $doc->getMergeData()) {
		set_time_limit(30);				// Restart 30 second timer for each page

		// Generate Image
		$image = ImageWorkshop::initFromPath($doc->getBackgroundImage());		// Start with a new layer for every record
		foreach ($fields as $field) {
			if ($field['value'] != "")
				renderField($image, $field);
		}

		// Create Folder if required
		$save_folder = $doc->getSaveFolder();
		if (! is_dir($save_folder))
			make_path($save_folder);

		// Save Image
		$picfilename = $doc->getSaveFileName();
		$extension = explode('.', $picfilename);
		$extension = strtolower($extension[count($extension) - 1]);

		if ($extension == "jpg" || $extension == "jpeg")
			$pic = $image->getResult("ffffff");			// Get Merged Image with white background for JPEG
		elseif ($extension == "png")
			$pic = $image->getResult();			// Get Merged Image

		if (method_exists($doc, "getDPI")) {
			$dpi = $doc->getDPI();
			if ($dpi == null || $dpi < 96)
				$dpi = 96;
			elseif ($dpi > 300)
				$dpi = 300;
			imageresolution($pic, $dpi);
		}

		$savefilename = $save_folder . "/" . $picfilename;

		if ($extension == "jpg" || $extension == "jpeg")
			imagejpeg($pic, $savefilename, 85);		// 85% quality
		elseif ($extension == "png")
			imagepng($pic, $savefilename, 1);	// 10% compression

		// $image->save($save_folder, $doc->getSaveFileName(), true, null, $imageQuality = 85, $interlace = false);
		++ $count;
	}

	echo "Generated $count files are under $save_folder folder !";
}
else {
	echo "Invalid Parameters !";
}

?>

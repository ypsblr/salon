<?php

// Return Exif from image file
function exif_exposure_program($exif) {
    if (isset($exif['ExposureProgram'])) {
    	switch ($exif['ExposureProgram']) {
            case 1 : return "Manual";
    		case 2 : return "Program";
    		case 3 : return "Aperture priority";
    		case 4 : return "Shutter Priority";
    		case 5 : return "Creative";
    		case 6 : return "Action";
    		case 7 : return "Low DoF";
    		case 8 : return "High DoF";
    		default : return "";
    	}
    }
    else
        return "";
}

function exif_metering_mode($exif) {
    if (isset($exif['MeteringMode'])) {
    	switch ($exif['MeteringMode']) {
    		case 1 : return "Aerage";
    		case 2 : return "Center Weighted";
    		case 3 : return "Spot";
    		case 4 : return "Multi-Spot";
    		case 5 : return "Multi-Segment";
    		case 6 : return "Partial";
    		default : return "";
    	}
    }
    else
        return "";
}

function exif_light_source($exif) {
    if (isset($exif['LightSource'])) {
        switch($exif['LightSource']) {
            case 1 : return "Daylight";
            case 2 : return "Flourescent";
            case 3 : return "Tungsten";
            case 4 : return "Flash";
            case 9 : return "Daylight";
            case 10 : return "Overcast";
            case 11 : return "Shade";
            default : return "";
        }
    }
    else
        return "Manual WB";
}

function exif_white_balance($exif) {
    if (isset($exif['WhiteBalance'])) {
    	switch ($exif['WhiteBalance']) {
    		case 0 : return "Auto WB";
    		case 1 : return exif_light_source($exif);
    		default : return "";
    	}
    }
    else
        return "";
}

function exif_shutter_speed($exif) {
    if (isset($exif['ExposureTime'])) {
        $val = eval("return " . $exif['ExposureTime'] . ";");
        if (is_numeric($val)) {
            $ss = $val;
            if ($ss >= 1)
                return round($ss, 0) . "s";
            else
                return "1/" . round(1/$ss, 0) . "s";
        }
        return $exif['ExposureTime'] . "s";
    }
    else if (isset($exif['ShutterSpeedValue'])) {
        $val = eval("return " . $exif['ShutterSpeedValue'] . ";");
        if (is_numeric($val)) {
            $ss = $val * $val;
            if ($ss >= 1)
                return "1/" . round($ss, 0) . "s";
            else
                return round(1/$ss, 0) . "s";
        }
        else
            return "";
    }
    else
        return "";
}

function exif_aperture($exif) {
    if (isset($exif['FNumber'])) {
        $val = eval("return " . $exif['FNumber'] . ";");
        if (is_numeric($val)) {
            $fn = round($val, 1);
            return "F" . $fn;
        }
    }
    else if (isset($exif['ApertureValue'])) {
        $val = eval("return " . $exif['ApertureValue'] . ";");
        if (is_numeric($val)) {
            $fn = round(pow(sqrt(2), $val), 1);
            return "F" . $fn;
        }
        else
            return "";
    }
    else
        return "";
}

function exif_iso($exif) {
    if (isset($exif['ISOSpeedRatings']))
        if (is_array($exif['ISOSpeedRatings']))
            return $exif['ISOSpeedRatings'][0];
        else
            return $exif['ISOSpeedRatings'];
    else
        return "";
}

function exif_bias($exif) {
    if (isset($exif['ExposureBiasValue'])) {
        $val = eval("return " . $exif['ExposureBiasValue'] . ";");
        if (is_numeric($val))
            return round($val, 1);
        else
            return "";
    }
    else
        return "";
}

function exif_camera($exif) {
    // if (isset($exif['Make']))
    //     return $exif['Make'] . (isset($exif['Model']) ? " " . $exif['Model'] : '');
    if (isset($exif['Model']))
        return $exif['Model'];
    else
        return "";
}

function exif_lens($exif) {
    if(isset($exif['UndefinedTag:0xA434']))
        return $exif['UndefinedTag:0xA434'];
    else
        return "";
}

function exif_focal_length($exif) {
    if (isset($exif['FocalLength'])) {
        $fl = eval("return " . $exif['FocalLength'] . ";");
        if (is_numeric($fl))
            return round($fl, 0) . "mm";
        else
            return "";
    }
    else
        return "";
}

function exif_flash($exif) {
    $flash = "";
    if (isset($exif['Flash'])) {
        if ($exif['Flash'] & 0x20)
            return "No Flash";
        if ($exif['Flash'] & 0x01)
            return "Flash fired";
        else
            return "Flash not fired";
    }
    else
        return "";
}

function exif_pp_software($exif) {
    if (isset($exif['Software']))
        return $exif['Software'];
    else
        return "";
}

function exif_color_space($exif) {
    if (isset($exif['ColorSpace']))
        return ($exif['ColorSpace'] == 1 ? "sRGB" : "");
    else
        return "";
}

function exif_picture_date($exif) {
    if (isset($exif['DateTimeOriginal']))
        return $exif['DateTimeOriginal'];
    else if (isset($exif['DateTimeDigitized']))
        return $exif['DateTimeDigitized'];
    else
        return "";
}

function exif_width($exif) {
    if (isset($exif['ExifImageWidth']))
        return $exif['ExifImageWidth'];
    else
        return "";
}

function exif_height($exif) {
    if (isset($exif['ExifImageLength']))
        return $exif['ExifImageLength'];
    else
        return "";
}

function exif_file_size($exif) {
    if (isset($exif['FileSize']))
        return $exif['FileSize'];
    else
        return "";
}

function exif_data($img) {
    // Read exif data
    if (! ($exif = exif_read_data($img)))
        return false;

    // if there is no ISO data and shutter speed data, assume EXIF is missing
    if (exif_iso($exif) == "" && exif_shutter_speed($exif) == "")
        return false;

    list($width, $height) = getimagesize($img);

    return array(
            "date" => exif_picture_date($exif),
            "bytes" => exif_file_size($exif),
            "width" => exif_width($exif) == "" ? $width : exif_width($exif),
            "height" => exif_height($exif) == "" ? $height : exif_height($exif),
            "iso" => exif_iso($exif),
            "program" => exif_exposure_program($exif),
            "aperture" => exif_aperture($exif),
            "speed" => exif_shutter_speed($exif),
            "bias" => exif_bias($exif),
            "metering" => exif_metering_mode($exif),
            "white_balance" => exif_white_balance($exif),
            "camera" => exif_camera($exif),
            "lens" => exif_lens($exif),
            "focal_length" => exif_focal_length($exif),
            "flash" => exif_flash($exif)
    );
}

?>

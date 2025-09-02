/*
** exif.js - Return an exif structure with exif data from jpegfile
**           Requires jpegmeta.js to be loaded before this is loaded
*/
// -- EXIF DISCOVERY -->
//
// EXIF Discovery Functions
// Uses JpegMeta library to discover EXIF
// In case of images passed as URL, the picture is downloaded to discover EXIF
// The parameter jpeg refers to jpeg var created by jpegmeta.js from an image file
//
function exposureProgram(ep) {
	switch (ep) {
		case 1 : return "Manual";
		case 2 : return "EP";
		case 3 : return "Av";
		case 4 : return "Tv";
		default : return "";
	}
}

function meteringMode(mm) {
	switch (mm) {
		case 1 : return "Aerage";
		case 2 : return "Center Weighted";
		case 3 : return "Spot";
		case 4 : return "Multi-Spot";
		case 5 : return "Multi-Segment";
		case 6 : return "Partial";
		default : return "";
	}
}

function whiteBalance(wb) {
	switch (wb) {
		case 1 : return "Auto WB";
		case 2 : return "Manual WB";
		default : return "";
	}
}

function isoSpeedSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.ISOSpeedRatings)
		return jpeg.exif.ISOSpeedRatings.value;
	else
		return "";
}

function apertureSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.FNumber)
		return "F" + (jpeg.exif.FNumber.value.num / jpeg.exif.FNumber.value.den);
	else
		return "";
}

function shutterSpeedSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.ExposureTime) {
		let et_denom = jpeg.exif.ExposureTime.value.den;
		let et_nom = jpeg.exif.ExposureTime.value.num;
		if (et_denom > et_nom)
			return "1/" + Math.floor(et_denom / et_nom) + " sec";
		else
			return Math.floor(et_nom / et_denom) + " sec";
	}

}

function evBiasSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.ExposureBiasValue && jpeg.exif.ExposureBiasValue.value.num != 0)
		return "EV " + jpeg.exif.ExposureBiasValue.value.num;
	else
		return "";
}

function exposureProgramSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.ExposureProgram)
		return exposureProgram(jpeg.exif.ExposureProgram.value);
	else
		return "";
}

function meteringModeSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.MeteringMode)
		return meteringMode(jpeg.exif.MeteringMode.value);
	else
		return "";
}

function whiteBalanceSetting(jpeg) {
	if (jpeg.exif && jpeg.exif.WhiteBalance)
		return whiteBalance(jpeg.exif.WhiteBalance.value);
	else
		return "";
}

function exposureStr(jpeg) {

	if (jpeg.exif) {
		const exif = jpeg.exif;
		let exposure = "";
		if (exif.ISOSpeedRatings)
			exposure += "ISO " + exif.ISOSpeedRatings.value;
		if (exif.FNumber)
			exposure += ", F" + (exif.FNumber.value.num / exif.FNumber.value.den);
		if (exif.ExposureTime) {
			let et_denom = exif.ExposureTime.value.den;
			let et_nom = exif.ExposureTime.value.num;
			if (et_denom > et_nom)
			exposure += ", 1/" + Math.floor(et_denom / et_nom);
				else
			exposure += ", " + Math.floor(et_nom / et_denom) + " sec";
		}
		if (exif.ExposureBiasValue && exif.ExposureBiasValue.value.num != 0)
			exposure += ", EV " + exif.ExposureBiasValue.value.num;
		if (exif.ExposureProgram)
			exposure += ", " + exposureProgram(exif.ExposureProgram.value);
		if (exif.MeteringMode)
			exposure += ", " + meteringMode(exif.MeteringMode.value);

		return exposure;
	}
	else
		return "";
}

function cameraModel (jpeg) {
	if (jpeg.tiff && jpeg.tiff.Model)
		return jpeg.tiff.Model.value;
	else
		return "";
}

function lensName (jpeg) {
	if (jpeg.tiff && jpeg.tiff.LensName)
		return jpeg.tiff.LensName;
	else
		return "";
}

function focalLength (jpeg) {
	if (jpeg.exif && jpeg.exif.FocalLength)
		return jpeg.exif.FocalLength + "mm";
	else
		return "";
}

function dateClicked(jpeg) {
	if (jpeg.exif && jpeg.exif.DateTimeOriginal)
		return jpeg.exif.DateTimeOriginal.value;
	else
		return "";
}

function pixelWidth(jpeg) {
	if (jpeg.general && jpeg.general.pixelWidth)
		return jpeg.general.pixelWidth.value;
	else if (jpeg.exif && jpeg.exif.PixelXDimension)
		return jpeg.exif.PixelXDimension;
	else
		return 0;
}

function pixelHeight(jpeg) {
	if (jpeg.general && jpeg.general.pixelHeight)
		return jpeg.general.pixelHeight.value;
	else if (jpeg.exif && jpeg.exif.PixelYDimension)
		return jpeg.exif.PixelYDimension;
	else
		return 0;
}

function flashStatus(jpeg) {
    if (jpeg.exif && jpeg.exif.Flash) {
        if (jpeg.exif.flash & 0x20)
            return "No Flash";
        if (jpeg.exif.Flash & 0x01)
            return "Flash Fired";
        else
            return "Flash not fired";
    }
    return "";
}


//
// Return exif object assembled by calling the above functions
//
function getExifData(jpeg) {
    if (jpeg.general || jpeg.exif || jpeg.tiff) {
        return {
            error : "",
            width : pixelWidth(jpeg),
            height: pixelHeight(jpeg),
            date : dateClicked(jpeg),
            camera : cameraModel(jpeg),
			lens : lensName(jpeg),
            lens_focal_length : focalLength(jpeg),
            iso : isoSpeedSetting(jpeg),
            program : exposureProgramSetting(jpeg),
            aperture : apertureSetting(jpeg),
            speed : shutterSpeedSetting(jpeg),
            ev : evBiasSetting(jpeg),
            flash : flashStatus(jpeg),
            metering : meteringModeSetting(jpeg),
            wb : whiteBalanceSetting(jpeg),
            exposure : exposureStr(jpeg),
        };
    }
    else {
        return {
            error : "No EXIF Data available",
        }
    }
}


// Get Exif Data of url referred to im img.src
function getUrlExif(url, callback) {

	var $j = this.JpegMeta.JpegFile;

	var xhr = new XMLHttpRequest();
	xhr.responseType = "blob";

	// Set Handlers
	xhr.onload = function () {
		// var imgBlob = new Blob(this.response);
		// Use File Reader to convert to Data Url
		var fr = new FileReader();
		fr.onload = function () {
			var jpeg = new $j(atob(this.result.replace(/^.*?,/,'')), "url-img");
			callback({result : "OK", errmsg : "", jpeg : jpeg});
		}
		fr.onerror = function () {
			callback({result: "FAIL", errmsg : "Unable to load image" });
		}
		fr.readAsDataURL(this.response);
	}

	xhr.onerror = function() {
		callback({result: "FAIL", errmsg : this.statusText });
	}

	// Send Request
	xhr.open("GET", url, true);
	xhr.send();

}

// Get Exif Data from a URI of local file
function getFileExif (imageURI, callback) {
	var $j = this.JpegMeta.JpegFile;

	// Read URI into array buffer
	window.resolveLocalFileSystemURL(imageURI, function (fileEntry) {
		fileEntry.file(function (file) {
			var reader = new FileReader();
			// set up event handler
			reader.onload = function () {
				var jpeg = new $j(atob(this.result.replace(/^.*?,/,'')), file);
				callback({result : "OK", errmsg : "", jpeg : jpeg});
			}
			// error result
			reader.onerror = function () {
				callback({result : "FAIL", errmsg : this.error.message});
			}
			// read file into buffer
			reader.readAsDataURL(file);
		});
	});

}

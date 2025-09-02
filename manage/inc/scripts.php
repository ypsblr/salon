<?php
?>
<!-- JS Global -->
<script src="plugin/jquery/js/jquery.min.js"></script>
<script src="plugin/bootstrap/js/bootstrap.min.js"></script>

<!-- JS Plugins -->
<script src="plugin/misc/js/scrolltopcontrol.js"></script>
<script src="plugin/misc/js/jquery.sticky.js"></script>
<script src="plugin/swal/js/sweetalert.min.js"></script>
<!-- <script src="plugin/swal/js/sweet_alert.js"></script> -->
<!-- JPEGMETA - load EXIF -->
<script src="plugin/misc/js/jpegmeta.js"></script>

<!-- JS Custom -->
<script src="custom/js/custom.js"></script>

<!-- Crypto JS -->
<script src="plugin/cryptojs/cryptojs-aes.min.js"></script>
<script src="plugin/cryptojs/cryptojs-aes-format.js"></script>


<!-- Automatic display of Errors, Information and Warning Messages -->
<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['err_msg']) && $_SESSION['err_msg'] != "") {
?>
<script>
	$(document).ready(function() {
		swal({
			title: 'Error',
			text:  '<?php echo $_SESSION['err_msg']; ?>',
			icon: "error",
			button: 'Dismiss'
		});
	});
</script>
<?php
	}
	unset($_SESSION['err_msg']);
?>

<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['success_msg']) && $_SESSION['success_msg'] != "") {
?>
<script>
	$(document).ready(function() {
		swal({
			title: 'Success',
			text:  '<?php echo $_SESSION['success_msg']; ?>',
			icon: "success",
			button: 'Great'
		});
	});
</script>
<?php
	}
	unset($_SESSION['success_msg']);
?>

<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['info_msg']) && $_SESSION['info_msg'] != "") {
?>
<script>
	$(document).ready(function() {
		swal({
			title: 'Information',
			text:  '<?php echo $_SESSION['info_msg']; ?>',
			icon: "info",
			button: 'Thanks'
		});
	});
</script>
<?php
	}
	unset($_SESSION['info_msg']);
?>


<?php
	// Keep session alive by refreshing session at regular intervals
	//
	if ( isset($_SESSION['MEMEX_SESSION_START']) && isset($_SESSION['MEMEX_SESSION_DURATION']) ) {
?>
	<script>
		var session_timer;
		var call_in_progress;
		function validate_session_status() {
			if (! call_in_progress) {
				call_in_progress = true;
				$.ajax({
					url: "ajax/keep_session_alive.php",
					type: "POST",
					data: {
						memexc : '<?= $_SESSION["MEMEX_SESSION_CODE"];?>',
						memexd : CryptoJS.AES.encrypt('<?= json_encode($_SESSION);?>', '<?= $_SESSION["MEMEX_SESSION_CODE"];?>', { format: CryptoJSAesJson }).toString(),
					},
					cache: false,
					success: function(response) {
						call_in_progress = false;
						data = JSON.parse(response);
						if (data.status != "ALIVE") {
							swal({
								title: 'Session ' + data.status,
								text:  data.errmsg,
								icon: "info",
								button: 'Thanks'
							});
						}
					},
					error: function() {
						call_in_progress = false;
					}
				});

			}
		}
		$(document).ready(function() {
			validate_session_status();			// Run once immediately
			session_timer = setInterval(validate_session_status, 5*60*1000);	// Run every 5 minutes to keep session variables alive
		});
	</script>

<?php
	}
?>


<?php
	if (defined("LIMIT_ENTRIES_TO") && LIMIT_ENTRIES_TO > 0) {
?>
<!-- Update Entries Left -->
<script>
	if (document.getElementById("entries-left")) {
		var timer = setInterval(function() {
			var entries_left = $("#entries-left").html();
			if (entries_left == "CLOSED") {
				clearInterval(timer);
			}
			else {
				$.post("ajax/get_entries_left.php", function (data) {
					if (data.success) {
						$("#entries-left").html(data.num_entries_left > 0 ? data.num_entries_left + " slots left" : "CLOSED");
					}
				});
			}
		}, 5 * 60 * 1000);	// Update every 5 minutes
	}

</script>
<?php
	}
?>



<script>
	// Enable All tooltips
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>

<!-- Logging Errors to server -->
<script>
	function log_js_error(status, errorText, jqXHR = {}, emailField = "") {

		let source = "<?= basename($_SERVER['PHP_SELF'], '.php');?>.php";
		let param = {};
		let text = "";
		let xml = "";
		if (jqXHR.responseText)
			text = jqXHR.responseText;
		if (jqXHR.responseXML)
			xml = jqXHR.responseXML;
		let statusText = status + ((jqXHR.status) ?  "(" + jqXHR.status + ")" : "");
		let error = errorText + ((jqXHR.statusText) ? "[" + jqXHR.statusText + "]" : "");

		if (emailField != "" && $("#" + emailField).length > 0)
			param = { rurobot : 'IamHuman', source : source, status : statusText, error : error, text : text, xml : xml, context : $("#" + emailField).val() };
		else
			param = { rurobot : 'IamHuman', source : source, status : statusText, error : error, text : text, xml : xml };

		$.post("ajax/log_js_error.php", param);
	}
</script>

<?php
	if ( defined("UPLOADS_IMAGE") && UPLOADS_IMAGE ) {
?>
<!-- EXIF DISCOVERY -->
<script>
	//
	// EXIF Discovery Functions
	// Uses JpegMeta library to discover EXIF
	// In case of images passed as URL, the picture is downloaded to discover EXIF
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
			return "-NA-";
	}

	function apertureSetting(jpeg) {
		if (jpeg.exif && jpeg.exif.FNumber)
			return "F" + (jpeg.exif.FNumber.value.num / jpeg.exif.FNumber.value.den);
		else
			return "-NA-";
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
			return "-NA-";
	}

	function exposureProgramSetting(jpeg) {
		if (jpeg.exif && jpeg.exif.ExposureProgram)
			return exposureProgram(jpeg.exif.ExposureProgram.value);
		else
			return "-NA-";
	}

	function meteringModeSetting(jpeg) {
		if (jpeg.exif && jpeg.exif.MeteringMode)
			return meteringMode(jpeg.exif.MeteringMode.value);
		else
			return "-NA-";
	}

	function whiteBalanceSetting(jpeg) {
		if (jpeg.exif && jpeg.exif.WhiteBalance)
			return whiteBalance(jpeg.exif.WhiteBalance.value);
		else
			return "-NA-";
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
			return jpeg.tiff.Model;
		else
			return "-NA-";
	}

	function focalLength (jpeg) {
		if (jpeg.exif && jpeg.exif.FocalLength)
			return jpeg.exif.FocalLength + "mm";
		else
			return "-NA-";
	}

	function dateClicked(jpeg) {
		if (jpeg.exif && jpeg.exif.DateTimeOriginal)
			return jpeg.exif.DateTimeOriginal.value;
		else
			return "-NA-";
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

	function getExif (imageURI, callback) {
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
</script>


<!-- Support loading photo from disk and returning EXIF -->
<script>
	// Load Avatar image from File
	// Param input = INPUT Dom Element
	// progressCallback - function(result{src : img_src, exif: exif, percentComplete : percentageUploadCompleted})
	// doneCallback - function(result {status: "OK" | "FAIL", errmsg : "" | "error message", tempFile : name of file uploaded to temp area, exif : exif{} })
	function loadFilePhoto(input, upload = false, doneCallback = null, progressCallback = null) {

		/* Initialize JPEG Meta tags reader */
		var $j = this.JpegMeta.JpegFile;

		// Meta values checking & Load Picture for Display
		var reader = new FileReader();

		// Handler for file read completion
		reader.onload = function (e) {
			// Discover EXIF
			var jpeg = new $j(atob(this.result.replace(/^.*?,/,'')), input.files[0]);
			let exif = {
						size : input.files[0].size,
						width : pixelWidth(jpeg),
						height: pixelHeight(jpeg),
						date : dateClicked(jpeg),
						camera : cameraModel(jpeg),
						lens : focalLength(jpeg),
						iso : isoSpeedSetting(jpeg),
						program : exposureProgramSetting(jpeg),
						aperture : apertureSetting(jpeg),
						speed : shutterSpeedSetting(jpeg),
						ev : evBiasSetting(jpeg),
						metering : meteringModeSetting(jpeg),
						wb : whiteBalanceSetting(jpeg),
						exposure : exposureStr(jpeg),
					};
			// Initiate Preview
			if (progressCallback)
				progressCallback({ src: e.target.result, exif : exif, percentComplete : 0});

			// Initiate upload
			if (upload)
				uploadFilePhoto(input, e.target.result, exif, doneCallback, progressCallback);
			else if (doneCallback)
				doneCallback({ status : "OK", errmsg : "", src : e.target.result, exif : exif, tempFile : ""});
		};

		// Handler for File Read Error
		reader.onerror = function(e) {
			if (doneCallback)
				doneCallback({ status: "FAIL", errmsg: "Unable to open selected picture"});
		}

		// Perform File Read
		reader.readAsDataURL(input.files[0]);
	}
</script>


<!-- Support for uploading photograph -->
<script>
	// Function to upload file in the background
	function uploadFilePhoto (input, src, exif, doneCallback, progressCallback) {

		// var input = $("#" + input_id).get(0);
		var file = input.files[0];

		var fd = new FormData();

		// These extra params aren't necessary but show that you can include other data.
		fd.append("rurobot", "IamHuman");

		// Read image file and create a blob
		var reader = new FileReader();

		// Handler for successfil readonly
		reader.onload = function () {
			// Create Blob and Attach to Form Data
			var imgBlob = new Blob([this.result], {type: "image/jpeg"});
			fd.append("photo", imgBlob);

			// Send to Server
			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'ajax/upload_tmp_photo.php', true);

			xhr.upload.onprogress = function(e) {
				// Update Progress Bar
				if (e.lengthComputable) {
					var percentComplete = Math.round((e.loaded / e.total) * 100);
					if (progressCallback)
						progressCallback({src: src, exif : exif, percentComplete: percentComplete});
					console.log(percentComplete + '% uploaded');
				}
			};

			xhr.upload.onerror = function(e) {
				let errmsg = "Error uploading file. ";
				if (e.loaded < e.total)
					errmsg += "File not fully uploaded. ";
				else
					errmsg += "File upload aborted. ";

				if (doneCallback)
					doneCallback( {status: "FAIL", errmsg: errmsg} );

				console.log(errmsg);
			}

			xhr.onload = function() {
				if (this.status == 200) {
					var resp = JSON.parse(this.response);
					if (resp.success) {
						if (doneCallback)
							doneCallback({
											status : "OK",
											errmsg : "",
											src : src,
											exif : exif,
											tempFile : resp.tmpfile,
										});
					}
					else {
						// Service returned error
						if (doneCallback)
							doneCallback( {status : "FAIL", errmsg : resp.msg} );
						console.log(resp.msg);
					}
				}
				else {
					// Server returned error
					if (doneCallback)
						doneCallback( {status : "FAIL", errmsg : "Server Returned Error"} );

					console.log("Server Error : " . this.status);
				}
			};

			xhr.send(fd);
		}

		// Handle Read Error
		reader.onerror = function () {
			if (doneCallback)
				doneCallback( {status : "FAIL", errmsg : "Unable to read selected file !" } );

		}

		// Perform File Read
		reader.readAsArrayBuffer(file);
	}
</script>
<?php
	}		// Block containing Image Upload Support
?>

<script>
	// Remove all File Inputs
	function stripFileInputs(formData) {
		$("input").filter("[type='file']").filter("[accept='image/jpeg']").each(function(index, elem) {
			formData.delete(elem.name);
		});
		return formData;
	}
</script>

<script>
	// Remove all File Inputs
	function copyFormStrings(srcFormData) {
		let formData = new FormData();

		// Send form data for logging
		let key, value;
		for ([key, value] of srcFormData.entries()) {
			if (typeof value == "string")
				formData.append(key, value);
		}
		return formData;
	}
</script>

<script>
	function jsonFormData(formData) {
		// Send form data for logging
		let fdd = {};
		let key, value;
		for ([key, value] of formData.entries()) {
			if (typeof value == "string")
				fdd[key] = value;
		}
		return JSON.stringify(fdd);
	}
</script>

<script>
	function encryptFormData(formData) {
		let encryptedFormData = new FormData();
		let key, value;
		let fields = {};
		for ([key, value] of formData.entries()) {
			if (typeof value == "string")
				fields[key] = value;
			else
				encryptedFormData.append(key, value);
		}
		let memexc = "<?= $_SESSION['MEMEX_SESSION_CODE'];?>";
		let memexd = CryptoJS.AES.encrypt(JSON.stringify(fields), memexc, { format: CryptoJSAesJson }).toString();
		encryptedFormData.append("memexc", memexc);
		encryptedFormData.append("memexd", memexd);

		return encryptedFormData;
	}
</script>



<?php
?>

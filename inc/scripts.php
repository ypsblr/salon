<?php
// Return date in string format at a specified timezone
function tz_offest($tz) {
	$cur_tz = date_default_timezone_get();
	date_default_timezone_set($tz);
	$offset = date("P", strtotime("now"));
	date_default_timezone_set($cur_tz);

	return $offset;
}
?>
    <!-- JS Global -->
    <script src="plugin/jquery/js/jquery.min.js"></script>
    <script src="plugin/bootstrap/js/bootstrap.min.js"></script>

    <!-- JS Plugins -->
    <script src="plugin/misc/js/scrolltopcontrol.js"></script>
    <script src="plugin/misc/js/jquery.sticky.js"></script>
	<script src="plugin/swal/js/sweetalert.min.js"></script>
	<!-- JPEGMETA - load EXIF -->
	<script src="plugin/misc/js/jpegmeta.js"></script>
	<script src="custom/js/exif.js"></script>

    <!-- JS Custom -->
    <script src="custom/js/custom.js"></script>

	<!-- Crypto Functions -->
	<script src="plugin/cryptojs/cryptojs-aes.min.js"></script>
	<script src="plugin/cryptojs/cryptojs-aes-format.js"></script>

	<!-- Hide Splash Screen -->
	<script>
		$(document).ready(function () {
			$(".splash").hide();
		});
	</script>

	<!-- Reset Password Confirmation -->
	<script>
		var baseurl= '<?php echo http_method() .$_SERVER['SERVER_NAME']."/";?>';

		function resetConfirmation(){
			var login_id = $("#login_id").val();
			if (login_id.trim() != "") {
				swal({
					title: 'Reset Password',
					text:  'Do you want to reset your password? The present password will be deleted and a new generated password will be emailed to you. You can change the password after logging in.',
					imageUrl: 'img/info1.png',
					showCancelButton: true,
					confirmButtonColor: '#00BAFF',
					cancelButtonColor: '#696969',
					confirmButtonText: 'Yes'
				})
				.then(function() {
					location = 'op/login.php?reset=' + login_id;
					return false;
				});
				return false;
			}
		}
	</script>


<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['err_msg']) && $_SESSION['err_msg'] != "") {
?>
	<script>
		$(document).ready(function() {
			swal({
				title: 'Error',
				text:  '<?php echo str_replace_quotes($_SESSION['err_msg']); ?>',
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
				text:  '<?php echo str_replace_quotes($_SESSION['success_msg']); ?>',
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
				text:  '<?php echo str_replace_quotes($_SESSION['info_msg']); ?>',
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
	// Keep Track of sessions. If session is invalid move to Index page
	// Set up a timer if there is a valid session
	// Checks every 5 minutes
	// if (isset($_SESSION['USER_ID']) && $_SESSION['USER_ID'] != "") {
?>
	<!-- <script>
		var session_timer = 0;
		var suspend_ticker = false;
		function validate_session_status() {
			if(! suspend_ticker)
				$.post("ajax/validate_session.php", function (session_status) {
												// If session is active, try again after 5 minutes
												if (session_status == "DEAD") {
													suspend_ticker = true;
													clearInterval(session_timer);
													swal("Logged Out", "Inactive for too long. You have been logged out. Login again.", "warning")
													.then(function(){ location.href = "/index.php"; });

												}
				});
		}
		$(document).ready(function() {
			session_timer = setInterval(validate_session_status, 1*60*1000);	// Every 1 minute
		});
	</script> -->

<?php
	// }
?>


<?php
	// Keep session alive by refreshing session at regular intervals
	// NO_SESSION_ALIVE is set for non-logged-in operations like uploading full resolution pictures
	use Nullix\CryptoJsAes\CryptoJsAes;
	// include("inc/CryptoJsAes.php");
	if ( isset($_SESSION['SALON_SESSION_START']) && (! defined("NO_SESSION_ALIVE")) ) {
		$session_vars = [];
		foreach($_SESSION as $session_key => $session_value) {
			if (! in_array($session_key, array('info_msg', 'success_msg', 'err_msg')))
				$session_vars[$session_key] = $session_value;
		}
		define("SALON_SESSION_DATA", CryptoJsAes::encrypt(json_encode($session_vars), SALON_SESSION_KEY));
?>
	<script>
		var session_timer;
		var session_status = "<?= isset($_SESSION['USER_ID']) ? 'LOGGED_IN' : 'LOGGED_OUT';?>";	// Initial Status

		function validate_session_status() {
			$.ajax({
				url: "ajax/keep_session_alive.php",
				type: "POST",
				data: {
					status : session_status,
					salond : '<?= SALON_SESSION_DATA;?>',
				},
				cache: false,
				success: function(response) {
					data = JSON.parse(response);
					if (data.success) {
						if (data.status != session_status) {
							// status has changed
							session_status = data.status;
							// and user has been logged out
							if (data.status == "LOGGED_OUT") {
								// Display message and logout to index page - Session may
								swal("Logged Out", "Inactive for too long. You have been logged out. Login again.", "warning")
								.then(function(){ location.href = "/index.php"; });
							}
						}
					}
				},
			});
		}

		// Start Keep Alive process
		$(document).ready(function() {
			validate_session_status();			// Run once immediately
			session_timer = setInterval(validate_session_status, 5*60*1000);	// Run every 5 minutes to keep session variables alive
		});
	</script>

<?php
	}
?>


	<!-- Update Countdown Timer -->
	<script>
		// Set the date we're counting down to
		var isoDateStr = "<?php echo date("Y-m-d", strtotime_tz($registrationLastDate, $submissionTimezone)) . 'T23:59:59' . tz_offest($submissionTimezone);?>";
		// var isoDateStr = "<?php echo date("Y-m-d", strtotime($registrationLastDate)) . 'T23:59:59' . tz_offest($submissionTimezone);?>";
		var countDownDate = new Date(isoDateStr).getTime();
		if (document.getElementById("countdown")) {

			// Update the count down every 1 second
			var x = setInterval(function() {

				// Get todays date and time
				var now = new Date().getTime();

				// Find the distance between now an the count down date
				var distance = countDownDate - now;

				// Time calculations for days, hours, minutes and seconds
				var days = Math.floor(distance / (1000 * 60 * 60 * 24));
				var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);

				// Output the result in an element with id="demo"
				document.getElementById("countdown").innerHTML = days + "d " + hours + "h "
				+ minutes + "m " + seconds + "s ";

				// If the count down is over, write some text
				if (distance < 0) {
					clearInterval(x);
					document.getElementById("countdown").innerHTML = "CONTEST CLOSED";
				}
			}, 1000);
		}
	</script>

	<!-- Keep Rotating the awards till clicked -->
	<script>
		var tabClicked = false;
		var tabs = [];
		var pills = [];
		var i = 0;
		var max = 0;
		$(document).ready(function (){
			// Create a list of award tabs
			$("div[id^='award_']").each(function(index, item){
				var awtab = $(item).data("tab");
				if (tabs[awtab] == undefined) {
					tabs[awtab] = [];
					i = 0;
				}
				tabs[awtab][i] = item.id;
				// Set listener for each tab
				$("#"+item.id).click(function(){tabClicked = (tabClicked ? false : true);});
				++i;
			});
			i = 0;
			$("li[id^='pill_']").each(function(index, item){
				var awpill = $(item).data("pill");
				if (pills[awpill] == undefined) {
					pills[awpill] = [];
					i = 0;
				}
				pills[awpill][i] = item.id;
				// Set listener for each pill
				$("#"+item.id).click(function(){tabClicked = (tabClicked ? false : true);});
				++i;
			});

			// Initialize Indexes to 0
			var idx_tab = [];
			for (var awtab in tabs)
				idx_tab[awtab] = 0;

			var idx_pill = [];
			for (var awpill in pills)
				idx_pill[awpill] = 0;

			// Keep rotating till one of the tabs is clicked.
			var x = setInterval(function(){
				if (! tabClicked) {
					for (var awtab in tabs) {
						for (var i = 0; i < tabs[awtab].length; ++i) {
							if (i == idx_tab[awtab])
								$("#"+tabs[awtab][i]).attr("class", "tab-pane fade in active");
							else
								$("#"+tabs[awtab][i]).attr("class", "tab-pane fade");
						}
						++ idx_tab[awtab];
						if (idx_tab[awtab] >= tabs[awtab].length)
							idx_tab[awtab] = 0;
					}
					for (var awpill in pills) {
						for (var i = 0; i < pills[awpill].length; ++i) {
							if (i == idx_pill[awpill])
								$("#"+pills[awpill][i]).attr("class", "active");
							else
								$("#"+pills[awpill][i]).attr("class", "");
						}
						++ idx_pill[awpill];
						if (idx_pill[awpill] >= pills[awpill].length)
							idx_pill[awpill] = 0;
					}
				}
			}, 5000);

		});
	</script>

	<!-- Keep Rotating the recognitions till clicked -->
	<script>
	/*
		$(document).ready(function (){
			var tabClicked = false;
			var tabs = new Array();
			var pills = new Array();
			var i = 0;
			// Create a list of award tabs
			$("div[id^='recognition_fill_']").each(function(index, item){
				tabs[i] = item.id;
				// Set listener for each tab
				$("#"+item.id).click(function(){tabClicked = true;});
				++i;
			});
			i = 0;
			$("li[id^='recognition_pill_']").each(function(index, item){
				pills[i] = item.id;
				// Set listener for each pill
				$("#"+item.id).click(function(){tabClicked = true;});
				++i;
			});

			// Keep rotating till one of the tabs is clicked.
			i = 0;	// Set it to first tab
			var x = setInterval(function(){
				if (! tabClicked) {
					$("#"+pills[i]).attr("class", "");
					$("#"+tabs[i]).attr("class", "tab-pane fade");
					i++;
					if (i >= tabs.length)
						i = 0;
					$("#"+pills[i]).attr("class", "active");
					$("#"+tabs[i]).attr("class", "tab-pane fade in active");
				}
			}, 5000);

		});
	*/
	</script>

	<script>
		/*** Ajax File Upload Facility ***/
		var ajax_upload_token = "";
		var upload_max_filesize = 0;

		// Get the token
		function initAjaxUpload() {
			$.post("/ajax/ajax_upload_photo.php", {"rurobot" : "IamHuman", "set_token" : "true"}, function(response){
				let data = JSON.parse(response);
				if (data.success) {
					ajax_upload_token = data.token;
					upload_max_filesize = data.upload_max_filesize;
				}
			})
		}

		function file_size_in_mb(size) {
			let size_in_mb = Math.round(size * 10 / (1024 * 1024));
			return size_in_mb / 10;
		}

		// Function to upload file in the background with progress reporting
		// file_input_field_id = ID associated with Input field of type file used to select a file for upload
		// completion_callback(temp_file_name) - Invoked after the upload is completed. Will return the path under which the file has been uploaded
		// error_callback(error_message) - Invoked when an error is encountered
		// progress_callback(percent_complete) - Callback function called periodically to report percentage uploaded
		// min_size - Optional minimum pixels for validation
		function ajaxPictureUpload (file_input_field_id, completion_callback, error_callback, progress_callback, min_size = 0) {

			if (ajax_upload_token == "") {
				if (error_callback)
					error_callback("Upload not initialized");
				else
					console.log("Upload not initialized");
			}

			var input = $("#" + file_input_field_id).get(0);
			var file = input.files[0];
			if (file.size > upload_max_filesize) {
				let errmsg = "File Size (" + file_size_in_mb(file.size) + " MB) exceeds maximum supported (" + file_size_in_mb(upload_max_filesize) + " MB). ";
				errmsg += "Save with lower jpeg quality and upload.";
				if (error_callback)
					error_callback(errmsg);
				else
					console.log(errmsg);
				return;
			}

			var fd = new FormData();

			// These extra params aren't necessary but show that you can include other data.
			fd.append("rurobot", "IamHuman");
			fd.append("auth_token", ajax_upload_token);

			// Read image file and create a blob
			var reader = new FileReader();

			// Handler for successfil readonly
			reader.onload = function (e) {

				// Get Image Height and Width
				if (min_size != 0) {
					var bytes = new Uint8Array(this.result);
					var jpeg = new JpegMeta.JpegFile(bytes.reduce((str, char) => str + String.fromCharCode(char), ""), "tmpimg");
					var pic_width = jpeg.general.pixelWidth.value;
					var pic_height = jpeg.general.pixelHeight.value;
					console.log("Uploaded image width = ", pic_width);
					console.log("Uploaded image height = ", pic_height);
					if (pic_width < min_size || pic_height < min_size) {
						let min_size_err = "Picture selected is smaller than " + min_size.toString() + " pixels on one of the sides";
						if (error_callback)
							error_callback(min_size_err);
						else
							console.log(min_size_err);
						return;
					}
				}

				// Create Blob and Attach to Form Data
				var imgBlob = new Blob([this.result], {type: "image/jpeg"});
				fd.append("photo", imgBlob);

				// Send to Server
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '/ajax/ajax_upload_photo.php', true);

				xhr.upload.onprogress = function(e) {
					// Update Progress Bar
					let percentComplete = 0;
					if (e.lengthComputable) {
						percentComplete = Math.round((e.loaded / e.total) * 100);
					}
					if (progress_callback)
						progress_callback(percentComplete);
					else
						console.log("Uploaded " + percentComplete + "%");
				};

				xhr.upload.onerror = function(e) {
					let errmsg = "Error uploading file. ";
					if (e.loaded < e.total)
						errmsg += "File not fully uploaded. ";
					else
						errmsg += "File upload aborted. ";
					if (error_callback)
						error_callback(errmsg);
					else
						console.log(errmsg);
				}

				xhr.onload = function() {
					if (this.status == 200) {
						let stat = JSON.parse(this.response);
						if (stat.success) {
							if (completion_callback)
								completion_callback(stat.tmpfile);
							else
								console.log("Uploaded to " + stat.tmpfile);
						}
						else {
							if(error_callback)
								error_callback(stat.errmsg);
							else
								console.log(stat.errmsg);
						}
					}
					else {
						if (error_callback)
							error_callback("Upload failed with status " + this.status + " and returned [" + this.response + "]");
						else
							console.log("Upload failed with status " + this.status + " and returned [" + this.response + "]");
					}
				};

				xhr.send(fd);
			}

			// Handle Read Error
			reader.onerror = function (e) {
				if (error_callback)
					error_callback("Error reading the selected picture");
				else
					console.log("Reader failed on selected file");
			}

			// Perform File Read
			reader.readAsArrayBuffer(file);
		}

	</script>

	<script>
	// Enable All tooltips
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
	</script>

	<script>
	// Automatically mark a list item active
	$(document).ready(function($){
		var url = window.location.pathname;
		$('.nav li a[href="'+url+'"]').parent().addClass('active');
	});
	</script>

	<script>
	// Look for pattern in search
	String.prototype.fill_template = function(srch, repl) {
		var str = this;
		for (var i = 0; i < srch.length; ++ i) {
			var pattern = new RegExp('~' + srch[i] + '~', 'g');
			str = str.replace(pattern, repl[i]);
		}
		return str;
	}
	</script>

	<!-- Encryption Methods -->

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
			// Send form data for logging - Only type string is copied, File Inputs are not copied
			let fields = {};
			let key, value;
			for ([key, value] of formData.entries()) {
				if (typeof value == "string") {
					if (key.substr(-2) == "[]") {
						key = key.substr(0, key.length - 2);
						if (fields[key])
							fields[key].push(value);
						else
							fields[key] = [value];	// create array
					}
					else
						fields[key] = value;
				}
			}
			return JSON.stringify(fields);
		}
	</script>

	<script>
		function encryptFormData(formData) {
			let encryptedFormData = new FormData();
			let key, value;
			let fields = {};
			for ([key, value] of formData.entries()) {
				if (typeof value == "string") {
					if (key.substr(-2) == "[]") {
						key = key.substr(0, key.length - 2);
						if (fields[key])
							fields[key].push(value);
						else
							fields[key] = [value];		// create array
					}
					else
						fields[key] = value;
				}
				else
					encryptedFormData.append(key, value);
			}
			let ypsd = CryptoJS.AES.encrypt(JSON.stringify(fields), "<?= defined("SALONBOND") ? SALONBOND : "NOT_DEFINED";?>", { format: CryptoJSAesJson }).toString();
			encryptedFormData.append("ypsd", ypsd);

			return encryptedFormData;
		}
	</script>

	<!-- Support for login form encryption (non-ajax) -->
	<script>
		$(document).ready(function() {
			$("input[name='login_login_id']").hide();
			$("input[name='check_it']").attr("placeholder", "Email (or YPS Member ID)");
			$("#reset_form").find("input[name='check_it']").attr("placeholder", "Email");
		});
	</script>

	<script>
		function submit_form(data) {
			// Encrypt
			let ypsd = CryptoJS.AES.encrypt(JSON.stringify(data), "<?= defined("SALONBOND") ? SALONBOND : "NOT_DEFINED";?>", { format: CryptoJSAesJson }).toString();
			$("#ypsd").val(ypsd);
			$("#submission_form").submit();
		}

		// Login Form
		$("#login_check").click(function(e){
			// Prevent the main form from getting submitted
			e.preventDefault();

			// Gather Data
			let data = {
				login_id : $("#login_form").find("input[name='check_it']").val(),
				login_password : $("#login_form").find("input[name='login_password']").val(),
				login_check : true,
			}

			// Validate
			if (! (data.login_id && data.login_id.trim() != "")) {
				$("#login_err_msg").html("Enter Email (or YPS Member Id)");
				return;
			}
			if (! data.login_password) {
				$("#login_err_msg").html("Password required");
				return;
			}

			// Submit the Form
			submit_form(data);
		});

		// Signup Form
		$("#sign_up").click(function(e){
			// Prevent the main form from getting submitted
			e.preventDefault();

			// Gather Data
			let data = {
				login_id : $("#signup_form").find("input[name='check_it']").val(),
				phone : $("#signup_form").find("input[name='call_it']").val(),
				login_password : "",
				sign_up : true,
			}

			// Validate
			if (data.login_id.trim() == "" || data.phone.trim() == "" || data.phone == 0) {
				$("#login_err_msg").html("Enter Email (or YPS Member Id) and Phone Number");
				return;
			}

			// Submit the Form
			submit_form(data);
		});

		// Reset Form
		$("#login_reset_password").click(function(e){
			// Prevent the main form from getting submitted
			e.preventDefault();

			// Gather Data
			let data = {
				login_id : $("#reset_form").find("input[name='check_it']").val(),
				login_password : "",
				login_reset_password : true,
			}

			// Validate
			if (! (data.login_id && data.login_id.trim() != "") ) {
				$("#login_err_msg").html("Enter Email");
				return;
			}

			// Submit the Form
			submit_form(data);
		});
	</script>

	<div id="loader_div" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
		<img alt="Loader Image" title="Loading..." src="img/progress_bar.gif">
	</div>

	<script>
		function showLoader(elem) {
			$(elem).data("html", $(elem).html());		// Save current element text
			$(elem).prop("disabled", true);
			let spinner = '<span style="color: white;"><i class="fa fa-spinner fa-spin"></i> Working...</span>';
			$(elem).html(spinner);
		}
		function hideLoader(elem) {
			$(elem).html($(elem).data("html"));
			$(elem).prop("disabled", false);
		}
	</script>



<?php
?>

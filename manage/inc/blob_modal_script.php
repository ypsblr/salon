<?php
function tag_to_name ($tag) {
	$name = str_replace("-", " ", $tag);
	return ucwords($name);
}
?>
<!-- tinymce editor -->
<script src='plugin/tinymce/tinymce.min.js'></script>
<script src='plugin/tinymce/plugins/link/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/lists/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/image/plugin.min.js'></script>
<script src='plugin/tinymce/plugins/table/plugin.min.js'></script>
<!-- <script src="custom/js/yps_tags.js"></script> -->

<script>
	$(document).ready(function(){
		// Gather Custom Tags
		var yps_custom_tags = [];
		<?php
			$yps_custom_tags = [];
			if ( (! empty($cr_name)) && (! empty($salon_reports[$cr_name]['custom_tags'])) ) {
				$yps_custom_tags = explode(",", $salon_reports[$cr_name]['custom_tags']);
				foreach ($yps_custom_tags as $tag) {
		?>
			yps_custom_tags.push(
					{
						type: 'menuitem',
						text : '<?= tag_to_name($tag);?>',
						onAction : function() {
							editor.insertContent(' <?= $tag;?> ');
						},
					}
			);
		<?php
				}
			}
		?>
		// Init
		tinymce.init({
			selector: '#blob_content',
			height: 600,
			plugins : 'link lists image table code',
			link_assume_external_targets : false,
			// content_css : 'custom/css/mail.css',
			// allow_html_in_named_anchor : true,
			// extended_valid_elements : 'html[*],head[*],stye[*],body[*]',
			// valid_children : '+html[head|body],+head[style]',
			menu : {
				yps_tags : {
					title : "YPS TAGS",
					items : "yps_salon yps_judging yps_exhibition yps_others <?= sizeof($yps_custom_tags) > 0 ? 'yps_custom' : '';?>",
				},
			},
			menubar : 'file edit view insert format table yps_tags',
			toolbar : 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | image | code',
			setup : function(editor) {
				editor.ui.registry.addNestedMenuItem("yps_salon", {
					text : "Salon Info",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : 'Salon Website',
								onAction : function(){
									editor.insertContent(' [salon-website] ');
								},
							},
							{
								type: 'menuitem',
								text : 'YPS Website',
								onAction : function(){
									editor.insertContent(' [yps-website] ');
								},
							},
							{
								type: 'menuitem',
								text : "Salon Folder",
								onAction : function(){
									editor.insertContent(' [yearmonth] ');
								}
							},
							{
								type: 'menuitem',
								text : "Salon Name",
								onAction : function(){
									editor.insertContent(' [salon-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Entry Last Date",
								onAction : function(){
									editor.insertContent(' [registration-last-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Digital Upload Last Date",
								onAction : function(){
									editor.insertContent(' [digital-submission-last-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Print Upload Last Date",
								onAction : function(){
									editor.insertContent(' [print-submission-last-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Timezone",
								onAction : function(){
									editor.insertContent(' [submission-timezone-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Max Pic Width",
								onAction : function(){
									editor.insertContent(' [max-pic-width] ');
								}
							},
							{
								type: 'menuitem',
								text : "Max Pic Height",
								onAction : function(){
									editor.insertContent(' [max-pic-height] ');
								}
							},
							{
								type: 'menuitem',
								text : "Max File Size",
								onAction : function(){
									editor.insertContent(' [max-pic-file-size-in-mb] ');
								}
							},
							{
								type: 'menuitem',
								text : "Chairman Role",
								onAction : function(){
									editor.insertContent(' [chairman-role] ');
								}
							},
							{
								type: 'menuitem',
								text : "Chairman Name",
								onAction : function(){
									editor.insertContent(' [salon-chairman] ');
								}
							},
							{
								type: 'menuitem',
								text : "Secretary Role",
								onAction : function(){
									editor.insertContent(' [secretary-role] ');
								}
							},
							{
								type: 'menuitem',
								text : "Secretary Name",
								onAction : function(){
									editor.insertContent(' [salon-secretary] ');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_judging", {
					text : "Judging Info",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Start Date",
								onAction : function(){
									editor.insertContent(' [judging-start-date] ');
								}
							},
							{
								type : 'menuitem',
								text : "End Date",
								onAction : function(){
									editor.insertContent(' [judging-end-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Result Date",
								onAction : function(){
									editor.insertContent(' [result-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Update End Date",
								onAction : function(){
									editor.insertContent(' [update-end-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Venue",
								onAction : function(){
									editor.insertContent(' [judging-venue] ');
								}
							},
							{
								type: 'menuitem',
								text : "Address",
								onAction : function(){
									editor.insertContent(' [judging-venue-address] ');
								}
							},
							{
								type: 'menuitem',
								text : "Location Map",
								onAction : function(){
									editor.insertContent(' [judging-venue-location-map] ');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_exhibition", {
					text : "Exhibition Info",
					getSubmenuItems : function() {
						return "yps_exhibition_venue yps_exhibition_chair yps_exhibition_guest yps_exhibition_other ";
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_exhibition_venue", {
					text : "Venue Info",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Exhibition Name",
								onAction : function(){
									editor.insertContent(' [exhibition-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Start Date",
								onAction : function(){
									editor.insertContent(' [exhibition-start-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "End Date",
								onAction : function(){
									editor.insertContent(' [exhibition-end-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Venue",
								onAction : function(){
									editor.insertContent(' [exhibition-venue] ');
								}
							},
							{
								type: 'menuitem',
								text : "Address",
								onAction : function(){
									editor.insertContent(' [exhibition-venue-address] ');
								}
							},
							{
								type: 'menuitem',
								text : "Location Map",
								onAction : function(){
									editor.insertContent(' [exhibition-venue-location-map] ');
								}
							},
							{
								type: 'menuitem',
								text : "Invitation Image",
								onAction : function(){
									// editor.insertContent(' [exhibition-invitation-img] ');
									editor.execCommand('InsertImage', false, '[salon-website]/salons/[yearmonth]/img/[exhibition-invitation-img]');
								}
							},
							{
								type: 'menuitem',
								text : "Email Header Image",
								onAction : function(){
									// editor.insertContent(' [exhibition-email-header-img] ');
									editor.execCommand('InsertImage', false, '[salon-website]/salons/[yearmonth]/img/[exhibition-email-header-img]');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_exhibition_chair", {
					text : "Exhibition Chair",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Role",
								onAction : function(){
									editor.insertContent(' [exhibition-chair-role] ');
								}
							},
							{
								type: 'menuitem',
								text : "Name",
								onAction : function(){
									editor.insertContent(' [exhibition-chair-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Position",
								onAction : function(){
									editor.insertContent(' [exhibition-chair-position] ');
								}
							},
							{
								type: 'menuitem',
								text : "Avatar",
								onAction : function(){
									// editor.insertContent(' [exhibition-chair-avatar] ');
									editor.execCommand('InsertImage', false, '[salon-website]/salons/[yearmonth]/img/[exhibition-chair-avatar]');
								}
							},
							{
								type: 'menuitem',
								text : "Profile",
								onAction : function(){
									editor.insertContent(' [exhibition-chair-blob] ');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_exhibition_guest", {
					text : "Guest of Honor",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Role",
								onAction : function(){
									editor.insertContent(' [exhibition-guest-role] ');
								}
							},
							{
								type: 'menuitem',
								text : "Name",
								onAction : function(){
									editor.insertContent(' [exhibition-guest-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Position",
								onAction : function(){
									editor.insertContent(' [exhibition-guest-position] ');
								}
							},
							{
								type: 'menuitem',
								text : "Avatar",
								onAction : function(){
									// editor.insertContent(' [exhibition-guest-avatar] ');
									editor.execCommand('InsertImage', false, '[salon-website]/salons/[yearmonth]/img/[exhibition-guest-avatar]');
								}
							},
							{
								type: 'menuitem',
								text : "Profile",
								onAction : function(){
									editor.insertContent(' [exhibition-guest-blob] ');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_exhibition_other", {
					text : "Other Dignitories",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Role",
								onAction : function(){
									editor.insertContent(' [exhibition-other-role] ');
								}
							},
							{
								type: 'menuitem',
								text : "Name",
								onAction : function(){
									editor.insertContent(' [exhibition-other-name] ');
								}
							},
							{
								type: 'menuitem',
								text : "Position",
								onAction : function(){
									editor.insertContent(' [exhibition-other-position] ');
								}
							},
							{
								type: 'menuitem',
								text : "Avatar",
								onAction : function(){
									// editor.insertContent(' [exhibition-other-avatar] ');
									editor.execCommand('InsertImage', false, '[salon-website]/salons/[yearmonth]/img/[exhibition-other-avatar]');
								}
							},
							{
								type: 'menuitem',
								text : "Profile",
								onAction : function(){
									editor.insertContent(' [exhibition-other-blob] ');
								}
							},
						];
					}
				});
				editor.ui.registry.addNestedMenuItem("yps_others", {
					text : "Other Info",
					getSubmenuItems : function() {
						return [
							{
								type: 'menuitem',
								text : "Section-wise Cut-off Table",
								onAction : function(){
									editor.insertContent(' [cut-off-table] ');
								}
							},
							{
								type: 'menuitem',
								text : "Catalog Release",
								onAction : function(){
									editor.insertContent(' [catalog-release-date] ');
								}
							},
							{
								type: 'menuitem',
								text : "Catalog Download File",
								onAction : function(){
									editor.insertContent(' [catalog-file-download] ');
								}
							},
							{
								type: 'menuitem',
								text : "Catalog View File",
								onAction : function(){
									editor.insertContent(' [catalog-file-view] ');
								}
							},
							{
								type: 'menuitem',
								text : "Salon Patronage",
								onAction : function(){
									editor.insertContent(' [recognition-data] ');
								}
							},
							{
								type: 'menuitem',
								text : "Salon Partners",
								onAction : function(){
									editor.insertContent(' [partner-data] ');
								}
							},
						];
					}
				});
				<?php
					if (sizeof($yps_custom_tags) > 0) {
				?>
				editor.ui.registry.addNestedMenuItem("yps_custom", {
					text : "Custom",
					getSubmenuItems : function() {
						return yps_custom_tags;
					}
				});
				<?php
					}
				?>
			},
		});

		// Event handle to load Blob Modal
		$(".edit-blob").click(function(e){
			$("#blob_yearmonth").val($(this).attr("data-yearmonth"));
			$("#blob_type").val($(this).attr("data-blob-type"));
			$("#blob_file").val($(this).attr("data-blob"));
			$("#blob_input").val($(this).attr("data-blob-input"));
			$("#blob_file_name").html($(this).attr("data-blob"));
			// if ($(this).attr("data-custom-tags") != "")
			// 	$("#blob-custom-tags").html("Can also use <b>" + $(this).attr("data-custom-tags") + "</b> tags");
			tinymce.get("blob_content").setContent("Loading content...");
			$("#edit_blob_modal").modal("show");
		});
	});
</script>

<!-- Allow tinymce link field edit -->
<script>
	$(document).on('focusin', function(e) {
	    if ($(e.target).closest(".tox-dialog").length) {
	        e.stopImmediatePropagation();
	    }
	});
</script>

<!-- Edit Rules Action Handlers -->
<script>
	// Get Rules Text from server
	$(document).ready(function(){
		$("#edit_blob_modal").on("shown.bs.modal", function(){
			if ($("#blob_type").val() != "textarea") {
				// Load Rules from file on server
				let yearmonth = $("#blob_yearmonth").val();
				let blob_type = $("#blob_type").val();
				let blob_file = $("#blob_file").val();
				$('#loader_img').show();
				$.post("ajax/get_blob.php", {yearmonth, blob_type, blob_file}, function(response){
					$('#loader_img').hide();
					response = JSON.parse(response);
					if(response.success){
						// $("#er_contest_rules").val(response.rules);
						$("#blob_file").val(response.blob_file);
						tinymce.get("blob_content").setContent(response.blob_content);
					}
					else{
						swal({
								title: "Load Failed",
								text: "Unable to load blob: " + response.msg,
								icon: "warning",
								confirmButtonClass: 'btn-warning',
								confirmButtonText: 'OK'
						});
					}
				});
			}
		});

		// Update rules back to server
		let vaidator = $('#edit_blob_form').validate({
			rules:{
			},
			messages:{
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {
				// Assemble Data
				let formData = new FormData();
				formData.append("yearmonth", $("#blob_yearmonth").val());
				formData.append("blob_type", $("#blob_type").val());
				formData.append("blob_file", $("#blob_file").val());
				formData.append("blob_content", tinymce.get("blob_content").getContent());

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_blob.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Blob Saved",
										text: $("#blob_file").val() + " has been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Update blob file name into the form
								let blob_input = $("#blob_input").val();
								$("#" + blob_input).val($("#blob_file").val());
								$("#edit_blob_modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: $("#blob_file").val() + " could not be saved: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
				return false;
			},
		});
	});

</script>

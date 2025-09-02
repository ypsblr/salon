<?php
?>

<!-- Edit Posting Data Action Handlers -->
<script>
	// Populate fields from data in the link
	$(document).ready(function(){
		var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		var current_date = yyyy + "-" + mm + "-" + dd;

		// Global Data
		var posting_operation = "";
		var yearmonth = 0;
		var posting_type = "";
		var profile_id = 0;
		var profile_name = "";
		var currency = "INR";
		var posting_data = {
			yearmonth: 0,
			profile_id: 0,
			posting_type: "",
			posting_date: current_date,
			bank_account: "",
			cash_award: 0,
			tracking_no: "",
			tracking_website: "https://www.indiapost.gov.in",
			post_operator: "",
		};

		function edit_posting_handler(edit_id) {
			$("#posting-type-disp").html($(edit_id).attr("data-posting-type"));
			$("#posting-profile-name").html($(edit_id).attr("data-profile-name"));
			// Populate Global Data
			posting_operation = $(edit_id).attr("data-operation");
			yearmonth = $(edit_id).attr("data-yearmonth");
			posting_type = $(edit_id).attr("data-posting-type");
			profile_id = $(edit_id).attr("data-profile-id");
			profile_name = $(edit_id).attr("data-profile-name");
			if (posting_type == "CASH")
				currency = $(edit_id).attr("data-currency");
			if (posting_operation == "add") {
				posting_data = {
					yearmonth: $(edit_id).attr("data-yearmonth"),
					profile_id: $(edit_id).attr("data-profile-id"),
					posting_type: $(edit_id).attr("data-posting-type"),
					posting_date: yyyy + "-" + mm + "-" + dd,
					bank_account: $(edit_id).attr("data-bank-account"),
					cash_award: $(edit_id).attr("data-cash-award"),
					tracking_no: "",
					tracking_website: ($(edit_id).attr("data-posting-type") == "CASH" ? "" : "https://www.indiapost.gov.in"),
					post_operator: ($(edit_id).attr("data-posting-type") == "CASH" ? "State Bank of India" : "India Posts"),
				};
			}
			else {
				posting_data = JSON.parse($(edit_id).attr("data-posting-data"));
			}
			// Show MODAL
			$("#edit-posting-modal").modal("show");
		}

		$(".edit-posting").click(function(){
			edit_posting_handler("#" + $(this).get(0).id);
		});

		$("#edit-posting-modal").on("shown.bs.modal", function(){
			$("#posting-type-disp").html(posting_type);
			$("#posting-profile-name").html(profile_name);
			// Hidden Fields
			$("#posting-yearmonth").val(yearmonth);
			$("#posting-profile-id").val(profile_id);
			$("#posting-type").val(posting_type);
			$("#posting-operation").val(posting_operation);
			$("#posting-currency").val(currency);
			// Form fields
			$("#posting-date").val(posting_data.posting_date);
			$("#bank-account").val(posting_data.bank_account);
			$("#cash-award").val(posting_data.cash_award);
			$("#tracking-no").val(posting_data.tracking_no);
			$("#tracking-website").val(posting_data.tracking_website);
			if (posting_data.posting_type == "CASH") {
				$("#tracking-no-disp").hide();
				$("#tracking-website-disp").hide();
				$("#bank-account-disp").show();
				$("#cash-award-disp").show();
			}
			else {
				$("#tracking-no-disp").show();
				$("#tracking-website-disp").show();
				$("#bank-account-disp").hide();
				$("#cash-award-disp").hide();
			}
			$("#post-operator").val(posting_data.post_operator);
		});

		function create_edit_details() {
			// Update Posting Data from the form so that edited data appears
			posting_data.posting_date = $("#posting-date").val();
			posting_data.bank_account = $("#bank-account").val();
			posting_data.cash_award = $("#cash-award").val();
			posting_data.tracking_no = $("#tracking-no").val();
			posting_data.tracking_website = $("#tracking-website").val();
			posting_data.post_operator = $("#post-operator").val();

			// Create html with updated values
			let html = "";
			if (posting_type == "CASH") {
				html  = currency + " " + posting_data.cash_award + " transferred to ";
				html += "<br>" + posting_data.bank_account;
				html += "<br>on " + posting_data.posting_date;
			}
			else if (posting_type == "AWARD") {
			 	html += "Awards posted on " + posting_data.posting_date;
				html += "<br>thru " + posting_data.post_operator;
				html += "<br>tracking no : " + posting_data.tracking_no;
			}
			else {
				html += "Catalog posted on " + posting_data.posting_date;
				html += "<br>thru " + posting_data.post_operator;
				html += "<br>tracking no : " + posting_data.tracking_no;
			}
			html += "<br>";
			html += "<a data-yearmonth='" + yearmonth + "' ";
			html += "data-profile-id='" + profile_id + "' ";
			html += "data-profile-name='" + profile_name + "' ";
			html += "data-bank-account='" + posting_data.bank_account + "' ";
			html += "data-cash-award='" + posting_data.cash_award + "' ";
			html += "data-operation='edit' ";
			html += "data-posting-type='" + posting_type + "' ";
			html += "data-posting-data='" + JSON.stringify(posting_data) + "' ";
			html += "id='edit-" + profile_id + "-" + posting_type + "' ";
			html += "class='edit-posting' >";
			html += "<i class='fa fa-edit'></i> Edit</a>";

			$("#" + profile_id + "-" + posting_type).html(html);
		}

		// Update rules back to server
		let vaidator = $('#edit-posting-form').validate({
			rules:{
				posting_date : {
					required : true,
					date_max : current_date,
				},
				bank_account : {
					required : function() { return (posting_type == "CASH"); },
				},
				cash_award : {
					required : function() { return (posting_type == "CASH"); },
				},
				tracking_no : {
					required : function() { return (posting_type != "CASH"); },
				},
				tracking_website : {
					required : function() { return (posting_type != "CASH"); },
				},
				posting_operator : {
					required : true,
				},
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
				let formData = new FormData(form);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_posting.php",
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
										title: "Posting Data Saved",
										text: "Posting Data has been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								create_edit_details();
								$(".edit-posting").click(function(){
									edit_posting_handler("#edit-" + profile_id + "-" + posting_type);
								});
								$("#edit-posting-modal").modal('hide');
							}
							else{
								swal({
										title: "Save Failed",
										text: "Posting Data could not be saved: " + response.msg,
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

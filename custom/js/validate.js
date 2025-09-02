
$(document).ready(function() {

// Additional Custom Validation Methods
// =====================================
// Validate a value against values already in the list
jQuery.validator.addMethod(
	"in_list",
	function(value, element, list) {
		if (list.includes(value))
			return this.optional(element);
		else
			return true;
	},
	"This value already exists"
);

jQuery.validator.addMethod(
	"filesizekb",
	function (value, element, param) {
		return this.optional(element) || (element.files[0].size <= param * 1024);
	},
	"File size cannot exceed {0} KB"
);

// Salutation and Gender must match
jQuery.validator.addMethod(
	"salutation_match",
	function(value, element, params) {
		var gender = value;
		if ( (gender == "FEMALE" && $("#sal_mr").prop("checked")) ||
				(gender == "MALE" && ($("#sal_ms").prop("checked") || $("#sal_mrs").prop("checked")) ) )
			return this.optional(element);
		else
			return true;
	},
	"Salutation does not match the Gender"
);

function ymdDate(datestr) {
	let year, month, date;

	if (typeof datestr == "string") {
		if (datestr.split("-").length == 3) {
			[year, month, date] = datestr.split("-");
			return new Date(year, month, date);
		}
		else if (datestr.split("/").length == 3) {
			[year, month, date] = datestr.split("/");
			return new Date(year, month, date);
		}

		return null;
	}
	else
		return datestr;		// assume date object
}

// Date Range validation
// To be consistent with MySQL and PHP, Dates must be in YYYY-MM-DD format
jQuery.validator.addMethod (
	"date_range",
	function(value, element, params) {
		let input_date = ymdDate(value);
		let date_1, date_2, earliest_date, latest_date;
		date_1 = ymdDate(params[0]);
		date_2 = ymdDate(params[1]);
		if (input_date == null || date_1 == null || date_2 == null) {
			return this.optional(element);
		}

		if (date_1 > date_2) {
			earliest_date = date_2;
			latest_date = date_1;
		}
		else {
			earliest_date = date_1;
			latest_date = date_2;
		}

		if (input_date < earliest_date || input_date > latest_date)
			return this.optional(element);
		else
			return true;
	},
	"Date of Birth exceeds the range of {0} - {1}"
);

function spam_found(text, list) {
	if (list.some(function (spam_text) {
						return text.toLowerCase().includes(spam_text);
					}) )
		return;
	else
		return true;
}

// Reject spam emails from history
jQuery.validator.addMethod(
	"spam_email_filter",
	function(value, element, params) {
		var spam_email_domains = ["pochtar.top", "vykupom.info", "bestmail.club", "mailllc.top", "poreglot.ru", "domailnew.com", "dfokamail.com", ];
		return this.optional(element) || spam_found(value, spam_email_domains);
	},
	"Sorry ! Invalid Email ID ! Cannot send Email !"
);

// Reject subject and queries containing text used in spam emails
jQuery.validator.addMethod(
	"spam_text_filter",
	function(value, element, params) {
		var spam_text_strings = ["offer", "call center", "call-center", "proposal", "business", "special friend", "know each other", "million", "seo", "traffic", "optimiz", "charity", "funds", "bank", "beneficiary", "private"];
		return this.optional(element) || spam_found(value, spam_text_strings);
		},
	"Sorry ! Invalid Text ! Cannot send Email !"
);

// Reject incorrectly formed names
function name_ok (name) {
	// If name does not contain space separating first and last name, reject
	if (! name.includes(" "))
		return false;

	for (i = 0; i < name.length; ++i) {
		if (! ( (name.substr(i, 1) >= "A" && name.substr(i, 1) <= "Z") || (name.substr(i, 1) >= "a" && name.substr(i, 1) <= "z") || name.substr(i, 1) == " " ))
			return false;
	}
	return true;
}

jQuery.validator.addMethod(
	"spam_name_filter",
	function(value, element, params) {
		return this.optional(element) || name_ok(value);
	},
	"Incorrect name ! Enter Full Name, not just the first name ! Name cannot contain numbers and special characters !"
);

// Reject unallowed club_names
function club_name_ok (club_name) {
	// If name does not contain space separating first and last name, reject
	if (club_name.match(/yps/i) || club_name.match(/^youth.*photo.*soc.*$/i))
		return;

	return true;
}

jQuery.validator.addMethod(
	"club_name",
	function(value, element, params) {
		return this.optional(element) || club_name_ok(value);
	},
	"Incorrect Club name ! Club names like YPS or Youth Photographic Society are not allowed !"
);

/*
** methods moved from additional_methods.js
*/
// Accept a value from a file input based on a required mimetype
jQuery.validator.addMethod(
	"accept",
	function( value, element, param ) {

		// Split mime on commas in case we have multiple types we can accept
		var typeParam = typeof param === "string" ? param.replace( /\s/g, "" ) : "image/*";
		var optionalValue = this.optional( element );
		var i, file, regex;

		// Element is optional
		if ( optionalValue ) {
			return optionalValue;
		}

		if ( $( element ).attr( "type" ) === "file" ) {

			// Escape string to be used in the regex
			// see: https://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
			// Escape also "/*" as "/.*" as a wildcard
			typeParam = typeParam
					.replace( /[\-\[\]\/\{\}\(\)\+\?\.\\\^\$\|]/g, "\\$&" )
					.replace( /,/g, "|" )
					.replace( /\/\*/g, "/.*" );

			// Check if the element has a FileList before checking each file
			if ( element.files && element.files.length ) {
				regex = new RegExp( ".?(" + typeParam + ")$", "i" );
				for ( i = 0; i < element.files.length; i++ ) {
					file = element.files[ i ];

					// Grab the mimetype from the loaded file, verify it matches
					if ( !file.type.match( regex ) ) {
						return false;
					}
				}
			}
		}

		// Either return true because we've validated each file, or because the
		// browser does not support element.files and the FileList feature
		return true;
	},
	"This type of file is not valid for this upload."
);

// Check for permitted file extensions
jQuery.validator.addMethod(
	"extension",
	function( value, element, param ) {
		// param = typeof param === "string" ? param.replace( /,/g, "|" ) : "png|jpe?g|gif";
		param = typeof param === "string" ? param.replace( /,/g, "|" ) : "jpe?g";
		return this.optional( element ) || value.match( new RegExp( "\\.(" + param + ")$", "i" ) );
	},
	"Please select a file with one of {0} extensions."
);

// Do not allow special chars in text
jQuery.validator.addMethod(
	"nosplchars",
	function( value, element, param ) {
		var splchars = /[!@#$%^&*()_+\-=\[\]{};:"\\|,.<>\/?~]/;
		return this.optional( element ) || (! splchars.test(value) );
	},
	"Cannot use special characters."
);

// Methods added in December 2021 to enforce English inputs in names, addresses, cities and picture titles
// english-name
jQuery.validator.addMethod(
	"english_name",
	function( value, element, param ) {
		var english_name = /^[a-zA-Z '`.\-]*$/;
		return ( this.optional( element ) || english_name.test(value) );
	},
	"Name contains non-English characters and numerals."
);

// English Address
jQuery.validator.addMethod(
	"english_address",
	function( value, element, param ) {
		var english_address = /^[a-zA-Z0-9'`.,:;&#?_+=<>()[\s\"\/\-\]\\]*$/;
		return ( this.optional( element ) || english_address.test(value) );
	},
	"Address contains non-English characters."
);






$('#lostpassword').validate({
			rules:{
				email:{ required:true, email:true,
					remote:{
						url:'ajax/getAjaxValidate.php',
						type:'post',
						data:{
							emailID:function() {return $('#email').val()},
							},
						}
				},
			},
			messages:{

				email:{
					required:"",
					remote: "",
				},
			},
			errorElement:"div",
			errorClass:"valid-error",
			submitHandler:function(form){
				form.submit();
			},
	});



$('#signinForm').validate({
			rules:{
				username:{ required:true,
					remote:{
						url:'ajax/getAjaxValidate.php',
						type:'post',
						data:{
							entry_id:function() {return $('#username').val()},
							},
						}
				},
				password:  { required:true, },
			},
			messages:{
				password:{
					required:'',
				},
				username:{
					required:'',
					remote: "",
				},
			},
			errorElement:"div",
			errorClass:"valid-error",
			submitHandler:function(form){
				form.submit();
			},
	});

$('#changePassword').validate({
			rules:{
				npassword:{ required:true, },
				opassword:  { required:true,
					remote:{
						url:'ajax/getAjaxValidate.php',
						type:'post',
						data:{
							opassword:function() {return $('#opassword').val()},
							},
						}
				},

			},
			messages:{
				npassword:{
					required:'Please enter new password.',
				},
				opassword:{
					required:'Please enter old password.',
					remote: "Invaild old password ",
				},

			},
			errorElement:"div",
			errorClass:"valid-error",
			submitHandler:function(form){
				form.submit();
			},
		});

});

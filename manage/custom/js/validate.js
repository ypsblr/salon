
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

// Salutation and Gender must match
// Attached to Gender
// value = gender
// params = value of salutation field
jQuery.validator.addMethod(
	"salutation_match",
	function(value, element, params) {
		var gender = value;
		var salutation = params;
		if ( (gender == "F" && salutation == "Mr") ||
				(gender == "M" && (salutation =="Ms." || salutation == "Mrs")) )
			return this.optional(element);
		else
			return true;
	},
	"Salutation does not match the Gender"
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
		return;

	for (i = 0; i < name.length; ++i) {
		if (! ( (name.substr(i, 1) >= "A" && name.substr(i, 1) <= "Z") || (name.substr(i, 1) >= "a" && name.substr(i, 1) <= "z") || name.substr(i, 1) == " " ))
			return;
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

// Compare against a Minimum Date
// Param - Date String in YYYY-MM-DD format or jQuery Selector
jQuery.validator.addMethod(
	"date_min",
	function(value, element, params) {
		if (/\d+\-\d+\-\d+/.test(params))
			return (value >= params);
		else if (value >= $(params).val())
			return true;
		else
			return this.optional(element);
	},
	"Date is less than the minimum limit"
);

// Compare against a Maximum Date
// Param - Date String in YYYY-MM-DD format or jQuery Selector
jQuery.validator.addMethod(
	"date_max",
	function(value, element, params) {
		// Check if the parameter is a string in date format
		if (/\d+\-\d+\-\d+/.test(params))
			return (value <= params);
		else if (value <= $(params).val())
			return true;
		else
			return this.optional(element);
	},
	"Date exceeds the maximum limit"
);

// Compare against a Maximum Date - can be used to limit the age
jQuery.validator.addMethod(
	"not_less_than",
	function(value, element, params) {
		if (value > $(params).val())
			return true;
		else
			return this.optional(element);
	},
	"Value is less than the minimum limit"
);

// Compare against a Maximum Date - can be used to limit the age
jQuery.validator.addMethod(
	"not_more_than",
	function(value, element, params) {
		if (value < $(params).val())
			return true;
		else
			return this.optional(element);
	},
	"Value is more than the maximum limit"
);

});


$(document).ready(function(){
	
	var x4 = 1; //Initial field counter is 1
		var maxField4 = 4; //Input fields increment limitation
	var addButton4 = $('.add_button4'); //Add button selector
	var wrapper4 = $('.field_wrapper4'); //Input field wrapper
	var fieldHTML4 = '<div><label class="col-sm-3 control-label">Browse Images...*</label><div class="col-sm-4"><input type="file"  name="files[]" required/></div><div class="col-sm-4"><input type="text" class="form-control" name="img_title[]" onkeyup="countChar(this)" placeholder="Image Title" required></div><a href="javascript:void(0);" class="remove_button4" title="Remove field"><img src="remove-icon.png"/></a></div>'; //New input field html 
	
	
	$(addButton4).click(function(){ //Once add button is clicked
		if(x4 < maxField4){ //Check maximum number of input fields
			x4++; //Increment field counter
			$(wrapper4).append(fieldHTML4); // Add field html
		}
	});
	$(wrapper4).on('click', '.remove_button4', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x4--; //Decrement field counter
	});
	
	
});

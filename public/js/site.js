
/**
 * Add jQuery Validation plugin method for a valid password
 *
 * Valid passwords contain at least one letter and one number
 */
 $.validator.addMethod('validPassword',
 function(value, element, param) {
	 if (value != '') {
		 if (value.match(/.*[a-z]+.*/i) == null) {
			 return false;
		 }
		 if (value.match(/.*\d+.*/) == null) {
			 return false;
		 }
	 }
	 
	 return true;
 },
 'Must contain at least one letter and one number'
);
/**
* Add jQuery Validation plugin method for a valid password
*
* Valid passwords contain at least one letter and one number
*/
$.validator.addMethod('validAmount',
function(value, element, param) {
	if (value != '') {
		if (value.match(/(?!^0*$)(?!^0*\.0*$)^\d*((\.\d{1,2})|(,\d{1,2}))?$/) == null) {
			return false;
		}
	}
	
	return true;
},
'Proszę podać kwotę w odpowiednim formacie z dokładnością do 2 miejsc po przecinku'
);
/**
* Add jQuery Validation plugin method for a valid password
* 
* Valid passwords contain at least one letter and one number
*/
$.validator.addMethod('validDate',
function(value, element, param) {
	if (value != '') {
		if (value.match(/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/) == null) {
			return false;
		}
	}
	
	return true;
},
'Data powinna być w formacie YYYY-mm-dd'
);


$(document).ready(function() {
});

$(document).ready(function() {
    $( ".datepicker" ).datepicker(
               {
                       dateFormat: "yy-mm-dd"
               }
       );
 });
 $(document).ready(function(){
    $("#datepicker-13").datepicker().datepicker('setDate', new Date());
 }); 

 function removeCategory(btn) {
	var inputDiv = $(btn).parent()
	var categoryWrapDiv = $(btn).parent().parent();
	var numberOfInputs = categoryWrapDiv.find('.input-group').length;
	var numberOfHiddenInputs = categoryWrapDiv.find('.input-group:hidden').length
	if (numberOfInputs - numberOfHiddenInputs <= 1) {
		var errorDiv = categoryWrapDiv.find('.setting_error');
		if (errorDiv.length == 0){
			categoryWrapDiv.append("<div class='setting_error'><span>Nie można usunąć wszystkich pól!</span></div>")
			errorDiv = categoryWrapDiv.find('.setting_error');
		}
		errorDiv.show();
		errorDiv.fadeOut(1800);
	} else {
		var input = inputDiv.find('input').first();
		var inputName = input.attr('name');
		if (inputName.includes("new")){
			inputDiv.remove();
		} else {
			if (validateSetting(inputName)){
				$('#deleteModal').on('show.bs.modal', function () {
					var modal = $(this)
					modal.find('.modal-content').attr('name', inputName)
					modal.find('.category-name').text(input.val());
				  });
				$('#deleteModal').modal('show');
			} else {
				inputDiv.hide();
				input.attr('name', inputName + '_del');
			}
		}
	}
 }

$('#formSettingsCategories input').change(function() {
	$(this).attr('name', $(this).attr('name') + '_mod');
});

function addCategory(btn, setting){
	var form = $(btn).parent().parent();//form
	var input = form.find('.input-group').last();
	var newInputId = getRandomInt(0,999999999);
	var newInputGroup = input.clone(true);
	newInputGroup.show();
	var newInput = newInputGroup.find('input').first();
	var newInputName = setting + '_' + newInputId +'_new';
	newInput.attr('name', newInputName);
	newInput.val('');
	switch (setting) {
		case 'expense':
			newInput.attr('placeholder', 'Podaj nazwę kategorii');
			$('#expenseCategories').append(newInputGroup);
			break;
		case 'income':
			newInput.attr('placeholder', 'Podaj nazwę kategorii');
			$('#incomeCategories').append(newInputGroup);
			break;
		case 'payment':
			newInput.attr('placeholder', 'Podaj sposób płatności');
			$('#paymetMethods').append(newInputGroup);
			break;
	}
}

function getRandomInt(min, max) {
	min = Math.ceil(min);
	max = Math.floor(max);
	return Math.floor(Math.random() * (max - min) + min);
}

function validateSetting(setting){
	var status = false;
	$.ajax({
		url: "/settings/validate?setting="+setting,
		async: false
	})
		.done(function(data){
			if (data != 'false'){
				status = true;
			}
		});
	return status;
}

function closeDeleteModal(){
	$('#deleteModal').modal('hide')
}

function confirmCategoryRemoval(btn){
	$('#deleteModal').modal('hide')
	var modalContent = $(btn).parent().parent();
	var inputName = modalContent.attr('name');
	var input = $('input[name="'+inputName+'"]');
	var inputDiv = input.parent();
	inputDiv.hide();
	input.attr('name', inputName + '_del');
}

function confirmRemoveCategory(btn){
	if(confirm("Potwierdź usunięcie kategorii")){
	var inputDiv = $(btn).parent()
	var input = inputDiv.find('input').first();
	var inputName = input.attr('name');
	inputDiv.parent().find('span').first().hide();
	inputDiv.hide();
	input.attr('name', inputName + 'ed');
	}
}
function undoRemoveCategory(btn){
	var inputDiv = $(btn).parent()
	inputDiv.parent().find('span').first().hide();
	inputDiv.removeClass('border');
	var input = inputDiv.find('input').first();
	var inputName = input.attr('name');
	inputName = inputName.replace('confirm', 'del');
	$(btn).hide();
}

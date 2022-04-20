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
/* 
$(document).ready(function() {
});

$(function() {
    $( ".datepicker" ).datepicker(
		{
			dateFormat: "yy-mm-dd"
		}
	);
 });
 $(document).ready(function(){
    $("#datepicker-13").datepicker().datepicker('setDate', new Date());	
 }); */
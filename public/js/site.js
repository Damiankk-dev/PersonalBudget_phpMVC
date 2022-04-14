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
 });
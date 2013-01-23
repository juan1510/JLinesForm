/**
 * Script jQuery para agregar nuevas lineas basadas en un template de campos
 * 
 * NOTA!
 * Este script depende de jquery.format.js
 *
 */
$(document).ready(function(){
	$(".add").click(function(){
		var template = jQuery.format(jQuery.trim($(this).siblings(".template").val()));
		var place = $(this).parents(".templateFrame:first").children(".templateTarget");
		var i = place.find(".rowIndex").length>0 ? place.find(".rowIndex").max()+1 : 0;
		$(template(i)).appendTo(place);
	});

	$(".remove").live("click", function() {
		$(this).parents(".templateContent:first").remove();
	});
});
/**
 * jQuery script para funciones alternas de JLinesForm
 * @author Juan David Rodriguez R. <jdrodriguez429@gmail.com> @juanda1015
 * @copyright 2013 - Juan David Rodriguez Ramirez
 * @license New BSD License
 * @category User Interface
 */
function in_array(name,data){
        var a = false;
        $.each(data, function(id, errors){
            if(name === id){
                a = true
                return false;
            }
        });
        return a;
}
jQuery.extend({
    JafterValidate : function(form,hasError,models,action) {
         var contador = $('body').find('.rowIndex').max();
         var send = null;
         if(!hasError){
             $.ajax({
                     type:"POST",
                     url:action,
                     data: $("#"+form).serialize()+"&jlines="+form,
                     dataType : "json",
                     async:false,
                     success:function(data){
                            if(!$.isEmptyObject(data)){
                                send = false;
                                $.each(data, function(id, errors){
                                      $("#"+id).parents(".control-group").removeClass("success");
                                      $("#"+id).parents(".control-group").addClass("error");
                                      $.each(errors,function(index,error){
                                            $("#"+id+"_em").text(error);
                                            return false;
                                      });
                                      $("#"+id+"_em").show();
                                      $.each(models,function(model,attributes){
                                         for(i=0;i<=contador;i++){
                                             $.each(attributes,function(name,value){
                                                 if(!in_array(model+"_"+i+"_"+name,data)){
                                                      $("#"+model+"_"+i+"_"+name).parents(".control-group").removeClass("error");
                                                      $("#"+model+"_"+i+"_"+name).parents(".control-group").addClass("success");
                                                      $("#"+model+"_"+i+"_"+name+"_em").hide();
                                                      $("#"+model+"_"+i+"_"+name+"_em").text("");
                                                 }
                                             });
                                         }
                                      });
                                  });
                           }else{
                               send = true;
                               $.each(models,function(model,attributes){
                                     for(i=0;i<=contador;i++){
                                         $.each(attributes,function(name,value){
                                                 $("#"+model+"_"+i+"_"+name).parents(".control-group").removeClass("error");
                                                 $("#"+model+"_"+i+"_"+name).parents(".control-group").addClass("success");
                                                 $("#"+model+"_"+i+"_"+name+"_em").hide();
                                                 $("#"+model+"_"+i+"_"+name+"_em").text("");
                                         });
                                     }
                                  });
                           }
                     },
                });
         }
         return send;

    } 
});

$(function(){
  $(".add").click(function(){
            var template = jQuery.format(jQuery.trim($(this).siblings(".template").val()));
            var place = $(this).parents(".templateFrame:first").children(".templateTarget");
            var i = place.find(".rowIndex").length>0 ? place.find(".rowIndex").max()+1 : 0;
            $(template(i)).appendTo(place);
  });
        
  $("body").on("click",".remove", function() {
            $(this).parents(".templateContent:first").fadeOut('slow',function(){
                $(this).remove();
            });
  });
});

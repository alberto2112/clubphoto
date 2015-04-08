/**
 * Require JQuery
 */
$(function(){
  $("#showrules").click(function(){
    
    $("#rules").toogle();
    
    //var bg = $("#cv-photo").css("background-image");
    //document.getElementById( "cv-photo" ).style.backgroundImage = "url('/clubphoto/images/tercios.png'), "+bg;
    
    /*
    var canvas = document.getElementById('cv-photo');
    var context = canvas.getContext('2d');
    
    var canvas_w = 2048;
    var canvas_h = 1365;
    
    $("#cv-photo").css("display","block");
    $("#cv-photo").css("width",canvas_w);
    $("#cv-photo").css("height",canvas_h);
    $("#cv-photo").css("left", Math.round((canvas_w / 2) + 235)+"px");
    
    context.beginPath();
    context.moveTo(0, 0);
    context.lineTo(canvas_w, canvas_h);
    context.lineWidth = 1;

    // set line color
    context.strokeStyle = '#ff00ff';
    context.stroke();
    */
  });
});
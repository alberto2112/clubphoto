function ShowModalBox(LayerID){
  $("#modal-layer-bg").addClass("modal_show");
  $("#"+LayerID).addClass("modal_show");
  cur_modal_box_id = LayerID;
}

function HideModalBoxes(LayerID){
  if( $("#modal-layer-bg").hasClass("modal_show") ) {
    $("#"+LayerID).removeClass("modal_show");
    $("#modal-layer-bg").removeClass("modal_show");
  }
}
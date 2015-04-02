<!-- Modal boxes -->
    <div class="modal_layer_bg" id="modal-layer-bg">
      <!-- Modal box - Photos to trash -->
      <div class="modal_box" id="mb-del-selct">
        <p>Envoyer les photos s&eacute;lection&eacute;es &agrave; la corbeille</p>
        <div class="btn_wraper">
          <a href="#" class="button gray" onClick="HideModalBoxes('mb-del-selct');">Annuler</a>
          <a href="#" class="button red" onClick="delSelectedPhotos();">Continuer</a>
        </div>
      </div>
      <!-- Modal box - Photos to trash -->
      
      <!-- Modal box - Send to album -->
      <div class="modal_box" id="mb-snd-selct">
        <ul>
          <li><input type="text" name="txt_newalbum" palceholder="Nouveau album" /></li>
<?php
              $lof_albums = list_dirs(SYSTEM_ROOT.ALBUMS_DIR, false);
              if(count($lof_albums)>0){
                foreach($lof_albums as $album){
                  if(@is_readable($album.'config.php')===true)
                    $CFG = include $album.'config.php';
                  else
                    $CFG = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre
                  
                  echo '          <li><a href="#">'.get_arr_value($CFG, 'albumname').'</a></li>'."\n";
                }
                unset($CFG);
                unset($lof_albums);
              }
?>
        </ul>
        <div class="btn_wraper">
          <a href="#" class="button red">Continuer</a>
          <a href="#" class="button gray" onClick="HideModalBoxes('mb-snd-selct');">Annuler</a>
        </div>
      </div>
      <!-- Modal box - Send to album -->
    </div>
<!-- / Modal boxes -->

    <script type="text/javascript">
      var cur_modalbox_id;
      
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
      
      function delSelectedPhotos() {
       // JavaScript & jQuery Course - http://coursesweb.net/javascript/
        var selchbox = ""; //[];        // array that will store the value of selected checkboxes

        // gets all the input tags in frm, and their number
        var inpfields = document.getElementsByTagName('input');
        var nr_inpfields = inpfields.length;

        // traverse the inpfields elements, and adds the value of selected (checked) checkbox in selchbox
        for(var i=0; i<nr_inpfields; i++) {
          if(inpfields[i].type == 'checkbox' && inpfields[i].checked == true){
            //selchbox.push(inpfields[i].value);
            selchbox += inpfields[i].value+";";
          }
        }

        //alert(selchbox);
        document.location.href="<?php echo PUBLIC_ROOT.ADMIN_DIR.'managePhotos.php?'.URI_QUERY_ACTION.'=trash&'.URI_QUERY_ALBUM.'='.$_CODALBUM.'&'.URI_QUERY_PHOTO.'='  ?>"+selchbox ;
      }
      
      $(document).ready(function(){
        
        $(".cancel").click(function(){
            HideModalBoxes(cur_modalbox_id); 
        });
        
        $(document).keyup(function(e) {
          if(e.keyCode == 27) { // esc
            HideModalBoxes(cur_modalbox_id); 
          }
        });

      });
      
    </script>
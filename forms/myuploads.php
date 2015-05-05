<?php
  if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

// Get IP address
  $IP = getClient_ip();
  $LONGIP = @sprintf("%u",ip2long($IP)) | '0';

// Get and clean request vars
  $codalbum   = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);

  if($codalbum==false){
    // Open error log
    $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/events.log');
    $ERRLOG->insert('EMPTY ALBUM CODE - '.$IP.' - ['.FORMS_DIR.'myuploads.php]', true);
    echo '<h1>Album introuvable!</h1>';
    exit;
  }else{
    // Open logs
    $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/errors.log');
  }

  // Charger les parametres de l'album
  // Si le fichier config.php n'existe pas
  // remplir l'array avec des parametres par defaut
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php'))
    $CONFIG = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  else
    $CONFIG = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';// remplir l'array avec des parametres par defaut

// NOTE: Revoir ça; pourquoi le renvoyer quand on peut lui montrer un bouton pour qu'il telecharge quelque chose?
  //if($USER_SESSION==false){
// NOTE: Donc, on se fie au RKEY et pas au UKEY
  if(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($CONFIG, 'RKEY')){
    $ERRLOG->insert('EMPTY RKEY - '.$IP, true);
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
    exit;
  }

  $_SHOW_RATES   = (get_arr_value($CONFIG, 'showrateforuploads','0')=='1');
  $_ALLOW_DELETE = get_arr_value($CONFIG, 'allowphotomanag','0');
  $_CAN_UPLOAD   = !out_of_date(get_arr_value($CONFIG,'upload-from',false), get_arr_value($CONFIG,'upload-to',false));

// Determiner si le photographe peut effacer ses photos
  if($_ALLOW_DELETE=='2'){
    $_ALLOW_DELETE=true;
  }elseif($_ALLOW_DELETE=='1'){
    $_ALLOW_DELETE = $_CAN_UPLOAD;
  }else{
    $_ALLOW_DELETE = false;
  }

// Recuperer USER_KEY (Cookie)
  $USER_SESSION = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$codalbum, false);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>MJC-CP - Mes telechargements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/modalboxes.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/myuploads.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
  </head>
  <body>
<!-- Modal boxes -->
    <div class="modal_layer_bg" id="modal-layer-bg">
      <!-- Modal box - Photos to trash -->
      <div class="modal_box" id="mb-delete-photo">
        <p>Clickez sur &quot;Continuer&quot; pour supprimer la photo.</p>
        <div class="btn_wraper">
          <a href="#" class="button gray" onClick="HideModalBoxes('mb-delete-photo');">Annuler</a>
          <a href="#" class="button red" id="mbox-btn-delete">Continuer</a>
        </div>
      </div>
      <!-- /Modal box - Photos to trash -->
      <!-- Modal box - Update label n description -->
      <div class="modal_box" id="mb-update-photo">
        <form action="<?php echo PUBLIC_ROOT.RUN_DIR.'myuploads.php'?>" method="post" id="frm-update">
          <input type="hidden" name="<?php echo URI_QUERY_ALBUM; ?>" value="<?php echo $codalbum; ?>" />
          <input type="hidden" name="<?php echo URI_QUERY_PHOTO; ?>" id="mbupdate-photo-fname" value="" />
          <input type="hidden" name="<?php echo URI_QUERY_ACTION; ?>" value="update" />
          <input type="text" name="label" maxlength="128" id="mbupdate-photo-lbl" />
          <textarea maxlength="500" name="description" id="mbupdate-photo-dsc"></textarea>
          <img src="" id="mbupdate-photo-thumb" />

          <div class="btn_wraper">
            <a href="#" class="button gray" onclick="HideModalBoxes('mb-update-photo');">Annuler</a>
            <a href="#" class="button green" id="mbox-btn-update" onclick="javascript:$('#frm-update').submit();">Enregistrer</a>
            <!-- <input type="submit" value="Enregistrer" class="button green" /> -->
          </div>
        </form>
      </div>
      <!-- /Modal box - Update label n description -->
    </div>
<!-- /Modal boxes -->
    <div class="header">
      <h1 style="font-weight: 100; padding: .35em;">Mes t&eacute;l&eacute;chargements</h1>
    </div>

    <!-- PHOTO WRAPPER -->
    <div class="photo-wrapper">
<?php

  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre

  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php';
  }
  
// Determiner si le membre a deja poste des photos
  if(!empty($USER_SESSION) && is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION)){
    // Recuperer liste de photos telecharges
    $lof_photos = explode( "\n", file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION));
    
    // Montrer a l'utilisateur ses propres photos
    $i=0;
    foreach($lof_photos as $photo_filename){
      if($photo_filename!='' && file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo_filename)){
        $i++;
        // Get photo info
        $PHOTOINFO = read_csv(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.csv');
        
        // Load photo label
        if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt')){
          $PHOTOINFO[TITLE] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt', false, null, -1, 128); // Limited to 128 chars
        }
        
        // Load photo description
        if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt')){
          $PHOTOINFO[DESCRIPTION] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt', false, null, -1, 500); // Limited to 128 chars
        }
        
        echo '
      <div class="photo-card" style="background-image:url('.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo_filename.')">
        <input type="hidden" id="photo-filename-'.$i.'" value="'.$photo_filename.'" />
        <input type="hidden" id="photo-lbl-'.$i.'" value="'.get_arr_value($PHOTOINFO, TITLE).'" />
        <input type="hidden" id="photo-dsc-'.$i.'" value="'.get_arr_value($PHOTOINFO, DESCRIPTION).'" />'."\n";
        
        if($_SHOW_RATES){
          // Get points for photo
          $V = (file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.txt'))? filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.txt') : 0;
          $P = (file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.pts.txt'))? filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.pts.txt') : 0;
          $M = ($V>0)? round($P / $V, 1) : $P ;
/*
          if(file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.pts.txt')){
            $P = filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.pts.txt');
          }else{
            $P = 0;
          }
*/        
          // Show points
          echo '<div class="photo-rate" title="Moyenne"><span>'.$M.'</span></div>';
        }
        
        // Montrer bouton pour supprimer la photo
        if($_ALLOW_DELETE){
          echo '
        <div class="photo-tools">
          <a href="#" onclick="javascript:dlgDelete(\''.$photo_filename.'\')" title="Supprimer" class="delete">Supprimer</a>
        </div>';
        }
        
        echo '
        <div class="photo-form">
          <a href="#" onclick="javascript:dlgUpdate(\''.$i.'\');" class="photo-label">'.get_arr_value($PHOTOINFO, LIBELLE).'</a>
        </div>
      </div>'."\n";
      }
    }
    echo '<div class="button-wrapper at-center">';
    
    // Montrer bouton pour telecharger plus de photos s'il n'a pas atteint la limite
    if($_CAN_UPLOAD && $i < $CONFIG['uploadslimit']){
      echo '
    <a class="green" href="'.PUBLIC_ROOT.FORMS_DIR.'upload.php?'.URI_QUERY_ALBUM.'='.$codalbum.'">Rajouter des photos</a>';
    }
    
    echo '
    <a class="blue" href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Continuer</a>
    </div>';
  }else{
    echo '<p>Vous n\'avez toujours rien t&eacute;l&eacute;charg&eacute;</p>';
    echo '<div class="button-wrapper at-center"><a class="green" href="'.PUBLIC_ROOT.FORMS_DIR.'upload.php?'.URI_QUERY_ALBUM.'='.$codalbum.'">T&eacute;l&eacute;charger maintenant</a></div>';
  }
?>
    </div> 
    <!-- /PHOTO WRAPPER -->

    <script type="text/javascript">
      //var photo_to_delete; // <---- Peut s'effacer
      var cur_modal_box_id;

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
      
      function dlgDelete(Photo2Del){
        //this.photo_to_delete = Photo2Del; // <---- Peut s'effacer
        $("#mbox-btn-delete").attr("href", "<?php echo PUBLIC_ROOT.RUN_DIR. 'myuploads.php?'.URI_QUERY_ACTION.'=delete&'.URI_QUERY_ALBUM.'='.$codalbum.'&'.URI_QUERY_PHOTO.'='; ?>"+Photo2Del);
        ShowModalBox('mb-delete-photo');
      }
      
      function dlgUpdate(photoID){
        //this.photo_to_delete = Photo2Del; // <---- Peut s'effacer
        var photo_filename = $('#photo-filename-'+photoID).val();
        
        $("#mbupdate-photo-thumb").attr("src", "<?php echo PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/medium/';?>"+photo_filename);
        
        $("#mbupdate-photo-fname").val( photo_filename );
        $("#mbupdate-photo-lbl").val( $('#photo-lbl-'+photoID).val() );
        $("#mbupdate-photo-dsc").html( $('#photo-dsc-'+photoID).val().replace(/<br\s?\/?>/g,"\n") ); // For other all
        
        ShowModalBox("mb-update-photo");
        
        // Repositionning modalbox
        // var y = round( ($(window).height() - $("#mb-update-photo").height()) / 2);  // <- don't work
        // $("#mb-update-photo").css("margin-top", y+"px");   // <- don't work
        $("#mb-update-photo").css("margin-top", "5%");
      }
    </script>
  </body>
</html>
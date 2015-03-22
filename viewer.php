<?php
/** INFOS IMPORTANTES
  *  - Cet fichier est appelle par un autre dont son emplacement
  *    est SYSTEM_ROOT.ALBUMS_DIR.code_album."/index.php"
  *    celui-ci declare la variable $_CODALBUM
  **/

  if(!defined('SYSTEM_ROOT')) 
    require_once __DIR__.'/settings.php';

  if(!isset($_CODALBUM)){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
    exit;
  }

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  

  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre
  $RKEY     = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);
  $_ISADMIN = is_admin();
  $ERROR    = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/logs/error.log');

  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php';
    
    if(!empty($RKEY) && get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY) == $RKEY){
      setcookie(COOKIE_RIGHTS_KEY, $RKEY, time() + (3600 * 2), PUBLIC_ROOT); // Permettre a cette personne de voter ou telecharger ses photos pendant 2 heures
    }elseif(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY)){
      // Empecher de telecharger des photos a toute personne externe au club photo
      $AL_CONF['allowupload']='0';
    }

    if($AL_CONF['allowupload']=='1'){
      // Calculer droit de vote par raport de la date limite
      $UPLOAD_FROM = (@array_key_exists('upload-from', $AL_CONF))? explode('/', $AL_CONF['upload-from'],3):false;
      $UPLOAD_TO = (@array_key_exists('upload-to', $AL_CONF))? explode('/', $AL_CONF['upload-to'],3):false;
// debug
//      print_r($VOTE_FROM);
// debug />
      if($UPLOAD_FROM==false)
        $AL_CONF['allowupload']='0';
      else
        if(time() <= mktime(0,0,0, $UPLOAD_FROM[1], $UPLOAD_FROM[0], $UPLOAD_FROM[2])) // Si la periode n'a pas commence
          $AL_CONF['allowupload']='0';

      if($UPLOAD_TO==false)
        $AL_CONF['allowupload']='0';
      else
        if(time()-(3600 * 24) >= mktime(0,0,0, $UPLOAD_TO[1], $UPLOAD_TO[0], $UPLOAD_TO[2])) // Si la periode est depasee
          $AL_CONF['allowupload']='0';
    }
  } else{
    $ERROR->insert('ALBUM CONFIG NOT FOUND AT: '.SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php', true);
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';
    $AL_CONF['allowupload']='0';
  }
?>
<!DOCTYPE html>
<html class="no-js">
  <head>
    <title><?php echo get_arr_value($AL_CONF, 'albumname'); ?> - MJC ClubPhoto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/modalboxes.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/viewer.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="all" href="<?php echo PUBLIC_ROOT; ?>css/collagePlus.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="all" href="<?php echo PUBLIC_ROOT; ?>css/collagePlus.transitions.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />

    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.collagePlus.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.removeWhitespace.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.collageCaption.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/fingerprint.js"></script>

    <script type="text/javascript">
      var fgrpt = new Fingerprint({screen_resolution: true}).get();
      $.post(<?php echo '"'.((SYS_HTTPS_AVAILABLE==true)?'https://':'http://').SITE_DOMAIN.PUBLIC_ROOT.RUN_DIR.'fingerprint.php", {'.URI_QUERY_ACTION.':"refresh", '.URI_QUERY_FINGERPRINT.':'; ?>fgrpt});
      
      $(window).load(function () {
          $(document).ready(function(){
              collage();
              $('.Collage').collageCaption();
          });
      });

      // Here we apply the actual CollagePlus plugin
      function collage() {
          $('.Collage').removeWhitespace().collagePlus(
              {
                  'fadeSpeed'     : 2000,
                  'targetHeight'  : 200,
                  'effect'        : 'effect-2',
                  'direction'     : 'vertical',
                  'allowPartialLastRow':false
              }
          );
      };

      // This is just for the case that the browser window is resized
      var resizeTimer = null;
      $(window).bind('resize', function() {
          // hide all the images until we resize them
          $('.Collage .Image_Wrapper').css("opacity", 0);
          // set a timer to re-apply the plugin
          if (resizeTimer) clearTimeout(resizeTimer);
          resizeTimer = setTimeout(collage, 200);
      });
    </script>
  </head>
  <body>
<!-- Header -->
    <div class="header">
        <h1><a class="toolbar-home" href="<?php echo PUBLIC_ROOT; ?>">Home</a><?php echo get_arr_value($AL_CONF, 'albumname'); ?></h1>

        <div class="album-infos">
          <p><?php echo get_arr_value($AL_CONF, 'albumdesc'); ?></p>
        </div>
    </div>
<!-- / Header -->
<?php
  //include SYSTEM_ROOT.FORMS_DIR.'viewer_admin_buttons.php';
if($_ISADMIN){
?>
    
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
<!-- Admin Toolbar -->
    <div id="admin-tools" class="toolbar">
      <ul>
        <li><a href="#" title="Suprimer photos s&eacute;lection&eacute;s" onclick="ShowModalBox('mb-del-selct')"><img src="<?php echo PUBLIC_ROOT.'images/toolbar_delete_16x16.png'; ?>" alt="Suprimer" /></a></li>
        <li><a href="#" title="Envoyer photos s&eacute;lection&eacute;s &aacute; ..." onclick="ShowModalBox('mb-snd-selct')"><img src="<?php echo PUBLIC_ROOT.'images/toolbar_envoyer_16x16.png'; ?>" alt="Envoyer &aacute; ..." /></a></li>
        <li><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM; ?>" title="Editer album"><img src="<?php echo PUBLIC_ROOT.'images/toolbar_settings_16x16.png'; ?>" alt="Editer" /></a></li>
<?php
    if($AL_CONF['allowupload']=='1'){
      echo '        <li><a href="'.PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Mes t&eacute;l&eacute;chargements"><img src="'.PUBLIC_ROOT.'images/toolbar_my_uploads_16x16.png'.'" alt="Mes t&eacute;l&eacute;chargements" /></a></li>';
    }
?>
      </ul>
    </div>
<!-- / Admin Toolbar -->
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
<?php
  } // if($_ISADMIN) />

  if($AL_CONF['allowupload']=='1' && !$_ISADMIN){
    echo '
<!-- User Toolbar -->
    <div id="user-tools" class="toolbar">
      <ul>
        <li><a href="'.PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Mes t&eacute;l&eacute;chargements"><img src="'.PUBLIC_ROOT.'images/toolbar_my_uploads_20x20.png'.'" alt="Mes t&eacute;l&eacute;chargements" /></a></li>
      </ul>
    </div>
<!-- / User Toolbar -->';
  }

  echo '<!-- Content -->'."\n".'    <div class="Collage effect-parent">'."\n";

  if($AL_CONF['allowupload']=='1'){    
    echo '
      <div class="Image_Wrapper" data-caption="<u>Ajouter</u> photos">
          <a href="http://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'upload.php?'.URI_QUERY_ALBUM."=$_CODALBUM".'"><img src="'.PUBLIC_ROOT.'images/tile_ajouter_photos.png" /></a>
      </div>';
  }
/*
      <div class="Image_Wrapper" data-caption="<u>Comparer</u> plusieurs photos">
          <a href="#"><img src="<?php echo PUBLIC_ROOT; ?>images/tile_compare_photos.png" /></a>
      </div>

*/
  // Lire le dossier "thumbs" et composer la gallerie de photos
  if($_ISADMIN){
    $i=0;
    foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/thumbs/','*.jpg',true) as $file)
    {
      $i++;
      echo '<div class="Image_Wrapper"><input type="checkbox" id="c['.$i.']" value="'.$file.'" /><label for="c['.$i.']"></label><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$file.'&'.URI_QUERY_ALBUM.'='.$_CODALBUM.'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/medium/'.$file.'" /></a></div>'."\n";
    }
  }else{
    foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/thumbs/','*.jpg',true) as $file)
    {
      echo '<div class="Image_Wrapper"><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$file.'&'.URI_QUERY_ALBUM.'='.$_CODALBUM.'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/medium/'.$file.'" /></a></div>'."\n";
    }
  }
  
?>
    </div>
<!-- / Content -->
    <p class="footnote">Club photo - MJC Rodez - 
<?php
  if($_ISADMIN){
    echo '<a href="'.PUBLIC_ROOT.'logout.php">Se d&eacute;connecter</a>';
  } else {
    echo '<a href="'.PUBLIC_ROOT.'login.php">Administrer</a>';
  }
?>
      </p>
  </body>
</html>
<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

// Forcer administrateur
    if(!is_admin()){
      if(SYS_HTTPS_AVAILABLE){
        header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }else{
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }
      exit();
    }
/*
// Calculer used quota
    if(DISK_QUOTA > 0){
      if(file_exists(FILE_USED_QUOTA)){
        $quota_used = file_get_contents(FILE_USED_QUOTA,null,null,null,9); // in Ko
        $quota_used_prct = ($quota_used * 100) / DISK_QUOTA;
      }
    }
*/
//    $ntrash = count_files(SYSTEM_ROOT.TRASH_DIR, '*');
?>
<html>
<head>
    <title>CPanel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
  echo '
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/cpanel.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/modalboxes.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/album_cards.css?v='.VERSION_CSS.'" type="text/css" />
    <script src="'.PUBLIC_ROOT.'js/modalboxes.js?v='.VERSION_JS.'"></script>
    <script src="'.PUBLIC_ROOT.'js/jquery.1.10.1.min.js?v='.VERSION_JS.'"></script>';
?>

</head> 
<body>
<!-- modalboxes -->
  <div class="modal_layer_bg" id="modal-layer-bg">
    <!-- Modal box - Delete album -->
    <div class="modal_box" id="mb-del-album">
      <h3 class="mb-title" id="mb-del-album-title"></h3>
      <p id="mb-del-album-desc"></p>
      <div class="btn_wraper">
        <a href="#" class="button gray" onClick="CancelDialog();">Annuler</a>
        <a href="#" class="button red" id="mb-del-album-okbtn">Continuer</a>
      </div>
    </div>
    <!-- Modal box - Delete album -->
  </div>
<!-- /modalboxes -->
  <div class="header">
      <h1><a class="header-button ico-home notext" href="<?php echo PUBLIC_ROOT.ADMIN_DIR; ?>">CPanel</a>Corbeille</h1>
  </div>
  
  <div class="albums">
<?php
  foreach(glob(SYSTEM_ROOT.TRASH_DIR.'*', GLOB_ONLYDIR) as $folder){
    if($folder!='.' && $folder!='..'){
      $fname = basename($folder);
      $nphotos = count_files(SYSTEM_ROOT.TRASH_DIR.$fname.'/photos/thumbs', '*.jpg');
      
      if(@is_readable(SYSTEM_ROOT.TRASH_DIR.$fname.'/config.php')===true)
        $AL_CONF = include SYSTEM_ROOT.TRASH_DIR.$fname.'/config.php';
      else
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre

      echo '<div class="card">';
        echo '<div class="row">';
          echo '<div class="content image">';
            echo '<a href="'.PUBLIC_ROOT.ADMIN_DIR.'tviewer.php?'.URI_QUERY_ALBUM.'='.$fname.'" class="info-photo" title="Regarder album"';
      // Add any photo thumb
          foreach(read_dir(SYSTEM_ROOT.TRASH_DIR.$fname.'/photos/thumbs','*.jpg',true) as $file){
            echo ' style="background-image: url('.PUBLIC_ROOT.TRASH_DIR.$fname.'/photos/medium/'.$file.');"';
            break;
          }
            echo '><span class="info-aname">'.$AL_CONF['albumname'].'</span>';
            echo '<span class="info-nphotos">'.$nphotos.'</span></a>';
          echo '</div>';
        echo '</div>';
      echo '</div>';
    }
  }
?>
  </div>
<?php
  include SYSTEM_ROOT.ADMIN_DIR.'footer.php';
?>
</body>
</html>
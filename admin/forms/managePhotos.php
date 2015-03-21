<?php
  if(!defined('SYSTEM_ROOT'))
    require __DIR__.'/../../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit();
  }

  $codalbum = getRequest_param(URI_QUERY_ALBUM, false);

  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';

  // Lire le dossier "thumbs" et composer la gallerie de photos
  foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/','*.jpg',true) as $file){
    echo '<div class="Image_Wrapper"><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$file.'&'.URI_QUERY_ALBUM.'='.$codalbum.'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/medium/'.$file.'" /></a></div>'."\n";
  }
?>
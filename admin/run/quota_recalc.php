<?php
  if(!defined('SYSTEM_ROOT'))
    require_once __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit;
  }

  include SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include SYSTEM_ROOT.LIB_DIR.'log.class.php';


  foreach(glob(SYSTEM_ROOT.ALBUMS_DIR.'*', GLOB_ONLYDIR) as $folder){
    if($folder!='.' && $folder!='..'){
      $fname = basename($folder);
      $nphotos = count_files(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs', '*.jpg');
      
      if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php')===true)
        $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php';
      else
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre

      if ($hdir = opendir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs')) {
          $entry = readdir($hdir);
          closedir($hdir);
      }
      
      echo '<div class="card">';
        echo '<div class="row">';
          echo '<div class="content image">';
            echo '<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$fname.'" class="info-photo" title="Regarder album"';
      // Add any photo thumb
          foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg',true) as $file){
            echo ' style="background-image: url('.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/medium/'.$file.');"';
            break;
          }
/*
      if ($hdir = opendir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs')) {
        while (false !== ($entry = readdir($hdir))) {
          
          if($entry != '.' && $entry != '..')
            break;
        }

          echo '<span><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs/'.basename($entry).'" /></span>';
          closedir($hdir);
      }
*/
            echo '><span class="info-aname">'.$AL_CONF['albumname'].'</span>';
            echo '<span class="info-nphotos">'.$nphotos.'</span></a>';
          echo '</div>';
        echo '</div>';
      echo '</div>';
    }
  }
?>
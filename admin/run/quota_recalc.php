<?php
  if(!defined('SYSTEM_ROOT'))
    require_once __DIR__.'/../../settings.php';

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

  $quota = $album_size = 0;

  foreach(glob(SYSTEM_ROOT.ALBUMS_DIR.'*', GLOB_ONLYDIR) as $album){
    if($album!='.' && $album!='..'){
      $album_size = 0;
      $aname = basename($album);

      // Calculate total size of thumbs
      foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$aname.'/photos/thumbs','*.jpg', false) as $file){
        $album_size += filesize($file);
      }

      // Calculate total size of medium
      foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$aname.'/photos/medium','*.jpg', false) as $file){
        $album_size += filesize($file);
      }

      // Calculate total size of large
      foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$aname.'/photos/large','*.jpg', false) as $file){
        $album_size += filesize($file);
      }

      // Calculate total size of trash
      if(file_exists(SYSTEM_ROOT.ALBUMS_DIR.$aname.'trash')){
        foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$aname.'/trash','*.jpg', false) as $file){
          $album_size += filesize($file);
        }
      }
      
      $album_size = round($album_size / 1024); // Convert in kB
      
      // Update album quota file
      file_put_contents(SYSTEM_ROOT.ALBUMS_DIR.$aname.'/album_size.txt', $album_size, LOCK_EX);
     
      // Increment quota size var
      $quota += $album_size;
    }
  }

  // Update site quota file
  file_put_contents(FILE_USED_QUOTA, $quota, LOCK_EX);

  // User redirect
  header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR);
  
?>
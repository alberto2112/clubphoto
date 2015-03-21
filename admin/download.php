<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

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

  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, ''), 'a-zA-Z0-9', 8, false);
  $photo = clear_request_param(getRequest_param(URI_QUERY_PHOTO, ''), 'a-zA-Z0-9\.', 36, false);

  if($codalbum!='' && $photo!=''){
    if(substr($photo,-3)=='jpg'){

      $votes_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo.'.txt';
      $points_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo.'.pts.txt';
      $photo_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo;
      
      if(is_readable($points_filename))
        $photo = filesize($points_filename).'e_'.$photo;
      
      if(is_readable($votes_filename))
        $photo = filesize($votes_filename).'v_'.$photo;
      
      
      header('Content-type: application/octet-stream');
      header('Content-Length: '. filesize($photo_filename));
      header('Content-disposition: attachment; filename='. $photo);
      header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Expires: 0');
      readfile($photo_filename);
      exit;
    }
  }
?>
<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, ''), 'a-zA-Z0-9', 8, false);
  $photo    = clear_request_param(getRequest_param(URI_QUERY_PHOTO, ''), 'a-zA-Z0-9\.', 36, false);

  if($codalbum!='' && $photo!=''){
    if(substr($photo,-3)=='jpg'){
      $photo_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo;      
      
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
<?php
  if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'photo.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';

// Get IP address
  $IP = getClient_ip();

// Get and clean request vars
  $codalbum   = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $action     = clear_request_param(getRequest_param(URI_QUERY_ACTION, false), 'a-zA-Z0-9', 8, false);
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO,''), 'a-zA-Z0-9\.', 42, false);

  if($codalbum==false){
  // Open error log
    $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/events.log');
    $ERRLOG->insert('EMPTY ALBUM CODE - '.$IP.' - ['.RUN_DIR.'myuploads.php]', true);
    echo '<h1>Album introuvable!</h1>';
    exit;
  }else{
    // Open logs
    $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/errors.log');
    $LOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/events.log');
  }

  // Lire fichier de configuation de l'album
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';

    // Renvoyer s'il n'a pas RKEY ou si RKEY ne correspond avec celui de l'album
    if(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($AL_CONF, 'RKEY')){
      $ERRLOG->insert('EMPTY RKEY - '.$IP, true);
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
      exit;
    }

  // Recuperer USER_KEY (Cookie)
    $USER_KEY = get_arr_value($_COOKIE, COOKIE_USER_KEY.$codalbum, false);

    switch($action){
      case 'delete':
        // Leer fichero $photo_filename.csv
        $photo_info = read_csv(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.csv');

        if($USER_KEY==$photo_info[PHOTOGRAPHE_UKEY]){
          // Calculer used quota
          $file_album_size = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/album_size.txt';

          $current_used_quota = (file_exists(FILE_USED_QUOTA))? 
                                  file_get_contents(FILE_USED_QUOTA,null,null,null,9) // in Ko
                                  : 0;

          $album_current_size = (file_exists($file_album_size))? 
                                  file_get_contents($file_album_size,null,null,null,9) // in Ko
                                  : 0;

          // Delete photo
          $used_disk = delete_photo(SYSTEM_ROOT.ALBUMS_DIR.$codalbum, $photo_filename, false);

          //Insert log line journal and close/unlock journal log file
          $LOG->insert('photo='.$photo_filename.' [-] DELETED by user - ip='.$IP.' - ['.RUN_DIR.'myuploads.php]', true);

          //Update quota file
          if($current_used_quota > 0)
            file_put_contents(FILE_USED_QUOTA, ($current_used_quota - $used_disk), LOCK_EX);

          //Update album size file
          if($album_current_size > 0)
            file_put_contents($file_album_size, ($album_current_size - $used_disk), LOCK_EX);

          header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$codalbum);

        }
        break;
    }
  } else{
    $ERRLOG->insert('[!] CONFIG NOT FOUND - album='.$codalbum.' - ip='.$IP.'['.RUN_DIR.'myuploads.php]', true);
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
    exit;
  }
?>
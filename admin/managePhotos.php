<?php
  if(!defined('SYSTEM_ROOT'))
    require __DIR__.'/../settings.php';

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

  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'photo.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';

  // Vars declarations
  $codalbum       = clear_request_param(getRequest_param(URI_QUERY_ALBUM, ''), 'a-zA-Z0-9', 8, false);
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO,''), 'a-zA-Z0-9\.\;', -1, false);
  $action         = clear_request_param(getRequest_param(URI_QUERY_ACTION, 'null'), 'a-z', 8, false);
  
  //Other vars and objects declarations
  $IP             = getClient_ip();

  if($codalbum==false){
    // Open error log
      $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/events.log');
      $ERRLOG->insert('EMPTY ALBUM CODE - '.$IP.' - ['.ADMIN_DIR.'managePhotos.php]', true);
      echo '<h1>Album introuvable!</h1>';
      exit;
  }else{
    $LOG            = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/events.log');
  }

  switch($action){
    case 'delfromtrash':
    case 'delete':
      if(
          !empty($photo_filename) 
          //&& is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo_filename)
        )
      {
        
        $file_album_size = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/album_size.txt';

        $current_used_quota = (file_exists(FILE_USED_QUOTA))? 
                                file_get_contents(FILE_USED_QUOTA,null,null,null,9) // in Ko
                                : 0;

        $album_current_size = (file_exists($file_album_size))? 
                                file_get_contents($file_album_size,null,null,null,9) // in Ko
                                : 0;
        $used_disk = 0;
        
        $dft = ($action=='delfromtrash'); // Dans une boucle, il est plus rapide de comparer un booleen qu'un string
        
        foreach(explode(';',$photo_filename,-1) as $pfn){
          if($dft==true){
            $used_disk += delete_photo(SYSTEM_ROOT.ALBUMS_DIR.$codalbum, $pfn, TRASH_DIR);
            //Insert log line journal and close/unlock journal log file
            $LOG->insert('photo='.$pfn.' [-] DELETED FROM TRASH by admin - ip='.$IP.' - ['.ADMIN_DIR.'managePhotos.php]');
          }else{
            $used_disk += delete_photo(SYSTEM_ROOT.ALBUMS_DIR.$codalbum, $pfn, false);
            //Insert log line journal and close/unlock journal log file
            $LOG->insert('photo='.$pfn.' [-] DELETED by admin - ip='.$IP.' - ['.ADMIN_DIR.'managePhotos.php]');
          }
// ---->
/*
          //Delete thumbnail
          if($dft==true){
            $used_disk += @filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR.$pfn);
            @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR.$pfn);
          }else{
            $used_disk += @filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$pfn);
            @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$pfn);
          }

          //Delete medium photo
          $used_disk += @filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/medium/'.$pfn);
          @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/medium/'.$pfn);

          //Delete large photo
          $used_disk += @filesize(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$pfn);
          @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$pfn);

          //Delete photo stats
          foreach (glob(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/votes/'.$pfn.'*') as $file2del) {
            @unlink($file2del);
          }
          # @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/votes/'.$pfn.'.txt');
          # @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/votes/'.$pfn.'.pts.txt');

          //Delete photo infos
          @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$pfn.'.csv');
*/
// ----<
        }
        
        //Update quota file
        if($current_used_quota > 0)
          file_put_contents(FILE_USED_QUOTA, ($current_used_quota - $used_disk), LOCK_EX);

        //Update album size file
        if($album_current_size > 0)
          file_put_contents($file_album_size, ($album_current_size - $used_disk), LOCK_EX);
        
      }
      break;
    
    case 'trash':
      if(!empty($photo_filename))
      {
        // Creer dossier 'poubelle'
        if(!file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR))
          mkdir(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR);
        
        if(strpos($photo_filename,';')){
          foreach(explode(';',$photo_filename,-1) as $pfn){
            //echo "Photo:$pfn<br />\n";
            if(!empty($pfn)){
              rename(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$pfn, SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR.$pfn);

              //Insert log line journal and close/unlock journal log file
              $LOG->insert('photo='.$pfn.' [-] MOVED TO TRASH by admin - ip='.$IP.' - ['.ADMIN_DIR.'managePhotos.php]');
            }
          }
        }else{
          rename(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo_filename, SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.TRASH_DIR.$photo_filename);
          //Insert log line journal and close/unlock journal log file
          $LOG->insert('photo='.$pfn.' [-] MOVED TO TRASH by admin - ip='.$IP.' - ['.ADMIN_DIR.'managePhotos.php]');
        }
      }
      //TODO: If not ajax
      header("Location: ".PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
      break;
    
    case 'restore':
      break;
    
    case 'update':
      break;
    
    case 'replace':
      break;
    
    case 'move':
      break;
    
    case 'tonewalbum':
      break;
  }
?>
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

  include_once SYSTEM_ROOT.ETC_DIR.'flickr.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'phpFlickr.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';

  $action = clear_request_param(getRequest_param(URI_QUERY_ACTION, ''), 'a-z', 8, false);
  $photos = clear_request_param(getRequest_param(URI_QUERY_PHOTO, ''), 'a-zA-Z0-9\.\,', 1024, false);
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);

  if(!empty($photos) && !empty($codalbum)){
    // Define other vars
    $ALBUM_ROOT = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';
    $photoids   = array();
    
    $flickr     = new phpFlickr(FLICKR_KEY, FLICKR_SECRET, true);
    $flickr->setToken(FLICKR_TOKEN);
    
    // List photos
    foreach(explode(',', $photos) as $photo){
      // Lire fichier CSV de la photo
      $photo_info = read_csv($ALBUM_ROOT.'photos/'.$photo.'.csv');
      
      // Init vars
      $photo_info[DESCRIPTION] = $photo_info[TITLE] = $tags='';
      
      // Recuperer statistiques
      //$votes_fname = $ALBUM_ROOT.'votes/'.$photo.'txt';
      //$points_fname = $ALBUM_ROOT.'votes/'.$photo.'.pts.txt';
      $votes  = filesize($ALBUM_ROOT.'votes/'.$photo.'.txt');
      $points = filesize($ALBUM_ROOT.'votes/'.$photo.'.pts.txt');
      $avg    = round($points / $votes, 1);

      // Load photo label
      if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo.'.lbl.txt')){
        $photo_info[TITLE] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo.'.lbl.txt', false, null, -1, 128); // Limited to 128 chars
      }else{
        $photo_info[TITLE] = $photo_filename;
      }

      // Load photo description
      if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo.'.dsc.txt')){
        $photo_info[DESCRIPTION] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo.'.dsc.txt', false, null, -1, 512)."\n\n"; // Limited to 512 chars
      }
      $photo_info[DESCRIPTION] .= '&Eacute;toiles: '.$points.' Moyenne:'.$avg;
      
      // Telecharger photos sur flickr
      $id = $flickr->sync_upload(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo, $photo_info[TITLE], $photo_info[DESCRIPTION], $tags);
      $photoids[] = $id;
      
    }
    
    //TODO: if count($photoids)>0 show button
    echo '<a href="http://www.flickr.com/photos/upload/edit/?ids='.implode(',', $photoids).'">Go to Flickr</a>';
  }

//TEST
/*
  $tags='';
  $title='Test';
  $description='PHP Photo-Upload test';
  $photoids = array();

  $flickr = new phpFlickr(FLICKR_KEY, FLICKR_SECRET, true);
/ *
//Redirect to flickr for authorization
     if(!$_GET['frob']){
         $flickr->auth('write');
     }else {
         //If authorized, print the token
         $tokenArgs = $flickr->auth_getToken($_GET['frob']);
         echo "<pre>"; var_dump($tokenArgs); echo "</pre>";
     }

* /
    $flickr->setToken(FLICKR_TOKEN);
// Upload
    $id = $flickr->sync_upload('/tmp/erase.jpg', $title, $description, $tags, 'is_public=0');
    echo $id;
    $photoids[] = $id;

    $id = $flickr->sync_upload('/tmp/erase2.jpg', $title, $description, $tags, 'is_public=0');
    echo $id;
    $photoids[] = $id;

  echo '<a href="http://www.flickr.com/photos/upload/edit/?ids='.implode(',', $photoids).'">Go to Flickr</a>';
*/
//TEST />

?>
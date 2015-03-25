<?php
  if(!defined('SYSTEM_ROOT')) 
    require_once __DIR__.'/settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';

// Get IP address
  $IP = getClient_ip();
  //$LONGIP = @sprintf("%u",ip2long($IP)) | '0';

// Get and clean request vars
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, ''), 'a-z', 8, false); // redocache | nocache

  if($codalbum==false){
    // Open error log
    $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/events.log');
    $ERRLOG->insert('EMPTY ALBUM CODE - ip='.$IP.' - (/classement.php)', true);
    echo '<h1>Album introuvable!</h1>';
    exit;
  }else{
    // Open logs
    $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/errors.log');
  }

// Get other vars
  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre
  $RKEY     = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);
  $ALBUM_ROOT = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';
  $RANKING_FILENAME = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.'/ranking_cache.html';

// Load album config
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  }
//------------------------------
if(OPT_DEVELOPPING){
  print "DEBUG!";
  goto DEBUG;
}
//------------------------------
// Get rights key
  if(!empty($RKEY) && get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY) == $RKEY){
    setcookie(COOKIE_RIGHTS_KEY, $RKEY, time() + (3600 * 2), PUBLIC_ROOT); // Permettre a cette personne de regarder le classement pendant 2 heures
  }elseif(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY)){
    // Empecher de regarder le classement a toute personne externe au club photo
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
    exit;
  }

DEBUG:
  echo '<h2>Classement approximatif pour l\'album: '.get_arr_value($AL_CONF, 'albumname').'</h2>';
  if($action != 'nocache' && $action != 'redocache' && is_readable($RANKING_FILENAME)){
    include $RANKING_FILENAME;
  }else{
  /**
   * MAKE RANKING CACHE FILE
   */ 
     
    $i=0;
    $LoP = array();
    $aPoints = array();
    $aVotes = array();
    // Make array to sort
    foreach(glob($ALBUM_ROOT.'votes/*') as $file){
      if($file!='.' && $file!='..'){
        if(substr($file,-7)=='jpg.txt'){
          $votes_fname = $file;
          $thumb_fname = substr($file, strrpos($file,DIRECTORY_SEPARATOR)+1,-4);
          $points_fname = $ALBUM_ROOT.'votes/'.$thumb_fname.'.pts.txt';
          //$thumb_fname = PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$thumb_fname;
          $votes = filesize($votes_fname);
          $points = filesize($points_fname);

          // Leer fichero $photo_filename.csv
          //$photo_info = read_csv($ALBUM_ROOT.'photos/'.$thumb_fname.'.csv');
          $LoP[] = array($thumb_fname, $votes, $points);
          $aPoints[] = $points;
          $aVotes[] = $votes;

        }
      }
    }
    
    // Array sort
    array_multisort($aPoints, SORT_DESC, $aVotes, SORT_ASC, $LoP);
    
    if($action != 'nocache'){
      // Create and open cache file
      $ranking_cache = new LOG($RANKING_FILENAME, false);
      // Print result to cache file
      foreach($LoP as $photo){
        $i++;
        $ranking_cache->insert('<p>['.$i.']<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" /></a> votes='.$photo[1].', points='.$photo[2].', moyenne='.round($photo[2] / $photo[1], 1).'</p>', false);
      }

      // Close cache file
      $ranking_cache->close();

      // Import cache file
      include $RANKING_FILENAME;
    }else{
    /**
     * PRINT RANKING (NO CACHE FILE)
     */ 
      // Print result
      foreach($LoP as $photo){
        $i++;
        echo '<p>['.$i.']<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" /></a> votes='.$photo[1].', points='.$photo[2].', moyenne='.round($photo[2] / $photo[1], 1).'</p>', false;
      }
    }
  }
?>
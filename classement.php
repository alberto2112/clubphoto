<?php
  if(!defined('SYSTEM_ROOT')) 
    require_once __DIR__.'/settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

// Get IP address
  $IP = getClient_ip();
  //$LONGIP = @sprintf("%u",ip2long($IP)) | '0';

// Get and clean request vars
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, ''), 'a-z', 9, false); // redocache | nocache

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
  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre
  $RKEY     = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);
  $ALBUM_ROOT = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';
  $RANKING_FILENAME = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.'/ranking_cache.html';

// Load album config
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  }

/*
//------------------------------
if(OPT_DEVELOPPING){
  print "DEBUG!";
  goto DEBUG;
}
//------------------------------

// Get rights key
  if(!empty($RKEY) && get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY) == $RKEY){
    setcookie(COOKIE_RIGHTS_KEY, $RKEY, time() + SESSION_LIFE_RKEY, PUBLIC_ROOT); // Renouveller la cle pendant 2 heures
  }elseif(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY)){
    // Empecher de regarder le classement a toute personne externe au club photo
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
    exit;
  }
*/
DEBUG:
  echo '
<html>
<head>
    <title>Club photo - MJC Rodez</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/reset.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/ranking.css?v='.VERSION_CSS.'" type="text/css" />
</head> 
<body>

<!-- Header -->
    <div class="header">
        <h1>Classement approximatif pour l\'album: '.get_arr_value($AL_CONF, 'albumname').'</h1>
    </div>
<!-- / Header -->
<div class="ranking">';

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

    if($action != 'nocache' || $action == 'redocache'){
      // Create and open cache file
      $ranking_cache = new LOG($RANKING_FILENAME, false, true);
      // Print result to cache file
      foreach($LoP as $photo){
        $i++;
        $ranking_cache->insert('<div class="photo"><span class="pos">'.$i.'</span><span class="votes">'.$photo[1].'</span><span class="points">'.$photo[2].'</span><span class="avg" title="Moyenne">'.round($photo[2] / $photo[1], 1).'</span><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" /></a></div>', false);
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
        echo '<div class="photo"><span class="pos">'.$i.'</span><span class="votes">'.$photo[1].'</span><span class="points">'.$photo[2].'</span><span class="avg" title="Moyenne">'.round($photo[2] / $photo[1], 1).'</span><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" /></a></div>'."\n", false;
      }
    }
  }
  echo '</div>
  </body>
</html>';
?>
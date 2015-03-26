<?php
  if(!defined('SYSTEM_ROOT')) 
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  //include SYSTEM_ROOT.LIB_DIR.'csv.lib.php';
  
// -------------------------------------------------------
  //Function to check if the request is an AJAX request
  function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  }
// -------------------------------------------------------
  function vote_up($filename, $points_up=1){
    if(!is_numeric($points_up))
        $points_up = 1;

    
    if(file_exists($filename)){
      // Ouvrir le fichier
      $handle = smart_file_open($filename, 'a+b', 3000);
      // Comptabiliser et ajouter votes
      $points = filesize($filename);
    }else{
      // Creer un nouveau le fichier
      $handle = smart_file_open($filename, 'wb', 3000);
      $points = 0;
    }
    flock($handle, LOCK_EX); // Verrouiller le fichier

    $strout='';
    for($i=0; $i < $points_up; $i++){
      $strout .= 0x1;
    }
    
    if(strlen($strout)>0)
      $success = fwrite($handle, $strout);
    else
      $success = true;

    flock($handle, LOCK_UN); // Enlever le verrou
    fclose($handle);
    
    if(is_numeric($points))
      $points += $points_up;
    else
      $points = -1;

    // Return resultat
    if($success===false){ //Il y a eu une erreur
      return 'erreur';
    }else{ //Le vote a ete bien comptabilise
      return $points;
    }

  }

// =========================================================

  // Clear request vars
  $codalbum   = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $points     = clear_request_param(getRequest_param(URI_QUERY_POINTS, 1), '0-9', 1, false);
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO, false), 'a-zA-Z0-9\.', 42, false);
  $comments = clear_request_param(getRequest_param(URI_QUERY_COMMENTS, false), false, 500, true);
  $RKEY     = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);

// Recuperer USER_KEY (Cookie)
  $USER_KEY = get_arr_value($_COOKIE, COOKIE_USER_KEY.$codalbum, false);

  $votes_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.txt';
  $points_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.pts.txt';
  $comments_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.cmts.csv';
  $str_cookie = $codalbum.'_'.str_replace('.','_',$photo_filename);
  $IP = getClient_ip();
  $vote_result='';


if(empty($codalbum)){
// Open ERROR LOG
  $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'logs/errors.log');
  $ERRLOG->insert('UNKNOWN ALBUM CODE - '.$IP.' - [/'.RUN_DIR.'vote.php] - QUERY STRING: '.get_arr_value($_SERVER, 'QUERY_SRTING', 'UNKNOWN'), true);
  echo '[!] ERROR: UNKNOWN ALBUM CODE';
  exit;
}elseif(empty($photo_filename)){
// Open ERROR LOG
  $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/error.log');
  $ERRLOG->insert('UNKNOWN PHOTO FILENAME - '.$IP.' - QUERY STRING: '.get_arr_value($_SERVER, 'QUERY_STRING', 'UNKNOWN'), true);
  echo '[!] ERROR: UNKNOWN PHOTO FILENAME';
  exit;
}else{
// Open ERROR LOG
  $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/error.log');
}

//Open vote log
  $LOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/votes.log');

// Lire fichier de configuation de l'album
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true)
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  else
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';

  if(!empty($RKEY) && get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY) == $RKEY){
    setcookie(COOKIE_RIGHTS_KEY, $RKEY, time() + (3600 * 2), PUBLIC_ROOT); // Permettre a cette personne de voter ou telecharger ses photos pendant 2 heures
  }elseif(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY)){
    // Empecher les votes a toute personne externe au club photo
    $AL_CONF['allowvotes']='0';
    $vote_result   = 'VOTE REJETE. RKEY NON TROUVE';
    $points_result = 'Vous n\'avez pas assez de privil&egrave;ges pour voter. Contactez l\'administrateur';
  }

  if($AL_CONF['allowvotes']=='1'){
      // Calculer droit de vote par raport de la date limite
      $VOTE_FROM = (@array_key_exists('vote-from', $AL_CONF))? explode('/', $AL_CONF['vote-from'],3):false;
      $VOTE_TO   = (@array_key_exists('vote-to', $AL_CONF))? explode('/', $AL_CONF['vote-to'],3):false;

      if(!empty($VOTE_FROM) && time() <= mktime(0,0,0, $VOTE_FROM[1], $VOTE_FROM[0], $VOTE_FROM[2])) // Si la periode n'a pas commence
        {
          $AL_CONF['allowvotes']='0';
        
          $vote_result   = 'VOTE PREMATURE';
          $points_result = 'La periode de votes n\'a toujours pas d&eacute;but&eacute;';
        }
        
      if(!empty($VOTE_TO) && time()-(3600 * 24) >= mktime(0,0,0, $VOTE_TO[1], $VOTE_TO[0], $VOTE_TO[2])) // Si la periode est depasee
        {
          $AL_CONF['allowvotes']='0';
        
          $vote_result   = 'VOTE TROP TARD';
          $points_result = 'La periode de votes a termin&eacute;';
        }
  }

  if($AL_CONF['antitriche']=='1' && array_key_exists($str_cookie, $_COOKIE)){
// Mecanisme antitriche
    $sended_points = get_arr_value($_COOKIE, $str_cookie); // Récuperer points
    if($sended_points=='-1'){
      // TODO: Permetre aux photographes de voter par ses propres photos ??
      $vote_result = 'PROPIETAIRE PHOTO';
      $points_result = 'Dis donc! vous ne pouvez pas voter par vos propres photos. Ne trichez pas, eh oh!';
    }else{
      $vote_result = 'DEJA VOTE AUPARAVANT';
      $points_result = 'Dis donc! vous avez d&eacute;j&egrave; donn&eacute; '.$sended_points.' points! Ne trichez pas, eh oh!';
    }
  }elseif($AL_CONF['allowvotes']=='1'){
    // Comptabiliser le vote
    $vote_result   = vote_up($votes_filename, 1);
    $points_result = vote_up($points_filename, $points);
    
    // Comptabiliser qualite de vote
    if($AL_CONF['ratemethod']=='stars')
      vote_up(substr($points_filename, 0, -3).$points.'.txt', 1);
    
    // Enregistrement du commentaire
    if(!empty($comments)){
    // Remplacer characteres de nouvelle ligne
      //$comments = preg_replace('/\s+/', ' ', $comments); // Remplacer espaces and \n par un seul et unique espace
      $comments = str_replace("\r", '', $comments);
      $comments = rtrim(str_replace("\n", '[\n]', $comments));
      
      // Remplacer autres caracteres que la merde de JQuery a l'obligation de mettre
      $comments = str_replace('&Atilde;&iexcl;', '&aacute;', $comments);
      $comments = str_replace('&Atilde;&nbsp;', '&agrave;', $comments);
      $comments = str_replace('&Atilde;&copy;', '&eacute;', $comments);
      $comments = str_replace('&Atilde;&scaron;', '&egrave;', $comments);
      $comments = str_replace('&Atilde;&sup1;', '&ugrave;', $comments);
      $comments = str_replace('&Atilde;&ordf;', '&ecirc;', $comments);
      $comments = str_replace('&Atilde;&reg;', '&icirc;', $comments);
      $comments = str_replace('&Atilde;&macr;', '&uml;', $comments);
      $comments = str_replace('&Atilde;&sect;', '&ccedil;', $comments);
      
    // Enregistrer traçabilite du vote
      if($USER_KEY && is_numeric($points_result)){
        $V= new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_KEY.'.votes', false);
        $V->insert(date('d.m.Y H:i:s').';'.$points.';'.$photo_filename);
        $V->close();
      }
      
    // Enregistrer commentaire    
      $C = new LOG($comments_filename, false);
      $C->insert(date('d.m.Y H:i:s').';;;'.$comments); // Pour l'instant on n'enregistre pas l'adresse IP
      $C->close();
    }
  }

  if(is_numeric($vote_result)){
    // Log result
    $LOG->insert($IP.' - '.$photo_filename.' - '.$points .' points - SUCCESS', true); 
    
    // Set cookie
    if($AL_CONF['antitriche']=='1')
      setcookie($str_cookie,$points,time()+(3600 * 24 * 14), PUBLIC_ROOT);

    // Send result
    if($AL_CONF['ratemethod']=='stars')
      echo $points.'/5';
    else
      echo '1';
  }else{
    $LOG->insert($IP.' - '.$photo_filename.' - '.$points.' points - '.$vote_result, true);
    echo $points_result;
    /*
    if(is_readable($votes_filename) && is_readable($points_filename))
      echo 'Points:'.filesize($points_filename).'; Votes:'.filesize($votes_filename).';';
    else
      echo '-1';
    */
  }
/*
  if (is_ajax()) {
    echo '1';
  }
*/
?>
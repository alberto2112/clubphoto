<?php
  if(!defined('SYSTEM_ROOT')) 
    include __DIR__.'/../../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'photo.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'rate.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';
  
// -------------------------------------------------------
/*
  //Function to check if the request is an AJAX request
  function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  }
*/
// -------------------------------------------------------

  $title_filename = SYSTEM_ROOT.ETC_DIR.'medisection.html';

  // Clear request vars
  $codalbum       = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO, false), 'a-zA-Z0-9\.', 42, false);
  $title          = clear_request_param(getRequest_param(URI_QUERY_COMMENTS, false), false, 256, true);
  $IP             = getClient_ip();

  // Open ERROR LOG
  $ERRLOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'logs/errors.log');

  if(empty($codalbum)){
    $ERRLOG->insert('UNKNOWN ALBUM CODE - '.$IP.' - [/'.ADMIN_DIR.RUN_DIR.'send_to_home.php] - QUERY STRING: '.get_arr_value($_SERVER, 'QUERY_SRTING', 'UNKNOWN'), true);
    echo '[!] ERROR: UNKNOWN ALBUM CODE';
    exit;
  }elseif(empty($photo_filename)){
    $ERRLOG->insert('UNKNOWN PHOTO FILENAME - '.$IP.' - QUERY STRING: '.get_arr_value($_SERVER, 'QUERY_STRING', 'UNKNOWN'), true);
    echo '[!] ERROR: UNKNOWN PHOTO FILENAME';
    exit;
  }

// Copier photo à: /images/ms_background.jpg
  copy(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo_filename, SYSTEM_ROOT.'images/ms_background.jpg');

// Enregistrement du commentaire
  if(!empty($title)){
  // Effacer fichier du title
    unlink($title_filename);
  // Remplacer characteres de nouvelle ligne
    //$title = preg_replace('/\s+/', ' ', $title); // Remplacer espaces and \n par un seul et unique espace
    $title = str_replace("\r", '', $title);
    $title = rtrim(str_replace("\n", '[\n]', $title));

    // Remplacer autres caracteres que la merde de JQuery a l'obligation de mettre
    $title = str_replace('&Atilde;&iexcl;', '&aacute;', $title);
    $title = str_replace('&Atilde;&nbsp;', '&agrave;', $title);
    $title = str_replace('&Atilde;&copy;', '&eacute;', $title);
    $title = str_replace('&Atilde;&scaron;', '&egrave;', $title);
    $title = str_replace('&Atilde;&sup1;', '&ugrave;', $title);
    $title = str_replace('&Atilde;&ordf;', '&ecirc;', $title);
    $title = str_replace('&Atilde;&cent;', '&acirc;', $title);
    $title = str_replace('&Atilde;&reg;', '&icirc;', $title);
    $title = str_replace('&Atilde;&macr;', '&uml;', $title);
    $title = str_replace('&Atilde;&sect;', '&ccedil;', $title);
    $title = str_replace('&Acirc;&euro;&trade;', "'", $title);
    $title = str_replace('&acirc;&euro;&trade;', "'", $title);

  // Enregistrer title
    $C = new LOG($title_filename, false);
    //$C->fopen_mode = 'w'; //ca marche pas, donc je fais un unlink
    $C->insert('<div class="medi-section" style="background-image: url('.PUBLIC_ROOT.'images/ms_background.jpg?v='.date('YmdHis').');">' );
    $C->insert('    <div class="ms-title">'.$title.'</div>');
    $C->insert('</div>');
    $C->close();
  }else{
    //effacer $title_filename
    //TODO
  }

// Return no errors to jQuery
  echo 'OK';
?>
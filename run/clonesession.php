<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';

// Get request vars
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, 0), 'a-zA-Z0-9', 8, false);
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, false), 'a-zA-Z0-9', 8, false);
  $pincode  = clear_request_param(getRequest_param('pin', false), '0-9', 5, false);
  
// Get other vars
  $USER_SESSION = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$codalbum, false);
  $IP       = getClient_ip();
  $LONGIP   = @sprintf("%u",ip2long($IP)) | '0';
  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre

// TODO: Add LOGs

if(!empty($pincode))
  $pincode = md5($pincode);

// Lire fichier de configuation de l'album
if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true)
  $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';


  if(!empty($codalbum)){
    switch($action){
      case 'clone':
        if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn')){
          $NEW_USER_KEY = explode( ';', file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn'), 3);
//echo $NEW_USER_KEY[0];
          if(count($NEW_USER_KEY)>0){
          // Comprobar PIN
            if($NEW_USER_KEY[1]==$pincode){
            // SUCCESS: Refresh/Create USER_KEY cookie
              setcookie(COOKIE_USER_SESSION.$codalbum, $NEW_USER_KEY[0], time() + (3600 * 24 * 10), PUBLIC_ROOT); //Cookie for 10 Days

            // Remove clone request
              unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn');

            // Redirect user to album
              header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.'?'.URI_QUERY_RIGHTS_KEY.'='.$AL_CONF['RKEY']);
            }else{
                //ERROR: PIN incorrecto
                //TODO: PIN incorrecto
                echo '<h1>PIN incorrect</h1>';
            }
          }else{
            // ERROR: No user key found on clone request
            // TODO: Error handling
          // Remove clone request
            unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn');
          }
        }else{
            // TODO: Error handling
            echo 'ERROR: codalbum='.$codalbum.' action='.$action.' longip='.$LONGIP;
        }
      break;

      case 'merge':
      // Comprobar si existe peticion de clonado para esta ip
        if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn')){

        // Comprobar si existe sesion para el equipo actual
          $CUR_SESSION_KEY = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$codalbum, false);
          $CUR_SESSION = '';

          // Comprobar sesion a clonar
            $NEW_USER_KEY = explode( ';', file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn'), 3);
            
            if(count($NEW_USER_KEY)>0){
            // Comprobar pin
              if($NEW_USER_KEY[1]==$pincode){
                // SUCCESS: 
                // Recuperar sesion actual
                if(!empty($CUR_SESSION_KEY) && file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$CUR_SESSION_KEY)){
                  $CUR_SESSION = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$CUR_SESSION_KEY);
                  if(is_writable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$NEW_USER_KEY[0])){
                  // Volcar sesion actual en sesion a clonar
                   file_put_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$NEW_USER_KEY[0], $CUR_SESSION, FILE_APPEND); 
                  }
                }
                // Clonar cookie de sesion
                setcookie(COOKIE_USER_SESSION.$codalbum, $NEW_USER_KEY[0], time() + (3600 * 24 * 10), PUBLIC_ROOT); //Cookie for 10 Days

                // Eliminar peticion de clonado
                unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn');
                
                // Redirect user to album
                header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.'?'.URI_QUERY_RIGHTS_KEY.'='.$AL_CONF['RKEY']);
                exit;
              }else{
                //ERROR: PIN incorrecto
                //TODO: PIN incorrecto
                echo '<h1>PIN incorrect</h1>';
              }
          // Redirect user to album
              header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
              exit;
            }else{
                // ERROR: Session not found on clone request
                // TODO: Error: Session not found
              }
          }
        
      break;
      
      case 'cancel':
      // Eliminar peticion de clonado
        @unlink(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn');
      // Redirect user to album
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
        exit;
        break;

      case 'request':
      default:
      if(!empty($USER_SESSION) && !empty($LONGIP)){
        
        $PIN = make_rkey(5, '0123456789');
        file_put_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn', $USER_SESSION.';'.md5($PIN));
        echo $PIN;
      }
      break;
    }
  }
?>
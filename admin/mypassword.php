<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit();
  }

  //sleep(3); // Sleep 3 seconds for self protect from brute force attack
# =======================================================================
function user_redirect($URL){
  if(SYS_HTTPS_AVAILABLE){
    header('Location: https://'.$URL);
  }else{
    header('Location: http://'.$URL);
  }
}
# =======================================================================
  $pwd_file = SYSTEM_ROOT.ETC_DIR.'users.csv';
  $IP = getClient_ip();
  $LOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/events.log');
  $success = 0;

  $old_pwd = clear_request_param(getRequest_param('old_pwd', false), 'a-zA-Z0-9', 32, false);
  $new_pwd_1 = clear_request_param(getRequest_param('new_pwd_1', false), 'a-zA-Z0-9', 32, false);
  $new_pwd_2 = clear_request_param(getRequest_param('new_pwd_2', false), 'a-zA-Z0-9', 32, false);


  if(!empty($new_pwd_1) && !empty($new_pwd_2) && !empty($old_pwd)){
    $old_pwd = md5($old_pwd);
    
    if($new_pwd_1==$new_pwd_2){
      // Preparar fichero temporal
      $temp_filename = $pwd_file.'.'.make_rkey(5);
      $tmp_pwd_file = fopen($temp_filename, 'w');
      
      // Recorrer fichero de usuarios
      foreach(file($pwd_file) as $user){
        $U = explode(';', $user, 5);

        // Comprobar ID de usuario
        if($U[1] == $_SESSION['UID']){
          // Si el hash es correcto cambiar contrasena
          if($U[0]==$old_pwd){
            $success = fwrite($tmp_pwd_file, md5($new_pwd_1).substr($user, strpos($user,';')));
          }
        }else{
          fwrite($tmp_pwd_file, $user);
        }
      }
      
      fclose($tmp_pwd_file);

      // Actualizar archivo de contrasenas
      if($success > 0){
        if(file_exists($pwd_file.'.old'))
          unlink($pwd_file.'.old');
        
        rename($pwd_file, $pwd_file.'.old'); // Crear copia de seguridad
        rename($temp_filename, $pwd_file);  // Renombrar fichero temporal
        
        $LOG->insert('[*] uid='.$_SESSION['UID'].' - uname='.$_SESSION['UNAME'].' - ip='.$IP.' - action=UPDATE PASWORD - msg=SUCCESS', true);
        user_redirect(SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'mypassword.php?res=1');
      }else{
      // Bad password
        unlink($temp_filename);
        $LOG->insert('[!] uid='.$_SESSION['UID'].' - uname='.$_SESSION['UNAME'].' - ip='.$IP.' - action=UPDATE PASWORD - msg=BAD OLD PASSWORD', true);
        user_redirect(SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'mypassword.php?res=badpwd');
      }
    }else{
    // No password matching
      $LOG->insert('[!] uid='.$_SESSION['UID'].' - uname='.$_SESSION['UNAME'].' - ip='.$IP.' - action=UPDATE PASWORD - msg=NEW PASSWORD CONFIRMATION ERROR', true);
      user_reirect(SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'mypassword.php?res=nomatch');
    }
  }else{
  // Empty password
    $LOG->insert('[!] uid='.$_SESSION['UID'].' - uname='.$_SESSION['UNAME'].' - ip='.$IP.' - action=UPDATE PASWORD - msg=EMPTY PASSWORD', true);
    user_redirect(SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'mypassword.php?res=0');
  }
  $LOG->close();
?>
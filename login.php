<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/settings.php';

  include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include SYSTEM_ROOT.ETC_DIR.'versions.php';
  include SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include SYSTEM_ROOT.LIB_DIR.'log.class.php';

  if(is_logged()){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR);
    exit;
  }elseif(SYS_HTTPS_AVAILABLE && !isHTTPS()){
    header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    exit;
  }

  $action = clear_request_param(getRequest_param(URI_QUERY_ACTION, 'show_form'), 'a-z', 8, false);
  $password = clear_request_param(getRequest_param(URI_QUERY_PASSWORD, ''), 'a-zA-Z0-9', 32, false);
  $userid = clear_request_param(getRequest_param(URI_QUERY_USER, ''), 'a-zA-Z0-9', 32, false);

  if($action == 'login'){
    $pwd_file = SYSTEM_ROOT.ETC_DIR.'users.csv';
    $IP = getClient_ip();
    
    sleep(3); // Sleep 3 seconds for self protect from brute force attack

    // Preparer journal d'evenements
    $LOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'logs/events.log');
    
    if($password=='' || $userid==''){
      $LOG->insert('[!] Tentative de connexion avec un mot de passe vide - ip='.$IP);
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
      exit;
    }elseif(!file_exists($pwd_file)){
      $LOG->insert('[!] Tentative de connexion. Le fichier de mots de passe n\'existe pas - ip='.$IP);
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'error.php?'.URI_QUERY_ERROR.'=PWD_FILE_NOT_FOUND');
      exit;
    }else{
      if(do_login($userid, $password, file($pwd_file))){
        $LOG->insert('[@] Connexion reussie - uid='.$userid.' - uname='.$_SESSION['UNAME'].' - ip='.$IP);
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR);
        exit;
      }else{
        $LOG->insert('[!] Tentative de connexion avec un mot de passe invalide - uid='.$userid.' - ip='.$IP);
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
        exit;
      }
    }
    
    $LOG->close();
  }
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo PUBLIC_ROOT; ?>css/login.css?v=<?php echo VERSION_CSS; ?>" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:600italic,400,300,600,700" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div class="login-form">
<!--
      <div class="logo">
        <img src="<?php echo PUBLIC_ROOT.'images/logo-mjc-login.png'; ?>" />
      </div>
-->
      <form action="<?php echo PUBLIC_ROOT.'login.php'; ?>" method="post">
        <input type="hidden" name="<?php echo URI_QUERY_ACTION; ?>" value="login" />
        <div class="select-box user">
          <select name="<?php echo URI_QUERY_USER; ?>">
            <option disabled selected style="display:none;">Choisissez administrateur</option>
            <option value="1">Philippe</option>
            <option value="2">Alberto</option>
          </select>
        </div>
        <div class="text-box">
          <input type="password" name="<?php echo URI_QUERY_PASSWORD; ?>" placeholder="Mot de passe" />
        </div>
        <div class="button-box">
          <input type="submit" value="S'identifier" class="button" />
        </div>
        <div class="clear"></div>
      </form>
    </div>
  </body>
</html>
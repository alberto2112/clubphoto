<?php
    if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../../settings.php';

    include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
    include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
    include_once SYSTEM_ROOT.LIB_DIR.'htmlhelper.badlib.php';
    include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

    // Forcer administrateur
    if(!is_admin()){
      if(SYS_HTTPS_AVAILABLE){
        header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }else{
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }
      exit();
    }

    // Charger les parametres des notifications
    // Si le fichier config.php n'existe pas
    // remplir l'array avec des parametres par defaut
    if(is_readable(SYSTEM_ROOT.ETC_DIR.'push_'.$_SESSION['UID'].'.config.php'))
      $PUSH = include SYSTEM_ROOT.ETC_DIR.'push_'.$_SESSION['UID'].'.config.php';
    else
      $PUSH = include SYSTEM_ROOT.ETC_DIR.'push_def.config.php';// remplir l'array avec des parametres par defaut

    $HTML = array(
      'sendnotifications'     => getHTML4CheckBoxState( get_arr_value($PUSH,'sendnotifications') ),
      'newalbum'     => getHTML4CheckBoxState( get_arr_value($PUSH,'newalbum') ),
      'delalbum'     => getHTML4CheckBoxState( get_arr_value($PUSH,'delalbum') ),
      'uploaderr'    => getHTML4CheckBoxState( get_arr_value($PUSH,'uploaderr') ),
      'ratingerr'    => getHTML4CheckBoxState( get_arr_value($PUSH,'ratingerr') ),
      'loginfail'    => getHTML4CheckBoxState( get_arr_value($PUSH,'loginfail') ),
      'quotalimit'    => getHTML4CheckBoxState( get_arr_value($PUSH,'quotalimit') ),
      'appid'        => 'value="'.get_arr_value($PUSH,'appid', false).'" ',
      'appsecret'    =>'value="'.get_arr_value($PUSH,'appsecret', false).'" '
    );
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel_forms.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
  </head>
	<body>
      <div class="panel">
        <form action="<?php echo PUBLIC_ROOT.ADMIN_DIR.RUN_DIR; ?>pushNotifications.php" method="post" class="basic-grey">
          <input type="hidden" name="UID" value="<?php echo $_SESSION['UID']; ?>" />
          <!-- service -->
          <div class="card">
            <div class="row title red">
              <h1>Notifications push</h1>
            </div>
            <ul class="row border-up content">
              <li><span><input type="checkbox" name="sendnotifications" value="1" <?php echo $HTML['sendnotifications']; ?>/>Activer service de notifications push</span></li>
            </ul>
          </div>
          <!-- /service -->
          
          <!-- Identifiants -->
          <div class="card">
            <div class="row">
              <h2>Identifiants Instapush</h2>
            </div>
            <ul class="row border-up content">
              <li>
                  <span>Key:</span>
                  <input type="text" name="appid" placeholder="Cl&eacute; d'application" <?php echo $HTML['appid']; ?>/>
              </li>
              <li>
                  <span>Secret:</span>
                  <input type="text" name="appsecret" placeholder="Valeur secret" <?php echo $HTML['appsecret']; ?>/>
              </li>
            </ul>
          </div>
          <!-- /Identifiants -->
          
          <!-- ou -->
          <div class="card">
            <div class="row">
              <h2>Recevoir notifications pour</h2>
            </div>
            <ul class="row border-up content">
              <li><span><input type="checkbox" name="newalbum" value="1" <?php echo $HTML['newalbum']; ?>/>Nouvel album cr&eacute;e</span></li>
              <li><span><input type="checkbox" name="delalbum" value="1" <?php echo $HTML['delalbum']; ?>/>Supression d'album</span></li>
              <li><span><input type="checkbox" name="uploaderr" value="1" <?php echo $HTML['uploaderr']; ?>/>Erreur de t&eacute;l&eacute;chargement</span></li>
              <li><span><input type="checkbox" name="ratingerr" value="1" <?php echo $HTML['ratingerr']; ?>/>Erreur de votation.</span></li>
              <li><span><input type="checkbox" name="loginfail" value="1" <?php echo $HTML['loginfail']; ?>/>Login d'administrateur rat&eacute;</span></li>
              <li><span><input type="checkbox" name="quotalimit" value="1" <?php echo $HTML['quotalimit']; ?>/>Limite de quota atteint</span></li>
            </ul>
          </div>
          <!-- /ou -->

          <input type="submit" class="button" value="Enregistrer" />
      </form>
    </div>
  </body>
</html>
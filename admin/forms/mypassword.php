<?php
    if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../../settings.php';

    include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'login.lib.php';
    include SYSTEM_ROOT.ETC_DIR.'versions.php';

    // Forcer administrateur
    if(!is_admin()){
      if(SYS_HTTPS_AVAILABLE){
        header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }else{
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }
      exit();
    }


	$result = clear_request_param(getRequest_param('res', 'step0'), 'a-zA-Z0-9', 16, false);
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
<?php
  if($result=='1'){
?>
      <div class="err-message"><h1>Bravo</h1><p>Le mot de passe a &eacute;t&eacute; chang&eacute; avec success</p></div>
<?php
  }else{
    switch($result){
      case '0':
        echo '<div class="err-message"><h1>Mot de passe vide</h1><p>Le mot de passe n\'a pas pu etre chang&eacute;</p></div>';
        break;
      case 'badpwd':
        echo '<div class="err-message"><h1>Mot de passe incorrect</h1><p>Le mot de passe n\'a pas pu etre chang&eacute;</p></div>';
        break;
      case 'nomatch':
        echo '<div class="err-message"><h1>Confirmation incorrecte</h1><p>Le mot de passe n\'a pas pu etre chang&eacute;</p></div>';
        break;
    }
?>
      <div class="panel">
        <form action="<?php echo PUBLIC_ROOT.ADMIN_DIR; ?>mypassword.php" method="post" class="basic-grey">          
          <div class="card">
            <div class="row title red">
              <h1>Modifier mot de passe</h1>
            </div>
            <div class="row">
              <div class="content">
                  <input id="pwd" type="password" name="old_pwd" placeholder="Mot de passe actuel" />
              </div>
            </div>
            
            <div class="row border-up">
              <div class="content">
                  <input id="new_pwd_1" type="password" name="new_pwd_1" placeholder="Nouveau mot de passe" />
                  <input id="new_pwd_2" type="password" name="new_pwd_2" placeholder="Confirmez nouveau mot de passe" />
              </div>
            </div>
          </div>      
          <input type="submit" class="button" value="Enregistrer" />
      </form>
    </div>
<?php
 }
?>
  </body>
</html>
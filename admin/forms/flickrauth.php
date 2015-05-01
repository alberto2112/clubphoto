<?php
    if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../../settings.php';

    include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'login.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'htmlhelper.badlib.php';

    // Forcer administrateur
    if(!is_admin()){
      if(SYS_HTTPS_AVAILABLE){
        header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }else{
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
      }
      exit();
    }

    $err = clear_request_param(getRequest_param(URI_QUERY_ERROR, false), 'a-zA-Z0-9', 12, false);

    @include_once SYSTEM_ROOT.ETC_DIR.'flickr.php';

    $HTML = array(
      'apikey'    =>( defined('FLICKR_KEY') )?'value="'.FLICKR_KEY.'" ':'',
      'apisecret' =>( defined('FLICKR_SECRET') )?'value="'.FLICKR_SECRET.'" ':'',
      'apitoken'  =>( defined('FLICKR_TOKEN') )?'value="'.FLICKR_TOKEN.'" ':''
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

<?php
  if($err=='TOKENOK'){
    //Show message to user
    echo '<div class="card">
    <div class="row title red">
      <h2 class="title green">Token OK</h2>
    </div>
    <div class="row">
      <p class="content">Le token a &eacute;t&eacute; bien enregistr&eacute;. <a href="'.PUBLIC_ROOT.ADMIN_DIR.'">Retour</a></p>
    </div>    
</div>';
  }
?>
        <form action="<?php echo PUBLIC_ROOT.ADMIN_DIR.RUN_DIR; ?>flickrauth.php" method="post" class="basic-grey">
          <div class="card">
            <div class="row title red">
              <h1>Flickr auth</h1>
            </div>
            <div class="row">
              <div class="content">
                  <span>Cl&eacute;:</span>
                  <input id="sortie" type="text" name="apikey" placeholder="Cl&eacute; d'application" <?php echo $HTML['apikey']; ?>/>

                  <span>Secret:</span>
                  <input id="sortie" type="text" name="apisecret" placeholder="Valeur secret" <?php echo $HTML['apisecret']; ?>/>

                  <span>Cl&eacute;:</span>
                  <input id="sortie" type="text" name="apitoken" placeholder="Cl&eacute; d'autoritation" <?php echo $HTML['apitoken']; ?>/>
              </div>
            </div>
          </div>
          <input type="submit" class="button" value="Envoyer" />
      </form>
    </div>
  </body>
</html>
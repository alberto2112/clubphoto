<?php
  if(!defined('SYSTEM_ROOT'))
    require_once __DIR__.'/../../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit;
  }

  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.LIB_DIR.'phpFlickr.php';

  @include_once SYSTEM_ROOT.ETC_DIR.'flickr.php';


  $apikey    = clear_request_param(getRequest_param('apikey', ''), 'a-zA-Z0-9\-', 32, false);
  $apisecret = clear_request_param(getRequest_param('apisecret', ''), 'a-zA-Z0-9\-', 32, false);
  $apitoken  = clear_request_param(getRequest_param('apitoken', ''), 'a-zA-Z0-9\-', 64, false);

  $FROB  = clear_request_param(getRequest_param('frob', ''), 'a-zA-Z0-9\-', 64, false);

  $config_filename = SYSTEM_ROOT.ETC_DIR.'flickr.php';

  if(!defined('FLICKR_KEY'))
    define('FLICKR_KEY', $apikey);

  if(!defined('FLICKR_SECRET'))
    define('FLICKR_SECRET', $apisecret);

// Create flickr object
  $flickr = new phpFlickr(FLICKR_KEY, FLICKR_SECRET, true);

  if(!$FROB && empty($apitoken)){ // Token not found, then get auth
      $strconfig = '<?php
    define(\'FLICKR_KEY\', \''.FLICKR_KEY.'\');
    define(\'FLICKR_SECRET\',\''.FLICKR_SECRET.'\');
    define(\'FLICKR_TOKEN\', \''.$apitoken.'\');
?>';

     //Write config file
      file_put_contents($config_filename,$strconfig);
     
     // Get Auth
      $flickr->auth('write');
   }else {
      //If authorized, print the token
    if(empty($apitoken)){
      $tokenArgs = $flickr->auth_getToken($_GET['frob']);
      //   echo "<pre>"; var_dump($tokenArgs); echo "</pre>";
      $apitoken = $tokenArgs['token']['_content'];
    }
     
      //echo "<pre>"; var_dump($tokenArgs); echo "</pre>";
      $strconfig = '<?php
    define(\'FLICKR_KEY\', \''.FLICKR_KEY.'\');
    define(\'FLICKR_SECRET\',\''.FLICKR_SECRET.'\');
    define(\'FLICKR_TOKEN\', \''.$apitoken.'\');
?>';
     
     // Write config file
      file_put_contents($config_filename,$strconfig);
     
     // User redirect
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'flickrauth.php?'.URI_QUERY_ERROR.'=TOKENOK');
   }
?>
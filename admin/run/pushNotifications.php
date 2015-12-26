<?php
  //RUN

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


  $UID    = clear_request_param(getRequest_param('UID', ''), 'a-zA-Z0-9\-', 32, false);
  $action = clear_request_param(getRequest_param(URI_QUERY_ACTION, ''), 'a-zA-Z0-9', 12, false);


  if(!empty($UID)){
    $config_filename = SYSTEM_ROOT.ETC_DIR.'push_'.$UID.'.config.php';
    
    if($action=='test'){
      if(is_readable($config_filename)){
        require_once SYSTEM_ROOT.LIB_DIR.'pushservice.class.php';
        
        // Load config
        $C = include $config_filename;
        
        // Send push notification
        $push = PushService::getInstance($C['appid'], $C['appsecret']);
        $result = $push->track('Test', array('domain'=>SITE_DOMAIN));
        
        $err = ($result)? 'TEST_OK':'FAILED_TEST';
        
        // User redirect
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.'?'.URI_QUERY_ERROR.'='.$err);
        exit;
      }
      
    }else{
      $appid     = clear_request_param(getRequest_param('appid', ''), 'a-zA-Z0-9\-', 32, false);
      $appsecret = clear_request_param(getRequest_param('appsecret', ''), 'a-zA-Z0-9\-', 32, false);

      $sendnotifications = clear_request_param(getRequest_param('sendnotifications', '0'),'0-9',2,false);
      $newalbum          = clear_request_param(getRequest_param('newalbum', '0'),'0-9',2,false);
      $delalbum          = clear_request_param(getRequest_param('delalbum', '0'),'0-9',2,false);
      $uploaderr         = clear_request_param(getRequest_param('uploaderr', '0'),'0-9',2,false);
      $ratingerr         = clear_request_param(getRequest_param('ratingerr', '0'),'0-9',2,false);
      $loginfail         = clear_request_param(getRequest_param('loginfail', '0'),'0-9',2,false);
      $quotalimit        = clear_request_param(getRequest_param('quotalimit', '0'),'0-9',2,false);

      $strconfig = '<?php return array(
      \'appid\'=>\''.$appid.'\',
      \'appsecret\'=>\''.$appsecret.'\',
      \'sendnotifications\'=>\''.$sendnotifications.'\',
      \'newalbum\'=>\''.$newalbum.'\',
      \'delalbum\'=>\''.$delalbum.'\',
      \'uploaderr\'=>\''.$uploaderr.'\',
      \'ratingerr\'=>\''.$ratingerr.'\',
      \'loginfail\'=>\''.$loginfail.'\',
      \'quotalimit\'=>\''.$quotalimit.'\'
      ); ?>';

     //Write config file
      file_put_contents($config_filename, $strconfig);
      
    // User redirect
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.'?'.URI_QUERY_ERROR.'=OK');
      exit;
    }
  }
// User redirect
  header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ADMIN_DIR.'?'.URI_QUERY_ERROR.'=UNKNOWN_UID');
?>
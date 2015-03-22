<?php
  if(!defined('SYSTEM_ROOT')) 
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';

  $fingerprint = clear_request_param(getRequest_param(URI_QUERY_FINGERPRINT, false), 'a-zA-Z0-9', 12, false);
  $action      = clear_request_param(getRequest_param(URI_QUERY_ACTION, false), 'a-zA-Z0-9', 8, false);

if($fingerprint)
  setcookie(COOKIE_FINGERPRINT, $fingerprint,time()+(3600*24*30), PUBLIC_ROOT, SYS_HTTPS_AVAILABLE);
?>
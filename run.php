<?php
  if(!defined('SYSTEM_ROOT'))
    require __DIR__.'/settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, ''), 'a-zA-Z0-9', 8, false);
  //$RKEY = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);
  $action = clear_request_param(getRequest_param(URI_QUERY_ACTION, 'null'), 'a-z', 8, false);

  switch($action){
    case 'new_rkey':
      echo make_rkey();
      break;
  }
?>
<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

// Get request vars
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, 0), 'a-zA-Z0-9', 8, false);
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, false), 'a-zA-Z0-9', 8, false);
  
// Get other vars
  $USER_SESSION = get_arr_value($_COOKIE, COOKIE_USER_KEY.$codalbum, false);
  $IP       = getClient_ip();
  $LONGIP   = @sprintf("%u",ip2long($IP)) | '0';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>MJC-CP - Clonner sesion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/clonesession.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
  </head>
  <body>
<?php
if($action=='detected')
  include SYSTEM_ROOT.FORMS_DIR.'clonesession_step2.inc';
else
  include SYSTEM_ROOT.FORMS_DIR.'clonesession_step1.inc';
?>
  </body>
</html>
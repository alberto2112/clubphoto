<?php
  define('SITE_DOMAIN','192.168.1.40');   // Sans 'http://'
  define('PUBLIC_ROOT','/clubphoto/');    // Toujours termine par slash (/)
  define('SYSTEM_ROOT',realpath( dirname(__FILE__)).'/');

  define('DATA_DIR','data/');
  define('LIB_DIR','lib/');
  define('ETC_DIR','etc/');
  define('PROC_DIR','proc/');
  define('TRASH_DIR','trash/');
  define('FORMS_DIR','forms/');
  define('RUN_DIR','run/');
  define('ALBUMS_DIR','albums/');
  define('ADMIN_DIR','admin/');
  define('WORKSPACE_DIR','tmp/');
  define('SOFTWARE_DIR','bin/');

  define('DIM_THUMB','186x186');
  define('DIM_MEDIUM','512x512');
  define('DIM_LARGE','2048x2048');

  define('DISK_QUOTA',307200); // 300Mo - Determine l'espace maxi d'usage de disque
  define('FILE_USED_QUOTA', SYSTEM_ROOT.ALBUMS_DIR.'used_quota.txt');

  define('SYS_MEMORY_LIMIT', '256M');
  define('SYS_HTTPS_AVAILABLE', false);

  define('CHARSET','ISO-8859-15');

  define('OPT_HTACCESS_AVAILABLE',true);
  define('OPT_DEVELOPPING',true);

  define('URI_QUERY_PHOTO','p');
  define('URI_QUERY_ALBUM','a');
  define('URI_QUERY_ACTION','x');
  define('URI_QUERY_JOURNAL','J');
  define('URI_QUERY_RATE_METHOD','rm');
  define('URI_QUERY_POINTS','n');
  define('URI_QUERY_ERROR','r');
  define('URI_QUERY_USER','U');
  define('URI_QUERY_PASSWORD','mdp');
  define('URI_QUERY_RIGHTS_KEY','rgtk');
  define('URI_QUERY_COMMENTS','C');
  define('URI_QUERY_FINGERPRINT','fp');

  define('COOKIE_USER_SESSION','USRKY');
  define('COOKIE_RIGHTS_KEY','RKEY');
  define('COOKIE_FINGERPRINT','JSFPSESSID');

  define('SESSION_LIFE_RKEY', 3600*3);     //3 HOURS #[!] NEW
  define('SESSION_LIFE_MEMBER', 3600*24*15);  //15 DAYS #[!] NEW
  define('SESSION_LIFE_FINGERPRINT', 3600*24*30);  //30 DAYS #[!] NEW

  define('USER_LEVEL_SUPERADMIN','1');
  define('USER_LEVEL_ADMIN','2');
  define('USER_LEVEL_EXPO','5');
  define('USER_LEVEL_GUEST','9');

# ======================================================= #
  if(function_exists('date_default_timezone_set'))
    date_default_timezone_set("Europe/Paris");

  if(OPT_DEVELOPPING===true){
    //error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); // Best develop practice
    error_reporting(E_ALL);
    ini_set('display_errors','On');
  }else{
    error_reporting(0); // No errors please!
    # error_reporting(E_ALL); // Repport all errors to be logged
    ini_set('display_errors','Off');
    ini_set('log_errors','On');
    # ini_set('error_log',SYSTEM_ROOT.'php_error.log');
  }

  if(!defined('__DIR__'))
    define('__DIR__', dirname(__FILE__));
?>
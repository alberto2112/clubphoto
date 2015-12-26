<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
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

  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';

// Get vars
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, false), 'a-z', 8, false);
  $journal  = clear_request_param(getRequest_param(URI_QUERY_JOURNAL, false), 'a-zA-Z0-9\.', 42, false);
  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
	
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel_journals.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <h1><a class="header-button ico-home notext" href="<?php echo PUBLIC_ROOT.ADMIN_DIR; ?>">CPanel</a><?php echo ($journal==false)? 'Journaux' : 'Journal: '.$codalbum.'/'.$journal ?></h1>
    <div class="j-list">
<?php
// Print lof journals
  echo '<h2>System</h2><ul>'."\n";
  foreach(read_dir(SYSTEM_ROOT.ADMIN_DIR.'logs/','*.log',true) as $file){
    echo '<li><a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_JOURNAL.'='.$file.'">'.$file.'</a></li>'."\n";
  }
  echo '</ul>'."\n";

  echo '<h2>Albums</h2><ul>';
  $lof_albums = list_dirs(SYSTEM_ROOT.ALBUMS_DIR, true);
  if(count($lof_albums)>0){
    foreach($lof_albums as $album){
      if($album==$codalbum){
        echo '<li><h3 style="background-color:#aaa;color:#fff;">'.$album.'</h3><ul>';
      }else{
        echo '<li><h3>'.$album.'</h3><ul>';
      }
      foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$album.'/logs/','*.log',true) as $file){
        if($album==$codalbum && $journal==$file){
          echo '<li style="background-color:#aaa;color:#fff;">'.$file.'</li>'."\n";
        }else{
          echo '<li><a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_ALBUM.'='.$album.'&amp;'.URI_QUERY_JOURNAL.'='.$file.'">'.$file.'</a></li>'."\n";
        }
      }
      echo '</ul></li>'."\n";
    }
  }
  echo '</ul>';
?>
    </div>
    <div class="j-file">
      <pre>
<?php
  if($journal!=false){
    if($codalbum==false){
      if(is_readable(SYSTEM_ROOT.ADMIN_DIR.'logs/'.$journal)){
        print file_get_contents(SYSTEM_ROOT.ADMIN_DIR.'logs/'.$journal);
      }
    }else{  
      if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/'.$journal)){
        print file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/'.$journal);
      }    
    }
  }
?>
      </pre>
    </div>
  </body>
</html>
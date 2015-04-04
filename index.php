<?php 
  include 'settings.php';
  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';
?>
<html>
<head>
    <title>Club photo - MJC Rodez</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/reset.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/buttons.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/home.css?v='.VERSION_CSS; ?>" type="text/css" />
</head> 
<body>
  <div class="header">
      <img src="images/logo-mjc.png" class="logo-mjc" />
      <a href="login.php" class="link-login">Administrer</a>
  </div>
  
  <div class="medi-section" style="background-image: url(<?php echo PUBLIC_ROOT.'images/ms_background.jpg?v='.VERSION_HOME_BG; ?>);">
    <div class="ms-title">Photo la plus &eacute;toil&eacute; pour la sortie: Le canyon de Bozouls</div>
  </div>
  
  <div class="albums">
<?php
  foreach(glob(SYSTEM_ROOT.ALBUMS_DIR.'*', GLOB_ONLYDIR) as $folder){
    if($folder!='.' && $folder!='..'){
      $fname = basename($folder);
      $nphotos = count_files(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs', '*.jpg');
      
      if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php')===true)
        $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php';
      else
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre

      echo '<div class="card">';
        echo '<div class="row">';
          echo '<div class="content image">';
            echo '<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$fname.'" class="info-photo" title="Regarder album"';
      // Add any photo thumb
          foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg',true) as $file){
            echo ' style="background-image: url('.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/medium/'.$file.');"';
            break;
          }
            echo '><span class="info-aname">'.$AL_CONF['albumname'].'</span>';
            echo '<span class="info-nphotos">'.$nphotos.'</span></a>';
          echo '</div>';
        echo '</div>';
      echo '</div>';
    }
  }
?>
  </div>
<?php
  include SYSTEM_ROOT.'footer.php';
?>
</body>
</html>
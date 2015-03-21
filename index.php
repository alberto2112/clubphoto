<?php 
  include 'settings.php';
  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
?>
<html>
<head>
    <title>Club photo - MJC Rodez</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css?v=20150212" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=20150215" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=20150218" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/home.css?v=20150311" type="text/css" />
</head> 
<body>
  <div class="header">
      <img src="images/logo-mjc.png" class="logo-mjc" />
      <a href="login.php" class="link-login">Administrer</a>
  </div>
  
  <div class="medi-section">
    <div class="ms-title">Photo la plus &eacute;toil&eacute; pour la sortie: L'Aubrac enneig&eacute;</div>
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

      if ($hdir = opendir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs')) {
          $entry = readdir($hdir);
          closedir($hdir);
      }
      
      echo '<div class="card">';
        echo '<div class="row">';
          echo '<div class="content image">';
            echo '<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$fname.'" class="info-photo" title="Regarder album"';
      // Add any photo thumb
          foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg',true) as $file){
            echo ' style="background-image: url('.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/medium/'.$file.');"';
            break;
          }
/*
      if ($hdir = opendir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs')) {
        while (false !== ($entry = readdir($hdir))) {
          
          if($entry != '.' && $entry != '..')
            break;
        }

          echo '<span><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs/'.basename($entry).'" /></span>';
          closedir($hdir);
      }
*/
            echo '><span class="info-aname">'.$AL_CONF['albumname'].'</span>';
            echo '<span class="info-nphotos">'.$nphotos.'</span></a>';
          echo '</div>';
        echo '</div>';
      echo '</div>';
    }
  }
?>
  </div>
  <p class="footnote">Club photo - MJC Rodez</p>
</body>
</html>
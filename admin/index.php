<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
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

// Calculer used quota
    if(DISK_QUOTA > 0){
      if(file_exists(FILE_USED_QUOTA)){
        $quota_used = file_get_contents(FILE_USED_QUOTA,null,null,null,9); // in Ko
        $quota_used_prct = ($quota_used * 100) / DISK_QUOTA;
      }
    }
?>
<html>
<head>
    <title>CPanel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
  echo '
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS.'" type="text/css" />
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/cpanel.css?v='.VERSION_CSS.'" type="text/css" />
    <script src="'.PUBLIC_ROOT.'js/jquery.1.10.1.min.js?v='.VERSION_JS.'"></script>';
?>

</head> 
<body>
  <div class="header">
      <h1>Administration site votations</h1>
      <h4>Bienvenu: <?php echo $_SESSION['UNAME']; ?></h4>
  </div>
<!-- <panel> -->
  <div class="panel">
    <!-- <Albums> -->
    <div class="card">
      <div class="row">
        <h2>Albums</h2>
      </div>
      <div class="row border-up">
        <ul class="content">
          <li><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR; ?>manageAlbum.php">Cr&eacute;er nouvel album</a></li>
        </ul>
      </div>
<?php
  $ddc=0;
  foreach(glob(SYSTEM_ROOT.ALBUMS_DIR.'*', GLOB_ONLYDIR) as $folder){
    if($folder!='.' && $folder!='..'){
      $ddc++;
      $fname = basename($folder);
      $nphotos = count_files(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg');
      
      if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php')===true)
        $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php';
      else
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre
      
      echo '<div class="row border-up"><ul class="content">';
        echo '<li>';
          echo '<a href="#" class="album-name" onclick="javascript:showList(\'ddwn-'.$ddc.'\');">'.$AL_CONF['albumname'].'<span class="nphotos">'.$nphotos.'</span></a>';
        echo '</li>';
        echo '<li style="display:none;" id="ddwn-'.$ddc.'">';
/*
        echo '<div class="album-thmbnl"';
        // Add any photo thumb
          $BG=false;
          foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg',true) as $file){
            echo ' style="background-image:url('.PUBLIC_ROOT.ALBUMS_DIR.$fname.'/photos/medium/'.$file.');"';
            $BG=true;
            break;
          }
          if(!$BG){
            echo ' style="background-image:url('.PUBLIC_ROOT.'images/album_no_images.jpg);"';
          }
        echo '></div>';
*/
        echo '<ul class="album-tools">';
          echo '<li><a class="tool-view" href="http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$fname.'">Regarder album</a></li>';
          echo '<li><a class="tool-logs" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_ALBUM.'='.$fname.'">Journaux</a></li>';
          echo '<li><a class="tool-settings" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'">Parametrage</a></li>';
          echo '<li><a class="tool-ranking" href="'.PUBLIC_ROOT.ADMIN_DIR.'stats.php?'.URI_QUERY_ALBUM.'='.$fname.'">Votes</a></li>';
          echo '<li><a class="tool-trash" href="#">Envoyer &agrave; la corbeille</a></li>';
          echo '<li><a class="tool-delete" href="#">Suprimer </a></li>';
/*
          echo '<li><a class="tool-trash" href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=totrash">Envoyer &agrave; la corbeille</a></li>';
          echo '<li><a class="tool-delete" href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=delete">Suprimer </a></li>';
*/
        echo '</ul>';
      echo '</li></ul></div>';
    }
  }
?>
      
    </div>
    <!-- </Albums> -->
    <script type="text/javascript">
      function showList(id){
        $("#"+id).toggle();
      }
    </script>
        <!-- <Quota> -->
    <div class="card">
      <div class="row">
        <h2>Quota</h2>
      </div>
      <div class="row">
        <div class="quota-total">
          <div class="quota-used" style="width:<?php echo $quota_used_prct; ?>%;"><span><?php echo human_filesize($quota_used * 1024); ?>o</span></div>
        </div>
<?php
      if($_SESSION['ULEVEL']==USER_LEVEL_SUPERADMIN){
        echo '
      <div class="row">
        <ul class="content">
          <li><a href="'.PUBLIC_ROOT.ADMIN_DIR.RUN_DIR.'quota_recalc.php">Recalculer quota</a></li>
        </ul>
      </div>'."\n";
      }
?>
      </div>
    </div>
    <!-- </Quota> -->

    <!-- <Administrateur> -->
    <div class="card">
      <div class="row">
        <h2>Administrateur</h2>
      </div>
      <div class="row border-up">
        <ul class="content">
          <li><a href="<?php echo FORMS_DIR; ?>mypassword.php">Changer mot de passe</a></li>
        </ul>
      </div>
<?php
      if($_SESSION['ULEVEL']==USER_LEVEL_SUPERADMIN){
        echo '
      <div class="row border-up">
        <ul class="content">
          <li>Nouveau administrateur</li>
        </ul>
      </div>
      <div class="row border-up">
        <ul class="content">
          <li>Gerer administrateurs</li>
        </ul>
      </div>';
      }
?>
    </div>
    <!-- </Administrateur> -->

    <!-- <Journal> -->
    <div class="card">
      <div class="row">
        <h2>Journals</h2>
      </div>
      <div class="row border-up">
        <ul class="content">
          <li><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_JOURNAL.'=events.log'; ?>">Journaux de syst&egrave;me</a></li>
        </ul>
      </div>
      <div class="row border-up">
        <ul class="content">
          <li><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_JOURNAL.'=albums.log'; ?>">Journaux d'albums</a></li>
        </ul>
      </div>
    </div>
    <!-- </Journal> -->
  </div>
<!-- </panel> -->
</body>
</html>
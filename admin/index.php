<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

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
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/cpanel.css?v='.VERSION_CSS.'" type="text/css" />';
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
      <div class="row border-up">
        <ul class="content">
          <li><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR; ?>list_albums.php">Voir albums en cours</a></li>
        </ul>
      </div>
    </div>
    <!-- </Albums> -->
    
        <!-- <Quota> -->
    <div class="card">
      <div class="row">
        <h2>Quota</h2>
      </div>
      <div class="row border-up">
        <div class="quota-total">

          <div class="quota-used" style="width:<?php echo $quota_used_prct; ?>%;"><span><?php echo $quota_used; ?> Ko</span></div>
        </div>
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
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
    <link rel="stylesheet" media="screen" href="'.PUBLIC_ROOT.'css/modalboxes.css?v='.VERSION_CSS.'" type="text/css" />
    <script src="'.PUBLIC_ROOT.'js/modalboxes.js?v='.VERSION_JS.'"></script>
    <script src="'.PUBLIC_ROOT.'js/jquery.1.10.1.min.js?v='.VERSION_JS.'"></script>';
?>

</head> 
<body>
<!-- modalboxes -->
  <div class="modal_layer_bg" id="modal-layer-bg">
    <!-- Modal box - Delete album -->
    <div class="modal_box" id="mb-del-album">
      <h3 class="mb-title" id="mb-del-album-title"></h3>
      <p id="mb-del-album-desc"></p>
      <div class="btn_wraper">
        <a href="#" class="button gray" onClick="CancelDialog();">Annuler</a>
        <a href="#" class="button red" id="mb-del-album-okbtn">Continuer</a>
      </div>
    </div>
    <!-- Modal box - Delete album -->
    <!-- Modal box - Album card -->
    <div class="modal_box" id="mb-album">
      <h3 class="mb-title" id="mb-album-title"></h3>
      <ul class="album-tools">
        <li><a class="tool-settings" id="mb-album-settings" href="#">Parametrage</a></li>
        <li><a class="tool-view" id="mb-album-view" href="#">Aller &agrave; l'album</a></li>
        <li><a class="tool-ranking" id="mb-album-ranking" href="#">Voir votes</a></li>
        <li><a class="tool-logs" id="mb-album-logs" href="#">Aller aux journaux</a></li>
        <li><a class="tool-trash" id="mb-album-trash" href="#">Envoyer &agrave; la corbeille</a></li>
        <li><a class="tool-delete" id="mb-album-delete" href="#">Suprimer l'album</a></li>
      </ul>
<br />
      <div class="btn_wraper">
        <a href="#" class="button gray" onClick="HideModalBoxes('mb-album');">Retour</a>
      </div>
    </div>
    <!-- /Modal box - Album card -->
  </div>
<!-- /modalboxes -->
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
      <ul class="row border-up content">
          <li><a class="link" href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR; ?>manageAlbum.php">Cr&eacute;er nouvel album</a></li>

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
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre
      
        echo '<li><a id="list-'.$fname.'" href="#" class="album-name" onclick="javascript:ShowCard(\'mb-album\', \''.$fname.'\');">'.$AL_CONF['albumname'].'<span class="nphotos">'.$nphotos.'</span></a></li>';

/*
        echo '<li style="display:none;" id="ddwn-'.$ddc.'">';
        echo '<ul class="album-tools">';
          echo '<li><a class="tool-settings" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'" title="Parametrage">Parametrage</a></li>';
          echo '<li><a class="tool-view" href="http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$fname.'" title="Regarder album">Regarder album</a></li>';
          echo '<li><a class="tool-ranking" href="'.PUBLIC_ROOT.ADMIN_DIR.'stats.php?'.URI_QUERY_ALBUM.'='.$fname.'" title="Voir votes">Votes</a></li>';
          echo '<li><a class="tool-logs" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_ALBUM.'='.$fname.'" title="Voir journaux">Journaux</a></li>';
          echo '<li><a class="tool-trash" href="#" title="Envoyer &aacute; la corbeille">Envoyer &agrave; la corbeille</a></li>';
          echo '<li><a class="tool-delete" href="#" title="Suprimer album">Suprimer</a></li>';

          #echo '<li><a class="tool-trash" href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=totrash">Envoyer &agrave; la corbeille</a></li>';
          #echo '<li><a class="tool-delete" href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=delete">Suprimer </a></li>';

        echo '</ul>';
      echo '</li></ul>';
*/
    }
  }
  
?>
      
      </ul>
    </div>
    <script type="text/javascript">      
      function ShowCard(mboxID, codalbum){
        $("#mb-album-title").text( $("#list-"+codalbum).text() );
        $("#mb-album-settings").attr("href", "<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM ?>="+codalbum);
        $("#mb-album-view").attr("href", "<?php echo 'http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR ?>"+codalbum);
        $("#mb-album-ranking").attr("href", "<?php echo PUBLIC_ROOT.ADMIN_DIR.'stats.php?'.URI_QUERY_ALBUM ?>="+codalbum);
        $("#mb-album-logs").attr("href", "<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_ALBUM ?>="+codalbum);
        $("#mb-album-trash").attr("onclick","javascript:DeleteDialog('<?php echo $fname; ?>', true);");
        $("#mb-album-delete").attr("onclick","javascript:DeleteDialog('<?php echo $fname; ?>', false);");

        ShowModalBox(mboxID);
      }
      
      function CancelDialog(){
        HideModalBoxes('mb-del-album');
        ShowModalBox('mb-album');
      }
      
      function DeleteDialog(codalbum, toTrash){
        $("#mb-del-album-title").text( $("#mb-album-title").text() );
        if(toTrash==false){
          $("#mb-del-album-desc").html( "L'album sera supprim&eacute; &agrave; jamais !" );
          $("#mb-del-album-okbtn").attr("href", "<?php echo PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ACTION.'=delete&'.URI_QUERY_ALBUM.'=';  ?>"+codalbum);
        }else{
          $("#mb-del-album-desc").html( "L'album sera transfer&eacute; &agrave; la corbeille.<br />Vous pourrez le recuperer plus tard." );
          $("#mb-del-album-okbtn").attr("href", "<?php echo PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ACTION.'=totrash&'.URI_QUERY_ALBUM.'=';  ?>"+codalbum);
        }
        HideModalBoxes('mb-album');
        ShowModalBox('mb-del-album');
      }
    </script>
    <!-- </Albums> -->

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
          <li><a class="link" href="'.PUBLIC_ROOT.ADMIN_DIR.RUN_DIR.'quota_recalc.php">Recalculer quota</a></li>
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
      <ul class="row border-up">
        <li><a class="link" href="<?php echo FORMS_DIR; ?>mypassword.php">Changer mot de passe</a></li>
<?php
      if($_SESSION['ULEVEL']==USER_LEVEL_SUPERADMIN){
        echo '
        <li class="disabled">Nouveau administrateur</li>
        <li class="disabled">Gerer administrateurs</li>'."\n";
      }
?>
      </ul>
    </div>
    <!-- </Administrateur> -->

<?php
      if($_SESSION['ULEVEL']==USER_LEVEL_SUPERADMIN){
        echo '
    <!-- <Instapush> -->
    <div class="card">
      <div class="row">
        <h2>Instapush</h2>
      </div>
      <ul class="row border-up">
        <li><a class="link" href="'.FORMS_DIR.'pushNotifications.php">Mes notifications</a></li>
        <li><a class="link" href="'.RUN_DIR.'pushNotifications.php?'.URI_QUERY_ACTION.'=test&amp;UID='.$_SESSION['UID'].'">Lancer un test</a></li>
      </ul>
    </div>
    <!-- </Instapush> -->
    
    <!-- <Flickr> -->
    <div class="card">
      <div class="row">
        <h2>Flickr</h2>
      </div>
        <ul class="row border-up">
          <li><a class="link" href="'.FORMS_DIR.'flickrauth.php">Administrer token</a></li>
        </ul>
    </div>
    <!-- </Flickr> -->'."\n";
      }
?>
    
    <!-- <Journal> -->
    <div class="card">
      <div class="row">
        <h2>Journaux</h2>
      </div>
      <ul class="row border-up content">
        <li><a class="link" href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_JOURNAL.'=events.log'; ?>">Journaux de syst&egrave;me</a></li>
        <li><a class="link" href="<?php echo PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_JOURNAL.'=albums.log'; ?>">Journaux d'albums</a></li>
      </ul>
    </div>
    <!-- </Journal> -->
  </div>
<!-- </panel> -->
</body>
</html>
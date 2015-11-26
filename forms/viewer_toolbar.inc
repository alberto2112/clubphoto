<?php
  require_once __DIR__.'/../settings.php';

  echo '
<!-- Toolbar -->
    <div class="toolbar">
      <ul>';

  if($_ISADMIN){
    echo '
        <li class="tb-delete-w"><a href="#" title="Suprimer photos s&eacute;lection&eacute;s" onclick="ShowModalBox(\'mb-del-selct\')">Delete</a></li>';
  }

  if(!empty($USER_SESSION) && file_exists(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/'.PROC_DIR.$USER_SESSION)){    
    if(filesize(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/'.PROC_DIR.$USER_SESSION)>4){
      echo '
        <li class="tb-myuploads-w"><a href="'.PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Mes t&eacute;l&eacute;chargements">My uploads</a></li>';
    }
  }
    echo '
        <li><a href="#" class="tb-dots-w" id="tgl-toolbar">More</a>
          <ul id="sub-toolbar" class="submenu_ico">
            <li class="tb-myrates-g disabled"><a href="'.PUBLIC_ROOT.FORMS_DIR.'myrates.php?'.URI_QUERY_ACTION.'=view&amp;'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Gerer mes votes">Mes &eacute;valuations</a></li>
            <li class="tb-clonesession-g"><a href="'.PUBLIC_ROOT.FORMS_DIR.'clonesession.php?'.URI_QUERY_ACTION.'=request&amp;'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Cloner session">Clonner sesion</a></li>';

  if(!$_ISADMIN){
    echo '
            <li class="tb-settings-g"><a href="'.PUBLIC_ROOT.'login.php" title="Administrer">Administrer</a></li>';
    if($_SHOWRANKING){
      echo '
            <li class="tb-stats-g"><a href="'.PUBLIC_ROOT.'classement.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Voir classement">Classement</a></li>';
    }
  }else{
    echo '
            <li class="tb-stats-g"><a href="'.PUBLIC_ROOT.ADMIN_DIR.'stats.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Voir classement">Classement</a></li>
            <li class="tb-recycle-g disabled"><a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'trash.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Voir corbeille">Corbeille (0)</a></li>
            <li class="tb-settings-g"><a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'" title="Editer album">Param&egrave;tres</a></li>
            <li class="tb-sendto-g disabled"><a href="#" title="Transf&eacute;rer photos s&eacute;lection&eacute;s vers ..." onclick="ShowModalBox(\'mb-snd-selct\')">Transf&eacute;rer vers ...</a></li>';
  }

  echo '
          </ul>
        </li>
      </ul>
    </div>';
?>

<script>

  $( "#tgl-toolbar" ).click(function() {
    $( "#sub-toolbar" ).toggle();
  });
</script>

<!-- / Toolbar -->
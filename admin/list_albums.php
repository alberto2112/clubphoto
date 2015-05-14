<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';

    // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit();
  }

  include SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  
?>
<html>
  <head>
    <style>
      table{
        width:75%;
        margin:0 auto;
      }
      
      td{
        padding:.5em;
      }
      
      table,td{
        border:1px solid #333;
      }
      
    </style>
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Album</th>
          <th>N. photos</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
<?php
  foreach(glob(SYSTEM_ROOT.ALBUMS_DIR.'*', GLOB_ONLYDIR) as $folder){
    if($folder!='.' && $folder!='..'){
      $fname = basename($folder);
      $nphotos = count_files(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/photos/thumbs','*.jpg');
      
      if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php')===true)
        $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$fname.'/config.php';
      else
        $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre
      
      echo '<tr>';
        echo '<td align="center"><a href="http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$fname.'" title="Voir album">'.$fname.'</a></td>';
        echo '<td>'.$AL_CONF['albumname'].'</td>';
        echo '<td align="center">'.$nphotos.'</td>';
        echo '<td align="center">';
          echo '<a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'journal.php?'.URI_QUERY_ALBUM.'='.$fname.'">Journaux</a>&nbsp;|&nbsp;';
          echo '<a href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'">Parametrage</a>&nbsp;|&nbsp;';
          echo '<a href="'.PUBLIC_ROOT.ADMIN_DIR.'stats.php?'.URI_QUERY_ALBUM.'='.$fname.'">Voir votes</a>&nbsp;|&nbsp;';
          echo '<a href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=totrash">Envoyer &agrave; la corbeille</a>&nbsp;|&nbsp;';
          echo 'Suprimer <a href="'.PUBLIC_ROOT.ADMIN_DIR.'manageAlbum.php?'.URI_QUERY_ALBUM.'='.$fname.'&amp;'.URI_QUERY_ACTION.'=delete">[X]</a>';
        echo '</td>';
      echo '</tr>';
    }
  }
?>
      </tbody>
    </table>
  </body>
</html>
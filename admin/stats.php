<?php
  if(!defined('SYSTEM_ROOT'))
    include(__DIR__.'/../settings.php');

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit();
  }

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $ALBUM_ROOT = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';

  if(is_readable($ALBUM_ROOT.'votes')){
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php';
    
  // Load album config
    if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true)
      $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
    
?>
<html>
  <head>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/stupidtable.min.js"></script>
    <style>
      table{
        width:75%;
        margin:0 auto;
      }
      
      th[data-sort]{
        cursor:pointer;
        color:#777;
      }
      
      td{
        padding:.5em;
        text-align:center;
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
          <th>Miniature</th>
          <th>Auteur</th>
          <th data-sort="int">Votes</th>
<?php
    if($AL_CONF['ratemethod']=='stars'){
      echo '
          <th data-sort="int">Points</th>
          <th>Moyenne</th>
          ';
    }
?>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
<?php
    foreach(glob($ALBUM_ROOT.'votes/*') as $file){
      if($file!='.' && $file!='..'){
        if(substr($file,-7)=='jpg.txt'){
          $votes_fname = $file;
          $thumb_fname = substr($file, strrpos($file,DIRECTORY_SEPARATOR)+1,-4);
          $points_fname = $ALBUM_ROOT.'votes/'.$thumb_fname.'.pts.txt';
          //$thumb_fname = PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$thumb_fname;
          $votes = filesize($votes_fname);
          $points = filesize($points_fname);

          // Leer fichero $photo_filename.csv
          $photo_info = read_csv($ALBUM_ROOT.'photos/'.$thumb_fname.'.csv');

          echo '<tr>';
            echo '<td><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$thumb_fname.'" alt="'.$thumb_fname.'" /></td>';
            echo '<td>'.$photo_info[AUTHOR].'</td>';
            echo '<td>'.$votes.'</td>';
            if($AL_CONF['ratemethod']=='stars'){
              echo '<td>'.$points.'</td>';
              echo '<td>'.round($points / $votes, 1).'/5</td>';
            }
            echo '<td><a href="download.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$thumb_fname.'">T&eacute;l&eacute;charger</a></td>';
          echo '</tr>';
        }
      }
    }
  ?>
      </tbody>
    </table>
    <script>
      $(function(){
          $("table").stupidtable();
      });
    </script>
  </body>
</html>
<?php
  }else{
    //TODO: Afficher méssage d'erreur 
  }
?>
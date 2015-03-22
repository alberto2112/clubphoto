<?php
  if(!defined('SYSTEM_ROOT'))
    include(__DIR__.'/../settings.php');

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';

//==============================
  function print_table_header($ratemethod){
    echo '
<table>
      <thead>
        <tr>
          <th>Miniature</th>
          <th>Auteur</th>
          <th data-sort="int">Votes</th>';

    if($ratemethod=='stars'){
      echo '
          <th data-sort="int">Points</th>
          <th>Moyenne</th>
          ';
    }

    echo '
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>';
  }
//-------------------------------
  function print_table_footer(){
    echo '
      </tbody>
    </table>';
  }
//==============================

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
  $action   = clear_request_param(getRequest_param(URI_QUERY_ACTION, 'defsort'), 'a-z', 8, false);
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
<?php
    if($action=='defsort'){
      print_table_header($AL_CONF['ratemethod']);
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
      print_table_footer();
    }elseif($action=='group'){
      $LoP = array();
      
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
            if(!array_key_exists($photo_info[UPLOADERID], $LoP)){
              $LoP[$photo_info[UPLOADERID]] = array();
            }
            array_push($LoP[$photo_info[UPLOADERID]], array($thumb_fname, $votes, $points, $photo_info[AUTHOR]));
            
          }
        }
      }
      
      foreach($LoP as $uploader){
        
        print_table_header($AL_CONF['ratemethod']);
        foreach($uploader as $photo){
          echo '<tr>';
            echo '<td><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" alt="'.$photo[0].'" /></td>';
            echo '<td>'.$photo[3].'</td>';
            echo '<td>'.$photo[1].'</td>';
            if($AL_CONF['ratemethod']=='stars'){
              echo '<td>'.$photo[2].'</td>';
              echo '<td>'.round($photo[2] / $photo[1], 1).'/5</td>';
            }
            echo '<td><a href="download.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'">T&eacute;l&eacute;charger</a></td>';
          echo '</tr>';
        }
      }
      print_table_footer();
    }
  ?>

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
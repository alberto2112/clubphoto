<?php
  if(!defined('SYSTEM_ROOT'))
    include(__DIR__.'/../settings.php');

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';
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
          <th data-sort="int" data-sort-default="desc">Points</th>
          <th>Moyenne</th>
          ';
    }

    echo '
          <th>Choisir</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>'."\n";
  }
//-----------------------------------------------------
  function print_row($codalbum, &$photo, $nline, $ratemethod='stars', $selected=false){
    /**
     * @param $codalbum
     * @param $photo (Array)
     * @param $nline (Integer)
     */
    
      $votes = $photo[1];//filesize($votes_fname);
      $points = $photo[2]; //filesize($points_fname);
    
      echo '<tr>';
        echo '<td><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" alt="'.$photo[0].'" /></a></td>';
        echo '<td>'.$photo[3].'</td>';
        echo '<td>'.$votes.'</td>';
        if($ratemethod=='stars'){
          echo '<td>'.$points.'</td>';
          echo '<td>'.round($points / $votes, 1).'/5</td>';
        }
        echo '<td><input type="checkbox" id="p['.$nline.']" name="p['.$nline.']" value="'.$photo[0].'" onclick="javascript:countChecked();" '.(($selected)?'checked="checked" ':'').'/></td>';
        echo '<td><a href="download.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'">T&eacute;l&eacute;charger</a></td>';
      echo '</tr>'."\n";
  }
//-----------------------------------------------------
  function print_table_footer(){
    echo '
      </tbody>
    </table><br />&nbsp;<br />';
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
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php';
    
  // Load album config
    if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true)
      $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
    
  // Make sorted list of photos
    $i=0;
    $LoP = array();
    $aPoints = array();
    $aVotes = array();
    
    foreach(glob($ALBUM_ROOT.'votes/*') as $file){
      if($file!='.' && $file!='..'){
        if(substr($file,-7)=='jpg.txt'){
          $votes_fname = $file;
          $thumb_fname = substr($file, strrpos($file,DIRECTORY_SEPARATOR)+1,-4);
          if(file_exists($ALBUM_ROOT.'photos/thumbs/'.$thumb_fname)){
            $points_fname = $ALBUM_ROOT.'votes/'.$thumb_fname.'.pts.txt';
            //$thumb_fname = PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$thumb_fname;
            $votes = filesize($votes_fname);
            $points = filesize($points_fname);

            // Leer fichero $photo_filename.csv
            //$photo_info = read_csv($ALBUM_ROOT.'photos/'.$thumb_fname.'.csv');
            $LoP[] = array($thumb_fname, $votes, $points);
            $aPoints[] = $points;
            $aVotes[] = $votes;
          }
        }
      }
    }
    
    // Sort array of photos
    array_multisort($aPoints, SORT_DESC, $aVotes, SORT_ASC, $LoP);
    
?>
<html>
  <head>
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/buttons.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/ranking.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/modalboxes.css?v='.VERSION_CSS; ?>" type="text/css" />
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/stupidtable.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/modalboxes.js"></script>
    <style>
      body{
        padding-top:2.75em;
      }
      
      table{
        width:75%;
        margin:6px auto;
        background-color:#fff;
      }
      
      th{
        color:#000;
        font-weight:100;
      }
      
      th[data-sort]{
        cursor:pointer;
        color:#559;
        font-weight:700;
      }
      
      td{
        padding:.5em;
        text-align:center;
      }
      
      table,td{
        border:1px solid #333;
      }
      .selection{
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:2em;
        background-color:#555;
      }
      .selection p{
        margin:0;
        padding:.25em 0 .3em .5em;
        font-size:85%;
        color:#aaa;
      }
      
      .selection a{
        color:#aaa;
      }
    </style>
  </head>
  <body>
<!-- Modal boxes -->
    <div class="modal_layer_bg" id="modal-layer-bg">
      <!-- Modal box - Photos to flickr -->
      <div class="modal_box" id="mb-snd-flickr">
        <p>Envoyer les photos s&eacute;lection&eacute;es &agrave; Flickr</p>
        <div class="btn_wraper">
          <a href="#" class="button gray" onClick="HideModalBoxes('mb-snd-flickr');">Annuler</a>
          <a href="#" class="button red" onClick="sndSelectedPhotos();">Continuer</a>
        </div>
      </div>
      <!-- Modal box - Photos to flickr -->
    </div>
<!-- / Modal boxes -->
    <div class="selection"><p>Photos s&eacute;lection&eacute;es: <span id="chkbox_counter">0</span> <a href="#" onclick="javascript:ShowModalBox('mb-snd-flickr')" class="button flickr">Les envoyer &agrave; flickr</a></p></div>
<?php
    echo '<div class="button-wrapper at-center">';
      if($action=='defsort'){
        echo '<a class="blue" href="?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=group">Grouper photos</a>';
        echo '<a class="disabled" href="#">Ne pas grouper photos</a>';
      }else{
        echo '<a class="disabled" href="#">Grouper photos</a>';
        echo '<a class="blue" href="?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=defsort">Ne pas grouper photos</a>';
      }
    echo '</div>';
    
    $i=0;
    if($action=='defsort'){
      print_table_header($AL_CONF['ratemethod']);
      foreach($LoP as $photo){
            

          // Leer fichero $photo_filename.csv
          $photo_info = read_csv($ALBUM_ROOT.'photos/'.$photo[0].'.csv');

          $i++;
          $photo[3]=$photo_info[AUTHOR];
        
          print_row($codalbum, $photo, $i, $AL_CONF['ratemethod'], ($i<16));
      }
      print_table_footer();
    }elseif($action=='group'){
      
// GROUPING UPLOADERS
      $g_LoP = array();
      //TODO: Add groupping methods: by PHOTOGRAPHE_UKEY | by UPLOADERID
      $idx2group=PHOTOGRAPHE_UKEY;
      //$idx2group=UPLOADERID;

      // Create grouped photos array
      foreach($LoP as $photo){

        $votes = $photo[1];
        $points = $photo[2];

        // Leer fichero $photo_filename.csv
        $photo_info = read_csv($ALBUM_ROOT.'photos/'.$photo[0].'.csv');

        if(!array_key_exists($photo_info[$idx2group], $g_LoP)){
          $g_LoP[$photo_info[$idx2group]] = array();
        }
        array_push($g_LoP[$photo_info[$idx2group]], array($photo[0], $votes, $points, $photo_info[AUTHOR]));
      }
      // Render array
      foreach($g_LoP as $uploader){
        
        print_table_header($AL_CONF['ratemethod']);
        $i++;
        $j=0;
        foreach($uploader as $photo){

          $j++;
        
          print_row($codalbum, $photo, 'g'.$i.'p'.$j, $AL_CONF['ratemethod'], ($j==1));
/*
          echo '<tr>';
            echo '<td><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$photo[0].'" alt="'.$photo[0].'" /></a></td>';
            echo '<td>'.$photo[3].'</td>';
            echo '<td>'.$photo[1].'</td>';
            if($AL_CONF['ratemethod']=='stars'){
              echo '<td>'.$photo[2].'</td>';
              echo '<td>'.round($photo[2] / $photo[1], 1).'/5</td>';
            }
            echo '<td><a href="#">Choisir</a><br /><a href="download.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo[0].'">T&eacute;l&eacute;charger</a></td>';
          echo '</tr>';
*/
        }
        print_table_footer();
      }
    }
  ?>

    <script>
      $(function(){
          $("table").stupidtable();
      });
      
      function countChecked(){
        $("#chkbox_counter").text($("input[type=checkbox]:checked").length) ;
      }
      
      function sndSelectedPhotos(){
        $('#mb-snd-flickr').html("<p>Envoi des photos en cours...</p>");
        
        var selectedPhotos = $("input:checkbox:checked").map(function() {
              return this.value;
            }).get();
        
        //alert( selectedPhotos );
        document.location.href="<?php echo PUBLIC_ROOT.ADMIN_DIR.RUN_DIR.'flickr.php?'.URI_QUERY_ACTION.'=send&'.URI_QUERY_ALBUM.'='.$codalbum.'&'.URI_QUERY_PHOTO.'='  ?>"+selectedPhotos ;
      }
    </script>
  </body>
</html>
<?php
  }else{
    //TODO: Afficher méssage d'erreur 
  }
?>
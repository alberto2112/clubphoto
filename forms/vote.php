<?php
/** INFOS IMPORTANTES
  *  - Cet fichier est appelle par un autre dont son emplacement
  *    est SYSTEM_ROOT.ALBUMS_DIR.code_de_album."/index.php"
  *    celui-ci declare la variable $_CODALBUM
  **/

  if(!defined('SYSTEM_ROOT')) 
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'photo.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'rate.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  

  //Get $codalbum
  $codalbum       = clear_request_param(getRequest_param(URI_QUERY_ALBUM,''), 'a-zA-Z0-9', 8, false);

  // Ne rien faire s'il n'y a pas de codalbum
  if(empty($codalbum))
    exit;

  //Get n clear request vars
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO,''), 'a-zA-Z0-9\.', 42, false);
  $str_cookie     = $codalbum.'_'.str_replace('.','_',$photo_filename);
  $AL_CONF        = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre
  $RIGHTS_KEY     = get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY);
  $USER_SESSION   = 'NotAMember';
  $_CAN_RATE      = false;
  $_HAS_RATED     = false;
  $_RATED_POINTS  = '';
  $_ISADMIN       = is_admin();
  $_IS_AUTHOR     = false;
  $str_message    = '';
  //$votes_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.txt';
  $comments_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.cmts.csv';


  // Leer fichero $photo_filename.csv
  $photo_info = read_csv(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.csv');

  // Init sensible vars
  $photo_info[DESCRIPTION] = $photo_info[TITLE] = '';

  // Load photo label
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt')){
    $photo_info[TITLE] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt', false, null, -1, 128); // Limited to 128 chars
  }else{
    $photo_info[TITLE] = $photo_filename;
  }

  // Load photo description
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt')){
    $photo_info[DESCRIPTION] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt', false, null, -1, 512); // Limited to 512 chars
  }

  // Lire fichier de configuation de l'album
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';

//===========================================================================
    // Get RIGHTS_KEY or cookie RIGHTS_KEY
    if(empty($RIGHTS_KEY) || !array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || ($RIGHTS_KEY != get_arr_value($AL_CONF, 'RKEY')) ){
      // Si la cookie n'existe pas et il n'apporte pas le RIGHT_KEY nier les droits de voter
      $_CAN_RATE = false;
    }else{
      // RIGHT_KEY a ete trouve
      $_CAN_RATE = ( get_arr_value($AL_CONF, 'allowvotes')=='1' );
      // Renouveler temps de vie du cookie
      setcookie(COOKIE_RIGHTS_KEY, $RIGHTS_KEY, time() + SESSION_LIFE_RKEY, PUBLIC_ROOT);
      // Recuperer session
      $USER_SESSION   = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$codalbum, make_rkey(14,'012345679VWXYZ'));
      // Refresh/Create USER_SESSION cookie
      setcookie(COOKIE_USER_SESSION.$codalbum, $USER_SESSION, time() + SESSION_LIFE_MEMBER, PUBLIC_ROOT); //Cookie for X Days
    }

    if($_CAN_RATE){
      $_IS_AUTHOR    = is_author($photo_filename, SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION);
      // Determiner si le membre a deja vote
      $_RATED_POINTS = get_rate_for($photo_filename, SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION.'.rates');
      $_HAS_RATED    = ($_RATED_POINTS > 0);
      $str_message   = ($_IS_AUTHOR) ? 'Vous &ecirc;tes le proprietaire de cette photo.' : '';
/*
      if(file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION.'.rates')){
        foreach(file(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION.'.rates', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line){
          $item = explode(';',$line,3);
          if($item[2]==$photo_filename){
            $_HAS_RATED    = true;
            $_RATED_POINTS = $item[1];
          }
        }
      }
*/
      
    // Anti triche
      if($_HAS_RATED && $AL_CONF['antitriche']=='1'){
        $_CAN_RATE    = false;
        $str_message  = 'Vous avez d&eacute;j&agrave attribu&eacute; '.$_RATED_POINTS.' points &agrave; cette photo.';
      }else{
      // Periode de votations
        $ood_result = out_of_date($AL_CONF['vote-from'], $AL_CONF['vote-to'], true);
        $_CAN_RATE  = ($ood_result=='0');
        
        if($ood_result==1){
          // Periode terminee
          $str_message = 'La p&eacute;riode de votes est termin&eacute; le '.$AL_CONF['vote-to'];
        }elseif($ood_result==-1){
          // La periode n'a pas commence
          $str_message = 'La p&eacute;riode de votes d&eacute;bute le '.$AL_CONF['vote-from'];
        }
      }
    
    
    // Self rating
      if($_CAN_RATE && $AL_CONF['allowselfrating']=='0'){
          // Determiner si le membre est l'auteur de la photo
//          $_IS_AUTHOR = is_author($photo_filename, SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION);
          $_CAN_RATE  = !$_IS_AUTHOR;
  /*
          if(file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION)){
            $_IS_AUTHOR = in_array(
              $photo_filename, 
              file(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
              true
            );

            $_CAN_RATE = !$_IS_AUTHOR;
          }
  */
      }
  } //EOC >> if($_CAN_RATE)
//===========================================================================

  }else{
    //$str_message = 'Le fichier de parametrage n\'existe pas: '.SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_def.config.php';
    $AL_CONF['allowvotes'] ='0';
  }
// ------------------------------------
  
?>
<!DOCTYPE html>
<html>
  <head>
    <title>&Eacute;valuation: <?php echo get_arr_value($AL_CONF, 'albumname'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php

// Make twitter metatags
  echo '    <meta name="twitter:card" content="photo">
    <meta name="twitter:url" content="http://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$photo_filename.'&'.URI_QUERY_ALBUM.'='.$codalbum.'">
    <meta name="twitter:title" content="'.get_arr_value($AL_CONF, 'albumname').' &gt; '.$photo_info[TITLE].'">
    <meta name="twitter:description" content="'.$photo_info[DESCRIPTION].'">
    <meta name="twitter:image" content="http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/medium/'.$photo_filename.'">'."\n";
?>
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/vote.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/vote_sizes.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />

    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
  </head>
  <body>
<!-- Header -->
    <div class="header">
        <h1><a href="<?php echo PUBLIC_ROOT.ALBUMS_DIR.$codalbum; ?>"><?php echo get_arr_value($AL_CONF, 'albumname'); ?></a> <span>&gt; <?php echo $photo_info[TITLE]; ?></span></h1>
    </div>
<!-- /Header -->
    
<!-- Photo -->
    <div class="photo-wrapper" style="background-image: url('<?php echo PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo_filename; ?>')">
      <img src="<?php echo PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/photos/large/'.$photo_filename; ?>" class="handheld" />
    </div>
<!-- /Photo -->

<!-- Navigation -->
    <div class="nav">
<?php

  // Calculer nom de la photo précedante et suivante
  $photo_precedente = '';
  $photo_suivante = '';
  $last_photo = '-1';
  foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/','*.jpg',true) as $file){
    if($photo_precedente!='' && $photo_suivante==''){
      $photo_suivante = $file;
      break; // Sortir du boucle. Mauvaise practique!
    }

    if($file == $photo_filename)
      $photo_precedente = $last_photo;

    $last_photo = $file;
  }
  
  echo '<div class="button-wrapper">';
  if($photo_precedente=='' || $photo_precedente=='-1'){
    echo '<span class="disabled">Photo pr&eacute;c&eacute;dente</span>'."\n";
  }else{
    echo '<a class="blue" href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$photo_precedente.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'">Photo pr&eacute;c&eacute;dente</a>'."\n";
  }

  if($photo_suivante==''){
    echo '<span class="disabled">Photo suivante</span>'."\n";
  }else{
    echo '<a class="blue" href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$photo_suivante.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'">Photo suivante</a>'."\n";
  }
  echo '</div>';
?>
    </div>
<!-- /Navigation -->

<!-- Info Photo -->
    <div class="photo-info">
      <h2>Informations</h2>
      <ul>
<?php

    if( ($_CAN_RATE && $AL_CONF['hidecammodelonrate']!='1') || !$_CAN_RATE){
      echo '
        <li class="camera">
          <span title="Boitier">'.$photo_info[MODEL].'</span>
        </li>'."\n";
    }
?>
        <li class="aperture">
          <span title="Ouverture"><?php echo $photo_info[OUVERTURE]; ?></span>
        </li>
        <li class="expo">
          <span title="Exposition"><?php echo $photo_info[EXPO]; ?></span>
        </li>
        <li class="iso">
          <span title="ISO"><?php echo $photo_info[ISO]; ?></span>
        </li>
        <li class="focal">
          <span title="Focal35: <?php echo $photo_info[FOCAL35]; ?>"><?php echo $photo_info[FOCAL]; ?></span>
        </li>
        <li class="expobias">
          <span title="Exposure compensation"><?php echo $photo_info[EXBIAS]; ?></span>
        </li>
        <li class="flash">
          <span title="Flash"><?php echo $photo_info[FLASH]; ?></span>
        </li>
        
<?php
  //<li><span id="showrules">Show rules</span></li>
  
  if(strpos(get_arr_value($AL_CONF, 'albumdesc', ''), '[\d]') > 0){
    echo '<li class="download"><a href="'.PUBLIC_ROOT.RUN_DIR.'download.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_PHOTO.'='.$photo_filename.'">T&eacute;l&eacute;charger</a></li>';
  }

?>
      </ul>
    </div>
<!-- / Info Photo -->

<!-- Rating -->
<?php
// Print photo description if exists
  if(!empty($photo_info[DESCRIPTION])){
    echo  '<!-- Desc Photo -->'."\n".'<div class="message"><h2>Description</h2><p>'.$photo_info[DESCRIPTION].'</p></div>'."\n".'<!-- / Desc Photo -->';
  }

//DEBUG
//$AL_CONF['allowvotes']='1';
//$AL_CONF['ratemethod']='likes';
//DEBUG />

// Afficher les messages s'il y en a
  if(!empty($str_message)){
    echo '<div class="message"><h2>Messages</h2><p>'.$str_message.'</p></div>';
  }

// Print vote form
  if($_CAN_RATE){ // Montrer le bouton uniquement si la periode de votes est ouverte
    echo '<div class="vote-form" id="vote-counter"><h2>Votations</h2>';
    if($AL_CONF['ratemethod']=='stars'){
      // Vote par etoiles
      //$href =RUN_DIR. 'vote.php?'.URI_QUERY_PHOTO.'='.$photo_filename.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_RATE_METHOD.'='.$AL_CONF['ratemethod'].'&amp;'.URI_QUERY_POINTS.'=';
      echo '<div class="rating-wrapper rating-stars" id="rating-stars">
        <a href="#" points="5" class="btn-vote">&#9733;</a>
        <a href="#" points="4" class="btn-vote">&#9733;</a>
        <a href="#" points="3" class="btn-vote">&#9733;</a>
        <a href="#" points="2" class="btn-vote">&#9733;</a>
        <a href="#" points="1" class="btn-vote">&#9733;</a>
        </div>';
    }else{
      // Vote par likes
      echo '<input type="checkbox" id="chk-vote" points="1" /><label>Voter pour cette photo</label>';
    }
    
    if($AL_CONF['allowcomments']=='1'){
      echo "\n".'        <textarea placeholder="Vos impressions (factultatif, max 500 chars)" id="comments" maxlength="500"></textarea>';
    }
    
    echo "\n".'          <div class="button-wrapper at-center"><a class="green hidden" id="send-vote" href="'.RUN_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$photo_filename.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_POINTS.'=1">Confirmer vote</a></div>
          </div>';
  }

// Afficher commentaires
  if($_ISADMIN){
    if(is_readable($comments_filename)){
      echo '<div class="comments"><h2>Comentaires</h2><ul>';
      foreach(file($comments_filename) as $comment){
        $c = explode(';', $comment, 4);
        echo '<li><span class="cmt-timestamp">'.$c[0].'</span><span class="cmt">'.$c[3].'</span></li>';
      }
      echo '</ul></div>';
    }
  }
?>

<!-- / Rating -->

    <script type="text/javascript">
        var vote_points=0;
      
        $('.btn-vote').bind('click', function(e){
          e.preventDefault();
          //str_points = $(this).attr('href').substring(9) + '&<?php echo URI_QUERY_ACTION; ?>=ajax-vote';
          vote_points = $(this).attr('points');
          $('#send-vote').removeClass("hidden");
        });
      
       $('#chk-vote').bind('click', function(e){          
         if($(this).prop('checked')){
           vote_points = $(this).attr('points');
           $('#send-vote').removeClass("hidden");
         } else {
           vote_points=0;
           $('#send-vote').addClass("hidden");
         }
        });

        $('#send-vote').bind('click', function(e){
          e.preventDefault();
          //(a-acute=á) (c-cedil=ç) (enne=ñ)
          //var data = $(this).attr('href').substring(9) + '&<?php echo URI_QUERY_ACTION; ?>=ajax-vote';
          if(vote_points > 0){
            //var data = "<?php echo URI_QUERY_COMMENTS.'='; ?>"+$('#comments').val();
            var data = {"<?php echo URI_QUERY_COMMENTS; ?>": $('#comments').val()};
            var urlparams = "<?php echo URI_QUERY_PHOTO.'='.$photo_filename.'&'.URI_QUERY_ALBUM.'='.$codalbum.'&'.URI_QUERY_ACTION.'=ajax-vote&'.URI_QUERY_POINTS.'='; ?>"+vote_points;

            $.ajax({
              type: 'POST',
              dataType: 'text',
              //contentType: 'text/plain; charset=<?php echo CHARSET; ?>',
              //scriptCharset: '<?php echo CHARSET; ?>',
              url: 'http://<?php echo SITE_DOMAIN.PUBLIC_ROOT.RUN_DIR; ?>vote.php?'+urlparams,
              data: data,
              success: function(data) {
                if(data.length < 5){
                  $('#vote-counter').html('<h2>Votations</h2><p>Votre vote a  bien &eacute;t&eacute; enregistr&eacute;. Note attribu&eacute;: '+data+'</p>');
                }else{
                  $('#vote-counter').html('<h2>Votations</h2><p>'+data+'</p>');
                }
              }
            });

          }
          return false;
        });
    </script>
  </body>
</html>
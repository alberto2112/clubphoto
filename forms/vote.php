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
  include_once SYSTEM_ROOT.LIB_DIR.'csv.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';
  include_once SYSTEM_ROOT.ETC_DIR.'photoinfo.csv.conf.php';
  

  //Clear request vars
  $codalbum       = clear_request_param(getRequest_param(URI_QUERY_ALBUM,''), 'a-zA-Z0-9', 8, false);
  $photo_filename = clear_request_param(getRequest_param(URI_QUERY_PHOTO,''), 'a-zA-Z0-9\.', 42, false);
  $str_cookie     = $codalbum.'_'.str_replace('.','_',$photo_filename);
  $AL_CONF        = include SYSTEM_ROOT.ETC_DIR.'clean_album.config.php'; // Charger array de configuration propre
  $USER_RKEY      = get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY);
  $_ISADMIN       = is_admin();
  $str_message    = '';
  //$votes_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.txt';
  $comments_filename = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/votes/'.$photo_filename.'.cmts.csv';

  // Ne rien faire s'il n'y a pas de codalbum
  if(empty($codalbum))
    exit;

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

  // Load photo label
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt')){
    $photo_info[DESCRIPTION] = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt', false, null, -1, 128); // Limited to 128 chars
  }

  // Lire fichier de configuation de l'album
  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';

    // Empecher de voter a toute personne externe au club photo
    
    if(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || ($USER_RKEY != get_arr_value($AL_CONF, 'RKEY')) ){
      $AL_CONF['allowvotes']='0';
    }else{
      // Renouveler temps de vie du cookie
      setcookie(COOKIE_RIGHTS_KEY, $USER_RKEY, time() + 3600, PUBLIC_ROOT); 
    }
    
    // Empecher de voter si:
    if(array_key_exists($str_cookie, $_COOKIE)){
      $sended_points = get_arr_value($_COOKIE, $str_cookie, '0'); // Chercher et lire cookie pour cette photo
      
      if($sended_points=='-1'){
        // Il est le proprietaire de la photo
        // TODO: si c'est configure comme ça
        $str_message = 'Vous &ecirc;tes le proprietaire de cette photo.';
        $AL_CONF['allowvotes']='0';
      }elseif($AL_CONF['antitriche']=='1' && $AL_CONF['allowvotes']=='1'){
        // Il a deja vote et l'antitriche a ete active
        $str_message = 'Vous avez d&eacute;j&agrave; vot&eacute; pour cette photo avec '.$sended_points.' points.';
        $AL_CONF['allowvotes']='0';
      }
    }
    
    if($AL_CONF['allowvotes']=='1'){
      // Calculer droit de vote par raport de la date limite
      $VOTE_FROM = (@array_key_exists('vote-from', $AL_CONF))? explode('/', $AL_CONF['vote-from'],3):false;
      $VOTE_TO   = (@array_key_exists('vote-to', $AL_CONF))? explode('/', $AL_CONF['vote-to'],3):false;

      if(!empty($VOTE_FROM) && time() <= mktime(0,0,0, $VOTE_FROM[1], $VOTE_FROM[0], $VOTE_FROM[2])) // Si la periode n'a pas commence
        {
          $AL_CONF['allowvotes']='0';
          $str_message = 'La p&eacute;riode de votes d&eacute;bute le '.$AL_CONF['vote-from'];
        }
        
      if(!empty($VOTE_TO) && time()-(3600 * 24) >= mktime(0,0,0, $VOTE_TO[1], $VOTE_TO[0], $VOTE_TO[2])) // Si la periode est depasee
        {
          $AL_CONF['allowvotes']='0';
          $str_message = 'La p&eacute;riode de votes est termin&eacute; le '.$AL_CONF['vote-to'];
        }
    }
  }else{
    //$str_message = 'Le fichier de parametrage n\'existe pas: '.SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';
    $AL_CONF['allowvotes'] ='0';
  }
// ------------------------------------
  
?>
<!DOCTYPE html>
<html>
  <head>
    <title>&Eacute;valuation: <?php echo get_arr_value($AL_CONF, 'albumname'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <li class="camera">
          <span title="Boitier"><?php echo $photo_info[MODEL]; ?></span>
        </li>
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

// Print vote form
  if($AL_CONF['allowvotes']=='1'){ // Montrer le bouton uniquement si la periode de votes est ouverte
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
    
    echo "\n".'        <textarea placeholder="Vos impressions (factultatif, max 500 chars)" id="comments" maxlength="500"></textarea>
        <div class="button-wrapper at-center"><a class="green hidden" id="send-vote" href="'.RUN_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$photo_filename.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_POINTS.'=1">Confirmer vote</a></div>
        </div>';
  }

// Afficher les messages s'il y en a
  if(!empty($str_message)){
    echo '<div class="message"><h2>Messages</h2><p>'.$str_message.'</p></div>';
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
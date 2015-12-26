<?php
/** INFOS IMPORTANTES
  *  - Cet fichier est appelle par un autre dont son emplacement
  *    est SYSTEM_ROOT.TRASH_DIR.code_de_album."/index.php"
  *    celui-ci declare la variable $_CODALBUM
  **/

  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../../settings.php';

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
  $RIGHTS_KEY     = clear_request_param(get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY.$codalbum), 'a-zA-Z0-9', 24, false);
  $USER_SESSION   = 'NotAMember';
  $_CAN_RATE      = false;
  $_HAS_RATED     = false;
  $_RATED_POINTS  = '';
  $_ISADMIN       = is_admin();
  $_IS_AUTHOR     = false;
  $str_message    = '';
  //$votes_filename = SYSTEM_ROOT.TRASH_DIR.$codalbum.'/votes/'.$photo_filename.'.txt';
  $comments_filename = SYSTEM_ROOT.TRASH_DIR.$codalbum.'/votes/'.$photo_filename.'.cmts.csv';


  // Leer fichero $photo_filename.csv
  $photo_info = read_csv(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/'.$photo_filename.'.csv');

  // Init sensible vars
  $photo_info[DESCRIPTION] = $photo_info[TITLE] = '';

  // Load photo label
  if(is_readable(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt')){
    $photo_info[TITLE] = file_get_contents(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/'.$photo_filename.'.lbl.txt', false, null, -1, 128); // Limited to 128 chars
  }else{
    $photo_info[TITLE] = $photo_filename;
  }

  // Load photo description
  if(is_readable(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt')){
    $photo_info[DESCRIPTION] = file_get_contents(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/'.$photo_filename.'.dsc.txt', false, null, -1, 512); // Limited to 512 chars
  }

  // Lire fichier de configuation de l'album
  if(@is_readable(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.TRASH_DIR.$codalbum.'/config.php';

  }else{
    //$str_message = 'Le fichier de parametrage n\'existe pas: '.SYSTEM_ROOT.TRASH_DIR.$codalbum.'/config.php';
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

// Write twitter metatags
/*
  echo '    <meta name="twitter:card" content="photo">
    <meta name="twitter:url" content="http://'.SITE_DOMAIN.PUBLIC_ROOT.TRASH_DIR.FORMS_DIR.'tviewphoto.php?'.URI_QUERY_PHOTO.'='.$photo_filename.'&'.URI_QUERY_ALBUM.'='.$codalbum.'">
    <meta name="twitter:title" content="'.get_arr_value($AL_CONF, 'albumname').' &gt; '.$photo_info[TITLE].'">
    <meta name="twitter:description" content="'.$photo_info[DESCRIPTION].'">
    <meta name="twitter:image" content="http://'.SITE_DOMAIN.PUBLIC_ROOT.TRASH_DIR.$codalbum.'/photos/medium/'.$photo_filename.'">'."\n";
*/
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
        <h1><a href="<?php echo PUBLIC_ROOT.ADMIN_DIR.'tviewer.php?'.URI_QUERY_ALBUM.'='.$codalbum; ?>"><?php echo get_arr_value($AL_CONF, 'albumname'); ?></a> <span>&gt; <?php echo $photo_info[TITLE]; ?></span></h1>
    </div>
<!-- /Header -->

<!-- Photo -->
    <div class="photo-wrapper" style="background-image: url('<?php echo PUBLIC_ROOT.TRASH_DIR.$codalbum.'/photos/large/'.$photo_filename; ?>')">
      <img src="<?php echo PUBLIC_ROOT.TRASH_DIR.$codalbum.'/photos/large/'.$photo_filename; ?>" class="handheld" />
    </div>
<!-- /Photo -->

<!-- Navigation -->
    <div class="nav">
<?php

  // Calculer nom de la photo précedante et suivante
  $photo_precedente = '';
  $photo_suivante = '';
  $last_photo = '-1';
  foreach(read_dir(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/photos/thumbs/','*.jpg',true) as $file){
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
    echo '<a class="blue" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'tviewphoto.php?'.URI_QUERY_PHOTO.'='.$photo_precedente.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'">Photo pr&eacute;c&eacute;dente</a>'."\n";
  }

  if($photo_suivante==''){
    echo '<span class="disabled">Photo suivante</span>'."\n";
  }else{
    echo '<a class="blue" href="'.PUBLIC_ROOT.ADMIN_DIR.FORMS_DIR.'tviewphoto.php?'.URI_QUERY_PHOTO.'='.$photo_suivante.'&amp;'.URI_QUERY_ALBUM.'='.$codalbum.'">Photo suivante</a>'."\n";
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
      echo '
        <li class="camera">
          <span title="Boitier">'.$photo_info[MODEL].'</span>
        </li>'."\n";
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


  </body>
</html>

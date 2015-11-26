<?php
/** INFOS IMPORTANTES
  *  - Cet fichier est appelle par un autre dont son emplacement
  *    est SYSTEM_ROOT.ALBUMS_DIR.code_album."/index.php"
  *    celui-ci declare la variable $_CODALBUM
  **/

  if(!defined('SYSTEM_ROOT'))
    require_once __DIR__.'/settings.php';

  if(!isset($_CODALBUM)){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
    exit;
  }

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';

// Voir s'il y a une demande de clonage de sesion et rediriger utilisateur vers le formulaire correspondant
  $IP       = getClient_ip();
  $LONGIP   = @sprintf("%u",ip2long($IP)) | '0';

  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.DIRECTORY_SEPARATOR.PROC_DIR.$LONGIP.'.clnssn')){
  // Il existe bien une demande de clonage de sesion
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'&'.URI_QUERY_ACTION.'=detected');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'&'.URI_QUERY_ACTION.'=detected');
    }
    exit;
  }

  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';

  $AL_CONF  = include SYSTEM_ROOT.ETC_DIR.'album_clean.config.php'; // Charger array de configuration propre
  $RKEY     = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);

  $_ISADMIN     = is_admin();
  $_ISMEMBER    = false;
  $_SHOWRANKING = false;
  $_CAN_UPLOAD  = false;

  $ERROR    = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/logs/error.log');

  if(@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php')===true){
    $AL_CONF = include SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php';

    // Recuperer session
    $USER_SESSION   = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$_CODALBUM, make_rkey(14,'012345679VWXYZ'));
    // Determiner si cette personne es un des adherents
    $_ISMEMBER  = file_exists(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/'.PROC_DIR.$USER_SESSION); //Il a deja eu acces avec le lien privilegie
    $_ISMEMBER += !empty($RKEY) && (get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY) == $RKEY);    //Il a access depuis le lien privilegie envoye par mail (ou autre)
    $_ISMEMBER += array_key_exists(COOKIE_RIGHTS_KEY.$_CODALBUM, $_COOKIE) && (get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY.$_CODALBUM) == get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY));    

    if($_ISMEMBER){
      // Est un adherent: Permettre a cette personne de voter ou telecharger ses photos pendant X heures/jours
      setcookie(COOKIE_RIGHTS_KEY.$_CODALBUM, get_arr_value($AL_CONF, COOKIE_RIGHTS_KEY), time() + SESSION_LIFE_RKEY, PUBLIC_ROOT);
      // Renew/Create USER_SESSION cookie
      setcookie(COOKIE_USER_SESSION.$_CODALBUM, $USER_SESSION, time() + SESSION_LIFE_MEMBER, PUBLIC_ROOT);
    }

    // Determiner droit de telechargement
    if($_ISMEMBER && get_arr_value($AL_CONF,'allowupload',false)=='1')
      $_CAN_UPLOAD = !out_of_date(get_arr_value($AL_CONF,'upload-from',false), get_arr_value($AL_CONF,'upload-to',false));

    // Determiner droit a voir le classement
    if(get_arr_value($AL_CONF,'showranking',false)=='2'){
     // Show ranking always
      $_SHOWRANKING=true;
    }elseif(get_arr_value($AL_CONF,'showranking',false)=='1' && get_arr_value($AL_CONF,'allowvotes',false)=='1'){
      // Show ranking after rating period
      $_SHOWRANKING = (out_of_date(get_arr_value($AL_CONF,'vote-from',false), get_arr_value($AL_CONF,'vote-to',false), true) == 1);
    }
  }else{
    $ERROR->insert('ALBUM CONFIG NOT FOUND AT: '.SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/config.php', true);
    $AL_CONF = include SYSTEM_ROOT.ETC_DIR.'album_def.config.php';
    $AL_CONF['allowupload']='0'; // empecher de telecharger des photos deliberement
  }
?>
<!DOCTYPE html>
<html class="no-js">
  <head>
    <title><?php echo get_arr_value($AL_CONF, 'albumname'); ?> - MJC ClubPhoto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include SYSTEM_ROOT.ETC_DIR.'metatags.html'; ?>
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/reset.css" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/modalboxes.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT; ?>css/viewer.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="all" href="<?php echo PUBLIC_ROOT; ?>css/collagePlus.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="all" href="<?php echo PUBLIC_ROOT; ?>css/collagePlus.transitions.css?v=<?php echo VERSION_CSS; ?>" type="text/css" />

    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.collagePlus.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.removeWhitespace.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.collageCaption.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/fingerprint.js"></script>

    <script type="text/javascript">
      if(!window.navigator.doNotTrack){
        var fgrpt = new Fingerprint({screen_resolution: true}).get();
        $.post(<?php echo '"'.((SYS_HTTPS_AVAILABLE==true)?'https://':'http://').SITE_DOMAIN.PUBLIC_ROOT.RUN_DIR.'fingerprint.php", {'.URI_QUERY_ACTION.':"refresh", '.URI_QUERY_FINGERPRINT.':'; ?>fgrpt});
      }

      $(window).load(function () {
          $(document).ready(function(){
              collage();
              $('.Collage').collageCaption();
          });
      });

      // Here we apply the actual CollagePlus plugin
      function collage() {
          $('.Collage').removeWhitespace().collagePlus(
              {
                  'fadeSpeed'     : 2000,
                  'targetHeight'  : 200,
                  'effect'        : 'effect-2',
                  'direction'     : 'vertical',
                  'allowPartialLastRow':false
              }
          );
      };

      // This is just for the case that the browser window is resized
      var resizeTimer = null;
      $(window).bind('resize', function() {
          // hide all the images until we resize them
          $('.Collage .Image_Wrapper').css("opacity", 0);
          // set a timer to re-apply the plugin
          if (resizeTimer) clearTimeout(resizeTimer);
          resizeTimer = setTimeout(collage, 200);
      });
    </script>
  </head>
  <body>
<!-- Header -->
    <div class="header">
        <h1><a class="header-button ico-home notext" href="<?php echo PUBLIC_ROOT; ?>">Home</a><?php echo get_arr_value($AL_CONF, 'albumname'); ?></h1>

        <div class="album-infos">
          <p><?php echo get_arr_value($AL_CONF, 'albumdesc'); ?></p>
        </div>
    </div>
<!-- / Header -->
<?php

  if($_ISMEMBER || $_ISADMIN){
    // Incluire toolbar s'il est un membre ou un administrateur
    include SYSTEM_ROOT.FORMS_DIR.'viewer_toolbar.inc';

    if($_ISADMIN){
      // Incluire les dialogs modales s'il est un administrateur
      include SYSTEM_ROOT.FORMS_DIR.'viewer_modalboxes.inc';
    }
  }
?>

  <!-- Content -->
  <div class="Collage effect-parent">

<?php
  if($_CAN_UPLOAD){
    echo '
      <div class="Image_Wrapper" data-caption="<u>Ajouter</u> photos">
          <a href="http://'.SITE_DOMAIN.PUBLIC_ROOT.FORMS_DIR.'upload.php?'.URI_QUERY_ALBUM."=$_CODALBUM".'"><img src="'.PUBLIC_ROOT.'images/tile_ajouter_photos.png" /></a>
      </div>';
  }

  if($_SHOWRANKING){
    echo '
      <div class="Image_Wrapper" data-caption="Classement">
          <a href="http://'.SITE_DOMAIN.PUBLIC_ROOT.'classement.php?'.URI_QUERY_ALBUM."=$_CODALBUM".'"><img src="'.PUBLIC_ROOT.'images/tile_stats_album.png" /></a>
      </div>';
  }
/*
      <div class="Image_Wrapper" data-caption="<u>Comparer</u> plusieurs photos">
          <a href="#"><img src="<?php echo PUBLIC_ROOT; ?>images/tile_compare_photos.png" /></a>
      </div>

*/

  // Lire le dossier "thumbs" et composer la gallerie de photos
  if($_ISADMIN){
    $i=0;
    foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/thumbs/','*.jpg',true) as $file)
    {
      $i++;
      echo '<div class="Image_Wrapper"><input type="checkbox" id="c['.$i.']" value="'.$file.'" /><label for="c['.$i.']"></label><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$file.'&'.URI_QUERY_ALBUM.'='.$_CODALBUM.'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/medium/'.$file.'" /></a></div>'."\n";
    }
  }else{
    foreach(read_dir(SYSTEM_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/thumbs/','*.jpg',true) as $file)
    {
      echo '<div class="Image_Wrapper"><a href="'.PUBLIC_ROOT.FORMS_DIR.'vote.php?'.URI_QUERY_PHOTO.'='.$file.'&'.URI_QUERY_ALBUM.'='.$_CODALBUM.'"><img src="'.PUBLIC_ROOT.ALBUMS_DIR.$_CODALBUM.'/photos/medium/'.$file.'" /></a></div>'."\n";
    }
  }

?>
    </div>
<!-- / Content -->


<?php
  include SYSTEM_ROOT.'footer.php';
?>
  </body>
</html>
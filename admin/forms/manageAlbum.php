<?php
    if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../../settings.php';

    include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'login.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'htmlhelper.badlib.php';

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
	$action = 'edit';
	$send_caption = 'Enregistrer';

	if($codalbum===false){
		$action='new';
		$codalbum = tinyURL(5);
		$send_caption = 'Cr&eacute;er album';
        $CONFIG = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php'; // remplir l'array avec des parametres par defaut
	} else{
		$action='edit';

        // Charger les parametres de l'album
        // Si le fichier config.php n'existe pas
        // remplir l'array avec des parametres par defaut
        if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php'))
          $CONFIG = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
        else
          $CONFIG = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';// remplir l'array avec des parametres par defaut
    }

    // Forcer creation de une rights_key
    if(!array_key_exists('RKEY', $CONFIG) || empty($CONFIG['RKEY']))
        $CONFIG['RKEY'] = tinyURL(16,'0123456789abcdefABCDEF');

    // Construir code html parametres formulaire
    $HTML = array(
      'albumname'        =>(get_arr_value($CONFIG,'albumname')!='')?'value="'.$CONFIG['albumname'].'" ':'',
      'watermark'        => getHTML4CheckBoxState( get_arr_value($CONFIG,'watermark') ),
      'allowupload'      => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowupload') ),
      'uploadslimit'     =>'<option selected>'.get_arr_value($CONFIG,'uploadslimit','6').' (actuellement)</option>',
      'allowvotes'       => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowvotes') ),
      'antitriche'       => getHTML4CheckBoxState( get_arr_value($CONFIG,'antitriche') ),
      'ratemethod_likes' => getHTML4CheckBoxState( get_arr_value($CONFIG,'ratemethod'), 'like' ),
      'ratemethod_stars' => getHTML4CheckBoxState( get_arr_value($CONFIG,'ratemethod'), 'stars' ),
      'allowselfrating'  => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowselfrating') ),
      'allowcomments'    => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowcomments') ),
      'showranking_0'      => getHTML4CheckBoxState( get_arr_value($CONFIG,'showranking'),'0' ),
      'showranking_1'      => getHTML4CheckBoxState( get_arr_value($CONFIG,'showranking'),'1' ),
      'showranking_2'      => getHTML4CheckBoxState( get_arr_value($CONFIG,'showranking'),'2' ),
      'showrateforuploads' => getHTML4CheckBoxState( get_arr_value($CONFIG,'showrateforuploads') ),
      'allowphotomanag_0'  => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowphotomanag'),'0' ),
      'allowphotomanag_1'  => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowphotomanag'),'1' ),
      'allowphotomanag_2'  => getHTML4CheckBoxState( get_arr_value($CONFIG,'allowphotomanag'),'2' )
    );
//DEBUG
//echo '<pre>DEBUG';
//print_r($CONFIG);
//echo '</pre>';
//DEBUG />
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="<?php echo PUBLIC_ROOT; ?>css/base.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo PUBLIC_ROOT; ?>css/cpanel_forms.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
    <link href="<?php echo PUBLIC_ROOT; ?>css/jquery-ui.min.css?v=<?php echo VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery.1.10.1.min.js"></script>
    <script src="<?php echo PUBLIC_ROOT; ?>js/jquery-ui.min.js"></script>
  </head>
	<body>
      <div class="panel">
        <form action="<?php echo PUBLIC_ROOT.ADMIN_DIR; ?>manageAlbum.php" method="post" class="basic-grey">
          <input type="hidden" name="<?php echo URI_QUERY_ALBUM; ?>" value="<?php echo $codalbum; ?>" />
          <input type="hidden" name="<?php echo URI_QUERY_ACTION; ?>" value="<?php echo $action; ?>" />
          <input type="hidden" name="<?php echo URI_QUERY_RIGHTS_KEY; ?>" value="<?php echo $CONFIG['RKEY']; ?>" />

          <div class="card">
            <div class="row title red">
              <h1><?php echo ($action=='new')?'Nouvel album':'Modifier album'; ?></h1>
            </div>
            <div class="row">
              <div class="content">
                  <span>Lieu de la sortie:</span>
                  <input id="sortie" type="text" name="albumname" placeholder="Nom du village ou endroit" <?php echo $HTML['albumname']; ?>/>

                  <span>Commentaires:</span>
                  <textarea id="description" name="albumdesc" maxlength="500" placeholder="Description/Notes/Observations"><?php echo get_arr_value($CONFIG,'albumdesc'); ?></textarea>

              </div>
            </div>
          </div>

          <!-- telechargements -->
          <div class="card">
            <div class="row">
              <h2>T&eacute;l&eacute;chargements</h2>
            </div>
            <ul class="row border-up content">
              <li>
                  <input id="allowupload" type="checkbox" name="allowupload" value="1" <?php echo $HTML['allowupload']; ?>/>
                  <span>Permetre aux utilisateurs de poster ses photos.</span>
              </li>
              <li>
                <div class="indent">
                  <span>Du</span>
                  <input id="upload-from" type="text" name="upload-from" value="<?php echo $CONFIG['upload-from']; ?>" class="datepicker" />

                  <span>Au</span>
                  <input id="upload-to" type="text" name="upload-to" value="<?php echo $CONFIG['upload-to']; ?>" class="datepicker" />
                </div>
              </li>
              <li>
                <span>Limite de t&eacute;l&eacute;chargements par personne:</span>
                <select id="uploadslimit" type="text" name="uploadslimit">
                  <?php echo $HTML['uploadslimit']; ?>
                  <option value="2">2</option>
                  <option value="4">4</option>
                  <option value="6">6</option>
                  <option value="8">8</option>
                  <option value="10">10</option>
                  <option value="12">12</option>
                  <option value="14">14</option>
                  <option value="16">16</option>
                </select>
              </li>
              <li class="row border-up">
                <span>Les photographes peuvent supprimer ses photos</span>
                <div class="indent">
                  <span><input type="radio" name="allowphotomanag" value="0" <?php echo $HTML['allowphotomanag_0']; ?>/>Jamais</span><br />
                  <span><input type="radio" name="allowphotomanag" value="1" <?php echo $HTML['allowphotomanag_1']; ?>/>Pendant la p&eacute;riode de t&eacute;l&eacute;chargements</span><br />
                  <span><input type="radio" name="allowphotomanag" value="2" <?php echo $HTML['allowphotomanag_2']; ?>/>&Aacute; tout moment</span>
                </div>
              </li>
            </ul>
          </div>
          <!-- /telechargements -->

          <!-- votes -->
          <div class="card">
            <div class="row border-down">
              <h2>Votations</h2>
            </div>

            <ul class="row border-down content">
              <li>
                  <input id="allowvotes" type="checkbox" name="allowvotes" value="1" <?php echo $HTML['allowvotes']; ?>/>
                  <span>Les membres peuvent voter pour les photos post&eacute;s.</span>
              </li>
              <li>
                <div class="indent">
                  <span class="datepicker">Du</span>
                  <input id="vote-from" type="text" name="vote-from" value="<?php echo $CONFIG['vote-from']; ?>" class="datepicker" />

                  <span class="datepicker">Au</span>
                  <input id="vote-to" type="text" name="vote-to" value="<?php echo $CONFIG['vote-to']; ?>" class="datepicker" />
                </div>
              </li>
              <li>
                  <input id="antitriche" type="checkbox" name="antitriche" value="1" <?php echo $HTML['antitriche']; ?>/>
                  <span>&Eacute;viter que les utilisateurs puissent voter plusieurs fois pour la m&ecirc;me photo.</span>
              </li>
              <li>
                  <input id="allowcomments" type="checkbox" name="allowcomments" value="1" <?php echo $HTML['allowcomments']; ?>/>
                  <span>Les membres peuvent donner son avis lors du vote.</span>
              </li>
              <li>
                  <input id="allowrateview" type="checkbox" name="showrateforuploads" value="1" <?php echo $HTML['showrateforuploads']; ?>/>
                  <span>Les membres peuvent regarder les points attribu&eacute;s &agrave; ses photos en temps r&eacute;el.</span>
              </li>
              <li class="disabled">
                  <input id="allowselfrating" type="checkbox" name="allowselfrating" value="1" <?php echo $HTML['allowselfrating']; ?>/>
                  <span>Les membres peuvent voter pour ses propres photos.</span>
              </li>
              <li>
                  <span>Montrer classement aux membres:</span>
                <div class="indent">
                  <span><input type="radio" name="showranking" value="0" <?php echo $HTML['showranking_0']; ?>/>Jamais</span><br />
                  <span><input type="radio" name="showranking" value="1" <?php echo $HTML['showranking_1']; ?>/>Apr&egrave;s la periode de votes</span><br />
                  <span><input type="radio" name="showranking" value="2" <?php echo $HTML['showranking_2']; ?>/>Tout le temps</span>
                </div>
              </li>
              <li class="row">
                <span>Syst&egrave;me de votes</span>
                <div class="indent">
                  <span><input id="ratemethod-like" type="radio" name="ratemethod" value="like" <?php echo $HTML['ratemethod_likes']; ?>/>Standard.</span><br />
                  <span><input id="ratemethod-stars" type="radio" name="ratemethod" value="stars" <?php echo $HTML['ratemethod_stars']; ?>/>Par &eacute;toiles.</span>
                </div>
              </li>
            </ul>
          </div>
          <!-- votes -->

          <!-- generalites -->
          <div class="card">
            <div class="row">
              <h2>G&eacute;n&eacute;ralit&eacute;es</h2>
            </div>
            <div class="row border-up">
              <div class="content">
                <span class="disabled"><input id="watermark" type="checkbox" name="watermark" value="1" <?php echo $HTML['watermark']; ?>/>
                Apliquer automatiquement une filigrane aux photos post&eacute;s.</span>
              </div>
            </div>
          </div>
          <!-- /generalites -->

          <!-- securite --
          <div class="card">
            <div class="row">
              <h2>Securit&eacute;</h2>
            </div>
            <div class="row">
              <div class="content">
                <span><a href="#" class="link">Administrer cl&eacute;s de s&eacute;cours</a></span>
              </div>
            </div>
            <div class="row border-up">
              <div class="content">
                <span><a href="#" class="link">Calculer un nouveau lien priv&eacute;</a></span>
              </div>
            </div>
          </div>
          !-- securite -->

          <!-- liens -->
          <div class="card">
            <div class="row border-down">
              <h2>Liens</h2>
            </div>
            <ul class="row content">
                <li><span>Lien publique:</span><br /><span class="link"><?php echo 'http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum; ?></span></li>
                <li><span>Lien privil&eacute;gi&eacute;:</span><br /><span class="link"><?php echo 'http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/?'.URI_QUERY_RIGHTS_KEY.'='.$CONFIG['RKEY']; ?></span></li>
            </ul>
          </div>
          <!-- liens -->

          <input type="submit" class="button" value="<?php echo $send_caption ?>" />
      </form>

    </div>
    <script type="text/javascript">
      $(function() {
        $( "#upload-from" ).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat:"dd/mm/yy",
          onClose: function( selectedDate ) {
            $( "#upload-to" ).datepicker( "option", "minDate", selectedDate );
          }
        });
        $( "#upload-to" ).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat:"dd/mm/yy",
          onClose: function( selectedDate ) {
            $( "#upload-from" ).datepicker( "option", "maxDate", selectedDate );
          }
        });
        $( "#vote-from" ).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat:"dd/mm/yy",
          onClose: function( selectedDate ) {
            $( "#vote-to" ).datepicker( "option", "minDate", selectedDate );
          }
        });
        $( "#vote-to" ).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat:"dd/mm/yy",
          onClose: function( selectedDate ) {
            $( "#vote-from" ).datepicker( "option", "maxDate", selectedDate );
          }
        });
      });
    </script>
  </body>
</html>
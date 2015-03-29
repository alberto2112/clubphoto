<?php
    if(!defined('SYSTEM_ROOT'))
      include __DIR__.'/../../settings.php';

    include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
    include SYSTEM_ROOT.LIB_DIR.'login.lib.php';

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
/*
    $def_cal_date = array(
      'upload-from'=>date('d/m/Y'),
      'upload-to'=>date('d/m/Y',time() + (7 * 24 * 60 * 60)),
      'vote-to'=>date('d/m/Y',time() + (14 * 24 * 60 * 60))
    );
    $def_cal_date['vote-from']=$def_cal_date['upload-to'];
*/
    // Si le fichier config.php existe, remplir les cases avec son contenu
    // TODO
    // Construir code html parametres formulaire
    $FORM = array(
      'albumname'=>(get_arr_value($CONFIG,'albumname')!='')?'value="'.$CONFIG['albumname'].'" ':'',
      'watermark'=>(get_arr_value($CONFIG,'watermark')=='1')?'checked="checked" ':'',
      'allowupload'=>(get_arr_value($CONFIG,'allowupload')=='1')?'checked="checked" ':'',
      'uploadslimit'=>'<option selected>'.get_arr_value($CONFIG,'uploadslimit','6').' (actuellement)</option>',
      'allowvotes'=>(get_arr_value($CONFIG,'allowvotes')=='1')?'checked="checked" ':'',
      'antitriche'=>(get_arr_value($CONFIG,'antitriche')=='1')?'checked="checked" ':'',
      'ratemethod_likes'=>(get_arr_value($CONFIG,'ratemethod')=='like')?'checked="checked" ':'',
      'ratemethod_stars'=>(get_arr_value($CONFIG,'ratemethod')=='stars')?'checked="checked" ':''
    );
//DEBUG
//print_r($CONFIG);
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
                  <input id="sortie" type="text" name="albumname" placeholder="Nom du village ou endroit" <?php echo $FORM['albumname']; ?>/>

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
                  <input id="allowupload" type="checkbox" name="allowupload" value="1" <?php echo $FORM['allowupload']; ?>/>
                  <span>Permetre aux utilisateurs de poster ses photos.</span>
              </li>
              <li>
                <span>Du</span>
                <input id="upload-from" type="text" name="upload-from" value="<?php echo $CONFIG['upload-from']; ?>" class="datepicker" />

                <span>Au</span>
                <input id="upload-to" type="text" name="upload-to" value="<?php echo $CONFIG['upload-to']; ?>" class="datepicker" />
              </li>
              <li>
                <label>
                  <span>Limite de t&eacute;l&eacute;chargements par personne:</span>
                  <select id="uploadslimit" type="text" name="uploadslimit">
                    <?php echo $FORM['uploadslimit']; ?>
                    <option value="2">2</option>
                    <option value="4">4</option>
                    <option value="6">6</option>
                    <option value="8">8</option>
                    <option value="10">10</option>
                    <option value="12">12</option>
                    <option value="14">14</option>
                    <option value="16">16</option>
                  </select>
                </label>
              </li>
            </ul>
          </div>
          <!-- /telechargements -->
          
          <!-- votes -->
          <div class="card">
            <div class="row">
              <h2>Votations</h2>
            </div>
            

            <ul class="row border-down content chbx_cascade">
              <li>
                  <input id="allowvotes" type="checkbox" name="allowvotes" value="1" <?php echo $FORM['allowvotes']; ?>/>
                  <span>Permetre aux utilisateurs de voter parmi les photos post&eacute;s.</span>
              </li>
              <li>
                <span class="datepicker">Du</span>
                <input id="vote-from" type="text" name="vote-from" value="<?php echo $CONFIG['vote-from']; ?>" class="datepicker" />

                <span class="datepicker">Au</span>
                <input id="vote-to" type="text" name="vote-to" value="<?php echo $CONFIG['vote-to']; ?>" class="datepicker" />
              </li>
              <li>
                  <input id="antitriche" type="checkbox" name="antitriche" value="1" <?php echo $FORM['antitriche']; ?>/>
                  <span>&Eacute;viter que les utilisateurs puissent voter plusieurs fois par la m&ecirc;me photo.</span>
              </li>
              <li class="disabled">
                  <input id="allowcomments" type="checkbox" name="allowcomments" value="0" />
                  <span>Les photographes peuvent donner son avis lors du vote.</span>
              </li>
              <li class="disabled">
                  <input id="allowrateview" type="checkbox" name="allowrateview" value="0" />
                  <span>Les photographes peuvent regarder le classement pour ses photos en temps r&eacute;el.</span>
              </li>
              <li class="disabled">
                  <input id="allowselfrating" type="checkbox" name="allowselfrating" value="0" />
                  <span>Les photographes peuvent voter par ses propres photos.</span>
              </li>
            </ul>
            
            <div class="row">
              <h3>Syst&egrave;me de votes</h3>
            </div>
            <div class="row border-down">
              <div class="content chbx_cascade">
                <span><input id="ratemethod-like" type="radio" name="ratemethod" value="like" <?php echo $FORM['ratemethod_likes']; ?>/>
                Standard.</span><br />
                <span><input id="ratemethod-stars" type="radio" name="ratemethod" value="stars" <?php echo $FORM['ratemethod_stars']; ?>/>
                Par &eacute;toiles.</span>
              </div>
            </div>
          </div>
          <!-- votes -->
          
          <!-- generalites -->
          <div class="card">
            <div class="row">
              <h2>G&eacute;n&eacute;ralit&eacute;es</h2>
            </div>
            <div class="row border-down">
              <div class="content chbx_cascade">
                <span class="disabled"><input id="watermark" type="checkbox" name="watermark" value="1" <?php echo $FORM['watermark']; ?>/>
                Apliquer automatiquement une filigrane aux photos post&eacute;s.</span>
              </div>
            </div>
            <div class="row border-down">
              <h3>Les photographes peuvent gerer ses photos</h3>
            </div>
            <div class="row border-down">
              <div class="content disabled">
                <span><input id="ratemethod-like" type="radio" name="user-photo-del" value="never" />Jamais</span><br />
                <span><input id="ratemethod-like" type="radio" name="user-photo-del" value="onuploadtime" />Pendant la p&eacute;riode de t&eacute;l&eacute;chargements</span><br />
                <span><input id="ratemethod-like" type="radio" name="user-photo-del" value="always" />&Aacute; tout moment</span>
              </div>
            </div>
            <div class="row">
              <div class="content">
                
              </div>
            </div>
          </div>
          <!-- /generalites -->
          
          <!-- securite -->
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
          <!-- securite -->
          
          <!-- liens -->
          <div class="card">
            <div class="row">
              <h2>Liens</h2>
            </div>
            <div class="row">
              <div class="content">
                <label><span>Lien publique:</span><span class="link"><?php echo 'http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum; ?></span></label>
                <label><span>Lien privil&eacute;gi&eacute;:</span><span class="link"><?php echo 'http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/?'.URI_QUERY_RIGHTS_KEY.'='.$CONFIG['RKEY']; ?></span></label>
              </div>
            </div>
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
<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include SYSTEM_ROOT.LIB_DIR.'system.lib.php';

  $codalbum = getRequest_param(URI_QUERY_ALBUM, false);

  if(empty($codalbum))
    exit;

  // Charger les parametres de l'album
  // Si le fichier config.php n'existe pas
  // remplir l'array avec des parametres par defaut
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php'))
    $CONFIG = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  else
    $CONFIG = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';// remplir l'array avec des parametres par defaut

  // Empecher de telecharger des photos a tout personne externe au club photo
  if(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($CONFIG, 'RKEY')){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
    exit;
  }

  // Get filesize and test with disk Quota
  if(DISK_QUOTA > 0){
    if(file_exists(FILE_USED_QUOTA)){
      $current_used_quota = file_get_contents(FILE_USED_QUOTA,null,null,null,9); // in Ko
      if($current_used_quota > DISK_QUOTA){
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'error.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&E=QUOTA');
        exit;
      }
    }
  }

  $max_uploads = get_arr_value($CONFIG,'uploadslimit','6');

  // Get private user RKEY
  $USER_KEY = get_arr_value($_COOKIE, COOKIE_USER_KEY.$codalbum, make_rkey(14,'012345679VWXYZ'));
//$USER_KEY = (array_key_exists(COOKIE_USER_KEY.$codalbum, $_COOKIE))? $_COOKIE[COOKIE_USER_KEY.$codalbum] : make_rkey(14,'012345679VWXYZ');

  // Refresh/Create USER_KEY cookie
  setcookie(COOKIE_USER_KEY.$codalbum, $USER_KEY, time() + (3600 * 24 * 10), PUBLIC_ROOT); //Cookie for 10 Days

  // Get user name
  $UNAME = (@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_KEY.'.uname')) ? file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_KEY.'.uname') : '';
  
?>
<html>
    <head>
        <title>Upload</title>
        <link href="<?php echo PUBLIC_ROOT; ?>css/upload.css?v=20150315" media="all" rel="stylesheet" type="text/css" />
        <link href="<?php echo PUBLIC_ROOT; ?>css/buttons.css?v=20150213" media="all" rel="stylesheet" type="text/css" />
        <link href="<?php echo PUBLIC_ROOT; ?>css/dropzone.css" media="all" rel="stylesheet" type="text/css" />
      
    </head>
    <body>
        <form action="../<?php echo RUN_DIR; ?>upload.php" class="dropzone" id="myDropzone"
              method="post" enctype="image/jpeg" >
			<input type="hidden" name="<?php echo URI_QUERY_ALBUM; ?>" value="<?php echo $codalbum; ?>" />
            <input type="hidden" name="<?php echo URI_QUERY_RIGHTS_KEY; ?>" value="<?php echo $USER_KEY; ?>" />
            <div class="form_items">
              <p>Pour faciliter le tri vis-&agrave;-vis de l'administrateur, vous pouvez renseigner votre nom et pr&eacute;nom <i>(Avant de t&eacute;l&eacute;charger quoique se soit)</i>.</p>
              <p>Cette information sera r&eacute;serv&eacute;e uniquement &agrave; l'administrateur</p>
              <input class="txt_auteur" type="text" name="auteur" value="<?php echo html_entity_decode($UNAME, ENT_COMPAT, CHARSET); ?>" placeholder="[1] NOM, Prenom auteur (facultatif)" />
            </div>
            <div class="fallback">
                <input name="file" type="file" multiple />
            </div>
        </form>
      <div style="text-align:center;">
		<div class="button-wrapper" id="btn-terminer" style="display:none;">
			<a class="blue" href="<?php echo PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$codalbum; ?>" class="button">Continuer</a>
		</div>
      </div>
        <script src="<?php echo PUBLIC_ROOT; ?>js/dropzone.js" type="text/javascript"></script>
		<script>
            var added_files=0;
            var uploaded_files=0;
            var other_counter=0;
			Dropzone.options.myDropzone = {
			  maxFiles: <?php echo $max_uploads; ?>,
			  init: function() {
				//this.on("addedfile", function(file) { alert("Added file."); });
				this.on("maxfilesexceeded", function(file) { alert("Vous avez d&eacute;j&agrave; t&eacute;l&eacute;charg&eacute; beaucoup trop de fichiers."); });
                this.on("canceled", function(file, messageOrDataFromServer, myEvent){
                  ++other_counter;
                  if(uploaded_files + other_counter==added_files){
                    document.getElementById('btn-terminer').style.display = 'block';
                  }
                });
                this.on("complete", function(file, messageOrDataFromServer, myEvent){
                  ++uploaded_files;
                  if(uploaded_files + other_counter==added_files){
                    document.getElementById('btn-terminer').style.display = 'block';
                  }
                });
                this.on("addedfile", function(file, messageOrDataFromServer, myEvent){
                  document.getElementById('btn-terminer').style.display = 'none';
                  ++added_files;
                });
                
                this.on("queuecomplete", function(file, messageOrDataFromServer, myEvent){
                  document.getElementById('btn-terminer').style.display = 'block';
                });
			  }
			};
		</script>
    </body>
</html>
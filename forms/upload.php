<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include SYSTEM_ROOT.ETC_DIR.'versions.php';
  include SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);
  $count = 0;

  if(empty($codalbum)){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
    exit;
  }

  // Charger les parametres de l'album
  // Si le fichier config.php n'existe pas
  // remplir l'array avec des parametres par defaut
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php'))
    $CONFIG = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
  else
    $CONFIG = include SYSTEM_ROOT.ETC_DIR.'album_def.config.php';// remplir l'array avec des parametres par defaut

  // Empecher de telecharger des photos a tout personne externe au club photo
  if(!array_key_exists(COOKIE_RIGHTS_KEY.$codalbum, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY.$codalbum) != get_arr_value($CONFIG, 'RKEY')){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
    exit;
  }

  // Determiner si la periode de telechargement est en cours
  if(out_of_date($CONFIG['upload-from'], $CONFIG['upload-to'])){
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum);
    exit; 
  }

  // Get filesize and test with disk Quota
  if(DISK_QUOTA > 0){
    if(file_exists(FILE_USED_QUOTA)){
      $current_used_quota = file_get_contents(FILE_USED_QUOTA,null,null,null,9); // in Ko
      if($current_used_quota > DISK_QUOTA){
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'error.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&'.URI_QUERY_ERROR.'=QUOTA');
        exit;
      }
    }
  }

  $max_uploads = get_arr_value($CONFIG,'uploadslimit','6');

  // Get private user session
  $USER_SESSION = get_arr_value($_COOKIE, COOKIE_USER_SESSION.$codalbum, make_rkey(14,'012345679VWXYZ'));
//$USER_SESSION = (array_key_exists(COOKIE_USER_SESSION.$codalbum, $_COOKIE))? $_COOKIE[COOKIE_USER_SESSION.$codalbum] : make_rkey(14,'012345679VWXYZ');

  // Refresh/Create USER_SESSION cookie
  setcookie(COOKIE_USER_SESSION.$codalbum, $USER_SESSION, time() + SESSION_LIFE_MEMBER, PUBLIC_ROOT); //Cookie for X Days

  // Get number of uploads for this user
  if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION)){
    foreach(file(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.DIRECTORY_SEPARATOR.PROC_DIR.$USER_SESSION, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $k => $f){
      if(file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/photos/thumbs/'.$f)){
        $count++;
      }
    }
    
    // Redirect user if N uploads >= max uploads authorized
    if($max_uploads > 0 && $count >= $max_uploads){
        header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'error.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&'.URI_QUERY_ERROR.'=UPLOAD_LIMIT');
        exit;
    }
  }

  // Get user name
  $UNAME = (@is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION.'.uname')) ? file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION.'.uname') : '';
  
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Upload</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="<?php echo PUBLIC_ROOT.'css/upload.css?v='.VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
        <link href="<?php echo PUBLIC_ROOT.'css/buttons.css?v='.VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
        <link href="<?php echo PUBLIC_ROOT.'css/dropzone.css?v='.VERSION_CSS; ?>" media="all" rel="stylesheet" type="text/css" />
        <script src="<?php echo PUBLIC_ROOT.'js/dropzone.js?v='.VERSION_JS; ?>" type="text/javascript"></script>
        <script src="<?php echo PUBLIC_ROOT.'js/jquery.1.10.1.min.js?v='.VERSION_JS; ?>" type="text/javascript"></script>
    </head>
    <body>
        <form action="<?php echo PUBLIC_ROOT.RUN_DIR; ?>upload.php" class="dropzone" id="myDropzone"
              method="post" enctype="image/jpeg" accept="image/*" capture>
			<input type="hidden" name="<?php echo URI_QUERY_ALBUM; ?>" value="<?php echo $codalbum; ?>" />
            <input type="hidden" name="<?php echo URI_QUERY_RIGHTS_KEY; ?>" value="<?php echo $USER_SESSION; ?>" />
            <div class="form_items" id="frm-step-1">
              <p>Pour faciliter le tri vis-&agrave;-vis de l'administrateur, vous pouvez renseigner votre nom et pr&eacute;nom.<br />
              Cette information sera r&eacute;serv&eacute;e uniquement &agrave; l'administrateur</p>
              <input class="txt_auteur" type="text" name="auteur" value="<?php echo html_entity_decode($UNAME, ENT_COMPAT, CHARSET); ?>" placeholder="NOM, Prenom auteur (facultatif)" />
            </div>
            <div class="fallback" id="frm-step-2" style="display:none;">
                <input name="file" type="file" multiple />
            </div>
        </form>
      
		<div class="button-wrapper" style="text-align:center; padding:.8em;">
            <a class="button blue" id="btn-next" style="display:inline;" onclick="javascript:showNextStep();" href="#">Suivant</a>
            <a class="button blue" id="btn-prev" style="display:none;" onclick="javascript:showPrevStep();" href="#">Retour</a>
			<a class="button green" id="btn-send" style="display:none;" href="<?php echo PUBLIC_ROOT.FORMS_DIR.'myuploads.php?'.URI_QUERY_ALBUM.'='.$codalbum; ?>">Continuer</a>
		</div>
      
		<script>
            var added_files=0;
            var uploaded_files=<?php echo $count; ?>;
            var other_counter=0;
            var step_counter=1;
            var total_steps=2;
          
            function showNextStep(){
              $('#frm-step-'+step_counter).hide();
              ++step_counter;
              
              // Hide next button
              if(step_counter==total_steps){
                $('#btn-next').hide();
                $('.dz-default').show();
                $('#btn-prev').show();
              }else{
                $('#frm-step-'+step_counter).show();
              }
            }
          
          function showPrevStep(){
              $('#frm-step-'+step_counter).hide();
              $('.dz-default').hide();
              --step_counter;
              $('#frm-step-'+step_counter).show();
              
              // Hide next button
              if(step_counter==1){
                $('#btn-prev').hide();
                $('#btn-next').show();
              }
            }
          
			Dropzone.options.myDropzone = {
			  maxFiles: <?php echo $max_uploads; ?>,
			  init: function() {
				//this.on("addedfile", function(file) { alert("Added file."); });
				this.on("maxfilesexceeded", function(file) { alert("Vous avez d&eacute;j&agrave; t&eacute;l&eacute;charg&eacute; assez de photos."); });
                this.on("canceled", function(file, messageOrDataFromServer, myEvent){
                  ++other_counter;
                  if(uploaded_files + other_counter==added_files){
                    document.getElementById('btn-send').style.display = 'inline';
                  }
                });
                this.on("complete", function(file, messageOrDataFromServer, myEvent){
                  ++uploaded_files;
                  if(uploaded_files + other_counter==added_files){
                    document.getElementById('btn-send').style.display = 'inline';
                  }
                });
                this.on("addedfile", function(file, messageOrDataFromServer, myEvent){
                  document.getElementById('btn-send').style.display = 'none';
                  ++added_files;
                });
                
                this.on("queuecomplete", function(file, messageOrDataFromServer, myEvent){
                  document.getElementById('btn-send').style.display = 'inline';
                });
			  }
			};
		</script>
    </body>
</html>
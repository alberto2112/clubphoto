<?php
  if(!defined('SYSTEM_ROOT'))
    include __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'datetime.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'photo.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'log.class.php';

  //If class Imagick not exists load bricoled Imagick class with GD library
  if(!class_exists('Imagick')){
      include SYSTEM_ROOT.LIB_DIR.'imagick.class.php';
      $CLONE_EXIF = true;
  }else{
    $CLONE_EXIF = false;
  }

// Get IP address
  $IP = getClient_ip();

// Get and clean request vars
  $codalbum   = clear_request_param(getRequest_param(URI_QUERY_ALBUM, false), 'a-zA-Z0-9', 8, false);

// Variable codalbum non especifie
  if($codalbum==false){
    $E = new LOG(SYSTEM_ROOT.ADMIN_DIR.'logs/events.log');
    $E->insert('[*] UNKNOWN ALBUM CODE - ip='.$IP.' file=/'.RUN_DIR.'upload.php referer='.get_arr_value($_SERVER, 'HTTP_REFERER', 'UNKNOWN'));
    $E->close();
    exit;
  }
  
// Open error log
  $ERRLOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/errors.log');

// Get User session
  $USER_SESSION = clear_request_param(
                   getRequest_param(
                       URI_QUERY_RIGHTS_KEY, 
                       get_arr_value(
                           $_COOKIE, 
                           COOKIE_USER_SESSION.$codalbum, 
                           make_rkey(14,'012345679VWXYZ')
                       )
                   ), 
                   'a-zA-Z0-9', 
                   16, 
                   false);


// Get fingerprint
  if(array_key_exists(COOKIE_FINGERPRINT, $_COOKIE)){
    $UPLOADERID = $_COOKIE[COOKIE_FINGERPRINT];
  }else{
    $UPLOADERID = @sprintf("%u",ip2long($IP)) | '0';
  }

  if (!empty($_FILES)) {

    // Charger les parametres de l'album
    // Si le fichier config.php n'existe pas
    // remplir l'array avec des parametres par defaut
    if(is_readable(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php'))
      $CONFIG = include SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php';
    else
      $CONFIG = include SYSTEM_ROOT.ETC_DIR.'default_album.config.php';// remplir l'array avec des parametres par defaut

    // Empecher de telecharger des photos a tout personne externe au club photo
    if(!array_key_exists(COOKIE_RIGHTS_KEY, $_COOKIE) || get_arr_value($_COOKIE,COOKIE_RIGHTS_KEY) != get_arr_value($CONFIG, COOKIE_RIGHTS_KEY)){
      // Add log line if not album_rkey cookie founded
      $ERRLOG->insert('[!] NOT A MEMBER - ip='.$IP.' uploaderid='.$UPLOADERID.' referer='.get_arr_value($_SERVER, 'HTTP_REFERER', 'UNKNOWN'));
      $ERRLOG->close();
      exit;
    }
    
    // Determiner si la periode de telechargement est en cours
    if(out_of_date(get_arr_value($CONFIG, 'upload-from',false), get_arr_value($CONFIG,'upload-to',false))){
      // Add log line 
      $ERRLOG->insert('[*] UPLOAD OUT OF DATE - ip='.$IP.' uploaderid='.$UPLOADERID.' referer='.get_arr_value($_SERVER, 'HTTP_REFERER', 'UNKNOWN'));
      $ERRLOG->close();
      exit; 
    }

  // Get and clean other request vars
    $author    = clear_request_param(getRequest_param('auteur', false), 'a-zA-Z0-9\,\'\ ', 32, false);
    
    $file_album_size = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/album_size.txt';
    //$file_current_used_quota = FILE_USED_QUOTA; //SYSTEM_ROOT.ALBUMS_DIR.'quota.txt';
    $current_album_size = 0;
    $current_used_quota = 0;

  // Create USER_KEY if error
    if($USER_SESSION==false){
      $USER_SESSION=make_rkey(14,'012345679VWXYZ');
      setcookie(COOKIE_USER_SESSION.$codalbum, $USER_SESSION, time() + SESSION_LIFE_MEMBER, PUBLIC_ROOT); //Cookie for 10 Days
    }

  // Open upload log
    $LOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/uploads.log');

    if(!file_exists(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR))
      @mkdir(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR,0755);

  // Create proc file
    $PROC = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION, false);

  // Save author name
    if(!empty($author)){
      @file_put_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/'.PROC_DIR.$USER_SESSION.'.uname', $author, LOCK_EX);
    }

    // Get filesize and test with disk Quota
    if(DISK_QUOTA > 0){
      if(file_exists(FILE_USED_QUOTA)){
        $current_used_quota = file_get_contents(FILE_USED_QUOTA,null,null,null,9); // in Ko
        if($current_used_quota > DISK_QUOTA){
          $LOG->insert('[!] QUOTA DEPASEE - '.$IP.' - UKEY: '.$USER_SESSION.' (/'.RUN_DIR.'upload.php)', false);
          $LOG->insert('    -> DISK LIMIT: '.DISK_QUOTA.'Ko - CURRENT USED DISK SPACE: '.$current_used_quota.'Ko');
          $LOG->close();
          exit;
        }
      }

      if(file_exists($file_album_size)){
        $current_album_size = file_get_contents($file_album_size,null,null,null,9); // in Ko
      }
    }

    $workspace  = SYSTEM_ROOT.WORKSPACE_DIR.$IP.'/';
    $title = clear_request_param(@substr(basename($_FILES['file']['name']), 0, -4), false, 128, true); // Transformer characteres non conformes du nom de fichier
    $photo_basename = md5(rand().time()."ClubPhotoMJCRodez").'.jpg'; // Calculer nuveau nom du fichier
    $targetAlbum = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';   // Album d'installation
    
  // Remplacer autres caracteres que JavaScript a l'obligation de mettre
    $title = str_replace('&Atilde;&iexcl;', '&aacute;', $title);
    $title = str_replace('&Atilde;&nbsp;', '&agrave;', $title);
    $title = str_replace('&Atilde;&copy;', '&eacute;', $title);
    $title = str_replace('&Atilde;&scaron;', '&egrave;', $title);
    $title = str_replace('&Atilde;&sup1;', '&ugrave;', $title);
    $title = str_replace('&Atilde;&ordf;', '&ecirc;', $title);
    $title = str_replace('&Atilde;&cent;', '&acirc;', $title);
    $title = str_replace('&Atilde;&reg;', '&icirc;', $title);
    $title = str_replace('&Atilde;&macr;', '&uml;', $title);
    $title = str_replace('&Atilde;&sect;', '&ccedil;', $title);
    

    // Creer dossiers necesaires
    @mkdir($targetAlbum,0777,true); // Dossier des albums
    @mkdir($workspace,0777,true); // Dossier temporel base en adresse ip

    // Ajouter evenement au log d'activite
    $LOG->insert('UPLOADED - ip='.$IP.' UKEY='.$USER_SESSION.' uploaderid='.$UPLOADERID.' msg=Temp file name: '.$_FILES['file']['tmp_name'].' (/'.RUN_DIR.'upload.php)');

    try{
      // Reserver memoire
      ini_set('memory_limit', SYS_MEMORY_LIMIT );
      
      // Calculer nom final du fichier
      if(file_exists($targetAlbum.$photo_basename.'.csv')){
        $photo_basename = md5(rand().time()."ClubPhotoMJCRodez").'.jpg';
        while(!file_exists($targetAlbum.$photo_basename.'.csv')){
            $photo_basename = md5(rand().time()."ClubPhotoMJCRodez").'.jpg';
        }
      }
    
      $tempFile = $workspace. $photo_basename;                       // Destination origine du fichier

      // Stocker photo en dossier temporel
      move_uploaded_file($_FILES['file']['tmp_name'], $tempFile);
      
      // Reserver fichier CSV
      $CSV = fopen($targetAlbum.'photos/'.$photo_basename.'.csv','w');
      flock($CSV, LOCK_EX);
      
      // Extraire infos EXIF
      $exif_infos = extract_exif($tempFile);
      
      // Ecrire le fichier CSV pour la photo
      fwrite($CSV, $exif_infos);
      
      // Installer photo et creer fichier d'information
      $result = install_photo($tempFile, $targetAlbum, false, DIM_THUMB, DIM_MEDIUM, DIM_LARGE );
      
      if(is_object($result)){
        // S'il y a une erreur logger celui-ci dans les archives
        $LOG->insert('[!] INSTALL ERROR - ip='.$IP.' UKEY='.$USER_SESSION.' uploaderid='.$UPLOADERID.' photo='.$photo_basename.' (/'.RUN_DIR.'upload.php)'); //
        $ERRLOG->insert('[!] INSTALL ERROR - photo='.$photo_basename.' file='.$e->getFile().' [@'.$e->getLine().'] msg='.$e->getMessage());
        
      }else{
        // Creer cookie d'auteur
        $str_cookie = $codalbum.'_'.str_replace('.','_',$photo_basename);
        setcookie($str_cookie,'-1',time()+(3600 * 24 * 21), PUBLIC_ROOT); // For 21 days

        // Ajouter photo a user session
        $PROC->insert($photo_basename, true); // Insert and close file

        // Ajouter auteur et identificateur dans le fichier d'informations relatives a la photo
        #fwrite($CSV,';'.$title.';;;;'.$USER_SESSION.';'.$author.';'.$UPLOADERID);
        fwrite($CSV,';;;;;'.$USER_SESSION.';'.$author.';'.$UPLOADERID);

        // Liberer et fermer fichier CSV
        flock($CSV, LOCK_UN);
        fclose($CSV);

        // Enregistrer libelle
        file_put_contents($targetAlbum.'photos/'.$photo_basename.'.lbl.txt', $title);

        // Cloner EXIF avec PEL si necesaire
        if($CLONE_EXIF){
          include SYSTEM_ROOT.LIB_DIR.'pel/autoload.php';

          $orig = new lsolesen\pel\PelJpeg($tempFile);
          $dest = new lsolesen\pel\PelJpeg($targetAlbum.'photos/large/'.$photo_basename);

          $exif = $orig->getExif();
          if(!empty($exif)){
            $dest->setExif($exif);
            $dest->saveFile($targetAlbum.'photos/large/'.$photo_basename);
          }
        }

        // Ajouter taille d'image aux fichiers de quota
        $photo_filesize = round($result / 1024);
        file_put_contents($file_album_size, ($current_album_size + $photo_filesize), LOCK_EX);
        file_put_contents(FILE_USED_QUOTA, ($current_used_quota + $photo_filesize), LOCK_EX);

        // Creer et/ou ajouter au log d'activite
        $LOG->insert('INSTALLED - ip='.$IP.' UKEY='.$USER_SESSION.' uploaderid='.$UPLOADERID.' photo='.$photo_basename);
      }
    } catch (Exception $e){
      // Si une erreur est produite lors de l'installation de l'image l'enregistrer dans le log
      $LOG->insert('[!] INSTALL ERROR - ip='.$IP.' UKEY='.$USER_SESSION.' uploaderid='.$UPLOADERID.' photo='.$photo_basename.' (/'.RUN_DIR.'upload.php)'); //
      $ERRLOG->insert('[!] INSTALL ERROR - photo='.$photo_basename.' file='.$e->getFile().' @='.$e->getLine().' msg='.$e->getMessage());
    }
    
  // Effacer toute trace du dossier de travail temporel
    rmdir_recurse($workspace);
  }else{
    if(empty($USER_SESSION))
      $USER_SESSION = 'Unknown';
    
    $ERROR->insert('[!] UPLOAD ERROR - ip='.$IP.' - UKEY='.$USER_SESSION.' uploaderid='.$UPLOADERID.' - msg=Telechargement vide (/'.RUN_DIR.'upload.php)');
  }
  $LOG->close();
  $ERRLOG->close();
  $PROC->close();
?>
<?php
  if(!defined('SYSTEM_ROOT'))
    require_once __DIR__.'/../settings.php';

  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.LIB_DIR.'login.lib.php';

  // Forcer administrateur
  if(!is_admin()){
    if(SYS_HTTPS_AVAILABLE){
      header('Location: https://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }else{
      header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.'login.php');
    }
    exit;
  }

  include SYSTEM_ROOT.LIB_DIR.'filesystem.lib.php';
  include SYSTEM_ROOT.LIB_DIR.'log.class.php';

  $VARLABELS = array(
    'ALBUMNAME'=>'albumname',
    'ALBUMDESC'=>'albumdesc',
    'ALLOWUPLOAD'=>'allowupload',
    'UPLOADSLIMIT'=>'uploadslimit',
    'UPLOAD-FROM'=>'upload-from',
    'UPLOAD-TO'=>'upload-to',
    'ALLOWVOTES'=>'allowvotes',
    'VOTE-FROM'=>'vote-from',
    'VOTE-TO'=>'vote-to',
    'WATERMARK'=>'watermark',
    'ANTITRICHE'=>'antitriche',
    'RATEMETHOD'=>'ratemethod',
    'RKEY'=>'RKEY'
  );

  $codalbum = clear_request_param(getRequest_param(URI_QUERY_ALBUM, ''), 'a-zA-Z0-9', 8, false);
  $action = clear_request_param(getRequest_param(URI_QUERY_ACTION, 'new'), 'a-z', 8, false);
  $path_album = SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/';

  $ADMIN_NAME = get_arr_value($_SESSION, 'UNAME', 'Unknown');// For LOGs

  $IP  = getClient_ip();
  $LOG = new LOG(SYSTEM_ROOT.ADMIN_DIR.'/logs/albums.log'); // Le fichier sera cree/ouvert uniquement si on ajoute des lignes

  if($codalbum==''){
    // TODO: Afficher message d'erreur
    echo "<p>Album non trouv&eacute;</p>";

    // Enregistrer activitee
    $LOG->insert('[!] msg=ALBUM CODE NOT FOUND ip='.$IP.' admin='.$ADMIN_NAME);    
  }else{
    $AL_LOG = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/logs/events.log'); // Le fichier sera cree/ouvert uniquement si on ajoute des lignes
    
    if($action=='new' || $action=='edit'){

      $RKEY = clear_request_param(getRequest_param(URI_QUERY_RIGHTS_KEY, ''), 'a-zA-Z0-9', 16, false);
      $ratemethod=clear_request_param(getRequest_param($VARLABELS['RATEMETHOD'], '0'),'a-z',8,false);

      $albumname    = clear_request_param(getRequest_param($VARLABELS['ALBUMNAME'], date('d.m.Y')), false, 254, true);
      $albumdesc    = nl2br2( clear_request_param(getRequest_param($VARLABELS['ALBUMDESC'],''), false, 500, true) );

      $allowupload  = clear_request_param(getRequest_param($VARLABELS['ALLOWUPLOAD'], '0'),'0-9',2,false);
      $uploadslimit = clear_request_param(getRequest_param($VARLABELS['UPLOADSLIMIT'], '6'),'0-9',2,false);
      $antitriche   = clear_request_param(getRequest_param($VARLABELS['ANTITRICHE'], '0'),'0-9',2,false);
      $allowvotes   = clear_request_param(getRequest_param($VARLABELS['ALLOWVOTES'], '0'),'0-9',2,false);
      $watermark    = clear_request_param(getRequest_param($VARLABELS['WATERMARK'], '0'),'0-9',2,false);
      
      $showranking        = clear_request_param(getRequest_param('showranking', '0'),'0-9',2,false);
      $allowphotomanag    = clear_request_param(getRequest_param('allowphotomanag', '0'),'0-9',2,false);
      $showrateforuploads = clear_request_param(getRequest_param('showrateforuploads', '0'),'0-9',2,false);
      $allowcomments      = clear_request_param(getRequest_param('allowcomments', '0'),'0-9',2,false);
      $allowselfrating    = clear_request_param(getRequest_param('allowselfrating', '0'),'0-9',2,false);

      $upload_from  = clear_request_param(getRequest_param($VARLABELS['UPLOAD-FROM'], ''), '0-9\/', 10, false);
      $upload_to    = clear_request_param(getRequest_param($VARLABELS['UPLOAD-TO'], ''), '0-9\/', 10, false);
      $vote_from    = clear_request_param(getRequest_param($VARLABELS['VOTE-FROM'], ''), '0-9\/', 10, false);
      $vote_to      = clear_request_param(getRequest_param($VARLABELS['VOTE-TO'], ''), '0-9\/', 10, false);
    }
    
    switch($action){
      case 'new':
        // Creer dossiers necesaires
        mkdir($path_album,0755,true);
        mkdir($path_album.'logs');
        mkdir($path_album.'votes');
        mkdir($path_album.'photos');
        mkdir($path_album.'photos/thumbs');
        mkdir($path_album.'photos/medium');
        mkdir($path_album.'photos/large');
        mkdir($path_album.TRASH_DIR);
        mkdir($path_album.PROC_DIR);

        // Verifier creation des dossiers necesaires
        if(!file_exists($path_album)){
          echo '<p>Une erreur est survenue lors de la cr&eacute;ation de l\'album &quote;'.$codalbum.'&quote;</p>';
          echo '<p><i><b>DEBUG: </b>'.$path_album.'</i></p>';
          exit;
        }

        // Copier les fichiers nécesaires
        copy(SYSTEM_ROOT.'viewer.model.php', $path_album.'index.php');

        // Inserer une page index.html (en blanc) dans chaque dossier cree
        file_put_contents($path_album.'photos/index.html', 
                          '<h1>Rien &aacute; regarder ici</h1>');

        file_put_contents($path_album.'photos/thumbs/index.html', 
                          '<h1>Rien &aacute; regarder ici</h1>');

        file_put_contents($path_album.'photos/medium/index.html', 
                          '<h1>Rien &aacute; regarder ici</h1>');

        file_put_contents($path_album.'photos/large/index.html', 
                          '<h1>Rien &aacute; regarder ici</h1>');
      
        // Proteger dossiers et fichiers sensibles
        file_put_contents($path_album.'photos/.htaccess', 
                          '<Files ~"\.csv$">'."\n".'Order allow,deny'."\n".'Deny from all'."\n".'</Files>', 
                          LOCK_EX);
        file_put_contents($path_album.'votes/.htaccess', 
                          'Deny from all', 
                          LOCK_EX);
        file_put_contents($path_album.'logs/.htaccess', 
                          'Deny from all', 
                          LOCK_EX);
        file_put_contents($path_album.PROC_DIR.'/.htaccess', 
                          'Deny from all', 
                          LOCK_EX);

        // Enregistrer activitee
        $LOG->insert('[+] ['.$codalbum.'] - ALBUM CREATED by '.$ADMIN_NAME.'  - '.$IP);
        $AL_LOG->insert('[+] - ALBUM CREATED by '.$ADMIN_NAME.'  - '.$IP);

        // !! Ne pas ajouter -> break;

      case 'edit':
        // Creer fichier config.php
        $AL_CONF = new LOG(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/config.php', false, true);
        $AL_CONF->insert('<?php',false);
        $AL_CONF->insert('return array(',false);

        $confstr = '"'.$VARLABELS['ALBUMNAME'].'"=>"'.$albumname.'",'
                  .'"'.$VARLABELS['ALLOWUPLOAD'].'"=>"'.$allowupload.'",'
                  .'"'.$VARLABELS['UPLOADSLIMIT'].'"=>"'.$uploadslimit.'",'
                  .'"'.$VARLABELS['UPLOAD-FROM'].'"=>"'.$upload_from.'",'
                  .'"'.$VARLABELS['UPLOAD-TO'].'"=>"'.$upload_to.'",'
                  .'"'.$VARLABELS['ANTITRICHE'].'"=>"'.$antitriche.'",'
                  .'"'.$VARLABELS['ALLOWVOTES'].'"=>"'.$allowvotes.'",'
                  .'"'.$VARLABELS['WATERMARK'].'"=>"'.$watermark.'",'
                  .'"'.$VARLABELS['VOTE-FROM'].'"=>"'.$vote_from.'",'
                  .'"'.$VARLABELS['VOTE-TO'].'"=>"'.$vote_to.'",'
                  .'"'.$VARLABELS['RATEMETHOD'].'"=>"'.$ratemethod.'",'
                  .'"'.$VARLABELS['RKEY'].'"=>"'.$RKEY.'",'
                  .'"'.$VARLABELS['ALBUMDESC'].'"=>"'.$albumdesc.'",'
                  .'"showranking"=>"'.$showranking.'",'
                  .'"allowphotomanag"=>"'.$allowphotomanag.'",'
                  .'"showrateforuploads"=>"'.$showrateforuploads.'",'
                  .'"allowcomments"=>"'.$allowcomments.'",'
                  .'"allowselfrating"=>"'.$allowselfrating.'"';

        $AL_CONF->insert($confstr,false);

        $AL_CONF->insert(');',false);
        $AL_CONF->insert('?>',false,'');
        $AL_CONF->close();
      
        // Enregistrer activitee
        if($action=='edit'){
          $LOG->insert('[=] ['.$codalbum.'] - ALBUM MODIFIED by '.$ADMIN_NAME.' - '.$IP);
          $AL_LOG->insert('[=] - ALBUM MODIFIED by '.$ADMIN_NAME.' - '.$IP);
        }
      
        $LOG->close();
        $AL_LOG->close();
        break;
      
      case 'totrash':
      case 'trash':
        if(!file_exists(SYSTEM_ROOT.TRASH_DIR))
          mkdir(SYSTEM_ROOT.TRASH_DIR);

        //TODO: if(file_exists(SYSTEM_ROOT.TRASH_DIR.$codalbum))
      
        rename(SYSTEM_ROOT.ALBUMS_DIR.$codalbum, SYSTEM_ROOT.TRASH_DIR.$codalbum);
      
        // Enregistrer activitee
        $LOG->insert('[<] ['.$codalbum.'] -  ALBUM TO TRASH by '.$ADMIN_NAME.'  - '.$IP, true);

        header('Location: '.PUBLIC_ROOT.ADMIN_DIR.'list_albums.php'); exit;
        break;
      
      case 'trashdel':
        // Get album size
        $album_size = file_get_contents(SYSTEM_ROOT.TRASH_DIR.$codalbum.'/album_size.txt',null,null,null,9);
          
        // Update used quota
        update_quota(FILE_USED_QUOTA, ($album_size * -1));
      
        // Delete album
        rmdir_recurse(SYSTEM_ROOT.TRASH_DIR.$codalbum);
      
        $LOG->insert('[-] ['.$codalbum.'] -  ALBUM DELETED FROM TRASH by '.$ADMIN_NAME.'  - '.$IP, true);
      
        header('Location: '.PUBLIC_ROOT.ADMIN_DIR.'list_albums.php'); exit;
        break;
      
      case 'delete':
        // Get album size
        $album_size = file_get_contents(SYSTEM_ROOT.ALBUMS_DIR.$codalbum.'/album_size.txt',null,null,null,9);
          
        // Update used quota
        update_quota(FILE_USED_QUOTA, ($album_size * -1));
          
        // Delete album
        rmdir_recurse(SYSTEM_ROOT.ALBUMS_DIR.$codalbum);
      
        // Enregistrer activitee
        $LOG->insert('[-] ['.$codalbum.'] - ALBUM DELETED by '.$ADMIN_NAME.'  - '.$IP, true);

        header('Location: '.PUBLIC_ROOT.ADMIN_DIR.'list_albums.php'); exit;
        break;
    }

    // Envoyer a l'album cree
    header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'/?'.URI_QUERY_RIGHTS_KEY.'='.$RKEY);

    // S'en assurer que l'execution du code termine ici. Conseil done par php.net (http://php.net/manual/en/function.header.php)
    exit;
  }
//echo '<a href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Aller &agrave; l\'album</a>';
?>
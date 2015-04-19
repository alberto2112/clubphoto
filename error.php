<?php
  if(!defined('SYSTEM_ROOT'))
    include(__DIR__.'/settings.php');

  include(SYSTEM_ROOT.LIB_DIR.'system.lib.php');

  $codalbum = getRequest_param(URI_QUERY_ALBUM, false);
  $E = getRequest_param(URI_QUERY_ERROR, false);
  $error_title = '';
  $error_msg = '';
  switch($E){
    case 'QUOTA':
      $error_title = 'DISQUE PLEIN';
      $error_msg = 'La limite d\'utilisation d\'espace en disque est atteinte ('.DISK_QUOTA.'Ko). Veuillez contacter l\'administrateur pour r&eacute;soudre le probl&egrave;me';
      break;
    
    case 'PWD_FILE_NOT_FOUND':
      $error_title = 'FICHER DE MOTS DE PASSE NON TROUVE';
      $error_msg = 'Le fichier de mots de passe n\'a pas &eacute;t&eacute; trouv&eacute;. Contactez le d&eacute;veloppeur du site pour arranger ce petit contretemps';
      break;
    
    case 'UPLOAD_LIMIT':
      $error_title = 'Limite de t&eacute;l&eacute;chargements atteinte';
      $error_msg = 'Vous avez atteint la limite de t&eacute;l&eacute;chargements pour cet album. <a href="http://'.SITE_DOMAIN.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Retour &agrave; l\'album</a>';
      break;
    
    default:
      $error_title = 'Erreur inconnu';
      $error_msg = 'Contactez le d&eacute;veloppeur du site pour arranger ce petit contretemps.';
  }
?>
<html>
    <head>
        <title>Error</title>
        <link href="<?php echo PUBLIC_ROOT; ?>css/error.css" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div class="error_msg">
      <h1><?php echo $error_title; ?></h1>
      <p><?php echo $error_msg; ?></p>
    </div>
  </body>
</html>
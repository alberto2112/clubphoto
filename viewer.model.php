<?php
  // Cet fichier sera copie sous le nom 'index.php' dans chaque album cree
  // dont son emplacement sera SYSTEM_ROOT.ALBUMS_DIR.code_de_album

  $_CODALBUM = basename(dirname(__FILE__)); //getRequest_param('codalbum', '');
  include '../../viewer.php';
?>
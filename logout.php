<?php
  include 'settings.php';

  session_start();
  session_destroy();
  header('Location: http://'.SITE_DOMAIN.PUBLIC_ROOT);
?>
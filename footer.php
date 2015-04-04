<?php
  if(!isset($_ISADMIN))
    $_ISADMIN = false;

  if(!isset($_ISMEMBER))
    $_ISMEMBER = false;
?>
<footer>
  <div class="centered clearfix onecol">
    <ul>
      <li><a href="http://www.mjcrodez.com/">MJC de Rodez</a></li>
      <li>1 Rue Saint-Cyrice<br />12000 Rodez</li>
      <li><a href="">Site officiel du Club photo</a></li>
  <?php
    if($_ISMEMBER && $_SHOWRANKING){
      echo '<li><a href="'.PUBLIC_ROOT.'classement.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'">Voir classement</a></li>';
    }

    if($_ISADMIN){
      echo '<li><a style="color:#E88" href="'.PUBLIC_ROOT.'logout.php">Se d&eacute;connecter</a></li>';
    } else {
      echo '<li><a href="'.PUBLIC_ROOT.'login.php">Administrer</a></li>';
    }
  ?>
    </ul>
    <ul>
      <li class="carte"></li>
    </ul>
    <ul>
      <li><h3>Autres infos</h3></li>
      <li>Upload script: <a href="http://www.dropzonejs.com/">DropzoneJS</a></li>
    </ul>
  </div>
</footer>
<?php
  if(!isset($_ISADMIN))
    $_ISADMIN = false;

  if(!isset($_ISMEMBER))
    $_ISMEMBER = false;

  if(!isset($_SHOWRANKING))
    $_SHOWRANKING = false;
?>
<footer>
  <div class="centered clearfix">
    <ul>
      <li class="title">Liens d'utilit&eacute;</li>
  <?php
    if($_ISADMIN){
      echo '<li><a href="'.PUBLIC_ROOT.ADMIN_DIR.'">Administrer</a></li>';
    } else {
      echo '<li><a href="'.PUBLIC_ROOT.'login.php">Administrer</a></li>';
    }

    if($_SHOWRANKING){ //$_ISMEMBER && $_SHOWRANKING
      echo '<li><a href="'.PUBLIC_ROOT.'classement.php?'.URI_QUERY_ALBUM.'='.$_CODALBUM.'">Voir classement</a></li>';
    }

  ?>
      <li class="disabled">Comment &eacute;valuer</li>
      <li class="disabled">FAQ</li>
      <li><a href="<?php echo PUBLIC_ROOT; ?>maj.php">Mises &agrave; jour</a></li>
      <li class="disabled">Signaler un probl&egrave;me</li>
<?php
  if($_ISADMIN){
      echo '<li><a style="color:#E88" href="'.PUBLIC_ROOT.'logout.php">Se d&eacute;connecter</a></li>';
    }
?>
    </ul>

    <ul>
      <li class="title">Autres infos</li>
      <li><a href="http://photo.mjcrodez.com/">Site officiel du Club photo</a></li> 
      <li><a href="http://www.mjcrodez.com/">Site officiel de la MJC (Rodez)</a></li>     
      <li>Upload script: <a href="http://www.dropzonejs.com/">DropzoneJS</a></li>
      <li>Icons made by Freepik from <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a></li>
      <li><a href="https://github.com/lsolesen/pel">PEL</a> (PHP Exif Library) by <a href="https://github.com/weberhofer">Martin Geisler</a></li>
      <li>Notifications push: <a href="https://instapush.im/">Instapush</a></li>
    </ul>

    <ul class="noborder">
      <li class="title">O&ugrave; sommes nous?</li>
      <li>MJC de Rodez</li>
      <li>1 Rue Saint-Cyrice</li>
      <li>12000 Rodez</li>
      <li><a href="https://www.google.fr/maps/place/Maison+des+Jeunes+et+de+la+Culture+de+Rodez/@44.352998,2.577991,17z/data=!3m1!4b1!4m2!3m1!1s0x0000000000000000:0x71ac842e7e08e3dc" class="carte"></a></li>
      <li><a href="http://mjcrodez.fr/clubs/activit%C3%A9s/culture/multim%C3%A9dia/80-num%C3%A9rique.html">+ d'infos</a></li>
    </ul>
  </div>
</footer>
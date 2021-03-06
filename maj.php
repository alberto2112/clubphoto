<?php 
  include 'settings.php';
  include_once SYSTEM_ROOT.LIB_DIR.'system.lib.php';
  include_once SYSTEM_ROOT.ETC_DIR.'versions.php';
?>
<html>
<head>
    <title>Club photo - MJC Rodez</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include SYSTEM_ROOT.ETC_DIR.'metatags.html'; ?>
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/reset.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/base.css?v='.VERSION_CSS; ?>" type="text/css" />
    <link rel="stylesheet" media="screen" href="<?php echo PUBLIC_ROOT.'css/maj.css?v='.VERSION_CSS; ?>" type="text/css" />
</head> 
<body>
  <!-- Header -->
  <div class="header">
    <h1><a class="header-button ico-home" href="<?php echo PUBLIC_ROOT; ?>">Home</a>Journal de mises-&agrave;-jour</h1>
  </div>
<!-- / Header -->
  <ul class="maj">
    <li>
      <h2 class="date">27.11.2015</h2>
      <ol>
        <li>Adh&eacute;rents: Ranking g&eacute;neral de fin de p&eacute;riode de votes class&eacute; par: Moyenne(DESC), Nombre de votes(ASC) pour un r&eacute;sultat plus juste.</li>
      </ol>
    </li>
    <li>
      <h2 class="date">14.05.2015</h2>
      <ol>
        <li>Administrateur: Implementation de notifications push (<a href="https://instapush.im">Instapush</a>)</li>
        <li>Administrateur: Menu pour supprimer des albums</li>
        <li>Administrateur: Menu pour envoyer des albums &agrave; la corbeille</li>
        <li>Administrateur: Am&eacute;liorations visuelles</li>
      </ol>
    </li>
    <li>
      <h2 class="date">30.04.2015</h2>
      <ol>
        <li>Site: Tatouage pour les photos post&eacute;s</li>
        <li>Adh&eacute;rents: Statistiques d&eacute;tailles dans le dossier 'mes t&eacute;l&eacute;chargements'</li>
      </ol>
    </li>
  </ul>
<?php
  include SYSTEM_ROOT.'footer.php';
?>
</body>
</html>
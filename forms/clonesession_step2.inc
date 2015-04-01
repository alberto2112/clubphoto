    <div class="header">
      <h1 style="font-weight: 100; padding: .35em;">Clonner sesion</h1>
    </div>
  <div class="pinform">
    <input type="text" name="pincode" />
  </div>
  <div class="button-wrapper at-center">
  <?php
    echo '<a class="button blue" href="'.PUBLIC_ROOT.RUN_DIR.'/clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=clone">Cloner</a>';
    echo '<a class="button blue" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=merge">Combiner</a>';
    echo '<a class="button red" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=cancel">Annuler</a>';
  ?>
  </div>
    <div class="header">
      <h1 style="font-weight: 100; padding: .35em;">Clonner sesion</h1>
    </div>
  <div class="form-pincode">
    <span>Saisissez le code PIN</span>
    <input type="text" id="txt-pincode" name="pincode" maxlength="5" inputmode="numeric" pattern="[0-9]*" />
  </div>
  <div class="button-wrapper at-center">
  <?php
    echo '<a class="button blue btn-clone" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=clone">Cloner</a>';
    echo '<a class="button blue btn-clone" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=merge">Combiner</a>';
    echo '<a class="button red" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=cancel">Annuler</a>';
  ?>
  </div>

<script>
      $(".btn-clone").click(function (e){
        e.preventDefault();
        var url = $(this).attr('href');
        var pincode = $("#txt-pincode").val();
/*
        $.post(url+"&amp;pin="+pincode).done(function(data){
          //TODO: Show OK message to user
          window.location.href="<?php echo PUBLIC_ROOT.ALBUMS_DIR.$codalbum; ?>";
        });
*/
        window.location.href=url+"&pin="+pincode;
        return false;
      });
    </script>
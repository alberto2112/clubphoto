    <div class="header">
      <h1 style="font-weight: 100; padding: .35em;">Clonner sesion</h1>
    </div>
    <p style="color:red;text-align:center;font-size:135%;font-weight:100;">&Aacute; r&eacute;diger correctement</p>
    <div class="faq">
      <p><img src="../images/clonesession_h.png" style="float:left; margin:.75em;"/>Ceci est un outil qui permet de transferer votre sesion actuel vers autre dispositif (smartphone, tablette, pc portable, etc.) de votre r&eacute;sseau.</p>

      <div class="button-wrapper at-center" style="clear:both;">
<?php
  echo '<a class="button red" href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Annuler</a>';
  //echo '<a class="button blue" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=request" onclick="javscript:cloneSendRequest(this);">Cloner</a>';
  echo '<a class="button blue btn-request" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=request">Cloner</a>';
?>
      </div>
  
      <h2>Qu&rsquo;est-ce qu&rsquo;une session ?</h2>
      <p>Quand vous postez des photos sur le site ou quand vous votez, le site cr&eacute;e, ce qu&rsquo;on appelle une session, dans v&ocirc;tre navigateur. Cette session permet au site de vous reconna&icirc;tre et agir en cons&eacute;quence.</p>
      <p>Ceci est de vital importance pour vous permettre d&rsquo;interagir avec les autres membres du club.</p>

      <h2>Pour qu&rsquo;elle raison je peux avoir besoin de cloner ma session ?</h2>
      <p>Vous &ecirc;tes nombreux &agrave; en avoir plusieurs dispositifs chez vous: smartphones, tablettes, pc portable&hellip; et en certaines ocassions on peut en avoir envie de changer le libell&eacute; et description d&rsquo;une de nos photos ou tout simplement regarder l&rsquo;&eacute;volution des votes pour nos photos.</p>
      <p>Probl&egrave;me: On n&rsquo;est pas &agrave; la maison, ou, on a la fl&egrave;me de se lever du canap&eacute;, allumer l&rsquo;ordi, attendre, ouvrir l&rsquo;explorateur internet (red&eacute;marrer &agrave; petition de Windows et tout recomencer) et se connecter au site.</p>
      <p>Cet outil vous permet de transf&eacute;rer vos infos de session &agrave; d&rsquo;autres dispositifs pour &eacute;viter tout ce cin&eacute;ma &agrave; chaque fois.</p>

      <h2>Comment &ccedil;a marche ?</h2>
      <p>Quand vous faites la demande de transfert de session le site active un code PIN et il reste &agrave; l&rsquo;&eacute;coute pour toute connexion venante de v&ocirc;tre r&eacute;seau d&rsquo;internet (par exemple, une tablette ou smartphone connect&eacute; via WiFi chez vous).</p>
      <p>En suite vous acc&eacute;dez &agrave; l&rsquo;album avec le dispositif au quel vous voulez transf&eacute;rer votre session et le site vous demandera le code PIN.</p>
      <p>Saisissez le code PIN, confirmez et la session sera copi&eacute; sur le nouveau appareil. La demande sera en suite supprim&eacute;.</p>

      <h2>Et si j&rsquo;ai travaill&eacute; aussi avec l&rsquo;autre dispositif ?</h2>
      <p>Il n&rsquo;y a aucun probl&egrave;me. Le site vous permet aussi de combiner vos sessions de fa&ccedil;on que toutes vos actions soient conserv&eacute;es dans une seule et unique session.</p>

      <h2>J&rsquo;ai clon&eacute; ma session, et maintenant ?</h2>
      <p>Maintenant toutes vos actions seront automatiquement synchronis&eacute;es: vos photos post&eacute;es, vos &eacute;valuations, etc.</p>
      <p>Par exemple: si vous t&eacute;l&eacute;chargez une photo, celle-ci appara&icirc;tra aussi dans les appareils o&ugrave; la session &agrave; &eacute;t&eacute; transf&eacute;r&eacute; et vous pourrez la g&eacute;rer comme vous le souhaitez: lui changer la libell&eacute;, la description, voir l&rsquo;&eacute;volution de votes (pendant la p&eacute;riode pr&eacute;vue &agrave; cet effet) et/ou la supprimer.</p>

      <div class="button-wrapper at-center">
  <?php
    echo '<a class="button red" href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Annuler</a>';

    echo '<a class="button blue btn-request" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=request" onclick="javascript:cloneSendRequest(this);">Cloner</a>';
  ?>
      </div>
    </div>
      
    <div class="pin">
      <p>Votre num&eacute;ro PIN est:</p>
      <p id="pincode"></p>
    </div>
    <script>
      $(".btn-request").click(function (e){
        e.preventDefault();
        var url = $(this).attr('href'); 
        /* $.post(
          "http://<?php echo SITE_DOMAIN.PUBLIC_ROOT.RUN_DIR; ?>clonesession.php",
          {<?php echo URI_QUERY_ALBUM.':"'.$codalbum.'"'; ?>});
        */
        $.post(url).done(function(data){
          $(".faq").hide();
          $("#pincode").html(data);
          //alert(data);
          $(".pin").show();
        });
        /*
        var data = {<?php echo '"'.URI_QUERY_ALBUM.'":"'.$codalbum.'"' ?>"};
        $.ajax({
          type: 'POST',
          dataType: 'text',
          //contentType: 'text/plain; charset=<?php echo CHARSET; ?>',
          //scriptCharset: '<?php echo CHARSET; ?>',
          url: 'http://<?php echo SITE_DOMAIN.PUBLIC_ROOT.RUN_DIR; ?>clonesession.php',
          data: data,
          success: function(data) {
            if(data.length < 5){
              $('#vote-counter').html('<h2>Votations</h2><p>Votre vote a  bien &eacute;t&eacute; enregistr&eacute;. Note attribu&eacute;: '+data+'</p>');
            }else{
              $('#vote-counter').html('<h2>Votations</h2><p>'+data+'</p>');
            }
          }
        });
        */
        return false;
      });
    </script>
    <div class="header">
      <h1 style="font-weight: 100; padding: .35em;">Clonner sesion</h1>
    </div>
    <div class="faq">
      <p><img src="../images/clonesession_h.png" style="float:left; margin:.75em;"/>Ceci est un outil qui permet de transferer votre sesion actuel vers autre dispositif (smartphone, tablette, pc portable, etc.) de vôtre reśseau.</p>

    <div class="button-wrapper at-center" style="clear:both;">
<?php
  echo '<a class="button red" href="'.PUBLIC_ROOT.ALBUMS_DIR.$codalbum.'">Annuler</a>';
  //echo '<a class="button blue" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=request" onclick="javscript:cloneSendRequest(this);">Cloner</a>';
  echo '<a class="button blue btn-request" href="'.PUBLIC_ROOT.RUN_DIR.'clonesession.php?'.URI_QUERY_ALBUM.'='.$codalbum.'&amp;'.URI_QUERY_ACTION.'=request">Cloner</a>';
?>
    </div>
      
      <h2>Quest-ce qu'une sesion ?</h2>
      <p>Quand vous postez des photos sur le site ou quand vous votez, le site cree, ce qu'on appelle, une sesion dans vôtre navigateur. Cette sesion permet au site de vous reconnaître et agir en consequence.</p>
      <p>Ceci est de vital importance pour vous permettre d'interactuer dans le site.</p>
      
      <h2>Pour qu'elle raison je peux avoir besoin de cloner ma sesion ?</h2>
      <p>Vous êtes nombreux à en avoir plusieurs dispositifs chez vous: smartphones, tablettes, pc portable... et en certaines ocassions on peut en avoir envie de changer le libellé et description d'une de nos photos ou tout simplement regarder l'évolution des votes pour nos photos.</p>
      <p>Problème: On n'est pas à la maison, ou, on a la flème de se lever du canapé, allumer l'ordi, attendre, ouvrir l'explorateur internet (redémarrer à petition de Windows et tout recomencer) et se connecter au site.</p>
      <p>Cet outil vous permet de transferer vos infos de sesion à d'autres dispositifs pour éviter tout ce cinéma à chaque fois.</p>
      
      <h2>Comment ça marche ?</h2>
      <p>Quand vous faites la demande de transfert de sesion le site genere un code PIN et il reste à l'écoute pour toute connexion venant de vôtre reseau d'internet (par exemple, une tablette conecté via WiFi chez vous).</p>
      <p>Une fois que vous y accedez à l'album le site vous informera que une demande de transfer de sesion à été faite et vous demandera le code PIN.</p>
      <p>Si le code PIN que vous saisisez est le bon, la sesion sera copié dans le nouveau disposifit et la demande sera supprimé.</p>
      
      <h2>Et si j'ai travaille aussi avec l'autre dispositif ?</h2>
      <p>Il n'y a rien à craindre. Le site vous permet aussi de combiner vos sesions de façon que toutes vos actions soient sonservées.</p>
      
      <h2>J'ai cloné ma sesion, et maintenant ?</h2>
      <p>Maintenant toutes vos actions seront automatiquement sinchronisées.</p>
      <p>Par exemple: si vous téléchargez une photo, celle-ci apairetra aussi dans les apareils où la sesion à été transferé et vous pourrez la supprimer, lui changer la libellé, la description et voir l'évolution de votes pendant la période prévue à cet effet.</p>
      <p>Peu importe le sense de travail, cela marche aussi bien de A -> B que de B -> A</p>
    
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
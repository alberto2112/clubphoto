<?php
  require_once __DIR__.'/../../settings.php';

  echo '
<!-- Toolbar -->
    <div class="toolbar">
      <ul>';

  if($_ISADMIN){
    echo '
        <li class="tb-delete-w"><a href="#" title="Suprimer album" onclick="ShowModalBox(\'mb-delete\')">Delete</a></li>
        <li class="tb-restore-w"><a href="#" title="Restaurer album" onclick="ShowModalBox(\'mb-restore\')">Restore</a></li>';
  }

  echo '
          </ul>
        </li>
      </ul>
    </div>';
?>

<script>

  $( "#tgl-toolbar" ).click(function() {
    $( "#sub-toolbar" ).toggle();
  });
</script>

<!-- / Toolbar -->
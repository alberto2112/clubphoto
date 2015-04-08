<?php
  function count_up($filename, $n=1){
    if(!is_numeric($n))
        $n = 1;
    
    if(file_exists($filename)){
      // Comptabiliser votes
      $counter = filesize($filename);
      // Ouvrir le fichier
      $handle = smart_file_open($filename, 'ab', 3000);
    }else{
      // Creer un nouveau le fichier
      $handle = smart_file_open($filename, 'wb', 3000);
      $counter = 0;
    }
    
    // Verrouiller le fichier
    flock($handle, LOCK_EX);

    /*
    $strout='';
    for($i=0; $i < $n; $i++){
      $strout .= 0x1;
    }
    */
    // Calculer nombre de bytes à ajouter au fichier
    $strout = str_repeat(0x1, $n);
    
    // Ecrire bytes au fichier
    $success = fwrite($handle, $strout);
    
    if(is_numeric($counter))
      $counter += $n;
    else
      $counter = $success;

    flock($handle, LOCK_UN); // Enlever le verrou
    fclose($handle);

    // Return resultat
    if($success===false){ //Il y a eu une erreur
      return 'error';
    }else{ //Le vote a ete bien comptabilise
      return $counter;
    }
  }

//-----------------------------------------------

  function count_down($filename, $n=1){
    if(!is_numeric($n))
        $n = 1;
    
    if(file_exists($filename)){
      // Comptabiliser votes
      $counter = filesize($filename);
    
      if($counter > $n){
        // Ouvrir le fichier
        $handle = smart_file_open($filename, 'wb', 3000);

        // Verrouiller le fichier
        flock($handle, LOCK_EX);

        // Calculer nombre de bytes à ajouter au fichier
        $strout = str_repeat(0x1, ($counter - $n));

        // Ecrire bytes au fichier
        $success = $counter = fwrite($handle, $strout);

        flock($handle, LOCK_UN); // Enlever le verrou
        fclose($handle);
          
      }else{
        // Si on doit enlever le même nombre de points ou plus
        // retourner 0 et supprimer le fichier
        $counter = 0;
        $success = unlink($filename);
      }

      // Return resultat
      if($success===false){ //Il y a eu une erreur
        return 'error';
      }else{ //Le vote a ete bien comptabilise
        return $counter;
      }
    }
  }
?>
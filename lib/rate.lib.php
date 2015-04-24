<?php
  function count_up($filename, $n=1){
    /**
     * @param $filename (String) - Absolute file name
     * @param $n (Integer)
     * @return (Integer) Total counter for $filename | (String) 'error' if error
     */ 
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
    /**
     * @param $filename (String) - Absolute file name
     * @param $n (Integer)
     * @return (Integer) Total counter for $filename | (String) 'error' if error
     */ 
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

//-----------------------------------------------
  function get_rate_for($photo_basename, $rating_filename){
    /**
     * @param $photo_basename (String)
     * @param $rating_filename (CSV file name)
     * @return N of points | 0 if no rates
     */ 
    $R = 0;
    
    if(!empty($photo_basename) && is_readable($rating_filename)){
      foreach(file($rating_filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line){
        $item = explode(';',$line,3);
        if($item[2]==$photo_basename){
          //$_HAS_RATED    = true;
          $R = $item[1];
        }
      }
    }
    
    return $R;
  }
?>
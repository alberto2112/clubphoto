<?php
  function exif_get_float($value) {
    $pos = strpos($value, '/');
    if ($pos === false) return (float) $value;
    $a = (float) substr($value, 0, $pos);
    $b = (float) substr($value, $pos+1);
    return ($b == 0) ? ($a) : ($a / $b);
  }
// -----------------------------------------------------------------
/*
  function exif_get_shutter(&$exif) {
    if (!isset($exif['ShutterSpeedValue'])) return false;
    $apex    = exif_get_float($exif['ShutterSpeedValue']);
    $shutter = pow(2, -$apex);
    if ($shutter == 0) return false;
    if ($shutter >= 1) return round($shutter) . 's';
    return '1/' . round(1 / $shutter) . 's';
  }
*/
// -----------------------------------------------------------------
  function exif_get_fstop(&$exif) {
    if (!isset($exif['ApertureValue'])) return false;
    $apex  = exif_get_float($exif['ApertureValue']);
    $fstop = pow(2, $apex/2);
    if ($fstop == 0) return false;
    return 'f/' . round($fstop,1);
  }
// -----------------------------------------------------------------
  function extract_exif($image_filename, $output_filename=false){
    // Extract EXTIF info
    $exif_ifd0 = @read_exif_data($image_filename ,'IFD0' ,0);
    $exif_exif = @read_exif_data($image_filename ,'EXIF' ,0);

    $notFound = "Unavailable"; // Error control

    if (@array_key_exists('Make', $exif_ifd0)) {
        $camMake = $exif_ifd0['Make'];
    } else { $camMake = $notFound; }

    if (@array_key_exists('Model', $exif_ifd0)) {
        $camModel = $exif_ifd0['Model'];
    } else { $camModel = $notFound; }

    if (@array_key_exists('ExposureTime', $exif_ifd0)) {
      $camExposure = exif_get_float($exif_ifd0['ExposureTime']);
      if ($camExposure == 0) $camExposure = $notFound;
      elseif ($camExposure >= 1) $camExposure = round($camExposure);
      else $camExposure = '1/' . round(1/$camExposure);
/*
      if (strpos($camExposure, '/') > 0) {
        $parts = explode("/", $camExposure);
        $camExposure = implode("/", array(1, $parts[1]/$parts[0]));
      }
*/
    } else { $camExposure = $notFound; }

    if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
        $camAperture = $exif_ifd0['COMPUTED']['ApertureFNumber'];
    } else { $camAperture = $notFound; }

    if (@array_key_exists('ISOSpeedRatings',$exif_exif)) {
        $camIso = $exif_exif['ISOSpeedRatings'];
    } else { $camIso = $notFound; }

    if (@array_key_exists('FocalLength',$exif_exif)) {
        $apex = exif_get_float($exif_exif['FocalLength']);
        //$flength = round($apex);
        $camFocal = round($apex);
        //$camFocal = $exif_exif['FocalLength'];
    } else { $camFocal = $notFound; }

    // Focus Distance
    if (@array_key_exists('FocusDistance', $exif_ifd0['COMPUTED'])) {
        $camFDistance = $exif_ifd0['COMPUTED']['FocusDistance'];
    } else { $camFDistance = $notFound; }

    if (@array_key_exists('FocalLengthIn35mmFilm',$exif_exif)) {
        $camFocal35 = $exif_exif['FocalLengthIn35mmFilm'];
    } else { $camFocal35 = $notFound; }

    // Correction d'exposition -> demande par le groupe
    if (@array_key_exists('ExposureBiasValue',$exif_exif)) {
        $camExBias = $exif_exif['ExposureBiasValue'];
    } else { $camExBias = $notFound; }

    if (@array_key_exists('Flash',$exif_exif)) {
		$fdata = $exif_exif['Flash'];

		if ($fdata == 0) $fdata = 'No Flash';
		else if ($fdata == 1) $fdata = 'Flash';
		else if ($fdata == 5) $fdata = 'Flash, strobe return light not detected';
		else if ($fdata == 7) $fdata = 'Flash, strob return light detected';
		else if ($fdata == 9) $fdata = 'Compulsory Flash';
		else if ($fdata == 13) $fdata = 'Compulsory Flash, Return light not detected';
		else if ($fdata == 15) $fdata = 'Compulsory Flash, Return light detected';
		else if ($fdata == 16) $fdata = 'No Flash';
		else if ($fdata == 24) $fdata = 'No Flash';
		else if ($fdata == 25) $fdata = 'Flash, Auto-Mode';
		else if ($fdata == 29) $fdata = 'Flash, Auto-Mode, Return light not detected';
		else if ($fdata == 31) $fdata = 'Flash, Auto-Mode, Return light detected';
		else if ($fdata == 32) $fdata = 'No Flash';
		else if ($fdata == 65) $fdata = 'Red Eye';
		else if ($fdata == 69) $fdata = 'Red Eye, Return light not detected';
		else if ($fdata == 71) $fdata = 'Red Eye, Return light detected';
		else if ($fdata == 73) $fdata = 'Red Eye, Compulsory Flash';
		else if ($fdata == 77) $fdata = 'Red Eye, Compulsory Flash, Return light not detected';
		else if ($fdata == 79) $fdata = 'Red Eye, Compulsory Flash, Return light detected';
		else if ($fdata == 89) $fdata = 'Red Eye, Auto-Mode';
		else if ($fdata == 93) $fdata = 'Red Eye, Auto-Mode, Return light not detected';
		else if ($fdata == 95) $fdata = 'Red Eye, Auto-Mode, Return light detected';
		else $fdata = 'Inconnu: ' . $fdata;

        $camFlash = $fdata;
    }else { $camFlash = $notFound; }

    // Create info
    $info_content = trim($camMake).';'
                   .trim($camModel).';'
                   .trim($camExposure).' s;'
                   .trim($camAperture).';'
                   .trim($camFDistance).';'
                   .trim($camExBias).' EV;'
                   .trim($camFocal).' mm;'
                   .trim($camFocal35).' mm;'
                   .trim($camIso).';'
                   .trim($camFlash);

    // Save info file
    if(empty($output_filename)){
      return $info_content;
    }else{
      file_put_contents($output_filename, $info_content);
    }
/*
     // Orientate
    if(@array_key_exists('Orientation',$exif_ifd0)){
     if($exif_ifd0['Orientation']!=1)
       exec('convert "'.$image_filename.'" -auto-orient "'.$image_filename.'"');
    }
*/
  }
// -----------------------------------------------------------------
  function install_photo($image_filename, $album_dest, $watermark_filename='', $thumb_size='192x192', $medium_size='640x480', $large_size='1280x1280'){
    /**
     * @param $image_filename => absolute filename
     * @param $album_dest => absolute path
     * @param $watermark_filename => absolute filename. If null = no watermark
     * @return String => image final filename
     */

      // Make necesary dirs
      @mkdir($album_dest.'/logs',0755,true);
      @mkdir($album_dest.'/photos/thumbs',0755,true);
      @mkdir($album_dest.'/photos/medium',0755);
      @mkdir($album_dest.'/photos/large',0755);

    // Extraire basename
      $output_filename = basename($image_filename);


    //If class Imagick not exists load bricoled Imagick class with GD library
    if(!class_exists('Imagick'))
        include realpath( dirname(__FILE__)).'/imagick.class.php';

    $photo = new Imagick();

     // Do large and watermark
     //exec('mogrify -resize '.$large_size.' +repage "'.$image_filename.'"');
    $size = explode('x',$large_size,2);
    $photo->readImage($image_filename);
    $photo->scaleImage($size[0],$size[1],true);
    // Do watermark
    if($watermark_filename!= '' && $watermark_filename != false && is_readable($watermark_filename)){
      $wm = new Imagick($watermark_filename);

      $iWidth = $photo->getImageWidth();
      $iHeight = $photo->getImageHeight();
      $wWidth = $wm->getImageWidth();
      $wHeight = $wm->getImageHeight();

      if ($iHeight < $wHeight || $iWidth < $wWidth) {
          // resize the watermark
          $wm->scaleImage($iWidth, $iHeight);
          $wWidth = $wm->getImageWidth();
          $wHeight = $wm->getImageHeight();
      }

      // calculer position
      $x = ($iWidth - $wWidth) - 25;
      $y = ($iHeight - $wHeight) - 15;

      $photo->compositeImage($wm, imagick::COMPOSITE_OVER, $x, $y);
    }
    $photo->writeImage($album_dest.'photos/large/'.$output_filename);

    // Do medium
    $size = explode('x',$medium_size,2);
    $photo->readImage($image_filename);
    $photo->scaleImage($size[0],$size[1],true);
    $photo->writeImage($album_dest.'photos/medium/'.$output_filename);

    // Do thumbs
    $size = explode('x',$thumb_size,2);
    $photo->cropThumbnailImage(intval($size[0]),intval($size[1]));
    $photo->writeImage($album_dest.'photos/thumbs/'.$output_filename);

    $photo->clear();

/*
     // Do and install thumbs
     //exec('convert "'.$image_filename.'" -resize '.$thumb_size.'^ -gravity center -extent '.$thumb_size.' "'.$album_dest.'photos/thumbs/'.$output_filename.'"');

     // Do watermark
     if($watermark_filename=='' || $watermark_filename===false){
        // Install large version
        copy($image_filename, $album_dest.'photos/large/'.$output_filename);
     }else{
        // Install large version with watermark
        exec('composite -gravity SouthEast "'.$watermark_filename.'" "'.$image_filename.'" "'.$album_dest.'photos/large/'.$output_filename.'"');
     }

     // Do and install medium
     exec('mogrify -resize '.$medium_size.' +repage "'.$image_filename.'"');
     copy($image_filename, $album_dest.'photos/medium/'.$output_filename);
*/
    return $output_filename;
  }

// -----------------------------------------------------------------
  function photo_to_trash($codalbum, $photo_filename){
    
  }
    
// -----------------------------------------------------------------

  function uninstall_photo($album_path, $photo_filename, $uninstall_from_trash=false)
    /**
     * @param $uninstall_from_trash => False | String (trash dir)
     * 
     * @return disk space libered
     */ 
  {
    
      // FIXME: Comprobar si los ficheros existen y olvidarse de $uninstall_from_trash
      
      //Delete thumbnail
      if($uninstall_from_trash===false){
        $used_disk = @filesize($album_path.'/photos/thumbs/'.$photo_filename);
        @unlink($album_path.'/photos/thumbs/'.$photo_filename);
      }else{
        $used_disk = @filesize($album_path.'/'.$uninstall_from_trash.$photo_filename);
        @unlink($album_path.'/'.$uninstall_from_trash.$photo_filename);
      }

      //Delete medium photo
      $used_disk += @filesize($album_path.'/photos/medium/'.$photo_filename);
      @unlink($album_path.'/photos/medium/'.$photo_filename);

      //Delete large photo
      $used_disk += @filesize($album_path.'/photos/large/'.$photo_filename);
      @unlink($album_path.'/photos/large/'.$photo_filename);

      //Delete photo stats
      foreach (glob($album_path.'/photos/votes/'.$photo_filename.'*') as $file2del) {
        @unlink($file2del);
      }
      //Delete photo infos
      //@unlink($album_path.'/photos/'.$photo_filename.'.csv'); // Ne pas supprimer ce fichier
    
      return $used_disk;
  }

// -----------------------------------------------------------------

  function delete_photo($codalbum, $photo_filename, $delete_from_trash=false)
    /**
     * Alias of uninstall_photo();
     */ 
  {
    return uninstall_photo($codalbum, $photo_filename, $delete_from_trash);
  }
?>
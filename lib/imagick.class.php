<?php
class Imagick{
  public $file_in='';   // Image in filename
  public $file_out='';  // Image out filename
  public $image_temp;    // Temp working image
  public $image_in_width=0;
  public $image_in_height=0;
  
  const COMPOSITE_OVER = 0;
# --------------------------------------------------------
  function __construct($im_filename=''){
	$this->readImage($im_filename);
  }
# --------------------------------------------------------
  function readImage($im_filename){
    if($im_filename!=''){
		$this->file_in = $im_filename;
		$filext=strtoupper(substr($im_filename,-4));
		switch($filext){
			case '.PNG':
				$this->image_temp = imagecreatefrompng($im_filename);
				break;

			case '.GIF':
				$this->image_temp = imagecreatefromgif($im_filename);
				break;

			case 'JPEG':
			case '.JPG':
            default:
				$this->image_temp = imagecreatefromjpeg($im_filename);
				break;
		}
		$this->getSizes();
	}
  }
# --------------------------------------------------------
  function scaleImage($toWidth,$toHeight,$bestfit=false){
    /** Esta función redimensiona un archivo JPG manteniendo
     * su radio de aspecto original dentro de los límites
     * $toWidth y $toHeight.
     * Parámetros:
     * $originalImage: Nombre del archivo en formato JPG
     * a redimensionar.
     * $toWidth: Ancho máximo de la imágen redimensionada.
     * $toHeight: Alto máximo de la imágen redimensionada.
     * Devuelve una imágen en memoria con las proporciones
     * correctas.
     */

    //list($width, $height) = getimagesize($this->file_in);
    if($this->image_in_width==0 || $this->image_in_height==0) {
            $this->getSizes();
    }

    if(($this->image_in_width > $toWidth || $this->image_in_height > $toHeight) || $bestfit===false ){
      // Obtener relacion aspecto imagen
      $logos = $this->image_in_width / $this->image_in_height;

      if($logos < 1){
        // La imagen es mas alta que ancha
        $new_width = $toHeight * $logos;
        $new_height = $toHeight;
      }else{
        // La imagen es cuadrada o mas ancha que alta
        $new_width = $toWidth;
        $new_height = $toWidth / $logos;
      }
    }else{
      // Si la imagen de entrada es mas pequena que las dimensiones deseadas en la salida la imagen no se redimensiona
      $new_width = $this->image_in_width;
      $new_height = $this->image_in_height;
    }

    // Reservamos espacio en memoria para la nueva imágen
    $imageResized = imagecreatetruecolor($new_width, $new_height);

    // Cargamos la imágen original y redimensionamos
    //$img_tmp     = imagecreatefromjpeg ($this->file_in);
    imagecopyresampled($imageResized, $this->image_temp,
                       0, 0, 0, 0,
                       $new_width, $new_height,
                       $this->image_in_width, $this->image_in_height);

    // Devolvemos la nueva imágen redimensionada.
    imagedestroy($this->image_temp);
    $this->image_temp = $imageResized;

	$this->image_in_width = $new_width;
	$this->image_in_height = $new_height;

	//imagedestroy($imageResized);
  }
# --------------------------------------------------------
  function getSizes(){
    if($this->file_in != '' || is_readable($this->file_in)){
		list($width, $height) = getimagesize($this->file_in);
		$this->image_in_width = $width;
		$this->image_in_height = $height;
	}
  }
# --------------------------------------------------------
  function getSize(){
    if($this->file_in != '' || is_readable($this->file_in)){
		list($width, $height) = getimagesize($this->file_in);
		$this->image_in_width = $width;
		$this->image_in_height = $height;
		return array('columns'=>$width, 'rows'=>$height);
	}
  }
# --------------------------------------------------------
  function getImageWidth(){
	if($this->image_in_width == 0 || $this->image_in_height == 0 ) $this->getSizes();
	return $this->image_in_width;
  }
# --------------------------------------------------------
  function getImageHeight(){
	if($this->image_in_width == 0 || $this->image_in_height == 0 ) $this->getSizes();
	return $this->image_in_height;
  }
# --------------------------------------------------------
  function compositeImage($composite_object, $composite_operator, $x, $y, $channel = 0){
	imagecopymerge($this->image_temp, $composite_object, $x, $y, 0, 0, $composite_object->getImageWidth, $composite_object->getImageHeight, 50);
  }
# --------------------------------------------------------
  function writeImage($out_filename){
    $this->file_out = $out_filename;
    //return imagegd($this->image_temp, $out_filename);
	return imagejpeg($this->image_temp, $out_filename, 90);
  }
# --------------------------------------------------------
  function cropThumbnailImage($toWidth,$toHeight=-1){

    if($toHeight==-1) $toHeight = $toWidth;
/*
	$ini_filename = 'test.JPG';
	$im = imagecreatefromjpeg($ini_filename );

	$ini_x_size = getimagesize($ini_filename )[0];
	$ini_y_size = getimagesize($ini_filename )[1];
*/
	//the minimum of xlength and ylength to crop.
//	$crop_measure = min($this->image_in_width, $this->image_in_height);

	$original_aspect = $this->image_in_width / $this->image_in_height;
	$thumb_aspect = $toWidth / $toHeight;
	if ( $original_aspect >= $thumb_aspect )
	{
	   // If image is wider than thumbnail (in aspect ratio sense)
	   $new_height = $toHeight;
	   $new_width = $this->image_in_width / ($this->image_in_height / $toHeight);
	}
	else
	{
	   // If the thumbnail is wider than the image
	   $new_width = $toWidth;
	   $new_height = $this->image_in_height / ($this->image_in_width / $toWidth);
	}

	$thumb = imagecreatetruecolor( $toWidth, $toHeight );

	// Resize and crop
	imagecopyresampled($thumb,
					   $this->image_temp,
					   0 - ($new_width - $toWidth) / 2, // Center the image horizontally
					   0 - ($new_height - $toHeight) / 2, // Center the image vertically
					   0, 0,
					   $new_width, $new_height,
					   $this->image_in_width, $this->image_in_height);

    imagedestroy($this->image_temp);
	$this->image_temp = $thumb;

	$this->image_in_width = $new_width;
	$this->image_in_height = $new_height;

	//imagedestroy($thumb);

	//$to_crop_array = array('x' =>0 , 'y' => 0, 'width' => $crop_measure, 'height'=> $crop_measure);
	//$thumb_im = imagecrop($im, $to_crop_array);

	//imagejpeg($thumb_im, 'thumb.jpeg', 94);
  }
# --------------------------------------------------------
  function destroy(){
    return imagedestroy($this->image_temp);
  }
# --------------------------------------------------------
  function clear(){
    $this->destroy();
  }
# --------------------------------------------------------
}
?>
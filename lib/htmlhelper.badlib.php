<?php
 /**
 * Stupid HTML helper
 */

//-------------------------------------------------------
  function print_footer($filename){
      if(is_readable($filename)){
        include $filename;
      }
  }
//-------------------------------------------------------
function add_footer($filename){
  /**
   * alias of print_footer();
   */ 
  print_footer($filename);
}
//-------------------------------------------------------
  function insert_css($stylesheet, $version=null, $media='screen'){
    if(empty($version)){
      echo '    <link rel="stylesheet" media="'.$media.'" href="'.$stylesheet.'" type="text/css" />'."\n";
    }else{
      echo '    <link rel="stylesheet" media="'.$media.'" href="'.$stylesheet.'?v='.$version.'" type="text/css" />'."\n";
    }
  }
//-------------------------------------------------------
  function insert_js($script, $version=null){
    if(empty($version)){
      echo '<script src="'.$script.'"></script>';
    }else{
      echo '<script src="'.$script.'?v='.$version.'"></script>';
    }
  }
//-------------------------------------------------------
 ?>
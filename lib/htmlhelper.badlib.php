<?php
 /**
 * Stupid HTML helper
 */

//-------------------------------------------------------
  function add_footer($filename){
    /**
     * alias of print_footer();
     */ 
    print_footer($filename);
  }
//-------------------------------------------------------
  function getHTML4CheckBoxState($var, $equals='1'){
    $str2Return = '';
    if(!empty($var)){
      if($var==$equals){
        $str2Return = 'checked="checked" ';
      }
    }
    return $str2Return;
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
//-------------------------------------------------------
  function print_footer($filename){
      if(is_readable($filename)){
        include $filename;
      }
  }
 ?>
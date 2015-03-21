<?php
    function catch_include( $filename, $catch_error=false )
    /**
     * @desc    Incluye un archivo si este existe, si no existe devuelve la variable $catch_error
     * @param   $filename     string: Ruta absoluta del fichero a incluir
     * @param   $catch_error  string | integer | boolean
     * @return  include( $filename ) | $catch_error
     */
    {
        if(file_exists( $filename ) && is_readable( $filename ))
            return include( $filename );
        else
            return $catch_error;
    }

# ------------------------------------------------------------------------------------

  function human_readable_filesize($bytes) {
    $type = array('', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
    $idx = 0;

    while($bytes >= 1024){
      $bytes /= 1024;
      $idx++;
    }

    return(round($bytes, 2).' '.$type[$idx].'o');
  }

# ------------------------------------------------------------------------------------

    function read_dir($dir,$filetypes='',$returnfilename=false,$banderas=null,$recursive=false,$n_recurses=0) {
    /**
     * GLOB_MARK        - Agrega una barra a cada elemento devuelto
     * GLOB_NOSORT      - Devuelve los archivos como aparecen en el directorio (sin ordenar)
     * GLOB_NOCHECK     - Devuelve el patron de busqueda si no se han encontrado archivos coincidentes
     * GLOB_NOESCAPE    - Las barras invertidas no son usadas para escapar metacaracteres
     * GLOB_BRACE       - Expande {a,b,c} para que coincida con 'a', 'b', o 'c'
     * GLOB_ONLYDIR     - Devuelve unicamente entradas de directorios que coinciden con el patron
     * GLOB_ERR         - Detenerse en errores de lectura (como directorios inaccesibles), los errores son ignorados por omision.
     */
        if( empty( $dir ) || $n_recurses > 64){
            return false;
        } else {
            $arbol=array();
            $n_recurses++;
            $dir=(substr($dir,-1)!='/')? $dir.'/' : $dir;
            if( empty( $filetypes ) || $filetypes == '*'){
                foreach (glob($dir.'*',$banderas) as $filename) {
                  $arbol[] = $filename;
                  //array_push($arbol, $filename);
                  if(is_dir($filename) && $recursive == true)
                      $arbol= array_merge($arbol, read_dir($filename,$filetypes,$returnfilename,$banderas,$recursive,$n_recurses));
                }
            } else {
                $arr_files = glob($dir.$filetypes,$banderas);
                if(is_array($arr_files) && count($arr_files) > 0){
                  foreach($arr_files as $filename) {
                    //if(preg_match($filetypes, $filename))
                        $arbol[] = $filename;

                    if(is_dir($filename) && $recursive == true)
                      $arbol = array_merge($arbol, read_dir($filename,$filetypes,$returnfilename,$banderas,$recursive,$n_recurses));
                  }
                }
            }

            if ($returnfilename===true) {
                foreach($arbol as $i=>$a){
                    //$arbol[$i] = str_replace(PATH_private,PATH_public,$a);
                    //$arbol[$i] = PATH_public.substr($a,strlen(PATH_private));
                    $arbol[$i] = basename($a);
                }
            }

            # Ordenar arbol
            sort($arbol);
            reset($arbol);

            return $arbol;
        }
    }

# ------------------------------------------------------------------------------------

function list_dirs($path, $basenamesonly=true){
  /**
   * @param $path => String, path to scan (always ends width slash '/')
   * @param $basenamesonly => Boolean, (false)return integral root, (true)return folders base name
   * return array( directories )
   */
  $folders = array();
  
  if(!empty($path)){
    foreach( glob($path.'*', GLOB_ONLYDIR) as $folder){
      if($folder!='.' && $folder!='..'){
        $folders[] = ($basenamesonly===true)? basename($folder) : $folder;
      }
    }
  }
  
  return $folders;
}

# ------------------------------------------------------------------------------------

    function rmdir_recurse($d,$savethis=false) {
        $d= rtrim($d, '/').'/';
        $result=true;
        if(file_exists($d)) {
            $handle = opendir($d);
            for (;false !== ($file = readdir($handle));)
                if($file != "." and $file != ".." )
                {
                    $fullpath= $d.$file;
                    if( is_dir($fullpath) )
                    {
                        $result *= rmdir_recurse($fullpath);
                        (file_exists($fullpath)) ? rmdir($fullpath) : null;
                    }
                    else
                      unlink($fullpath);
                }
            closedir($handle);
            ($savethis==false && $result)?rmdir($d) : null;
            return $result;
         }
         else
             return false;
    }
# ------------------------------------------------------------------------------------

    function clean_file_name($file_name)
    /**
     * @desc    Limpia un nombre de fichero o directorio a fin de evitar problemas de tipo: /home//fichero.txt ; /home/../../.././\/\///etc/passwd
     * @param   $file_name   String: Nombre del fichero o directorio. No tiene por que ser un nombre absoluto
     * @return  String
     */
    {
        $file_name = str_replace('\\','', $file_name);
/*
        $file_name = str_replace('..','', $file_name);
        while(strpos($filename, '//')){
            $filename = str_replace('//','/', $filename);
        }
*/
        return preg_replace(array('/(\.+)/','/(\/+)/'), array('.','/'),$file_name);
    }

#--------------------------------------------------------------------------------------------------------

    function count_files( $folder, $filetypes='' )
    {
        if(is_readable( $folder ))
            return count(read_dir($folder, $filetypes));
        else
            return 0;
    }

# -------------------------------------------------------------------------------------------------------

    function file_line_count( $filename )
      /**
       * return Integer: lines in file OR Integer: -1 if file is not readable
       */ 
    {
      $linecount = 0;
      
      if(is_readable($filename)){
        $handle = fopen($filename, "r");

        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }

        fclose($handle);
        return $linecount;
      }else{
        return -1;
      }
    }

#--------------------------------------------------------------------------------------------------------

  function smart_file_open($filename, $method='r', $timeout = 3000)
  /**
   * @param $timeout = milliseconds
   * @return false | resource
   */ 
  {
    // Prevention d'ecriture simultanee
    if(file_exists($filename)){
      $i=0;
      $timeout /= 100;
      if($method=='r' || $method=='rb' || $method=='br'){
        // Attendre a que le fichiers soit disponible
        while(!is_readable($filename) && $i < 30){
          usleep(100000); //100ms
          $i++;
        }
      } else {
        // Attendre a que le fichiers soit disponible
        while(!is_writable($filename) && $i < 30){
          usleep(100000); //100ms
          $i++;
        }
      }
    }
    
    //TODO. Comprobar si el directorio es escribible
      
    try{
      return fopen($filename, $method);
    } catch(Exception $e){
      return false;
    }
  }

#--------------------------------------------------------------------------------------------------------

  function update_quota($quota_filename, $size){
    if(!empty($size) && is_numeric($size) && is_readable($quota_filename)){
      $current_used_quota = file_get_contents($quota_filename,null,null,null,9);
      $current_used_quota += $size;
      file_put_contents($quota_filename, $current_used_quota, LOCK_EX);
      
      return $current_used_quota;
    } else
      return false;
  }
#--------------------------------------------------------------------------------------------------------

  function update_quota_file($quota_filename, $size){
    /**
     * alias of update_quota()
     */ 
    return update_quota($quota_filename, $size);
  }

#--------------------------------------------------------------------------------------------------------
?>

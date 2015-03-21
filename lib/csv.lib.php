<?php
  function read_csv($csv_file,$parsing_str=';'){
    if(is_readable("$csv_file")){
      $DB = array();
      $fh = fopen($csv_file, "rb");
      //$i=0;

      while (!feof($fh) ) {
        $line = fgets($fh);
        $line = preg_replace('#[\r\n]#', '', $line);
        if(substr($line,0,1) != '#'){
          $items = explode($parsing_str, $line);
        }
      }
      fclose($fh);
      return $items;
    }
  }

  function smart_read_csv($csv_file,$parsing_str=';'){}
?>
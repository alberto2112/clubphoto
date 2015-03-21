<?php
  class LOG{
    public $log_filename = '';
    public $add_timestamp = true;
    public $fopen_mode = 'a';

    private $file_opened = false;
    private $file_handle = false;
// ----------------------------------------------------------
    public function __construct($filename, $timestamp=true, $truncate=false){
      $this->add_timestamp = $timestamp;
      $this->log_filename= $filename;
      $this->fopen_mode = ($truncate===false)? 'a':'w';
    }
// ----------------------------------------------------------
    public function __destruct(){
      $this->close();
    }
// ----------------------------------------------------------
    public function open($filename){
      $fexists = false;
      $this->log_filename = $filename;
      // Prevention d'ecriture simultanee
      if(file_exists($filename)){
        $fexists = true;
        $i=0;
        // Attendre a que le fichiers soit disponible
        while(!is_writable($filename) && $i < 30){
          usleep(100000); //100ms
          $i++;
        }
      }

      if($fexists){
        $this->file_handle = fopen($filename, $this->fopen_mode);
        $votes = filesize($filename);
      }elseif($filename!=''){
        $this->file_handle = fopen($filename, $this->fopen_mode);
        $votes = 0;
      }

      if($this->file_handle!==false){
        flock($this->file_handle, LOCK_EX); // Verrouiller le fichier
        $this->file_opened = true;
      }
    }
// ----------------------------------------------------------
    public function close(){
      if($this->file_opened){
        flock($this->file_handle, LOCK_UN); // Deverrouiller le fichier
        fclose($this->file_handle);
        $this->file_opened = false;
      }
    }
// ----------------------------------------------------------
    public function insert($string, $close_after_insert=true, $newline="\n"){
      $success = false;
      $header = '';
      if($this->file_opened == false)
        $this->open($this->log_filename);

      if($this->file_opened == true){
        //rewind($this->file_handle); // Place le pointeur de fichier au debut du flux || Ne marche pas!!!
        if($this->add_timestamp)
          $header = date('Y-m-d H:i:s').' - ';

        if($string!=''){
          $success = fwrite($this->file_handle, $header.$string.$newline);
        }

        if($close_after_insert)
          $this->close();
      }
      return $success;
    }
// ----------------------------------------------------------
    public function add($string, $newline="\n"){  // Alias de insert
      $this->insert($string, $newline);
    }
  }
?>
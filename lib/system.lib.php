<?php
/**
 * GETTERS
 ***********/
  function getRequest_param( $query_string, $return_if_false = null )
  {
      return (@(isset( $_REQUEST[$query_string] ) &&  $_REQUEST[$query_string] !=='' || $_REQUEST[$query_string]===0 ) ? trim(strip_tags($_REQUEST[$query_string])) : $return_if_false);
  }
//-------------------------------------------------------
  function getClient_ip()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) // Check for ip from share internet
      $IP = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) // Check for the Proxy User
      $IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
      $IP = $_SERVER['REMOTE_ADDR'];

    return $IP;
  }
// -------------------------------------------------------
  function get_arr_value($arr, $arr_key, $if_false=''){
    if(@array_key_exists($arr_key, $arr)){
      return $arr[$arr_key];
    } else
      return $if_false;
  }
//-------------------------------------------------------
/**
 * OTHERS
 ***********/
  function tinyURL($num_chars=5, $chars='123456789abcdefghijklmnopqrstuvwxyz')
  {
	$i = 0;
	//$chars = '123456789abcdefghijklmnopqrstuvwxyz'; //keys to be chosen from
	$keys_length = strlen($chars);
	$url  = '';

	while($i<$num_chars)
 	{
		$rand_num = mt_rand(1, $keys_length-1);
		$url .= $chars[$rand_num];
		$i++;
	 }
 	return $url;
  }
//-------------------------------------------------------
  function make_rkey($num_chars=16, $chars='0123456789abcdefABCDEF'){
    /**
     * Alias of tinyURL()
     */ 
    return tinyURL($num_chars, $chars);
  }
//-------------------------------------------------------
  function isHTTPS() {
    return
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || $_SERVER['SERVER_PORT'] == 443;
  }
//-------------------------------------------------------
  function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
  }
//-------------------------------------------------------
  function clear_request_param($param, $pattern='A-Za-z0-9', $max_length=1024, $do_htmlentities=true)
  {
    if(is_bool($param)){
      // Ne pas traiter les parametres booleans
      return $param;
    }elseif(strlen($param) > 0){
      $out_str = $param;

      // Corriger possibles erreurs de saisie
      if(!is_numeric($max_length))  $max_length = 1024;
      if(is_numeric($pattern))      $pattern = '0-9';

      // Remplacer caracteres non desirables
      if($pattern!=false && $pattern!=''){
        $out_str = preg_replace('/[^'.$pattern.']/', '', $out_str, -1);
      }

      // Couper chaine si celle-ci est trop longue
      if($max_length > 0 && strlen($out_str) > $max_length){
        $out_str = substr($out_str, 0, $max_length);
      }

      // Traduire chaine a balises html
      if($do_htmlentities){
        $out_str = htmlentities($out_str, ENT_QUOTES, CHARSET);
      }

      // Rendre le resultat
      return $out_str;
    }else
      return '';
  }
//-------------------------------------------------------
  function slug($string) {
    /**
     * Convert une chaine de caracteres en friendly URL
     */
    
    $characters = array(
        'Á' => 'A', 'Ç' => 'c', 'É' => 'e', 'Í' => 'i', 'Ñ' => 'n', 'Ó' => 'o', 'Ú' => 'u',
        'á' => 'a', 'ç' => 'c', 'é' => 'e', 'í' => 'i', 'ñ' => 'n', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u'
        );
    
    $string = strtr($string, $characters); // Remplacer caracteres especiaux
    $string = strtolower(trim($string));   // Passer tout a minuscules et enlever les espaces de debut et fin
    $string = preg_replace('/[^a-z0-9-]/', '-', $string); // Remplacer autres caracteres especiaux par '-'
    $string = preg_replace('/-+/', '-', $string);
    
    if(substr($string, strlen($string) - 1, strlen($string)) === '-') {
      $string = substr($string, 0, strlen($string) - 1);
    }
    return $string;
  }
?>
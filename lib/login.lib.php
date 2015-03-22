<?php
//----------------------------------------------------------
  function is_logged(){
    /**
     * @return Boolean: True | False
     */
    @session_start();
    return array_key_exists('UID', $_SESSION);
    
  }
//----------------------------------------------------------
  function is_admin($level = 2, $strict_mode=false){
    /**
     * USER LEVEL (1) = SUPER ADMIN
     * USER LEVEL (2) = ADMIN
     * @return Boolean: True | False
     */ 
    if(array_key_exists('ULEVEL', $_SESSION) && is_numeric($_SESSION['ULEVEL'])){
      if($strict_mode){
        return ($_SESSION['ULEVEL'] == $level);
      }else{
        return ($_SESSION['ULEVEL'] =< $level);
      }
    }else{
      return false;
    }
  }
//----------------------------------------------------------
  function is_super_admin($level = 1){
    /**
     * alias of #is_admin($level);
     */ 
    
      return is_admin($level, true);
    }
  }
//----------------------------------------------------------
  function do_login($userid, $password, $password_list, $givenPwdIsAHash=false){
    /**
     * @param $password - String;
     * @param $password_list - Array: List of passwords array('pass_1;uid;utype;urights;uname'[, 'pass_2;uid;utype;urights;uname'[, ...]] )
     * 
     * return True | False
     */
    
    $great_login = false;
    
    if(!$givenPwdIsAHash)
      $password = md5($password);
    
    if(is_array($password_list)){
      foreach($password_list as $user){
        $U = explode(';',$user,5);
        if($userid==$U[1]) {
          if($password==$U[0]){
            $great_login = true;
          }
          break;
        }
      }
    }else{
      $U = explode(';',$password_list,5);
      if($userid > 0){
        if($userid==$U[1] && $password==$U[0])
          $great_login = true;
      }else{
        if($password==$U[0])
          $great_login = true;
      }
    }
    
    // Start session
    if($great_login){
      session_start();
      
      $_SESSION['UID']    = $U[1];
      $_SESSION['ULEVEL'] = $U[2];
      $_SESSION['URIGHTS']= $U[3];
      $_SESSION['UNAME']  = rtrim($U[4]);
    }

    return $great_login;
  }
//----------------------------------------------------------
  function do_logout(){
    session_start();
	session_unset(); // Detruire toutes les variables de session
	session_destroy();
  }
//----------------------------------------------------------
?>
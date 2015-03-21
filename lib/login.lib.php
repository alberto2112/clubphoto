<?php
//----------------------------------------------------------
  function is_logged(){
    /**
     * @return True | False
     */
    //return empty(session_id());
    @session_start();
    if(isset($_SESSION['ADMIN'])){
      return ($_SESSION['ADMIN']=='true');
    }else{
      return false;
    }
  }
//----------------------------------------------------------
  function is_admin(){
    #Alias from is_logged();
    return is_logged();
  }
//----------------------------------------------------------
  function is_super_admin(){
    //TODO is_super_admin()
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
      
      $_SESSION['ADMIN']='true';
      $_SESSION['UID']=$U[1];
      $_SESSION['ULEVEL']=$U[2];
      $_SESSION['URIGHTS']=$U[3];
      $_SESSION['UNAME']=rtrim($U[4]);
    }

    return $great_login;
  }
//----------------------------------------------------------
  function do_logout(){
    session_start();
	session_unset(); // Detruire toutes les variables de session
    //session_unset('ADMIN');
	session_destroy();
  }
//----------------------------------------------------------
?>
<?php
  function get_subscriptors($subscriptors_dir, $all=false){
    /**
     * @param $subscriptors_dir (String) Directory of push.N.config.php
     * 
     * @return array([UID]=>array()) | false
     */ 
    $subscriptors = array();
    $p_start = strlen($subscriptors_dir.'push.');
    
    foreach(glob($subscriptors_dir.'push_*.config.php', GLOB_NOSORT) as $subscriptor){
      //$p_start = strrpos($subscriptor, '/push.')+6;
      $UID = substr($subscriptor, $p_start, -11);
      if(is_readable($subscriptor)){
        $stor_config = include $subscriptor;
        if($all==true || $stor_config['sendnotifications']=='1'){
          $subscriptors[$UID]=$stor_config;
        }
      }
    }
    
    return (empty($subscriptors))? false : $subscriptors;
  }
//----------------------------------------
  function get_susbcriptors_for($event, $subscriptors_dir){
    $subscriptors = array();
    
    if(is_array($subscriptors_dir))
      $subscriptors_raw = $subscriptors_dir;
    else
      $subscriptors_raw = get_subscriptors($subscriptors_dir);
    
    if(!empty($subscriptors_raw)){
      foreach($subscriptors_raw as $subscriptor){
        if(array_key_exists($event, $subscriptor)){
          if($subscriptor[$event]=='1'){
            $subscriptors[] = $subscriptor;
          }
        }
      }
    }
    
    return $subscriptors;
  }
//----------------------------------------
  function send_push_to($subscriptors, $instapush_inst, $event, $trackers){
    /**
     * @param $instapush_inst (Object) - Instapush instance
     * @param $trackers (Array)
     * 
     * @return boolean
     */ 
    if(!empty($subscriptors) && !empty($instapush_inst) && !empty($event)){
      $result = true;
      foreach($subscriptors as $subscriptor){
        if($subscriptor[$event]=='1'){
          $instapush_inst->appId = $subscriptor['appid'];
          $instapush_inst->appSecret = $subscriptor['appsecret'];

          $result *= $instapush_inst->track($event, $trackers);
        }
      }
      
      return $result;
    }else
      return false;
  }
//----------------------------------------
//----------------------------------------
?>
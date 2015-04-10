<?php

//-----------------------------------------------

  function out_of_date($date_from, $date_to, $return_integer=false){
    /**
     * @param $date_from (String) = dd/mm/yyyy
     * @param $date_to (String) = dd/mm/yyyy
     * @param $return_integer (Boolean)
     * return (Boolean) true | false if $return_integer = false
     *        (Integer) if $return integer = true
     *                  1 => if date() > $date_to
     *                  0 => if date() is near $date_from && $date_to
     *                 -1 => if date() < $date_from
     */
    
    $TO_RETURN = 0;
    
    if(!empty($date_from) && !empty($date_to)){
      $DATE_FROM = (empty($date_from))? false : explode('/', $date_from, 3);
      $DATE_TO   = (empty($date_to))?   false : explode('/', $date_to, 3);
      
      if(!empty($DATE_FROM) && time() <= mktime(0,0,0, $DATE_FROM[1], $DATE_FROM[0], $DATE_FROM[2])) // Si la periode n'a pas commence
      {
        $TO_RETURN = -1;
      }
      elseif(!empty($DATE_TO) && time()-(3600 * 24) >= mktime(0,0,0, $DATE_TO[1], $DATE_TO[0], $DATE_TO[2])) // Si la periode est depasee
      {
        $TO_RETURN = 1;
      }
    }

    return ($return_integer == false)? ($TO_RETURN != 0) : $TO_RETURN;
  }
?>
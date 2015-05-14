<?php
  $_dfac_UF = date('d/m/Y');                            //UPLOAD FROM
  $_dfac_UT = date('d/m/Y',time() + (7 * 24 * 3600));   //UPLOAD TO
  $_dfac_VF = $_dfac_UT;                                //VOTE FROM
  $_dfac_VT = date('d/m/Y',time() + (14 * 24 * 3600));  //VOTE TO

  return array(
      'albumcode'=>'',
      'albumname'=>date('d.m.Y'),
      'allowupload'=>'1',
      'allowphotomanag'=>'1', // 0=Never, 1=On upload periode, 2=Allways
      'uploadslimit'=>'6',
      'upload-from'=>$_dfac_UF,
      'upload-to'=>$_dfac_UT,
      'allowvotes'=>'1',
      'allowselfrating'=>'0',
      'allowcomments'=>'0',
      'hidecammodelonrate'=>'1',
      'showrateforuploads'=>'1',
      'vote-from'=>$_dfac_VF,
      'vote-to'=>$_dfac_VT,
      'showranking'=>'1', // 0=Never, 1=After rating periode, 2=Allways
      'watermark'=>'1',
      'antitriche'=>'1',
      'ratemethod'=>'stars',
      'RKEY'=>''
  );
?>
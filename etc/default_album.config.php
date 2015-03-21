<?php
  return array(
      'albumname'=>date('d.m.Y'),
      'allowupload'=>'1',
      'uploadslimit'=>'6',
      'upload-from'=>date('d/m/Y'),
      'upload-to'=>date('d/m/Y',time() + (7 * 24 * 60 * 60)),
      'allowvotes'=>'1',
      'vote-from'=>date('d/m/Y',time() + (7 * 24 * 60 * 60)),
      'vote-to'=>date('d/m/Y',time() + (14 * 24 * 60 * 60)),
      'watermark'=>'1',
      'antitriche'=>'1',
      'ratemethod'=>'stars',
      'RKEY'=>''
  );
?>
<?php
  include 'pel/autoload.php';
  //use lsolesen\pel\PelJpegComment;
  //use lsolesen\pel\PelJpegContent;
  //use lsolesen\pel\PelJpegMarker;
  //use lsolesen\pel\PelException;
  use lsolesen\pel\Pel;
  use lsolesen\pel\PelTag;
  //use lsolesen\pel\PelDataWindow;
  use lsolesen\pel\PelJpeg;
  use lsolesen\pel\PelExif;
  //use lsolesen\pel\PelTiff;
  //use lsolesen\pel\PelEntry;



  $orig = new pelJpeg('/tmp/a.jpg');
  $dest = new pelJpeg('/tmp/b.jpg');

  $exif = $orig->getExif();
  //$tiff = $exif->getTiff(); // No vale pa na
  //$ifd0 = $tiff->getIfd(); // No vale pa na
  //$iso_a = $ifd0->getEntry(PelTag::ISO_SPEED_RATINGS); // No funciona

  $dest->setExif($exif);


  //$exof = $dest->getExif();
  //$orig->getIfd();
  //$dest->setIfd($orig->getIfd());
  //$toff = $exof->setTiff( $tiff );
  //$ofd0 = $toff->getIfd();
  //$iso_b = $ofd0->getEntry(PelTag::ISO_SPEED_RATINGS);

  //$iso_b->setValue( $iso_a );

  $dest->saveFile('/tmp/c.jpg');
?>
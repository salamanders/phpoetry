<?php

class ImageThumb {

  public static function saveUploadImage($form_name, $max_dim = 300) {
    if (!isset($_FILES[$form_name])) {
      throw new exception('No file uploaded from form.');
    }
    if ($_FILES[$form_name]['error'] != UPLOAD_ERR_OK) {
      throw new exception("Error with file upload:" . $_FILES[$form_name]['error']);
    }
    $ext = self :: formImageType($form_name);

    // Hope no duplicates.
    $fileName = IMAGE_PATH . safeHash(time() . $_FILES[$form_name]['tmp_name']) . '.' . $ext;
    move_uploaded_file($_FILES[$form_name]['tmp_name'], $fileName);

    return self :: getThumbUrl($fileName, $max_dim);
  }

  public static function getThumbUrl($name, $max_dim) {
    $path_parts = pathinfo($name);
    $thumbPath = THUMB_PATH . $path_parts['filename'] . '_' . $max_dim . '.jpg';

    // Lazy
    if (!is_readable($thumbPath)) {
      if (!is_readable($name)) {
        throw new Exception("Need to generate thumb, but original media missing for $name");
      }

      list ($origWidth, $origHeight) = getimagesize($name);

      switch (strtolower($path_parts['extension'])) {
        case ('jpeg') :
        case ('jpg') :
          $oImg = imagecreatefromjpeg($name);
          break;
        case ('gif') :
          $oImg = imagecreatefromgif($name);
          break;
        case ('png') :
          $oImg = imagecreatefrompng($name);
          break;
        case ('bmp') :
          $oImg = imagecreatefromwbmp($name);
          break;
        default :
          throw new exception('Not yet able to thumbnail media of type:' . $name);
      }
      $scalar = $max_dim / max($origWidth, $origHeight);
      $nw = round($scalar * $origWidth);
      $nh = round($scalar * $origHeight);
      $nImg = imagecreatetruecolor($nw, $nh);
      imagecopyresampled($nImg, $oImg, 0, 0, 0, 0, $nw, $nh, $origWidth, $origHeight);
      imagejpeg($nImg, $thumbPath, 50);
    }
    return $thumbPath;
  }

  public static function formImageType($form_name) {
    switch ($_FILES[$form_name]['type']) {
      case ('image/jpeg') :
      case ('image/pjpeg') :
        return 'jpg';
      case ('image/gif') :
        return 'gif';
      case ('image/png') :
      case ('image/x-png') :
        return 'png';
      case ('image/bmp') :
        return 'bmp';
      default :
        throw new exception('Not yet able to determine media of type ' . $_FILES[$form_name]['type']);
    }
  }

}

<?php
namespace Pondol\Meta\Services;

class Image
{

  private $save_path;
  private $background_image;
  private $font;
  private $fontSize;
  private $meta;


  public function __construct($meta)
  {
    $this->meta = $meta;
    $this->save_path = config('pondol-meta.dummy_image.save_path');
    $this->background_image = config('pondol-meta.dummy_image.background_image');
    $this->font = config('pondol-meta.dummy_image.font');
    $this->fontSize = config('pondol-meta.dummy_image.fontSize');
  }
   /**
   * og용 이미지 제작
   */
  public function create() {
    $image = $this->save_path.'/'.$this->meta->id.'.jpg';

    if (file_exists($image)) {
      return $this->meta->image(str_replace(public_path(), '', $image));
    }

    return $this->re_create($image);
  }

  public function re_create($image) {
    // create image
    $bg_image = imagecreatefromjpeg($this->background_image);//replace with your image 

    $fontColor = imagecolorallocate($bg_image, 255, 255, 255);
    $black = imagecolorallocate($bg_image, 255, 255, 255);
    $angle = 0;

    $iWidth = imagesx($bg_image);
    $iHeight = imagesy($bg_image);
    $tSize = imagettfbbox($this->fontSize, $angle, $this->font, $this->meta->title);
    $tWidth = max([$tSize[2], $tSize[4]]) - min([$tSize[0], $tSize[6]]);
    $tHeight = max([$tSize[5], $tSize[7]]) - min([$tSize[1], $tSize[3]]);
    // text is placed in center you can change it by changing $centerX, $centerY values
    $centerX = ceil(($iWidth - $tWidth) / 2);
    $centerX = $centerX<0 ? 0 : $centerX;
    $centerY = ceil(($iHeight - $tHeight) / 2);
    $centerY = $centerY<0 ? 0 : $centerY;


    imagettftext($bg_image, $this->fontSize, $angle, $centerX, $centerY, $black, $this->font, $this->meta->title);
    imagejpeg($bg_image, $this->save_path.'/'.$this->meta->id.'.jpg'); // save image
    imagedestroy($bg_image);
    return $this->meta->image(str_replace(public_path(), '', $image));
  }

  public function save_path($save_path) {
    $this->save_path = $save_path ?? $this->save_path;
    return $this;
  }

  public function background_image($background_image) {
    $this->background_image = $background_image ?? $this->background_image;
    return $this;
  }

  public function font($font) {
    $this->font = $font ?? $this->font;
    return $this;
  }

  public function fontSize($fontSize) {
    $this->fontSize = $fontSize ?? $this->fontSize;
    return $this;
  }

  public function __set($name, $value) {
    $this->{$name} .= $value;

    echo $name.':'.$value;

  }
}
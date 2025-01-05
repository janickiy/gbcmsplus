<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use mcms\partners\Module;


class TempFile extends Model
{

  public $file;
  public $image;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['image', 'file', 'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], 'wrongMimeType' => Yii::_t('partners.support.ticket_images_error')],
      ['image', 'file', 'extensions' => 'png, jpg, gif, jpeg', 'maxSize' => 10240000],
    ];
  }

}

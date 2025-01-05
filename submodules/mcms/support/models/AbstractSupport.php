<?php

namespace mcms\support\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

abstract class AbstractSupport extends ActiveRecord
{

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function getSupportCategory()
  {
    return $this->hasOne(SupportCategory::class, ['id' => 'support_category_id']);
  }

  public function getDelegatedTo()
  {
    return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'delegated_to']);
  }

  public function isOpened()
  {
    return !!$this->getAttribute('is_opened');
  }

  public function close()
  {
    $this->setAttribute('is_opened', 0);
    return $this;
  }

  public function open()
  {
    $this->setAttribute('is_opened', 1);
    return $this;
  }
}
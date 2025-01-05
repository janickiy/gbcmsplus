<?php

namespace mcms\common\validators;


use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\validators\Validator;

/**
 * Валидатор, который не позволяет вводить localhost и все локальные URL
 */
class LocalhostUrlValidator extends Validator
{
  /* @var array диапазоны локальных IP из \yii\validators\IpValidator */
  private static $filterIpRanges = ['multicast', 'linklocal', 'localhost', 'documentation', 'private'];

  /**
   * @param \yii\base\Model $model
   * @param string $attribute
   */
  public function validateAttribute($model, $attribute)
  {
    //Достаем из url хост
    $parsed = parse_url($model->{$attribute});
    $host = ArrayHelper::getValue($parsed, 'host');


    $ipValidator = Validator::createValidator(IpValidator::class, $model, [$attribute]);

    //Если хост это ip валидируем его Ip валидатором
    if ($ipValidator->validate($host)) {
      $ipValidator->ranges = self::$filterIpRanges;
      //Если ip в диапазоне локальных адресов добавляем ошибку
      if ($ipValidator->validate($host)) {
        $this->addError($model, $attribute, Yii::t('yii', '{attribute} is not a valid URL.', [
          'attribute' => $model->{$attribute}
        ]));
      }
    }

    //Если хост localhost добавляем ошибку
    if ($host == 'localhost') {
      $this->addError($model, $attribute, Yii::t('yii', '{attribute} is not a valid URL', [
        'attribute' => $model->{$attribute}
      ]));
    }

  }

}
<?php

namespace mcms\promo\validators;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Source;
use Yii;
use yii\validators\Validator;

/**
 * При удалении глобального постбека у партнера, проверяет есть ли у него активные ссылки с включенным глобальным постбеком
 * Class CheckOnActiveLinksWithGlobalPBValidator
 * @package mcms\common\validators
 */
class GlobalPBValidator extends Validator
{
  public $userId;

  const POSTBACK_URL = 'postback_url';
  const COMPLAINS_POSTBACK_URL = 'complains_postback_url';

  const USE_GPU = 'use_global_postback_url';
  const USE_CGPU = 'use_complains_global_postback_url';

  // Если меняем postback_url, нужно проверить флаг use_global_postback_url
  // Если меняем complains_postback_url, нужно проверить флаг use_complains_global_postback_url
  const FIELDS = [
    self::POSTBACK_URL => self::USE_GPU,
    self::COMPLAINS_POSTBACK_URL => self::USE_CGPU,
  ];

  public function init()
  {
    parent::init();
    if ($this->userId === null) {
      $this->userId = Yii::$app->user->id;
    }
  }

  /**
   * @param \yii\base\Model $model
   * @param string $attribute
   */
  public function validateAttribute($model, $attribute)
  {
    // Валидация нужна только если поле было заполненым, а его очистили
    $oldValue = $model->getOldAttribute($attribute);
    if (empty($model->$attribute) && !empty($oldValue)) {
      $field = ArrayHelper::getValue(self::FIELDS, $attribute);
      if (!$field) {
        return;
      }
      $activeLinksWithGlobalPb = Source::find()->where([
        'user_id' => $this->userId,
        $field => 1,
        'status' => Source::STATUS_APPROVED,
      ])->one();
      if ($activeLinksWithGlobalPb !== null) {
        $this->addError($model, $attribute, Yii::_t('users.forms.user_have_links_with_global_postback_url'));
      }
    }
  }
}
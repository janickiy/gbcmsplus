<?php

namespace mcms\promo\components\widgets;

use mcms\promo\models\UserPromoSetting;
use Yii;
use yii\base\Exception;
use yii\base\Widget;

/**
 * Class UserFakeWidget
 * @package mcms\promo\components\widgets
 */
class UserFakeWidget extends Widget {

  public $userId;

  /**
   * @inheritDoc
   */
  public function run()
  {
    if (!$this->userId) throw new Exception('Не указан обязательный параметр userId');
    return Yii::$app->getModule('promo')->isIndividualFakeSettingsEnabled() && Yii::$app->getModule('promo')->canEditIndividualFakeSettings()
      ? $this->render('user_fake_widget', [
        'model' => UserPromoSetting::findOne(['user_id' => $this->userId]) ?: new UserPromoSetting(['user_id' => $this->userId])
      ])
      : null;
  }
}
<?php

namespace mcms\promo\commands\api_actions;

use Yii;
use yii\base\Action;
use mcms\promo\Module;

/**
 * Class GetAdsParams
 * @package mcms\promo\commands\api_actions
 */
class GetAdsParams extends Action
{

  public function run($userId)
  {
    $isAllowedSourceRedirect = Yii::$app->getModule('promo')->settings->getValueByKey(Module::SETTINGS_ALLOW_SOURCE_REDIRECT);

    if (!$isAllowedSourceRedirect) {
      $isAllowedSourceRedirect = Yii::$app->getModule('users')->api('userPromoSettings')->getIsAllowedSourceRedirect($userId);
    }

    echo json_encode([
      'isAllowedSourceRedirect' => $isAllowedSourceRedirect,
    ]);
    Yii::$app->end();
  }
}
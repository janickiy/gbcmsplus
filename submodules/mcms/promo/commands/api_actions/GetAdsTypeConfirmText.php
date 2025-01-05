<?php

namespace mcms\promo\commands\api_actions;


use mcms\promo\models\LandingCategory;
use mcms\promo\models\Source;
use Yii;
use yii\base\Action;

class GetAdsTypeConfirmText extends Action
{

  public function run($sourceId)
  {

    /** @var $landingCategoryModel LandingCategory */
    if (!$landingCategoryModel = Source::findOne($sourceId)->getCategory()->one()) {
      return null;
    }

    if ($resultText = $landingCategoryModel->getAttribute('click_n_confirm_text')) {
      echo json_encode(unserialize($resultText));
      return null;
    }

    echo json_encode(Yii::$app->getModule('promo')->api('settings')->getDefaultClickNConfirmText());
  }
}
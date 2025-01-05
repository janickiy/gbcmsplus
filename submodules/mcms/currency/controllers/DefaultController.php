<?php

namespace mcms\currency\controllers;

use mcms\common\web\AjaxResponse;
use mcms\promo\components\api\MainCurrencies;
use Yii;
use mcms\common\controller\AdminBaseController;
use mcms\currency\models\Currency;
use mcms\currency\models\search\CurrencySearch;
use rgk\utils\actions\IndexAction;
use rgk\utils\actions\UpdateModalAction;

/**
 * для валют
 */
class DefaultController extends AdminBaseController
{

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => CurrencySearch::class
      ],
      'update-modal' => [
        'class' => UpdateModalAction::class,
        'modelClass' => Currency::class
      ],
    ];
  }

  /**
   * Валидация курсов валют
   * @param integer $id
   * @return array
   */
  public function actionValidateCustomCourse($id)
  {
    $currency = Currency::findOne(['id' => $id]);
    $errors = [];

    if (!$currency->load(Yii::$app->request->post())) {
      return AjaxResponse::error($errors);
    }

    foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) {
      if (!$currency->isCustomCourseProfitable($currencyCode)) {
        $errors[] = Yii::_t('currency.main.custom_course_became_unprofitable', [
          'course' => strtoupper($currencyCode)
        ]);
      }
    }

    if (!empty($errors)) {
      return AjaxResponse::error($errors);
    }

    return AjaxResponse::success();
  }
}
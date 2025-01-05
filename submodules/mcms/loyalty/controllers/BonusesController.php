<?php

namespace mcms\loyalty\controllers;

use mcms\loyalty\models\LoyaltyBonus;
use mcms\loyalty\models\search\LoyaltyBonusSearch;
use rgk\utils\actions\IndexAction;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Просмотр бонусов реселлера
 */
class BonusesController extends Controller
{
  /**
   * Подключение необходимых экшенов
   * @return array
   */
  public function actions()
  {
    return [
      // Список бонусов реселлера
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => LoyaltyBonusSearch::class,
      ],
    ];
  }

  /**
   * Модалка детального просмотра бонуса
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionViewModal($id)
  {
    $model = LoyaltyBonus::findOne((int)$id);
    if (!$model) throw new NotFoundHttpException;

    $this->view->title = LoyaltyBonus::t('loyalty_bonus_external_id', ['id' => $model->external_id]);

    return $this->renderAjax('view', ['model' => $model, 'bonusDetails' => $model->getDetails()]);
  }
}
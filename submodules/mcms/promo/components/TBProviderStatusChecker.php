<?php


namespace mcms\promo\components;

use mcms\promo\models\TrafficbackProvider;
use Yii;

/**
 * tricky: Если сменили категорию или активировали провайдер, ищем активные провайдеры с такой же категорией и делаем неактивными
 */
class TBProviderStatusChecker
{
  /** @var TrafficbackProvider */
  private $tbProvider;

  /**
   * TBProviderStatusChecker constructor.
   * @param TrafficbackProvider $tbProvider
   */
  public function __construct(TrafficbackProvider $tbProvider)
  {
    $this->tbProvider = $tbProvider;
  }

  /**
   * Деактивация провайдеров с той же категорией
   * @param $insert
   * @param $changedAttributes
   */
  public function disableSameCategoryProviders($insert, $changedAttributes)
  {
    if ($this->tbProvider->status === TrafficbackProvider::STATUS_ACTIVE &&
      (
        $insert ||
        isset($changedAttributes['status']) ||
        isset($changedAttributes['category_id'])
      )) {
      $sameCategoryProviders = TrafficbackProvider::find()->andWhere([
        'status' => TrafficbackProvider::STATUS_ACTIVE,
        'category_id' => $this->tbProvider->category_id
      ])
        ->andWhere(['<>', 'id', $this->tbProvider->id])
        ->all();
      foreach ($sameCategoryProviders as $provider) {
        /** @var TrafficbackProvider $provider */
        if (!$provider->setDisabled()->save()) {
          $errors = print_r($provider->getErrors(), true);
          Yii::error(
            'При активации провайдера ТБ, провайдер "' . $provider->name . '" не деактивировался. Ошибки: ' . $errors,
            __METHOD__
          );
        }
      }
    }
  }

}
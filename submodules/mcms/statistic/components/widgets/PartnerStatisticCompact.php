<?php

namespace mcms\statistic\components\widgets;

use mcms\partners\components\mainStat\CompactFormModel;
use mcms\partners\components\mainStat\Row;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\Group;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Widget;

/**
 * Статистика партнера в компактном виде
 */
class PartnerStatisticCompact extends Widget
{
  /**
   * @var int ID пользователя
   * @see \mcms\statistic\components\AbstractStatistic::$viewerId
   */
  public $userId;
  /** @var string Начальная дата статистики */
  public $dateFrom = 'first day of 6 month ago';

  /**
   * @inheritdoc
   */
  public function init()
  {
    if (!$this->userId) {
      throw new InvalidParamException('Не передан параметр userId');
    }
    if (!$this->dateFrom) {
      throw new InvalidParamException('Не передан параметр dateFrom');
    }

    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $formModel = new CompactFormModel();
    $formModel->groups = [Group::BY_MONTH_NUMBERS];
    $formModel->viewerId = $this->userId;
    $formModel->dateFrom = Yii::$app->formatter->asDate($this->dateFrom, 'php:Y-m-d');
    Yii::$container->set(Row::class, [
      // TRICKY сделано присвоение через контейнер, чтобы не внедряться из-за этого свойства в класс Fetch
      'isRatioByUniques' => $formModel->isRatioByUniques
    ]);

    /** @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel], ['rowClass' => Row::class]);

    $dataProvider = $fetch->getDataProvider(['sort' => ['attributes' => ['group'], 'defaultOrder' => ['group' => SORT_DESC]]]);
    return $this->render('partner_statistic_compact', [
      // Если статистика включает данные за разные года, отображать в группировке не только месяц, но и год
      'model' => $formModel,
      'dataProvider' => $dataProvider,
//      'paymentSettings' => $paymentsModule->api('userSettingsData', ['userId' => $this->userId])->getResult(),
    ]);
  }
}
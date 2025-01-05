<?php

namespace mcms\statistic\components\data_providers;

use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Класс нужен для того, чтобы исключить подзапрос из подсчета кол-ва строк (для пагинации).
 * Иначе этот COUNT() работает долго
 *
 * Class LabelDataProvider
 * @package mcms\statistic\components\data_providers
 */
class LabelDataProvider extends ActiveDataProvider
{

  /**
   * @inheritdoc
   */
  protected function prepareTotalCount()
  {
    /** @var Query $subQuery */
    $subQuery = clone $this->query;
    $subQueryAlias = 'sub';
    $subQuery->addSelect($subQuery->groupBy);

    return (int) (new Query())
      ->select([new Expression('COUNT(DISTINCT ' . implode(', ', $subQuery->groupBy) . ')')])
      ->from([$subQueryAlias => $subQuery
        ->limit(-1)
        ->offset(-1)
        ->orderBy([])])
      ->scalar();
  }
}
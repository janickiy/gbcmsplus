<?php

namespace mcms\payments\models\paysystems;

use yii\data\ArrayDataProvider;

class PaySystemApiGroupSearch extends PaySystemApi
{
  public function rules()
  {
    return [];
  }

  /**
   * @return ArrayDataProvider
   */
  public function search()
  {
    $query = static::find();
    $itemsGrouped = $this->groupItems($query->all());

    return new ArrayDataProvider([
      'allModels' => $itemsGrouped,
    ]);
  }

  /**
   * Группировка ПС
   * @param PaySystemApi[] $items
   * @return PaySystemApiGroup[]
   */
  private function groupItems($items)
  {
    $itemsGrouped = [];

    /** @var PaySystemApi $item */
    foreach ($items as $item) {
      if (!isset($itemsGrouped[$item->code])) $itemsGrouped[$item->code] = new PaySystemApiGroup;

      /** @var PaySystemApiGroup $itemsGroup */
      $itemsGroup = $itemsGrouped[$item->code];
      $itemsGroup->paysystemApis[] = $item;
      $itemsGroup->updateData();
    }

    return $itemsGrouped;
  }
}
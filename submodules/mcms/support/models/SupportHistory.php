<?php

namespace mcms\support\models;

use Yii;

class SupportHistory extends AbstractSupport
{

  const SCENARIO_HISTORY = 'history';

  public static function tableName()
  {
    return 'support_history';
  }

  public function getSupport()
  {
    return $this->hasOne(Support::class, ['id' => 'support_id']);
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_HISTORY => ['support_category_id', 'delegated_to', 'is_opened', 'created_at', 'updated_at', 'created_by']
    ]);
  }

  public function getCreatedBy()
  {
    return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'created_by']);
  }

  static function saveHistory(Support $support, array $changedAttributes, $forced = false)
  {
    if (!$forced &&
      array_diff(self::getListenerAttributes(), array_keys($changedAttributes)) == self::getListenerAttributes()) {
      return false;
    }
    $history = new self();
    $history->scenario = self::SCENARIO_HISTORY;
    $history->created_by = Yii::$app->user->getId();
    $history->support_id = $support->id;
    $historyActionAttributes = array_flip($history->activeAttributes());

    unset($historyActionAttributes['created_by']);

    foreach (array_flip($historyActionAttributes) as $attributeName) {
      $history->{$attributeName} = $support->getAttribute($attributeName);
    }

    return $history->save();
  }

  public static function getListenerAttributes()
  {
    return [
      'support_category_id',
      'is_opened',
      'delegated_to',
    ];
  }
}
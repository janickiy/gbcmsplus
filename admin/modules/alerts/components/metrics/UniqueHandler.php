<?php

namespace admin\modules\alerts\components\metrics;

use yii\db\Query;

class UniqueHandler extends BaseHandler
{
  /**
   * @inheritdoc
   */
  public function baseQuery(array $where = [])
  {
    return (new Query())
      ->select(
          ['count_uniques' => 'COUNT(st.id)']
      )
      ->from(['st' => 'hits'])
      ->innerJoin('operators o', 'o.id = st.operator_id')
      ->innerJoin('sources s', 's.id = st.source_id')
      ->innerJoin('landings l', 'l.id = st.landing_id')
      ->andFilterWhere(['st.source_id' => $this->event->sources])
      ->andWhere($where)
      ->andWhere(['is_unique' => 1]);
  }
}
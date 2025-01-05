<?php

namespace admin\modules\alerts\models\query;

use admin\modules\alerts\models\EventLog;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class EventQuery extends ActiveQuery
{
    /* @const инвервал проверки событий алертов */
    const CHECK_INTERVAL = 10 * 3600;

    /**
     * Только активные
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['is_active' => true]);
    }

    /**
     * Только актуальные (которые еще не обработаны)
     * @return $this
     */
    public function actual()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $modelClassTableName = $modelClass::tableName();

        return $this
            ->leftJoin(['log' => EventLog::tableName()],
                "log.event_id = {$modelClassTableName}.id AND log.created_at > :time - {$modelClassTableName}.check_interval",
                [':time' => time()]
            )
            ->andWhere([
                'log.id' => null,
            ]);
    }
}

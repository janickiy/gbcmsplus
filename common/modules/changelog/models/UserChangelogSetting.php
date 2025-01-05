<?php

namespace common\modules\changelog\models;

use rgk\changelog\models\Changelog;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class UserChangelogSetting
 *
 * @property integer $user_id
 * @property integer $changelog_last_read
 */
class UserChangelogSetting extends ActiveRecord
{
    const CHANGELOG_LAST_READ = 'changelog_last_read';

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return '{{%user_changelog_settings}}';
    }

    /**
     * @param $attribute
     * @return int
     */
    private function touch($attribute)
    {
        $tableName = static::tableName();
        $time = time();

        $sql = <<<SQL
INSERT INTO {$tableName} (user_id, {$attribute})
VALUES (:user_id, :time)
ON DUPLICATE KEY UPDATE
user_id = :user_id,
{$attribute} = :time
SQL;

        $this->getDb()->createCommand($sql)->bindValues([
            ':user_id' => $this->user_id,
            ':time' => $time,
        ])->execute();

        return $time;
    }

    /**
     * @param $attribute
     * @param $defaultValue
     * @return mixed
     */
    private function getLog($attribute, $defaultValue)
    {
        $log = $this->findOne($this->user_id);
        if ($log) {
            return $log->$attribute;
        }

        return $defaultValue;
    }

    /**
     * Дата последнего просмотра ченджлога
     * @return integer
     */
    private static function getCurrentUserChangelogLastRead()
    {
        $model = new self;
        $model->user_id = Yii::$app->user->id;
        return $model->getLog(static::CHANGELOG_LAST_READ, 0);
    }

    /**
     * Обновить дату последнего просмотра ченджлога
     * @return int
     */
    public static function touchCurrentUserChangelogLastRead()
    {
        $model = new self;
        $model->user_id = Yii::$app->user->id;
        return $model->touch(static::CHANGELOG_LAST_READ);
    }


    /**
     * @return bool
     */
    public static function currentUserHasUnreadedChangeLog()
    {
        $maxUpdatedAt = Changelog::find()->max('updated_at');
        return $maxUpdatedAt > static::getCurrentUserChangelogLastRead();
    }
}

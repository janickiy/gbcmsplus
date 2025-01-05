<?php

namespace common\models\multilanguage;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class Text
 * @package common\models\multilanguage
 * @property $language
 * @property $entity_id
 * @property $header
 * @property $text
 */
class Text extends ActiveRecord
{
    public static function tableName()
    {
        return 'texts';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        return $this->hasOne(Entity::class, ['entity_id' => 'id']);
    }
}
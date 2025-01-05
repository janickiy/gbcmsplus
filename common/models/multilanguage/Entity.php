<?php

namespace common\models\multilanguage;

use yii\db\ActiveRecord;

/**
 * Class Entity
 * @package common\models\multilanguage
 * @property $foreign_id
 * @property $type
 * @property $label
 */
class Entity extends ActiveRecord
{
    public static function tableName()
    {
        return 'entities';
    }

    public function getResource()
    {
        return $this->hasOne($this->type, ['foreign_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTexts()
    {
        return $this->hasMany(Text::class, ['entity_id' => 'id']);
    }

    public function getTextByLanguage($language)
    {
        return $this
            ->getTexts()
            ->where('language = :language', [':language' => $language]);
    }

}
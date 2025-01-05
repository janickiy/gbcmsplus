<?php

namespace mcms\common\multilang;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Class MultiLangModel - расширение модели для реализации мультиязычности
 * валидация красиво работает только при включенной у формы enableAjaxValidation
 * @package mcms\common\multilang
 */
abstract class MultiLangModel extends ActiveRecord
{

    /**
     * @return mixed
     */
    abstract public function getMultilangAttributes();

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        foreach ($this->getMultilangAttributes() as $attribute) {
            if (@unserialize($this->getAttribute($attribute)) === false)
                $this->$attribute = serialize((array)$this->getAttribute($attribute));
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateArrayString($attribute, $params)
    {
        $tooLong = \Yii::t('yii', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.', ['attribute' => $this->getAttributeLabel($attribute), 'min' => ArrayHelper::getValue($params, 'min'), 'max' => ArrayHelper::getValue($params, 'max')]);
        $tooShort = \Yii::t('yii', '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.', ['attribute' => $this->getAttributeLabel($attribute), 'min' => ArrayHelper::getValue($params, 'min'), 'max' => ArrayHelper::getValue($params, 'max')]);

        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            return false;
        }

        $hasErrors = false;

        foreach ($this->$attribute as $key => $value) {
            $length = mb_strlen($value);
            if ($params['min'] !== null && $length < $params['min']) {
                $this->addError($attribute . '[' . $key . ']', $tooShort);
                $hasErrors = true;
            }
            if ($params['max'] !== null && $length > $params['max']) {
                $this->addError($attribute . '[' . $key . ']', $tooLong);
                $hasErrors = true;
            }
        }

        return !$hasErrors;
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function validateArrayRequired($attribute)
    {
        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            return false;
        }

        $hasErrors = false;

        foreach ($this->$attribute as $key => $value) {
            if (empty($value)) {
                $this->addError($attribute . '[' . $key . ']', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel($attribute)]));
                $hasErrors = true;
            }
        }

        return !$hasErrors;
    }

    /**
     * Обязателен один из языков
     * @param $attribute
     * @return bool
     */
    public function validateArrayOneRequired($attribute)
    {
        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            return false;
        }

        $hasErrors = true;
        foreach ($this->$attribute as $key => $value) {
            if ($value) $hasErrors = false;
        }

        if ($hasErrors) {
            foreach ($this->$attribute as $key => $value) {
                $this->addError($attribute . '[' . $key . ']', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel($attribute)]));
            }
        }
        return !$hasErrors;
    }

    /**
     * @param $content
     * @return array
     */
    public static function filterArrayPurifier($content)
    {
        if (empty($content) || !is_array($content)) {
            return $content;
        }

        $filtered = [];
        foreach ($content as $key => $value) {
            $filtered[$key] = is_string($value) ? HtmlPurifier::process($value, [
                'Attr.AllowedFrameTargets' => ['_blank'],

                /* @see http://htmlpurifier.org/live/configdoc/plain.html#CSS.MaxImgLength */
                /* @see http://htmlpurifier.org/live/configdoc/plain.html#HTML.MaxImgLength */
                'HTML.MaxImgLength' => null,
                'CSS.MaxImgLength' => null,
            ]) : $value;
        }

        return $filtered;
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function attributeIsEmpty($attribute)
    {
        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            return true;
        }

        foreach ($this->$attribute as $key => $value) {
            if (!empty($value)) return false;
        }
        return true;
    }

    /**
     * @param string $name
     * @return LangAttribute|mixed
     */
    public function __get($name)
    {

        $multiLangAttributes = $this->getMultilangAttributes();

        if (in_array($name, $multiLangAttributes)) {

            $attribute = $this->getAttribute($name);
            if (!empty($attribute) && !is_array($attribute) && @unserialize($attribute) !== false) {
                $attribute = new LangAttribute($attribute);
            }
            return $attribute;

        }

        return parent::__get($name);
    }
}

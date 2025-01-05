<?php

namespace mcms\common\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 *
 */
abstract class AbstractMassModel extends Model
{
    /**
     * @var $edit array массив атрибутов которые должны быть сохранены
     */
    public $edit = [];
    /**
     * @var ActiveRecord $model модель AR для сохранения в базу
     */
    public $model = null;

    public function init()
    {
        parent::init();
        if (!$this->model) {
            throw new Exception('Undefined $model property');
        }
    }

    /**
     * @param $attributeNames
     * @param $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $valid = parent::validate($attributeNames, $clearErrors);

        $this->model->setAttributes($this->attributes);
        if (!$this->model->validate($this->fields())) {
            foreach ($this->model->getErrors() as $attr => $error) {
                $this->addError($attr, $error);
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Поля для отображения, в правильно порядке.
     * @return array
     */
    public abstract function ownFields();

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['edit', 'validateEmptyness'],
            [$this->fields(), 'required', 'when' => function ($model, $attribute) {
                return in_array($attribute, $model->edit);
            }],
        ];
    }

    /**
     * Проверяем на пустоту поля для редактирования
     * @param $attribute
     */
    public function validateEmptyness($attribute)
    {
        $empty = empty($this->{$attribute});
        if (!$empty) {
            $empty = 1;
            foreach ($this->edit as $item) {
                if ($item) {
                    $empty = 0;
                    break;
                }
            }
        }

        if ($empty) {
            $this->addError($attribute, Yii::_t('commonMsg.main.mass-model-choose-for-update'));
            return;
        }
    }

    /**
     * Сохранение модели AR привязанной к текущей модели массового созранения
     * @param array $selection ID для какие записи обновлять
     * @return bool
     */
    public function save(array $selection)
    {
        $toUpdate = [];
        foreach ($this->edit as $attr) {
            if ($attr) {
                $toUpdate[$attr] = $this->{$attr};
            }
        }

        try {
//      call_user_func([get_class($this->model), 'updateAll'], $toUpdate, ['id' => $selection]);

            $modelClass = $this->model;
            $models = $modelClass::findAll(['id' => $selection]);
            foreach ($models as $model) {
                $model->setAttributes($toUpdate, false);
                $model->save();
            }
        } catch (\yii\db\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return $this->ownFields();
    }
}

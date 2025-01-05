<?php

namespace mcms\common;

use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

abstract class DynamicActiveRecord extends ActiveRecord
{
  protected $additionalFieldsRelationName = NULL;
  protected $useObjectInsteadRelation = false;

  /** @var  ActiveRecord */
  private $_additionalFieldsModel;

  /**
   * @inheritDoc
   */
  public function afterFind()
  {
    parent::afterFind();
    if ($this->additionalFieldsRelationName !== NULL) {
      if ($relation = parent::__get($this->additionalFieldsRelationName)) {
        $this->_additionalFieldsModel = $relation;
      }
    }
  }

  private function getGetterFunction($name)
  {
    return sprintf('get%s', ucfirst($name));
  }

  private function getSetterFunction($name)
  {
    return sprintf('set%s', ucfirst($name));
  }



  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    if ($this->additionalFieldsRelationName !== NULL) {
      $relationMethod = "get" . ucfirst($this->additionalFieldsRelationName);
      if (method_exists($this, $relationMethod)) {
        $relation = call_user_func([$this, $relationMethod]);
        $this->_additionalFieldsModel = $this->useObjectInsteadRelation ? $relation : \Yii::createObject($relation->modelClass);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function __get($name)
  {
    if (!$this->additionalFieldsRelationName) {
      return parent::__get($name);
    }

    $getterFunctionName = sprintf('get%s', ucfirst($name));
    if (method_exists($this, $getterFunctionName)) {
      $value = $this->$getterFunctionName();
      if ($value instanceof ActiveQuery) {
        return parent::__get($name);
      }

      return $value;
    } else if ($this->hasAttribute($name)) {
      return parent::__get($name);
    }
    if ($this->shouldUseAdditionalModel()
      && ($this->_additionalFieldsModel->hasAttribute($name) || method_exists($this->_additionalFieldsModel, $getterFunctionName))) {
      return $this->_additionalFieldsModel->__get($name);
    }

    $getterFunction = $this->getGetterFunction($name);
    if (method_exists($this, $getterFunction)) {
      return $this->$getterFunction();
    }

    return null;
  }

  /**
   * @inheritDoc
   */
  public function __set($name, $value)
  {
    $setterFunctionName = sprintf('set%s', ucfirst($name));
    if ($this->hasAttribute($name) || method_exists($this, $setterFunctionName)) {
      return parent::__set($name, $value);
    }

    if ($this->shouldUseAdditionalModel()
      && ($this->_additionalFieldsModel->hasAttribute($name) || method_exists($this->_additionalFieldsModel, $setterFunctionName))) {
      $this->_additionalFieldsModel->__set($name, $value);
    }

    $setterFunction = $this->getSetterFunction($name);
    if (method_exists($this, $setterFunction)) {
      return $this->$setterFunction($value);
    }

    return null;
  }

  public function save($runValidation = true, $attributeNames = null)
  {
    $result = true;
    if ($this->getIsNewRecord()) {
      $result &= $this->insert($runValidation, $attributeNames);
    } else {
      $result &= $this->update($runValidation, $attributeNames) !== false;
    }

    if ($this->_additionalFieldsModel->getIsNewRecord()) {
      if ($this->useObjectInsteadRelation) {
        $this->_additionalFieldsModel->linkDynamicActiveRecord($this);
        $result &= $this->_additionalFieldsModel->save($runValidation);
      } else {
        $this->link($this->additionalFieldsRelationName, $this->_additionalFieldsModel);
      }
    } else {
      $result &= $this->_additionalFieldsModel->save($runValidation);
    }

    return $result;
  }

  /**
   * @inheritDoc
   */
  public function getAttribute($name)
  {
    return parent::hasAttribute($name)
      ? parent::getAttribute($name)
      : $this->_additionalFieldsModel->getAttribute($name)
      ;
  }

  /**
   * @inheritDoc
   */
  public function getAttributeLabel($attribute)
  {
    return parent::hasAttribute($attribute)
      ? parent::getAttributeLabel($attribute)
      : $this->_additionalFieldsModel->getAttributeLabel($attribute)
      ;
  }

  public function getAdditionalModel()
  {
    return $this->_additionalFieldsModel;
  }

  private function shouldUseAdditionalModel()
  {
    return $this->additionalFieldsRelationName !== NULL && $this->_additionalFieldsModel !== NULL;
  }

  /**
   * @return ActiveRecord
   */
  public function getAdditionalFieldsModel()
  {
    return $this->_additionalFieldsModel;
  }

  public function setScenario($value)
  {
    parent::setScenario($value);
    if ($this->_additionalFieldsModel instanceof Model) {
      $this->_additionalFieldsModel->setScenario($value);
    }
  }

  public function activeAttributes()
  {
    return array_merge(
      parent::activeAttributes(),
      $this->_additionalFieldsModel->activeAttributes()
    );
  }

  public function additionalFieldsActiveAttributes()
  {
    $attributes = [];
    foreach ($this->_additionalFieldsModel->activeAttributes() as $attribute) {
      $attributes[$attribute] = $this->__get($attribute);
    }

    return $attributes;
  }
}
<?php

namespace mcms\common\traits\model;

use common\models\multilanguage\Entity;
use common\models\multilanguage\Text;

trait MultiLang
{
  public function getText($label, $language = null)
  {
    $entity = $this->getEntity($label);
    if ($entity === NULL) return null;

    $text = $entity
      ->getTexts()
      ->where('language = :language', [':language' => $this->getLanguage($language)])
      ->one()
    ;

    return $text === null ? '' : $text->text;
  }

  private function getLanguage($language = null)
  {
    return $language === null ? \Yii::$app->language : $language;
  }

  private function getEntity($label = null)
  {
    $entity = Entity::findOne([
      'type' => $this::class,
      'label' => $label,
      'foreign_id' => $this->id
    ]);

    return $entity;
  }

  private function getOrCreateEntity($label = null)
  {
    $entity = $this->getEntity($label);
    if ($entity !== NULL) return $entity;

    $entity = new Entity();
    $entity->foreign_id = $this->id;
    $entity->type = $this::class;
    $entity->label = $label;
    $entity->save();

    return $entity;
  }

  public function setText($label, $text, $language = null)
  {
    $language = $this->getLanguage($language);
    $entity = $this->getOrCreateEntity($label);
    $texts = $entity->getTexts()->where(['language' => $language])->one();
    if ($texts === null) {
      $texts = new Text();
      $texts->entity_id = $entity->id;
    }

    $texts->language = $language;
    $texts->text = $text;
    return $texts->save();
  }

  public function getActiveValidators($attribute = null)
  {
    $validators = [];
    $scenario = $this->getScenario();
    $modelAttributes = $this->attributes();
    /** @var \yii\validators\RequiredValidator $validator */
    foreach ($this->getValidators() as $validator) {
      if ($validator->isActive($scenario) && ($attribute === null || in_array($attribute, $validator->attributes, true))) {

        $validatorAttributes = [];
        foreach ($validator->attributes as $validatorAttribute) {
          if (!in_array($validator, $modelAttributes)) continue;
          $validatorAttributes[] = $validatorAttribute;
        }

        $validator->attributes = array_filter(
          $validator->attributes,
          function($validatorAttribute) use ($modelAttributes) {
            return in_array($validatorAttribute, $modelAttributes);
          }
        );

        $validators[] = $validator;
      }
    }
    return $validators;
  }
}
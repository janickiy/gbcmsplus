<?php

namespace mcms\notifications\components\event;

use mcms\common\helpers\ArrayHelper;
use Yii;

class EventReplacements
{
  protected $class;
  protected $field;
  protected $label;
  protected $systemReplacements;

  public function __construct($class, $field, $label = null)
  {
    $this->class = $class;
    $this->field = $field;
    $this->label = $label;
    $this->systemReplacements = [
      'homeUrl' => [
        'value' => Yii::$app->getUrlManager()->getHostInfo(),
        'help' => Yii::_t('main.homeUrl')
      ],
    ];
  }

  private function recursiveResolveReplacements(array $replacements, $rootField = null)
  {
    $replacementsResult = [];
    if (count($replacements)) foreach ($replacements as $field => $replacement) {
      $value = \yii\helpers\ArrayHelper::getValue($replacement, 'value');
      if (is_array($value) && count($value)) {
        $value = $this->recursiveResolveReplacements($value, $field);
      }

      $replacementsResult[$field] = $value;
    }

//    return $rootField === null ? $replacementsResult : [$rootField => $replacementsResult];
    return $replacementsResult;
  }

  private function recursiveResolveReplacementsHelp(array $replacements, $rootField = null)
  {
    $replacementsResult = [];
    if (count($replacements)) foreach ($replacements as $field => $replacement) {
      $replacementValue = \yii\helpers\ArrayHelper::getValue($replacement, 'value');
      $replacementHelp = \yii\helpers\ArrayHelper::getValue($replacement, 'help');

      if ($class = \yii\helpers\ArrayHelper::getValue($replacementHelp, 'class', false)) {
        $classObject = Yii::createObject($class);
        if (method_exists($classObject, 'getReplacements')) {
          $replacementsResult = array_merge($replacementsResult, ArrayHelper::array_dot($this->recursiveResolveReplacementsHelp($classObject->getReplacements(), $field)));
        }
      } else if (is_array($replacementValue) && count($replacementValue) > 0) {
        $arrayReplacements = [];
        foreach ($replacementValue as $replacementField => $value) {
          if (is_array($value['value']) && count($value['value'])) {
            $arrayReplacements = array_merge(
              $arrayReplacements,
              ArrayHelper::array_dot($this->recursiveResolveReplacementsHelp($value['value'], $replacementField))
            );
          } else {
            $arrayReplacements[$replacementField] = ArrayHelper::getValue($value, 'help.label');
          }
        }
        $replacementsResult[$field] = $arrayReplacements;
      } else {
        $replacementsResult[$field] = \yii\helpers\ArrayHelper::getValue($replacementHelp, 'label');
      }
    }
    if ($rootField !== null) [$replacementsResult = [$rootField => $replacementsResult]];
    return $replacementsResult;
  }

  public function getReplacements()
  {
    if (!method_exists($this->class, 'getReplacements')) {
      return [sprintf("{%s}", $this->field) => $this->class];
    }

    $replacements = $this->recursiveResolveReplacements($this->class->getReplacements(), $this->field);
    $replacementsResult = [];

    foreach($this->systemReplacements as $field => $value) {
      $replacementsResult[sprintf('{%s}', $field)] = ArrayHelper::getValue($value, 'value');
    }
    foreach (ArrayHelper::array_dot($replacements) as $field => $value) {
      $replacementsResult[sprintf('{%s.%s}', $this->field, $field)] = Yii::$app->formatter->asText($value);
    }

    unset($replacements);

    return $replacementsResult;
  }

  public function getReplacementsHelp()
  {
    if (!method_exists($this->class, 'getReplacements')) return [
      sprintf("{%s}", $this->field) => Yii::_t($this->label)
    ];

    $replacementHelp = $this
      ->recursiveResolveReplacementsHelp((new $this->class)->getReplacements(), $this->field)
    ;

    $replacement = [];
    foreach($this->systemReplacements as $field => $value) {
      $replacement[sprintf('{%s}', $field)] = ArrayHelper::getValue($value, 'help');
    }
    foreach (ArrayHelper::array_dot($replacementHelp) as $key => &$help) {
      $replacement[sprintf("{%s}", $key)] = Yii::_t($help);
    }

    unset($replacementHelp);

    return $replacement;
  }
}
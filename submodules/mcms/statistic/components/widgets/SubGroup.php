<?php

namespace mcms\statistic\components\widgets;

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\widgets\assets\SubGroupAsset;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


/**
 * Вторая группировка в статистике
 */
class SubGroup extends Widget
{
  /** @var array */
  public $groups;
  /** @var FormModel */
  public $formModel;

  /**
   * @return string|void
   */
  public function run()
  {
    if (!$this->groups) {
      throw new InvalidParamException('Не передан обязательный параметр groups');
    }
    if (!$this->formModel) {
      throw new InvalidParamException('Не передан обязательный параметр formModel');
    }

    // Убираем текущую группировку
    $currentGroup = reset($this->formModel->groups);
    ArrayHelper::remove($this->groups, $currentGroup);

    // Убираем взаимоисключающие группировки
    if ($currentGroup === Group::BY_LINKS) {
      ArrayHelper::remove($this->groups, Group::BY_WEBMASTER_SOURCES);
    }
    if ($currentGroup === Group::BY_WEBMASTER_SOURCES) {
      ArrayHelper::remove($this->groups, Group::BY_LINKS);
      ArrayHelper::remove($this->groups, Group::BY_STREAMS);
    }
    if ($currentGroup === Group::BY_STREAMS) {
      ArrayHelper::remove($this->groups, Group::BY_WEBMASTER_SOURCES);
    }

    $jsonGroups = Json::encode($this->groups);
    $searchField = $this->formModel->getSearchFieldByGroup();
    $query = Json::encode(Yii::$app->getRequest()->getQueryParams());
    $hideLabel = Yii::_t('statistic.new_statistic_refactored.submenu_hide_label');

    $js = <<<JS
      subGroupInit({
        'groups': $jsonGroups,
        'searchFields': '$searchField',
        'query': $query,
        'hideLabel': '$hideLabel'
      });
JS;

    $this->getView()->registerJs($js);

    SubGroupAsset::register($this->getView());
  }
}
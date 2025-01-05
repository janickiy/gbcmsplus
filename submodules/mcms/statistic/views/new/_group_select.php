<?php

use yii\helpers\Html;

/** @var string[] $groups */
/** @var string $selectedGroup */

// Костыль для того: чтобы вынести переключатель из формы
$this->registerJs(<<<JS
  $('#groups-select').change(function(){
    val = $(this).val();
    $('#formmodel-groups').val(val).trigger('change');
  });
JS
);
?>

<?= Html::tag('span', Yii::_t('statistic.new_statistic_refactored.report_by'), ['class' => 'statisticHeader']) .
Html::dropDownList('groups', $selectedGroup, $groups, [
  'id' => 'groups-select',
  'class' => 'selectpicker col-i',
  'data-style' => 'btn-xs blue-link-button btn',
  'multiple' => false,
  'data-dropdown-align-right' => 1,
]); ?>
<div id="default-second-group"></div>

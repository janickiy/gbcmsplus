<?php

use yii\helpers\Html;
use yii\bootstrap\Html as BHtml;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use mcms\common\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var string $trafficType */
/** @var int|null $selectedTemplateId */
/** @var array $columnsTemplates */

// Костыль для того: чтобы вынести переключатель из формы
$this->registerJs(<<<JS
  $('.trafficType-select').click(function(){
    $('.trafficType-select').removeClass('active');
    $('.new-columns-templates-select button').removeClass('active');
     
    $(this).addClass('active');
    $('#columns-templates').val(null);
    $('#columns-templates').selectpicker('refresh');
    
    var columnsTemplateId = $(this).data('value');
    
    $('#statistic-template').val(columnsTemplateId);
    
    $('#statistic-submit-btn').trigger('click');
  });
JS
);

$columnsTemplatesOptions = [];
foreach ($columnsTemplates as $columnsTemplate) {
  $columnsTemplatesOptions[$columnsTemplate->id] = [
    'data-columns' => $columnsTemplate->columns,
    'data-content' => Html::tag('span',
      $columnsTemplate->name
      . (!$columnsTemplate->isNewRecord
        ? Html::tag('span', BHtml::icon('cog'), [
          'class' => 'columns-template-icon',
          'title' => Yii::t('yii', 'Update'),
          'data-template-id' => $columnsTemplate->id,
        ])
        : null),
      ['class' => 'text columns-template-text']),
  ];
}

?>

<div class="btn-group btn-group-xs pull-right">
  <?= Html::a(Yii::_t('statistic.new_statistic_refactored.traffic_type-total'), 'javascript:void(0)', [
    'class' => [
      'btn btn-xs btn-default trafficType-select' .
      (($selectedTemplateId === ColumnsTemplate::SYS_TEMPLATE_TOTAL) ? ' active' : '')
    ],
    'data-value' => ColumnsTemplate::SYS_TEMPLATE_TOTAL,
  ]) ?>
  <?= Html::a(Yii::_t('statistic.new_statistic_refactored.traffic_type-cpa'), 'javascript:void(0)', [
    'class' => [
      'btn btn-xs btn-default trafficType-select' .
      (($selectedTemplateId === ColumnsTemplate::SYS_TEMPLATE_CPA) ? ' active' : '')
    ],
    'data-value' => ColumnsTemplate::SYS_TEMPLATE_CPA,
  ]) ?>
  <?= Html::a(Yii::_t('statistic.new_statistic_refactored.traffic_type-revshare'), 'javascript:void(0)', [
    'class' => [
      'btn btn-xs btn-default trafficType-select' .
      (($selectedTemplateId === ColumnsTemplate::SYS_TEMPLATE_REVSHARE) ? ' active' : '')
    ],
    'data-value' => ColumnsTemplate::SYS_TEMPLATE_REVSHARE,
  ]) ?>
  <?= Html::a(Yii::_t('statistic.new_statistic_refactored.traffic_type-otp'), 'javascript:void(0)', [
    'class' => [
      'btn btn-xs btn-default trafficType-select' .
      (($selectedTemplateId === ColumnsTemplate::SYS_TEMPLATE_ONETIME) ? ' active' : '')
    ],
    'data-value' => ColumnsTemplate::SYS_TEMPLATE_ONETIME,
  ]) ?>
  <?= Html::dropDownList('columns-templates', $selectedTemplateId, ArrayHelper::map($columnsTemplates, 'id', 'name'), [
    'class' => 'selectpicker menu-right col-i new-columns-templates-select',
    'id' => 'columns-templates',
    'multiple' => true,
    'title' => Yii::_t('statistic.new_statistic_refactored.custom_template'),
    'data-count-selected-text' => Yii::_t('statistic.new_statistic_refactored.custom_template'),
    'data-selected-text-format' => 'count>0',
    'data-max-options' => 1,
    'data-dropdown-align-right' => 1,
    'data-new-template' => Yii::_t('statistic.statistic.columns_templates_new'),
    'data-update-title' => Yii::t('yii', 'Update'),
    'options' => $columnsTemplatesOptions,
    'data-get-columns-template-url' => Url::to(['/statistic/new-column-templates/get-template']),
    'data-class' => 'btn-xs btn-default' . (in_array($selectedTemplateId, [
        ColumnsTemplate::SYS_TEMPLATE_TOTAL,
        ColumnsTemplate::SYS_TEMPLATE_CPA,
        ColumnsTemplate::SYS_TEMPLATE_REVSHARE,
        ColumnsTemplate::SYS_TEMPLATE_ONETIME,
      ]) ? '' : ' active')
  ]); ?>


</div>
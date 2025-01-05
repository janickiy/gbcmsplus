<?php
use admin\modules\alerts\models\EventFilter;
use kartik\depdrop\DepDrop;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\common\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var \admin\modules\alerts\models\Event $model */
?>
<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#filtersPjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">



  <div class="row filters-row">
    <div class="col-md-6">
      <?= $form->field($model, "type")->dropDownList(EventFilter::getFilters(), ['prompt' => Yii::_t('alerts.event_filter.type-choose')])->label(false) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, "value")->widget(DepDrop::class, [
        'data' => $model->type
          ? ArrayHelper::map(EventFilter::getFilterValues($model->type), 'id', 'name')
          : [],
        'type' => DepDrop::TYPE_SELECT2,
        'select2Options' => [
          'theme' => Select2::THEME_SMARTADMIN,
        ],
        'pluginOptions' => [
          'depends' => ['eventfilter-type'],
          'placeholder' => Yii::_t('alerts.event_filter.value-choose'),
          'url' => Url::to(['get-values'])
        ]
      ])->label(false) ?>
    </div>
  </div>



</div>
<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'), ['class' => 'pull-right btn btn-success']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveKartikForm::end(); ?>

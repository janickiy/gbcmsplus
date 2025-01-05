<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\PrelandDefaults;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\StreamsDropdown;
use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var PrelandDefaults $model */
/** @var array $select2InitValues */
/** @var \mcms\promo\models\Source $source */

$prelandDefaultsType = PrelandDefaults::TYPE_ADD;
$this->registerJs(<<<JS
    var type = $('#prelanddefaults-type');
    var container = $('.add_preland_container');

    function refreshType() {
      if (type.val() == $prelandDefaultsType) {
        container.show();
      } else {
        $("#prelanddefaults-stream_id").val(null).trigger("change"); 
        $("#prelanddefaults-user_id").val(null).trigger("change"); 
        container.hide();
      }
    }
    
    type.on('change', refreshType);
    refreshType();
JS
);

?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['/promo/preland-defaults/form-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#preland-defaults-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">

  <?= $form->field($model, 'type')->dropDownList($model->getTypes()) ?>

  <?php if ($source): ?>
    <?= Html::activeHiddenInput($model, 'source_id')?>
  <?php else: ?>
    <div class="add_preland_container">
      <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
        'initValueUserId' => $model->user_id,
        'options' => ['placeholder' => Yii::_t('app.common.not_selected')],
        'roles' => ['partner'],
        'pluginOptions' => [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['/users/users/find-user/']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var sourceId = $("#' . Html::getInputId($model, 'source_id') . '").val();
            var streamId = $("#' . Html::getInputId($model, 'stream_id') . '").val();
            return {
              q: params.term ? params.term : "",
              data: {"roles":"partner","format":"#:id: - :email:"},
              source_ids: sourceId ? [sourceId] : [],
              stream_ids: streamId ? [streamId] : [],
            };
          }')
          ],
        ],
      ]); ?>

      <?= $form->field($model, 'stream_id')->widget(
        Select2::class, [
        'initValueText' => ArrayHelper::getValue($select2InitValues, 'stream'),
        'options' => ['placeholder' => Yii::_t('app.common.not_selected')],
        'pluginOptions' => [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['streams/stream-search']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var userId = $("#' . Html::getInputId($model, 'user_id') . '").val();
            var sourceId = $("#' . Html::getInputId($model, 'source_id') . '").val();
            return {
              q: params.term ? params.term : "",
              user_ids: userId ? [userId] : [],
              source_ids: sourceId ? [sourceId] : [],
            };
          }')
          ],
        ],
      ]) ?>
    </div>

    <?= $form->field($model, 'source_id')->widget(
      Select2::class, [
      'initValueText' => ArrayHelper::getValue($select2InitValues, 'source'),
      'options' => ['placeholder' => Yii::_t('app.common.not_selected')],
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => [
          'url' => Url::to(['arbitrary-sources/select2']),
          'dataType' => 'json',
          'data' => new JsExpression('function(params) {
            var streamId = $("#' . Html::getInputId($model, 'stream_id') . '").val();
            var userId = $("#' . Html::getInputId($model, 'user_id') . '").val();
            return {
              q: params.term ? params.term : "",
              stream_ids: streamId ? [streamId] : [],
              user_ids: userId ? [userId] : [],
            };
          }')
        ],
      ],
    ]) ?>
  <?php endif; ?>

  <?= $form->field($model, 'operators')->widget(
    OperatorsDropdown::class, [
    'options' => [
      'multiple' => true,
      'data-none-selected-text' => Yii::_t('app.common.not_selected'),
    ],
  ]) ?>

  <?= $form->field($model, 'status')->dropDownList($model->getStatuses()) ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end(); ?>


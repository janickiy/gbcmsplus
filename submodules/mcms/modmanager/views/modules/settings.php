<?php
use kartik\builder\Form;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use mcms\common\grid\ContentViewPanel;

$this->render('actions', ['id' => false]);
$this->beginBlock('actions');
echo $this->blocks['list_button'];
$this->endBlock();
$js = <<< JS
  $('#modules-settings-form').on('afterValidate', function() {
    var error = $('div.has-error').first();
    if (error.length === 0) return;
    
    var tabName = error.closest('.tab-pane').attr('id');
    $('.nav-tabs a[href="#' + tabName + '"]').tab('show')
  });
JS;
$this->registerJs($js);

?>

<?php ContentViewPanel::begin([
  'class' => 'well',
]) ?>

<?php $form = ActiveForm::begin([
  'options' => [
    'id' => 'modules-settings-form',
    'enctype' => 'multipart/form-data',
  ]
]) ?>

<?php if (count($attributesMap) > 1): ?>
  <ul class="nav nav-tabs nav-settings bordered">
    <?php foreach (array_keys($attributesMap) as $key => $divider): ?>
      <li <?php if ($key == 0): ?>class="active"<?php endif; ?>><a href="#settings_tab_<?= $key; ?>"
                                                                   data-toggle="tab"><?= $divider; ?></a></li>
    <?php endforeach; ?>
  </ul>

  <div class="tab-content padding-10">
    <?php foreach (array_keys($attributesMap) as $key => $divider): ?>
      <div class="tab-pane<?php if ($key == 0): ?> active<?php endif; ?>" id="settings_tab_<?= $key ?>">
        <?php
        if (!empty($attributesMap[$divider])) {
          echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => $attributesMap[$divider],
          ]);
        }
        ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <?php foreach (array_keys($attributesMap) as $key => $divider): ?>
    <?php
    if (!empty($attributesMap[$divider])) {
      echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => $attributesMap[$divider],
      ]);
    }
    ?>
  <?php endforeach; ?>
<?php endif; ?>

  <hr/>
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-success pull-right']) ?>
      <?= Html::resetButton(Yii::_t('app.common.Reset'), ['class' => 'btn btn-danger']) ?>
    </div>
  </div>

<?php ActiveForm::end(); ?>
<?php ContentViewPanel::end() ?>

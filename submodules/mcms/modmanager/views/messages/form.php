<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\TabsX;
use kartik\form\ActiveForm;
use yii\helpers\Html;

foreach ($data as $lang => $files) {
  $files_items = [];
  foreach ($files as $name => $content) {
    $files_items[] = [
      'label' => strtoupper(str_replace('_', ' ', $name)),
      'content' => $content,
    ];
  }

  $items[] = [
    'label' => '[' . strtoupper($lang) . ']',
    'content' => TabsX::widget([
      'items' => $files_items,
      'position' => TabsX::POS_LEFT,
      'encodeLabels' => false
    ])
  ];
}

$items[0]['active'] = true;
?>

<?php ContentViewPanel::begin([
  'class' => 'well',
]) ?>

<?php $this->render('actions');?>
<?php $this->beginBlock('actions');
  if (isset($this->blocks['list_button'])) {
    echo $this->blocks['list_button'];
  }
$this->endBlock(); ?>

<div class="content-form">
  <?php

  $form = ActiveForm::begin(['options' => ['class' => 'form-horizontal']]);

  echo TabsX::widget([
    'items' => $items,
    'position' => TabsX::POS_ABOVE,
    'align' => TabsX::POS_RIGHT,
    'encodeLabels' => false
  ]); ?>

  <hr/>
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary pull-right']); ?>
    </div>
  </div>

  <?php ActiveForm::end(); ?>

</div>

<?php ContentViewPanel::end() ?>
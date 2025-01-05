<?php

use mcms\common\form\AjaxActiveKartikForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

/** @var array $modalOptions */
/** @var string $containerId */
/** @var array $formAction */
/** @var string $updatePjaxId */
/** @var string $formView */
/** @var \yii\base\Model $formModel */

Modal::begin($modalOptions);
Pjax::begin(['id' => $containerId . '-form-pjax-container', 'timeout' => false]);
$form = AjaxActiveKartikForm::begin([
  'id' => $containerId . '-form',
  'enableAjaxValidation' => false,
  'action' => $formAction,
  'type' => AjaxActiveKartikForm::TYPE_HORIZONTAL,
  'formConfig' => [
    'labelSpan' => 4,
    'deviceSize' => AjaxActiveKartikForm::SIZE_MEDIUM,
    'showLabels' => true,
    'showErrors' => true,
    'showHints' => false,
  ],
  'ajaxSuccess' => "function(response){ if (response && response.success) { $.pjax.reload({ container: '{$updatePjaxId}', 'timeout': 5000 }); $('.{$containerId}-modal').modal('hide'); } }"
]); ?>

<?= $this->render($formView, [
  'model' => $formModel,
  'form' => $form,
]) ?>

<?php AjaxActiveKartikForm::end(); ?>
<?php
Pjax::end();
Modal::end();

?>
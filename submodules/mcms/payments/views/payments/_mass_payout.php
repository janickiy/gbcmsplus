<?php

use yii\web\View;
use yii\helpers\Html;

/** @var mcms\payments\models\forms\MassPayoutForm $model */
/** @var mcms\common\form\AjaxActiveKartikForm $form */


$reasonRequiredTypes = json_encode($model->getReasonRequiredTypes());

$js = <<<JS
  $(function(){
    var reasonRequiredTypes = JSON.parse('{$reasonRequiredTypes}');
    function triggerReason() {
      reasonRequiredTypes.indexOf($('#masspayoutform-type').val()) === -1
        ? $(".field-masspayoutform-reason").hide()
        : $(".field-masspayoutform-reason").show()
        ;
    }
    
    triggerReason();
    
    $('#masspayoutform-type').on('change', triggerReason);
    
  })
JS;
$this->registerJs($js, View::POS_READY);
?>

<?= $form->field($model, 'type')->dropDownList($model->getTypes()) ?>
<?= $form->field($model, 'reason')->textarea() ?>

<?= $form->field($model, 'selected_id_list')->hiddenInput()->label(false) ?>

<?= Html::submitButton(Yii::_t('payments.proceed'), ['class' => 'btn btn-primary submit-button', 'name' => 'submit']) ?>

<?php
/** @var \yii\web\View $view */
/** @var \mcms\promo\models\PartnerProgram $model */
/** @var \mcms\common\form\AjaxActiveKartikForm $form */
?>
<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'description')->textarea() ?>
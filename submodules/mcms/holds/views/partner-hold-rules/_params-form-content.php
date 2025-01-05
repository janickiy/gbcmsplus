<?php
/** @var \yii\web\View $view */
/** @var \mcms\holds\models\HoldProgram $model */
/** @var \mcms\common\form\AjaxActiveKartikForm $form */
?>
<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'description')->textarea() ?>
<?= $form->field($model, 'is_default')->checkbox()->hint(Yii::_t('holds.main.is_default-hint')) ?>
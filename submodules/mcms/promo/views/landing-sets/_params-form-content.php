<?php
/** @var \yii\web\View $this */
/** @var \mcms\promo\models\LandingSet $model */
/** @var \kartik\form\ActiveForm $form */
use mcms\common\helpers\Html;
use mcms\promo\models\LandingCategory;

?>
<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'category_id')->dropDownList(LandingCategory::getAllMap(), ['prompt' => Yii::_t('app.common.not_selected')]) ?>
<?= $form->field($model, 'autoupdate')->checkbox()->hint(Yii::_t('promo.landing_sets.autoupdate-warning')) ?>
<?= $form->field($model, 'description')->textarea() ?>



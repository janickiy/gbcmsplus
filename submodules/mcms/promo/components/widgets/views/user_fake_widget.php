<?php

use mcms\common\form\AjaxActiveKartikForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = AjaxActiveKartikForm::begin([
  'action' => Url::to(['/promo/user-fake-settings/update/', 'user_id' => $model->user_id])
]); ?>
<?= $form->field($model, 'add_fake_after_subscriptions')?>
<?= $form->field($model, 'add_fake_subscription_percent')?>
<?= $form->field($model, 'add_fake_cpa_subscription_percent')?>
<?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary pull-right'])?>
<?php AjaxActiveKartikForm::end(); ?>
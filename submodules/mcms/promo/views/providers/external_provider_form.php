<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\AbstractProviderSettings;
use mcms\promo\models\Provider;
use yii\widgets\Pjax;

/** @var Provider $model Провайдер */
/** @var AbstractProviderSettings|null $settings Настройки провайдера */
/** @var \yii\web\View $this */
/** @var string $pbHandlerUrl */
?>

<?php Pjax::begin([
  'id' => 'provider-form-pjax',
  'enablePushState' => false,
  'enableReplaceState' => false,
]) ?>
<?php $form = AjaxActiveForm::begin([
  'id' => 'provider-form',
  'ajaxSuccess' => Modal::ajaxSuccess('#providers-info'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'code'); ?>
    <?= $form->field($model, 'url'); ?>

    <p>
      <?= Html::a('<i class="glyphicon glyphicon-question-sign"></i> ' . Yii::_t('providers.default_provider_url_help_link'), '#provider-url-help', [
        'class' => '',
        'role' => 'button',
        'data-toggle' => 'collapse'
      ], true); ?>
    </p>

    <div class="collapse" id="provider-url-help">
      <?= $this->render('_url_variables'); ?>
    </div>

    <?= $form->field($model, 'status')->dropDownList($model->getStatuses()); ?>

    <p>
      <?= Html::a('<i class="glyphicon glyphicon-question-sign"></i> ' . Yii::_t('providers.default_pb_url_help_link'), '#provider-postback-help', [
        'class' => '',
        'role' => 'button',
        'data-toggle' => 'collapse'
      ], true); ?>
    </p>

    <div class="collapse" id="provider-postback-help">
      <div class="well">
        <p><?= Yii::_t('providers.default_pb_url') ?>: <code><?= $pbHandlerUrl ?: 'please ask your manager' ?></code></p>
        <p><?= Yii::_t('providers.default_pb_url_params') ?>:</p>

        <?= $this->render('_default_pb_parameters', ['verifyToken' => $model->secret_key]); ?>

        <p><?= Yii::_t('providers.default_pb_url_example') ?>:</p>

        <p><pre><?= $pbHandlerUrl ?: 'http://{your_postback_url}/' ?>?<?= htmlentities("verify_token=$model->secret_key&hit_id=123456&transaction_type=rebill&sum=5&currency=rub&phone=123456789&action_time=1517326299") ?>
        </pre></p>
      </div>
    </div>

    <?php if ($settings) { ?>
      <div class="well">
        <?= $this->render('provider_settings/default', ['form' => $form, 'model' => $settings]) ?>
      </div>
    <?php } ?>

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
<?php Pjax::end() ?>
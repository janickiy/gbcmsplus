<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\notifications\components\assets\CreateNotifyAsset;

CreateNotifyAsset::register($this);
/* @var \mcms\notifications\models\NotificationCreationForm $model */
?>

<?php
$ajaxSuccess = Modal::ajaxSuccess('#notificationsDeliveryGrid');
$form = AjaxActiveKartikForm::begin([
  'id' => 'module-event',
  'options' => [
    'data-notification-types' => $notificationTypes,
  ],
  'messageSuccess' => Yii::_t('notifications.notifications.delivery_create_success'),
  'ajaxSuccess' => /** @lang JavaScript */ "
  function(event, jqXHR, ajaxOptions, data) {
    if (!event.data.isTest) {
      ($ajaxSuccess)();  
    }
  }
  ",
]); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?= $this->title ?></h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-9">
              <?= MultiLangForm::widget([
                'model' => $model,
                'form' => $form,
                'attributes' => $model->getFormAttributes()
              ]); ?>

              <?= $this->render('../partial/replacements_collapse', [
                'replacementsDataProvider' => $replacementsDataProvider
              ]); ?>

            </div>

            <div class="col-md-3">
                <h3><?php echo Yii::_t('main.settings') ?></h3>

              <?= $form->field($model, 'roles[]')->checkboxList($model->getRoles(), ['unselect' => null]); ?>
              <?= $form->field($model, 'notificationType[]')->checkboxList($model->notificationTypes, ['unselect' => null]); ?>

              <?= $form->field($model, 'isImportant')->checkbox([
                'label' => Html::tag('span', $model->getAttributeLabel('isImportant')) .
                  (Html::tag('p', Yii::_t('notifications.notifications.send_inactive_users'), ['class' => 'note'])),
                'class' => 'checkbox',
              ]); ?>

              <?= $form
                ->field($model, 'isReplace')
                ->checkbox([
                  'label' => Html::tag('span', $model->getAttributeLabel('isReplace')) .
                    (Html::tag('p', Yii::_t('notifications.labels.notification_creation_isReplace_hint'), ['class' => 'note'])),
                  'class' => 'checkbox',
                ]); ?>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="row">
            <div class="col-md-12">
              <?php if (Yii::$app->user->can('NotificationsDeliveryTest')) { ?>
                <?= Html::submitButton(
                  '<i class="glyphicon glyphicon-wrench"></i> ' . Yii::_t('notifications.notifications.test_delivery'),
                  ['name' => $model->formName() . '[isTest]', 'value' => 1, 'class' => 'btn btn-info']
                ) ?>
              <?php } ?>
              <?= Html::submitButton(
                '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'),
                ['class' => 'btn btn-success']
              ) ?>
            </div>
        </div>
    </div>
<?php AjaxActiveKartikForm::end(); ?>
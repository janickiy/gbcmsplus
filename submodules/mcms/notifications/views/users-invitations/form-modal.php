<?php

use mcms\common\helpers\Html;
use mcms\mcms\common\widget\RgkTinyMce;
use mcms\notifications\models\NotificationInvitationForm;
use rgk\theme\smartadmin\widgets\controls\Select2;
use rgk\utils\widgets\form\AjaxActiveForm;
use rgk\utils\widgets\modal\Modal;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var NotificationInvitationForm $model
 * @var \yii\data\ActiveDataProvider $replacementsDataProvider
 */


$this->title = Yii::_t('notifications.forms.users_invitations_email_create');

?>
<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#usersInvitationsPjaxGrid'),
  'options' => [
    'enctype' => 'multipart/form-data',
  ],
  'isFilesAjaxUpload' => true,
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-sm-12">
        <?= $form->field($model, 'from')->textInput([
          'placeholder' => $model->getDefaultFrom(),
        ]); ?>
        <?= $form->field($model, 'header') ?>
        <?= $form->field($model, 'template')->widget(RgkTinyMce::class, [
          'language' => Yii::$app->language,
          'options' => ['class' => 'editor', 'id' => Html::getUniqueId()],
          'clientOptions' => [
            'height' => 400,
            'plugins' => [
              'image code lists link hr fullscreen',
            ],
            'menubar' => false,
            'branding' => false,

            'toolbar' => 'code formatselect bold italic strikethrough bullist numlist outdent indent image link align hr fullscreen',
            'images_upload_url' => Html::hasUrlAccess(['/notifications/notifications/image-upload/'])
              ? Url::toRoute(['notifications/image-upload/'])
              : null,
            'image_dimensions' => false,
            'image_description' => false,
            'image_class_list' => [
              ['title' => 'Full width', 'value' => 'img-full-width'],
              ['title' => 'Common', 'value' => 'img-common'],
            ],

            'relative_urls' => false,
            'remove_script_host' => false,
            'convert_urls' => true,
            'content_style' => '.img-full-width { width: 100%; margin: 0; }',
          ]
        ]) ?>

        <?= $this->render('../partial/replacements_collapse', [
          'replacementsDataProvider' => $replacementsDataProvider
        ]); ?>

        <?= $form->field($model, 'invitation_id')->widget(Select2::class, [
          'pluginOptions' => [
            'allowClear' => true,
            'ajax' => [
              'url' => Url::to(['/users/users-invitations/select2']),
              'dataType' => 'json',
              'data' => new JsExpression('function (params) {
                return {
                  strictSearch: 0,
                  q: params.term ? params.term : "",
                  username: params.term ? params.term : "",
                  status: 0
                };
          }')
            ],
          ]
        ])->hint(Yii::_t('notifications.invitations.invitation_creation_invitation_hint'), ['class' => 'note']) ?>

        <?= $form->field($model, 'send')->checkbox([
          'label' => Html::tag('span', $model->getAttributeLabel('send')) .
            (Html::tag('p', Yii::_t('notifications.invitations.invitation_creation_send_hint'), ['class' => 'note'])),
          'class' => 'checkbox',
        ]) ?>

        <?= $form->field($model, 'force_send')->checkbox([
          'label' => Html::tag('span', $model->getAttributeLabel('force_send')) .
            (Html::tag('p', Yii::_t('notifications.invitations.invitation_creation_force_send_hint'), ['class' => 'note'])),
          'class' => 'checkbox',
        ]) ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-success pull-right']) ?>
  </div>
<?php $form->end() ?>
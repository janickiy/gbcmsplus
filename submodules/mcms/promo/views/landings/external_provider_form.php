<?php
use kartik\widgets\FileInput;
use mcms\common\grid\ContentViewPanel;
use mcms\promo\assets\LandingsFormAsset;
use mcms\promo\models\Provider;
use yii\widgets\ActiveForm;
use mcms\common\helpers\Html;

\yii\bootstrap\BootstrapPluginAsset::register($this);
LandingsFormAsset::register($this);

$this->registerJs('
  $(\'[data-toggle="popover"]\').popover();
');
?>

<?php ContentViewPanel::begin([
]) ?>
<?php $form = ActiveForm::begin([
  'id' => 'landing-form',
  'options' => [
    'enctype' => 'multipart/form-data',
  ],
]); ?>

  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

  <div class="row">
    <div class="col-md-9">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title pull-left"><?= $this->title?></h3>
          <div class="clearfix"></div>
        </div>
        <div class="panel-body">
          <div class="row">
            <div class="col-md-4">
              <?php $initPreview = $model->image_src ? [Html::img($model->image_src, ['class' => 'file-preview-image'])] : [] ?>

              <?= $form->field($model, 'imageFile', ['options' => ['enableClientValidation' => false]])->widget('kartik\widgets\FileInput', [
                'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                  'showUpload' => false,
                  'initialPreview' => $initPreview,
                  'showRemove' => false,
                  'showClose' => false,
                  'showCaption' => false
                ]
              ])->label(Yii::_t('promo.landings.attribute-image_src')) ?>


        </div>
        <div class="col-md-8">
          <?= $form->field($model, 'name') ?>
          <?= $form->field($model, 'description')->textArea() ?>
          <?= $form->field($model, 'comment')->textArea() ?>
          <?= $form->field($model, 'promo_materials')->textInput(['readonly' => true]) ?>
          <?= $form->field($model, 'promoMaterialsFile', ['options' => ['enableClientValidation' => false]])
            ->widget(FileInput::class, [
              'options' => ['accept' => 'application/zip'],
              'pluginOptions' => [
                'showPreview' => false,
                'showUpload' => false,
                'initialPreview' => [$model->promo_materials_file_src],
                'initialCaption' => [$model->getPromoMaterialsFileName()]
              ]
            ])->label(Yii::_t('promo.landings.attribute-promo_materials_file_src'))
          ?>
          <?= $form->field($model, 'platformIds')->widget('mcms\common\widget\Select2', [
            'data' => $platforms,
            'options' => ['multiple' => true],
            'pluginOptions' => [
              'placeholder' => Yii::_t('app.common.choose'),
              'tags' => true,
            ]
          ]) ?>
          <?= $form->field($model, 'forbiddenTrafficTypeIds')->widget('mcms\common\widget\Select2', [
            'data' => $forbiddenTrafficTypes,
            'options' => ['multiple' => true],
            'pluginOptions' => [
              'placeholder' => Yii::_t('app.common.choose'),
              'tags' => true
            ]
          ]) ?>
            </div>
          </div>
        </div>
      </div>
      <div style="margin:50px 0;">
        <?= $this->render('_dynamic_form', [
          'model' => $model,
          'onetimeId' => $onetimeId,
          'payTypes' => $payTypes,
          'subscriptionTypes' => $subscriptionTypes,
          'form' => $form,
          'showDaysHold' => $showDaysHold,
          ]) ?>
      </div>

    </div>
    <div class="col-md-3">
      <div class="well">
        <?= $form->field($model, 'offer_category_id')->dropDownList($model->offerCategories, ['prompt' => Yii::_t('app.common.not_selected')]) ?>
        <?= $form->field($model, 'category_id')->dropDownList($model->categories, ['prompt' => Yii::_t('app.common.not_selected')]) ?>
        <hr>
        <?= $form->field($model, 'provider_id')->dropDownList(Provider::getNotRgkProviders(), ['prompt' => Yii::_t('app.common.not_selected')]) ?>
        <?= $form->field($model, 'send_id') ?>
        <?= $form->field($model, 'custom_url') ?>
        <hr>

        <?= $form->field(
          $model,
          'access_type',
          ['options' => ['class' => 'form-group']]
        )->radioList($model->accessTypes, ['separator' => '&nbsp;&nbsp;&nbsp;&nbsp;'])->label(
          $model->getAttributeLabel('access_type') .
          ' <button type="button" class="btn btn-default btn-xs" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-html=true data-content="' . Yii::_t('promo.landings.attribute_hint-access_type') . '"><i class="glyphicon glyphicon-question-sign"></i></button>'
        ) ?>
        <hr>

        <?php
        $statusLabel = $model->getAttributeLabel('status') .
          (
          Provider::canEditAllProviders()
            ? ' <button type="button" class="btn btn-default btn-xs" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-html=true data-content="' . Yii::_t('promo.landings.attribute_hint-status') . '"><i class="glyphicon glyphicon-question-sign"></i></button>'
            : ''
          );

        ?>
        <?= $model->canChangeStatus()
          ? $form->field(
            $model,
            'status',
            ['options' => ['class' => 'form-group']]
          )->radioList($model->statuses, ['separator' => '&nbsp;&nbsp;&nbsp;&nbsp;'])->label($statusLabel) . '<hr>'
          : '' ?>
      </div>

    </div>
  </div>

  <hr>
  <div class="form-group clearfix">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right']) ?>
  </div>

<?php ActiveForm::end(); ?>


<?php ContentViewPanel::end() ?>
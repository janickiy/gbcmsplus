<?php
/** @var \yii\web\View $this */
/** @var \mcms\holds\models\HoldProgram $model */
/** @var \yii\data\ActiveDataProvider $ruleDataProvider */
/** @var \yii\data\ActiveDataProvider $usersDataProvider */
/** @var \mcms\holds\models\HoldProgramRuleSearch $ruleSearchModel */
/** @var \mcms\user\models\search\User $usersSearchModel */

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\holds\components\UnholdSettingsDescription;
use mcms\holds\models\HoldProgramRule;
use mcms\promo\models\Country;
use yii\bootstrap\Html as BHtml;
use yii\widgets\Pjax;

$toolbar = Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => BHtml::icon('plus') . ' ' . Yii::_t('holds.main.add-rule'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/holds/partner-hold-rule-items/create-modal', 'hold_program_id' => $model->id],
]);

?>

<div class="row">
  <!-- Правила -->
  <div class="col-sm-8">
    <?php ContentViewPanel::begin([
      'header' => Yii::_t('holds.main.rules'),
      'buttons' => [],
      'padding' => false,
      'toolbar' => $toolbar,
    ]) ?>
    <?php Pjax::begin(['id' => 'hold_rules_list_pjax']) ?>
    <?= AdminGridView::widget([
      'dataProvider' => $ruleDataProvider,
      'filterModel' => $ruleSearchModel,
      'export' => false,
      'columns' => [
        [
        'attribute' => 'country_id',
        'label' => Yii::_t('promo.operators.attribute-country_id'),
        'filter' => Select2::widget([
          'model' => $ruleSearchModel,
          'attribute' => 'country_id',
          'data' => Country::getDropdownItems(),
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true
          ]
        ]),
        'format' => 'raw',
        'value' => 'country.viewLink'
        ],
        [
          'label' => Yii::_t('holds.main.rules'),
          'format' => 'raw',
          'value' => function (HoldProgramRule $rule) {
            return UnholdSettingsDescription::getModelDescription($rule, '<br>');
          }
        ],
        [
          'class' => 'mcms\common\grid\ActionColumn',
          'template' => '{update-modal} {delete}',
          'controller' => 'holds/partner-hold-rule-items',
          'contentOptions' => ['style' => 'width: 10%'],
        ],
      ],
    ]); ?>
    <?php Pjax::end() ?>
    <?php ContentViewPanel::end() ?>
  </div>
  <!-- Правила -->

  <div class="col-sm-4">
    <!-- Параметры группы -->
    <div class="col-sm-12">
      <?php ContentViewPanel::begin([
        'header' => Yii::_t('holds.main.params'),
        'buttons' => [],
      ]) ?>
      <?php $form = AjaxActiveKartikForm::begin() ?>
      <?= $this->render('_params-form-content', ['model' => $model, 'form' => $form]) ?>
      <div class="row">
        <div class="col-sm-12">
          <?= Html::submitButton(
            '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'), ['class' => 'pull-right btn btn-primary']
          ) ?>
        </div>
      </div>
      <?php AjaxActiveKartikForm::end() ?>
      <?php ContentViewPanel::end() ?>
    </div>
    <!-- /Параметры программы -->

    <!-- Управление партнерами -->
    <div class="col-sm-12">
      <?php ContentViewPanel::begin([
        'padding' => false,
        'header' => Yii::_t('holds.main.partners'),
        'buttons' => [],
        'toolbar' => Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'a',
            'title' => Yii::t('yii', 'Off'),
            'label' => BHtml::icon('plus') . ' ' . Yii::_t('holds.main.add-partner'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => ['/holds/partner-hold-rules/link-partner/', 'id' => $model->id],
        ])
      ]) ?>
      <?php Pjax::begin(['id' => 'partners-pjax']); ?>
      <?= AdminGridView::widget([
        'dataProvider' => $usersDataProvider,
        'filterModel' => $usersSearchModel,
        'export' => false,
        'rowOptions' => function ($model) {
          switch ($model->status) {
            case $model::STATUS_ACTIVATION_WAIT_HAND:
            case $model::STATUS_ACTIVATION_WAIT_EMAIL:
              return ['class' => 'warning'];
            case $model::STATUS_DELETED:
            case $model::STATUS_BLOCKED:
            case $model::STATUS_INACTIVE:
              return ['class' => 'danger'];
            default:
              return ['class' => ''];
          }
        },
        'columns' => [
          [
            'attribute' => 'id',
            'contentOptions' => ['class' => 'col-max-width-100'],
          ],
          [
            'attribute' => 'email',
            'format' => 'raw',
            'value' => function ($model) use ($usersModule) {
              $url = $usersModule->api('userLink')->getUserUpdateLinkParams($model->id);
              return Html::a($model->email, $url, ['data-pjax' => 0]);
            },
          ],
          [
            'class' => 'mcms\common\grid\ActionColumn',
            'buttonsPath' => ['delete' => '/holds/partner-hold-rules/unlink-partner/'],
            'template' => '{delete}',
            'contentOptions' => ['class' => 'col-min-width-100'],
          ],
        ],
      ]); ?>
      <?php Pjax::end(); ?>
      <?php ContentViewPanel::end() ?>
    </div>
    <!-- /Управление партнерами -->
  </div>
</div>

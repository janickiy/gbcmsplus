<?php
/** @var \yii\web\View $this */
/** @var \mcms\promo\models\PartnerProgram $model */
/** @var \yii\data\ActiveDataProvider $itemsDataProvider */
/** @var mcms\promo\models\search\PartnerProgramItemSearch $itemsSearchModel */
/** @var \yii\data\ActiveDataProvider $usersDataProvider */
  /** @var mcms\user\models\search\User $usersSearchModel */
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\AjaxRequest;
use mcms\common\widget\MassDeleteWidget;
use mcms\common\widget\MassUpdateGridView;
use mcms\common\widget\MassUpdateWidget;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\PartnerProgram;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PartnerProgramItemMassModel;
use mcms\promo\models\PersonalProfit;
use rgk\utils\widgets\AjaxButton;
use yii\bootstrap\Html as BHtml;
use yii\widgets\Pjax;

$toolbar = Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => BHtml::icon('plus') . ' ' . Yii::_t(PartnerProgramItem::LANG_PREFIX . 'create-condition'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['partner-program-items/create-modal', 'partnerProgramId' => $model->id],
]) . MassUpdateWidget::widget([
  'model' => new PartnerProgramItemMassModel(['model' => new PartnerProgramItem]),
  'pjaxId' => '#partner-program-items-list',
]) . MassDeleteWidget::widget(['pjaxId' => '#partner-program-items-list']) . ' ' .
AjaxButton::widget([
  'options' => ['class' => 'btn btn-xs btn-success', AjaxButton::RELOAD_ATTRIBUTE => 1],
  'text' => Yii::_t('promo.partner_programs.partner_program_clean'),
  'url' => ['/promo/partner-programs/clean', 'id' => $model->id]
]);
?>


<div class="row">
  <!-- Управление условиями -->
  <div class="col-sm-8">
    <?php ContentViewPanel::begin([
      'header' => Yii::_t(PartnerProgram::LANG_PREFIX . 'conditions'),
      'buttons' => [],
      'padding' => false,
      'toolbar' => $toolbar,
    ]) ?>
    <?php Pjax::begin(['id' => 'partner-program-items-list']) ?>
    <?= AdminGridView::widget([
      'dataProvider' => $itemsDataProvider,
      'filterModel' => $itemsSearchModel,
      'export' => false,
      'rowOptions' => function ($model) {
      /** @var PartnerProgramItem $model */
        return $model->landing ? ['class' => $model->landing->getStatusColors()] : [];
      },
      'columns' => [
        [
          'class' => 'yii\grid\CheckboxColumn',
          'headerOptions' => ['style' => 'padding-right: 0; padding-left: 0 !important'],
        ],
        [
          'attribute' => 'id',
          'contentOptions' => ['style' => 'width: 5%'],
        ],
        [
          'attribute' => 'operator_id',
          'format' => 'raw',
          'value' => 'operatorLink',
          'filter' => OperatorsDropdown::widget([
            'model' => $itemsSearchModel,
            'attribute' => 'operator_id',
            'options' => [
              'placeholder' => '',
            ],
            'pluginOptions' => ['allowClear' => true],
            'useSelect2' => true
          ]),
          'contentOptions' => ['style' => 'width: 25%'],
        ],
        [
          'attribute' => 'landing_id',
          'format' => 'raw',
          'value' => 'landingLink',
          'filter' => LandingsDropdown::widget([
            'model' => $itemsSearchModel,
            'attribute' => 'landing_id',
            'options' => [
              'placeholder' => '',
            ],
            'pluginOptions' => ['allowClear' => true],
            'useSelect2' => true
          ]),
          'contentOptions' => ['style' => 'width: 25%'],
        ],
        [
          'attribute' => 'rebill_percent',
          'contentOptions' => ['style' => 'width: 5%'],
        ],
        [
          'attribute' => 'buyout_percent',
          'contentOptions' => ['style' => 'width: 5%'],
        ],
        [
          'visible' => PersonalProfit::canManagePersonalCPAPrice(),
          'label' => Yii::_t(PartnerProgramItem::LANG_PREFIX . 'cpa-profit'),
          'format' => 'html',
          'value' => function (PartnerProgramItem $item) {
            $values = [];
            if ($item->cpa_profit_rub) $values[] = $item->cpa_profit_rub . '&nbsp;Р';
            if ($item->cpa_profit_usd) $values[] = '$&nbsp;' . $item->cpa_profit_usd;
            if ($item->cpa_profit_eur) $values[] = '&euro;&nbsp;' . $item->cpa_profit_eur;

            return $values ? implode('&nbsp;|&nbsp;', $values) : null;
          },
          'contentOptions' => ['style' => 'width: 15%'],
        ],
        [
          'class' => 'mcms\common\grid\ActionColumn',
          'template' => '{update-modal} {delete}',
          'controller' => 'promo/partner-program-items',
          'contentOptions' => ['style' => 'width: 10%'],
        ],
      ],
    ]); ?>
    <?php Pjax::end() ?>
    <?php ContentViewPanel::end() ?>
  </div>
  <!-- /Управление условиями -->

  <div class="col-sm-4">
    <!-- Параметры программы -->
    <div class="col-sm-12">
      <?php ContentViewPanel::begin([
        'header' => Yii::_t(PartnerProgram::LANG_PREFIX . 'params'),
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
        'header' => Yii::_t(PartnerProgram::LANG_PREFIX . 'partners'),
        'buttons' => [],
        'toolbar' => Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'a',
            'title' => Yii::t('yii', 'Off'),
            'label' => BHtml::icon('plus') . ' ' . Yii::_t(PartnerProgram::LANG_PREFIX . 'add-partner'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => ['/promo/partner-programs/link-partner/', 'id' => $model->id],
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
            'value' => function ($model) use ($userModule) {
              $url = $userModule->api('userLink')->getUserUpdateLinkParams($model->id);
              return Html::a($model->email, $url, ['data-pjax' => 0]);
            },
          ],
          [
            'class' => 'mcms\common\grid\ActionColumn',
            'buttonsPath' => ['delete' => '/promo/partner-programs/unlink-partner/'],
            'template' => '{delete} {sync-partner} {autosync-enable} {autosync-disable}',
            'contentOptions' => ['class' => 'col-min-width-100'],
            'buttons' => [
              'sync-partner' => function ($url, $model) {
                return AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('refresh'),
                  'pjaxId' => '#partners-pjax',
                  'useAccessControl' => false,
                  'confirm' => Yii::_t('promo.partner_programs.are-you-sure-want-to-sync'),
                  'options' => [
                    'title' => Yii::_t('promo.partner_programs.partner-sync'),
                    'class' => 'btn btn-xs btn-info',
                  ]
                ]);
              },
              'autosync-enable' => function ($url, $model) {
                return !$model->userPromoSettings->isPartnerProgramAutosync() ? AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('ok'),
                  'pjaxId' => '#partners-pjax',
                  'useAccessControl' => false,
                  'options' => [
                    'title' => Yii::_t('promo.partner_programs.attribute-partner_program_aytosync'),
                    'class' => 'btn btn-xs btn-success',
                  ]
                ]) : null;
              },
              'autosync-disable' => function ($url, $model) {
                return $model->userPromoSettings->isPartnerProgramAutosync() ? AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('remove'),
                  'pjaxId' => '#partners-pjax',
                  'useAccessControl' => false,
                  'options' => [
                    'title' => Yii::_t('promo.partner_programs.attribute-partner_program_aytosync'),
                    'class' => 'btn btn-xs btn-danger',
                  ]
                ]) : null;
              },
            ]
          ],
        ],
      ]); ?>
      <?php Pjax::end(); ?>
      <?php ContentViewPanel::end() ?>
    </div>
    <!-- /Управление партнерами -->
  </div>
</div>

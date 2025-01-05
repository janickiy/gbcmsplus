<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\Editable;
use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\ProvidersDropdown;
use mcms\promo\models\Country;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\helpers\Html;
use mcms\promo\Module;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\models\PersonalProfit;
use mcms\common\widget\Select2;
use mcms\promo\models\PartnerProgram;

/** @var \yii\web\View $this */
/** @var integer $userId */
/** @var string $layout */
/** @var bool $renderCreateButton */
/** @var bool $enableFilters */
/** @var bool $renderActions */
/** @var bool $enableSort */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \mcms\promo\models\search\PersonalProfitSearch $searchModel */
/** @var array $ignoreIds */
/** @var int|null $userPartnerProgramId */
/** @var bool $userPartnerProgramAutosync */
$paymentsModule = Yii::$app->getModule('payments');
?>

<?php Pjax::begin(['id' => 'personal-profit-pjax-block']); ?>

<?php if (!$renderActions && $userPartnerProgramId): ?>
<label for="partner_program_id">
  <?= Yii::_t('promo.personal-profits.partner_program') ?>
  <?= ArrayHelper::getValue(PartnerProgram::dropdown(), $userPartnerProgramId) ?>
</label>
<?php endif ?>
<?php if ($userId && $renderActions): ?>
  <div class="form-group">
    <label for="partner_program_id"><?= Yii::_t('promo.personal-profits.partner_program') ?>
    <?= Editable::widget([
        'name' => 'partner_program_id',
        'asPopover' => true,
        'buttonsTemplate' => '{submit}',
        'submitButton'=>[
          'icon' => '<i class="glyphicon glyphicon-ok"></i>',
          'class' => 'btn btn-success',
        ],
        'displayValue' => $userPartnerProgramId ? PartnerProgram::dropdown()[$userPartnerProgramId] : null,
        'inputType' => Editable::INPUT_DROPDOWN_LIST,
        'data' => PartnerProgram::dropdown(),
        'pjaxContainerId' => 'personal-profit-pjax-block',
        'options' => ['class' => 'form-control', 'prompt' => Yii::_t('app.common.not_selected')],
        'editableValueOptions' => ['class' => 'text-danger kv-editable-link', 'disabled' => !\mcms\common\helpers\Html::hasUrlAccess(['/promo/partner-programs/link-partner-editable'])],
        'formOptions' => ['action' => Url::to(['/promo/partner-programs/link-partner-editable', 'userId' => $userId])],
        'pluginEvents' => [
          'editableSuccess' => 'function(){$.pjax.reload({container:"#personal-profit-pjax-block", timeout:false})}'
        ]
      ]) ?>
    </label>
    <?php if ($userPartnerProgramId): ?>
      <?= $userPartnerProgramAutosync
        ? \mcms\common\widget\AjaxRequest::widget([
          'url' => ['/promo/partner-programs/autosync-disable', 'id' => $userId],
          'pjaxId' => '#personal-profit-pjax-block',
          'title' => Html::icon('remove') .
            Yii::_t('promo.partner_programs.autosync-disable'),
          'options' => [
            'class' => 'btn btn-warning btn-xs',
          ],
        ])
        : \mcms\common\widget\AjaxRequest::widget([
          'url' => ['/promo/partner-programs/autosync-enable', 'id' => $userId],
          'pjaxId' => '#personal-profit-pjax-block',
          'title' => Html::icon('ok') . ' ' .
            Yii::_t('promo.partner_programs.autosync-enable'),
          'options' => [
            'class' => 'btn btn-success btn-xs',
          ],
          'confirm' => Yii::_t('promo.partner_programs.autosync-enable') . '?'
        ]) ?>

      <?= \mcms\common\widget\AjaxRequest::widget([
        'url' => ['/promo/partner-programs/sync-partner', 'id' => $userId],
        'pjaxId' => '#personal-profit-pjax-block',
        'title' => Html::icon('refresh') . ' ' .
          Yii::_t('promo.partner_programs.sync-now'),
        'options' => [
          'class' => 'btn btn-info btn-xs',
        ],
        'confirm' => Yii::_t('promo.partner_programs.sync-now') . '?'
      ]) ?>
    <?php endif ?>
  </div>
<?php endif ?>

<?php if(empty($emptyHeader)): ?>
  <?= Html::beginTag('section',['id'=>'widget-grid']);
  ContentViewPanel::begin([
    'padding' => false,
    'buttons' => [],
    'toolbar' => $renderCreateButton && !$userPartnerProgramAutosync
      ? $this->render('_create_button', ['userId' => $userId])
      : null,
  ]);
  ?>
<?php endif; ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $enableFilters ? $searchModel : null,
  'sorter' => [],
  'export' => false,
  'columns' => [
    [
      'attribute' => 'rebill_percent',
      'enableSorting' => $enableSort,
      'value' => function ($model) {
        if ($model->user_id == Yii::$app->user->id) return null;
        return $model->rebill_percent;
      }
    ],
    [
      'attribute' => 'buyout_percent',
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'cpa_profit_rub',
      'enableSorting' => $enableSort,
      'value' => function ($profitModel) use ($paymentsModule) {
        /** @var $profitModel \mcms\promo\models\PersonalProfit */
        if (!$profitModel->cpa_profit_rub || !$profitModel->user_id) return null;
        return sprintf(
          '%s',
          $profitModel->cpa_profit_rub
        );
      },
      'visible' => PersonalProfit::canManagePersonalCPAPrice()
    ],
    [
      'attribute' => 'cpa_profit_usd',
      'enableSorting' => $enableSort,
      'value' => function ($profitModel) use ($paymentsModule) {
        /** @var $profitModel \mcms\promo\models\PersonalProfit */
        if (!$profitModel->cpa_profit_usd || !$profitModel->user_id) return null;
        return sprintf(
          '%s',
          $profitModel->cpa_profit_usd
        );
      },
      'visible' => PersonalProfit::canManagePersonalCPAPrice()
    ],
    [
      'attribute' => 'cpa_profit_eur',
      'enableSorting' => $enableSort,
      'value' => function ($profitModel) use ($paymentsModule) {
        /** @var $profitModel \mcms\promo\models\PersonalProfit */
        if (!$profitModel->cpa_profit_eur || !$profitModel->user_id) return null;
        return sprintf(
          '%s',
          $profitModel->cpa_profit_eur
        );
      },
      'visible' => PersonalProfit::canManagePersonalCPAPrice()
    ],
    [
      'attribute' => 'user_id',
      'visible' => !$userId,
      'filter' => UserSelect2::widget([
        'model' => $searchModel,
        'options' => [
          'placeholder' => '',
        ],
        'attribute' => 'user_id',
        'initValueUserId' => $searchModel->user_id,
        'ignoreIds' => $ignoreIds,
      ]),
      'format' => 'raw',
      'value' => 'userLink',
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'value' => 'operatorLink',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'value' => 'landingLink',
      'filter' => LandingsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'provider_id',
      'format' => 'raw',
      'value' => 'providerLink',
      'filter' => ProvidersDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'provider_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'countryId',
      'label' => Yii::_t('promo.operators.attribute-country_id'),
      'format' => 'raw',
      'value' => 'countryLink',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'countryId',
        'data' => Country::getDropdownItems(),
        'options' => [
          'placeholder' => '',
          'multiple' => true
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {delete}',
      'controller' => 'promo/personal-profits',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'update-modal' => function ($url, $model) use ($userId) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'title' => Yii::t('yii', 'Update'),
              'label' => Html::icon('pencil'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => Url::to([
              '/' . Module::getInstance()->id . '/personal-profits/update-modal',
              'user_id' => $model->user_id,
              'landing_id' => $model->landing_id,
              'operator_id' => $model->operator_id,
              'country_id' => $model->country_id,
              'provider_id' => $model->provider_id,
              'isPersonal' => (bool)$userId,
            ]),
          ]);
        }
      ],
      'visible' => !$userPartnerProgramAutosync && $renderActions
    ],

  ],
]); ?>

<?php if(empty($emptyHeader)): ?>
  <?php ContentViewPanel::end() ?>
  <?= Html::endTag('section');?>
<?php endif; ?>

<?php Pjax::end(); ?>

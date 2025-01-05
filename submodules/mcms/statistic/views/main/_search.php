<?php
use kartik\form\ActiveForm;
use kartik\helpers\Html;
use mcms\common\widget\UserSelect2;
use mcms\statistic\components\FilterDropDownWidget;
use mcms\statistic\components\mainStat\FormModel;
use yii\helpers\Url;

/** @var FormModel $formModel */
/** @var int $maxGroups */
/** @var array $filterDatePeriods */
/** @var array $landingPayTypes */
/** @var array $providers */
/** @var array $landingCategories */
/** @var array $platforms */
/** @var array $countries */
/** @var array $operatorIds */
/** @var string[] $groups */
?>

<div class="default-filters-block">

  <?php if ($formModel->getPermissionsChecker()->canFilterByCurrency()) { ?>
    <?php $this->beginBlock('actions'); ?>

    <?= Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
      'type' => 'buttons',
      'containerId' => 'statisticCurrency'
    ])->getResult() ?>

    <?php $this->endBlock() ?>
  <?php } ?>


  <?php $form = ActiveForm::begin([
    'method' => 'GET',
    'action' => ['/' . Yii::$app->controller->getRoute()],
    'type' => ActiveForm::TYPE_INLINE,
    'options' => [
      'data-pjax' => true,
      'id' => 'statistic-filter-form',
    ],
  ]); ?>
  <div class="dt-toolbar">
    <div class="filters-left">
      <div class="filter_pos filter_pos_flex">
        <?= $this->render('_group_select', [
          'formModel' => $formModel,
          'maxGroups' => $maxGroups,
          'groups' => $groups,
        ]) ?>
      </div>
    </div>

    <div class="filters-right">

      <?= $this->render('_date_select', [
        'formModel' => $formModel,
        'form' => $form,
        'filterDatePeriods' => $filterDatePeriods
      ])?>

      <?php if ($formModel->getPermissionsChecker()->canFilterByCurrency()) : ?>
        <?= $form->field($formModel, 'currency')->hiddenInput([
          'id' => 'hiddenCurrency'
        ])->label(false); ?>
      <?php endif; ?>

      <div class="filter_pos">

        <?= Html::button(Html::icon('filter') . ' ' . Yii::_t('statistic.statistic.filters'), [
          'class' => 'btn btn-default',
          'data-toggle' => 'collapse',
          'href' => '#hidden-filters',
          'aria-expanded' => 'false',
          'aria-controls' => 'collapseExample',
        ])?>

      </div>

    </div>

    <div class="clearfix"></div>

  </div>

  <div class="collapse" id="hidden-filters">
    <div class="well">
      <div class="row">
        <?php if ($formModel->getPermissionsChecker()->canFilterByLandingPayTypes()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= Html::activeDropDownList(
            $formModel,
            'landingPayTypes',
            $landingPayTypes,
            [
              'class' => 'form-control',
              'prompt' => $formModel->getAttributeLabel('landingPayTypes'),
              'style' => 'width:100%;'
            ]
          )?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByProviders()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'providers')->widget(FilterDropDownWidget::class, ['items' => $providers]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByCountries()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'countries')->widget(FilterDropDownWidget::class, ['items' => $countries]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByOperators()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'operators')->widget(Yii::$app->getModule('promo')->api('operatorsDropdown')
            ->getWidgetclass, [
            'onlyActiveCountries' => false,
            'countriesId' => array_keys($countries),
            'operatorsId' => $operatorIds,
            'options' => [
              'title' => $formModel->getAttributeLabel('operators'),
              'prompt' => null,
              'multiple' => true,
              'data-selected-text-format' => 'count>0',
              'data-count-selected-text' => $formModel->getAttributeLabel('operators'). ' ({0}/{1})',
            ]]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByUsers()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= UserSelect2::widget([
            'model' => $formModel,
            'url' => ['stat-filters/users'],
            'options' => [
              'placeholder' => Yii::_t('statistic.users'),
              'multiple' => true,
            ],
            'attribute' => 'users',
            'initValueUserId' => $formModel->users,
          ]) ?>
        </div>
        <?php } ?>


        <?php if ($formModel->getPermissionsChecker()->canFilterByStreams()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'streams')->widget(Yii::$app->getModule('promo')->api('streamsDropdown')->getWidgetclass, [
            'initValueId' => $formModel->streams,
            'url' => Url::to(['stat-filters/streams']),
            'options' => [
              'placeholder' => Yii::_t('statistic.streams'),
              'multiple' => true,
            ]]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterBySources()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'sources')->widget(Yii::$app->getModule('promo')->api('sourcesDropdown')->getWidgetclass, [
            'initValueId' => $formModel->sources,
            'url' => Url::to(['stat-filters/sources']),
            'options' => [
              'placeholder' => Yii::_t('statistic.sources'),
              'multiple' => true,
            ]]) ?>
        </div>
        <?php } ?>


        <?php if ($formModel->getPermissionsChecker()->canFilterByLandings()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'landings')
            ->widget(Yii::$app->getModule('promo')->api('ajaxLandingsDropdown')->getWidgetclass, [
              'url' => Url::to(['/promo/landings/stat-filters-select2/']),
              'initValueId' => $formModel->landings,
              'options' => [
                'placeholder' => $formModel->getAttributeLabel('landings'),
                'multiple' => true,
              ]]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByLandingCategories()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'landingCategories')->widget(FilterDropDownWidget::class, ['items' => $landingCategories]) ?>
        </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByPlatform()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'platforms')->widget(FilterDropDownWidget::class, ['items' => $platforms]) ?>
        </div>
        <?php } ?>
        <div class="col-sm-3 pull-right">
          <?= Html::submitButton(Yii::_t('statistic.filter_submit'), ['class' => 'btn btn-info pull-right'])?>
          <?= Html::a(
            Yii::_t('statistic.filter_reset'),
            Url::to(['/' . Yii::$app->controller->getRoute()]),
            ['class' => 'btn btn-default pull-right', 'style' => 'margin-right: 10px']
          ) ?>
        </div>

        <?php if ($formModel->getPermissionsChecker()->canFilterByFakeRevshare()) {?>
        <div class="col-sm-3">
          <?= $form->field($formModel, 'isFake')->widget(FilterDropDownWidget::class, ['items' => [
            Yii::_t('statistic.statistic.is_fake_no'),
            Yii::_t('statistic.statistic.is_fake_yes'),
          ]]) ?>
        </div>
        <?php } ?>

      </div>

    </div>
  </div>


  <?php ActiveForm::end(); ?>

</div>
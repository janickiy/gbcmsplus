<?php
use kartik\form\ActiveForm;
use kartik\helpers\Html;
use mcms\mcms\api\components\widgets\implementations\OfferCategoriesComplexFilter;
use mcms\statistic\components\newStat\FormModel;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use mcms\mcms\api\components\widgets\implementations\CountriesComplexFilter;
use mcms\mcms\api\components\widgets\implementations\PartnersComplexFilter;
use mcms\mcms\api\components\widgets\implementations\LandingCategoriesComplexFilter;
use mcms\mcms\api\components\widgets\implementations\LandingPayTypesComplexFilter;
use mcms\mcms\api\components\widgets\implementations\ProvidersComplexFilter;
use mcms\mcms\api\components\widgets\implementations\StreamsComplexFilter;
use mcms\mcms\api\components\widgets\implementations\PlatformsComplexFilter;
use mcms\mcms\api\components\widgets\implementations\FakeComplexFilter;
use admin\widgets\onoffswitch\SwitchWidget;

/** @var FormModel $formModel */
/** @var int $maxGroups */
/** @var string[] $groups */
/** @var \yii\web\View $this */
/** @var int|null $selectedTemplateId */
/** @var string $customField */
?>

<div class="default-filters-block">

  <?php $form = ActiveForm::begin([
    'method' => 'GET',
    'action' => ['/' . Yii::$app->controller->getRoute()],
    'options' => [
      'data-pjax' => true,
      'id' => 'statistic-filter-form',
    ],
  ]); ?>
  <?= Html::hiddenInput('template', $selectedTemplateId, ['id' => 'statistic-template'])?>
  <div class="dt-toolbar">
    <div class="filters-left">
      <div class="filter_pos filter_pos_flex">
        <input value="<?= ArrayHelper::getValue($formModel->groups, 0) ?>" type="hidden" name="FormModel[groups][]" id="formmodel-groups" class="auto_filter statistic-group-filter">
        <?php if ($formModel->getPermissionsChecker()->canFilterByOperators()) { ?>
          <div class="filter_pos">
            <?= CountriesComplexFilter::widget([
              'formName' => 'FormModel',
              'fieldName' => 'countries',
              'relatedFieldName' => 'operators',
              'customFields' => [$customField, 'operators' => [$customField]],
              'orderFields' => [$customField => SORT_DESC, 'operators' => [$customField => SORT_DESC]],
            ])?>
          </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByUsers()) { ?>
          <div class="filter_pos">
            <?= PartnersComplexFilter::widget([
              'formName' => 'FormModel',
              'fieldName' => 'users',
              'relatedFieldName' => 'sources',
              'relatedFieldLabelMask' => '#{id}. {name} {if url}<a href="{url}"><span class="complex-filter-source-link"></span></a>{/if}',
              'fields' => ['id', 'username', 'sources' => ['id', 'name', 'url']],
              'customFields' => [$customField, 'sources' => [$customField]],
              'orderFields' => [$customField => SORT_DESC, 'sources' => [$customField => SORT_DESC]],
            ]); ?>
          </div>
        <?php } ?>

        <?php if ($formModel->getPermissionsChecker()->canFilterByLandings()) { ?>
          <div class="filter_pos">
            <?php /*LandingCategoriesComplexFilter::widget([
              'formName' => 'FormModel',
              'fieldName' => 'landingCategories',
              'relatedFieldName' => 'landings',
              'customFields' => [$customField, 'landings' => [$customField]],
              'orderFields' => [$customField => SORT_DESC, 'landings' => [$customField => SORT_DESC]],
            ]);*/ ?>
          </div>
          <div class="filter_pos">
            <?= OfferCategoriesComplexFilter::widget([
              'formName' => 'FormModel',
              'fieldName' => 'offerCategories',
              'relatedFieldName' => 'landings',
              'customFields' => [$customField, 'landings' => [$customField]],
              'orderFields' => [$customField => SORT_DESC, 'landings' => [$customField => SORT_DESC]],
            ]); ?>
          </div>
        <?php } ?>

      </div>
    </div>

    <div class="filters-right">

      <?= $this->render('_date_select', [
        'formModel' => $formModel,
        'form' => $form,
      ])?>

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
  </div>
  <div class="clearfix"></div>

  <div class="well">
    <div class="row collapse" id="hidden-filters">
      <?php if ($formModel->getPermissionsChecker()->canFilterByLandingPayTypes()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">

          <?= LandingPayTypesComplexFilter::widget([
            'formName' => 'FormModel',
            'fieldName' => 'landingPayTypes',
            'customFields' => [$customField],
            'orderFields' => [$customField => SORT_DESC],
          ]); ?>
        </div>
      <?php } ?>

      <?php if ($formModel->getPermissionsChecker()->canFilterByProviders()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= ProvidersComplexFilter::widget([
            'formName' => 'FormModel',
            'fieldName' => 'providers',
            'customFields' => [$customField],
            'orderFields' => [$customField => SORT_DESC],
          ]); ?>
        </div>
      <?php } ?>


      <?php if ($formModel->getPermissionsChecker()->canFilterByStreams()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= StreamsComplexFilter::widget([
            'formName' => 'FormModel',
            'fieldName' => 'streams',
            'customFields' => [$customField],
            'orderFields' => [$customField => SORT_DESC],
          ]); ?>
        </div>
      <?php } ?>

      <?php if ($formModel->getPermissionsChecker()->canFilterByPlatform()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= PlatformsComplexFilter::widget([
            'formName' => 'FormModel',
            'fieldName' => 'platforms',
            'customFields' => [$customField],
            'orderFields' => [$customField => SORT_DESC],
          ]); ?>
        </div>
      <?php } ?>

      <?php if ($formModel->getPermissionsChecker()->canFilterByFakeRevshare()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= FakeComplexFilter::widget([
            'formName' => 'FormModel',
            'fieldName' => 'isFake',
            'items' => [
              ['id' => 0, 'name' => Yii::_t('statistic.statistic.is_fake_no')],
              ['id' => 1, 'name' => Yii::_t('statistic.statistic.is_fake_yes')],
            ],
          ]); ?>
        </div>
      <?php } ?>

      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($formModel, 'ltvDateTo')->widget(DatePicker::class,[
          'pickerButton' => false,
          'type' => DatePicker::TYPE_COMPONENT_APPEND,
          'pluginOptions' => [
            'endDate' => Yii::$app->formatter->asDate('today', 'php:Y-m-d'),
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom',
            'weekStart' => 1
          ],
          'options' => [
            'placeholder' => $formModel->getAttributeLabel('ltvDateTo'),
            'autocomplete' => 'off',
          ]
        ])->label(false) ?>
      </div>

      <?php if ($formModel->getPermissionsChecker()->canFilterByProviders()) { ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($formModel, 'isNoRgk')->checkbox([
            'label' => Html::tag('span', $formModel->getAttributeLabel('isNoRgk')),
            'class' => 'checkbox',
          ]) ?>
        </div>
      <?php } ?>

    </div>
    <div class="row">
      <div class="col-sm-2 col-xs-12 col-md-3 pull-right">
        <?= Html::submitButton(Yii::_t('statistic.filter_submit'), ['class' => 'btn btn-info pull-right', 'id' => 'statistic-submit-btn']) ?>
        <?= Html::a(
          Yii::_t('statistic.filter_reset'),
          Url::to(['/' . Yii::$app->controller->getRoute()]),
          ['class' => 'btn btn-default pull-right', 'style' => 'margin-right: 10px']
        ) ?>
      </div>
      <div class="col-sm-2 col-xs-12 col-md-3 margin-bottom-10">
        <?= SwitchWidget::widget([
          'label' => Yii::_t('statistic.new_statistic_refactored.autosubmit'),
          'options' => [
            'id' => 'is_auto_apply',
            'name' => 'is_auto_apply'
          ],
        ]) ?>
      </div>
      <div class="col-sm-2 col-xs-12 col-md-3 margin-bottom-10">
        <?= SwitchWidget::widget([
          'label' => Yii::_t('statistic.new_statistic_refactored.autorefresh'),
          'options' => [
            'id' => 'is_auto_refresh',
            'name' => 'is_auto_refresh'
          ],
          'onLabel' => '5min'
        ]) ?>
      </div>
      <div class="col-sm-3 col-xs-12 col-md-3 margin-bottom-10">
        <?= SwitchWidget::widget([
          'model' => $formModel,
          'attribute' => 'decimals',
          'options' => [
            'uncheck' => 2,
            'value' => 4,
          ],
        ]) ?>
      </div>
    </div>
  </div>

  <?php ActiveForm::end(); ?>

</div>
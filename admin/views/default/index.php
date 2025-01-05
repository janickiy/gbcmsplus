<?php

use admin\assets\DashboardAsset;
use admin\assets\SelectpickerAsset;
use admin\dashboard\widgets\base\BaseWidget;
use kartik\form\ActiveForm;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var array[] $userWidgets */
/** @var string[] $widgetsSelect */
/** @var string[] $widgetsSelected */
/** @var array[] $userGadgets */
/** @var string[] $gadgetsSelect */
/** @var string[] $gadgetsSelected */
/** @var string $filters */
/** @var bool $canFilterByCurrency */
SelectpickerAsset::register($this);
DashboardAsset::register($this);
$this->params['showBreadcrumbs'] = false;

$countriesOptionsAttributes = [];
foreach ($countries as $key => $country) {
    $countriesOptionsAttributes[$key] = [
        'data-content' => Html::img('@web/img/flags/' . $country['code'] . '.svg') . ' ' . $country['name']
    ];
}

if (!array_merge($widgetsSelect, $gadgetsSelect)) return;
?>
<?php
$getDashboardDataUrl = Url::to(['get-dashboard-data']);
$this->registerJs(<<<JS
var itemsSelected = $('#dashboard-items-select').val();
$('#dashboard-items-select').on('hide.bs.select', function() {
  if (!compareArrays($(this).val(), itemsSelected)) $(this).parents('form').submit();
});

dashboardFilters.init($filters);
DashboardRequest.setUrl('$getDashboardDataUrl');
JS
);
?>
<?php $this->beginBlock('headerData'); ?>
<?php ActiveForm::begin() ?>
<h1 class="page-title"><?= Yii::_t('app.dashboard.dashboard') ?>
    <?= Html::dropDownList(
        'dashboard-items',
        array_merge($widgetsSelected, $gadgetsSelected),
        array_merge($gadgetsSelect, $widgetsSelect),
        [
            'id' => 'dashboard-items-select',
            'class' => 'selectpicker menu-right col-i',
            'multiple' => true,
            'title' => '<i class="fa-fw fa fa-gear"></i>',
            'data-selected-text-format' => 'static',
            'data-dropdown-align-right' => 0,
            'data-style' => 'dashboard-items-toggle btn-xs',
        ]
    ) ?>
</h1>
<?php ActiveForm::end() ?>
<?php $this->endBlock('headerData'); ?>

<?php if (($widgetsSelected || $gadgetsSelected) && $canFilterByCurrency): ?>
    <div class="row">
        <div class="col-xs-7">
            <div class="statbox__header_title"><?= Yii::_t('app.dashboard.overview') ?></div>
        </div>
        <div class="col-xs-5 text-right">
            <?= Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
                'type' => 'dropdown',
                'name' => 'currency',
                'containerId' => 'currency-change',
                'class' => 'selectpicker',
                'data' => [
                    'width' => 'auto',
                    'callback' => 'location.reload()',
                ],
                'data-width' => 'auto',
                'style' => 'width:auto',
            ])->getResult() ?>
        </div>
    </div>
<?php endif; ?>

<div class="overview">
    <?php /** @var array $gadget */ ?>
    <?php foreach ($userGadgets as $gadget) { //TODO отрефакторить тут и ниже, вынести проверку в модель ?>
        <?php if (
            !ArrayHelper::getValue($gadget, 'itemId')
        ) continue ?>
        <?= $gadget['class']::widget(array_merge($gadget, [
            'useFilters' => false,
        ])) ?>
    <?php } ?>
</div>
<!-- widget grid -->
<section id="widget-grid">
    <div class="row">
        <div class="col-xs-8 content-left">
            <?php if ($widgetsSelected || $gadgetsSelected): ?>
                <div class="filters">
                    <?=
                    Html::dropDownList(
                        'dashboard-countries',
                        null,
                        ArrayHelper::getColumn($countries, 'name'),
                        [
                            'id' => 'dashboard-countries',
                            'class' => 'selectpicker countries-select',
                            'title' => Yii::_t('app.dashboard.countries_filter-title'),
                            'data-selected-text-format' => 'count > 0',
                            'data-count-selected-text' => Yii::_t('app.dashboard.countries_filter-selected-text'),
                            'data-width' => 'auto',
                            'multiple' => true,
                            'options' => $countriesOptionsAttributes
                        ]
                    )
                    ?>
                    <div id="dashboard-periods" class="btn-group btn-period" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" name="dashboard-period" value="-6 days" checked>
                            <?= Yii::_t('app.dashboard.period-week') ?>
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="dashboard-period" value="-13 days">
                            <?= Yii::_t('app.dashboard.period-two-weeks') ?>
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="dashboard-period" value="-1 month">
                            <?= Yii::_t('app.dashboard.period-month') ?>
                        </label>
                    </div>
                    <?php if (in_array('wclicks_subscriptions', $widgetsSelected) || in_array('wprofit', $widgetsSelected)): ?>
                        <div class="switcher pull-right hidden-xs">
                            <div class="switcher__label"><?= Yii::_t('app.dashboard.forecast') ?></div>
                            <div class="switcher__bar">
                                <label>
                                    <input type="checkbox" id="dashboard-forecast" name="dashboard-forecast" checked>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="common_stats">
                <?php /** @var array $gadget */ ?>
                <?php foreach ($userGadgetsWithFilters as $gadget) { ?>
                    <?php if (
                        !ArrayHelper::getValue($gadget, 'itemId')
                    ) continue ?>
                    <?= $gadget['class']::widget($gadget) ?>
                <?php } ?>
            </div>
            <?php /** @var array $widget */ ?>
            <?php foreach ($userWidgets as $widget) { ?>
                <?php if (
                    ArrayHelper::getValue($widget, 'position', null) != BaseWidget::POSITION_LEFT ||
                    !ArrayHelper::getValue($widget, 'itemId')
                ) continue ?>
                <?= $widget['class']::widget($widget) ?>
            <?php } ?>
        </div>
        <div class="col-xs-4 content-right pull-right">
            <?php /** @var array $widget */ ?>
            <?php foreach ($userWidgets as $widget) { ?>
                <?php if (
                    ArrayHelper::getValue($widget, 'position', null) != BaseWidget::POSITION_RIGHT ||
                    !ArrayHelper::getValue($widget, 'itemId')
                ) continue ?>
                <?= $widget['class']::widget($widget) ?>
            <?php } ?>
        </div>
    </div>
</section>
<!-- end widget grid -->
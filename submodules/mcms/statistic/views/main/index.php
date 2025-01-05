<?php
use mcms\common\grid\ContentViewPanel;
use mcms\statistic\assets\MainAdminStatisticAsset;
use mcms\statistic\components\mainStat\DataProvider;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Grid;
use mcms\statistic\models\ColumnsTemplate;
use yii\widgets\Pjax;
use rgk\export\ExportMenu;
use yii\bootstrap\Html as BHtml;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/** @var DataProvider $dataProvider */
/** @var string $exportFileName */
/** @var FormModel $formModel */
/** @var Grid $exportGrid */
/** @var int $maxGroups */
/** @var array $filterDatePeriods */
/** @var array $landingPayTypes */
/** @var array $providers */
/** @var array $landingCategories */
/** @var array $platforms */
/** @var array $countries */
/** @var array $operatorIds */
/** @var string[] $groups */
/** @var array $columnsTemplates */
/** @var int|null $selectedTemplateId */
/** @var string $exportWidgetId */
?>

<?php
$selectedTemplateCookieKey = ColumnsTemplate::getSelectedTemplateCookieKey();

$columnsTemplatesOptions = [];
foreach ($columnsTemplates as $columnsTemplate) {
  $columnsTemplatesOptions[$columnsTemplate->id] = [
    'data-columns' => $columnsTemplate->columns,
    'data-content' => Html::tag('span',
      $columnsTemplate->name
      . (!$columnsTemplate->isNewRecord
        ? Html::tag('span', BHtml::icon('cog'), [
          'class' => 'columns-template-icon',
          'title' => Yii::t('yii', 'Update'),
          'data-template-id' => $columnsTemplate->id,
        ])
        : null),
      ['class' => 'text columns-template-text']),
  ];
}

/*
 * TRICKY отдельно выводим кнопки модалок и тригерим их при клике на икоку в селекте шаблонов
 * так сделано чтобы при клике на иконку не всплывало событие селекта
 */
foreach ($columnsTemplates as $columnsTemplate) {
  echo Modal::widget([
    'toggleButtonOptions' => [
      'class' => 'hidden columns-template-update-modal-button',
      'data-template-id' => $columnsTemplate->id,
    ],
    'url' => Url::to(['/statistic/column-templates/update', 'id' => $columnsTemplate->id]),
  ]);
}

// Модалка для формы создания шаблона столбцов
echo Modal::widget([
  'toggleButtonOptions' => [
    'id' => 'new-columns-template-modal',
    'class' => 'hidden',
  ],
  'url' => Url::to(['/statistic/column-templates/create']),
]);

MainAdminStatisticAsset::register($this);
$toolbar = ExportMenu::widget([
  'id' => $exportWidgetId,
  'dataProvider' => $dataProvider,
  'filterFormId' => 'statistic-filter-form',
  'isPartners' => true,
  'statisticModel' => $formModel,
  'columns' => $exportGrid->getExportColumns(),
  'template'=>'{menu}',
  'target' => ExportMenu::TARGET_BLANK,
  'filename' => $exportFileName,
  'dropdownOptions' => [
    'label' => Yii::_t('main.export'),
    'class' => 'btn-xs btn-success export-btn', 'menuOptions' => ['class' => 'pull-right']
  ],
  'exportConfig' => [
    ExportMenu::FORMAT_HTML => false,
    ExportMenu::FORMAT_PDF => false,
    ExportMenu::FORMAT_EXCEL => false,
    ExportMenu::COPY_URL => [
      'label' => Yii::_t('main.copy_url'),
      'linkOptions' => [
        'id' => 'export_copy_url_link',
        'url' => 'javascript:void(0)',
      ],
      'alertMsg' => '',
      'options' => [
        'title' => Yii::_t('main.copy_url_title'),
      ],
    ],
  ],
]);
// Выпадающий список с шаблонами столбцов
$toolbar .= ' ' . Html::dropDownList('columns-templates', $selectedTemplateId, ArrayHelper::map($columnsTemplates, 'id', 'name'), [
    'id' => 'columns-templates',
    'class' => 'selectpicker menu-right col-i columns-templates-select',
    'multiple' => true,
    'title' => BHtml::icon('cog') . ' ' . Yii::_t('statistic.statistic.columns_templates'),
    'data-count-selected-text' => BHtml::icon('cog') . ' ' . Yii::_t('statistic.statistic.columns_templates'),
    'data-selected-text-format' => 'count>0',
    'data-max-options' => 1,
    'data-dropdown-align-right' => 1,
    'data-new-template' => Yii::_t('statistic.statistic.columns_templates_new'),
    'data-update-title' => Yii::t('yii', 'Update'),
    'options' => $columnsTemplatesOptions,
    'data-get-columns-template-url' => Url::to(['/statistic/column-templates/get-template']),
  ]);
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $toolbar,
    ]); ?>

    <?= $this->render('_search', [
      'formModel' => $formModel,
      'maxGroups' => $maxGroups,
      'filterDatePeriods' => $filterDatePeriods,
      'landingPayTypes' => $landingPayTypes,
      'providers' => $providers,
      'landingCategories' => $landingCategories,
      'platforms' => $platforms,
      'countries' => $countries,
      'operatorIds' => $operatorIds,
      'groups' => $groups,
    ]) ?>

    <?php Pjax::begin(['id' => 'statistic-pjax']); ?>
      <?= Grid::widget([
        'dataProvider' => $dataProvider,
        'statisticModel' => $formModel,
        'templateId' => $selectedTemplateId
      ])
      ?>
    <?php Pjax::end(); ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>

<?php
use mcms\common\grid\ContentViewPanel;
use mcms\statistic\components\newStat\ExportMenu;
use mcms\statistic\assets\NewAdminStatisticAsset;
use mcms\statistic\components\newStat\DataProvider;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Grid;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\widgets\SubGroup;
use rgk\utils\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\widget\modal\Modal;

/** @var DataProvider $dataProvider */
/** @var string $exportFileName */
/** @var FormModel $formModel */
/** @var Grid $exportGrid */
/** @var int $maxGroups */
/** @var string[] $groups */
/** @var int|null $selectedTemplateId */
/** @var array $columnsTemplates */
/** @var string $exportWidgetId */
/** @var View $this */
/** @var string $customField */

/** @var \mcms\statistic\Module $statModule */
$statModule = Yii::$app->getModule('statistic');
?>

<?php

// Показываем блок с текстом при выборе группировки по часам
$js = <<<JS
  $(document).on('click', '.showSubtable', function(){
    if ($(this).data('code') === 'hours') {
       $('#footer_text_hour_warning').show();
    }
  });

  window.toggleSubidGroupping = function (show) {
    show = !!show;
    var \$select = $('#groups-select, #default-second-group-select');
    var \$options = \$select.find('option[value="subid1"], option[value="subid2"]');
    var \$subgroup = $('.groupCell a[data-code="subid1"], .groupCell a[data-code="subid2"]').parent();
    
    \$options.hide();
    \$subgroup.hide();
    if (show) {
      \$options.show();
      \$subgroup.show();
    }
    
    \$select.selectpicker("refresh");
  }
  
JS;
$this->registerJs($js);


NewAdminStatisticAsset::register($this);
$toolbar = $statModule->canExportNewStatistic() ? ExportMenu::widget([
  'id' => $exportWidgetId,
  'dataProvider' => $dataProvider,
  'filterFormId' => 'statistic-filter-form',
  'statisticModel' => $formModel,
  'columns' => $exportGrid->getExportColumns(),
  'templateId' => $selectedTemplateId,
  'template'=>'{menu}',
  'target' => ExportMenu::TARGET_BLANK,
  'filename' => $exportFileName,
  'showColumnSelector' => false,
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
]) : '';

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
    'size' => Modal::SIZE_LG,
    'url' => Url::to(['/statistic/new-column-templates/update', 'id' => $columnsTemplate->id]),
  ]);
}

// Модалка для формы создания шаблона столбцов
echo Modal::widget([
  'toggleButtonOptions' => [
    'id' => 'new-columns-template-modal',
    'class' => 'hidden',
  ],
  'size' => Modal::SIZE_LG,
  'url' => Url::to(['/statistic/new-column-templates/create']),
]);

$label = $this->render('_traffic_type_buttons', [
    'trafficType' => $formModel->trafficType,
    'columnsTemplates' => $columnsTemplates,
    'selectedTemplateId' => $selectedTemplateId,
  ]) . $this->render('_group_select', [
    'groups' => $groups,
    'selectedGroup' => $formModel->groups,
  ]);

$label = Html::tag('div', $label, ['id' => 'statisticHeadFilters']);
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">
    <?php ContentViewPanel::begin([
      'padding' => false,
      'label' => $label,
      'toolbar' => $toolbar,
    ]); ?>

    <?= $this->render('_search', [
      'formModel' => $formModel,
      'maxGroups' => $maxGroups,
      'groups' => $groups,
      'selectedTemplateId' => $selectedTemplateId,
      'customField' => $customField,
    ]) ?>

    <?php Pjax::begin(['id' => 'statistic-pjax']); ?>
    <?php
    $toggleSubidGrouppingParam = $showSubIdGroups ? 'true' : 'false';
    $js = <<<JS
      cFilter.prototype.updateCustomFields('$customField');
      // обернуто в setTimeout для того, чтобы выполнилось после updateCustomFields()
      setTimeout(function(){ toggleSubidGroupping($toggleSubidGrouppingParam) }, 0);
JS;
    $this->registerJs($js);
    ?>
    <?php SubGroup::widget(['groups' => $groups, 'formModel' => $formModel]); ?>
    <?= Grid::widget([
      'dataProvider' => $dataProvider,
      'statisticModel' => $formModel,
      'templateId' => $selectedTemplateId
    ])
    ?>

    <?php
    if (in_array(Group::BY_HOURS, $formModel->groups, true)) {
      $this->registerJs("$('#footer_text_hour_warning').show()");
    }
    ?>
    <?php Pjax::end(); ?>
    <?php ContentViewPanel::end() ?>

    <div class="row">
      <div class="col-sm-12 statistic-underTable-column">
        <p class="statistic-underTable">
          <?=Yii::_t('statistic.new_statistic_refactored.footer_text', ['timezoneOffset' => $formattedTimezoneOffset])?>
        </p>
        <p class="statistic-underTable" id="footer_text_hour_warning" style="display:none">
          <?=Yii::_t('statistic.new_statistic_refactored.footer_text_hour_warning')?>
        </p>
      </div>
    </div>

    <?php if (Yii::$app->request->get('rgkhelp')) { ?>
      <h1>TOTAL:</h1>
      <table class="table">
        <tr>
          <th>LTV<br>(AVG charges per subscriber)</th>
          <td>Количество ребиллов с тех подписок, которые в текущей строке.<br>(и среднее кол-во LTV-ребиллов на одну пдп)</td>
        </tr>
        <tr>
          <th>Active subscribers<br>(% on Total Customer Base)</th>
          <td>Количество "живых" подписок. Считаем по наличию ребиллов/пдп/отп за последние 30 дней с текущего момента<br>(процент от всей базы подписок с начала времён)</td>
        </tr>
        <tr>
          <th>Total subscribers<br>(% 30 days active)</th>
          <td>Количество подписок без отписок. <br>(процент подписок, которые пришли или по которым были ребиллы за последние 30 дней)</td>
        </tr>
      </table>

      <h1>CPA:</h1>
      <table class="table">
        <tr>
          <th>LTV<br>(AVG charges per subscriber)</th>
          <td>Количество ребиллов с тех подписок, которые в текущей строке. Подписки берём даже которые не выкупились<br>(и среднее кол-во LTV-ребиллов на одну пдп)</td>
        </tr>
        <tr>
          <th>Active subscribers<br>(% on Subscriptions)</th>
          <td>Количество "живых" подписок для выкупа. Считаем по наличию ребиллов/пдп/отп за последние 30 дней с текущего момента. Берём все пдп на выкуп, даже не выкупленные.<br>(процент от количества пдп на выкуп в этой строке)</td>
        </tr>
        <tr>
          <th>Subscribers<br>(% 30 days active)</th>
          <td>Количество подписок для выкупа без отписок. <br>(процент подписок, которые пришли или по которым были ребиллы за последние 30 дней)</td>
        </tr>
      </table>

      <h1>Revshare:</h1>
      <table class="table">
        <tr>
          <th>LTV<br>(AVG charges per subscriber)</th>
          <td>Количество ребиллов с тех подписок, которые в текущей строке.<br>(и среднее кол-во LTV-ребиллов на одну пдп)</td>
        </tr>
        <tr>
          <th>Active subscribers<br>(% on Subscriptions)</th>
          <td>Количество "живых" подписок. Считаем по наличию ребиллов/пдп/отп за последние 30 дней с текущего момента<br>(процент от ревшар пдп в этой строке)</td>
        </tr>
        <tr>
          <th>Subscribers<br>(% 30 days active)</th>
          <td>Количество ревшар подписок без отписок. <br>(процент подписок, которые пришли или по которым были ребиллы за последние 30 дней)</td>
        </tr>
      </table>
    <?php } ?>
  </div>
</div>

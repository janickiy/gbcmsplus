<?php
use mcms\common\helpers\Html;
use mcms\statistic\models\mysql\DetailStatistic;

/** @var string $currentGroup Код раздела детальной статистики */
/** @var bool $isFilterParamsMigrate
 * Применять параметры фильтра текущей статистики при переходе в другие разделы детальной статы. По умолчанию true */
?>

<div class="btn-group">
  <?php
  foreach ($groups as $code => $name): ?>
    <?php $isActiveClass = $code === $currentGroup ? ' active' : ''; ?>
    <?php $detailStatisticClass = ($code !== DetailStatistic::GROUP_HIT ? ' statistic-filter-params-migrate' : null); ?>
    <?= Html::a($name, ['/statistic/detail/' . $code . '/'], [
      'class' => 'btn btn-default' . $isActiveClass . $detailStatisticClass,
      'data-pjax' => 0
    ]) ?>
  <?php endforeach; ?>
</div>

<?php // Применить текущие параметры фильтра на раздел статистики, по которому произошел клик ?>
<?php // Адаптация параметров под нужый раздел статы произойдет на стороне экшена ?>
<?php if (!isset($isFilterParamsMigrate) || $isFilterParamsMigrate === true) {
  $this->registerJs(<<<'JS'
  $('.statistic-filter-params-migrate').on('click', function () {
        var url = $(this).attr('href');
        var filterFormData = $('#statistic-filter-form').serialize();
        
        location.href = url + (url.search(/\?/) === -1 ? '?' : '&') + filterFormData;
        
        return false;
  });
JS
);
}
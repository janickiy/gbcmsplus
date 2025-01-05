<?php
use mcms\statistic\models\resellerStatistic\Group;
use yii\bootstrap\Html;
use yii\web\View;

/** @var string $groupType */
/** @var View $this */
?>

  <div class="btn-group statistic-switcher">
    <?= Html::a(Yii::_t('statistic.reseller_profit.by_days'), 'javascript:void(0)', [
      'class' => ['btn btn-xs btn-default'],
      'data' => ['group-type' => Group::DAY]
    ]) ?>
    <?= Html::a(Yii::_t('statistic.reseller_profit.by_weeks'), 'javascript:void(0)', [
      'class' => ['btn btn-xs btn-default'],
      'data' => ['group-type' => Group::WEEK]
    ])?>
    <?= Html::a(Yii::_t('statistic.reseller_profit.by_months'), 'javascript:void(0)', [
      'class' => ['btn btn-xs btn-default'],
      'data' => ['group-type' => Group::MONTH]
    ])?>
  </div>

<?php
$js = <<<JS
  $('.statistic-switcher > a').click(function() {
    var group = $(this).data('group-type');
    setHiddenField(group);
    setActiveButton(group);
    submitForm();
  });

  function setHiddenField(value) {
    $('#statistic-group-type').val(value);
  }
  
  function submitForm() {
    $('#statistic-filter-form').trigger('submit');
  }
  
  function setActiveButton(value) {
    $('.statistic-switcher a').removeClass('active').filter('[data-group-type='+value+']').addClass('active');
  }
  
  setHiddenField('$groupType');
  setActiveButton('$groupType');
  
JS;

$this->registerJs($js);
?>
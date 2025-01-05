<?php
use mcms\statistic\assets\MainAdminStatisticGroupFiltersAsset;
use mcms\statistic\components\mainStat\FormModel;
use yii\helpers\Html;

/** @var FormModel $formModel */
/** @var int $maxGroups */
/** @var string[] $groups */
?>

<?php
MainAdminStatisticGroupFiltersAsset::register($this);

$removeGroupFilterLabel = Yii::_t('statistic.statistic.remove_filter_group');
$this->registerJs(/** @lang JavaScript */
  "window.removeGroupFilterLabel = '$removeGroupFilterLabel';
  STATISTIC_MAX_GROUPS = $maxGroups;",
  $this::POS_HEAD
);
?>

<?php $i = 0; ?>
<?php foreach ($formModel->groups ?: [null] as $value) { ?>
  <?php
    $i++;
    $options = [
      'class' => 'form-control auto_filter statistic-group-filter',
      'value' => $value,
    ];
    if ($i > 1) {
      $options['prompt'] = [
        'text' => Yii::_t('statistic.statistic.remove_filter_group'),
        'options' => ['value' => '', 'class' => 'prompt', 'label' => Yii::_t('statistic.statistic.remove_filter_group')]
      ];
    }
  ?>
  <?= Html::activeDropDownList($formModel, 'groups', $groups, $options) ?>
<?php } ?>
<?php if (count($formModel->groups) < $maxGroups) { ?>
  <button type="button" class="btn btn-default" id="add-group">+</button>
<?php } ?>
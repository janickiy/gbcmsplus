<?php

use admin\dashboard\widgets\top_publishers\TopPublishersWidget as Widget;

$js = <<<JS
  $('#countries-type').find('input[name="countries-type"]').on('change', function () {
     var requestObject = DashboardRequest.getObject({
      widgets: {
        top_countries: {
          name: 'top_countries',
          filter: $(this).val()
        }
      }
    });
    DashboardRequest.send(requestObject);
  });
JS;
$this->registerJs($js, $this::POS_LOAD);
?>

<div id="countries-type" class="statbox__header_buttons">
  <div class="btn-group" data-toggle="buttons">
    <label class="btn btn-default btn-sm  <?= $type == Widget::TYPE_TRAFFIC ? 'active' : '' ?>">
      <input type="radio" name="countries-type" value="<?= Widget::TYPE_TRAFFIC ?>" <?= $type == Widget::TYPE_TRAFFIC ? 'checked' : '' ?>>
      <?= Yii::_t('app.dashboard.widget_filter-traffic') ?>
    </label>
    <label class="btn btn-default btn-sm  <?= $type == Widget::TYPE_SUBSCRIBERS ? 'active' : '' ?>">
      <input type="radio" name="countries-type" value="<?= Widget::TYPE_SUBSCRIBERS ?>" <?= $type == Widget::TYPE_SUBSCRIBERS ? 'checked' : '' ?>>
      <?= Yii::_t('app.dashboard.widget_filter-subscribers') ?>
    </label>
    <?php if ($canViewRevenue): ?>
    <label class="btn btn-default btn-sm  <?= $type == Widget::TYPE_REVENUE ? 'active' : '' ?>">
      <input type="radio" name="countries-type" value="<?= Widget::TYPE_REVENUE ?>" <?= $type == Widget::TYPE_REVENUE ? 'checked' : '' ?>>
      <?= Yii::_t('app.dashboard.widget_filter-revenue') ?>
    </label>
    <?php endif;?>
  </div>
</div>

<?php

use admin\assets\WidgetAsset;
use yii\grid\GridView;

WidgetAsset::register($this);
$js = <<<JS
  DashboardRequest.addWidget({
    name: 'top_lp',
    events: ['dashboard:filter'],
    success: function(data) {
      $('#$wrapperId').replaceWith(data);
    }
  });
JS;
$this->registerJs($js, $this::POS_LOAD);
?>

<div id="<?= $wrapperId ?>">
    <?php if ($dataProvider): ?>
        <?= GridView::widget([
            'layout' => '{items}',
            'tableOptions' => [
                'class' => 'table statbox__table'
            ],
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'label' => Yii::_t('app.dashboard.top_lp_table-lp'),
                    'format' => 'raw',
                    'value' => function ($item) {
                        return $item['lp'];
                    },
                    'enableSorting' => false,
                ],
                [
                    'attribute' => 'clicks',
                    'label' => Yii::_t('app.dashboard.top_lp_table-clicks'),
                    'enableSorting' => false,
                ],
                [
                    'attribute' => 'cr',
                    'label' => Yii::_t('app.dashboard.top_lp_table-cr'),
                    'enableSorting' => false,
                ],
            ]
        ]); ?>
    <?php endif; ?>
</div>

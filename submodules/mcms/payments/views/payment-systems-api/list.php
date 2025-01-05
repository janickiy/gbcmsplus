<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\paysystems\PaySystemApiGroup;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel \mcms\payments\models\paysystems\PaySystemApiSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$balanceUrl = Url::to(['payment-systems-api/get-balances']);
$this->registerJs(<<<JS
$.ajax({
    type: 'POST',
    url: '$balanceUrl',
    success: function(result){
      $('.paysystem-api-balance-loading').remove();
      
      $.each(result.data, function (index, value) {
        if (value && value.balance !== undefined && value.balance != null) {
          $('.balance-' + index).html(value.balanceFormatted).parents('.balance-wrapper:first').removeClass('hidden');
        }
      });
      
    }
  });
JS
)
?>

<div class="margin-bottom-10 margin-top-5">
  <?= Html::a(
    '<i class="glyphicon glyphicon-download-alt"></i> ' . Yii::_t('payments.payment-systems-api.download-manual'),
    '@web/downloads/RGK_Payments_Doc.pdf',
    ['class' => 'btn btn-xs btn-danger', 'download' => true],
    true
  ) ?>
</div>

<?php Pjax::begin(['id' => 'payment-systems-api-grid-pjax']); ?>
<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?= AdminGridView::widget([
  'id' => 'payment-systems-api',
  'dataProvider' => $dataProvider,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'code',
      'label' => Yii::_t('payments.payment-systems-api.attribute-code'),
    ],
    [
      'label' => Yii::_t('payments.payment-systems-api.available-recipients'),
      'format' => 'html',
      'value' => function (PaySystemApiGroup $model) {
        $htmlBlocks = [];

        foreach ($model->getAvailableRecipients() as $recipient) {
          $htmlBlocks[] = $recipient['paysystem']->name . ': ' . Yii::$app->formatter->asCurrenciesList($recipient['allCurrencies'], $recipient['activeCurrencies']);
        }

        return implode('<br>', $htmlBlocks);
      },
    ],
    [
      'format' => 'html',
      'label' => Yii::_t('payments.payment-systems-api.attribute-balance'),
      'value' => function (PaySystemApiGroup $model) {
        $htmlBlocks = ['rub' => null, 'usd' => null, 'eur' => null];
        $htmlBlocks['loading'] = '<span class="paysystem-api-balance-loading glyphicon glyphicon-hourglass"></span>';

        foreach ($model->paysystemApis as $paysystemApi) {
          if (!$paysystemApi->currency) continue;
          $htmlBlocks[$paysystemApi->currency] = Html::tag(
            'span',
            Html::tag('span', '', ['class' => 'balance-' . $paysystemApi->id]) . '<br>',
            ['class' => 'balance-wrapper hidden']
          );
        }

        return implode('', array_filter($htmlBlocks));
      },
      'contentOptions' => ['style' => 'min-width: 90px;'],
    ],
    [
      'label' => Yii::_t('payments.payment-systems-api.system-is-configured'),
      'format' => 'html',
      'value' => function (PaySystemApiGroup $model) {
        $htmlBlocks = ['rub' => null, 'usd' => null, 'eur' => null];
        foreach ($model->paysystemApis as $paysystemApi) {
          if (!$paysystemApi->currency) continue;
          $htmlBlocks[$paysystemApi->currency] = Html::booleanIcon($paysystemApi->isValidSettings()) . ' ' . strtoupper($paysystemApi->currency);
        }

        return implode('<br>', array_filter($htmlBlocks));
      },
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update}',
      'buttons' => [
        'update' => function ($url, PaySystemApiGroup $model) {
          $options = [
            'title' => Yii::t('yii', 'Update'),
            'aria-label' => Yii::t('yii', 'Update'),
            'data-pjax' => '0',
            'class' => 'btn btn-xs btn-default'
          ];
          return Html::a(BHtml::icon('pencil'), ['update', 'code' => $model->code], $options);
        },
      ],
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],
  ],
]); ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end(); ?>

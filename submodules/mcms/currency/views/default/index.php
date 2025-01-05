<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\currency\models\search\CurrencySearch;
use mcms\promo\components\api\MainCurrencies;
use yii\widgets\Pjax;
use yii\bootstrap\Html;
use mcms\currency\models\Currency;
use mcms\common\grid\ActionColumn;
use mcms\common\widget\Select2;

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var CurrencySearch $searchModel
 */
$this->title = Yii::_t('currency.main.menu');
?>

<?= Html::beginTag('section', ['id' => 'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'currenciesPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'layout' => '{items}<div class="dt-toolbar-footer">
    <div class="col-xs-12 col-sm-6 dataTables_paginate paging_simple_numbers">{pager}</div>
    </div>',
  'rowOptions' =>  function (Currency $currency) use ($searchModel) {
    $options = [];
    if ($searchModel->customCourseType === $searchModel::CUSTOM_COURSE_ALERT) {
      $options = ['style' => 'display:none'];
      foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) {
        if ($currencyCode === $currency->code) {
          continue;
        }
        if (!$currency->isCustomCourseProfitable($currencyCode)) {
          $options = [];
          break;
        }
      }
    }
    return $options;
  },
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '10px',
    ],
    [
      'attribute' => 'code',
      'value' => function ($model) {
        return strtoupper($model->code);
      },
      'width' => '50px',
    ],
    [
      'label' => Yii::_t('currency.main.attribute-country_id'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'countryId',
        'data' => $searchModel::getCountriesList(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
      'format' => 'raw',
      'value' => 'countriesLinks',
      'width' => '150px',
    ],
    [
      'label' => Yii::_t('currency.main.column_original_courses'),
      'format' => 'raw',
      'value' => function (Currency $currency) {
        $rows = [];

        foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) {
          if ($currencyCode === $currency->code) {
            continue;
          }

          $rows[] = strtr(':from_currency &rArr; :to_currency: :course', [
            ':from_currency' => strtoupper($currency->code),
            ':to_currency' => strtoupper($currencyCode),
            ':course' => (float)$currency->{'to_' . $currencyCode},
          ]);
        }

        return implode('<br>', $rows);
      }
    ],
    [
      'attribute' => 'customCourseType',
      'label' => Yii::_t('currency.main.custom_courses'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'customCourseType',
        'data' => $searchModel->getCustomCourseTypes(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
      'format' => 'raw',
      'value' => function (Currency $currency) {
        $rows = [];

        foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) {
          if ($currencyCode === $currency->code || !$currency->{'custom_to_' . $currencyCode}) {
            continue;
          }

          $options = [];
          if (!$currency->isCustomCourseProfitable($currencyCode)) {
            $options['style'] = 'color:red';
          }
          $template = Html::tag('b', ':from_currency &rArr; :to_currency: :course', $options);
          $courseWithComission = (float)$currency->{'custom_to_' . $currencyCode};

          $rows[] = strtr($template, [
            ':from_currency' => strtoupper($currency->code),
            ':to_currency' => strtoupper($currencyCode),
            ':course' => $courseWithComission,
            ':percent' => Yii::$app->formatter->asDecimal($currency->{'partner_percent_' . $currencyCode}, 2),
            ':tooltip' => Yii::_t('currency.main.tooltip_percent'),
          ]);
        }

        return implode('<br>', $rows);
      }
    ],
    [
      'label' => Yii::_t('currency.main.final_courses'),
      'format' => 'raw',
      'value' => function (Currency $currency) {
        $rows = [];

        foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) {
          if ($currencyCode === $currency->code) {
            continue;
          }
          $template = ':from_currency &rArr; :to_currency: :course <span title=":tooltip">(:percent%)</span>';
          $courseWithComission = $currency->{'to_' . $currencyCode} * (100 - $currency->{'partner_percent_' . $currencyCode}) / 100;

          // Если задан custom курс, выводим его без наложения процентов
          if ($currency->{'custom_to_' . $currencyCode} && $currency->isCustomCourseProfitable($currencyCode)) {
            $template = '<b>:from_currency &rArr; :to_currency: :course</b>';
            $courseWithComission = (float)$currency->{'custom_to_' . $currencyCode};
          }

          $rows[] = strtr($template, [
            ':from_currency' => strtoupper($currency->code),
            ':to_currency' => strtoupper($currencyCode),
            ':course' => $courseWithComission,
            ':percent' => Yii::$app->formatter->asDecimal($currency->{'partner_percent_' . $currencyCode}, 2),
            ':tooltip' => Yii::_t('currency.main.tooltip_percent'),
          ]);
        }

        return implode('<br>', $rows);
      }
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{update-modal}',
    ],

  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');
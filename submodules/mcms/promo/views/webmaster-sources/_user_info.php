<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\SystemLanguage;
use mcms\promo\models\Source;

/**
 * @var Source $model
 * @var $currency
 */
?>

<?= \yii\widgets\DetailView::widget([
  'model' => [
    'username' => $model->user->username,
    'phone' => $model->user->getParams()->phone,
    'skype' => $model->user->getParams()->skype,
    'language' => ArrayHelper::getValue(SystemLanguage::getLanguangesDropDownArray(), $model->user->language),
    'currency' => $currency
  ],
  'attributes' => [
    [
      'attribute' => 'username',
      'label' => $model->user->getAttributeLabel('username')
    ],
    [
      'attribute' => 'phone',
      'label' => $model->user->getAttributeLabel('phone')
    ],
    [
      'attribute' => 'skype',
      'label' => $model->user->getAttributeLabel('skype')
    ],
    [
      'attribute' => 'language',
      'label' => $model->user->getAttributeLabel('language')
    ],
    [
      'attribute' => 'currency',
      'label' => Yii::_t('payments.main.attribute-currency')
    ],
  ]
]); ?>
<?php

use mcms\partners\assets\basic\ProfileAsset;
use mcms\common\helpers\Html;
use yii\widgets\DetailView;

/* @var $company \mcms\payments\models\PartnerCompany */

ProfileAsset::register($this);
?>

<div class="container-fluid">

  <div class="row">
    <div class="col-xs-6 mw650 right-col">
      <div class="bgf profile profile-finance">
        <div class="currency-wrapper">
          <div class="title title_with-action">
            <h2><?= Yii::_t('partners.payments.company_info') ?></h2>
          </div>
          <div class="content__position">
            <?= DetailView::widget([
              'model' => $company,
              'options' => ['class' => 'table'],
              'attributes' => [
                'name',
                'address',
                'country',
                'tax_code',
                'bank_entity',
                'bank_account',
                'swift_code',
                'currency',
                [
                  'format' => 'raw',
                  'attribute' => 'agreement',
                  'value' => function ($company) {
                    return Html::a(Yii::_t('payments.partner-companies.download'), ['/partners/payments/get-agreement/',
                      'id' => $company->id, 't' => time()]);
                  },
                  'visible' => $company->agreement !== null
                ]
              ]
            ]);
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

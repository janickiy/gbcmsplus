<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use mcms\payments\models\PartnerCompany;

/** @var PartnerCompany $model */
?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'name',
      [
      'attribute' => 'userLink',
      'format' => 'html'
      ],
      'country',
      'address',
      'city',
      'post_code',
      'tax_code',
      'bank_entity',
      'bank_account',
      'swift_code',
      'currency',
      'due_date_days_amount',
      'vat',
      [
        'attribute' => 'invoicing_cycle',
        'value' => function (PartnerCompany $model) {
          if ($model->invoicing_cycle === null) {
            return Yii::_t('app.common.not_selected');
          }

          return $model::getInvoicingCycleDropdown($model->invoicing_cycle);
        }
      ],
      [
        'attribute' => 'agreement',
        'format' => 'raw',
        'value' => function (PartnerCompany $model) {
          return $model->agreement
            ? Html::a(
              Yii::_t('payments.partner-companies.download'),
              ['/payments/partner-companies/get-agreement/', 'id' => $model->id, 't' => time()],
              ['style' => 'max-width:100%; max-height:100%;']
            )
            : null;
        }
      ],
      'created_at:datetime',
      'updated_at:datetime',
    ]
  ]); ?>
</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>

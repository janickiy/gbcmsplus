<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\statistic\models\Complain;

/** @var \mcms\common\web\View $this */
/** @var \mcms\statistic\models\mysql\DetailStatisticComplains $model */
/** @var array $record @see \mcms\statistic\models\mysql\DetailStatisticComplains::findOne */
?>

<?php
$userModule = Yii::$app->getModule('users');
$promoModule = Yii::$app->getModule('promo');
$landingId = ArrayHelper::getValue($record, 'landing_id'); ?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"># <?=$record['hit_id']?></h4>
</div>

<div class="modal-body">
<?= \yii\widgets\DetailView::widget([
  'model' => $record,
  'attributes' => [
    [
      'label' => $model->getGridColumnLabel('hit_id'),
      'attribute' => 'hit_id'
    ],
    [
      'attribute' => 'type',
      'label' => $model->getAttributeLabel('type'),
      'value' => isset($record['type']) ? ArrayHelper::getValue(Complain::getTypes(), $record['type']) : null
    ],
    [
      'label' => $model->getGridColumnLabel('time'),
      'attribute' => 'time',
      'format' => 'datetime'
    ],
    [
      'attribute' => 'description',
      'label' => $model->getAttributeLabel('description'),
      'format' => 'raw',
    ],
    [
      'label' => $model->getGridColumnLabel('ip'),
      'attribute' => 'ip',
      'visible' => $model->canViewIp(),
      'format' => 'ipFromLong'
    ],
    [
      'attribute' => 'email',
      'label' => $model->getGridColumnLabel('email'),
      'format' => 'raw',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'email'),
        $userModule->api('userLink')->buildProfileLink(ArrayHelper::getValue($record, 'user_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        ['UsersUserView' => ['userId' => ArrayHelper::getValue($record, 'user_id')]],
        false
      ),
      'visible' => $model->canViewUser()
    ],
    [
      'label' => $model->getGridColumnLabel('phone_number'),
      'attribute' => 'phone_number',
      'format' => $model->canViewFullPhone() ? 'raw' : 'protectedPhone',
      'visible' => $model->canViewPhone()
    ],
    [
      'label' => $model->getGridColumnLabel('stream'),
      'attribute' => 'stream_name',
      'visible' => $model->canViewStream(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'stream_name'),
        $promoModule->api('url')->viewStream(ArrayHelper::getValue($record, 'stream_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('source'),
      'attribute' => 'source_name',
      'visible' => $model->canViewSource(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'source_name'),
        $promoModule->api('url')->viewSource(
          ArrayHelper::getValue($record, 'source_id'),
          ArrayHelper::getValue($record, 'source_type')
        ),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'attribute' => 'landing_name',
      'label' => $model->getGridColumnLabel('landings'),
      'visible' => $model->canViewLanding(),
      'format' => 'stringOrNull',
      'value' => $landingId !== null
        ? Html::a(
            Yii::$app->formatter->asLanding($landingId, ArrayHelper::getValue($record, 'landing_name')),
            $promoModule->api('url')->viewLanding($landingId),
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          )
        : null
    ],
    [
      'label' => $model->getGridColumnLabel('countries'),
      'attribute' => 'country_name',
      'visible' => $model->canViewCountry(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'country_name'),
        $promoModule->api('url')->viewCountry(ArrayHelper::getValue($record, 'country_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('operators'),
      'attribute' => 'operator_name',
      'visible' => $model->canViewOperator(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'operator_name'),
        $promoModule->api('url')->viewOperator(ArrayHelper::getValue($record, 'operator_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('platforms'),
      'attribute' => 'platform_name',
      'visible' => $model->canViewPlatform(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'platform_name'),
        $promoModule->api('url')->viewPlatform(ArrayHelper::getValue($record, 'platform_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('landing_pay_type_name'),
      'attribute' => 'landing_pay_type_name',
    ],

    [
      'label' => $model->getGridColumnLabel('rebilled_at'),
      'attribute' => 'rebilled_at',
      'value' => ArrayHelper::getValue($record, 'rebilled_at') ?: null,
      'format' => 'datetime'
    ],
    [
      'label' => $model->getGridColumnLabel('subscribed_at'),
      'attribute' => 'subscribed_at',
      'value' => ArrayHelper::getValue($record, 'subscribed_at') ?: null,
      'format' => 'datetime'
    ],
    [
      'label' => $model->getGridColumnLabel('unsubscribed_at'),
      'attribute' => 'unsubscribed_at',
      'value' => ArrayHelper::getValue($record, 'unsubscribed_at') ?: null,
      'format' => 'datetime'
    ],
  ]
]); ?>

  <div class="col-xs-12">
    <?php if($model->canViewReferrer()): ?>
      <strong><?= $model->getGridColumnLabel('referrer') ?>:</strong>
      <p style="word-wrap: break-word"><?= $record['referrer'] ?></p>
    <?php endif;?>
    <?php if($model->canViewUserAgent()): ?>
      <strong><?= $model->getGridColumnLabel('userAgent') ?>:</strong>
      <p style="word-wrap: break-word"><?= $record['userAgent'] ?></p>
    <?php endif;?>
  </div>
  <div class="clearfix"></div>

</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>
<?php

use kartik\form\ActiveForm;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\statistic\assets\StatisticAsset;
use mcms\statistic\models\mysql\DetailStatistic;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var array $record */
/** @var mcms\statistic\models\mysql\DetailStatisticHit $statisticModel */

StatisticAsset::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
$landingId = ArrayHelper::getValue($record, 'landing_id');
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">
    <?php ContentViewPanel::begin([
      'padding' => false,
    ]); ?>
    <?php $form = ActiveForm::begin([
      'action' => Url::to(['hit']),
      'method' => 'GET',
      'type' => ActiveForm::TYPE_INLINE,
      'options' => [
        'data-pjax' => true,
        'id' => 'statistic-filter-form'
      ]
    ]); ?>
    <div class="dt-toolbar">
      <div class="filter_pos">
        <?= $this->render('_group_buttons', [
          'groups' => $model->getGroups(),
          'currentGroup' => DetailStatistic::GROUP_HIT,
          // Фильтры данного раздела статы не нужно переносить при переходе в другие разделы
          'isFilterParamsMigrate' => false,
        ]); ?>
      </div>
      <div class="filter_pos">
        <?= $form->field($statisticModel, 'id')->textInput(['type' => 'number', 'class' => 'auto_filter']); ?>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
    <?php ActiveForm::end(); ?>

    <?php Pjax::begin(['id' => 'statistic-pjax']) ?>
    <?php if ($record) {?>
      <?= DetailView::widget([
        'model' => $record,
        'attributes' => [
          [
            'label' => $statisticModel->getGridColumnLabel('time'),
            'attribute' => 'time',
            'format' => 'dateTime'
          ],
          [
            'attribute' => 'ip',
            'label' => $statisticModel->getGridColumnLabel('ip'),
            'visible' => $statisticModel->canViewIp(),
            'format' => 'ipFromLong'
          ],
          [
            'attribute' => 'email',
            'label' => $statisticModel->getGridColumnLabel('email'),
            'format' => 'raw',
            'value' => Html::a(
              '#' . ArrayHelper::getValue($record, 'user_id') . '. ' . ArrayHelper::getValue($record, 'email'),
              $userModule->api('userLink')->buildProfileLink(ArrayHelper::getValue($record, 'user_id')),
              ['target' => '_blank', 'data-pjax' => 0],
              ['UsersUserView' => ['userId' => ArrayHelper::getValue($record, 'user_id')]],
              false
            ),
            'visible' => $statisticModel->canViewUser()
          ],
          [
            'label' => $statisticModel->getGridColumnLabel('stream'),
            'attribute' => 'stream_name',
            'visible' => $statisticModel->canViewStream(),
            'format' => 'stringOrNull',
            'value' => !$isHitBelongToManagersPartner ? ArrayHelper::getValue($record, 'stream_name') : Html::a(
              ArrayHelper::getValue($record, 'stream_name'),
              $promoModule->api('url')->viewStream(ArrayHelper::getValue($record, 'stream_id')),
              ['target' => '_blank', 'data-pjax' => 0],
              [],
              false
            )
          ],
          [
            'label' => $statisticModel->getGridColumnLabel('source'),
            'attribute' => 'source_name',
            'visible' => $statisticModel->canViewSource(),
            'format' => 'stringOrNull',
            'value' => !$isHitBelongToManagersPartner ? ArrayHelper::getValue($record, 'source_name') : Html::a(
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
            'label' => $statisticModel->getGridColumnLabel('landings'),
            'visible' => $statisticModel->canViewLanding(),
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
            'label' => $statisticModel->getGridColumnLabel('countries'),
            'attribute' => 'country_name',
            'visible' => $statisticModel->canViewCountry(),
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
            'label' => $statisticModel->getGridColumnLabel('operators'),
            'attribute' => 'operator_name',
            'visible' => $statisticModel->canViewOperator(),
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
            'label' => $statisticModel->getGridColumnLabel('platforms'),
            'attribute' => 'platform_name',
            'visible' => $statisticModel->canViewPlatform(),
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
            'label' => $statisticModel->getGridColumnLabel('landingPayType'),
            'attribute' => 'landing_pay_type_name',
          ],
          [
            'label' => $statisticModel->getGridColumnLabel('is_unique'),
            'attribute' => 'is_unique',
            'format' => 'boolean',
          ],
          [
            'label' => $statisticModel->getGridColumnLabel('is_tb'),
            'attribute' => 'is_tb',
            'format' => 'boolean',
          ],
          [
            'attribute' => 'referrer',
            'label' => $statisticModel->getGridColumnLabel('referrer'),
            'visible' => $statisticModel->canViewReferrer(),
          ],
          [
            'attribute' => 'subid1',
            'label' => $statisticModel->getGridColumnLabel('subid1'),
            'visible' => $statisticModel->canViewSubid(),
          ],
          [
            'attribute' => 'subid2',
            'label' => $statisticModel->getGridColumnLabel('subid2'),
            'visible' => $statisticModel->canViewSubid(),
          ],
          [
            'attribute' => 'cid',
            'label' => $statisticModel->getGridColumnLabel('cid'),
            'visible' => $statisticModel->canViewCid(),
          ],
        ]
      ])
      ?>
    <?php } else {?>
        <div class="alert alert-<?= $statisticModel->id ? 'danger' : 'warning' ?>">
          <?= Yii::_t('statistic.statistic.' . ($statisticModel->id ? 'hit_not_found' : 'enter_hit_id')) ?>
        </div>
    <?php } ?>
    <?php Pjax::end() ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>

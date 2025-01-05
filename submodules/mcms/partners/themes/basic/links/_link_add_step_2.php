<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use mcms\partners\assets\PromoLinksAddStep2Asset;

PromoLinksAddStep2Asset::register($this);
?>
<?php $form = ActiveForm::begin([
  'id' => 'linkStep2Form',
  'action' => ['form-handle'],
  'enableAjaxValidation' => true,
  'validateOnBlur' => false,
  'validateOnChange' => false,
]); ?>
<?= Html::hiddenInput('stepNumber', 2) ?>
<?= $form->field($linkStep2Form, 'id', ['options' => ['class' => 'hidden']])->hiddenInput(['id' => 'linkId'])->label(false) ?>
<div>
  <div class="row">
    <div class="col-lg-12">
      <div class="addLinks__r-offer">
        <ul>
          <li class="selected__sing-cat" data-filter="*" data-paytypes="1"><?= Yii::_t('main.all') ?></li>
          <?php foreach ($offerCategories as $id => $name): ?>
            <li data-filter=".offer-<?= $id ?>" data-id="<?= $id ?>" data-paytypes="<?= $id === 1 ? 1 : 0 // скрываем типы оплат для всех, кроме 1 клик ?>"><?= $name ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="row row-p-0 addLinks__header">
    <div class="col-xs-4">
      <?= Html::dropDownList('', null, ArrayHelper::map($payTypes, 'id', 'name'),
        [
          'class' => 'selectpicker',
          'id' => 'paytypesList',
          'multiple' => 'true',
          'data-width' => '100%',
          'title' => Yii::_t('links.all_payments_types'),
          'data-selected-text-format' => 'count > 3'
        ]); ?>
    </div>
    <div class="col-xs-8">
      <?= Yii::_t('links.select_payment_types') ?>
    </div>
  </div>
  <div class="row row-p-0 addLinks__country-pos">
    <div class="col-xs-4">
      <div class="addLinks__country">
        <ul class="addLinks__country-l1">
          <li class="active">
            <span class="country__collapse"><?=Yii::_t('partners.main.countries')?></span>
            <ul class="addLinks__country-l2 collapse">
              <?php foreach ($countries as $country): /* @var $country mcms\promo\models\Country */ ?>
                <?php if (isset($countriesOperatorsActiveLandingsCount[$country->id])): ?>
                  <li data-country-id="<?= $country->id ?>" class='country-<?= $country->id ?> <?= ($activeOperator && $activeOperator->country_id == $country->id) ? 'active' : '' ?>'>
                    <?php $selectedCountryContent = !empty($landingsSelectedCount[$country->id])
                      ? 'selected__c'
                      : ''; ?>
                    <span style="background-image: url(/img/flags/<?= mb_strtolower($country->code) ?>.png);" class="set_oss <?= count($countriesOperatorsActiveLandingsCount[$country->id]) > 1 && !isset($countriesOperatorsActiveLandingsCount[$country->id]['hideOss']) ? '' : 'hide_oss_container hide_oss_permanently'?> <?= $selectedCountryContent?>">
                      <?= $country->name ?>
                    </span>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
            <?php foreach ($countries as $country): /* @var $country mcms\promo\models\Country */ ?>
              <?php if (isset($countriesOperatorsActiveLandingsCount[$country->id])): ?>
                <ul class="addLinks__country-l3<?= ($activeOperator && $activeOperator->country_id == $country->id) ? ' active' : '' ?>">
                  <?php foreach ($country->activeOperator as $operator): /* @var $operator mcms\promo\models\Operator */ ?>
                    <?php if (isset($countriesOperatorsActiveLandingsCount[$country->id][$operator->id]) && !$operator->isTrafficBlocked()): ?>
                      <?php $selectedOperatorContent = !empty($landingsSelectedCount[$country->id]) && !empty($landingsSelectedCount[$country->id][$operator->id])
                        ? '<i class="count__selected">' . $landingsSelectedCount[$country->id][$operator->id] . '</i>'
                        : ''; ?>
                      <li data-operator-title="<?= $operator->name ?>" data-operator-id="<?= $operator->id ?>" class='oss-<?= $operator->id ?> <?= ($activeOperator && $activeOperator->id == $operator->id) ? 'active' : '' ?>'><?= $operator->name ?><?= $selectedOperatorContent ?></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            <?php endforeach; ?>
          </li>
        </ul>
      </div>
    </div>
    <div class="col-xs-8 addLinks__r-col">
      <div class="">
        <div class="addLinks__r-header">
          <h2><?= Yii::_t('partners.links.select_landing')?> <span id="activeOperatorTitle"><?= $activeOperator ? $activeOperator->name : '' ?></span></h2>
          <div class="addLinks_grid" >
            <span><?= Yii::_t('main.view') ?>:</span>
            <a data-grid="column" href="" class="<?= $showType == 'column' ? 'active' : '' ?>"><i class="icon-grid_icon2"></i></a>
            <a data-grid="table" href="" class="<?= $showType == 'table' ? 'active' : '' ?>"><i class="icon-grid_icon1"></i></a>
          </div>
        </div>
        <div class="addLinks__r-category">
          <ul>
            <li class="selected__sing-cat" data-filter="*"><?= Yii::_t('main.all') ?></li>
            <?php foreach($landingCategories as $id => $name):?>
              <li data-filter=".category-<?= $id ?>"><?= $name ?></li>
            <?php endforeach;?>
          </ul>
        </div>
        <div class="ajax_container">
          <ul class="grid__header">
            <li class="row ">
              <div class="col-xs-6"><?= Yii::_t('links.landing') ?></div>
              <div class="col-xs-6 text-center">
                <div class="row">
                  <div class="col-xs-4 isotope-sort" data-sort="date"><span><?= Yii::_t('links.added') ?></span></div>
                  <div class="col-xs-4 isotope-sort" data-sort="number"><span><?= Yii::_t('links.rebill_label') ?></span></div>
                  <div class="col-xs-4 isotope-sort" data-sort="number_1"><span><?= Yii::_t('links.buyout_label') ?></span></div>
                </div>
              </div>
            </li>
          </ul>
          <div class="addLinks__r-lands">
            <ul class="grid <?= $showType == 'table' ? 'grid_list' : '' ?>">
              <?php /* delete if problem ?>
              <?= $this->render('landings_list', [
                'landings' => $landings,
                'landingPayTypes' => $landingPayTypes,
                'rebillValue' => $rebillValue,
                'buyoutValue' => $buyoutValue,
                'accessByRequestValue' => $accessByRequestValue,
                'unblockedRequestStatusModerationValue' => $unblockedRequestStatusModerationValue,
                'unblockedRequestStatusUnlockedValue' => $unblockedRequestStatusUnlockedValue,
                'link' => $link,
                'currency' => $currency,
              ]) ?>
            <?php */ ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?= Modal::widget(['id' => 'linkLandingModal', 'options' => ['class' => 'lands__modal']]) ?>
  <?= Modal::widget(['id' => 'linkRequestModal', 'options' => ['class' => 'request__access']]) ?>
  <?php if($link):?>
    <?php foreach($link->getActiveSourceOperatorLanding()->each() as $landing): /* @var $landing \mcms\promo\models\SourceOperatorLanding */?>
      <?php if ($landing->landing->isHiddenBlocked() || $landing->landingOperator->is_deleted) continue; ?>
      <?php if ($landing->landingOperator->operator->isTrafficBlocked()) continue;?>
      <?= Html::hiddenInput(
        'LinkStep2Form[linkOperatorLandings][' . $landing->landing_id . '][' . $landing->operator_id . '][profit_type]',
        $landing->profit_type,
        [
          'id' => 'selectedOperatorLanding-l' . $landing->landing_id . 'o' . $landing->operator_id,
          'data-operator-id' => $landing->operator_id,
          'data-landing-id' => $landing->landing_id,
          'class' => 'hidden-input selectedLandingHiddenInput selectedLanding' . $landing->landing_id,
        ]); ?>
    <?php endforeach;?>
  <?php else:?>
    <?= $form->field($linkStep2Form, 'linkOperatorLandings')->hiddenInput()->label(false)->error(false) ?>
  <?php endif;?>
</div>
<?php ActiveForm::end(); ?>

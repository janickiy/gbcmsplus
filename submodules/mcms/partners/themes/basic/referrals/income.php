<?php

use mcms\partners\assets\ReferralsAsset;

ReferralsAsset::register($this);

/* @var \mcms\payments\models\search\ReferralIncomeSearch $searchModel */
/* @var \yii\data\ActiveDataProvider $dataProvider */
/* @var \mcms\common\web\View $this */
/* @var string $currency */
/* @var float $mainAmount */
/* @var float $holdAmount */
/* @var boolean $hasNoReferrals */

/* @var float $lastWeekAmount */
/* @var float $lastWeekMainAmount */
/* @var float $lastWeekHoldAmount */
/* @var integer $activeReferralCount */
/* @var integer $totalReferralCount */
/* @var integer $referralPercent */
/* @var string $referralLink */

?>
<div class="container-fluid">
    <div class="row">

      <?= $this->render('_data', compact('currency', 'lastWeekAmount', 'lastWeekMainAmount', 'lastWeekHoldAmount',
        'activeReferralCount', 'totalReferralCount', 'referralPercent', 'referralLink')) ?>

      <div class="col-xs-7 referals-table-col">
          <div class="bgf">
              <?= $hasNoReferrals
                ? $this->render('_empty')
                : $this->render('_income_grid', compact('dataProvider', 'currency', 'mainAmount', 'holdAmount', 'searchModel')); ?>
          </div>
      </div>
    </div>
</div>
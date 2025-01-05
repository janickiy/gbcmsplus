<?php

use mcms\partners\components\widgets\PriceWidget;

/* @var \mcms\common\web\View $this */
/* @var string $currency */
/* @var string $referralLink */
/* @var float $lastWeekAmount */
/* @var float $lastWeekMainAmount */
/* @var float $lastWeekHoldAmount */
/* @var integer $totalReferralCount */
/* @var integer $activeReferralCount */
/* @var integer $referralPercent */
?>
<div class="col-xs-5 pull-right referals-balance-col">
    <div class="bgf">
        <div class="title">
            <h2><?= Yii::_t('referrals.referral-management') ?></h2>
        </div>
        <div class="referals content__position">
            <div class="referals-balance_total text-center">
                <span><?= PriceWidget::widget(['value' => $lastWeekAmount, 'currency' => $currency]) ?></span>
                <small><?= Yii::_t('referrals.last-week-income') ?></small>
            </div>
        </div>
        <div class="referals referals__position">
            <div class="row text-center">
                <div class="col-xs-6">
                    <div class="referals-balance">
                        <span><?= PriceWidget::widget(['value' => $lastWeekMainAmount, 'currency' => $currency]) ?></span>
                        <small><?= Yii::_t('referrals.last-week-main-income') ?></small>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="referals-balance">
                        <span><?= PriceWidget::widget(['value' => $lastWeekHoldAmount, 'currency' => $currency]) ?></span>
                        <small><?= Yii::_t('referrals.last-week-hold-income') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="referals referals__position referals__count">
            <div class="row text-center">
                <div class="col-xs-6">
                    <div class="referals-balance">
                        <span><?= $activeReferralCount ?></span>
                        <small><?= Yii::_t('referrals.active-referral-count') ?></small>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="referals-balance">
                        <span><?= $totalReferralCount ?></span>
                        <small><?= Yii::_t('referrals.referral-count') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="content__position referals_link">
            <span><?= Yii::_t('referrals.referral-link') ?>:</span>
            <div class="row">
              <div class="col-xs-8">
                <i class="ref__link" id="referralLink"><?= $referralLink ?></i>
              </div>
              <div class="col-xs-4 no_p">
                <span class="btn btn-white copy-button" data-clipboard-target="#referralLink">
                  <i class="icon-blank"></i><?= Yii::_t('partners.main.copy') ?>
                </span>
              </div>
            </div>
            <span class="ref_procent"><?= Yii::_t('referrals.referral-percent') ?>: <?= $referralPercent ?>%</span>
        </div>
    </div>
</div>

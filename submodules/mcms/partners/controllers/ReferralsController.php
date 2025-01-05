<?php

namespace mcms\partners\controllers;

use mcms\user\components\events\EventReferralRegistered;
use Yii;
use mcms\common\controller\SiteBaseController as BaseController;

class ReferralsController extends BaseController
{

  public $controllerTitle;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->theme = 'basic';

    $this->menu = [
      [
        'label' => Yii::_t('referrals.referrals-income'),
        'active' => $this->action->id == 'income',
        'url' => '/partners/referrals/income/',
      ],
    ];

    $this->controllerTitle = Yii::_t('partners.main.referrals');

    return parent::beforeAction($action);
  }

  public function __construct($id, $module, $config = [])
  {
    parent::__construct($id, $module, $config);
  }

  public function actionIncome()
  {
    Yii::beginProfile('actionIncome', self::class);
    if(!Yii::$app->getModule('users')->isRegistrationWithReferrals()) return $this->goHome();

    $userId = Yii::$app->user->id;
    Yii::beginProfile('api.payments.getUserCurrency', self::class);
    $currency = Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $userId])->getResult();
    Yii::endProfile('api.payments.getUserCurrency', self::class);
    $params = array_merge(Yii::$app->request->queryParams, [
      'userId' => $userId,
      'currency' => $currency,
      'partnerSearch' => true
    ]);

    Yii::beginProfile('api.payments.referralsGroupedBalance', self::class);
    $referralsGroupedBalance = Yii::$app->getModule('payments')->api('referralsGroupedBalance', $params);
    Yii::endProfile('api.payments.referralsGroupedBalance', self::class);

    $dataProvider = $referralsGroupedBalance->getResult();
    $searchModel = $referralsGroupedBalance->getSearchModel();
    $mainAmount = $referralsGroupedBalance->getTotalAmount(false);
    $holdAmount = $referralsGroupedBalance->getTotalAmount(true);
    Yii::endProfile('actionIncome', self::class);
    return $this->render('income', array_merge($this->getCommonData($userId, $currency), compact('dataProvider', 'currency', 'searchModel', 'mainAmount', 'holdAmount')));
  }

  protected function getCommonData($userId, $currency)
  {
    Yii::beginProfile('getCommonData', self::class);

    Yii::beginProfile('api.payments.userSettingsData', self::class);
    $referralPercent = Yii::$app->getModule('payments')
      ->api('userSettingsData', ['userId' => $userId])->getVisibleReferralPercent()
    ;
    Yii::endProfile('api.payments.userSettingsData', self::class);

    Yii::beginProfile('api.payments.referralsGroupedBalance', self::class);
    $lastWeekBalance = Yii::$app->getModule('payments')->api('referralsGroupedBalance', [
      'userId' => $userId,
      'currency' => $currency,
    ]);
    $lastWeekMainAmount = $lastWeekBalance->getLastWeekAmount(false);
    $lastWeekHoldAmount = $lastWeekBalance->getLastWeekAmount(true);
    $lastWeekAmount = $lastWeekHoldAmount + $lastWeekMainAmount;
    Yii::endProfile('api.payments.referralsGroupedBalance', self::class);

    Yii::beginProfile('api.users.referrals.getReferralCount', self::class);
    $totalReferralCount = Yii::$app->getModule('users')
      ->api('referrals')->getReferralCount($userId)
    ;
    Yii::endProfile('api.users.referrals.getReferralCount', self::class);
    Yii::beginProfile('api.statistic.activeReferrals.getCount', self::class);
    $activeReferralCount = Yii::$app->getModule('statistic')->api('activeReferrals', [
      'userId' => $userId,
      'startDate' => Yii::$app->formatter->asDate('-7 days', 'php:Y-m-d'),
      'endDate' => Yii::$app->formatter->asDate('today', 'php:Y-m-d'),
    ])->getCount();
    Yii::endProfile('api.statistic.activeReferrals.getCount', self::class);

    $hasNoReferrals = $totalReferralCount === 0;
    Yii::beginProfile('api.users.userLink.buildReferralLink', self::class);
    $referralLink = Yii::$app->getModule('users')
      ->api('userLink')->buildReferralLink($userId)
    ;
    Yii::endProfile('api.users.userLink.buildReferralLink', self::class);
    Yii::endProfile('getCommonData', self::class);
    return compact('totalReferralCount', 'referralPercent', 'lastWeekMainAmount', 'lastWeekHoldAmount', 'lastWeekAmount', 'activeReferralCount', 'referralLink', 'hasNoReferrals');
  }

}

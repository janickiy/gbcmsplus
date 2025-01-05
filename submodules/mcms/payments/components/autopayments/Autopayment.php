<?php


namespace mcms\mcms\payments\components\autopayments;


use mcms\common\traits\LogTrait;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use mcms\user\models\User;
use mcms\user\Module as UsersModule;
use Yii;
use yii\base\BaseObject;

/**
 * Перебираем всех активных партнеров и создаем выплаты
 */
class Autopayment extends BaseObject
{
  use LogTrait;
  protected $_userIds = [];
  /** @var UserPaymentSetting[] */
  protected $_paymentSettings = [];
  /** @var UserWallet */
  protected $_autopayWallets = [];
  /** @var UsersModule */
  protected $usersModule;

  /**
   * @inheritDoc
   */
  public function init()
  {
    $this->usersModule = Yii::$app->getModule('users');

    parent::init();
  }

  /**
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  public function run()
  {
    $partners = $this->getPartners();
    foreach ($partners as $partner) {
      $partnerToPayment = new Partner($partner);
      $partnerToPayment->createPayment();
      $this->log($partnerToPayment->getLog() . PHP_EOL);
    }
  }

  /**
   * @param array $userIds
   */
  public function setUserIds(array $userIds)
  {
    $this->_userIds = $userIds;
  }

  /**
   * Получаем всех активных партнеров
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   * @return User[]
   */
  protected function getPartners()
  {
    $condition = ['namesRoles' => [UsersModule::PARTNER_ROLE], 'status' => User::STATUS_ACTIVE];

    return $this->usersModule->api('user', [
        'conditions' => array_merge($condition, count($this->_userIds) > 0 ? ['id' => $this->_userIds] : [])
      ]
    )->setResultTypeDataProvider()->getResult()->getModels();
  }
}
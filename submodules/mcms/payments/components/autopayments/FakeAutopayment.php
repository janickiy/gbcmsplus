<?php


namespace mcms\mcms\payments\components\autopayments;


use mcms\payments\models\UserBalanceInvoice;
use mcms\user\models\User;
use Yii;

/**
 * Перебираем всех активных партнеров и создаем выплаты
 */
class FakeAutopayment extends Autopayment
{

  /** @var int */
  public $compensationAmount = 500;

  /**
   * @inheritDoc
   */
  public function init()
  {
    $this->usersModule = Yii::$app->getModule('users');

    parent::init();
  }

  /**
   * @param User $partner
   * @param $amount
   * @throws \yii\base\InvalidConfigException
   */
  public function addCompensation(User $partner, $amount)
  {
    $this->log('Partner #' . $partner->getId() . '. Create compensation' . PHP_EOL);
    $model = new UserBalanceInvoice();
    $model->user_id = $partner->getId();
    $model->scenario = $model::SCENARIO_COMPENSATION;
    $model->amount = $amount;
    $model->type = $model::TYPE_COMPENSATION;
    $model->date = Yii::$app->formatter->asDate(strtotime('-12 day'), 'php:Y-m-d');
    $model->save();
  }

  /**
   * @param $amount
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  public function createCompensation($amount)
  {
    $partners = $this->getPartners();
    foreach ($partners as $partner) {
      $this->addCompensation($partner, $amount);
    }
  }

  /**
   * @param $amount
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function createPenalty($amount)
  {
    $partners = $this->getPartners();
    foreach ($partners as $partner) {

      $model = new UserBalanceInvoice();
      $model->setAttributes([
        'user_id' => $partner->getId(),
        'description' => 'Penalty from FakeAutopayment component',
        'scenario' => UserBalanceInvoice::SCENARIO_PENALTY,
        'amount' => $amount * -1,
        'type' => UserBalanceInvoice::TYPE_PENALTY,
      ]);

      $result = $model->save();
      $this->log('Penalty for #' . $partner->getId() . ' user: ' . ($result ? 'created' : 'error') . PHP_EOL);
    }
  }

  /**
   * @param array $userIds
   */
  public function setUserIds(array $userIds)
  {
    $this->_userIds = $userIds;
  }
}
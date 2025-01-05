<?php

namespace mcms\payments\commands;


use mcms\mcms\payments\components\autopayments\Autopayment;
use mcms\mcms\payments\components\autopayments\FakeAutopayment;
use yii\console\Controller;

/**
 * Автовыплаты
 */
class AutopaymentsController extends Controller
{
  /**
   * @var array
   */
  public $userIds = [];

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ['userIds'];
  }

  /**
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  public function actionIndex()
  {
    (new Autopayment(['userIds' => $this->userIds]))->run();
  }

  /**
   * @param int $amount
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   */
  public function actionFakeCompensation($amount = 0)
  {

    if (!YII_ENV_DEV) {
      return ;
    }

    $component = new FakeAutopayment();
    $amount = (int) $amount;

    $component->createCompensation($amount);
  }

  /**
   * @param $amount
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function actionFakePenalty($amount = 0)
  {
    if (!YII_ENV_DEV) {
      return ;
    }

    $component = new FakeAutopayment();
    $amount = (int) $amount;

    $component->createPenalty($amount);
  }
}
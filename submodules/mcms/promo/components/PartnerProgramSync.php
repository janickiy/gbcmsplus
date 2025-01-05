<?php

namespace mcms\promo\components;


use mcms\promo\components\events\PartnerProgramSynced;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PersonalProfit;
use mcms\promo\models\UserPromoSetting;
use mcms\promo\queues\PartnerProgramSyncPayload;
use mcms\promo\queues\PartnerProgramSyncWorker;
use Yii;
use yii\base\Component;
use yii\db\Expression;
use yii\db\Query;

/**
 * Синхронизация профитов пользователя с партнерскими программами
 */
class PartnerProgramSync extends Component
{
  /**
   * @var bool синхронизация без учета пермишена
   */
  public $forceSync = false;
  /**
   * @var int
   */
  public $userId;
  /**
   * @var string
   */
  private $userCurrency;
  /**
   * @var int
   */
  private $partnerProgramId;

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    $this->userCurrency = Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $this->userId])->getResult();
    $this->partnerProgramId = $this->getPartnerProgramId();
  }

  /**
   * @return bool
   * @throws \yii\db\Exception
   */
  public function run()
  {
    if (!$this->userId) return false;
    if (!$this->partnerProgramId) return false;

    $transaction = Yii::$app->db->beginTransaction();
    $condition = $this->getConditionToDeletePersonalProfit();
    // Если есть, что удалять, удаляем.
    if ($condition) {
      Yii::$app->db->createCommand()->delete(PersonalProfit::tableName(), $condition)->execute();
    }
    $command = Yii::$app->db->createCommand(strtr('
        INSERT INTO :table (
          user_id, operator_id, landing_id, rebill_percent,
          buyout_percent, cpa_profit_rub, cpa_profit_eur, cpa_profit_usd, created_by, created_at, updated_at
        )
        :select
      ', [
        ':table' => PersonalProfit::tableName(),
        ':select' => $this->getInsertLandingOperatorsQuery()->createCommand()->rawSql
      ]
    ));

    $command->execute();

    foreach ($this->getUpdateLandingOperatorQuery()->each() as $item) {
      $columns = $item;
      unset($columns['user_id'], $columns['operator_id'], $columns['landing_id']);
      Yii::$app->db->createCommand()->update(PersonalProfit::tableName(), $columns, [
        'user_id' => $this->userId,
        'operator_id' => $item['operator_id'],
        'landing_id' => $item['landing_id'],
      ])->execute();
    }

    $transaction->commit();

    (new PartnerProgramSynced($this->partnerProgramId, $this->userId))->trigger();
    (new PersonalProfit())->invalidateCache();

    return true;
  }

  /**
   * Синхронизация в фоне
   * @param $userId
   * @return bool Добавлена ли синхронизация в очередь (true не означает, что синхронизация выполнена)
   * @throws \InvalidArgumentException
   */
  public static function runAsync($userId)
  {
    try {
      $result = Yii::$app->queue->push(
        PartnerProgramSyncWorker::CHANNEL_NAME,
        new PartnerProgramSyncPayload(['initiatorUserId' => Yii::$app->user->id, 'userId' => $userId])
      );
    } catch (\Exception $e) {
      Yii::error(PartnerProgramSyncWorker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
      return false;
    }

    return $result;
  }

  /**
   * @return Query
   */
  private function getUpdateLandingOperatorQuery()
  {

    return $this
      ->getInsertLandingOperatorsQuery()
      ->select([
        'ppi.operator_id',
        'ppi.landing_id',
        'ppi.rebill_percent',
        'ppi.buyout_percent',
        'cpa_profit_rub' => 'ppi.cpa_profit_rub',
        'cpa_profit_eur' => 'ppi.cpa_profit_eur',
        'cpa_profit_usd' => 'ppi.cpa_profit_usd',
        'updated_at' => new Expression(time()),
      ])
      ->where(['ppi.partner_program_id' => $this->partnerProgramId, 'pp.user_id' => $this->userId]);
  }

  /**
   * @return Query
   */
  private function getInsertLandingOperatorsQuery()
  {
    return (new Query())
      ->select([
        'user_id' => new Expression($this->userId),
        'ppi.operator_id',
        'ppi.landing_id',
        'ppi.rebill_percent',
        'ppi.buyout_percent',
        'cpa_profit_rub' => (PersonalProfit::canManagePersonalCPAPrice() || $this->forceSync ? 'ppi.cpa_profit_rub' : new Expression('NULL')),
        'cpa_profit_eur' => (PersonalProfit::canManagePersonalCPAPrice() || $this->forceSync ? 'ppi.cpa_profit_eur' : new Expression('NULL')),
        'cpa_profit_usd' => (PersonalProfit::canManagePersonalCPAPrice() || $this->forceSync ? 'ppi.cpa_profit_usd' : new Expression('NULL')),
        'created_by' => new Expression(1),
        'created_at' => new Expression(time()),
        'updated_at' => new Expression(time()),
      ])
      ->from(['ppi' => PartnerProgramItem::tableName()])
      ->leftJoin(['pp' => PersonalProfit::tableName()], [
        'and',
        ['user_id' => $this->userId],
        [
          'or',
          ['and', 'ppi.landing_id = pp.landing_id', 'ppi.operator_id = pp.operator_id'],
          ['and', 'ppi.landing_id = pp.landing_id', 'ppi.operator_id is null', 'pp.operator_id is null'],
          ['and', 'ppi.operator_id = pp.operator_id', 'ppi.landing_id is null', 'pp.landing_id is null']
        ],
      ])
      ->where(['ppi.partner_program_id' => $this->partnerProgramId, 'pp.user_id' => null])
    ;
  }

  /**
   * @return array
   */
  private function getConditionToDeletePersonalProfit()
  {
    $result = (new Query())
      ->select([
        'user_id' => 'pp.user_id',
        'operator_id' => 'pp.operator_id',
        'landing_id' => 'pp.landing_id',
      ])
      ->from(['pp' => PersonalProfit::tableName()])
      ->leftJoin(['ppi' => PartnerProgramItem::tableName()], [
        'or',
        ['and', 'ppi.landing_id = pp.landing_id', 'ppi.operator_id = pp.operator_id'],
        ['and', 'ppi.landing_id = pp.landing_id', 'ppi.operator_id is null', 'pp.operator_id is null'],
        ['and', 'ppi.operator_id = pp.operator_id', 'ppi.landing_id is null', 'pp.landing_id is null'],
      ])
      ->where([
        'and',
        ['pp.user_id' => $this->userId],
        ['or',
          ['ppi.id' => null],
          ['<>', 'ppi.partner_program_id', $this->partnerProgramId]
        ]
      ])
      ->all();
    if ($result) {
      array_unshift($result, 'OR');
    }
    return $result;
  }

  /**
   * @return null|integer
   */
  public function getPartnerProgramId()
  {
    /** @var UserPromoSetting $userPromoSetting */
    if (!$userPromoSetting = UserPromoSetting::findOne(['user_id' => $this->userId])) {
      return null;
    }
    if (!$this->partnerProgramId = $userPromoSetting->partner_program_id) {
      return null;
    }
    return $this->partnerProgramId;
  }
}
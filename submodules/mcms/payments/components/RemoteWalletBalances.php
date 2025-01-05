<?php

namespace mcms\payments\components;

use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use rgk\payprocess\components\serviceResponse\BalanceResponse;
use Yii;
use yii\base\Event;
use yii\base\Object;
use yii\caching\TagDependency;

/**
 * Синглтон, лучше не инстанциировать каждый раз
 *
 * Class RemoteWalletBalances
 * @package mcms\payments\components
 */
class RemoteWalletBalances extends Object
{

  const CACHE_PREFIX = 'RemoteWalletBalances_';
  const CACHE_DURATION = 60 * 10;

  /**
   * @return array [['walletType' => 3, 'currency' => 'usd'], ['walletType' => 3, 'currency' => 'rub']]
   */
  public function getReadyToAutopayWallets()
  {
    /** @var Wallet[] $wallets */
    $wallets = Wallet::find()->andWhere([
      'or',
      ['IS NOT', 'rub_sender_api_id', null],
      ['IS NOT', 'usd_sender_api_id', null],
      ['IS NOT', 'eur_sender_api_id', null]
    ])->all();

    $ready = [];

    foreach ($wallets as $wallet) {
      foreach (['rub', 'usd', 'eur'] as $currency) {
        $attribute = $currency . '_sender_api_id';
        if (!$wallet->{$attribute}) continue;
        $relation = $currency . 'SenderApi';

        /** @var PaySystemApi $api */
        $api = $wallet->{$relation};

        if (!$api->isValidSettings()) continue;

        if (!$api->isBalanceApiAvailable()) continue;

        $ready[] = [
          'walletType' => $wallet->id,
          'currency' => $currency,
          'apiId' => $api->id,
          'balanceFromCache' => $this->getFromCache($api->id)
        ];
      }
    }

    return $ready;
  }

  /**
   * @param $apiId
   * @return float|null
   */
  public function getFromCache($apiId)
  {
    $cached = Yii::$app->cache->get(self::generateKey($apiId));

    if (is_numeric($cached)) return $cached;

    return null;
  }


  /**
   * Получить баланс внешнего кошелька
   * @param $apiId
   * @return number|null
   */
  public function get($apiId)
  {
    if ($cached = $this->getFromCache($apiId)) return $cached;

    /** @var BalanceResponse $balance */
    $balance = PaySystemApi::findOne($apiId)->getBalance()->balance;

    if (!is_numeric($balance)) return null;

    $balance = (float) $balance;

    $this->setCache($apiId, $balance);

    return $balance;
  }

  /**
   * @param $apiId
   * @return string
   */
  protected static function generateKey($apiId)
  {
    return self::CACHE_PREFIX . '_' . $apiId;
  }

  /**
   * @param $apiId
   * @param $value
   */
  protected function setCache($apiId, $value)
  {
    $tagDependency = new TagDependency(['tags' => [self::getCacheTag()]]);
    Yii::$app->cache->set(self::generateKey($apiId), $value, self::CACHE_DURATION, $tagDependency);
  }

  /**
   * @return string
   */
  protected static function getCacheTag()
  {
    return self::CACHE_PREFIX . 'Tag';
  }

  /**
   * Инвалидируем кэш. Либо по айди процессинга, либо все по тегу
   * @param int|null $apiId
   */
  public static function invalidateCache($apiId = null)
  {
    if ($apiId) {
      Yii::$app->cache->delete(self::generateKey($apiId));
      return;
    }

    TagDependency::invalidate(Yii::$app->cache, self::getCacheTag());
  }
}
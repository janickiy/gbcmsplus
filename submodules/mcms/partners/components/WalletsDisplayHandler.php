<?php

namespace mcms\partners\components;


use mcms\common\SystemLanguage;
use yii\base\Component;

/**
 * Class WalletsDisplayHandler
 * @package mcms\partners\components
 *
 * Компонент отвечает за отображение локальных ПС в партнерке
 *
 * @property bool $showLocal
 * @property bool $showLocalFirst
 * @property array $localWallets
 * @property array $internationalWallets
 */
class WalletsDisplayHandler extends Component
{
  const LOCAL_CURRENCY = 'rub';
  const LOCAL_LANGUAGE = 'ru';

  /**
   * Все типы платежных систем
   *
   * @var array
   */
  public $systemWallets = [];

  /**
   * Локальные платежные системы
   *
   * @var array
   */
  protected $localSystemWallets = [];

  /**
   * Международные платежные системы
   *
   * @var array
   */
  protected $internationalSystemWallets = [];

  /**
   * Кошельки партнера
   *
   * @var array
   */
  public $userWallets = [];

  /**
   * Валюта партнера
   *
   * @var string
   */
  public $userCurrency;

  /**
   * У партнера есть кошельки в локальных платежных системах
   *
   * @var bool
   */
  public $hasLocalUserWallets = false;

  /**
   * У партнера есть рублевые кошельки в любых платежных системах
   *
   * @var bool
   */
  public $hasWalletsInLocalCurrency = false;

  /**
   * Показывать локальные платежные системы
   *
   * @var bool
   */
  protected $showLocal = false;

  /**
   * Показывать локальные платежные системы сверху
   *
   * @var bool
   */
  protected $showLocalFirst = false;

  /**
   * @inheritdoc
   */
  public function init()
  {
    /**
     * Распределение между локальными и международными ПС
     * @var \mcms\payments\models\wallet\AbstractWallet $wallet
     */
    foreach ((array)$this->systemWallets as $wallet) {
      if ($wallet::isLocalityRu()) {
        $this->localSystemWallets[] = $wallet;
        continue;
      }
      $this->internationalSystemWallets[] = $wallet;
    }

    /**
     * Есть ли кошелек в локальной ПС
     * @var \mcms\payments\models\UserWallet $userWallet
     */
    foreach ((array)$this->userWallets as $userWallet) {
      $walletAccountClass = $userWallet->getAccountClass();
      if ($walletAccountClass::isLocalityRu()) {
        $this->hasLocalUserWallets = true;
        break;
      }
    }

    /**
     * Есть ли рублевый кошелек в любой из ПС
     * @var \mcms\payments\models\UserWallet $userWallet
     */
    foreach ((array) $this->userWallets as $userWallet) {
      if ($userWallet->currency === self::LOCAL_CURRENCY) {
        $this->hasWalletsInLocalCurrency = true;
        break;
      }
    }

    $systemLanguage = (new SystemLanguage)->getCurrent();

    /**
     * Язык партнера совпадает с локальным ||
     * У партнера есть кошельки в локальной валюте ||
     * Валюта партнера локальная
     */
    $this->showLocal = $systemLanguage === self::LOCAL_LANGUAGE
      || $this->hasLocalUserWallets === true
      || $this->userCurrency === self::LOCAL_CURRENCY;

    if ($this->showLocal) {
      $this->showLocalFirst = $systemLanguage === self::LOCAL_LANGUAGE && $this->userCurrency === self::LOCAL_CURRENCY;
    }
  }

  /**
   * @return bool
   */
  public function hasLocalWallets()
  {
    return !!$this->localSystemWallets;
  }

  /**
   * @return array
   */
  public function getLocalWallets()
  {
    return $this->localSystemWallets;
  }

  /**
   * @return bool
   */
  public function hasInternationalWallets()
  {
    return !!$this->internationalSystemWallets;
  }

  /**
   * @return array
   */
  public function getInternationalWallets()
  {
    return $this->internationalSystemWallets;
  }

  /**
   * @return bool
   */
  public function getShowLocal()
  {
    return $this->showLocal;
  }

  /**
   * @return bool
   */
  public function getShowLocalFirst()
  {
    return $this->showLocalFirst;
  }

  /**
   * Если это создание кошелька:
   *  Если валюта локальная и локальные не показывать - то вернёт false
   *  Во всех остальных случах true
   * Если это редактирование:
   *  Возвращаем true, усли валюта есть в списке
   *
   * @return bool
   */
  public function showCurrency($currencyCode, $isNewRecord = true, $availableCurrencies = [])
  {
    return $isNewRecord
      ? $currencyCode !== self::LOCAL_CURRENCY || $this->showLocal || $this->hasWalletsInLocalCurrency
      : in_array($currencyCode, $availableCurrencies, true);
  }
}
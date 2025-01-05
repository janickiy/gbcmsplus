<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\UserPromoSetting;
use mcms\promo\Module;
use mcms\promo\validators\GlobalPBValidator;
use mcms\user\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * Class UserPromoSettings
 * @package mcms\promo\components\api
 */
class UserPromoSettings extends ApiResult
{
  protected static $users = [];

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
  }

  /**
   * @param $userId
   * @param $value
   * @return bool
   */
  public function saveGridPageSize($userId, $value)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);

    if (!$model) $model = UserPromoSetting::getNewOne($userId);

    $model->grid_page_size = $value;

    return $model->save();
  }

  /**
   * @param $userId
   * @param $value
   * @return bool
   */
  public function saveIsAllowedSourceRedirect($userId, $value)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);

    if (!$model && !$value) return true;

    if (!$model) $model = UserPromoSetting::getNewOne($userId);

    $model->is_allowed_source_redirect = $value ? 1 : 0;

    return $model->save();
  }

  /**
   * @param $userId
   * @return bool
   */
  public function getIsAllowedSourceRedirect($userId)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) return false;

    return (bool)$model->is_allowed_source_redirect;
  }

  /**
   * Выключен ли выкуп для партнера
   * @param $userId
   * @param bool $useGlobal учитывать глобальную настройку
   * @return bool
   */
  public function getIsDisableBuyout($userId, $useGlobal = true)
  {
    // Если глобально выключено,
    $globalEnable = Yii::$app->settingsManager->getValueByKey(Module::SETTINGS_ENABLE_BUYOUT_FOR_PARTNERS);
    if (!$globalEnable && $useGlobal) return true;

    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) return false;

    return (bool)$model->is_disable_buyout;
  }

  /**
   * @param $userId
   * @param bool $value
   * @return bool
   */
  public function saveIsDisableBuyout($userId, $value)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) $model = UserPromoSetting::getNewOne($userId);

    $model->is_disable_buyout = (bool)$value;

    return $model->save();
  }

  /**
   * @param $userId
   * @return UserPromoSetting
   */
  public function getModel($userId)
  {
    $model = $userId ? $this->getUserSettings($userId) : null;
    return $model ?: UserPromoSetting::getNewOne($userId);
  }

  /**
   * @param $userId
   * @return int|null
   */
  public function getUserPartnerProgramId($userId)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) return false;

    return $model->partner_program_id;
  }

  /**
   * @param $userId
   * @return int|null
   */
  public function getUserPartnerProgramAutosync($userId)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) return false;

    return !!$model->partner_program_autosync;
  }

  /**
   * @param $userId
   * @param bool|true $enable
   * @return bool
   */
  public function setUserPartnerProgramAutosync($userId, $enable = true)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId) ? : UserPromoSetting::getNewOne($userId);
    $model->setPartnerProgramAutosync($enable);
    return $model->save();
  }

  /**
   * @param int $userId
   * @return UserPromoSetting
   */
  protected function getUserSettings($userId)
  {
    if(!ArrayHelper::keyExists($userId, self::$users)) {
      $userPromoSettings = UserPromoSetting::findOne($userId);
      self::$users[$userId] = $userPromoSettings ?: new UserPromoSetting(['user_id' => $userId]);
    }

    return ArrayHelper::getValue(self::$users, $userId);
  }

  /**
   * @param ActiveRecord $model
   * @param $column
   * @return \yii\db\ActiveQuery
   */
  public function hasOne(ActiveRecord $model, $column)
  {
    return $this->hasOneRelation($model, UserPromoSetting::class, ['user_id' => $column]);
  }

  /**
   * @return bool
   */
  public function getIsUserCanEditFakeFlag()
  {
    return Module::canEditUserFakeFlag();
  }

  /**
   * @param $userId
   * @param $value
   * @return bool
   */
  public function saveIsFakeRevshareEnabled($userId, $value)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);

    if (!$model && !$value) return true;

    if (!$model) $model = UserPromoSetting::getNewOne($userId);

    $model->is_fake_revshare_enabled = $value ? 1 : 0;

    return $model->save();
  }

  /**
   * @param $userId
   * @return int|null
   */
  public function getUserHasEnabledFakeRevshare($userId)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);
    if (!$model) return false;

    return (bool)$model->is_fake_revshare_enabled;
  }

  /**
   * @param $userId
   * @return int|null
   */
  public function getGridPageSize($userId)
  {
    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);

    return $model ? $model->grid_page_size : null;
  }

  /**
   * @param $userId
   * @param $value
   */
  public function saveGlobalPostbackUrl($userId, $value)
  {
    $model = $this->getModel($userId);
    $model->postback_url = $value;
    $model->save(false);
  }

  /**
   * @param $userId
   * @param $value
   */
  public function saveGlobalComplainsPostbackUrl($userId, $value)
  {
    $model = $this->getModel($userId);
    $model->complains_postback_url = $value;
    $model->save(false);
  }

  /**
   * @return string
   */
  public function getGlobalPostbackValidator()
  {
    return GlobalPBValidator::class;
  }

  /**
   * @param $userId
   * @return null|string
   */
  public function getGlobalPostbackUrl($userId)
  {
    return UserPromoSetting::getGlobalPostbackUrl($userId);
  }

  /**
   * @param $userId
   * @return null|string
   */
  public function getGlobalComplainsPostbackUrl($userId)
  {
    return UserPromoSetting::getGlobalComplainsPostbackUrl($userId);
  }

  /**
   * @param $userId
   * @return bool
   */
  public function getIsBlacklistTrafficBlocks($userId)
  {
    $settings = $this->getUserSettings($userId);
    if ($settings->isNewRecord) {
      return true; // по-дефолту у нас блэклист используется у партнера если нет строки user_promo_settings
    }
    return (bool)$settings->is_blacklist_traffic_blocks;
  }

  /**
   * @param $userId
   * @param $value
   * @return bool
   */
  public function saveIsBlacklistTrafficBlocks($userId, $value)
  {
    $value = (bool) $value;

    if (!User::findOne((int)$userId)) {
      // можно было бы в getUserSettings() поместить проверку,
      // но тот метод много где используется и могу навредить производительности
      throw new NotFoundHttpException();
    }

    /** @var UserPromoSetting $model */
    $model = $this->getUserSettings($userId);

    if ($model->isNewRecord && $value === true) {
      return true; // по-дефолту у нас блэклист используется у партнера если нет строки user_promo_settings
    }

    $model->is_blacklist_traffic_blocks = $value ? 1 : 0;

    return $model->save();
  }
}

<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\validators\LocalhostUrlValidator;
use mcms\common\validators\UrlValidator;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\SourceOperatorLandingsChangeProfitType;
use mcms\user\models\User;
use mcms\user\Module;
use mcms\promo\validators\GlobalPBValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;

/**
 * tricky: Если свойство необходимо менять через апи, необходимо добавить default value в правила валидации
 * Иначе в afterSave() нельзя понять, изменено свойство или нет
 *
 * @property integer $user_id
 * @property bool $is_allowed_source_redirect
 * @property bool $is_disable_buyout
 * @property bool $is_fake_revshare_enabled
 * @property bool $is_blacklist_traffic_blocks
 * @property integer $partner_program_id
 * @property integer $partner_program_autosync
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $add_fake_after_subscriptions
 * @property integer $add_fake_subscription_percent
 * @property integer $add_fake_cpa_subscription_percent
 * @property string $postback_url
 * @property string $complains_postback_url
 *
 * @property PartnerProgram $partnerProgram
 */
class UserPromoSetting extends \yii\db\ActiveRecord
{
  const SCENARIO_ADD_PARTNER_PROGRAM = 'add_partner_program';

  private static $_globalPostbackbUrl = null;
  private static $_globalComplainsPostbackbUrl = null;

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_ADD_PARTNER_PROGRAM => ['user_id', 'partner_program_id'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_promo_settings';
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    if (isset($changedAttributes['is_allowed_source_redirect'])) {
      ApiHandlersHelper::clearCache('get-ads-params_' . $this->user_id);
    }

    // если отключили выкуп для партнера, нужно сделать все его ленды ребиллами
    if (isset($changedAttributes['is_disable_buyout']) && $this->is_disable_buyout) {
      self::changeSourceOperatorLandingsProfitType($this->user);
    }
  }

  /**
   * Меняем profit_type на Ребилл для партнера, у которого отключили возможность выкупа
   * Сделано в рамках mcms-121
   * @param User|User[] $users
   */
  public static function changeSourceOperatorLandingsProfitType($users)
  {
    $users = is_array($users) ? $users : [$users];
    // Приводим массив пользователей к integer
    $userIds = array_map('intval', ArrayHelper::getColumn($users, 'id', []));
    // Если некому изменять лендинги, выходим
    if (!$userIds) return;

    // Меняю CPA на Ребилл
    Yii::$app->db->createCommand(
      'UPDATE sources_operator_landings sol 
INNER JOIN sources s ON s.id = sol.source_id
INNER JOIN landing_operators lo ON lo.landing_id=sol.landing_id AND lo.operator_id=sol.operator_id
INNER JOIN landing_subscription_types lst ON lst.id=lo.subscription_type_id
SET sol.profit_type = :rebill
WHERE s.user_id IN (' . implode(',', $userIds) . ') AND sol.profit_type = :buyout AND lst.code = :code_sub'
    )->bindValues([
      ':rebill' => SourceOperatorLanding::PROFIT_TYPE_REBILL,
      ':buyout' => SourceOperatorLanding::PROFIT_TYPE_BUYOUT,
      ':code_sub' => LandingSubscriptionType::CODE_SUBSCRIPTION,
    ])->execute();

    foreach($users as $user) {
      (new SourceOperatorLandingsChangeProfitType($user))->trigger();
    }
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id'], 'required'],
      [['user_id', 'partner_program_id', 'created_at', 'updated_at', 'is_fake_revshare_enabled', 'is_allowed_source_redirect', 'add_fake_after_subscriptions', 'add_fake_subscription_percent', 'add_fake_cpa_subscription_percent', 'grid_page_size'], 'integer'],
      [['partner_program_autosync', 'is_disable_buyout'], 'boolean'],
      ['partner_program_id', 'safe', 'on' => static::SCENARIO_ADD_PARTNER_PROGRAM],
      ['user_id', 'checkIsPartner', 'on' => static::SCENARIO_ADD_PARTNER_PROGRAM],
      [['is_allowed_source_redirect'], 'default', 'value' => 0],
      // добавлено значение по умолчанию, т.к. иначе после сохранения не ясно, изменилось значение или нет
      [['is_disable_buyout'], 'default', 'value' => false],
      [['postback_url', 'complains_postback_url'], UrlValidator::class, 'enableIDN' => true],
      [['postback_url', 'complains_postback_url'], LocalhostUrlValidator::class],
      [['postback_url', 'complains_postback_url'], GlobalPBValidator::class, 'skipOnEmpty' => false],
      [['postback_url', 'complains_postback_url'], 'filter', 'filter' => function($value) {
        return str_replace(['"', "'"], '', $value);
      }],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'user_id' => 'User ID',
      'partner_program_id' => 'Partner Program',
      'partner_program_autosync' => 'Partner Program Autosync',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'is_disable_buyout' => Yii::_t('promo.settings.is_disable_buyout'),
      'is_allowed_source_redirect' => Yii::_t('promo.settings.is_allowed_source_redirect'),
      'add_fake_after_subscriptions' => Yii::_t('promo.settings.add_after_subscriptions'),
      'add_fake_subscription_percent' => Yii::_t('promo.settings.add_subscription_percent'),
      'add_fake_cpa_subscription_percent' => Yii::_t('promo.settings.add_cpa_subscription_percent'),
      'postback_url' => Yii::_t('promo.settings.postback_url'),
      'complains_postback_url' => Yii::_t('promo.settings.complains_postback_url'),
    ];
  }

  /**
   * @param $attribute
   * @param $params
   */
  public function checkIsPartner($attribute, $params)
  {
    /** @var Module $userModule */
    $userModule = Yii::$app->getModule('users');
    if (!$userModule->api('rolesByUserId', ['userId' => $this->$attribute])->isPartner()) {
      $this->addError($attribute, Yii::_t('promo.partner_programs.user_is_not_a_partner'));
    }
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPartnerProgram()
  {
    return $this->hasOne(PartnerProgram::class, ['id' => 'partner_program_id']);
  }

  /**
   * @param $userId
   * @return UserPromoSetting
   */
  public static function getNewOne($userId)
  {
    return new self(['user_id' => $userId]);
  }

  /**
   * @param bool|true $enable
   * @return $this
   */
  public function setPartnerProgramAutosync($enable = true)
  {
    $this->partner_program_autosync = $enable ? 1 : 0;
    return $this;
  }

  /**
   * @return bool
   */
  public function isPartnerProgramAutosync()
  {
    return !!$this->partner_program_autosync;
  }

  /**
   * Получение ссылки на глобальный постбек для партнера
   * @return null|string
   */
  public static function getGlobalPostbackUrl($userId = null)
  {
    if ($userId === null) {
      $userId = Yii::$app->user->id;
    }

    if (self::$_globalPostbackbUrl === null) {
      $userPromoSettings = UserPromoSetting::findOne(['user_id' => $userId]);
      self::$_globalPostbackbUrl = $userPromoSettings && $userPromoSettings->postback_url ? $userPromoSettings->postback_url : '';
    }

    return self::$_globalPostbackbUrl;
  }

  /**
   * Получение ссылки на глобальный постбек для жалоб для партнера
   * @return null|string
   */
  public static function getGlobalComplainsPostbackUrl($userId = null)
  {
    if ($userId === null) {
      $userId = Yii::$app->user->id;
    }

    if (self::$_globalComplainsPostbackbUrl === null) {
      $userPromoSettings = UserPromoSetting::findOne(['user_id' => $userId]);
      self::$_globalComplainsPostbackbUrl = $userPromoSettings && $userPromoSettings->complains_postback_url ? $userPromoSettings->complains_postback_url : '';
    }

    return self::$_globalComplainsPostbackbUrl;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }
}

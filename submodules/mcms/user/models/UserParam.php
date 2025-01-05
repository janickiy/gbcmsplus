<?php

namespace mcms\user\models;

use kartik\builder\Form;
use mcms\common\traits\model\FormAttributes;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserParam
 * @package mcms\user\models
 *
 * @property integer $id
 * @property string $phone
 * @property integer $user_id
 * @property string $skype
 * @property string $image_url
 * @property string $topname
 * @property string $avatar
 * @property string $preview_url
 * @property integer $partner_type
 * @property bool $notify_browser_system
 * @property bool $notify_browser_news
 * @property bool $notify_email_system
 * @property bool $notify_email_news
 * @property bool $notify_telegram_news
 * @property bool $notify_telegram_system
 * @property bool $notify_push_news
 * @property bool $notify_push_system
 * @property array $notify_browser_categories
 * @property array $notify_email_categories
 * @property array $notify_telegram_categories
 * @property array $notify_push_categories
 * @property string $notify_email
 * @property bool $show_promo_modal
 * @property integer $telegram_id
 */
class UserParam extends ActiveRecord
{
    use FormAttributes;

    const PARTNER_TYPE_ARBITRARY = 1;
    const PARTNER_TYPE_WEBMASTER = 2;

    const SCENARIO_EDIT_NOTIFICATION_SETTINGS = 'edit_notification_settings';
    const SCENARIO_EDIT_TOGGLE_PROMO_MODAL = 'edit_toggle_promo_modal';

    const CACHE_KEY_BY_USER_ID = 'mcms.userParams.by_user_id.userId{userId}';

    public $formAttributes = [];


    /**
     * Получение параметров пользователя по id
     * @param int $userId
     * @return UserParam
     */
    public static function getByUserId($userId)
    {
        $cacheKey = static::getCacheKey($userId);
        $data = $userId ? Yii::$app->cache->get($cacheKey) : null;

        if (!$data) {
            $data = $userId ? static::findOne(['user_id' => $userId]) : null;
            if (empty($data)) {
                $data = new static(['user_id' => $userId]);
            }

            if ($data->hasNotifyCategoriesColumns()) {
                $data->notify_browser_categories = $data->getNotifyBrowserCategories();
                $data->notify_email_categories = $data->getNotifyEmailCategories();
                $data->notify_telegram_categories = $data->getNotifyTelegramCategories();
                $data->notify_push_categories = $data->getNotifyPushCategories();
            }

            if ($userId) {
                Yii::$app->cache->set($cacheKey, $data, 3600);
            }
        }

        return $data;
    }

    private function hasNotifyCategoriesColumns()
    {
        $tableSchema = $this->getTableSchema();
        return
            $tableSchema->getColumn('notify_browser_categories') &&
            $tableSchema->getColumn('notify_email_categories') &&
            $tableSchema->getColumn('notify_telegram_categories') &&
            $tableSchema->getColumn('notify_push_categories');
    }

    /**
     * Категории для уведомления через браузер
     * @return array|mixed
     */
    public function getNotifyBrowserCategories()
    {
        $data = is_array($this->notify_browser_categories)
            ? $this->notify_browser_categories
            : unserialize($this->notify_browser_categories);

        return is_array($data)
            ? $data
            : $this->getDefaultNotificationCategories();
    }

    /**
     * Категории для уведомления через Email
     * @return array|mixed
     */
    public function getNotifyEmailCategories()
    {
        $data = is_array($this->notify_email_categories)
            ? $this->notify_email_categories
            : unserialize($this->notify_email_categories);

        return is_array($data)
            ? $data
            : $this->getDefaultNotificationCategories();
    }

    /**
     * Категории для уведомления через Telegram
     * @return array|mixed
     */
    public function getNotifyTelegramCategories()
    {
        $data = is_array($this->notify_telegram_categories)
            ? $this->notify_telegram_categories
            : unserialize($this->notify_telegram_categories);

        return is_array($data)
            ? $data
            : $this->getDefaultNotificationCategories();
    }

    /**
     * Категории для Push уведомлений
     * @return array|mixed
     */
    public function getNotifyPushCategories()
    {
        $data = is_array($this->notify_push_categories)
            ? $this->notify_push_categories
            : unserialize($this->notify_push_categories);

        return is_array($data)
            ? $data
            : $this->getDefaultNotificationCategories();
    }

    /**
     * Получение ключа для кеширования
     * @param int $userId
     * @return string
     */
    public static function getCacheKey($userId)
    {
        return strtr(self::CACHE_KEY_BY_USER_ID, ['{userId}' => $userId]);
    }

    public function initFormAttributes()
    {
        $this->formAttributes = [
            'phone' => [
                'type' => Form::INPUT_TEXT,
                'label' => Yii::_t('forms.user_phone'),
            ],
            'skype' => [
                'type' => Form::INPUT_TEXT,
                'label' => Yii::_t('forms.user_skype'),
            ],
            'hide_promo' => [
                'type' => Form::INPUT_CHECKBOX,
                'label' => Yii::_t('forms.user_hide_form'),
            ],
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['color', 'topname', 'notify_email'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process', 'except' => ['adminCreate', 'adminEdit']],
            ['telegram_id', 'integer'],
            ['phone', 'string', 'max' => 20],
            [['skype', 'topname'], 'string', 'max' => 255],
            [['partner_type', 'show_promo_modal'], 'safe'],
            [['color'], 'string', 'except' => ['adminCreate', 'adminEdit']],
            ['partner_type', 'safe', 'except' => ['adminCreate', 'adminEdit']],
            [['notify_browser_system', 'notify_browser_news', 'notify_email_system', 'notify_email_news', 'notify_telegram_news', 'notify_telegram_system'], 'integer', 'except' => ['adminCreate', 'adminEdit']],
            [['notify_browser_categories', 'notify_email_categories', 'notify_telegram_categories', 'notify_push_categories'], 'each', 'rule' => ['integer'], 'except' => ['adminCreate', 'adminEdit']],
            [['notify_email'], 'email', 'except' => ['adminCreate', 'adminEdit']],
            [['show_promo_modal'], 'default', 'value' => 1],
            [['color'], 'filter', 'filter' => 'addslashes', 'except' => ['adminCreate', 'adminEdit']],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'phone' => Yii::_t('users.forms.user_phone'),
            'skype' => Yii::_t('users.forms.user_skype'),
            'topname' => Yii::_t('users.forms.user_topname'),
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'create' => ['phone', 'skype', 'topname', 'show_promo_modal'],
            'adminEdit' => ['phone', 'skype', 'topname', 'show_promo_modal'],
            'adminCreate' => ['phone', 'skype', 'topname', 'show_promo_modal'],
            'edit' => ['phone', 'skype', 'color', 'topname', 'show_promo_modal'],
            'view' => ['phone', 'skype', 'topname'],
            self::SCENARIO_EDIT_NOTIFICATION_SETTINGS => [
                'notify_browser_system',
                'notify_browser_news',
                'notify_email_system',
                'notify_email_news',
                'notify_telegram_news',
                'notify_telegram_system',
                'notify_browser_categories',
                'notify_email_categories',
                'notify_telegram_categories',
                'notify_push_categories',
                'telegram_id',
            ],
            self::SCENARIO_EDIT_TOGGLE_PROMO_MODAL => [
                'show_promo_modal'
            ]
        ]);
    }

    static function tableName()
    {
        return 'user_params';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function invalidateCache()
    {
        Yii::$app->cache->delete(static::getCacheKey($this->user_id));
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;

        if ($this->hasNotifyCategoriesColumns()) {
            $this->notify_browser_categories = is_array($this->notify_browser_categories)
                ? serialize($this->notify_browser_categories)
                : $this->notify_browser_categories;
            $this->notify_email_categories = is_array($this->notify_email_categories)
                ? serialize($this->notify_email_categories)
                : $this->notify_email_categories;
            $this->notify_telegram_categories = is_array($this->notify_telegram_categories)
                ? serialize($this->notify_telegram_categories)
                : $this->notify_telegram_categories;
            $this->notify_push_categories = is_array($this->notify_push_categories)
                ? serialize($this->notify_push_categories)
                : $this->notify_push_categories;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->invalidateCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->invalidateCache();
    }

    private function getDefaultNotificationCategories()
    {
        return array_keys(Yii::$app->getModule('modmanager')
            ->api('modulesWithEvents', ['useDbId'])
            ->getResult()
        );
    }

    public function afterFind()
    {
        parent::afterFind();

        if ($this->hasNotifyCategoriesColumns()) {
            $browserUnserializedSettings = unserialize($this->notify_browser_categories);
            $emailUnserializedSettings = unserialize($this->notify_email_categories);
            $telegramUnserializedSettings = unserialize($this->notify_telegram_categories);
            $pushUnserializedSettings = unserialize($this->notify_push_categories);
            $this->notify_telegram_categories = $telegramUnserializedSettings !== false
                ? $telegramUnserializedSettings
                : $this->getDefaultNotificationCategories();
            $this->notify_push_categories = $pushUnserializedSettings !== false
                ? $pushUnserializedSettings
                : $this->getDefaultNotificationCategories();
            $this->notify_browser_categories = $browserUnserializedSettings !== false
                ? $browserUnserializedSettings
                : $this->getDefaultNotificationCategories();

            $this->notify_email_categories = $emailUnserializedSettings !== false
                ? $emailUnserializedSettings
                : $this->getDefaultNotificationCategories();
        }
    }

    /**
     * Связывание с DynamicActiveRecord при добавлении пользователя
     * @param \mcms\user\models\User $user
     */
    public function linkDynamicActiveRecord(User $user)
    {
        $this->user_id = $user->id;
    }

}
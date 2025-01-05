<?php

namespace mcms\support\models;

use mcms\common\helpers\ArrayHelper;
use mcms\support\Module;
use Yii;
use mcms\support\components\events\EventCreated;
use mcms\support\components\events\EventDelegated;
use mcms\support\components\events\EventStatusChanged;
use mcms\user\models\User;
use yii\helpers\StringHelper;
use mcms\common\helpers\Link;

/**
 * Class Support
 * @package mcms\support\models
 * @property $id
 * @property $support_category_id
 * @property $created_by
 * @property $delegated_to
 * @property $is_opened
 * @property $has_unread_messages
 * @property $owner_has_unread_messages
 * @property $last_text_created_at
 */
class Support extends AbstractSupport
{

  const SCENARIO_CREATE = 'create';
  const SCENARIO_CREATE_BY_PARTNER = 'create_by_partner';
  const SCENARIO_EDIT = 'edit';
  const SCENARIO_OWNER_SET_AS_READ = 'owner_set_as_read';

  public static function tableName()
  {
    return 'support';
  }

  public function getCreatedBy()
  {
    return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'created_by']);
  }

  public function getText()
  {
    return $this->hasMany(SupportText::class, ['support_id' => 'id']);
  }

  public function getTextCount()
  {
    return $this->getText()->count();
  }

  public function getHistory()
  {
    return $this->hasMany(SupportHistory::class, ['support_id' => 'id']);
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['name', 'support_category_id'],
      self::SCENARIO_EDIT => ['support_category_id', 'delegated_to'],
      self::SCENARIO_CREATE_BY_PARTNER => ['name', 'support_category_id'],
      self::SCENARIO_OWNER_SET_AS_READ => ['owner_has_unread_messages']
    ]);
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      ['name', 'string'],
      ['is_opened', 'default', 'value' => 1],
      ['has_unread_messages', 'default', 'value' => 1],
      ['owner_has_unread_messages', 'default', 'value' => 0],
      [['name', 'support_category_id', 'created_by'], 'required'],
      [['last_text_created_at'], 'integer'],
    ]);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   * @throws \Exception
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $transaction = Yii::$app->db->beginTransaction();

    try {
      SupportHistory::saveHistory($this, $changedAttributes, $insert);
      $transaction->commit();

      $isOpenedChanged = array_key_exists('is_opened', $changedAttributes);

      if (!$insert && ($isOpenedChanged || array_key_exists('has_unread_messages', $changedAttributes))) {
        Module::getInstance()->api('badgeCounters')->invalidateCache();
      }

      if (!$insert && $isOpenedChanged) {
        (new EventStatusChanged($this))->trigger();
      }

      if (!$insert && array_key_exists('delegated_to', $changedAttributes)) {
        (new EventDelegated($this, $this->getDelegatedTo()->one()))->trigger();
      }
    } catch (\Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  public function beforeSave($insert)
  {
    if (!$this->isOpened()) {
      $this->setAsRead();
      $this->setAsReadOwner();
    }

    return parent::beforeSave($insert);
  }

  public function hasUnreadMessages()
  {
    return !!$this->getAttribute('has_unread_messages');
  }

  public function ownerHasUnreadMessages()
  {
    return !!$this->getAttribute('owner_has_unread_messages');
  }

  public function handleUnreadMessages()
  {
    $lastText = $this
      ->getText()
      ->orderBy('created_at DESC')
      ->one()
    ;

    $this->has_unread_messages = $this->created_by != $lastText->from_user_id ? 0 : 1;
    return $this->save();
  }

  /**
   * Отметить тикет как прочитанный администратором
   * @return self
   */
  public function setAsRead()
  {
    $this->has_unread_messages = 0;

    return $this;
  }

  /**
   * Отметить тикет как прочитанный создателем тикета
   * @return self
   */
  public function setAsReadOwner()
  {
    $this->owner_has_unread_messages = 0;

    return $this;
  }

  public function markAsRead()
  {
    return $this->setAsRead()->save();
  }

  public function getReplacements()
  {
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();

    /** @var User $delegatedTo */
    $delegatedTo = $this->getDelegatedTo()->one();

    /** @var SupportText $text */
    $text = $this->getText()->orderBy('created_at DESC')->one();

    return [
      'id' => [
        'value' => $this->id,
        'help' => [
          'label' => 'support.replacements.ticket_id',
        ]
      ],
      'name' => [
        'value' => $this->name,
        'help' => [
          'label' => 'support.replacements.ticket_name',
        ]
      ],
      'truncatedName' => [
        'value' => $this->truncatedName,
        'help' => [
          'label' => 'support.replacements.ticket_name',
        ]
      ],
      'createdBy' => [
        'value' => $createdBy ? $createdBy->getReplacements() : null,
        'help' => [
          'label' => 'support.replacements.ticket_createdBy',
          'class' => Yii::$app->user->identityClass
        ]
      ],
      'delegatedTo' => [
        'value' => $delegatedTo ? $delegatedTo->getReplacements() : null,
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => 'support.replacements.ticket_delegatedTo'
        ]
      ],
      'isOpened' => [
        'value' => $this->is_opened
          ? Yii::_t('support.controller.ticket_opened')
          : Yii::_t('support.controller.ticket_closed'),
        'help' => [
          'label' => 'support.replacements.ticket_isOpened'
        ]
      ],
      'message' => [
        'value' => $text ? $text->getReplacements() : null,
        'help' => [
          'class' => SupportText::class,
          'label' => 'support.replacements.ticket_message'
        ]
      ],
    ];
  }

  public function canManageDelegatedTicket()
  {
    return Yii::$app->getUser()->can('SupportDelegatedTicketRule', ['ticket' => $this]);
  }

  public function canManageOwnTicket()
  {
    return Yii::$app->getUser()->can('SupportOwnTicketRule', ['ticket' => $this]);
  }

  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'nameLink' => Yii::_t('support.controller.ticket_name'),
      'name' => Yii::_t('support.controller.ticket_name'),
      'support_category_id' => Yii::_t('support.controller.ticket_category_label'),
      'is_opened' => Yii::_t('support.controller.ticket_isOpened'),
      'has_unread_messages' => Yii::_t('support.controller.ticket_hasUnreadMessages_label'),
      'created_at' => Yii::_t('support.controller.ticket_created_at'),
    ];
  }

  public function getCategoriesDropDown()
  {
    $categories = SupportCategory::find()->where(['is_disabled' => 0])->all();
    $dropDown = [];
    foreach ($categories as $category) {
      $dropDown[$category->id] = (string) $category->name;
    }
    return $dropDown;
  }

  /**
   * Роли на которые можно назначать тикеты
   * @return array
   */
  public function getDelegatedToRoles()
  {
    return (new SupportCategory())->getRolesIds(true);
  }

  public function getIsOpened($status = null)
  {
    $list = [
      0 => Yii::_t('support.controller.ticket_closed'),
      1 => Yii::_t('support.controller.ticket_opened')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }
  public function getOpenedName()
  {
    return $this->getIsOpened($this->is_opened);
  }

  public function getHasUnread($status = null)
  {
    $list = [
      0 => Yii::_t('support.controller.ticket_hasNotUnreadMessages'),
      1 => Yii::_t('support.controller.ticket_hasUnreadMessages')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }
  public function getHasUnreadName()
  {
    return $this->getHasUnread($this->has_unread_messages);
  }

  public function getCreatedByLink()
  {
    return \mcms\common\helpers\Html::a(
      $this->createdBy->username,
      ['/users/users/view', 'id' => $this->created_by],
      ['data-pjax' => 0],
      ['UsersUserView' => ['userId' => $this->created_by]],
      false
    );
  }
  public function getDelegatedToLink()
  {
    return $this->delegatedTo ? \mcms\common\helpers\Html::a(
       $this->delegatedTo->username,
      ['/users/users/view', 'id' => $this->delegated_to],
      ['data-pjax' => 0],
      ['UsersUserView' => ['userId' => $this->delegated_to]],
      false
    ) : null;
  }

  public function getTruncatedName($length = 140)
  {
    return StringHelper::truncate($this->name, $length, '...', null);
  }

  public function getNameLink()
  {
    return Link::get(
      '/support/tickets/view/',
      ['id' => $this->id],
      [],
      Yii::$app->formatter->asText($this->name),
      false
    );
  }
}

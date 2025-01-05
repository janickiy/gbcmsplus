<?php

namespace mcms\notifications\models;

use mcms\notifications\components\invitations\EmailInvitationsBuilder;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use Yii;

/**
 * Модель для создания уведомлений для приглашений
 * @package mcms\notifications\models
 */
class NotificationInvitationForm extends Model
{
  /**
   * @var string
   */
  public $header;

  /**
   * @var string
   */
  public $from;

  /**
   * @var string
   */
  public $template;

  /**
   * @var int|null id приглашения, если не указано, отправится всем
   */
  public $invitation_id;

  /**
   * @var int сохранить и отправить
   */
  public $send;

  /**
   * @var int оправить, даже если такое письмо уже отправлялось
   */
  public $force_send;

  /**
   * @var UserInvitationEmail
   */
  protected $model;

  /**
   * NotificationInvitationForm constructor.
   * @param UserInvitationEmail $model
   * @param array $config
   */
  public function __construct(UserInvitationEmail $model, array $config = [])
  {
    $this->model = $model;
    $this->setAttributes($model->attributes);

    parent::__construct($config);
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['header', 'template', 'from'], 'string'],
      ['from', 'default', 'value' => $this->getDefaultFrom()],
      ['from', 'email'],
      [['header',], 'required'],
      [['invitation_id', 'send', 'force_send'], 'integer'],
    ];
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'from' => Yii::_t('notifications.invitations.attribute-from'),
      'header' => Yii::_t('notifications.invitations.attribute-header'),
      'template' => Yii::_t('notifications.invitations.attribute-template'),
      'invitation_id' => Yii::_t('notifications.invitations.attribute-invitation'),
      'send' => Yii::_t('notifications.invitations.attribute-send'),
      'force_send' => Yii::_t('notifications.invitations.attribute-force_send'),
    ];
  }

  /**
   * @return string
   */
  public function getDefaultFrom()
  {
    /** @var \mcms\notifications\Module $notificationModule */
    $notificationModule = Yii::$app->getModule('notifications');

    return $notificationModule->noreplyEmail();
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->model->id;
  }

  /**
   * @return ArrayDataProvider
   */
  public function getReplacementsDataProvider()
  {
    $replacements = $this->model->getReplacementsHelp();
    $models = [];

    foreach ($replacements as $replacement => $help) {
      $models[] = [
        'key' => $replacement,
        'help' => $help
      ];
    }

    return new ArrayDataProvider([
      'pagination' => [
        'pageSize' => count($replacements),
      ],
      'allModels' => $models,
    ]);
  }

  /**
   * @param bool $runValidation
   * @return bool
   */
  public function save($runValidation = true)
  {
    if ($runValidation && !$this->validate()) {
      return false;
    }

    $this->model->setAttributes($this->attributes);
    if (!$this->model->save()) {
      $this->addErrors($this->model->errors);

      return false;
    }

    if ($this->send || $this->force_send) {
      $this->model->is_complete = 0;
      $this->model->save();

      $sender = new EmailInvitationsBuilder($this->model, [
        'invitationsIds' => [$this->invitation_id]
      ]);
      $sender->run($this->force_send);
    }

    return true;
  }
}
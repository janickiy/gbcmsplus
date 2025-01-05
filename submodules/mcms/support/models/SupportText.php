<?php

namespace mcms\support\models;

use mcms\support\components\events\EventMessageReceived;
use mcms\support\components\events\EventMessageSend;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yiidreamteam\upload\FileUploadBehavior;


/**
 * Class SupportText
 * @package mcms\support\models
 * @property int $id
 * @property int $supportId
 * @property int $from_user_id
 * @property string $text
 * @property int $created_at
 * @property int $updated_at
 * @property \yii\web\UploadedFile $images
 *
 * @property string $messageCreatedAt
 * @property bool $isOwner
 * @property User $fromUser
 */
class SupportText extends ActiveRecord
{

  const SCENARIO_CREATE_BY_PARTNER = 'create_by_partner';

  const FILE_PATH = '@uploadPath/support/message/[[pk]]/';
  const FILE_URL = '@uploadUrl/support/message/[[pk]]/';

  /**
   * Переменная для сохранения пути до файла из партнерского кабинета
   * @var string
   */
  private $partnerFilePath;

  public function behaviors()
  {
    $filename = Yii::$app->security->generateRandomString();
    return [
      TimestampBehavior::class,
      'file' => [
        'class' => FileUploadBehavior::class,
        'attribute' => 'images',
        'filePath' => self::FILE_PATH . $filename . '.[[extension]]',
        'fileUrl' => self::FILE_URL . $filename . '.[[extension]]'
      ]
    ];
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      ['text', 'filter', 'filter' => function($value) {
        $filteredValue = \yii\helpers\HtmlPurifier::process($value, [
          'HTML.Allowed' => 'blockquote,pre,h1,h2,h3,h4,h5,h6,p[style],strong,em,del,ul,ol,li,a[href|target|rel],hr',
          'Attr.AllowedRel' => ['noreferrer'],
          'Attr.AllowedFrameTargets' => ['_blank'],
          'CSS.AllowedProperties' => ['margin-left', 'text-align'],
        ]);
        return $filteredValue;
      }],
      [['text', 'from_user_id', 'support_id'], 'required'],
      ['images', 'file', 'mimeTypes' => ['image/jpeg', 'image/gif', 'image/jpg', 'image/png']],
      ['images', 'safe']
    ]);
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE_BY_PARTNER => ['text', 'images']
    ]);
  }

  public static function tableName()
  {
    return 'support_texts';
  }

  public function getSupport()
  {
    return $this->hasOne(Support::class, ['id' => 'support_id']);
  }

  public function getFromUser()
  {
    return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'from_user_id']);
  }

  public function beforeSave($insert)
  {
    if (!parent::beforeSave($insert)) return false;

    if ($this->scenario == self::SCENARIO_CREATE_BY_PARTNER && $this->images) {
      $this->partnerFilePath = $this->images;
      $this->images = basename($this->images);
    }

    return true;
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $support = $this->getSupport()->one();
    if ($insert) {
      $support->last_text_created_at = $this->created_at;
      $support->owner_has_unread_messages = Yii::$app->user->id != $support->created_by;
      $support->has_unread_messages = Yii::$app->user->id != $this->from_user_id;
      $support->save();
    }

    // В партнерском кабинете загрузка изображений происходит во временную папку
    // После сохранения изображения нужно переносить в папку для сообщения
    if ($this->scenario == self::SCENARIO_CREATE_BY_PARTNER && $this->partnerFilePath) {
      $newPath = Yii::getAlias(strtr(self::FILE_PATH, ['[[pk]]' => $this->id]));
      FileHelper::createDirectory($newPath);
      rename($this->partnerFilePath, $newPath.$this->images);
    }

    // Значение аттрибута хардкодено в behavior и поменять его нельзя
    // Приходится задавать его название заново и пересохранять
    if (array_key_exists('file', $this->behaviors)) {
      /** @var FileUploadBehavior $fileBehavior */
      $fileBehavior = $this->getBehavior('file');
      $this->setAttribute('images', basename($fileBehavior->getUploadedFilePath('images')));
      $this->detachBehavior('file');
      $this->save();
    } else {
      /* @var $support Support */

      $createdBy = $support->getCreatedBy()->one();

      $support->handleUnreadMessages();
      if ($support->getTextCount() > 1) {
        Yii::$app->user->id == $support->created_by
          ? (new EventMessageSend($support, $this))->trigger()
          : (new EventMessageReceived($support, $this, $createdBy))->trigger();
      }
    }
  }

  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => 'support.replacements.text_id'
        ]
      ],
      'fromUserId' => [
        'value' => $this->isNewRecord ? null : $this->getFromUser()->one()->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => 'support.replacements.text_fromUserId'
        ]
      ],
      'text' => [
        'value' => $this->isNewRecord ? null : $this->text,
        'help' => [
          'label' => 'support.replacements.text_text'
        ]
      ]
    ];
  }

  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), [
      'text' => Yii::_t('support.labels.support_text_text'),
      'images' => Yii::_t('support.labels.support_text_images')
    ]);
  }

  public function setImages($image)
  {
    $this->setAttribute('images', $image);
  }

  public function getUsername()
  {
    return $this->getFromUser()->one()->username;
  }

  public function getMessageCreatedAt()
  {
    return Yii::$app->getFormatter()->asDuration(time() - $this->created_at);
  }

  public function getIsOwner()
  {
    return $this->getSupport()->one()->created_by == $this->from_user_id;
  }

  public function canManageOwnTicketText()
  {
    return Yii::$app->getUser()->can('SupportOwnTicketTextRule', ['ticketText' => $this]);
  }

  public function getImageSrc()
  {
    return Yii::getAlias(strtr(self::FILE_URL, ['[[pk]]' => $this->id])) . $this->images;
  }

}
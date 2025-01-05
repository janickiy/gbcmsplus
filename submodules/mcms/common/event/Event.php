<?php

namespace mcms\common\event;


use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use mcms\notifications\components\event\EventReplacements;

abstract class Event implements \Serializable
{
  const EVENT_CAPTURED = 'event_captured';
  static $isOldSerialize = false;
  public $owner;
  public $addEmails;

  protected $allowSerialization = true;

  /**
   * @return array
   */
  public function getAdditionalReplacements()
  {
    return [];
  }

  public function getOwner()
  {
    return $this->owner === null ? Yii::$app->user->getIdentity() : $this->owner;
  }

  public static function on($name, $handler, $data = null, $append = true)
  {
    Yii::$app->on($name, $handler, $data, $append);
  }

  public static function off($name, $handler = null)
  {
    Yii::$app->off($name, $handler);
  }

  public function trigger()
  {
    $this->owner = $this->getOwner();

    $eventObject = new EventObject();
    $eventObject->event = $this;

    Yii::$app->trigger($this->getEventId(), $eventObject);
    Yii::trace('Event ' . $this->getEventId() .' triggered', self::class);
    Yii::$app->trigger(self::EVENT_CAPTURED, $eventObject);
    Yii::trace('Event ' . self::EVENT_CAPTURED . ' triggered', self::class);
  }

  public function release(Callable $handler)
  {
    static::off($this->class, $handler);
  }

  public function capture(Callable $handler, $append = true)
  {
    static::on($this->class, null, $handler, $append);
  }

  public static function class
  {
    return get_called_class();
  }

  abstract function getEventName();

  public function getReplacements()
  {
//    Yii::beginProfile('getReplacements', self::class);
    $eventVars = get_object_vars($this);
    if (isset($eventVars['owner'])) unset($eventVars['owner']);

    /** @var \mcms\notifications\Module $notificationModule */
    $notificationModule = Yii::$app->getModule('notifications');

    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');

    $replacements = [
      '{support_email}' => $notificationModule->supportEmail(),
      '{info_email}' => $notificationModule->infoEmail(),
      '{noreply_email}' => $notificationModule->noreplyEmail(),
      '{admin_email}' => $notificationModule->adminEmail(),
      '{serverName}' => $partnersModule->getServerName(),
      '{projectName}' => $partnersModule->getProjectName(),
      '{notificationSettingsUrl}' => $partnersModule->getNotificationSettingsUrl(),
    ];

    $additionalReplacements = $this->getAdditionalReplacements();
    if ($additionalReplacements && is_array($additionalReplacements)) {
      foreach ($additionalReplacements as $field => $value) {
        $replacements[sprintf('{%s}', $field)] = $value;
      }
    }

    foreach ($eventVars as $key => $class) {
      $eventReplacements = (new EventReplacements($class, $key))->getReplacements();
      $replacements = array_merge(
        $replacements,
        $eventReplacements
      );
    }

    $replacements = array_merge(
      $replacements,
      (new EventReplacements($this->owner, 'owner'))->getReplacements()
    );

//    Yii::endProfile('getReplacements', self::class);
    return $replacements;
  }

  public function getReplacementsHelp()
  {
//    Yii::beginProfile('getReplacementsHelp', self::class);
    $label = $this->labels();
    /** @var \mcms\notifications\Module $notificationModule */
    $notificationModule = Yii::$app->getModule('notifications');

    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');

    $help = [
      '{support_email}' => htmlspecialchars($notificationModule->supportEmail()),
      '{info_email}' => $notificationModule->infoEmail(),
      '{noreply_email}' => $notificationModule->noreplyEmail(),
      '{admin_email}' => $notificationModule->adminEmail(),
      '{serverName}' => $partnersModule->getServerName(),
      '{projectName}' => $partnersModule->getProjectName(),
      '{notificationSettingsUrl}' => $partnersModule->getNotificationSettingsUrl(),
    ];

    $additionalReplacements = $this->getAdditionalReplacements();
    if ($additionalReplacements && is_array($additionalReplacements)) {
      foreach ($additionalReplacements as $field => $value) {
        $help[sprintf('{%s}', $field)] = $value;
      }
    }

    $help = array_merge(
      $help,
      (new EventReplacements(Yii::$app->user->identityClass, 'owner'))->getReplacementsHelp()
    );

    foreach ((new \ReflectionClass($this))->getConstructor()->getParameters() as $dependency) {
      $help = array_merge($help, (new EventReplacements(
        ($dependency->getClass() ? $dependency->getClass()->name : null),
        $dependency->getName(),
        ($dependency->getClass() ? null : ArrayHelper::getValue($label, $dependency->name))
      ))->getReplacementsHelp()
      );
    }

//    Yii::endProfile('getReplacementsHelp', self::class);
    return $help;
  }

  public function getEventId()
  {
    return static::class;
  }

  /**
   * @return int
   */
  public function getModelId()
  {
    return null;
  }

  /**
   * @return bool
   */
  public function shouldSendNotification()
  {
    return true;
  }

  /**
   * @param null $id
   * @return string
   */
  public static function getUrl($id = null)
  {
    return null;
  }

  public function labels()
  {
    return [];
  }

  public function setOwner($owner)
  {
    $this->owner = $owner;
    return $this;
  }

  public function incrementBadgeCounter()
  {
    return false;
  }

  public function refreshModels()
  {
    $objectVars = get_object_vars($this);
    foreach ($objectVars as $key => &$var) {
      if (!$var instanceof ActiveRecord) continue;
      $var->refresh();
    }
  }

  /**
   * String representation of object
   * @link http://php.net/manual/en/serializable.serialize.php
   * @return string the string representation of the object or null
   * @since 5.1.0
   */
  public function serialize()
  {
    if(!$this->allowSerialization) return null;

    return self::$isOldSerialize
      ? $this->oldSerialize()
      : $this->newSerialize()
      ;
  }


  private function newSerialize()
  {
    $objectVars = get_object_vars($this);
    foreach ($objectVars as $key => $var) {
      if (!$var instanceof ActiveRecord) continue;

      $objectVars[$key] = [
        'id' => $var->id,
        'attributes' => $var->getOldAttributes(),
        'className' => $var::class,
      ];
    }

    return serialize($objectVars);
  }
  private function oldSerialize()
  {
    $objectVars = get_object_vars($this);
    foreach ($objectVars as $key => $var) {
      if (!$var instanceof ActiveRecord) continue;

      $objectVars[$key] = [
        'id' => $var->id,
        'className' => $var::class,
      ];
    }

    return serialize($objectVars);
  }

  /**
   * Constructs the object
   * @link http://php.net/manual/en/serializable.unserialize.php
   * @param string $serialized <p>
   * The string representation of the object.
   * </p>
   * @return void
   * @since 5.1.0
   */
  public function unserialize($serialized)
  {
    if(!$this->allowSerialization) return ;

    self::$isOldSerialize
      ? $this->oldUnSerialize($serialized)
      : $this->newUnSerialize($serialized)
      ;

  }

  private function oldUnSerialize($serialized)
  {
    $objectProperties = unserialize($serialized);
    if (!$objectProperties) return;
    foreach ($objectProperties as $propertyName => $propertyValue) {
      if (is_array($propertyValue)
        && isset($propertyValue['id'])
        && isset($propertyValue['className'])
      ) {
        $className = $propertyValue['className'];
        $id = $propertyValue['id'];
        $propertyValue = new $class;

        if ($propertyValue instanceof ActiveRecord) {
          $propertyValue = $className::findOne($id);
        }
      }

      $this->{$propertyName} = $propertyValue;
    }
  }

  private function newUnSerialize($serialized)
  {
    $objectProperties = unserialize($serialized);
    if (!$objectProperties) return;
    foreach ($objectProperties as $propertyName => $propertyValue) {
      if (is_array($propertyValue)
        && isset($propertyValue['attributes'])
        && isset($propertyValue['className'])
      ) {
        $className = $propertyValue['className'];
        $attributes = $propertyValue['attributes'];
        $propertyValue = new $class;

        if ($propertyValue instanceof ActiveRecord) {
          /** @var ActiveRecord $propertyValue */
          $propertyValue = new $class;
          $propertyValue::populateRecord($propertyValue, $attributes);
          $propertyValue->afterFind();
          $propertyValue->init();
        }
      }

      $this->{$propertyName} = $propertyValue;
    }
  }
}
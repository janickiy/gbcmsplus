<?php

namespace mcms\notifications\commands;

use mcms\pages\models\Faq;
use mcms\payments\components\events\UserBalanceInvoiceMulct;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\promo\models\Domain;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Operator;
use mcms\promo\models\Source;
use mcms\support\models\Support;
use mcms\support\models\SupportText;
use mcms\user\models\User;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Class DemoGeneratorController
 * @package mcms\notifications\commands
 */
class DemoGeneratorController extends Controller
{
  public $user;
  public $source;
  public $link;
  public $landing;
  public $operator;
  public $domain;
  public $landings;
  public $landingSet;
  public $landingSetItems;
  public $landingOperators;
  public $landingOperatorsByLandId;
  public $landingUnblockRequest;
  public $ticket;
  public $ticketMessage;
  public $userPayment;
  public $userBalanceInvoice;
  public $faq;

  public $userId;

  /**
   * @param $actionID
   * @return array
   */
  public function options($actionID)
  {
    return ['userId'];
  }

  /**
   * Консольная команда для создания тестовый браузерных уведомлений
   */
  public function actionIndex()
  {
    if (!$this->userId) {
      $this->stdout("Не указан параметр --userId\n", Console::FG_RED);
      return 0;
    }

    $this->user = $this->getUser();
    $this->source = $this->getSource();
    $this->link = $this->getLink();
    $this->landing = $this->getLanding();
    $this->landings = $this->getLandings();
    $this->landingSet = $this->getLandingSet();
    $this->landingSetItems = $this->getLandingSetItems();
    $this->operator = $this->getOperator();
    $this->landingOperators = $this->getLandingOperators();
    $this->landingOperatorsByLandId = $this->getLandingOperatorsByLandId();
    $this->domain = $this->getDomain();
    $this->landingUnblockRequest = $this->getLandingUnblockRequest();
    $this->ticket = $this->getSupport();
    $this->ticketMessage = $this->getSupportMessage();
    $this->userPayment = $this->getUserPayment();
    $this->userBalanceInvoice = $this->getUserBalanceInvoice();
    $this->faq = $this->getFaq();

    $events = [
      'mcms\user\components\events\EventRegistered' => [$this->user],
      'mcms\user\components\events\EventRegisteredHandActivation' => [$this->user],
      'mcms\user\components\events\EventReferralRegistered' => [$this->user],
      'mcms\promo\components\events\DisabledLandingsListReplaceFail' => [$this->landingOperatorsByLandId, $this->source, $this->user],
      'mcms\promo\components\events\DisabledLandingsListReplace' => [$this->landingOperatorsByLandId, $this->user],
      'mcms\promo\components\events\LandingListCreated' => [$this->landings],
      'mcms\promo\components\events\LandingCreated' => [$this->landing],
      'mcms\promo\components\events\LandingCreatedReseller' => [$this->landing],
      'mcms\promo\components\events\DomainBanned' => [$this->domain],
      'mcms\promo\components\events\SystemDomainBanned' => [$this->domain],
      'mcms\promo\components\events\DisabledLandingsReplaceFail' => [$this->landing, $this->source, $this->user, $this->landingOperators],
      'mcms\promo\components\events\DisabledLandingsListReseller' => [$this->landingOperatorsByLandId],
      'mcms\promo\components\events\DisabledLandingsReseller' => [$this->landing, $this->landingOperators],
      'mcms\promo\components\events\landing_sets\LandingsAddedToSet' => [$this->landingSet, [1, 2, 3]],
      'mcms\promo\components\events\landing_sets\LandingsRemovedFromSet' => [$this->landingSet, $this->landingSetItems],
      'mcms\promo\components\events\LandingListCreatedReseller' => [$this->landings],
      'mcms\promo\components\events\DomainAdded' => [$this->domain],
      'mcms\promo\components\events\SystemDomainAdded' => [$this->domain],
      'mcms\promo\components\events\LandingUnlocked' => [$this->landingUnblockRequest],
      'mcms\promo\components\events\SourceCreatedModeration' => [$this->source],
      'mcms\promo\components\events\SourceActivated' => [$this->source],
      'mcms\promo\components\events\LinkCreatedModeration' => [$this->link],
      'mcms\promo\components\events\LinkCreated' => [$this->link],
      'mcms\promo\components\events\LinkRejected' => [$this->link],
      'mcms\promo\components\events\DisabledLandingsReplace' => [$this->landing, $this->user, $this->landingOperators],
      'mcms\promo\components\events\SourceCreated' => [$this->source],
      'mcms\promo\components\events\SourceRejected' => [$this->source],
      'mcms\promo\components\events\LinkActivated' => [$this->link],
      'mcms\promo\components\events\LandingUnblockRequestCreated' => [$this->landingUnblockRequest],
      'mcms\promo\components\events\LandingDisabled' => [$this->landingUnblockRequest],
      'mcms\support\components\events\EventAdminClosed' => [$this->ticket],
      'mcms\support\components\events\EventAdminCreated' => [$this->ticket],
      'mcms\support\components\events\EventCreated' => [$this->ticket],
      'mcms\support\components\events\EventMessageReceived' => [$this->ticket, $this->ticketMessage, $this->user],
      'mcms\support\components\events\EventMessageSend' => [$this->ticket, $this->ticketMessage, $this->user],
      'mcms\payments\components\events\RegularPaymentCreated' => [$this->userPayment],
      'mcms\payments\components\events\PaymentStatusUpdated' => [$this->userPayment],
      'mcms\payments\components\events\UserBalanceInvoiceMulct' => [$this->userBalanceInvoice],
      'mcms\payments\components\events\EarlyPaymentCreated' => [$this->userPayment],
      'mcms\payments\components\events\UserBalanceInvoiceCompensation' => [$this->userBalanceInvoice],
      'mcms\payments\components\events\EarlyPaymentAdminCreated' => [$this->userPayment],
      'mcms\pages\components\events\FaqUpdateEvent' => [$this->faq],
      'mcms\notifications\components\events\TelegramAutoUnsubscribeEvent' => [$this->user],
    ];


    foreach ($events as $event => $params) {
      $reflection = new \ReflectionClass($event);
      if (!in_array(null, $params)) {
        $this->stdout((new $event)->getEventName() . ' ' . $event ."\n", Console::FG_GREEN);
        $eventInstance = $reflection->newInstanceArgs($params);
        $eventInstance->trigger();
      }

    }
  }

  /**
   * Возвращает модель партнера
   * @return User
   */
  public function getUser()
  {
    return User::find()->where(['id' => $this->userId])->one();
  }

  /**
   * @return Source
   */
  public function getSource()
  {
    return Source::findOne(['user_id' => $this->userId, 'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
  }

  /**
   * @return Source
   */
  public function getLink()
  {
    return Source::findOne(['user_id' => $this->userId, 'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
  }

  /**
   * @return Landing
   */
  public function getLanding()
  {
    return Landing::findOne(['status' => Landing::STATUS_ACTIVE]);
  }

  /**
   * @return Landing[]
   */
  public function getLandings()
  {
    return Landing::find()->where(['status' => Landing::STATUS_ACTIVE])->limit(3)->all();
  }

  /**
   * @return Operator
   */
  public function getOperator()
  {
    return Operator::findOne(['status' => Operator::STATUS_ACTIVE]);
  }

  /**
   * @return array
   */
  public function getLandingOperatorsByLandId()
  {
    $landingOperators = LandingOperator::find()->limit(5)->all();
    $return = [];
    foreach ($landingOperators as $landingOperator) {
      $return[$landingOperator->landing_id][] = $landingOperator;
    }
    return $return;
  }

  /**
   * @return LandingOperator[]
   */
  public function getLandingOperators()
  {
    return LandingOperator::find()->limit(5)->all();
  }

  /**
   * @return Domain
   */
  public function getDomain()
  {
    return Domain::findOne(['status' => Domain::STATUS_ACTIVE]);
  }

  /**
   * @return LandingSet
   */
  public function getLandingSet()
  {
    return LandingSet::findOne([]);
  }

  /**
   * @return LandingSetItem[]
   */
  public function getLandingSetItems()
  {
    return LandingSetItem::find()->limit(5)->all();
  }


  /**
   * @return LandingUnblockRequest
   */
  public function getLandingUnblockRequest()
  {
    $model = LandingUnblockRequest::findOne(['user_id' => $this->userId]);
    return $model;
  }

  /**
   * @return Support
   */
  public function getSupport()
  {
    return Support::findOne(['created_by' => $this->userId]);
  }

  /**
   * @return SupportText
   */
  public function getSupportMessage()
  {
    return SupportText::findOne(['from_user_id' => $this->userId]);
  }

  /**
   * @return UserPayment
   */
  public function getUserPayment()
  {
    return UserPayment::findOne(['user_id' => $this->userId]);
  }

  /**
   * @return UserBalanceInvoice
   */
  public function getUserBalanceInvoice()
  {
    return UserBalanceInvoice::findOne(['user_id' => $this->userId]);
  }

  /**
   * @return Faq
   */
  public function getFaq()
  {
    $model = Faq::findOne([]);
    if (!$model) {
      $model = new Faq();
      $model->visible = 1;
    }
    return $model;
  }

}
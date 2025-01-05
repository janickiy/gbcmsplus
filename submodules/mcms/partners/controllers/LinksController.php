<?php

namespace mcms\partners\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\partners\models\TestPostbackUrlForm;
use mcms\promo\components\events\LinkActivated;
use mcms\promo\components\events\LinkRejected;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\promo\models\UserPromoSetting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Cookie;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\partners\models\LinkForm;
use mcms\partners\models\LinkStep1Form;
use mcms\partners\models\LinkStep2Form;
use mcms\partners\models\LinkStep3Form;
use mcms\partners\models\LinkFormData;
use yii\widgets\ActiveForm;

/**
 * Class LinksController
 * @package mcms\partners\controllers
 */
class LinksController extends BaseController
{
  public $controllerTitle;

  const SOURCE_TYPE_LINK = 2;
  const STEP_THREE = 3;
  const STATUS_ACTIVE = 1;

  const COOKIE_LANDING_SHOW_TYPE = 'landingView';
  const COOKIE_IP_LIST_FORMAT = 'ipListFormat';
  const COOKIE_IP_LIST_GROUP = 'ipListGroup';
  const COOKIE_LIFETIME = 86400;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    if (!Yii::$app->user->identity) return parent::beforeAction($action);
    if (!Yii::$app->user->identity->canViewPromo()) {
      $this->flashFail('app.common.access_denied');
      $redirectUrl = Yii::$app->getModule('users')->getUrlCabinet();
      $this->redirect($redirectUrl);
      return false;
    }
    $this->theme = 'basic';

    $this->menu = [
      [
        'label' => Yii::_t('main.links'),
        'active' => $this->action->controller->id == 'links',
        'url' => '/partners/links/index/',
      ],
      [
        'label' => Yii::_t('domains.parking'),
        'url' => '/partners/domains/index/',
      ],
      [
        'label' => Yii::_t('partners.profile.postbackSettings'),
        'url' => '/partners/profile/postback-settings/',
      ],
    ];

    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('main.links');

    return parent::beforeAction($action);
  }

  /**
   * @param string $id the ID of this controller.
   * @param \yii\base\Module $module the module that this controller belongs to.
   * @param array $config name-value pairs that will be used to initialize the object properties.
   */
  public function __construct($id, $module, $config = [])
  {
    parent::__construct($id, $module, $config);
    $this->controllerTitle = Yii::_t('partners.main.promo');
  }

  public function actionIndex($choose = null)
  {
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);
    $sourcesApiObject = $linkFormData->getModulePromo()->api('sources', [
      'conditions' => array_merge(Yii::$app->getRequest()->post(), [
        'user_id' => Yii::$app->user->id,
        'source_type' => self::SOURCE_TYPE_LINK,
        'hideInactive' => true,
        'hideDeclined' => false,
      ]),
      'addSmartLink' => true,
      'sort' => ['defaultOrder' => ['created_at' => SORT_DESC], 'attributes' => ['created_at', 'id', 'name']]
    ]);
    $sourcesDataProvider = $sourcesApiObject->getResult();
    $searchModel = $sourcesApiObject->getSearchModel();

    $userApi = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id]);

    if ($choose && empty($linkFormData->streams)) {
      $linkFormData->getModulePromo()->api('streamCreate', [
        'name' => $this->module->getDefaultStream(),
        'userId' => Yii::$app->user->id,
      ])->getResult();
    }

    return $this->render(
      'index',
      [
        'sourcesDataProvider' => $sourcesDataProvider,
        'typeWebmaster' => $userApi->getWebmasterType(),
        'streams' => $linkFormData->streams,
        'domains' => $linkFormData->domainsItems,
        'searchModel' => $searchModel,
      ]
    );
  }

  public function actionAdd()
  {
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);

    /** @var \mcms\promo\models\Source $link */
    $link = null;
    if (Yii::$app->request->get('id')) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => Yii::$app->request->get('id'),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if ($link) {
        if ($link->isBlocked()) {
          $this->flashFail('links.is_blocked' );
          return $this->redirect(['index']);
        }
        if ($link->stream->isDisabled()) {
          $this->flashFail('links.stream_is_disabled' );
        }
      }
    }

    $showTypeCookie = Yii::$app->request->cookies->get(self::COOKIE_LANDING_SHOW_TYPE);
    $showType = $showTypeCookie ? $showTypeCookie->value : 'column';

    /** @var Stream $streamModel */
    $streamModel = $linkFormData->modulePromo->api('streams')->getModel([
      'user_id' => Yii::$app->user->id,
      'status' => 1
    ]);

    if($streamModel->load(Yii::$app->request->post())) {
      if(!$streamModel->validate()) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($streamModel);
      }
      return true;
    }

    return $this->render(
      'add',
      [
        'streamModel' => $streamModel,
        'showType' => $showType,
        'link' => $link,
        'countryPayTypes' => $linkFormData->countryPayTypes,
        'operatorPayTypes' => $linkFormData->operatorPayTypes,
        'countryOfferCategories' => $linkFormData->countryOfferCategories,
        'operatorOfferCategories' => $linkFormData->operatorOfferCategories,
        'testPostbackUrlForm' => new TestPostbackUrlForm(),
      ]
    );
  }

  public function actionAddStep1()
  {
    $linkStep1Form = new LinkStep1Form([
      'isNewStream' => 0,
    ]);
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);
    $link = null;
    if (Yii::$app->request->get('id')) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => Yii::$app->request->get('id'),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if ($link) {
        $linkStep1Form->setAttributes($link->attributes, false);
      }
    }

    return $this->renderAjax('_link_add_step_1', [
      'linkStep1Form' => $linkStep1Form,
      'streams' => $linkFormData->streams,
      'domains' => $linkFormData->domains,
      'domainsItems' => $linkFormData->domainsGroupedItems,
      'isSystemKeySystem' => $linkFormData->isSystemKeySystem,
      'isSystemKeyParked' => $linkFormData->isSystemKeyParked,
    ]);
  }

  public function actionAddStep2()
  {
    $linkStep2Form = new LinkStep2Form();
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);
    $link = null;
    $landingsSelectedCount = [];
    $activeOperator = $linkFormData->activeOperator;
    if (Yii::$app->request->get('id')) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => Yii::$app->request->get('id'),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if ($link) {
        $linkStep2Form->setAttributes($link->attributes, false);
        $activeOperator = $link->getActiveOperator() ? : $activeOperator;
        $landingsSelectedCount = $link->getSelectedCount();
      }
    }
    $showTypeCookie = Yii::$app->request->cookies->get(self::COOKIE_LANDING_SHOW_TYPE);
    $showType = $showTypeCookie ? $showTypeCookie->value : 'column';
    if (!empty($activeOperator)) {
      /** @var ActiveDataProvider $landingsProvider */
      $landingsProvider = $linkFormData->modulePromo->api('landingOperators', [
        'conditions' => [
          'operator_id' => $activeOperator->id,
          'landing_status' => self::STATUS_ACTIVE,
        ],
        'isOrderLandingsDirectionDesc' => true,
      ])->getResult();
      $landingsProvider->setPagination(false);
      $landings = $landingsProvider->getModels();
    } else {
      $landings = [];
    }
    $landingPayTypes = $linkFormData->getLandingPayTypes($landings);
    $currency = Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => Yii::$app->user->id])->getResult();

    return $this->renderAjax('_link_add_step_2', [
      'linkStep2Form' => $linkStep2Form,
      'payTypes' => $linkFormData->payTypes,
      'countries' => $linkFormData->countries,
      'countriesOperatorsActiveLandingsCount' => $linkFormData->getCountriesOperatorsActiveLandingsCount(),
      'activeOperator' => $activeOperator,
      'landingsSelectedCount' => $landingsSelectedCount,
      'showType' => $showType,
      'landingCategories' => $linkFormData->landingCategories,
      'offerCategories' => $linkFormData->offerCategories,
      'landings' => [], //$landings, // uncomment if problem
      'landingPayTypes' => $landingPayTypes,
      'rebillValue' => $linkFormData->rebillValue,
      'buyoutValue' => $linkFormData->buyoutValue,
      'accessByRequestValue' => $linkFormData->accessByRequestValue,
      'unblockedRequestStatusModerationValue' => $linkFormData->unblockedRequestStatusModerationValue,
      'unblockedRequestStatusUnlockedValue' => $linkFormData->unblockedRequestStatusUnlockedValue,
      'link' => $link,
      'currency' => $currency,
    ]);
  }

  public function actionAddStep3()
  {
    $linkStep3Form = new LinkStep3Form();
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);
    $link = null;
    if (Yii::$app->request->get('id')) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => Yii::$app->request->get('id'),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if ($link) {
        $linkStep3Form->setAttributes($link->attributes, false);
      }
    }
    list($landingsHasRevshare, $landingsHasCPA) = $this->landindsHasRevshareCPA($link);
    $linkFormData->getModulePromo();
    $ipListFormatCookie = Yii::$app->request->cookies->get(self::COOKIE_IP_LIST_FORMAT);
    $ipListFormat = $ipListFormatCookie ? $ipListFormatCookie->value : $linkFormData->ipFormatRange;
    $ipListGroupCookie = Yii::$app->request->cookies->get(self::COOKIE_IP_LIST_GROUP);
    $ipListGroup = $ipListGroupCookie ? $ipListGroupCookie->value : false;

    return $this->renderAjax('_link_add_step_3', [
      'linkStep3Form' => $linkStep3Form,
      'link' => $link,
      'trafficbackTypes' => $linkFormData->trafficbackTypes,
      'trafficbackTypeStaticValue' => $linkFormData->trafficbackTypeStaticValue,
      'trafficbackTypeDynamicValue' => $linkFormData->trafficbackTypeDynamicValue,
      'adsNetworks' => $linkFormData->adsNetworks,
      'adsNetworksItems' => $linkFormData->adsNetworksItems,
      'landingsHasCPA' => $landingsHasCPA,
      'landingsHasRevshare' => $landingsHasRevshare,
      'globalPostbackUrl' => UserPromoSetting::getGlobalPostbackUrl(),
      'globalComplainsPostbackUrl' => UserPromoSetting::getGlobalComplainsPostbackUrl(),
      'ipListFormat' => $ipListFormat,
      'ipListGroup' => $ipListGroup,
      'ipFormatRange' => $linkFormData->ipFormatRange,
      'ipFormatCidr' => $linkFormData->ipFormatCidr,
    ]);
  }

  private function landindsHasRevshareCPA($source)
  {
    /** @var \mcms\promo\models\Source $source */
    return $source !== null
      ? $source->landingsHasRevshareCPA()
      : [false, false]
      ;
  }

  public function actionLandingList()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);

    $link = null;
    if ($linkId = Yii::$app->request->post('linkId')) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => $linkId,
        'user_id' => Yii::$app->user->id,
      ])->getResult();
    }

    $operatorId = Yii::$app->request->post('operatorId');
    if ($operatorId !== null) {
      /** @var ActiveDataProvider $landingsProvider */
      $landingsProvider = $linkFormData->modulePromo->api('landingOperators', [
        'conditions' => [
          'operator_id' => $operatorId,
          'landing_status' => self::STATUS_ACTIVE,
        ],
        'isOrderLandingsDirectionDesc' => true,
        'isOrderLandingsOpenFirst' => true,
      ])->getResult();
      $landingsProvider->setPagination(false);
      $landings = $landingsProvider->getModels();
    } else {
      $landings = [];
    }
    $landingPayTypes = $linkFormData->getLandingPayTypes($landings);

    $currency = Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => Yii::$app->user->id])->getResult();

    return $this->renderPartial('landings_list', [
      'landings' => $landings,
      'landingPayTypes' => $landingPayTypes,
      'rebillValue' => $linkFormData->rebillValue,
      'buyoutValue' => $linkFormData->buyoutValue,
      'accessByRequestValue' => $linkFormData->accessByRequestValue,
      'unblockedRequestStatusModerationValue' => $linkFormData->unblockedRequestStatusModerationValue,
      'unblockedRequestStatusUnlockedValue' => $linkFormData->unblockedRequestStatusUnlockedValue,
      'link' => $link,
      'currency' => $currency,
    ]);
  }

  public function actionFormHandle()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $postData = Yii::$app->request->post();
    $stepNumber = ArrayHelper::getValue($postData, 'stepNumber');
    if ($stepNumber === null) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $save = Yii::$app->request->post("submit") == true;
    $linkForm = new LinkForm();
    $linkStep1Form = new LinkStep1Form();
    $linkStep2Form = new LinkStep2Form();
    $linkStep3Form = new LinkStep3Form();

    if ($save) {
      $formName = $linkForm->formName();
      $attributes = array_keys($linkForm->attributes);
      $postData[$formName] = array_merge(
        ArrayHelper::getValue($postData, $linkStep1Form->formName(), []),
        ArrayHelper::getValue($postData, $linkStep2Form->formName(), []),
        ArrayHelper::getValue($postData, $linkStep3Form->formName(), [])
      );
    } else {
      switch ($stepNumber) {
        case 1:
          $formName = $linkStep1Form->formName();
          $attributes = array_keys($linkStep1Form->attributes);
          break;
        case 2:
          $formName = $linkStep2Form->formName();
          $attributes = array_keys($linkStep2Form->attributes);
          break;
        case 3:
        case 4:
          $formName = $linkStep3Form->formName();
          $attributes = array_keys($linkStep3Form->attributes);
          break;
      }
    }
    $result = Yii::$app->getModule('promo')->api('linkCreate', [
      'postData' => $postData,
      'userId' => Yii::$app->user->id,
      'save' => $save,
      'formName' => $formName,
      'attributes' => $attributes,
    ])->getResult();

    if ($save && $stepNumber == self::STEP_THREE && $result['success']) {
      $this->flashSuccess('links.link_saved');

      return $this->redirect(['index']);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;
    return $result;
  }

  public function actionLandingRequest()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $result = Yii::$app->getModule('promo')->api('landingUnblockRequestCreate', [
      'postData' => Yii::$app->request->post(),
      'userId' => Yii::$app->user->id,
    ])->getResult();

    Yii::$app->response->format = Response::FORMAT_JSON;

    return $result;
  }

  public function actionGetLink()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $link = Yii::$app->getModule('promo')->api('getSource', [
      'source_id' => Yii::$app->request->post('source'),
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    /** @var \mcms\statistic\Module $module */
    $module = Yii::$app->getModule('statistic');

    $countPostbacks = $module->api('postbacks', ['conditions' => [
      'source_id' => $link->id,
      'complainTypes' => $module->getPartnerVisibleComplainsTypes(),
    ]])->getCount();

    if(!$link) return AjaxResponse::error(Yii::_t('links.error-not_found'));
    if ($link->isBlocked()) return AjaxResponse::error(Yii::_t('links.error-is_blocked'));

    return AjaxResponse::success(['form' => $this->renderPartial('_link', [
      'link' => $link,
      'countPostbacks' => $countPostbacks
    ])]);
  }

  public function actionDelete($id)
  {
    Yii::$app->getModule('promo')->api(
      'sourceDelete',
      [
        'user_id' => Yii::$app->user->id,
        'source_id' => $id,
      ]
    )->getResult();

    return $this->redirect(['index']);
  }

  public function actionIpList()
  {
    $sourceId = Yii::$app->request->post("id");
    $format = Yii::$app->request->post("format");
    $group = Yii::$app->request->post("group");

    Yii::$app->response->cookies->add(new Cookie([
      'name' => self::COOKIE_IP_LIST_FORMAT,
      'value' => $format,
      'expire' => time() + self::COOKIE_LIFETIME,
    ]));
    Yii::$app->response->cookies->add(new Cookie([
      'name' => self::COOKIE_IP_LIST_GROUP,
      'value' => $group,
      'expire' => time() + self::COOKIE_LIFETIME,
    ]));

    $promoModule = Yii::$app->getModule('promo');
    /* @var $source \mcms\promo\models\Source */
    $source = $promoModule->api('getSource', [
      'source_id' => $sourceId,
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    if (!$source) {
      throw new NotFoundHttpException('Не удалось получить источник. Скорее всего вы залогинены под другим пользователем');
    }

    return $source->getIPs($format, $group);
  }

  /**
   * @param integer $landingId
   * @param integer $operatorId
   * @param integer|null $linkId
   * @param bool $selected
   * @return string
   */
  public function actionLandingModal($landingId, $operatorId, $linkId = null, $selected = false)
  {
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);

    /* @var $landing \mcms\promo\models\LandingOperator */
    $landing = $linkFormData->modulePromo->api('landingOperatorById', [
      'landingId' => $landingId,
      'operatorId' => $operatorId,
    ])->getResult();

    $link = null;
    if ($linkId) {
      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => $linkId,
        'user_id' => Yii::$app->user->id,
      ])->getResult();
    }

    $rebillValue = $linkFormData->rebillValue;
    $buyoutValue = $linkFormData->buyoutValue;
    $accessByRequestValue = $linkFormData->accessByRequestValue;

    $unblockedRequestStatusModerationValue = $linkFormData->unblockedRequestStatusModerationValue;
    $unblockedRequestStatusUnlockedValue = $linkFormData->unblockedRequestStatusUnlockedValue;

    $currency = Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => Yii::$app->user->id])->getResult();
    $landingUnblockRequest = Yii::$app->getModule('promo')->api('landingUnblockRequest', [
      'landing_id' => $landingId,
    ])->getResult();

    $modalWindow = '_landing_modal_active';
    if ($landing->landing->isRequestStatusNotUnlocked()) {
      $modalWindow = '_landing_modal_lock';
      if ($landing->landing->isRequestStatusModeration()) $modalWindow = '_landing_modal_wait';
      if ($landing->landing->isRequestStatusBlocked())  $modalWindow = '_landing_modal_blocked';
    }

    if ($selected) {
      $modalWindow = '_landing_modal_selected';
      if ($landing->landing->isRequestStatusModeration()) $modalWindow = '_landing_modal_wait_selected';
      if ($landing->landing->isRequestStatusNotUnlocked()) $modalWindow = '_landing_modal_blocked';
    }

    $prices = LandingOperatorPrices::create($landing, Yii::$app->user->id);

    return $this->renderAjax('_landing_modal', compact(
      'modalWindow',
      'link',
      'landing',
      'operatorId',
      'rebillValue',
      'buyoutValue',
      'accessByRequestValue',
      'unblockedRequestStatusModerationValue',
      'unblockedRequestStatusUnlockedValue',
      'selected',
      'currency',
      'landingUnblockRequest',
      'prices'
    ));
  }

  public function actionRequestModal($landingId, $operatorId)
  {
    $modulePromo = Yii::$app->getModule('promo');

    /* @var $source \mcms\promo\models\Landing */
    $landing = $modulePromo->api('landingById', [
      'landingId' => $landingId,
    ])->getResult();

    $profitTypes = $modulePromo->api('profitTypes')->getResult();

    /** @var ActiveDataProvider $trafficTypesProvider */
    $trafficTypesProvider = $modulePromo->api('trafficTypes')
      ->setResultTypeDataProvider()
      ->getResult();
    $trafficTypesProvider->setPagination(false);
    $trafficTypes = $trafficTypesProvider->getModels();

    return $this->renderAjax('_landing_modal_request', compact('landing', 'operatorId', 'trafficTypes', 'profitTypes'));
  }

  public function actionChangeLandingsView($type) {
    Yii::$app->response->cookies->add(new Cookie([
      'name' => self::COOKIE_LANDING_SHOW_TYPE,
      'value' => $type,
      'expire' => time() + self::COOKIE_LIFETIME
    ]));

    return true;
  }

  public function actionTestPostbackUrl()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }
    $testPostbackUrlForm = new TestPostbackUrlForm();

    if($testPostbackUrlForm->load(Yii::$app->request->post()) && $testPostbackUrlForm->validate()) {
      return AjaxResponse::success($testPostbackUrlForm->getResult());
    }

    // костыльно делаем success, т.к. error на фронте в этом месте не обработан.
    return AjaxResponse::success(strip_tags(Html::errorSummary($testPostbackUrlForm, ['header' => false])));
  }

  public function actionLandingStatuses()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $linkId = Yii::$app->request->post("id");

    $promoModule = Yii::$app->getModule('promo');
    /* @var $source \mcms\promo\models\Source */
    $link = $promoModule->api('getSource', [
      'source_id' => $linkId,
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    return $this->renderAjax('_landing_statuses', ['link' => $link]);

  }

  /**
   * @param $id
   * @return Response
   */
  public function actionCopy($id)
  {
    /** @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    $result = $promoModule->api(
      'sourceCopy',
      ['source_id' => $id]
    )->getResult();

    $result ? $this->flashSuccess('main.operation_success') : $this->flashFail('main.operation_fail');

    return $this->redirect(['index']);
  }

  public function actionExportPostbacks($id)
  {
    /** @var Source $source */
    $source = Yii::$app->getModule('promo')->api('getSource', [
      'source_id' => $id,
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    if (!$source) {
      throw new ForbiddenHttpException();
    }

    /** @var \mcms\statistic\Module $module */
    $module = Yii::$app->getModule('statistic');

    $dataProvider = $module->api('postbacks', ['conditions' => [
      'source_id' => $id,
      'complainTypes' => $module->getPartnerVisibleComplainsTypes(),
      ],
      'pagination' => [
        'pageSize' => $module->getPartnersExportPostbackLimit(),
      ]
    ])->getResult();

    return $this->render('_export', [
      'dataProvider' => $dataProvider,
      'exportFileName' => 'postbacks_' . $source->hash,
      'exportRequestParam' => 'exportPostback',
    ]);
  }
}

<?php

namespace mcms\partners\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\MultiLangSort;
use mcms\common\multilang\LangAttribute;
use mcms\promo\components\events\SourceActivated;
use mcms\promo\components\events\SourceRejected;
use mcms\promo\models\Landing;
use Yii;
use yii\web\Response;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\common\web\AjaxResponse;
use mcms\partners\models\SourceForm;

/**
 * Class SourcesController
 * @package mcms\partners\controllers
 */
class SourcesController extends BaseController
{
  public $controllerTitle;

  const SOURCE_TYPE_WEBMASTER_SITE = 1;
  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {

    $this->theme = 'basic';

    $this->menu = [
      [
        'label' => Yii::_t('main.sources'),
        'active' => $this->action->controller->id == 'sources',
        'url' => '/partners/sources/index/',
      ],
//      [
//        'label' => Yii::_t('main.links'),
//        'url' => '/partners/links/index/',
//      ],
      [
        'label' => Yii::_t('domains.parking'),
        'url' => '/partners/domains/index/',
      ],
      [
        'label' => Yii::_t('partners.profile.postbackSettings'),
        'url' => '/partners/profile/postback-settings/',
      ],
    ];

    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('main.sources');

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
    $modulePromo = Yii::$app->getModule('promo');

    $sourcesApiObject = $modulePromo->api('sources', [
      'conditions' => array_merge(Yii::$app->getRequest()->post(), [
        'user_id' => Yii::$app->user->id,
        'source_type' => self::SOURCE_TYPE_WEBMASTER_SITE,
        'hideInactive' => true,
        'hideDeclined' => false
      ]),
      'sort' => ['defaultOrder' => ['created_at' => SORT_DESC], 'attributes' => []]
    ]);

    $sourcesDataProvider = $sourcesApiObject->getResult();
    $searchModel = $sourcesApiObject->getSearchModel();

    $adsTypes = MultiLangSort::sort(ArrayHelper::map($modulePromo->api('adsTypes')->getResult(), 'id', 'name'));
    $profitTypes = $modulePromo->api('profitTypes')->getResult();
    $categories = MultiLangSort::sort($modulePromo->api('landingCategories')->getMap());
    $statuses = $modulePromo->api('sourceStatuses')->getResult();

    $userApi = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id]);

    return $this->render(
      'index',
      [
        'sourcesDataProvider' => $sourcesDataProvider,
        'adsTypes' => $adsTypes,
        'profitTypes' => $profitTypes,
        'categories' => $categories,
        'statuses' => $statuses,
        'typeArbitrary' => $userApi->getArbitraryType(),
        'searchModel' => $searchModel,
      ]
    );
  }

  public function actionAdd()
  {
    $modulePromo = Yii::$app->getModule('promo');

    $adsApi = $modulePromo->api('adsTypes');

    $adsTypes = $adsApi->getResult();
    $defaultAdsType = $adsApi->getDefault();

    $profitTypes = $modulePromo->api('profitTypes')->getResult();

    $domains = $modulePromo->api('domains', [
      'conditions' => [
        'is_system' => 1,
      ],
    ])->getResult();

    $domain = !empty($domains) ? array_values($domains)[0] : null;

    $sourceForm = new SourceForm([
      'domain_id' => $domain,
      'ads_type' => $defaultAdsType ? $defaultAdsType->id : null
    ]);

    $sourceForm->url = 'http://';

    return $this->render(
      'add',
      [
        'adsTypes' => $adsTypes,
        'profitTypes' => $profitTypes,
        'sourceForm' => $sourceForm,
        'domain' => $domain,
      ]
    );
  }

  public function actionFormHandle()
  {
    $sourceForm = new SourceForm();

    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $sourceForm->stepNumber = Yii::$app->request->post("stepNumber");

    if (!$sourceForm->stepNumber) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $save = Yii::$app->request->post("submit") == true;

    $result = Yii::$app->getModule('promo')->api('sourceCreate', [
      'postData' => Yii::$app->request->post(),
      'userId' => Yii::$app->user->id,
      'save' => $save,
      'formName' => $sourceForm->formName(),
      'attributes' => $sourceForm->getStepAttributes()
    ])->getResult();

    if ($save && $sourceForm->stepNumber == 3 && $result['success']) {
      $this->flashSuccess('sources.source_saved');
      return $this->redirect(['index']);
    }
    Yii::$app->response->format = Response::FORMAT_JSON;

    return $result;
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

  public function actionCode()
  {
    $modulePromo = Yii::$app->getModule('promo');

    $source = $modulePromo->api('getSource', [
        'source_id' => Yii::$app->request->post('source'),
        'user_id' => Yii::$app->user->id,
    ])->getResult();

    if(!$source) return AjaxResponse::error(Yii::_t('sources.error-not_found'));
    if ($source->isBlocked()) return AjaxResponse::error(Yii::_t('sources.error-is_blocked'));

    $domains = $modulePromo->api('domains', [
      'conditions' => [
        'is_system' => 1,
      ],
    ])->getResult();

    return AjaxResponse::success(['form' => $this->renderPartial('code',[
      'hash' => $source->hash,
      'url' => $source->url,
      'domain' => !empty($domains) ? array_values($domains)[0] : null,
    ])]);
  }

  public function actionSettings()
  {
    if (
      Yii::$app->request->isAjax &&
      Yii::$app->request->post('source')
    ) {
      $modulePromo = Yii::$app->getModule('promo');

      $source = $modulePromo->api('getSource', [
        'user_id' => Yii::$app->user->id,
        'source_id' => Yii::$app->request->post('source'),
      ])->getResult();

      if(!$source) return AjaxResponse::error(Yii::_t('sources.error-not_found'));
      if ($source->isBlocked()) return AjaxResponse::error(Yii::_t('sources.error-is_blocked'));

      $adsTypes = $modulePromo->api('adsTypes')->getResult();
      $profitTypes = $modulePromo->api('profitTypes')->getResult();

      $countries = $modulePromo->api('countries', ['conditions' => ['onlyWithLandings' => true]])->getResult()->getModels();

      return AjaxResponse::success(['form' => $this->renderPartial(
        'settings',
        [
          'source' => $source,
          'adsTypes' => $adsTypes,
          'profitTypes' => $profitTypes,
          'countries' => $countries,
        ]
      )]);
    }
    return false;
  }

  public function actionEdit()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (
      Yii::$app->request->isAjax &&
      Yii::$app->request->post()
    ) {

      $result = Yii::$app->getModule('promo')->api('editSource', [
        'user_id' => Yii::$app->user->id,
        'source_id' => Yii::$app->request->post('source'),
        'adstype' => Yii::$app->request->post('adstype'),
        'default_profit_type' => Yii::$app->request->post('default_profit_type'),
        'filter_operators' => Yii::$app->request->post('operators'),
        'isOperators' => Yii::$app->request->get('isOperators')
      ])->getResult();

      return $result;
    }

    return false;
  }

}

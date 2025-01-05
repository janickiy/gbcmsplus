<?php

namespace mcms\partners\controllers;

use mcms\partners\components\widgets\CheckDomainWidget;
use mcms\promo\components\events\DomainBanned;
use mcms\promo\components\events\SystemDomainAdded;
use mcms\promo\components\events\SystemDomainBanned;
use Yii;
use yii\web\Response;
use mcms\common\helpers\ArrayHelper;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\common\web\AjaxResponse;
use mcms\partners\models\DomainForm;

/**
 * Class DomainsController
 * @package mcms\partners\controllers
 */
class DomainsController extends BaseController
{
  public $controllerTitle;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {

    $this->controllerTitle = Yii::_t('partners.main.domains');

    $this->theme = 'basic';

    $userApi = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id]);

    $userParams = $userApi->getResult();
    $partnerType = ArrayHelper::getValue($userParams, 'partner_type');

    $this->menu = [
      ($partnerType == $userApi->getArbitraryType()
        ? [
          'label' => Yii::_t('main.links'),
          'url' => '/partners/links/index/',
        ]
        : [
          'label' => Yii::_t('main.sources'),
          'url' => '/partners/sources/index/',
        ]),
      [
        'label' => Yii::_t('domains.parking'),
        'active' => $this->action->controller->id == 'domains',
        'url' => '/partners/domains/index/',
      ],
      [
        'label' => Yii::_t('partners.profile.postbackSettings'),
        'url' => '/partners/profile/postback-settings/',
      ],
    ];

    return parent::beforeAction($action);
  }


  public function actionIndex()
  {
    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('main.parking');

    $apiObject = Yii::$app->getModule('promo')->api('domains', [
      'conditions' => [
        'user_id' => Yii::$app->user->id,
        'system' => true,
        'onlyPartnerVisible' => true,
      ],
      'sort' => ['defaultOrder' => ['created_at' => SORT_DESC], 'attributes' => []]
    ]);

    $sourcesDataProvider = $apiObject->setResultTypeDataProvider()->getResult();

    return $this->render(
      'index',
      [
        'sourcesDataProvider' => $sourcesDataProvider,
      ]
    );
  }

  public function actionAdd()
  {
    $domainForm = new DomainForm();

    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $save = Yii::$app->request->post('submit') == true;

    $result = Yii::$app->getModule('promo')->api('domainCreate', [
      'postData' => Yii::$app->request->post(),
      'userId' => Yii::$app->user->id,
      'save' => $save,
      'formName' => $domainForm->formName(),
    ])->getResult();

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($save && $result['success']) {
      return AjaxResponse::success([
        'id' => $result['id'],
        'url' => $result['url'],
      ]);
    }

    return $result;
  }

  /**
   * Проверка на припаркованность домена
   * @param $host
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  public function actionCheck($host)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return CheckDomainWidget::isHostParked($host);
  }
}

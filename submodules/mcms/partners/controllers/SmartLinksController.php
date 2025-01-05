<?php

namespace mcms\partners\controllers;

use mcms\partners\models\LinkFormData;
use mcms\partners\models\LinkStep3Form;
use mcms\partners\models\TestPostbackUrlForm;
use mcms\promo\models\SmartLink;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\common\controller\SiteBaseController;
use yii\web\NotFoundHttpException;

/**
 * Class SmartLinksController
 * @package mcms\partners\controllers
 */
class SmartLinksController extends SiteBaseController
{
  public $controllerTitle;

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
        'active' => true,
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
    $this->controllerTitle = Yii::_t('partners.main.promo');
    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('main.links');

    return parent::beforeAction($action);
  }

  public function actionUpdate($id)
  {
    $linkForm = new LinkStep3Form();
    $linkFormData = new LinkFormData(['userId' => Yii::$app->user->id]);


      $link = $linkFormData->modulePromo->api('getSource', [
        'source_id' => $id,
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if (!$link) {
        throw new NotFoundHttpException('Не удалось найти ссылку');
      }

    $linkForm->setAttributes($link->attributes, false);

    return $this->render('update', [
      'linkForm' => $linkForm,
      'link' => $link,
      'trafficbackTypes' => $linkFormData->trafficbackTypes,
      'trafficbackTypeStaticValue' => $linkFormData->trafficbackTypeStaticValue,
      'trafficbackTypeDynamicValue' => $linkFormData->trafficbackTypeDynamicValue,
      'adsNetworks' => $linkFormData->adsNetworks,
      'adsNetworksItems' => $linkFormData->adsNetworksItems,
      'globalPostbackUrl' => UserPromoSetting::getGlobalPostbackUrl(),
      'globalComplainsPostbackUrl' => UserPromoSetting::getGlobalComplainsPostbackUrl(),
      'testPostbackUrlForm' => new TestPostbackUrlForm(),
      'landingCategories' => $linkFormData->modulePromo->api('cachedLandingCategories')->getResult()
    ]);
  }

  public function actionActivate()
  {
    $model = SmartLink::createForUser(Yii::$app->user->id);
    $this->redirect(['update', 'id' => $model->id]);
  }
}

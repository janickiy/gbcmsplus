<?php

namespace mcms\partners\controllers;

use mcms\common\controller\SiteBaseController;
use mcms\pages\components\events\FaqUpdateEvent;
use Yii;

/**
 * Class DomainsController
 * @package mcms\partners\controllers
 */
class FaqController extends SiteBaseController
{
  public $controllerTitle;
  public $theme = 'basic';
  public $categoryNoNav = true;

  public function actionIndex()
  {
    $this->controllerTitle = Yii::_t('partners.main.help');
    Yii::beginProfile('api.pages.GetCachedVisibleFaqList.getResult', self::class);
    $faqList = Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->getResult();
    Yii::endProfile('api.pages.GetCachedVisibleFaqList.getResult', self::class);
    return $this->render('index', [
      'faqList' => $faqList
    ]);
  }
}

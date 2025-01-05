<?php

namespace mcms\partners\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\partners\components\helpers\PromoHelper;
use mcms\partners\Module;
use Yii;
use mcms\common\controller\SiteBaseController as BaseController;

/**
 * Class PromoController
 * @package mcms\partners\controllers
 */
class PromoController extends BaseController
{

  public $controllerTitle;

  /** @var bool для лейаута */
  public $categoryNoNav = true;
  /** @var bool для лейаута */
  public $hideBgfBlock = true;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->theme = 'basic';
    return parent::beforeAction($action);
  }


  /**
   * Если у партнера нет настройки partner_type, то рендерим страницу выбора
   * При выборе ссылка ведёт на этот же экшен с параметром $choose, который равен выбранному типу партнера.
   * после этого мы сохраняем выбор пользователя в БД и редиректим на нужный экшен.
   *
   * Если у партнера уже есть в БД запись с типом, то сразу редиректим на нужную странцу.
   *
   * @param null $choose
   * @return string|\yii\web\Response
   */
  public function actionIndex($choose = null)
  {
    $this->controllerTitle = Yii::_t('main.promo');

    $userApi = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id]);

    if (!is_null($choose)) return $this->updatePartnerType($userApi->getArbitraryType(), $choose);

    $userParams = $userApi->getResult();

    $partnerType = ArrayHelper::getValue($userParams, 'partner_type');

    if ($partnerType == $userApi->getArbitraryType()) return $this->redirectArbitrary();

    if ($partnerType == $userApi->getWebmasterType()) return $this->redirectWebmaster();

    return $this->render(
      'index',
      [
        'typeArbitrary' => $userApi->getArbitraryType(),
        'typeWebmaster' => $userApi->getWebmasterType(),
      ]
    );
  }

  protected function updatePartnerType($arbitraryType, $choose)
  {
    PromoHelper::updatePartnerType($choose);
    return $choose == $arbitraryType ? $this->redirectArbitrary() : $this->redirectWebmaster();
  }

  protected function redirectArbitrary()
  {
    return $this->redirect(Module::$arbitraryUrl);
  }

  protected function redirectWebmaster()
  {
    return $this->redirect(Module::$webmasterUrl);
  }

}

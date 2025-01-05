<?php

namespace mcms\partners\controllers;

use Yii;
use mcms\news\components\api\NewsList;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\user\components\annotations\Role as RoleAnnotation;
use mcms\user\components\annotations\Description;

/**
 * Class SupportController
 * @package mcms\partners\controllers
 * @RoleAnnotation({"root", "admin", "partner"})
 */
class NewsController extends BaseController
{
  public $controllerTitle;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
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
    $this->controllerTitle = Yii::_t('partners.main.news');
  }

  /**
   * @Description("News list")
   * @return string
   */
  public function actionIndex()
  {
    $news = Yii::$app->getModule('news')->api('getNewsList')->getResult();

    return $this->render(NULL, [
      'news' => $news,
    ]);
  }

  /**
   * @Description("News view")
   * @return string
   */
  public function actionView($id)
  {
    $news = Yii::$app->getModule('news')->api('getOneNews', ['news_id' => $id])->getResult();

    return $this->render(NULL, [
      'news' => $news,
    ]);
  }


}

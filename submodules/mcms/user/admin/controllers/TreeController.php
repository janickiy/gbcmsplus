<?php

namespace mcms\user\admin\controllers;


use mcms\common\web\AjaxResponse;
use mcms\user\admin\components\AuthTree;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class TreeController
 * @package mcms\user\admin\controllers
 */
class TreeController extends Controller
{

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'assign' => ['POST'],
        ],
      ],
      'access' => [
        'class' => AccessControl::class,
        'rules' => [
          [
            'allow' => true,
            'roles' => ['UsersViewYiiAdmin'],
          ],
        ],
      ],
    ];
  }

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->view->title = Yii::_t('users.tree.menu');
    return parent::beforeAction($action);
  }

  /**
   * Дерево разрешений
   * @return string
   */
  public function actionIndex()
  {
    return $this->render('index');
  }

  /**
   * Назначение/снятие разрешения
   * @return array
   */
  public function actionAssign()
  {
    try {
      list($roleName, $itemName, $assign) = $this->getAssignPostParams();

      $tree = new AuthTree;
      $authManager = Yii::$app->authManager;
      $role = $authManager->getRole($roleName);
      $item = $authManager->getPermission($itemName) ?: $authManager->getRole($itemName);

      return AjaxResponse::set(
        boolval($assign) ? $this->assign($role, $item) : $this->revoke($role, $item),
        ['tree' => $tree->getTree()]
      );

    } catch (\Exception $e) {
      return AjaxResponse::error();
    }
  }

  /**
   * Дерево разрешений
   * @return array
   */
  public function actionGetTree()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return (new AuthTree)->getTree();
  }

  /**
   * @return array
   */
  protected function getAssignPostParams()
  {
    $request = Yii::$app->request;

    $assign = $request->post('assign');

    if (!$role = $request->post('role')) {
      throw new InvalidParamException('No role param passed');
    }
    if (!$item = $request->post('item')) {
      throw new InvalidParamException('No item param passed');
    }
    if (is_null($assign)) {
      throw new InvalidParamException('No assign param passed');
    }

    return [$role, $item, $assign];
  }

  /**
   * @param Item $parent
   * @param Item $child
   * @return bool
   */
  protected function assign(Item $parent, Item $child)
  {
    $authManager = Yii::$app->authManager;

    $assigned = $authManager->hasChild($parent, $child);

    if ($assigned) return true;

    return $authManager->addChild($parent, $child);
  }

  /**
   * @param Item $parent
   * @param Item $child
   * @return bool
   */
  private function revoke(Item $parent, Item $child)
  {
    $authManager = Yii::$app->authManager;

    $assigned = $authManager->hasChild($parent, $child);

    if (!$assigned) return true;

    return $authManager->removeChild($parent, $child);
  }
}
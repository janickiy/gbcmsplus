<?php

namespace mcms\payments\components\controllers;

use mcms\common\controller\AdminBaseController;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class BaseController extends AdminBaseController {
  
  private $userModule;
  
  public function pageNotFound()
  {
    $this->flashFail('app.common.page_not_found');
    if(Yii::$app->request->referrer) {
      return $this->redirect(Url::previous());
    }
    return $this->goHome();
  }

  /**
   * @param $id
   * @param bool $getRoles
   * @return \mcms\user\models\User
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  protected function getUser($id, $getRoles = false)
  {
    /** @var \mcms\user\models\User $user */
    if (!$user = ArrayHelper::getValue($this->getUserModule()->api('user')->search([['id' => $id]]), 0)) {
      return $this->pageNotFound();
    }

    if ($getRoles) {
      $user['roles'] = array_map(function($item) {
        return $item['name'];
      }, $this->getUserModule()->api('rolesByUserId', ['userId' => $id])->getResult());
    }
    return $user;
  }

  /**
   * @return \mcms\user\Module
   */
  protected function getUserModule()
  {
    if (!$this->userModule) {
      $this->userModule = Yii::$app->getModule('users');
    }
    return $this->userModule;
  }
}
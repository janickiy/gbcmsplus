<?php
namespace mcms\promo\controllers;

use mcms\common\actions\MassDeleteAction;
use mcms\common\actions\MassUpdateAction;
use mcms\common\actions\MassUpdateModalAction;
use mcms\common\behavior\MassUpdateControllerBehavior;
use mcms\common\helpers\ArrayHelper;
use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\components\PartnerProgramClean;
use mcms\promo\components\PartnerProgramSync;
use mcms\promo\models\PartnerProgram;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PartnerProgramItemMassModel;
use mcms\promo\models\search\PartnerProgramItemSearch;
use mcms\promo\models\search\PartnerProgramSearch;
use mcms\promo\models\UserPromoSetting;
use mcms\user\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Партнерские программы
 */
class PartnerProgramsController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    // Защита от CSRF
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
          'link-partner' => ['POST'],
          'unlink-partner' => ['POST'],
          'link-partner-editable' => ['POST'],
          'autosync-enable' => ['POST'],
          'autosync-disable' => ['POST'],
          'clean' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'mass-update' => [
        'class' => MassUpdateAction::class,
        'model' => new PartnerProgramItemMassModel(['model' => new PartnerProgramItem]),
      ],
      'mass-delete' => [
        'class' => MassDeleteAction::class,
        'model' => new PartnerProgramItem(),
      ],
    ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->view->title = Yii::_t(PartnerProgram::LANG_PREFIX . 'main');
    $this->setBreadcrumb($this->view->title, ['index'], false);

    return parent::beforeAction($action);
  }

  /**
   * Создание ПП
   */
  public function actionCreateModal()
  {
    $this->view->title = Yii::_t(PartnerProgram::LANG_PREFIX . 'create-program');

    $model = new PartnerProgram;
    $request = Yii::$app->request;

    // Форма ввода
    if (!$model->load($request->post())) {
      return $this->renderAjax('create', [
        'model' => $model,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    // Валидация
    if (!$request->post('submit')) {
      return ActiveForm::validate($model);
    }

    // Сохранение
    if ($model->insert()) {
      $this->flashSuccess('app.common.saved_successfully');
      return $this->redirect(['update', 'id' => $model->id]);
    }

    return AjaxResponse::error();
  }

  /**
   * Список
   */
  public function actionIndex()
  {
    $searchModel = new PartnerProgramSearch;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('list', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
  }

  /**
   * Обновление ПП.
   * Изменение параметров ПП, изменение условий и вывод партнеров
   * @param int $id
   * @return string
   */
  public function actionUpdate($id)
  {
    $this->view->title = Yii::_t(PartnerProgram::LANG_PREFIX . 'manage-program');
    $this->setBreadcrumb($this->view->title, [], false);

    $model = $this->findModel($id);
    $request = Yii::$app->request;
    $usersModule = Yii::$app->getModule('users');

    $userIds = $model->userIds;

    $itemsSearchModel = new PartnerProgramItemSearch;
    $itemsDataProvider = $itemsSearchModel->search($request->get());
    $itemsDataProvider->query->andWhere(['partner_program_id' => $model->id]);
    $itemsDataProvider->query->with(['operator', 'landing']);
    $itemsDataProvider->sort->sortParam .= $model->formName();

    /** @var \mcms\user\models\search\User $usersSearchModel */
    $usersSearchModel = $usersModule->api('user', ['condition' => [['id' => $userIds]]])->getSearchModel();
    $usersDataProvider = $usersSearchModel->search($request->queryParams);
    $usersDataProvider->query->andWhere(['id' => $userIds]);
    $usersDataProvider->sort->sortParam .= $usersSearchModel->formName();
    $usersDataProvider->pagination->pageParam .= $usersSearchModel->formName();

    // Страница редактирования
    if (!$model->load($request->post())) {
      return $this->render('update', [
        'model' => $model,
        'itemsSearchModel' => $itemsSearchModel,
        'itemsDataProvider' => $itemsDataProvider,
        'usersSearchModel' => $usersSearchModel,
        'usersDataProvider' => $usersDataProvider,
        'userModule' => $usersModule,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    // Валидация данных ПП
    if (!$request->post('submit')) {
      return ActiveForm::validate($model);
    }

    // Сохранение данных ПП
    return AjaxResponse::set($model->update());
  }

  /**
   * Удаление ПП
   * @param int $id
   * @return array
   */
  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }

  /**
   * Copy ПП
   * @param int $id
   * @return array
   */
  public function actionCopy($id)
  {
    $model = $this->findModel($id);

    $copy = new PartnerProgram($model->attributes);
    unset($copy->id);
    $copy->name .= ' copy';

    if ($count = PartnerProgram::find()
      ->where(['name' => $copy->name])
      ->orWhere(['like', 'name', $copy->name . ' ('])
      ->count()) {
      $copy->name .= ' (' . ($count + 1) . ')';
    }

    if ($copy->save()) {
      $items = PartnerProgramItem::find()->where(['partner_program_id' => $id])->each();

      $allCopied = true;
      foreach ($items as $item) {
        $copyItem = new PartnerProgramItem($item->attributes);
        unset($copyItem->id);
        $copyItem->partner_program_id = $copy->id;
        $allCopied = $allCopied && $copyItem->save();
      }
      return AjaxResponse::set($allCopied);
    }
    return AjaxResponse::error('promo.partner-programs.cant-copy');
  }

  /**
   * Прикрепление партнера
   * @param int $id
   * @return array
   */
  public function actionLinkPartner($id)
  {
    $this->view->title = Yii::_t('promo.partner_programs.add-partner');

    $request = Yii::$app->request;
    $partnerProgram = $this->findModel($id);
    $userId = ArrayHelper::getValue($request->post((new UserPromoSetting())->formName()), 'user_id');

    /** @var UserPromoSettings $userPromoSettings */
    $userPromoSettings = Yii::$app->getModule('promo')->api('userPromoSettings');

    $model = $userPromoSettings->getModel($userId);

    if ($model->load($request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      $model->partner_program_id = $partnerProgram->id;
      $model->scenario = UserPromoSetting::SCENARIO_ADD_PARTNER_PROGRAM;

      if ($request->post('submit') && $model->save()) {
        return AjaxResponse::success();
      }

      return ActiveForm::validate($model);
    }

    return $this->renderAjax('add-partner', [
      'partnerProgram' => $partnerProgram,
      'userPromoSetting' => $model,
      'userModule' => Yii::$app->getModule('users'),
    ]);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionAutosyncEnable($id)
  {
    return $this->handleActionAutosync($id);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionGetUsersByPartnerProgram($id)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    $userModule = Yii::$app->getModule('users');

    $ignoreIds = array_keys(UserPromoSetting::find()
      ->select(['user_id'])
      ->where(['partner_program_id' => $id])
      ->indexBy('user_id')
      ->all());

    $params = [
      'id' => Yii::$app->request->get('q'),
      'namesRoles' => $userModule::PARTNER_ROLE,
      'ignoreIds' => $ignoreIds,
    ];

    $idDataProviderModels = [];
    $queryDataProviderModels = [];
    $idDataProviderModelsCount = 0;

    $userIdSearch = $userModule->api('user')->getSearchModel();
    if ($params['id']) {
      // Поиск по ид

      /** @var ActiveDataProvider $idDataProvider */
      $idDataProvider = $userIdSearch->search([
        $userIdSearch->formName() => $params,
      ]);
      $idDataProvider->getPagination()->setPageSize($userIdSearch::SELECT2_LIMIT);
      $idDataProviderModels = $idDataProvider->getModels();
      /** если не производится поиск по айди, не делаем дополнительный запрос */
      $idDataProviderModelsCount = count($idDataProviderModels);
    }

    $userQuerySearch = $userModule->api('user')->getSearchModel();
    if ($userQuerySearch::SELECT2_LIMIT - $idDataProviderModelsCount !== 0) {
      // Поиск по запросу

      /** @var ActiveDataProvider $queryDataProvider */
      $queryDataProvider = $userQuerySearch->search([
        $userQuerySearch->formName() => [
          'queryName' => Yii::$app->request->get('q'),
          'namesRoles' => $userModule::PARTNER_ROLE,
          'ignoreIds' => $ignoreIds + ArrayHelper::getColumn($idDataProviderModels, 'id')
        ],
      ]);
      $queryDataProvider->getPagination()->setPageSize($userQuerySearch::SELECT2_LIMIT - $idDataProviderModelsCount);
      $queryDataProviderModels = $queryDataProvider->getModels();
    }

    // Сливаем результаты, выводим в первую очередь пользователя с ид, как в запросе
    $users = array_merge($idDataProviderModels, $queryDataProviderModels);

    return ['results' => array_map(function (User $item) {
      return [
        'text' => strtr(UserSelect2::USER_ROW_FORMAT, [
          ':id:' => $item->id,
          ':username:' => $item->username,
          ':email:' => $item->email
        ]),
        'id' => $item->id,
      ];
    }, $users)];
  }

  /**
   * @param $id
   * @return array
   */
  public function actionAutosyncDisable($id)
  {
    return $this->handleActionAutosync($id, false);
  }

  /**
   * @param $id
   * @param bool|true $enable
   * @return array
   */
  private function handleActionAutosync($id, $enable = true)
  {
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $status = Yii::$app->getModule('promo')->api('userPromoSettings')->setUserPartnerProgramAutosync($id, $enable);
    if ($status) {
      PartnerProgramSync::runAsync($id);
    }
    return AjaxResponse::set(
      Yii::$app->getModule('promo')->api('userPromoSettings')->setUserPartnerProgramAutosync($id, $enable)
    );
  }

  /**
   * @param $id
   * @return array
   */
  public function actionSyncPartner($id)
  {
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return AjaxResponse::set(PartnerProgramSync::runAsync($id));
  }

  /**
   * Открепление партнера
   * @param int $id
   * @return array
   */
  public function actionUnlinkPartner($id)
  {
    return AjaxResponse::set(UserPromoSetting::findOne($id)->delete());
  }

  /**
   * Очистка ПП от неактивных лендов
   * @param $id
   * @return array
   */
  public function actionClean($id)
  {
    (new PartnerProgramClean($this->findModel($id)))->run();
    return AjaxResponse::success();
  }

  public function actionLinkPartnerEditable($userId)
  {
    /** @var UserPromoSettings $userPromoSettings */
    $userPromoSettings = Yii::$app->getModule('promo')->api('userPromoSettings');
    $request = Yii::$app->request;

    if (!$request->post('hasEditable')) {
      return false;
    }
    Yii::$app->response->format = Response::FORMAT_JSON;

    /** @var UserPromoSetting $model */
    $model = $userPromoSettings->getModel($userId);
    $model->scenario = UserPromoSetting::SCENARIO_ADD_PARTNER_PROGRAM;

    if ($model->load($request->post(), '') && $model->save()) {
      $value = ArrayHelper::getValue($model->partnerProgram, 'name');
      return ['output' => $value, 'message' => ''];
    }
    return ['output' => '', 'message' => Yii::_t('app.common.Save failed')];
  }

  /**
   * Finds the PartnerProgram model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerProgram the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    $model = PartnerProgram::findOne((int)$id);
    if ($model !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}

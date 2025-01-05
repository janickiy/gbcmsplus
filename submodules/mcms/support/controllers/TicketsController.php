<?php

namespace mcms\support\controllers;

use mcms\common\behavior\ModelFetcher;

use mcms\common\web\AjaxResponse;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use mcms\support\Module;
use mcms\user\models\Role;
use mcms\support\models\Support;
use yii\data\ActiveDataProvider;
use mcms\support\models\SupportText;
use mcms\support\models\SupportForm;
use mcms\support\models\SupportCategory;
use mcms\support\models\search\SupportSearch;
use mcms\common\controller\AdminBaseController;
use mcms\support\components\storage\SupportStorage;
use mcms\support\components\events\EventAdminClosed;
use yii\widgets\ActiveForm;

/**
 * Class TicketsController
 * @package mcms\support\controllers
 * @method fetch($id)
 */
class TicketsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  private $supportStorage;

  public function __construct($id, $module, $config = [], SupportStorage $supportStorage)
  {
    $this->supportStorage = $supportStorage;
    parent::__construct($id, $module, $config);
  }

  public function behaviors()
  {
    return array_merge(parent::behaviors(), [
      [
        'class'         => ModelFetcher::class,
        'defaultAction' => $this->defaultAction,
        'storage'       => $this->supportStorage,
        'controller'    => $this
      ]
    ]);
  }

  /**
   * @return string
   */
  public function actionList()
  {
    $searchModel = new SupportSearch([
      'has_unread_messages' => true,
    ]);

    $this->getView()->title = Yii::_t('support.controller.tickets_title');

    return $this->render('list', [
      'dataProvider' => $searchModel->search(Yii::$app->request->getQueryParams()),
      'searchModel' => $searchModel,
    ]);
  }

  protected function handleForm(SupportForm $form)
  {
    if ($form->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($form);
      } else {
        // Сохранение
        return AjaxResponse::set($form->saveSupport());
      }
    }

    return $this->renderAjax('form', [
      'model' => $form
    ]);
  }

  /**
   * @param $id
   * @return string|Response
   */
  public function actionEdit($id)
  {
    // TODO Если этот метод будет исползоваться для редактирования тикета в модалке в списке,
    // то для модели нужно найти решение аналогичное @see ActiveRecord::uniqueFormName , иначе идентификаторы полей
    // будут дублироваться при открытии модалок и соответсвенно появятся конфликты в JS
    /** @var Support $support */
    $support = $this->fetch($id);
    $this->checkUserAccess($support->created_by);

    return $this->handleForm(new SupportForm($support));
  }

  /**
   * @return string|Response
   */
  public function actionCreate()
  {
    $this->getView()->title = Yii::_t('support.controller.support_action_create');

    return $this->handleForm(new SupportForm(new Support()));
  }

  /**
   * @param $id
   * @return string
   */
  public function actionView($id)
  {
    /** @var Support $support */
    $support = $this->fetch($id);
    $this->checkUserAccess($support->created_by);
    $this->getView()->title = Yii::_t('support.controller.support_action_view') . ' | ' . $support->name;

    /** @var SupportCategory $supportCategory */
    /** @var Role $role */
    $rolesAllowedToDelegate = $support->getSupportCategory()->one()->getRolesIds();
    $supportText = new SupportText();

    if (Yii::$app->request->isPost) {
      $supportText->load(Yii::$app->request->post());
      $supportText->from_user_id = Yii::$app->getUser()->id;
      $supportText->support_id = $support->id;

      if ($supportText->validate() && $supportText->save()) {
        $this->flashSuccess('support.controller.notification_message_saved');
        return $this->redirect(Yii::$app->request->getReferrer());
      }
    }

    return $this->render('view', [
      'support' => $support,
      'messagesUrl' => Yii::$app->getUrlManager()->createUrl(['support/tickets/messages', ['id' => $id]]),
      'messagesPostUrl' => Yii::$app->getUrlManager()->createUrl(['support/tickets/send-message', ['id' => $id]]),
      'history' => $support->getHistory()->orderBy('created_at ASC')->all(),
      'rolesAllowedToDelegate' => $rolesAllowedToDelegate,
      'delegateToUrl' => Yii::$app->getUrlManager()->createUrl(['support/tickets/delegate', ['id' => $id]]),
      'messageFormModel' => $supportText,
      'messagesDataProvider' => new ActiveDataProvider([
        'query' => $support->getText()->orderBy('created_at DESC')
      ]),
    ]);
  }

  /**
   * @param $id
   * @return Response
   */
  public function actionClose($id)
  {
    return $this->handleOpenClose($id, false);
  }

  /**
   * @param $id
   * @return Response
   */
  public function actionOpen($id)
  {
    return $this->handleOpenClose($id);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDelegate($id)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    if (!Yii::$app->request->isPost || !Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(405);
      return [
        'success' => false
      ];
    }

    /** @var Support $support */
    $support = $this->fetch($id);
    $this->checkUserAccess($support->created_by);
    $userId = Yii::$app->request->post('userId');
    if ($userId === null) {
      $this->flashFail('support.controller.notification_ticket_user_not_found');
      return [];
    }

    $support->delegated_to = $userId;
    if ($support->save()) {
      $this->flashSuccess('support.controller.notification_ticket_delegated');
    }

    return [];
  }

  public function actionRead($id)
  {
    /** @var Support $ticket */
    $ticket = $this->fetch($id);
    $this->checkUserAccess($ticket->created_by);
    if ($ticket->markAsRead()) {
      $this->flashSuccess('support.controller.notification_ticket_read');
    } else {
      $this->flashFail('support.controller.notification_ticket_not_read');
    }

    return $this->redirect(['/support/tickets/view', 'id' => $id]);
  }

  private function handleOpenClose($id, $isOpen = true)
  {
    /** @var Support $support */
    $support = $this->fetch($id);
    $this->checkUserAccess($support->created_by);
    $hasAccessError = false;

    $notificationMessage = $isOpen ? 'notification_ticket_opened' : 'notification_ticket_closed';
    $isOpen
      ? $support->open()
      : $support->close()
      ;

    if (!$hasAccessError && $support->save()) {
      if (!$isOpen) (new EventAdminClosed($support))->trigger();
      $this->flashSuccess('support.controller.' . $notificationMessage);
    }

    return $this->redirect(['/support/tickets/view', 'id' => $id]);
  }

  private function checkUserAccess($userId) {
    if (!Yii::$app->user->identity->canViewUser($userId)) {
      throw new NotFoundHttpException;
    }
  }
}
<?php

namespace mcms\partners\controllers;

use mcms\common\web\AjaxResponse;
use mcms\partners\models\TempFile;
use mcms\support\components\events\EventAdminClosed;
use mcms\support\components\events\EventAdminCreated;
use mcms\support\components\events\EventMessageReceived;
use mcms\support\components\events\EventPartnerClosed;
use mcms\support\models\Support;
use yii\web\Response as YiiResponse;
use Yii;
use mcms\partners\models\TicketForm;
use mcms\partners\models\TicketMessageForm;
use mcms\common\controller\SiteBaseController as BaseController;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\Url;

/**
 * Class SupportController
 * @package mcms\partners\controllers
 */
class SupportController extends BaseController
{
  public $controllerTitle;

  /** @var bool для лейаута */
  public $categoryNoNav = true;

  const TEMP_FILE_COUNT = 10;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->controllerTitle = Yii::_t('partners.main.support');
    $this->theme = 'basic';
    return parent::beforeAction($action);
  }

  /**
   * @return string
   */
  public function actionIndex()
  {
    $ticketMessageForm = new TicketMessageForm();
    return $this->render('index', [
      'ticketMessageForm' => $ticketMessageForm,
    ]);
  }

  /**
   * @return string
   */
  public function actionMessages($id)
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }
    
    $model = Yii::$app->getModule('support')->api('getTicket',
      ['ticket_id' => $id, 'user_id' => Yii::$app->user->id])->getResult();

    if(!$model)
      throw new NotFoundHttpException('The requested page does not exist.');

    return $this->renderAjax('messages', [
      'model' => $model
    ]);
  }

  public function actionClose($id)
  {
    $ticketsApi = Yii::$app->getModule('support')->api('closeTicket',
      ['ticket_id' => $id, 'user_id' => Yii::$app->user->id]);
    $result = $ticketsApi->getResult();
    if ($result) {
      (new EventPartnerClosed($ticketsApi->getTicket()))->trigger();
    }

    return AjaxResponse::set($result);
  }

  public function actionRead($id)
  {
    $result = Yii::$app->getModule('support')->api('readTicket',
      ['ticketId' => $id])->getResult();

    Yii::$app->response->format = YiiResponse::FORMAT_JSON;

    return (new AjaxResponse([
      'success' => $result,
      'data' => [
        'count' => Yii::$app->getModule('partners')->api('getCountOfTickets')->getResult(false),
      ],
    ]));
  }

  public function actionCreate()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $save = Yii::$app->request->post('submit') == true;

    $postData = Yii::$app->request->post();
    $formName = (new TicketForm())->formName();

    $session = Yii::$app->session;
    if(ArrayHelper::getColumn($postData[$formName], 'files')) {
      $postData[$formName]['images'] = $session->get('file');
      if ($save) $session->remove('file');
    }

    $result = Yii::$app->getModule('support')->api('createTicket', [
      'postData' => $postData,
      'userId' => Yii::$app->user->id,
      'save' => $save,
      'formName' => $formName,
    ])->getResult();

    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($save && $result['success']) return AjaxResponse::success();

    return $result;
  }

  public function actionSendMessage($ticketId)
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $save = Yii::$app->request->post('submit') == true;

    $postData = Yii::$app->request->post();
    $formName = (new TicketMessageForm())->formName();

    $session = Yii::$app->session;
    if(ArrayHelper::getValue($postData[$formName], 'files')) {
      $postData[$formName]['images'] = $session->get('file');
      if ($save) $session->remove('file');
    }

    $result = Yii::$app->getModule('support')->api('sendTicketMessage', [
      'postData' => $postData,
      'ticketId' => $ticketId,
      'save' => $save,
      'formName' => $formName,
    ])->getResult();

    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($save && $result['success']) return $this->actionMessages($ticketId);

    return $result;
  }

  public function actionUploadFile()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $session = Yii::$app->session;
    if (!$session->isActive) $session->open();

    $tempFile = new TempFile();
    $tempFile->image = UploadedFile::getInstance(new TicketMessageForm(), 'images') ?
      UploadedFile::getInstance(new TicketMessageForm(), 'images') :
      UploadedFile::getInstance(new TicketForm(), 'images');

    if ($tempFile->validate(['image'])) {

      $response = [];

      // Сохраняем во временную директорию
      $url = Yii::getAlias('@uploadUrl/' . $this->module->id . '/' . $this->id . '/temp/' . Yii::$app->user->id . '/');
      $path = Yii::getAlias('@uploadPath/' . $this->module->id . '/' . $this->id . '/temp/' . Yii::$app->user->id . '/');
      FileHelper::createDirectory($path);
      $ext = pathinfo($tempFile->image->name)['extension'];

      $files = FileHelper::findFiles($path);
      if (count($files) >= self::TEMP_FILE_COUNT) @unlink(array_shift($files));

      $filename = md5(Yii::$app->user->id) . time() . ".{$ext}";

      $fullPath = $path . $filename;
      $tempFile->file = $url . $filename;

      if ($tempFile->image->saveAs($fullPath)) {
        $session->set('file', $fullPath);
        $response['file'] = [
          'name' => $filename,
          'url' => $tempFile->file,
          'deleteUrl' => Url::to(['delete-file']),
          'deleteType' => 'POST'
        ];
      } else {
        $response = ['error' => Yii::t('app', 'Unable to save picture')];
      }
      @unlink($tempFile->image->tempName);
    } else {
      $response = ['error' => $tempFile->getErrors('image')];
    }

    return $response;
  }

  public function actionDeleteFile()
  {
    if (!Yii::$app->request->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return false;
    }

    $session = Yii::$app->session;
    @unlink($session->get('file'));
    $session->remove('file');

    Yii::$app->response->format = Response::FORMAT_JSON;
    return AjaxResponse::success();
 }

  public static function t($key)
  {
    return Yii::_t('partners.support.support-' . $key);
  }

}

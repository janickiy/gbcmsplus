<?php

namespace mcms\payments\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\payments\components\RemoteWalletBalances;
use mcms\payments\models\paysystems\api\BaseApiSettings;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\paysystems\PaySystemApiGroup;
use mcms\payments\models\paysystems\PaySystemApiGroupSearch;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * API платежных систем
 */
class PaymentSystemsApiController extends AdminBaseController
{
  const CACHE_BALANCE = 'balance';
  const BALANCE_CACHE_DURATION = 60;


  /**
   * Список API платежных систем
   */
  public function actionList()
  {
    $this->getView()->title = Yii::_t('payments.menu.payment-systems-api');
    $this->setBreadcrumb('payments.menu.payment-systems-api');

    $searchModel = new PaySystemApiGroupSearch;
    $dataProvider = $searchModel->search([]);

    return $this->render('list', compact(
      'dataProvider',
      'searchModel'
    ));
  }

  /**
   * Изменение настроен API ПС
   * @param string $code
   * @return array|bool|string
   */
  public function actionUpdate($code)
  {
    $apisGroup = PaySystemApiGroup::findGroup($code);

    $this->getView()->title = Yii::_t('payments.menu.payment-systems-api') . ' | ' . $apisGroup->getName();
    $this->setBreadcrumb('payments.menu.payment-systems-api', ['list']);
    $this->setBreadcrumb($apisGroup->getName());

    $settingObjects = $apisGroup->getSettingObjects();

    // Рендер форм
    if (!Yii::$app->request->isPost) {
      return $this->render('update', [
        'group' => $apisGroup,
        'models' => $settingObjects,
        'checkAllCurrency' => $this->getMatchAllCurrenciesSettings($apisGroup)
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $settingObjectsToSave = $apisGroup->loadSettings(Yii::$app->request->post());

    if (!Yii::$app->request->post("submit")) {
      return $apisGroup->validateSettings($settingObjectsToSave);
    }

    return $apisGroup->saveSettings($settingObjectsToSave);
  }

  // TODO Есть куча экшенов аналогичных этому. Нужно вынести их в один класс-экшен, что бы избежать дублирования
  // TODO SECURITY Защитить от загрузки вредоносных файлов (нет серверной валидации типа)
  public function actionFileUpload()
  {
    $filepath = BaseApiSettings::getFilepath(Yii::$app->request->post('formName'), Yii::$app->request->post('attribute'));
    $path = Yii::getAlias('@protectedUploadPath' . $filepath);

    FileHelper::createDirectory($path);

    $file = UploadedFile::getInstanceByName(Yii::$app->request->post('attribute'));

    if ($file && is_dir($path) && is_writable($path)) {
      $ext = pathinfo($file->name)['extension'];
      $newFilename = Yii::$app->security->generateRandomString() . ".{$ext}";
      if ($file->saveAs($path . $newFilename)) {
        echo json_encode(['url' => $filepath . $newFilename]);
      } else {
        echo json_encode(['error' => 'Can not upload file']);
      }
    } else {
      echo json_encode(['error' => 'can not upload file']);
    }

    Yii::$app->end();
  }

  // TODO SECURITY ДАННЫЙ КОД ПОЗВОЛЯЕТ УДАЛИТЬ ЛЮБОЙ ФАЙЛ НА СЕРВЕРЕ, ТАК КАК В $key МОЖНО ПЕРЕДАТЬ ЛЮБОЕ ЗНАЧЕНИЕ
  // TODO Найти аналогичные уязвимости в других местах
  // TODO Вынести аналогичные экшены в единый класс-экшен для истребления дублирования
  public function actionFileDelete($code)
  {
    $paysystem = PaySystemApi::findOne(['code' => $code]);
    if (!$paysystem) throw new NotFoundHttpException;
    $settings = $paysystem->getSettingsObject();

    $key = Yii::$app->request->post('key');
    $path = '@protectedUploadPath' . $settings->{$key};

    $settings->{$key} = null;

    if ($settings->save()) {
      unlink(Yii::getAlias($path));
      return true;
    }

    return false;
  }

  /**
   * Finds the Wallet model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PaySystemApi the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PaySystemApi::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  public function actionGetBalances()
  {
    if (($cacheResult = Yii::$app->getCache()->get(self::CACHE_BALANCE)) !== false) {
      return AjaxResponse::success($cacheResult);
    }

    $balances = [];
    foreach (PaySystemApi::find()->each() as $paymentSystemApi) {
      /* @var $paymentSystemApi PaySystemApi */
      $balances[$paymentSystemApi->id] = $paymentSystemApi->getBalance();
    }

    Yii::$app->getCache()->set(self::CACHE_BALANCE, $balances, self::BALANCE_CACHE_DURATION);

    return AjaxResponse::success($balances);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionGetBalance($id)
  {
    /** @var RemoteWalletBalances $service */
    $service = Yii::$container->get('mcms\payments\components\RemoteWalletBalances');
    $balance = $service->get($id);
    return AjaxResponse::success([
      'balance' => $balance,
      'formatted' => is_numeric($balance) ? Yii::$app->formatter->asDecimal($balance) : null
    ]);
  }

  /**
   * @param PaySystemApiGroup $apisGroup
   * @return bool Вернет true ТОЛЬКО в том случае, если все модели валидные, а также они все идентичны
   */
  protected function getMatchAllCurrenciesSettings(PaySystemApiGroup $apisGroup)
  {
    $settings = null;
    foreach ($apisGroup->paysystemApis as $paysystemApi) {
      if (!$paysystemApi->isActive()) return false;

      if (is_null($settings)) $settings = $paysystemApi->settings;

      if ($settings !== $paysystemApi->settings) return false;
    }

    return true;
  }

}

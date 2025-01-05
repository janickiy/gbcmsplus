<?php
namespace mcms\payments\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\payments\models\paysystems\api\YandexMoneyApiSettings;
use mcms\payments\models\paysystems\PaySystemApi;
use Yii;
use YandexMoney\API;
use yii\helpers\Url;

/**
 * Авторизация в платежных системах
 */
class PaymentSystemsAuthController extends AdminBaseController
{

  /**
   * Авторизация в YandexMoney
   */
  public function actionYandexMoney()
  {
    $model = PaySystemApi::findOne(['code' => 'yandex-money']);

    /**
     * @var $paysystem YandexMoneyApiSettings
     */
    $paysystem = $model->getSettingsObject();

    if ($code = Yii::$app->request->get('code')) {
      $result = API::getAccessToken($paysystem->clientId, $code,
        urlencode($paysystem->redirectURI), $paysystem->clientSecret);
      $paysystem->accessToken = $result->access_token;
      $model->settings = (string)$paysystem;
      $model->save();
    }

    if (empty($code) && $paysystem->validate()) {
      $url = API::buildObtainTokenUrl(
        $paysystem->clientId,
        urlencode($paysystem->redirectURI),
        explode(" ", $paysystem->scope)
      );
      return $this->redirect($url);
    } else {
      if (!$code) $this->flashFail('payments.payment-systems-api.fill-and-save-settings');
    }

    return $this->redirect(Url::toRoute(['payment-systems-api/update', 'code' => $model->code]));

  }

  public function actionAlfabank()
  {
    return $this->render('alfabank');
  }
}
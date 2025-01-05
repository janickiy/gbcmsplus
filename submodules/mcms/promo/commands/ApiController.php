<?php

namespace mcms\promo\commands;

use mcms\promo\commands\api_actions\GetAdsParams;
use mcms\promo\commands\api_actions\GetAdsTypeConfirmText;
use mcms\promo\commands\api_actions\GetLinksReplacementParams;
use mcms\promo\components\BannerCompiler;
use mcms\promo\models\Banner;
use mcms\promo\models\LandingCategory;
use mcms\promo\Module;
use Yii;
use yii\console\Controller;
use mcms\promo\commands\api_actions\GetSelectedBannerAction;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @deprecated больше этот апи не используем, используй другие способы
 */
class ApiController extends Controller
{

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'get-selected-banner' => [
        'class' => GetSelectedBannerAction::class,
      ],
      'get-ads-type-confirm-text' => [
        'class' => GetAdsTypeConfirmText::class,
      ],
      'get-ads-params' => [
        'class' => GetAdsParams::class,
      ],
    ];
  }

  function actionGetPersonalPercentList($encodedData)
  {
    $decodedData = json_decode($encodedData, true);
    $response = [];
    foreach ($decodedData as $key => $data) {
      $response[$key] = Yii::$app->getModule('promo')->api('personalProfit', [
        'userId' => ArrayHelper::getValue($data, 'userId'),
        'operatorId' => ArrayHelper::getValue($data, 'operatorId'),
        'landingId' => ArrayHelper::getValue($data, 'landingId')
      ])->setResultTypeArray()->getResult();
    }

    echo json_encode($response);
  }

  public function actionGetRebillCorrectList($encodedData)
  {
    $decodedData = json_decode($encodedData, true);
    $response = [];
    foreach ($decodedData as $key => $data) {
      $response[$key] = Yii::$app->getModule('promo')->api('rebillCorrectConditions', [
        'partnerId' => $data['userId'],
        'operatorId' => $data['operatorId'],
        'landingId' => $data['landingId']
      ])->getPercent();
    }

    echo json_encode($response);
  }

  function actionGetPersonalPercent($userId, $operatorId = NULL, $landingId = NULL)
  {

//    $this->stdout("Params: userId=$userId, operatorId=$operatorId, landingId=$landingId" . "\n", Console::FG_GREEN);

    $data = Yii::$app->getModule('promo')->api('personalProfit', [
      'userId' => $userId,
      'operatorId' => $operatorId,
      'landingId' => $landingId
    ])->setResultTypeArray()->getResult();

    // {"rebill_percent":92,"buyout_percent":92,"reseller_buyout_percent":100,"reseller_rebill_percent":90}
    echo json_encode($data);

  }

  public function actionGetMainCurrencies()
  {
    echo json_encode(Yii::$app->getModule('promo')->api('mainCurrencies')->getResult());
  }

  /**
   * @param null $partnerId
   * @param null $operatorId
   * @param null $landingId
   */
  public function actionGetRebillCorrect($partnerId = NULL, $operatorId = NULL, $landingId = NULL)
  {
    $data = Yii::$app->getModule('promo')->api('rebillCorrectConditions', [
      'partnerId' => $partnerId,
      'operatorId' => $operatorId,
      'landingId' => $landingId
    ])->getPercent();

    echo json_encode($data);
  }

  /**
   * Настройки fake Revshare subscriptions
   */
  public function actionFakeRevshareSettings($partnerId)
  {
    echo Json::encode(Module::getInstance()->api('fakeRevshareSettings', ['partnerId' => $partnerId])->getResult());
  }

  /**
   * Список Всех баннеров
   * @param int $lastUpdated
   */
  public function actionBannerList($lastUpdated = 0)
  {
    $banner = Banner::find()
      ->where([Banner::tableName() . '.is_disabled' => 0])
      ->joinWith('template')
      ->andWhere(Banner::tableName() . '.updated_at >= :lastUpdated', [':lastUpdated' => $lastUpdated])
    ;

    echo json_encode([
      'success' => true,
      'bannerList' => $banner->all()
    ]);
  }

  /**
   * Возвращает скомпиленный баннер
   */
  public function actionBanner($bannerId)
  {
    /** @var Banner $banner */
    $banner = Banner::getEnabledBannersById($bannerId);

    $result = [
      'bannerCode' => $banner->code,
    ];

    if ($banner === null) {
      $result['success'] = false;
      $result['error'] = 'Not found';

      echo json_encode($result, JSON_UNESCAPED_UNICODE);
      return ;
    }

    $compiler = BannerCompiler::createFromBannerLanguage($banner);

    $result['success'] = true;
    $result['banner'] = $compiler->compile();
    $result['templateCode'] = $compiler->getTemplateCode();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
  }
}

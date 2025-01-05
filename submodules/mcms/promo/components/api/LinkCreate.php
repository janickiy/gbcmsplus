<?php

namespace mcms\promo\components\api;

use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\common\helpers\FormHelper;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source as LinkSource;

class LinkCreate extends ApiResult
{
  protected $postData;
  protected $userId;
  protected $save;
  protected $formName;
  protected $attributes;

  public function init($params = [])
  {
    $this->postData = ArrayHelper::getValue($params, 'postData');
    $this->userId = ArrayHelper::getValue($params, 'userId');
    $this->save = ArrayHelper::getValue($params, 'save');
    $this->formName = ArrayHelper::getValue($params, 'formName');
    $this->attributes = ArrayHelper::getValue($params, 'attributes');
  }

  public function getResult()
  {
    /** @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    $source = new LinkSource([
      'status' => $promoModule->isArbitraryLinkModerationActive() ? LinkSource::STATUS_MODERATION : LinkSource::STATUS_APPROVED,
      'source_type' => LinkSource::SOURCE_TYPE_LINK
    ]);
    // шаг 4 - тестирование Postback Url
    $source->scenario = ArrayHelper::getValue($this->postData, 'stepNumber') == 4
      ? LinkSource::SCENARIO_PARTNER_TEST_POSTBACK_URL
      : LinkSource::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE;

    $source->user_id = $this->userId;

    $source->load($this->postData, $this->formName);

    // Попытка найти источник в БД
    if ($source->refresh()) {
      $source->afterFind();
      $source->load($this->postData, $this->formName); // Повторно загрузим данные из запроса
      // Если источник есть, но не были присланы LandingOperator, то заполняем их
      if ($this->save && $source->linkOperatorLandings === null) {
        $linkOperatorLandings = $source->getSourceOperatorLanding()->all();
        foreach ($linkOperatorLandings as $operatorLanding) {
          $source->linkOperatorLandings[$operatorLanding->landing_id][$operatorLanding->operator_id] = ['profit_type' => $operatorLanding->profit_type];
        }
      }
    }

    if (!$this->save) {
      return FormHelper::validate($source, $this->attributes, strtolower($this->formName));
    }

    $transaction = Yii::$app->db->beginTransaction();
    $landings = is_array($source->linkOperatorLandings) && count($source->linkOperatorLandings)
      ? $source->linkOperatorLandings
      : []
    ;
    list($landingsHasRevshare, $landingsHasCPA) = $this->landingsHasRevshareCPA($landings);

    $result = false;
    try {
      $source->initHash();
      $result = $source->save();
      $transaction->commit();
    } catch (\Exception $e) {
      $transaction->rollBack();
    } finally {
      return [
        'success' => $result,
        'errors' => $source->getErrors(),
        'id' => $source->id,
        'stream_id' => $source->stream_id,
        'link' => $source->getLink(),
        'landingsHasRevshare' => $landingsHasRevshare,
        'landingsHasCPA' => $landingsHasCPA,
      ];
    }
  }

  private function landingsHasRevshareCPA(array $landings)
  {
    $hasCPA = $hasRevshare = false;
    if (!count($landings)) return [false, false];

    foreach ($landings as $landing) {
      $landing = current($landing);
      if (!$hasCPA) {
        $hasCPA = $landing['profit_type'] == SourceOperatorLanding::PROFIT_TYPE_BUYOUT;
      }

      if (!$hasRevshare) {
        $hasRevshare = $landing['profit_type'] == SourceOperatorLanding::PROFIT_TYPE_REBILL;
      }

      if ($hasRevshare && $hasCPA) break;
    }

    return [$hasRevshare, $hasCPA];
  }

}

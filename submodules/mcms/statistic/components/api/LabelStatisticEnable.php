<?php

namespace mcms\statistic\components\api;

use mcms\common\web\Response;
use mcms\statistic\models\UserStatSettings;
use mcms\statistic\Module;
use Yii;
use mcms\common\module\api\ApiResult;
use yii\base\InvalidParamException;
use yii\helpers\BaseHtml;
use yii\web\NotFoundHttpException;

/**
 * Class LabelStatisticEnable
 * @package mcms\statistic\components\api
 */
class LabelStatisticEnable extends ApiResult
{


  /**
   * @inheritdoc
   */
  function init($params = [])
  {

  }

  /**
   * @param $userId
   * @return bool
   */
  public function getIsEnabledByUser($userId)
  {
    if (!$userId) return !!UserStatSettings::DEFAULT_IS_LABEL_STAT_ENABLED;

    if (!$this->getIsEnabledGlobally()) return false;

    /** @var UserStatSettings $model */
    $model = UserStatSettings::findOne(['user_id' => $userId]);

    if ($model) return !!$model->is_label_stat_enabled;

    return !!UserStatSettings::DEFAULT_IS_LABEL_STAT_ENABLED;
  }

  /**
   * @return bool
   */
  public function getIsEnabledGlobally()
  {
    return Yii::$app->getModule('statistic')->settings->getValueByKey(Module::SETTINGS_ENABLE_LABEL_STAT);
  }

  /**
   * @param $userId
   * @param $flag
   * @return Response
   * @throws NotFoundHttpException
   */
  public function saveUserFlag($userId, $flag)
  {
    if (!$userId) throw new InvalidParamException('User id is missing');

    /** @var UserStatSettings $model */
    $model = UserStatSettings::findOne(['user_id' => $userId]);

    if (!$model) {
      $model = new UserStatSettings([
        'user_id' => $userId
      ]);
    };

    $model->is_label_stat_enabled = $flag;

    return (new Response([
      Response::DEFAULT_SUCCESS_PARAM => $model->save(),
      Response::DEFAULT_ERROR_PARAM => BaseHtml::errorSummary($model)
    ]));
  }
}

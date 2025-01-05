<?php

namespace mcms\promo\commands;

use mcms\currency\models\Currency;
use mcms\promo\components\handlers\KP;
use mcms\promo\models\Provider;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class MagicCoursesController extends Controller
{

  protected $coursesSummary = [];
  protected $courses = [];
  protected $eurModel;

  public function actionIndex($isIncoming = 0)
  {
    $providers = Provider::find()
      ->where(['handler_class_name' => 'KP'])
      ->all();

    $this->eurModel = Currency::findOne(['code' => 'eur']);

    foreach ($providers as $provider) {
      $handlerClass = KP::class;
      /* @var $handler KP */
      $handler = new $handlerClass($provider);

      $auth = $handler->auth();

      if (!$auth) {
        continue;
      }

      $landingsStr = $handler->getLandingsFromApi();

      $landings = json_decode($landingsStr, true);

      $landings = ArrayHelper::getValue($landings, 'data');

      foreach ($landings as $landingObj) {
        if ($landingObj['status'] !== KP::KP_LAND_STATUS_ACTIVE) {
          continue;
        }

        $operators = ArrayHelper::getValue($landingObj, 'operators', []);

        foreach ($operators as $operator) {
          if ((int)$operator['status'] !== 1) {
            continue;
          }

          if ($isIncoming) {
            $currency = strtolower(substr($operator['incoming'], -3));
          } else {
            $currency = $operator['currency_default'];
          }

          if ($currency === 'eur') {
            continue;
          }

          $course = $operator['price_real'] / $operator['price_default'];

          $this->courses[$currency][] = $course;

          if (!isset($this->coursesSummary[$currency][(string)$course])) {
            $this->coursesSummary[$currency][(string)$course] = 0;
          }
          $this->coursesSummary[$currency][(string)$course]++;
        }
      }
    }

    $logStr = print_r($this->eurModel->getAttributes(), true);
    $logStr .= print_r($this->coursesSummary, true);



    // присваиваем курсы валют

    foreach ($this->courses as $currencyCode => $courses) {
      $avg = array_sum($courses) / count($courses);

      if ($avg) {
        $currency = Currency::findOne(['code' => $currencyCode]);
        $currency->custom_to_eur = $avg;

        if ($currencyCode !== 'usd') {
          $currency->custom_to_usd = $avg * $this->eurModel->to_usd;
        }

        if ($currencyCode !== 'rub') {
          $currency->custom_to_rub = $avg * $this->eurModel->to_rub;
        }

        $logStr .= $currencyCode . ' AVG=' . $avg . PHP_EOL;
        if (!$currency->save()) {
          $msg = 'CURRENCY NOT SAVED!' . PHP_EOL .
            print_r($currency->getAttributes(), true) . PHP_EOL .
            print_r($currency->getErrors(), true);
          $this->stdout($msg);
          $logStr .= $msg;
          continue;
        }
      }
    }

    file_put_contents(Yii::getAlias('@protectedUploadPath/' . time() . '_magic-courses.log'), $logStr);

    $this->stdout('SUCCESS' . PHP_EOL);
  }
}

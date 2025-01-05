<?php
namespace mcms\statistic\controllers;

use Exception;
use Facebook\WebDriver\Exception\TimeOutException;
use mcms\common\controller\AdminBaseController;
use mcms\common\output\ArrayOutput;
use mcms\statistic\components\traffic_generator\AbstractGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\ComplainsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\OffsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\RebillsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\SubsGenerator;
use mcms\statistic\components\traffic_generator\GeneratorConfig;
use mcms\statistic\components\traffic_generator\TrafficGenerator;
use mcms\statistic\components\traffic_generator\TrafficGeneratorForm;
use mcms\common\output\OutputInterface;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Траф-генератор. Отправляет реальные хиты и конверсии в микросервис
 */
class TrafficGeneratorController extends AdminBaseController
{
  public $defaultAction = 'index';

  /**
   * @return array|string
   * @throws NotFoundHttpException
   * @throws \yii\base\InvalidConfigException
   */
  public function actionIndex()
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      throw new NotFoundHttpException();
    }
    $model = new TrafficGeneratorForm();

    if ($model->load(Yii::$app->getRequest()->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      if(!Yii::$app->request->post("submit")) {
        return ActiveForm::validate($model);
      }

      $config = new GeneratorConfig([
        'sourceId' => $model->sourceId,
        'operatorId' => $model->operatorId,
        'subsPercent' => $model->subsPercent,
        'rebillsPercent' => $model->rebillsPercent,
        'offsPercent' => $model->offsPercent,
        'complainsPercent' => $model->complainsPercent,
        'kpSecret' => $model->kpSecret,
        'pbHandlerUrl' => $model->pbHandlerUrl,
        'hitHandlerUrl' => $model->hitHandlerUrl,
        'hitsDateFrom' => $model->hitsDateFrom,
        'hitsCount' => $model->hitsCount,
        'inaccuracyPercent' => $model->inaccuracyPercent
      ]);

      $generators = [
        'hits' => TrafficGenerator::class,
        'subs' => SubsGenerator::class,
        'rebills' => RebillsGenerator::class,
        'offs' => OffsGenerator::class,
        'complains' => ComplainsGenerator::class,
      ];

      Yii::$container->set(OutputInterface::class, [
        'class' => ArrayOutput::class
      ]);

      try {
        foreach ($generators as $key => $generatorClass) {
          /** @var AbstractGenerator $generator */
          $generator = Yii::createObject($generatorClass, [$config]);
          $generator->execute();
        }
      } catch (Exception $exception) {
        return ['error'];
      }

      return ArrayOutput::getMessages();
    }


    return $this->render('index', ['model' => $model]);
  }

}

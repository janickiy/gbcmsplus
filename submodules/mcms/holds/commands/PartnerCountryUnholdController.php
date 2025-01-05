<?php

namespace mcms\holds\commands;

use mcms\holds\components\PartnerCountryUnhold;
use yii\console\Controller;

/**
 * Расчитываем расхолд партнеров. Можно выполнять много раз. Но желательно чтобы в этот момент не менялись правила
 */
class PartnerCountryUnholdController extends Controller
{
  public function actionIndex()
  {
    $this->stdout('Begin...' . PHP_EOL);
    (new PartnerCountryUnhold())->run();
    $this->stdout('SUCCESS' . PHP_EOL);
  }
}

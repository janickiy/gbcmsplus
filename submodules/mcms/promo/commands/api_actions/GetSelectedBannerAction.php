<?php

namespace mcms\promo\commands\api_actions;

use mcms\common\web\Response;
use Yii;
use yii\base\Action;

/**
 * Получение ссылки баннера для форматов рекламы Push и DialogAds
 * (т.е. где показываем модалку)
 *
 * Class GetSelectedBannerAction
 * @package mcms\promo\commands\api_actions
 */
class GetSelectedBannerAction extends Action
{

  /**
   * @param $sourceId
   * @throws \yii\base\ExitException
   */
  public function run($sourceId)
  {
    $data = Yii::$app->getModule('promo')->api('banners', ['sourceId' => $sourceId])->getSelected();
    @ob_clean(); // Чистим буфер, чтобы ничего лишнее не попало в вывод до следующих операций
    echo json_encode(new Response(['success' => true, 'data' => $data]));
    Yii::$app->end();
  }


}
<?php

namespace mcms\partners\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\web\View;
use mcms\partners\Module;
use Yii;
use yii\helpers\Url;

/**
 * Class Publication
 * Метатеги для соц-сетей
 * @package mcms\partners\components\api
 */
class Publication extends ApiResult
{
  /** @var  View */
  public $view;
  public function getResult() {}

  /**
   * @param array $params
   */
  function init($params = []) {
    $this->view = ArrayHelper::getValue($params, 'view');
  }

  /**
   * @return bool
   */
  public function registerImage()
  {
    if (!$this->view instanceof \yii\web\View) {
      return false;
    }
    if (!$image = Module::getInstance()->settings->offsetGet(Module::SETTINGS_LOGO_PUBLICATION)->getUrl()) {
      return false;
    }
    $image = Url::to($image, true);

    //vk fb
    $this->view->registerMetaTag([
      'property' => 'og:image',
      'content' => $image,
    ]);

    //twitter
    $this->view->registerMetaTag([
      'name' => 'twitter:image',
      'content' => $image,
    ]);

    return true;
  }
}
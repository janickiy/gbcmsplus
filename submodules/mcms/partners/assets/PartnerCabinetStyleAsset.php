<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Ассет кастомных стилей ПП
 * Class PartnerCabinetStyleAsset
 * @package mcms\partners\assets
 */
class PartnerCabinetStyleAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/partner-cabinet-styles';

  public $depends = [
    '\mcms\partners\assets\BasicAsset',
  ];
  /**
   * @var \mcms\pages\models\PartnerCabinetStyle
   */
  private $customStyle;

  /**
   * @inheritdoc
   */
  public function init()
  {
    /**
     * @var \mcms\pages\components\api\PartnerCabinetStyleApi $partnerCabinetStyleApi
     */
    $partnerCabinetStyleApi = Yii::$app->getModule('pages')->api('partnerCabinetStyle');
    $this->customStyle = $partnerCabinetStyleApi->getActive();
  }

  /**
   * Получить путь до файла сгенерированного css
   * @return string
   */
  public function getFilename()
  {
    if (!$this->customStyle) {
      return null;
    }

    return $this->customStyle->id . '_' . $this->customStyle->updated_at . '.css';
  }

  /**
   * Переопределяем метод publish чтобы добавить публикацию кастомных стилей
   * @param \yii\web\AssetManager $am
   */
  public function publish($am)
  {
    $filename = $this->getFilename();

    if (!$filename) {
      parent::publish($am);
      return;
    }

    $this->css = [$filename];

    $tmpFile = $this->sourcePath . DIRECTORY_SEPARATOR . $filename;

    // копируем чтобы потом assetManager скопировал файл куда ему надо
    if (!file_exists($tmpFile)) {
      file_put_contents(Yii::getAlias($tmpFile), $this->customStyle->generateCss());
    }

    parent::publish($am);
  }
}

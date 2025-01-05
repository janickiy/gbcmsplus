<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 24.08.16
 * Time: 17:17
 */

namespace mcms\common\widget\modal;


use yii\web\AssetBundle;

class ModalAsset extends AssetBundle
{
  public $sourcePath = '@mcms/common/widget/modal/assets';
  public $js = ['js/modal.js'];
}
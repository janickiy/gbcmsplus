<?php

namespace admin\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * TRICKY В utils есть новая версия этого класса. Важно знать, что в prompt там убран параметр afterShow и добавлен параметр required
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ConfirmAsset extends AssetBundle
{
    public function init()
    {
        parent::init();
        $yes = Yii::t('yii', 'Yes');
        $no = Yii::t('yii', 'No');
        $js = <<<JS
yii.confirm = function (message, ok, cancel) {
  $.SmartMessageBox({
    title : message,
    buttons : '[$no][$yes]'
  }, function(ButtonPressed) {
    if (ButtonPressed === '$yes') {
      !ok || ok();
    }
    if (ButtonPressed === '$no') {
      !cancel || cancel();
    }
  });
};
yii.prompt = function (message, placeholder, ok, cancel, aftershow) {

  $.SmartMessageBox({
    title : message,
    placeholder: placeholder,
    inputValue: '',
    input:'text',
    buttons : '[$no][$yes]'
  }, function(ButtonPressed) {
    if (ButtonPressed === '$yes') {
      !ok || ok();
    }
    if (ButtonPressed === '$no') {
      !cancel || cancel();
    }
  });
  !aftershow || aftershow();
};
JS;
        Yii::$app->view->registerJs($js);
    }
}
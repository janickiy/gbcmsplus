<?php

namespace mcms\statistic\components\grid;

use mcms\common\grid\ActionColumn;
use mcms\common\widget\modal\Modal;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\Url;

class DetailActionColumn extends ActionColumn
{

  public $idField = 'id';
  public $statisticType;
  public $header = '&nbsp;'; // иначе DataTable выводит название столбца "0"

  public function init()
  {
    parent::init();

    $this->buttons['subscription-detail'] = $this->getInfoFn();
    $this->buttons['ik-detail'] = $this->getInfoFn();
    $this->buttons['sells-detail'] = $this->getInfoFn();
    $this->buttons['complain-detail'] = $this->getInfoFn();
  }

  protected function getInfoFn()
  {
    return function ($url, $model, $key) {
      return Modal::widget([
        'toggleButtonOptions' => array_merge([
            'tag' => 'a',
            'title' => Yii::t('yii', 'View'),
            'label' => Html::icon('eye-open'),
            'class' => 'btn btn-xs btn-default',
            'data-pjax' => 0,
          ], $this->buttonOptions),
        'size' => Modal::SIZE_LG,
        'url' => Url::to($url),
      ]);
    };
  }

}
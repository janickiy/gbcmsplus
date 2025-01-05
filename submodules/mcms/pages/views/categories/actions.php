<?php

use mcms\common\helpers\Html;
use mcms\pages\models\Category;
use yii\helpers\Url;

$this->beginBlock('create_button');
  echo Html::a(
    '<i class="glyphicon glyphicon-plus"></i> ' . Category::translate('create'),
    Url::to(Category::getCreateLink()),
    ['class' => 'btn btn-success']
  );
$this->endBlock();

<?php

// кнопка создать
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\Category;
use yii\helpers\Url;

$this->beginBlock('categories_button');
echo Html::a(
  '<i class="glyphicon glyphicon-list"></i> ' . Category::translate('list'),
  Category::getListLink(),
  ['class' => 'btn btn-info']
);
$this->endBlock();

// кнопка редактировать
$this->beginBlock('update_button');
echo Html::a(
  '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::_t("main.update_page"),
  ['update', 'id' => $id],
  ['class' => 'btn btn-warning']
);
$this->endBlock();

// кнопка перейти к списку
$this->beginBlock('list_button');
echo Html::a(
  '<i class="glyphicon glyphicon-list"></i> ' . Yii::_t("main.list_of_pages"),
  ['index'],
  ['class' => 'btn btn-primary']
);
$this->endBlock();


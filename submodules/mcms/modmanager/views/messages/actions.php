<?php
$this->beginBlock('list_button');
echo mcms\common\helpers\Html::a('<i class="glyphicon glyphicon-list"></i> ' . Yii::_t("controller.modules_list"), ['modules/index/'], ['class' => 'btn btn-primary']);
$this->endBlock();

$this->beginBlock('update_button');

$this->endBlock();


$this->beginBlock('create_button');

$this->endBlock();

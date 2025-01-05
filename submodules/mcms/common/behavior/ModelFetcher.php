<?php

namespace mcms\common\behavior;

use yii\base\Behavior;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class ModelFetcher extends Behavior
{

    public $defaultAction;

    /** @var  \mcms\common\storage\StorageInterface */
    public $storage;

    /** @var  Controller */
    public $controller;


    public function fetch($id)
    {
        $module = $this->storage->findOne(['id' => $id]);
        if (!$module) throw new NotFoundHttpException;

        return $module;
    }
}
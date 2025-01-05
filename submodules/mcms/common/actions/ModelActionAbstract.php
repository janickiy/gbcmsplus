<?php

namespace mcms\common\actions;

use Yii;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use mcms\common\web\AjaxResponse;
use yii\widgets\ActiveForm;
use yii\base\Action;

/**
 * Class ModelActionAbstract
 * @package mcms\common\actions
 */
abstract class ModelActionAbstract extends Action
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var ActiveRecord
     */
    protected $modal;


    /**
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function getModel($id = null)
    {
        if (!$id) return (new $this->modelClass);

        $model = call_user_func($this->modelClass . '::findOne', $id);

        if ($model !== null) return $model;

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param ActiveRecord $model
     * @return array
     */
    protected function handleAjaxForm(ActiveRecord $model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (Yii::$app->request->post("submit")) {
                return AjaxResponse::set($model->save());
            }
            return ActiveForm::validate($model);
        }

        return $this->controller->renderAjax('form-modal', [
            'model' => $model,
        ]);
    }
}
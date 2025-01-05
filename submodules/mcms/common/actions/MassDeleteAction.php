<?php

namespace mcms\common\actions;

use mcms\common\widget\MassDeleteWidget;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Class MassDeleteAction
 * @package mcms\common\actions
 */
class MassDeleteAction extends Action
{
    /** @var ActiveRecord */
    public $model;

    public function run()
    {
        $ids = Json::decode(Yii::$app->request->post('value'));
        return AjaxResponse::set(MassDeleteWidget::deleteValues($this->model, $ids));
    }
}

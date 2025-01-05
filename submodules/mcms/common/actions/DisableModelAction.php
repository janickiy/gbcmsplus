<?php

namespace mcms\common\actions;

use mcms\common\web\AjaxResponse;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Class DisableModelAction
 * @package mcms\common\actions
 */
class DisableModelAction extends ModelActionAbstract
{

    /**
     * @param $id
     * @return array
     */
    public function run($id)
    {
        /** @var ActiveRecord $model */
        $model = $this->getModel($id);

        if ($model->setDisabled()->save()) {
            return AjaxResponse::success();
        }

        return AjaxResponse::error(Html::errorSummary($model));
    }


}

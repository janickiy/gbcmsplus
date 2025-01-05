<?php

namespace mcms\common\actions;

use mcms\common\web\AjaxResponse;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Class EnableModelAction
 * @package mcms\common\actions
 */
class EnableModelAction extends ModelActionAbstract
{

    /**
     * @param $id
     * @return array
     */
    public function run($id)
    {
        /** @var ActiveRecord $model */
        $model = $this->getModel($id);

        if ($model->setEnabled()->save()) {
            return AjaxResponse::success();
        }

        return AjaxResponse::error(Html::errorSummary($model));
    }


}

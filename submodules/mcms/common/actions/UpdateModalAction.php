<?php

namespace mcms\common\actions;

/**
 * Class UpdateModalAction
 * @package mcms\common\actions
 */
class UpdateModalAction extends ModelActionAbstract
{

    /**
     * @param $id
     * @return array
     */
    public function run($id)
    {
        $model = $this->getModel($id);
        return $this->handleAjaxForm($model);
    }
}

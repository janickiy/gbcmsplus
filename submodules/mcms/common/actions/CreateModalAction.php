<?php

namespace mcms\common\actions;

/**
 * Class CreateModalAction
 * @package mcms\common\actions
 */
class CreateModalAction extends ModelActionAbstract
{

    /**
     * @return array
     */
    public function run()
    {
        $model = $this->getModel();
        return $this->handleAjaxForm($model);
    }
}

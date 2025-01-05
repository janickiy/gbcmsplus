<?php

namespace mcms\common\output;

use yii\base\Object;

/**
 * Class FakeOutput
 * @package mcms\common\output
 */
class FakeOutput extends Object implements OutputInterface
{
    /**
     * @param $message
     * @param array $params
     */
    public function log($message, $params = [])
    {
        // simply faker
    }
}
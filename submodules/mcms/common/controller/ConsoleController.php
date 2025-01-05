<?php

namespace mcms\common\controller;


use mcms\common\traits\LogTrait;
use yii\console\Controller;

/**
 * Class ConsoleController
 * @package mcms\common\controller
 */
abstract class ConsoleController extends Controller
{

    use LogTrait;

    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        $args = func_get_args();
        array_shift($args);
        $this->log(PHP_EOL . date('H:i:s') . ': ' . $string, $args);
    }
}
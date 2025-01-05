<?php

namespace mcms\common\output;

use yii\base\Object;
use yii\helpers\Console;

/**
 * Class ConsoleOutput
 * @package mcms\common\output
 */
class ConsoleOutput extends Object implements OutputInterface
{
    /**
     * @param $message
     * @param array $params
     */
    public function log($message, $params = [])
    {
        $before = '';
        $after = '';

        if (is_array($params) && !empty($params)) {
            if (($breakBefore = array_search(self::BREAK_BEFORE, $params)) !== false) {
                $before = "\n";
                unset($params[$breakBefore]);
            }
            if (($breakAfter = array_search(self::BREAK_AFTER, $params)) !== false) {
                $after = "\n";
                unset($params[$breakAfter]);
            }
            if (!empty($params)) {
                $message = Console::ansiFormat($message, $params);
            }
        }
        Console::stdout($before . $message . $after);
    }
}
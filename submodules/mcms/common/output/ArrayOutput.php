<?php

namespace mcms\common\output;

use yii\base\BaseObject;

class ArrayOutput extends BaseObject implements OutputInterface
{
    private static $_messages = [];

    /**
     * @param $message
     * @param array $params
     */
    public function log($message, $params = [])
    {
        self::$_messages[] = $message;
    }

    /**
     * @return array
     */
    public static function getMessages()
    {
        return self::$_messages;
    }
}
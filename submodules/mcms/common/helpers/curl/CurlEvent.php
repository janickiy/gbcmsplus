<?php

namespace mcms\common\helpers\curl;

use mcms\common\event\Event;

class CurlEvent extends Event
{
    public $config;
    public $result;
    public $error;
    public $curlInfo;

    public function __construct($config = null, $result = null, $error = null, $curlInfo = null)
    {
        $this->config = $config;
        $this->result = $result;
        $this->error = $error;
        $this->curlInfo = $curlInfo;
    }

    function getEventName()
    {
        return 'Curl sender';
    }

    function getReplacements()
    {
        return [
            'config' => $this->config,
            'result' => $this->result,
            'error' => $this->error,
            'curlInfo' => $this->curlInfo,
        ];
    }

    function getReplacementsHelp()
    {
        return [
            'config' => 'Curl config params',
            'result' => 'Curl response',
            'error' => 'Curl error',
            'curlInfo' => 'Curl info',
        ];
    }
}
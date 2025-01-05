<?php

namespace console\controllers;

use mcms\common\helpers\curl\Curl;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Тест cURL
 *
 * Class CurlController
 * @package mcms\modmanager\commands
 */
class CurlController extends Controller
{
    const REQUEST_DELAY = 1;
    const REPEAT_AMOUNT = 10;
    const PARAMS_AMOUNT = 10;
    const PARAM_NAME_LENGTH = 5;
    const PARAM_NAME_VALUE_LENGTH = 32;

    /**
     * Время запроса. command host [proxy:port]
     *
     * @param $host
     * @param null $proxy
     * @throws \mcms\common\helpers\curl\CurlInitException
     * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
     */
    public function actionTest($host, $proxy = null)
    {
        $curl = (new Curl([
            'url' => $host . '?' . $this->getRandomUrlParamsString(),
            'isReturnTransfer' => true,
        ]))->notUseProxy();
        $this->stdout(sprintf("Url: %s\n", $curl->url));

        $totalTime = $this->request($curl);
        $this->stdout("Without proxy server\n");

        if ($proxy || $curl->getProxy()) {
            $curl->useProxy();
            $proxy && $curl->setProxy($proxy);
            $this->stdout(sprintf("Use proxy %s", $curl->getProxy()));

            $totalTimeWithProxy = $this->request($curl);
            $this->stdout('Average time with proxy server');
            $this->stdout(
                sprintf(" %.4fsec\n", $totalTimeWithProxy),
                $totalTimeWithProxy / $totalTime > 1.5 ? Console::FG_RED : Console::FG_GREEN
            );
        } else {
            $this->stdout("Proxy server not set\n", Console::FG_YELLOW);
        }

        $this->stdout('Average time without proxy server');
        $this->stdout(sprintf(" %.4fsec\n", $totalTime), Console::FG_GREEN);
    }

    private function request(Curl $curl)
    {
        $timeList = [];
        $repeat = self::REPEAT_AMOUNT;
        while ($repeat--) {
            self::REQUEST_DELAY && sleep(self::REQUEST_DELAY);

            $content = $curl->getResult();


            $this->stdout(sprintf("Request #%d...", self::REPEAT_AMOUNT - $repeat));
            $this->stdout(self::getCode($curl), Console::FG_BLUE);
            $this->stdout(sprintf(" %.4fsec\n", $time = self::getTotalTime($curl)), Console::FG_GREEN);
            $this->stdout(sprintf("Content: %s\n\n", $content));

            $timeList[] = $time;
        }

        return array_sum($timeList) / count($timeList);
    }

    /**
     * @param Curl $curl
     * @return mixed
     */
    private static function getTotalTime(Curl $curl)
    {
        return ArrayHelper::getValue($curl->getCurlInfo(), 'total_time');
    }

    /**
     * @param Curl $curl
     * @return mixed
     */
    private static function getCode(Curl $curl)
    {
        return ArrayHelper::getValue($curl->getCurlInfo(), 'http_code');
    }

    /**
     * @param Curl $curl
     * @return mixed
     */
    private static function getUrl(Curl $curl)
    {
        return ArrayHelper::getValue($curl->getCurlInfo(), 'url');
    }

    private function getRandomUrlParamsString()
    {
        $params = [];
        for ($i = 0; $i < self::PARAMS_AMOUNT; $i++) {
            $params[Yii::$app->security->generateRandomString(self::PARAM_NAME_LENGTH)]
                = Yii::$app->security->generateRandomString(self::PARAM_NAME_VALUE_LENGTH);
        }

        return http_build_query($params);
    }
}
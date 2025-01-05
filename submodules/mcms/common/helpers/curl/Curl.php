<?php

namespace mcms\common\helpers\curl;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Curl
 * @package mcms\common\helpers
 */
class Curl extends \yii\base\Object implements CurlHelperInterface
{
    const PARAMS_PROXY = 'proxy';

    /**
     * @var string
     */
    public $url;
    /**
     * @var bool
     */
    public $isReturnTransfer = true;
    /**
     * @var int
     */
    public $timeout = 5;

    /**
     * @var bool
     */
    public $verifyCertificate = false;

    /**
     * @var bool
     */
    public $header = false;

    /**
     * @var array
     */
    public $httpHeader = [];

    /**
     * @var string
     */
    public $verifyCertificatePath;

    /**
     * @var
     */
    public $sslVersion;

    /**
     * @var
     */
    public $sslCert;

    /**
     * @var
     */
    public $sslKey;

    /**
     * @var string
     */
    private $proxy;
    /**
     * @var bool
     *
     */
    private $useProxy = true;
    /**
     * @var bool
     * для YII_ENV_DEV прокси автоматически отключается
     */
    public $isPost = false;
    /**
     *
     * Может быть либо массивом, либо строкой. Если массив, будет преобразовано при помощи http_build_query().
     *
     * @var array|string
     */
    public $postFields;

    /**
     * @var string
     */
    public $error;

    /**
     * Номер ошибки curl https://curl.haxx.se/libcurl/c/libcurl-errors.html
     * @var integer
     */
    private $errno;

    /** @var array */
    public $curlInfo;

    /**
     * @var string
     */
    public $userAgent;

    public $followRedirect = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->userAgent = $this->userAgent ?: Yii::$app->getModule('partners')->getProjectName();
        $this->setProxy(ArrayHelper::getValue(Yii::$app->params, self::PARAMS_PROXY));
        parent::init();
    }

    /**
     * Если установлен флаг $isReturnTransfer, то вернёт либо false, либо ответ в виде строки.
     * Если флаг не установлен, то вернёт true|false.
     *
     * @return bool|mixed
     * @throws CurlMandatoryUrlException
     * @throws CurlInitException
     */
    public function getResult()
    {
        // глушим курл при запуске юнитов
        if (defined('YII_ENV_TEST') && YII_ENV_TEST) {
            return null;
        }

        if (!$this->url) throw new CurlMandatoryUrlException();

        $request = curl_init();

        if ($request === false) throw new CurlInitException();

        $config = $this->getConfig();

        curl_setopt_array($request, $config);

        $result = curl_exec($request);

        $this->curlInfo = curl_getinfo($request);

        if ($result === false) {
            $this->error = curl_error($request);
            $this->errno = curl_errno($request);
        }
        (new CurlEvent($config, $result, $this->error, $this->curlInfo))->trigger();

        curl_close($request);

        return $result;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $curlParams = [
            CURLOPT_URL => $this->url,
            CURLOPT_POST => $this->isPost,
            CURLOPT_RETURNTRANSFER => $this->isReturnTransfer,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => $this->verifyCertificate,
            CURLOPT_FOLLOWLOCATION => $this->followRedirect
        ];

        if ($this->userAgent) {
            $curlParams[CURLOPT_USERAGENT] = $this->userAgent;
        }

        if ($this->useProxy && $this->proxy && !YII_ENV_DEV && !YII_ENV_TEST) {
            $curlParams[CURLOPT_PROXY] = $this->proxy;
        }

        if ($this->isPost) {
            $curlParams[CURLOPT_POSTFIELDS] = is_array($this->postFields) ? http_build_query($this->postFields) : $this->postFields;
        }

        if ($this->verifyCertificate) {
            $curlParams[CURLOPT_CAINFO] = $this->verifyCertificatePath;
        }

        if ($this->sslVersion) {
            $curlParams[CURLOPT_SSLVERSION] = $this->sslVersion;
        }

        if ($this->sslCert) {
            $curlParams[CURLOPT_SSLCERT] = $this->sslCert;
        }

        if ($this->sslKey) {
            $curlParams[CURLOPT_SSLKEY] = $this->sslKey;
        }

        if ($this->header) {
            $curlParams[CURLOPT_HEADER] = $this->header;
        }

        if (!empty($this->httpHeader)) {
            $curlParams[CURLOPT_HTTPHEADER] = $this->httpHeader;
        }

        return $curlParams;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return integer
     */
    public function getErrNo()
    {
        return $this->errno;
    }

    /**
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }

    /**
     * @param $proxy
     * @return $this
     * @throws CurlProxyAddressInvalidException
     */
    public function setProxy($proxy)
    {
        $proxySplit = explode(':', $proxy);
        $ip = ArrayHelper::getValue($proxySplit, 0);
        $port = ArrayHelper::getValue($proxySplit, 1);

        if (!empty($ip) && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new CurlProxyAddressInvalidException();
        }

        if (!empty($port) && !is_numeric($port)) {
            throw new CurlProxyAddressInvalidException();
        }

        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @return $this
     */
    public function useProxy()
    {
        $this->useProxy = true;
        return $this;
    }

    public function notUseProxy()
    {
        $this->useProxy = false;
        return $this;
    }
}
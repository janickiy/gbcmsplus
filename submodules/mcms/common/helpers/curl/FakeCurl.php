<?php

namespace mcms\common\helpers\curl;

use yii\helpers\ArrayHelper;

/**
 * Class FakeCurl
 * @package mcms\common\helpers
 */
class FakeCurl extends \yii\base\Object implements CurlHelperInterface
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
        parent::init();
    }

    /**
     * @return bool|mixed
     * @throws CurlMandatoryUrlException
     * @throws CurlInitException
     */
    public function getResult()
    {
        if (!$this->url) throw new CurlMandatoryUrlException();

        $result = rand(0, 1) == 1;

        $this->curlInfo = $this->createCurlInfo($result);

        // TODO Если не событие не нужно - выпилить
        // (new CurlEvent($this->getConfig(), $result, $this->error, $this->curlInfo))->trigger();

        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    protected function createCurlInfo($result)
    {
        return [
            "url" => $this->url,
            "content_type" => 'text/html',
            "http_code" => $result ? 200 : array_rand([400, 500, 501], 1),
            "header_size" => rand(300, 500),
            "request_size" => rand(300, 600),
            "filetime" => -1,
            "ssl_verify_result" => 0,
            "redirect_count" => 0,
            "total_time" => 0.1,
            "namelookup_time" => 0.1,
            "connect_time" => 0.1,
            "pretransfer_time" => 0.1,
            "size_upload" => 0,
            "size_download" => rand(100, 50000),
            "speed_download" => rand(1000, 30000),
            "speed_upload" => 0,
            "download_content_length" => rand(100, 5000),
            "upload_content_length" => -1,
            "starttransfer_time" => 0.123,
            "redirect_time" => 0,
            "certinfo" => [],
            "primary_ip" => '172.17.0.4',
            "primary_port" => 3128,
            "local_ip" => '172.17.0.4',
            "local_port" => 41700,
        ];
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

    /**
     * @return $this
     */
    public function notUseProxy()
    {
        $this->useProxy = false;
        return $this;
    }
}
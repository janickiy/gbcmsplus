<?php

namespace mcms\common\helpers\curl;


/**
 * Interface CurlHelperInterface
 * @package mcms\common\helpers\curl
 */
interface CurlHelperInterface
{
    /**
     * @return bool|mixed
     */
    public function getResult();

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return string
     */
    public function getError();

    /**
     * @return array
     */
    public function getCurlInfo();

    /**
     * @param $proxy
     * @return $this
     * @throws CurlProxyAddressInvalidException
     */
    public function setProxy($proxy);

    /**
     * @return string
     */
    public function getProxy();

    /**
     * @return $this
     */
    public function useProxy();

    /**
     * @return $this
     */
    public function notUseProxy();
}
<?php

namespace mcms\api\components;


use yii\base\Object;

/**
 * Class RequestHandler
 * @package mcms\api\components
 */
class RequestHandler extends Object
{
    /**
     * @var HttpQueryParser
     */
    public $query;

    /**
     * @var BaseMapper
     */
    public $mapper;

    /**
     * RequestHandler constructor.
     * @param BaseMapper $mapper
     * @param HttpQueryParser $query
     * @param array $config
     */
    public function __construct(BaseMapper $mapper, HttpQueryParser $query, array $config = [])
    {
        $this->mapper = $mapper;
        $this->query = $query;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->mapper->setFields($this->query->getFields());
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->mapper->run();
    }
}
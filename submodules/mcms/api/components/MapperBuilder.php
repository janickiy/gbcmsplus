<?php

namespace mcms\api\components;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class RequestHandler
 * Рекурсивно собирает мапперы по данным из QueryParser
 *
 * @package mcms\api\components
 */
class MapperBuilder extends Object
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var BaseMapper
     */
    public $mapper;

    /**
     * RequestHandler constructor.
     * @param string $type
     * @param array $config
     */
    public function __construct($type, array $config = [])
    {
        $this->type = $type;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->mapper = MapperFactory::factory($this->type);
    }

    /**
     * @param MapperDataParser $parser
     * @return BaseMapper
     */
    public function build(MapperDataParser $parser)
    {
        $this->mapper->enabledFields = $this->cleanFields($parser->getFields());
        $this->mapper->search = $parser->getSearchString();
        $this->mapper->searchFields = $this->cleanFields($parser->getSearchFields());
        $this->mapper->customFields = $this->cleanCustomFields($parser->getCustomFields());
        $this->mapper->limit = $parser->getLimit();
        $this->mapper->offset = $this->mapper->limit * $parser->getOffset();
        $this->mapper->depth = $parser->getDepth();
        $this->mapper->filters = $parser->getFilters();
        $this->mapper->orderFields = $parser->getOrderFields();

        if ($parser->getDepth() >= 0) {
            $this->mapper->relatedMappers = $this->getRelatedMappers($parser);
        }

        return $this->mapper;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function cleanFields($fields)
    {
        $clean = [];
        foreach ($fields as $field) {
            if (!is_array($field) && $this->mapper->isFieldAvailable($field)) {
                $clean[] = $field;
            }
        }

        return $clean;
    }

    /**
     * @param array $customFields
     * @return array
     */
    protected function cleanCustomFields($customFields)
    {
        $clean = [];
        foreach ($customFields as $customField) {
            if (!is_array($customField) && $this->mapper->isCustomFieldAvailable($customField)) {
                $clean[] = $customField;
            }
        }

        return $clean;
    }

    /**
     * @param MapperDataParser $parser
     * @return BaseMapper[]
     */
    protected function getRelatedMappers($parser)
    {
        $relatedMappers = [];
        $mapper = $this->mapper;

        foreach ($mapper::$availableRelatedFields as $name => $relation) {
            $mapperFields = ArrayHelper::getValue($parser->getFields(), $name, []);

            if (!$mapperFields) {
                continue;
            }

            $relatedMappers[$relation] = $this->createRelatedMapper($name, $mapperFields, $parser->getFilters(), $parser);
        }

        return $relatedMappers;
    }

    /**
     * @param string $name
     * @param array $fields
     * @param array $filters
     * @param MapperDataParser $parser
     * @return BaseMapper
     * @throws \yii\base\InvalidConfigException
     */
    protected function createRelatedMapper($name, $fields, $filters, $parser)
    {
        $data = new MapperData([
            'fields' => $fields,
            'searchFields' => ArrayHelper::getValue($parser->getSearchFields(), $name, []),
            'customFields' => ArrayHelper::getValue($parser->getCustomFields(), $name, []),
            'orderFields' => ArrayHelper::getValue($parser->getOrderFields(), $name, []),
            'searchString' => $parser->getSearchString(),
            'filters' => $filters,
            'limit' => $parser->getLimit(),
            'depth' => $parser->getDepth() - 1,
        ]);

        $parser = Yii::createObject(MapperDataParser::class, [$data]);

        return (new static($name))->build($parser);
    }
}

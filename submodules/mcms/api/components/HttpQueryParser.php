<?php

namespace mcms\api\components;

use mcms\common\helpers\ArrayHelper;

/**
 * Парсинг GET и POST полей для апи
 */
class HttpQueryParser extends MapperDataParser
{
    const RELATED_FIELDS_DELIMITER = '__';

    /** f - fields */
    const PARAM_FIELDS = 'fields';
    /** cf - custom fields */
    const PARAM_CUSTOM_FIELDS = 'custom_fields';
    /** sf - search fields */
    const PARAM_SEARCH_FIELDS = 'search_fields';
    /** s - search */
    const PARAM_SEARCH_STRING = 'search';
    /** of - order fields */
    const PARAM_ORDER_FIELDS = 'order_fields';
    /** l - limit */
    const PARAM_LIMIT = 'limit';
    /** p - page */
    const PARAM_OFFSET = 'page';
    /** d - depth */
    const PARAM_DEPTH = 'depth';
    const PARAM_FILTERS = 'filters';

    /**
     * QueryParser constructor.
     * @param string|array $query
     * @param array $config
     */
    public function __construct($query, array $config = [])
    {
        $data = $this->parseQuery($query);

        parent::__construct($data, $config);
    }

    /**
     * @param string|array $query
     * @return MapperData
     */
    public function parseQuery($query)
    {
        if (is_string($query)) {
            parse_str($query, $query);
        }

        return new MapperData([
            'fields' => ArrayHelper::getValue($query, self::PARAM_FIELDS),
            'customFields' => ArrayHelper::getValue($query, self::PARAM_CUSTOM_FIELDS),
            'searchFields' => ArrayHelper::getValue($query, self::PARAM_SEARCH_FIELDS),
            'orderFields' => ArrayHelper::getValue($query, self::PARAM_ORDER_FIELDS),
            'searchString' => ArrayHelper::getValue($query, self::PARAM_SEARCH_STRING),
            'limit' => ArrayHelper::getValue($query, self::PARAM_LIMIT),
            'offset' => ArrayHelper::getValue($query, self::PARAM_OFFSET),
            'depth' => ArrayHelper::getValue($query, self::PARAM_DEPTH),
            'filters' => ArrayHelper::getValue($query, self::PARAM_FILTERS),
        ]);
    }

    /**
     * @param array $default
     * @return array
     */
    public function getFields($default = [])
    {
        if (is_string($this->data->fields)) {
            $this->data->fields = $this->string2Array($this->data->fields);
        }

        return $this->data->fields;
    }

    /**
     * @param array $default
     * @return array
     */
    public function getCustomFields($default = [])
    {
        if (is_string($this->data->customFields)) {
            $this->data->customFields = $this->string2Array($this->data->customFields);
        }

        return $this->data->customFields;
    }

    /**
     * @return array
     */
    public function getSearchFields($default = [])
    {
        if (is_string($this->data->searchFields)) {
            $this->data->searchFields = $this->string2Array($this->data->searchFields);
        }

        return $this->data->searchFields;
    }

    /**
     * @return array
     */
    public function getOrderFields($default = [])
    {
        if (is_string($this->data->orderFields)) {
            $fields = $this->string2Array($this->data->orderFields);

            $this->data->orderFields = $this->processOrdering($fields);
        }

        return $this->data->orderFields;
    }

    /**
     * Преобразует строку вида 'id,name,operators__id,operators__name,operators__countries__name'
     * в массив [
     *   0 => 'id',
     *   1 => 'name',
     *   'operators' => [
     *     0 => 'id',
     *     1 => 'name',
     *     'countries' => [
     *       0 => 'name'
     *     ]
     *   ]
     * ]
     *
     * @param string $string
     * @return array
     */
    protected function string2Array($string)
    {
        $items = explode(',', $string);
        $result = [];
        $rawItems = [];

        foreach ($items as $item) {
            $rawItems[] = $this->explode($item);
        }

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                $result[] = $item;
                continue;
            }

            $key = key($item);

            $result[$key] = $this->merge($result, $item, $key);
        }

        return $result;
    }

    /**
     * Рекурсивно мержит сырые массивы в один, из
     * [
     *   'operators' => [
     *     0 => 'id'
     *   ]
     * ]
     * [
     *   'operators' => [
     *     'countries' => [
     *       0 => 'id'
     *     ]
     *   ]
     * ]
     *
     * на выходе будет [
     *   'operators' => [
     *     0 => 'id',
     *     'countries' => [
     *       0 => 'id',
     *     ]
     *   ]
     * ]
     *
     * @param array $result
     * @param array $item
     * @param string $key
     * @return array
     */
    protected function merge($result, $item, $key)
    {
        if (!isset($result[$key])) {
            return [$item[$key]];
        }

        if (!is_array($item[$key])) {
            return array_merge($result[$key], [$item[$key]]);
        }

        $subKey = key($item[$key]);
        $result[$key][$subKey] = $this->merge($result[$key], $item[$key], $subKey);

        return $result[$key];
    }

    /**
     * Преобразует строку вида 'operators__id' в массив ['operators' => [0 => 'id']]
     * Если строку не получилось разбить, возвращает её же
     *
     * @param string $string
     * @param string $delimiter
     * @return array|string
     */
    protected function explode($string, $delimiter = self::RELATED_FIELDS_DELIMITER)
    {
        if (($pos = strpos($string, $delimiter)) !== false) {
            $key = substr($string, 0, $pos);

            return [$key => $this->explode(substr($string, $pos + mb_strlen($delimiter)))];
        }

        return $string;
    }

    /**
     * @param $fields
     * @param int $defaultSort
     * @return array
     */
    protected function processOrdering($fields, $defaultSort = SORT_ASC)
    {
        $result = [];

        foreach ($fields as $name => $field) {
            $sort = $defaultSort;

            if (!$field) {
                continue;
            }

            if (is_string($field)) {
                if ($field[0] === '-') {
                    $sort = SORT_DESC;
                    $field = mb_substr($field, 1);
                }

                $result[$field] = $sort;
                continue;
            }

            if ($name[0] === '-') {
                $sort = SORT_DESC;
                $name = mb_substr($name, 1);
            }

            if (!isset($result[$name])) {
                $result[$name] = $this->processOrdering($field, $sort);
                continue;
            }

            $result[$name] = array_merge_recursive($result[$name], $this->processOrdering($field, $sort));
        }

        return $result;
    }
}
